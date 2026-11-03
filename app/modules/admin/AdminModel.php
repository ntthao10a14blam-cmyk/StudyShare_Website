<?php

class AdminModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    // DASHBOARD

    public function countUsers(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users
        ");

        return (int) $stmt->fetchColumn();
    }
    public function countDocuments(): int
{
    $stmt = $this->db->query("
        SELECT COUNT(*)
        FROM documents
    ");

    return (int) $stmt->fetchColumn();
}

    public function countPublicDocuments(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM documents
            WHERE visibility = 'public'
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countPendingDocuments(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM documents
            WHERE moderation_status = 'pending'
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countDownloads(): int
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(download_count), 0)
            FROM documents
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countQuestions(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM questions
            WHERE status = 'visible'
        ");

        return (int) $stmt->fetchColumn();
    }
 
    // USERS
    public function getUsers(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                full_name,
                email,
                role,
                status,
                created_at
            FROM users
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function toggleUserLock(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET status =
                CASE
                    WHEN status = 'active' THEN 'locked'
                    ELSE 'active'
                END
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
    // SUBJECTS
    public function getSubjects(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                description,
                created_at
            FROM subjects
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSubject(
        string $name,
        ?string $description = null
    ): bool {
        $stmt = $this->db->prepare("
            INSERT INTO subjects (name, description)
            VALUES (?, ?)
        ");

        return $stmt->execute([
            $name,
            $description
        ]);
    }

    public function updateSubject(
        int $id,
        string $name,
        ?string $description = null
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE subjects
            SET name = ?,
                description = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $name,
            $description,
            $id
        ]);
    }
    public function canDeleteSubject(int $id): bool
{
    $stmt = $this->db->prepare("
        SELECT
            (SELECT COUNT(*) FROM documents WHERE subject_id = ?) +
            (SELECT COUNT(*) FROM questions WHERE subject_id = ?) AS total
    ");

    $stmt->execute([$id, $id]);

    return (int) $stmt->fetchColumn() === 0;
}
    public function deleteSubject(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM subjects
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
    public function countSubjects(): int
{
    $stmt = $this->db->query("
        SELECT COUNT(*)
        FROM subjects
    ");

    return (int) $stmt->fetchColumn();
}

    // TAGS
    public function getTags(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                created_at
            FROM tags
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        return $stmt->execute([
            $name,
            $id
        ]);
    }
    public function canDeleteTag(int $id): bool
{
    $stmt = $this->db->prepare("
        SELECT
            (SELECT COUNT(*) FROM document_tags WHERE tag_id = ?) +
            (SELECT COUNT(*) FROM question_tags WHERE tag_id = ?) AS total
    ");

    $stmt->execute([$id, $id]);

    return (int) $stmt->fetchColumn() === 0;
}
    public function deleteTag(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM tags
            WHERE id = ?
        ");

        return $stmt->execute([$id]);
    }
    // DOCUMENTS

    public function getPendingDocuments(): array
    {
        $stmt = $this->db->query("
            SELECT
                d.*,
                u.full_name AS owner_name,
                s.name AS subject_name
            FROM documents d
            LEFT JOIN users u
                ON d.user_id = u.id
            LEFT JOIN subjects s
                ON d.subject_id = s.id
            WHERE d.moderation_status = 'pending'
            ORDER BY d.submitted_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDocumentById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                d.*,
                u.full_name AS owner_name,
                s.name AS subject_name
            FROM documents d
            LEFT JOIN users u
                ON d.user_id = u.id
            LEFT JOIN subjects s
                ON d.subject_id = s.id
            WHERE d.id = ?
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
        $this->db->beginTransaction();

        try {
            if ($action === 'approved') {
                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = 'approved',
                        visibility = 'public',
                        published_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([$documentId]);
            } elseif ($action === 'rejected') {
                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = 'rejected',
                        visibility = 'private'
                    WHERE id = ?
                ");

                $stmt->execute([$documentId]);
            } elseif ($action === 'hidden') {
                $stmt = $this->db->prepare("
                    UPDATE documents
                    SET moderation_status = 'hidden',
                        visibility = 'private'
                    WHERE id = ?
                ");

                $stmt->execute([$documentId]);
            } else {
                throw new InvalidArgumentException('Action không hợp lệ.');
            }

            $stmt = $this->db->prepare("
                INSERT INTO document_reviews
                    (action, reason, document_id, admin_id)
                VALUES
                    (?, ?, ?, ?)
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
            $this->db->rollBack();
            throw $e;
        }
    }
    // REPORTS
    public function getReports(): array
    {
        $stmt = $this->db->query("
            SELECT
                r.*,
                reporter.full_name AS reporter_name,
                handler.full_name AS handler_name
            FROM reports r
            LEFT JOIN users reporter
                ON r.reporter_id = reporter.id
            LEFT JOIN users handler
                ON r.handled_by = handler.id
            ORDER BY r.created_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM reports
            WHERE id = ?
        ");

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
        $stmt = $this->db->prepare("
            UPDATE reports
            SET status = ?,
                handled_by = ?,
                resolution_note = ?,
                handled_at = NOW()
            WHERE id = ?
        ");

        return $stmt->execute([
            $status,
            $adminId,
            $resolutionNote,
            $reportId
        ]);
    }
    // HIDE CONTENT
    public function hideQuestion(int $questionId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE questions
            SET status = 'hidden'
            WHERE id = ?
        ");

        return $stmt->execute([$questionId]);
    }

    public function hideAnswer(int $answerId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE answers
            SET status = 'hidden'
            WHERE id = ?
        ");

        return $stmt->execute([$answerId]);
    }

    public function hideComment(int $commentId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE document_comments
            SET status = 'hidden'
            WHERE id = ?
        ");

        return $stmt->execute([$commentId]);
    }
}