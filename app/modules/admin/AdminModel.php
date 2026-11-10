<?php

class AdminModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    // Thống kê tổng quan
    public function countUsers(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users"
        )->fetchColumn();
    }
    public function countActiveUsers(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM users WHERE status = 'active'"
        )->fetchColumn();
    }

    public function countDocuments(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM documents
            WHERE deleted_at IS NULL
        ")->fetchColumn();
    }

    public function countPublicDocuments(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM documents
            WHERE visibility = 'public'
              AND moderation_status = 'approved'
              AND deleted_at IS NULL
        ")->fetchColumn();
    }

    public function countPendingDocuments(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM documents
            WHERE moderation_status = 'pending'
              AND deleted_at IS NULL
        ")->fetchColumn();
    }

    public function countDownloads(): int
    {
        return (int) $this->db->query("
            SELECT COALESCE(SUM(download_count), 0)
            FROM documents
            WHERE deleted_at IS NULL
        ")->fetchColumn();
    }

    public function countQuestions(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM questions
            WHERE status = 'visible'
              AND deleted_at IS NULL
        ")->fetchColumn();
    }

    public function countPendingReports(): int
    {
        return (int) $this->db->query("
            SELECT COUNT(*)
            FROM reports
            WHERE status = 'pending'
        ")->fetchColumn();
    }

    public function getLatestPendingDocuments(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        $stmt = $this->db->query("
            SELECT d.*,
                   u.full_name AS owner_name,
                   s.name AS subject_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN subjects s ON s.id = d.subject_id
            WHERE d.moderation_status = 'pending'
              AND d.deleted_at IS NULL
            ORDER BY d.submitted_at DESC, d.id DESC
            LIMIT {$limit}
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Truy vấn dùng chung để lấy nội dung báo cáo

    private function getReportSelectSql(): string
    {
    return "
        SELECT
            r.*,
            reporter.full_name AS reporter_name,
            handler.full_name AS handler_name,

            CASE
                WHEN r.document_id IS NOT NULL THEN 'document'
                WHEN r.question_id IS NOT NULL THEN 'question'
                WHEN r.answer_id IS NOT NULL THEN 'answer'
                WHEN r.comment_id IS NOT NULL THEN 'comment'
                ELSE 'unknown'
            END AS content_type,

            CASE
                WHEN r.document_id IS NOT NULL
                    THEN d.title
                WHEN r.question_id IS NOT NULL
                    THEN q.content
                WHEN r.answer_id IS NOT NULL
                    THEN a.content
                WHEN r.comment_id IS NOT NULL
                    THEN c.content
                ELSE 'Nội dung không tồn tại'
            END AS reported_content,

            CASE
                WHEN r.question_id IS NOT NULL
                    THEN q.title
                WHEN r.answer_id IS NOT NULL
                    THEN aq.title
                WHEN r.comment_id IS NOT NULL
                    THEN cd.title
                ELSE NULL
            END AS parent_question_title,

            d.title AS document_title

        FROM reports r

        LEFT JOIN users reporter
            ON reporter.id = r.reporter_id

        LEFT JOIN users handler
            ON handler.id = r.handled_by

        LEFT JOIN documents d
            ON d.id = r.document_id

        LEFT JOIN questions q
            ON q.id = r.question_id

        LEFT JOIN answers a
            ON a.id = r.answer_id

        LEFT JOIN questions aq
            ON aq.id = a.question_id

        LEFT JOIN document_comments c
            ON c.id = r.comment_id

        LEFT JOIN documents cd
            ON cd.id = c.document_id
    ";
}

    public function getLatestReports(int $limit = 5): array
    {
        $limit = max(1, min(20, $limit));

        $sql = $this->getReportSelectSql() . "
            ORDER BY
                CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END,
                r.created_at DESC,
                r.id DESC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Quản lý tài khoản

    public function getUsers(
        string $keyword = '',
        int $page = 1,
        int $limit = 20
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $keyword = trim($keyword);

        $sql = "
            SELECT id, full_name, email, role, status, created_at
            FROM users
        ";

        $params = [];

        if ($keyword !== '') {
            $sql .= "
                WHERE full_name LIKE ?
                   OR email LIKE ?
            ";

            $search = '%' . $keyword . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countFilteredUsers(string $keyword = ''): int
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $this->countUsers();
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM users
            WHERE full_name LIKE ?
               OR email LIKE ?
        ");

        $search = '%' . $keyword . '%';
        $stmt->execute([$search, $search]);

        return (int) $stmt->fetchColumn();
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, full_name, email, role, status
            FROM users
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function toggleUserLock(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status = CASE
                WHEN status = 'active' THEN 'locked'
                WHEN status = 'locked' THEN 'active'
                ELSE status
            END
            WHERE id = ?
              AND status IN ('active', 'locked')
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    // Quản lý môn học

    public function getSubjects(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, description, created_at
            FROM subjects
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function subjectExists(string $name, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM subjects
            WHERE LOWER(TRIM(name)) = LOWER(TRIM(?))
              AND id <> ?
        ");

        $stmt->execute([$name, $excludeId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function createSubject(
        string $name,
        ?string $description = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO subjects (name, description)
            VALUES (?, ?)
        ");

        return $stmt->execute([$name, $description]);
    }

    public function updateSubject(
        int $id,
        string $name,
        ?string $description = null
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE subjects
            SET name = ?, description = ?
            WHERE id = ?
        ");

        $stmt->execute([$name, $description, $id]);

        return $stmt->rowCount() > 0;
    }

    public function getSubjectUsage(int $id): array
    {
    $stmt = $this->db->prepare("
        SELECT
            (
                SELECT COUNT(*)
                FROM documents
                WHERE subject_id = ?
            ) AS documents_count,

            (
                SELECT COUNT(*)
                FROM questions
                WHERE subject_id = ?
            ) AS questions_count
    ");

    $stmt->execute([$id, $id]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'documents' => (int) ($result['documents_count'] ?? 0),
        'questions' => (int) ($result['questions_count'] ?? 0)
    ];
    }

    public function canDeleteSubject(int $id): bool
    {
        $usage = $this->getSubjectUsage($id);

        return $usage['documents'] === 0
            && $usage['questions'] === 0;
    }

    public function deleteSubject(int $id): bool
    {
        if (!$this->canDeleteSubject($id)) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM subjects
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function countSubjects(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM subjects"
        )->fetchColumn();
    }

    // Quản lý thẻ

    public function getTags(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, created_at
            FROM tags
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tagExists(string $name, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM tags
            WHERE LOWER(TRIM(name)) = LOWER(TRIM(?))
              AND id <> ?
        ");

        $stmt->execute([$name, $excludeId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function createTag(string $name): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tags (name)
            VALUES (?)
        ");

        return $stmt->execute([$name]);
    }

    public function updateTag(int $id, string $name): bool
    {
        $stmt = $this->db->prepare("
            UPDATE tags
            SET name = ?
            WHERE id = ?
        ");

        $stmt->execute([$name, $id]);

        return $stmt->rowCount() > 0;
    }

    public function canDeleteTag(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT
                (
                    SELECT COUNT(*)
                    FROM document_tags
                    WHERE tag_id = ?
                ) +
                (
                    SELECT COUNT(*)
                    FROM question_tags
                    WHERE tag_id = ?
                )
        ");

        $stmt->execute([$id, $id]);

        return (int) $stmt->fetchColumn() === 0;
    }

    public function deleteTag(int $id): bool
    {
        if (!$this->canDeleteTag($id)) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM tags
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    // Quản lý tài liệu

    public function getPendingDocuments(): array
    {
        $stmt = $this->db->query("
            SELECT d.*,
                   u.full_name AS owner_name,
                   s.name AS subject_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN subjects s ON s.id = d.subject_id
            WHERE d.moderation_status = 'pending'
              AND d.deleted_at IS NULL
            ORDER BY d.submitted_at DESC, d.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicDocuments(): array
    {
        $stmt = $this->db->query("
            SELECT d.*,
                   u.full_name AS owner_name,
                   s.name AS subject_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN subjects s ON s.id = d.subject_id
            WHERE d.visibility = 'public'
              AND d.moderation_status = 'approved'
              AND d.deleted_at IS NULL
            ORDER BY d.published_at DESC, d.id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocumentById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*,
                   u.full_name AS owner_name,
                   s.name AS subject_name
            FROM documents d
            LEFT JOIN users u ON u.id = d.user_id
            LEFT JOIN subjects s ON s.id = d.subject_id
            WHERE d.id = ?
              AND d.deleted_at IS NULL
            LIMIT 1
        ");

        $stmt->execute([$id]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        return $document ?: null;
    }

    public function reviewDocument(
        int $documentId,
        int $adminId,
        string $action,
        ?string $reason = null
    ): bool {
        if (!in_array($action, ['approved', 'rejected', 'hidden'], true)) {
            throw new InvalidArgumentException('Thao tác không hợp lệ.');
        }

        if ($action !== 'approved' && trim((string) $reason) === '') {
            throw new InvalidArgumentException('Vui lòng nhập lý do.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT id, moderation_status, visibility
                FROM documents
                WHERE id = ?
                  AND deleted_at IS NULL
                FOR UPDATE
            ");

            $stmt->execute([$documentId]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$document) {
                throw new RuntimeException('Không tìm thấy tài liệu.');
            }

            if (
                in_array($action, ['approved', 'rejected'], true)
                && $document['moderation_status'] !== 'pending'
            ) {
                throw new RuntimeException('Tài liệu không còn chờ duyệt.');
            }

            if (
                $action === 'hidden'
                && (
                    $document['visibility'] !== 'public'
                    || $document['moderation_status'] !== 'approved'
                )
            ) {
                throw new RuntimeException('Tài liệu không còn ở trạng thái công khai.');
            }

            if ($action === 'approved') {
                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = 'approved',
                        visibility = 'public',
                        published_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([$documentId]);
            } else {
                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = ?,
                        visibility = 'private'
                    WHERE id = ?
                ");

                $stmt->execute([$action, $documentId]);
            }

            $stmt = $this->db->prepare("
                INSERT INTO document_reviews
                    (action, reason, document_id, admin_id)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $action,
                $reason,
                $documentId,
                $adminId
            ]);

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    // Ẩn tài liệu đang được công khai

    public function hidePublicDocument(
        int $documentId,
        int $adminId,
        string $reason
    ): bool {
        return $this->reviewDocument(
            $documentId,
            $adminId,
            'hidden',
            $reason
        );
    }

    // Quản lý báo cáo

    public function getReports(string $status = ''): array
    {
        $sql = $this->getReportSelectSql();

        $params = [];

        if (in_array($status, ['pending', 'resolved', 'dismissed'], true)) {
            $sql .= " WHERE r.status = ?";
            $params[] = $status;
        }

        $sql .= "
            ORDER BY
                CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END,
                r.created_at DESC,
                r.id DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            $this->getReportSelectSql() . "
                WHERE r.id = ?
                LIMIT 1
            "
        );

        $stmt->execute([$id]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        return $report ?: null;
    }

    public function handleReport(
        int $reportId,
        int $adminId,
        string $status,
        ?string $resolutionNote = null
    ): bool {
        if (!in_array($status, ['resolved', 'dismissed'], true)) {
            throw new InvalidArgumentException('Trạng thái báo cáo không hợp lệ.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT id
                FROM reports
                WHERE id = ?
                  AND status = 'pending'
                FOR UPDATE
            ");

            $stmt->execute([$reportId]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new RuntimeException('Báo cáo không tồn tại hoặc đã được xử lý.');
            }

            $stmt = $this->db->prepare("
                UPDATE reports
                SET status = ?,
                    handled_by = ?,
                    resolution_note = ?,
                    handled_at = NOW()
                WHERE id = ?
                  AND status = 'pending'
            ");

            $stmt->execute([
                $status,
                $adminId,
                $resolutionNote,
                $reportId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('Không thể cập nhật báo cáo.');
            }

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }

    // Ẩn nội dung bị báo cáo và đóng báo cáo

    public function hideReportedContent(
        int $reportId,
        int $adminId,
        string $reason
    ): bool {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Vui lòng nhập lý do xử lý.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                SELECT *
                FROM reports
                WHERE id = ?
                  AND status = 'pending'
                FOR UPDATE
            ");

            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$report) {
                throw new RuntimeException('Báo cáo không tồn tại hoặc đã được xử lý.');
            }

            if (!empty($report['document_id'])) {
                $documentId = (int) $report['document_id'];

                $stmt = $this->db->prepare("
                    SELECT id
                    FROM documents
                    WHERE id = ?
                      AND deleted_at IS NULL
                    FOR UPDATE
                ");

                $stmt->execute([$documentId]);

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    throw new RuntimeException('Tài liệu được báo cáo không tồn tại.');
                }

                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = 'hidden',
                        visibility = 'private'
                    WHERE id = ?
                ");

                $stmt->execute([$documentId]);

                $stmt = $this->db->prepare("
                    INSERT INTO document_reviews
                        (action, reason, document_id, admin_id)
                    VALUES ('hidden', ?, ?, ?)
                ");

                $stmt->execute([$reason, $documentId, $adminId]);
            } elseif (!empty($report['question_id'])) {
                $stmt = $this->db->prepare("
                    SELECT id
                    FROM questions
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([(int) $report['question_id']]);

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    throw new RuntimeException('Câu hỏi được báo cáo không tồn tại.');
                }

                $stmt = $this->db->prepare("
                    UPDATE questions
                    SET status = 'hidden'
                    WHERE id = ?
                ");

                $stmt->execute([(int) $report['question_id']]);
            } elseif (!empty($report['answer_id'])) {
                $stmt = $this->db->prepare("
                    SELECT id
                    FROM answers
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([(int) $report['answer_id']]);

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    throw new RuntimeException('Câu trả lời được báo cáo không tồn tại.');
                }

                $stmt = $this->db->prepare("
                    UPDATE answers
                    SET status = 'hidden'
                    WHERE id = ?
                ");

                $stmt->execute([(int) $report['answer_id']]);
            } elseif (!empty($report['comment_id'])) {
                $stmt = $this->db->prepare("
                    SELECT id
                    FROM document_comments
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([(int) $report['comment_id']]);

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    throw new RuntimeException('Bình luận được báo cáo không tồn tại.');
                }

                $stmt = $this->db->prepare("
                    UPDATE document_comments
                    SET status = 'hidden'
                    WHERE id = ?
                ");

                $stmt->execute([(int) $report['comment_id']]);
            } else {
                throw new RuntimeException('Báo cáo không có nội dung hợp lệ.');
            }

            $stmt = $this->db->prepare("
                UPDATE reports
                SET status = 'resolved',
                    handled_by = ?,
                    resolution_note = ?,
                    handled_at = NOW()
                WHERE id = ?
                  AND status = 'pending'
            ");

            $stmt->execute([$adminId, $reason, $reportId]);

            if ($stmt->rowCount() === 0) {
                throw new RuntimeException('Không thể cập nhật trạng thái báo cáo.');
            }

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}