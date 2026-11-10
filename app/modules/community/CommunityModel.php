<?php
/**
 * COMMUNITYMODEL.PHP — Truy vấn database phân hệ Cộng đồng & Chia sẻ tài liệu
 * Phụ trách: TV3
 */

class CommunityModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách tài liệu công khai đã được duyệt (approved)
     */
    public function getPublicDocuments(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $sql = "SELECT d.*, 
                       u.full_name AS author_name, 
                       u.avatar_path AS author_avatar, 
                       s.name AS subject_name,
                       (SELECT COUNT(*) FROM document_comments c 
                        WHERE c.document_id = d.id AND c.status = 'visible' AND c.deleted_at IS NULL) AS comment_count
                FROM documents d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN subjects s ON d.subject_id = s.id";

        // Join thêm nếu lọc theo tag
        if (!empty($filters['tag_id'])) {
            $sql .= " INNER JOIN document_tags dt ON d.id = dt.document_id AND dt.tag_id = :tag_id";
        }

        $sql .= " WHERE d.visibility = 'public' 
                    AND d.moderation_status = 'approved' 
                    AND d.deleted_at IS NULL";

        $params = [];

        if (!empty($filters['tag_id'])) {
            $params[':tag_id'] = (int) $filters['tag_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (d.title LIKE :keyword OR d.description LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND d.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }

        // Sắp xếp
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'downloads':
                $sql .= " ORDER BY d.download_count DESC, d.created_at DESC";
                break;
            case 'oldest':
                $sql .= " ORDER BY d.published_at ASC, d.created_at ASC";
                break;
            case 'title':
                $sql .= " ORDER BY d.title ASC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY d.published_at DESC, d.created_at DESC";
                break;
        }

        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số tài liệu công khai đã được duyệt (phục vụ phân trang)
     */
    public function countPublicDocuments(array $filters = []): int
    {
        $sql = "SELECT COUNT(DISTINCT d.id) FROM documents d";

        if (!empty($filters['tag_id'])) {
            $sql .= " INNER JOIN document_tags dt ON d.id = dt.document_id AND dt.tag_id = :tag_id";
        }

        $sql .= " WHERE d.visibility = 'public' 
                    AND d.moderation_status = 'approved' 
                    AND d.deleted_at IS NULL";

        $params = [];

        if (!empty($filters['tag_id'])) {
            $params[':tag_id'] = (int) $filters['tag_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (d.title LIKE :keyword OR d.description LIKE :keyword)";
            $params[':keyword'] = '%' . trim($filters['keyword']) . '%';
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND d.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Lấy chi tiết tài liệu theo ID
     */
    public function getDocumentById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT d.*, 
                    u.full_name AS author_name, 
                    u.avatar_path AS author_avatar, 
                    u.role AS author_role,
                    u.created_at AS author_joined_at,
                    s.name AS subject_name,
                    s.description AS subject_desc
             FROM documents d
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN subjects s ON d.subject_id = s.id
             WHERE d.id = :id AND d.deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Lấy các tài liệu liên quan / cùng môn học
     */
    public function getRelatedDocuments(int $subjectId, int $excludeId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            "SELECT d.id, d.title, d.file_size, d.download_count, d.created_at, d.published_at,
                    s.name AS subject_name
             FROM documents d
             LEFT JOIN subjects s ON d.subject_id = s.id
             WHERE d.subject_id = :subject_id 
               AND d.id != :exclude_id 
               AND d.visibility = 'public' 
               AND d.moderation_status = 'approved' 
               AND d.deleted_at IS NULL
             ORDER BY d.download_count DESC, d.created_at DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':subject_id', $subjectId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách ảnh đính kèm của tài liệu
     */
    public function getDocumentImages(int $documentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM document_images WHERE document_id = :doc_id ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([':doc_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách tags của tài liệu
     */
    public function getDocumentTags(int $documentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.id, t.name 
             FROM tags t
             INNER JOIN document_tags dt ON t.id = dt.tag_id
             WHERE dt.document_id = :doc_id
             ORDER BY t.name ASC"
        );
        $stmt->execute([':doc_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy tất cả môn học kèm số tài liệu công khai
     */
    public function getAllSubjectsWithCount(): array
    {
        $stmt = $this->db->query(
            "SELECT s.id, s.name, s.description,
                    (SELECT COUNT(*) FROM documents d 
                     WHERE d.subject_id = s.id 
                       AND d.visibility = 'public' 
                       AND d.moderation_status = 'approved' 
                       AND d.deleted_at IS NULL) AS doc_count
             FROM subjects s
             ORDER BY s.name ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách tags phổ biến
     */
    public function getPopularTags(int $limit = 15): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.id, t.name, COUNT(dt.document_id) AS doc_count
             FROM tags t
             INNER JOIN document_tags dt ON t.id = dt.tag_id
             INNER JOIN documents d ON dt.document_id = d.id
             WHERE d.visibility = 'public' 
               AND d.moderation_status = 'approved' 
               AND d.deleted_at IS NULL
             GROUP BY t.id, t.name
             ORDER BY doc_count DESC, t.name ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $tags = $stmt->fetchAll();

        // Nếu ít tài liệu có tag, lấy các tag mặc định trong DB
        if (empty($tags)) {
            $stmtFallback = $this->db->query("SELECT id, name, 0 AS doc_count FROM tags LIMIT 12");
            return $stmtFallback->fetchAll();
        }
        return $tags;
    }

    /**
     * Thống kê cộng đồng
     */
    public function getCommunityStats(): array
    {
        $stmt1 = $this->db->query(
            "SELECT COUNT(*) FROM documents WHERE visibility = 'public' AND moderation_status = 'approved' AND deleted_at IS NULL"
        );
        $totalDocs = (int) $stmt1->fetchColumn();

        $stmt2 = $this->db->query(
            "SELECT COALESCE(SUM(download_count), 0) FROM documents WHERE visibility = 'public' AND moderation_status = 'approved' AND deleted_at IS NULL"
        );
        $totalDownloads = (int) $stmt2->fetchColumn();

        $stmt3 = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $totalUsers = (int) $stmt3->fetchColumn();

        return [
            'total_docs'      => $totalDocs,
            'total_downloads' => $totalDownloads,
            'total_users'     => $totalUsers,
        ];
    }

    /**
     * Lấy danh sách bình luận của tài liệu
     */
    public function getComments(int $documentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.full_name, u.avatar_path, u.role
             FROM document_comments c
             JOIN users u ON c.user_id = u.id
             WHERE c.document_id = :doc_id 
               AND c.status = 'visible' 
               AND c.deleted_at IS NULL
             ORDER BY c.created_at DESC"
        );
        $stmt->execute([':doc_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Thêm bình luận mới
     */
    public function addComment(int $documentId, int $userId, string $content): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO document_comments (document_id, user_id, content) VALUES (:doc_id, :user_id, :content)"
        );
        $stmt->execute([
            ':doc_id'  => $documentId,
            ':user_id' => $userId,
            ':content' => trim($content),
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Lấy thông tin một bình luận
     */
    public function getCommentById(int $commentId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM document_comments WHERE id = :id AND deleted_at IS NULL LIMIT 1"
        );
        $stmt->execute([':id' => $commentId]);
        return $stmt->fetch();
    }

    /**
     * Sửa bình luận
     */
    public function updateComment(int $commentId, string $content): void
    {
        $stmt = $this->db->prepare(
            "UPDATE document_comments SET content = :content WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([
            ':content' => trim($content),
            ':id'      => $commentId,
        ]);
    }

    /**
     * Xóa bình luận (xóa mềm)
     */
    public function deleteComment(int $commentId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE document_comments SET deleted_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':id' => $commentId]);
    }

    /**
     * Gửi yêu cầu duyệt tài liệu lên cộng đồng (pending)
     */
    public function submitForReview(int $documentId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE documents 
             SET visibility = 'public', moderation_status = 'pending', submitted_at = NOW() 
             WHERE id = :id AND moderation_status != 'hidden'"
        );
        return $stmt->execute([':id' => $documentId]);
    }

    /**
     * Thu hồi chia sẻ (chuyển về private)
     */
    public function revokeShare(int $documentId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE documents 
             SET visibility = 'private', moderation_status = 'not_submitted' 
             WHERE id = :id"
        );
        return $stmt->execute([':id' => $documentId]);
    }

    /**
     * Tạo báo cáo vi phạm
     */
    public function createReport(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO reports (reason, details, document_id, comment_id, reporter_id) 
             VALUES (:reason, :details, :document_id, :comment_id, :reporter_id)"
        );
        $stmt->execute([
            ':reason'      => $data['reason'],
            ':details'     => !empty($data['details']) ? trim($data['details']) : null,
            ':document_id' => $data['document_id'] ?? null,
            ':comment_id'  => $data['comment_id'] ?? null,
            ':reporter_id' => $data['reporter_id'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}
