<?php
/**
 * QUESTIONMODEL.PHP — Truy vấn bảng questions, answers, question_images,
 * question_tags, reports (phần báo cáo câu hỏi / câu trả lời)
 * Phụ trách: TV5
 *
 * Quy ước:
 *  - Chỉ chứa truy vấn SQL (PDO prepared statements), không echo/redirect.
 *  - Mọi truy vấn "bình thường" phải loại nội dung đã xóa mềm: deleted_at IS NULL
 *  - Câu hỏi / câu trả lời bị Admin ẩn (status = 'hidden') không hiện ra danh sách.
 */

class QuestionModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ============================================================
    // DỮ LIỆU DÙNG CHO BỘ LỌC / FORM
    // ============================================================

    public function getSubjects(): array
    {
        return $this->db->query("SELECT id, name FROM subjects ORDER BY name")->fetchAll();
    }

    public function getTags(): array
    {
        return $this->db->query("SELECT id, name FROM tags ORDER BY name")->fetchAll();
    }

    public function subjectExists(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM subjects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Lọc ra các tag id có thật trong DB (chống giả mạo ID từ form).
     * @param int[] $ids
     * @return int[]
     */
    public function filterExistingTagIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        if (!$ids) return [];
        $in   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE id IN ($in)");
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // ============================================================
    // DANH SÁCH CÂU HỎI (index)
    // ============================================================

    /**
     * Ghép điều kiện WHERE từ bộ lọc.
     * $filters: q (từ khóa), subject_id, tag_id, status ('answered'|'unanswered'|'')
     * @return array{0:string,1:array} [chuỗi WHERE, mảng tham số]
     */
    private function buildWhere(array $filters): array
    {
        $where  = ["q.deleted_at IS NULL", "q.status = 'visible'"];
        $params = [];

        if (($filters['q'] ?? '') !== '') {
            // Escape ký tự đặc biệt của LIKE
            $kw = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']) . '%';
            // PDO (EMULATE_PREPARES = false) không cho dùng lại cùng một tên tham số 2 lần
            $where[] = "(q.title LIKE :kw1 OR q.content LIKE :kw2)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
        }
        if (!empty($filters['subject_id'])) {
            $where[] = "q.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }
        if (!empty($filters['tag_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM question_tags qt WHERE qt.question_id = q.id AND qt.tag_id = :tag_id)";
            $params[':tag_id'] = (int) $filters['tag_id'];
        }
        if (($filters['status'] ?? '') === 'answered') {
            $where[] = "q.accepted_answer_id IS NOT NULL";
        } elseif (($filters['status'] ?? '') === 'unanswered') {
            $where[] = "q.accepted_answer_id IS NULL";
        }

        return ['WHERE ' . implode(' AND ', $where), $params];
    }

    public function countQuestions(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM questions q $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Một trang danh sách câu hỏi, mỗi dòng có thêm: tên người hỏi, tên môn,
     * số câu trả lời, mảng 'tags'.
     */
    public function listQuestions(array $filters, int $offset, int $limit): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $sql = "SELECT q.id, q.title, q.content, q.created_at, q.accepted_answer_id, q.user_id,
                       q.subject_id, s.name AS subject_name,
                       u.full_name AS author_name, u.avatar_path AS author_avatar,
                       (SELECT COUNT(*) FROM answers a
                         WHERE a.question_id = q.id AND a.deleted_at IS NULL AND a.status = 'visible') AS answer_count
                  FROM questions q
                  JOIN users u    ON u.id = q.user_id
                  JOIN subjects s ON s.id = q.subject_id
                  $where
                 ORDER BY q.created_at DESC, q.id DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Gắn tag cho từng câu hỏi bằng 1 truy vấn (tránh N+1)
        $tagsByQuestion = $this->getTagsForQuestions(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['tags'] = $tagsByQuestion[$row['id']] ?? [];
        }
        unset($row);

        return $rows;
    }

    /**
     * @param int[] $questionIds
     * @return array<int, array> [question_id => [ ['id'=>..,'name'=>..], ... ]]
     */
    private function getTagsForQuestions(array $questionIds): array
    {
        if (!$questionIds) return [];
        $in   = implode(',', array_fill(0, count($questionIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT qt.question_id, t.id, t.name
               FROM question_tags qt JOIN tags t ON t.id = qt.tag_id
              WHERE qt.question_id IN ($in) ORDER BY t.name"
        );
        $stmt->execute(array_values($questionIds));
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[(int) $r['question_id']][] = ['id' => (int) $r['id'], 'name' => $r['name']];
        }
        return $out;
    }

    // ============================================================
    // CHI TIẾT CÂU HỎI (show / edit)
    // ============================================================

    /**
     * Lấy 1 câu hỏi chưa bị xóa (kể cả đang bị ẩn — controller tự quyết định cho xem hay không).
     */
    public function findQuestion(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT q.*, s.name AS subject_name,
                    u.full_name AS author_name, u.avatar_path AS author_avatar
               FROM questions q
               JOIN users u    ON u.id = q.user_id
               JOIN subjects s ON s.id = q.subject_id
              WHERE q.id = :id AND q.deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getQuestionTags(int $questionId): array
    {
        return $this->getTagsForQuestions([$questionId])[$questionId] ?? [];
    }

    public function getQuestionImages(int $questionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, image_path, sort_order FROM question_images
              WHERE question_id = :id ORDER BY sort_order, id"
        );
        $stmt->execute([':id' => $questionId]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // TẠO / SỬA / XÓA CÂU HỎI
    // ============================================================

    /**
     * Thêm câu hỏi + tag. Trả về id câu hỏi mới.
     * Controller bọc trong transaction (cùng với upload ảnh).
     */
    public function createQuestion(int $userId, int $subjectId, string $title, string $content, array $tagIds): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO questions (title, content, user_id, subject_id)
             VALUES (:title, :content, :user_id, :subject_id)"
        );
        $stmt->execute([
            ':title'      => $title,
            ':content'    => $content,
            ':user_id'    => $userId,
            ':subject_id' => $subjectId,
        ]);
        $id = (int) $this->db->lastInsertId();
        $this->setQuestionTags($id, $tagIds);
        return $id;
    }

    public function updateQuestion(int $id, int $subjectId, string $title, string $content, array $tagIds): void
    {
        $stmt = $this->db->prepare(
            "UPDATE questions SET title = :title, content = :content, subject_id = :subject_id
              WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            ':title'      => $title,
            ':content'    => $content,
            ':subject_id' => $subjectId,
            ':id'         => $id,
        ]);
        $this->setQuestionTags($id, $tagIds);
    }

    /** Ghi đè toàn bộ tag của câu hỏi. */
    private function setQuestionTags(int $questionId, array $tagIds): void
    {
        $this->db->prepare("DELETE FROM question_tags WHERE question_id = :id")
                 ->execute([':id' => $questionId]);
        if (!$tagIds) return;
        $stmt = $this->db->prepare("INSERT INTO question_tags (question_id, tag_id) VALUES (:q, :t)");
        foreach ($tagIds as $tagId) {
            $stmt->execute([':q' => $questionId, ':t' => $tagId]);
        }
    }

    /** Xóa mềm câu hỏi (câu trả lời giữ nguyên nhưng không còn truy cập được qua câu hỏi). */
    public function softDeleteQuestion(int $id): void
    {
        $this->db->prepare("UPDATE questions SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL")
                 ->execute([':id' => $id]);
    }

    // --- Ảnh đính kèm ---

    public function countImages(int $questionId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM question_images WHERE question_id = :id");
        $stmt->execute([':id' => $questionId]);
        return (int) $stmt->fetchColumn();
    }

    public function addImage(int $questionId, string $path, int $sortOrder): void
    {
        $this->db->prepare(
            "INSERT INTO question_images (question_id, image_path, sort_order) VALUES (:q, :p, :s)"
        )->execute([':q' => $questionId, ':p' => $path, ':s' => $sortOrder]);
    }

    /**
     * Xóa các ảnh (thuộc đúng câu hỏi này) khỏi DB.
     * @return string[] đường dẫn file đã xóa khỏi DB — controller dùng để xóa file thật
     */
    public function deleteImages(int $questionId, array $imageIds): array
    {
        $imageIds = array_values(array_filter(array_map('intval', $imageIds), fn($v) => $v > 0));
        if (!$imageIds) return [];

        $in   = implode(',', array_fill(0, count($imageIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT id, image_path FROM question_images WHERE question_id = ? AND id IN ($in)"
        );
        $stmt->execute(array_merge([$questionId], $imageIds));
        $rows = $stmt->fetchAll();
        if (!$rows) return [];

        $del = $this->db->prepare("DELETE FROM question_images WHERE id = :id AND question_id = :q");
        foreach ($rows as $r) {
            $del->execute([':id' => $r['id'], ':q' => $questionId]);
        }
        return array_column($rows, 'image_path');
    }

    // ============================================================
    // CÂU TRẢ LỜI
    // ============================================================

    /**
     * Các câu trả lời hiển thị được của 1 câu hỏi.
     * Câu trả lời được chấp nhận đứng đầu, còn lại cũ → mới.
     */
    public function getAnswers(int $questionId): array
    {
        $stmt = $this->db->prepare(
            "SELECT a.id, a.content, a.created_at, a.updated_at, a.user_id,
                    u.full_name AS author_name, u.avatar_path AS author_avatar,
                    (q.accepted_answer_id = a.id) AS is_accepted
               FROM answers a
               JOIN users u     ON u.id = a.user_id
               JOIN questions q ON q.id = a.question_id
              WHERE a.question_id = :id AND a.deleted_at IS NULL AND a.status = 'visible'
              ORDER BY is_accepted DESC, a.created_at ASC, a.id ASC"
        );
        $stmt->execute([':id' => $questionId]);
        return $stmt->fetchAll();
    }

    /** Câu trả lời chưa xóa (kể cả bị ẩn) — dùng để kiểm tra quyền sở hữu. */
    public function findAnswer(int $answerId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM answers WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $answerId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createAnswer(int $questionId, int $userId, string $content): int
    {
        $this->db->prepare(
            "INSERT INTO answers (question_id, user_id, content) VALUES (:q, :u, :c)"
        )->execute([':q' => $questionId, ':u' => $userId, ':c' => $content]);
        return (int) $this->db->lastInsertId();
    }

    public function updateAnswer(int $answerId, string $content): void
    {
        $this->db->prepare("UPDATE answers SET content = :c WHERE id = :id AND deleted_at IS NULL")
                 ->execute([':c' => $content, ':id' => $answerId]);
    }

    /**
     * Xóa mềm câu trả lời. Nếu đang là câu trả lời được chấp nhận thì gỡ chấp nhận trước,
     * để câu hỏi không trỏ tới một câu trả lời đã xóa.
     */
    public function softDeleteAnswer(int $answerId, int $questionId): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "UPDATE questions SET accepted_answer_id = NULL
                  WHERE id = :q AND accepted_answer_id = :a"
            )->execute([':q' => $questionId, ':a' => $answerId]);

            $this->db->prepare(
                "UPDATE answers SET deleted_at = NOW()
                  WHERE id = :a AND question_id = :q AND deleted_at IS NULL"
            )->execute([':a' => $answerId, ':q' => $questionId]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Câu trả lời này có thuộc câu hỏi này, còn hiển thị được không? (chống giả mạo ID) */
    public function answerBelongsToQuestion(int $answerId, int $questionId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM answers
              WHERE id = :a AND question_id = :q AND deleted_at IS NULL AND status = 'visible'"
        );
        $stmt->execute([':a' => $answerId, ':q' => $questionId]);
        return (bool) $stmt->fetchColumn();
    }

    public function acceptAnswer(int $questionId, int $answerId): void
    {
        $this->db->prepare(
            "UPDATE questions SET accepted_answer_id = :a WHERE id = :q AND deleted_at IS NULL"
        )->execute([':a' => $answerId, ':q' => $questionId]);
    }

    public function unacceptAnswer(int $questionId): void
    {
        $this->db->prepare(
            "UPDATE questions SET accepted_answer_id = NULL WHERE id = :q AND deleted_at IS NULL"
        )->execute([':q' => $questionId]);
    }

    // ============================================================
    // BÁO CÁO VI PHẠM
    // ============================================================

    /** Người này đã báo cáo mục tiêu này mà admin chưa xử lý chưa? */
    public function hasPendingReport(int $reporterId, string $targetType, int $targetId): bool
    {
        $col  = $targetType === 'answer' ? 'answer_id' : 'question_id';
        $stmt = $this->db->prepare(
            "SELECT 1 FROM reports WHERE reporter_id = :r AND $col = :t AND status = 'pending'"
        );
        $stmt->execute([':r' => $reporterId, ':t' => $targetId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Tạo báo cáo. Bảng reports có CHECK: đúng 1 trong 4 cột đích khác NULL.
     * $targetType: 'question' | 'answer'
     */
    public function createReport(int $reporterId, string $targetType, int $targetId, string $reason, ?string $details): void
    {
        $col = $targetType === 'answer' ? 'answer_id' : 'question_id';
        $this->db->prepare(
            "INSERT INTO reports (reporter_id, $col, reason, details)
             VALUES (:r, :t, :reason, :details)"
        )->execute([
            ':r'       => $reporterId,
            ':t'       => $targetId,
            ':reason'  => $reason,
            ':details' => $details,
        ]);
    }
}
