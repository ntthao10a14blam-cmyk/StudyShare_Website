<?php
/**
 * DOCUMENTMODEL.PHP — Truy vấn database liên quan đến tài liệu
 * Phụ trách: TV2
 * TV3 và TV6 cũng dùng class này — thêm method mới, không sửa method cũ
 */

class DocumentModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Tìm tài liệu theo ID (kèm thông tin môn học, tác giả)
     */
    public function findById(int $id, bool $includeDeleted = false): array|false
    {
        $sql = "
            SELECT d.*,
                   s.name AS subject_name,
                   u.full_name AS author_name,
                   u.avatar_path AS author_avatar,
                   u.email AS author_email
            FROM documents d
            LEFT JOIN subjects s ON d.subject_id = s.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.id = :id
        ";

        if (!$includeDeleted) {
            $sql .= " AND d.deleted_at IS NULL";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Lấy danh sách tài liệu của một user (có bộ lọc, tìm kiếm, phân trang)
     */
    public function findByUser(int $userId, array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $sql = "
            SELECT d.*,
                   s.name AS subject_name,
                   (SELECT COUNT(*) FROM document_images di WHERE di.document_id = d.id) AS image_count,
                   (SELECT image_path FROM document_images di WHERE di.document_id = d.id ORDER BY di.sort_order ASC, di.id ASC LIMIT 1) AS thumbnail_path
            FROM documents d
            LEFT JOIN subjects s ON d.subject_id = s.id
            WHERE d.user_id = :user_id
              AND d.deleted_at IS NULL
        ";

        $params = [':user_id' => $userId];

        // Tìm kiếm theo từ khoá (tiêu đề hoặc mô tả)
        if (!empty($filters['q'])) {
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query)";
            $params[':query'] = '%' . $filters['q'] . '%';
        }

        // Lọc theo môn học
        if (!empty($filters['subject_id'])) {
            $sql .= " AND d.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }

        // Lọc theo trạng thái kiểm duyệt (moderation_status)
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND d.moderation_status = :status";
            $params[':status'] = $filters['status'];
        }

        // Lọc theo hiển thị (visibility: public | private)
        if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
            $sql .= " AND d.visibility = :visibility";
            $params[':visibility'] = $filters['visibility'];
        }

        // Sắp xếp
        $sort = $filters['sort'] ?? 'newest';
        $orderBy = match ($sort) {
            'oldest'    => 'd.created_at ASC',
            'downloads' => 'd.download_count DESC, d.created_at DESC',
            'title'     => 'd.title ASC',
            default     => 'd.created_at DESC',
        };

        $sql .= " ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số tài liệu của user theo bộ lọc (phục vụ phân trang)
     */
    public function countByUser(int $userId, array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM documents d
            WHERE d.user_id = :user_id
              AND d.deleted_at IS NULL
        ";

        $params = [':user_id' => $userId];

        if (!empty($filters['q'])) {
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query)";
            $params[':query'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND d.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND d.moderation_status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['visibility']) && $filters['visibility'] !== 'all') {
            $sql .= " AND d.visibility = :visibility";
            $params[':visibility'] = $filters['visibility'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Thống kê tổng hợp số liệu tài liệu của một user
     */
    public function getUserStats(int $userId): array
    {
        $sql = "
            SELECT
                COUNT(*) AS total_docs,
                SUM(CASE WHEN moderation_status = 'approved' THEN 1 ELSE 0 END) AS approved_docs,
                SUM(CASE WHEN moderation_status = 'pending' THEN 1 ELSE 0 END) AS pending_docs,
                SUM(CASE WHEN visibility = 'private' THEN 1 ELSE 0 END) AS private_docs,
                COALESCE(SUM(download_count), 0) AS total_downloads
            FROM documents
            WHERE user_id = :user_id
              AND deleted_at IS NULL
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row = $stmt->fetch();

        return [
            'total'     => (int) ($row['total_docs'] ?? 0),
            'approved'  => (int) ($row['approved_docs'] ?? 0),
            'pending'   => (int) ($row['pending_docs'] ?? 0),
            'private'   => (int) ($row['private_docs'] ?? 0),
            'downloads' => (int) ($row['total_downloads'] ?? 0),
        ];
    }

    /**
     * Tạo tài liệu mới
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO documents (
                title, description, file_path, original_file_name, file_size,
                visibility, moderation_status, submitted_at, user_id, subject_id, created_at
            ) VALUES (
                :title, :description, :file_path, :original_file_name, :file_size,
                :visibility, :moderation_status, :submitted_at, :user_id, :subject_id, NOW()
            )
        ");

        $stmt->execute([
            ':title'              => $data['title'],
            ':description'        => $data['description'] ?? null,
            ':file_path'          => $data['file_path'],
            ':original_file_name' => $data['original_file_name'],
            ':file_size'          => (int) $data['file_size'],
            ':visibility'         => $data['visibility'] ?? 'private',
            ':moderation_status'  => $data['moderation_status'] ?? 'not_submitted',
            ':submitted_at'       => $data['submitted_at'] ?? null,
            ':user_id'            => $data['user_id'],
            ':subject_id'         => !empty($data['subject_id']) ? (int) $data['subject_id'] : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin tài liệu
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = [
            'title', 'description', 'file_path', 'original_file_name',
            'file_size', 'visibility', 'moderation_status', 'submitted_at',
            'published_at', 'subject_id'
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE documents SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xóa mềm tài liệu
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE documents SET deleted_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Gửi tài liệu yêu cầu duyệt (chuyển sang pending)
     * Ràng buộc: Tài liệu bị admin ẩn (hidden) không được phép tự gửi duyệt lại.
     */
    public function submitForReview(int $id): bool
    {
        $doc = $this->findById($id);
        if (!$doc || $doc['moderation_status'] === 'hidden') {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE documents
            SET visibility = 'public',
                moderation_status = 'pending',
                submitted_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Thu hồi chia sẻ tài liệu (về private, not_submitted)
     */
    public function revokeShare(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE documents
            SET visibility = 'private',
                moderation_status = 'not_submitted',
                submitted_at = NULL,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }

    // ============================================================
    // PHƯƠNG THỨC CHO TV3 (COMMUNITY) VÀ TV6 (ADMIN)
    // ============================================================

    /**
     * Lấy danh sách tài liệu công khai đã duyệt (cho TV3)
     */
    public function findPublicApproved(array $filters = [], int $limit = 12, int $offset = 0): array
    {
        $sql = "
            SELECT d.*,
                   s.name AS subject_name,
                   u.full_name AS author_name,
                   u.avatar_path AS author_avatar,
                   (SELECT COUNT(*) FROM document_comments c WHERE c.document_id = d.id AND c.status = 'visible') AS comment_count,
                   (SELECT COUNT(*) FROM document_images di WHERE di.document_id = d.id) AS image_count,
                   (SELECT image_path FROM document_images di WHERE di.document_id = d.id ORDER BY di.sort_order ASC, di.id ASC LIMIT 1) AS thumbnail_path
            FROM documents d
            LEFT JOIN subjects s ON d.subject_id = s.id
            LEFT JOIN users u ON d.user_id = u.id
            WHERE d.visibility = 'public'
              AND d.moderation_status = 'approved'
              AND d.deleted_at IS NULL
        ";

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query)";
            $params[':query'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['subject_id'])) {
            $sql .= " AND d.subject_id = :subject_id";
            $params[':subject_id'] = (int) $filters['subject_id'];
        }

        $sort = $filters['sort'] ?? 'newest';
        $orderBy = match ($sort) {
            'oldest'    => 'd.published_at ASC, d.created_at ASC',
            'downloads' => 'd.download_count DESC, d.created_at DESC',
            'title'     => 'd.title ASC',
            default     => 'COALESCE(d.published_at, d.created_at) DESC',
        };

        $sql .= " ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số tài liệu công khai đã duyệt (cho TV3)
     */
    public function countPublicApproved(array $filters = []): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM documents d
            WHERE d.visibility = 'public'
              AND d.moderation_status = 'approved'
              AND d.deleted_at IS NULL
        ";

        $params = [];

        if (!empty($filters['q'])) {
            $sql .= " AND (d.title LIKE :query OR d.description LIKE :query)";
            $params[':query'] = '%' . $filters['q'] . '%';
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
     * Cập nhật trạng thái duyệt từ Admin (cho TV6)
     */
    public function updateModerationStatus(int $id, string $status, ?string $reason = null, ?int $adminId = null): bool
    {
        $publishedAt = ($status === 'approved') ? 'NOW()' : 'published_at';

        $stmt = $this->db->prepare("
            UPDATE documents
            SET moderation_status = :status,
                published_at = {$publishedAt},
                updated_at = NOW()
            WHERE id = :id
        ");

        $success = $stmt->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);

        if ($success && $adminId && in_array($status, ['approved', 'rejected', 'hidden'], true)) {
            $reviewStmt = $this->db->prepare("
                INSERT INTO document_reviews (action, reason, document_id, admin_id, created_at)
                VALUES (:action, :reason, :document_id, :admin_id, NOW())
            ");
            $reviewStmt->execute([
                ':action'      => $status,
                ':reason'      => $reason ?? ($status === 'approved' ? 'Tài liệu hợp lệ' : ''),
                ':document_id' => $id,
                ':admin_id'    => $adminId,
            ]);
        }

        return $success;
    }

    // ============================================================
    // QUẢN LÝ ẢNH MINH HOẠ (document_images)
    // ============================================================

    /**
     * Lấy danh sách ảnh minh hoạ của tài liệu
     */
    public function getImages(int $documentId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, image_path, sort_order, created_at
            FROM document_images
            WHERE document_id = :document_id
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Đếm số ảnh hiện có của tài liệu
     */
    public function countImages(int $documentId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM document_images WHERE document_id = :document_id");
        $stmt->execute([':document_id' => $documentId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Thêm một ảnh minh hoạ vào tài liệu
     */
    public function addImage(int $documentId, string $imagePath, int $sortOrder = 0): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO document_images (document_id, image_path, sort_order, created_at)
            VALUES (:document_id, :image_path, :sort_order, NOW())
        ");
        $stmt->execute([
            ':document_id' => $documentId,
            ':image_path'  => $imagePath,
            ':sort_order'  => $sortOrder,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Xoá một ảnh minh hoạ (cả file vật lý trong storage)
     */
    public function deleteImage(int $imageId, int $documentId): bool
    {
        $stmt = $this->db->prepare("
            SELECT image_path FROM document_images
            WHERE id = :id AND document_id = :document_id
            LIMIT 1
        ");
        $stmt->execute([':id' => $imageId, ':document_id' => $documentId]);
        $image = $stmt->fetch();

        if (!$image) {
            return false;
        }

        // Xoá file vật lý
        Upload::delete($image['image_path']);

        // Xoá trong database
        $del = $this->db->prepare("DELETE FROM document_images WHERE id = :id");
        return $del->execute([':id' => $imageId]);
    }

    /**
     * Xoá toàn bộ ảnh minh hoạ của tài liệu
     */
    public function deleteImages(int $documentId): void
    {
        $images = $this->getImages($documentId);
        foreach ($images as $img) {
            Upload::delete($img['image_path']);
        }
        $del = $this->db->prepare("DELETE FROM document_images WHERE document_id = :document_id");
        $del->execute([':document_id' => $documentId]);
    }

    // ============================================================
    // QUẢN LÝ TỪ KHOÁ (document_tags) & MÔN HỌC (subjects)
    // ============================================================

    /**
     * Lấy các từ khoá của tài liệu
     */
    public function getTags(int $documentId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id, t.name
            FROM tags t
            JOIN document_tags dt ON t.id = dt.tag_id
            WHERE dt.document_id = :document_id
            ORDER BY t.name ASC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Đồng bộ từ khoá cho tài liệu (nhận mảng tên tag hoặc chuỗi phân cách bởi dấu phẩy)
     */
    public function syncTags(int $documentId, array|string $tags): void
    {
        if (is_string($tags)) {
            $tagList = array_map('trim', explode(',', $tags));
        } else {
            $tagList = $tags;
        }

        // Loại bỏ rỗng và trùng lặp
        $tagList = array_values(array_unique(array_filter($tagList, fn($t) => trim($t) !== '')));

        // Xoá liên kết cũ
        $delStmt = $this->db->prepare("DELETE FROM document_tags WHERE document_id = :document_id");
        $delStmt->execute([':document_id' => $documentId]);

        if (empty($tagList)) {
            return;
        }

        $findTag = $this->db->prepare("SELECT id FROM tags WHERE name = :name LIMIT 1");
        $insertTag = $this->db->prepare("INSERT INTO tags (name, created_at) VALUES (:name, NOW())");
        $linkTag = $this->db->prepare("INSERT IGNORE INTO document_tags (document_id, tag_id) VALUES (:document_id, :tag_id)");

        foreach ($tagList as $name) {
            $cleanName = mb_strtolower(trim($name), 'UTF-8');
            if (mb_strlen($cleanName, 'UTF-8') > 50 || $cleanName === '') continue;

            $findTag->execute([':name' => $cleanName]);
            $tagId = $findTag->fetchColumn();

            if (!$tagId) {
                $insertTag->execute([':name' => $cleanName]);
                $tagId = $this->db->lastInsertId();
            }

            $linkTag->execute([
                ':document_id' => $documentId,
                ':tag_id'      => (int) $tagId,
            ]);
        }
    }

    /**
     * Lấy lịch sử đánh giá / kiểm duyệt của tài liệu
     */
    public function getReviews(int $documentId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.*, u.full_name AS admin_name
            FROM document_reviews r
            LEFT JOIN users u ON r.admin_id = u.id
            WHERE r.document_id = :document_id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':document_id' => $documentId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy toàn bộ danh sách môn học
     */
    public function getAllSubjects(): array
    {
        return $this->db->query("SELECT id, name, description FROM subjects ORDER BY name ASC")->fetchAll();
    }

    /**
     * Lấy danh sách từ khoá phổ biến
     */
    public function getAllTags(int $limit = 30): array
    {
        return $this->db->query("SELECT id, name FROM tags ORDER BY name ASC LIMIT {$limit}")->fetchAll();
    }
}
