<?php

class QuestionModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

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

    public function filterExistingTagIds(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($v) => $v > 0)));
        if (!$ids) return [];
        $in   = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE id IN ($in)");
        $stmt->execute($ids);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function buildWhere(array $filters): array
    {
        $where  = ["q.deleted_at IS NULL", "q.status = 'visible'"];
        $params = [];

        if (($filters['q'] ?? '') !== '') {

            $kw = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filters['q']) . '%';

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
        } elseif (($filters['status'] ?? '') === 'mine' && !empty($filters['user_id'])) {
            $where[] = "q.user_id = :mine_uid";
            $params[':mine_uid'] = (int) $filters['user_id'];
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

        $tagsByQuestion = $this->getTagsForQuestions(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['tags'] = $tagsByQuestion[$row['id']] ?? [];
        }
        unset($row);

        return $rows;
    }

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

    public function softDeleteQuestion(int $id): void
    {
        $this->db->prepare("UPDATE questions SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL")
                 ->execute([':id' => $id]);
    }

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

    private function ensureLikesTable(): void
    {
        $this->db->prepare(
            "CREATE TABLE IF NOT EXISTS answer_likes (
                answer_id  BIGINT   NOT NULL,
                user_id    BIGINT   NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (answer_id, user_id),
                INDEX idx_answer_likes_user (user_id),
                CONSTRAINT fk_answer_likes_answer FOREIGN KEY (answer_id) REFERENCES answers(id) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_answer_likes_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB"
        )->execute();
    }

    public function getAnswers(int $questionId, ?int $currentUserId = null): array
    {
        $this->ensureLikesTable();
        $stmt = $this->db->prepare(
            "SELECT a.id, a.content, a.created_at, a.updated_at, a.user_id,
                    u.full_name AS author_name, u.avatar_path AS author_avatar,
                    (SELECT COUNT(*) FROM answer_likes al WHERE al.answer_id = a.id) AS like_count,
                    (SELECT COUNT(*) FROM answer_likes al2 WHERE al2.answer_id = a.id AND al2.user_id = :uid) AS is_liked
               FROM answers a
               JOIN users u ON u.id = a.user_id
              WHERE a.question_id = :id AND a.deleted_at IS NULL AND a.status = 'visible'
              ORDER BY like_count DESC, a.created_at ASC, a.id ASC"
        );
        $stmt->execute([
            ':id'  => $questionId,
            ':uid' => $currentUserId ?? 0,
        ]);
        return $stmt->fetchAll();
    }

    public function toggleAnswerLike(int $answerId, int $userId): bool
    {
        $this->ensureLikesTable();
        $check = $this->db->prepare(
            "SELECT 1 FROM answer_likes WHERE answer_id = :a AND user_id = :u LIMIT 1"
        );
        $check->execute([':a' => $answerId, ':u' => $userId]);

        if ($check->fetchColumn()) {
            $this->db->prepare(
                "DELETE FROM answer_likes WHERE answer_id = :a AND user_id = :u"
            )->execute([':a' => $answerId, ':u' => $userId]);
            return false;
        }

        $this->db->prepare(
            "INSERT INTO answer_likes (answer_id, user_id) VALUES (:a, :u)"
        )->execute([':a' => $answerId, ':u' => $userId]);
        return true;
    }

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

    public function hasPendingReport(int $reporterId, string $targetType, int $targetId): bool
    {
        $col  = $targetType === 'answer' ? 'answer_id' : 'question_id';
        $stmt = $this->db->prepare(
            "SELECT 1 FROM reports WHERE reporter_id = :r AND $col = :t AND status = 'pending'"
        );
        $stmt->execute([':r' => $reporterId, ':t' => $targetId]);
        return (bool) $stmt->fetchColumn();
    }

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
