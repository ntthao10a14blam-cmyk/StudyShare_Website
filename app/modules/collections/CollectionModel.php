<?php
// TV4: Các câu lệnh SQL cho bộ sưu tập và tài liệu yêu thích.
class CollectionModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(int $userId): array
    {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM collection_documents cd
                JOIN documents d ON d.id = cd.document_id
                WHERE cd.collection_id = c.id AND d.deleted_at IS NULL
                AND (d.user_id = c.user_id OR (d.visibility = 'public' AND d.moderation_status = 'approved'))) AS total
                FROM collections c WHERE c.user_id = ? ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id, int $userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM collections WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name, string $description, int $userId): void
    {
        $stmt = $this->db->prepare("INSERT INTO collections (name, description, user_id, created_at, updated_at)
                                    VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$name, $description, $userId]);
    }

    public function update(int $id, string $name, string $description, int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE collections SET name = ?, description = ?, updated_at = NOW()
                                    WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $description, $id, $userId]);
    }

    public function delete(int $id, int $userId): void
    {
        if (!$this->find($id, $userId)) return;
        // Xóa liên kết trước; KHÔNG xóa tài liệu gốc.
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM collection_documents WHERE collection_id = ?");
            $stmt->execute([$id]);
            $stmt = $this->db->prepare("DELETE FROM collections WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function documents(int $collectionId, int $userId): array
    {
        $sql = "SELECT d.id, d.title, d.user_id, d.visibility
                FROM collection_documents cd JOIN documents d ON d.id = cd.document_id
                WHERE cd.collection_id = ? AND d.deleted_at IS NULL
                AND (d.user_id = ? OR (d.visibility = 'public' AND d.moderation_status = 'approved'))
                ORDER BY cd.added_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$collectionId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function accessibleDocument(int $documentId, int $userId): bool
    {
        $sql = "SELECT id FROM documents WHERE id = ? AND deleted_at IS NULL
                AND (user_id = ? OR (visibility = 'public' AND moderation_status = 'approved'))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$documentId, $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function addableDocuments(int $userId, int $collectionId): array
    {
        $sql = "SELECT id, title FROM documents WHERE deleted_at IS NULL
                AND (user_id = ? OR (visibility = 'public' AND moderation_status = 'approved'))
                AND id NOT IN (SELECT document_id FROM collection_documents WHERE collection_id = ?)
                ORDER BY created_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $collectionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addDocument(int $collectionId, int $documentId): void
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO collection_documents
                                    (collection_id, document_id, added_at) VALUES (?, ?, NOW())");
        $stmt->execute([$collectionId, $documentId]);
    }

    public function removeDocument(int $collectionId, int $documentId): void
    {
        $stmt = $this->db->prepare("DELETE FROM collection_documents WHERE collection_id = ? AND document_id = ?");
        $stmt->execute([$collectionId, $documentId]);
    }

    public function favorites(int $userId): array
    {
        $sql = "SELECT d.id, d.title, d.user_id, d.visibility
                FROM favorites f JOIN documents d ON d.id = f.document_id
                WHERE f.user_id = ? AND d.deleted_at IS NULL
                AND (d.user_id = ? OR (d.visibility = 'public' AND d.moderation_status = 'approved'))
                ORDER BY f.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addableFavorites(int $userId): array
    {
        $sql = "SELECT id, title FROM documents WHERE deleted_at IS NULL
                AND (user_id = ? OR (visibility = 'public' AND moderation_status = 'approved'))
                AND id NOT IN (SELECT document_id FROM favorites WHERE user_id = ?)
                ORDER BY created_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isFavorite(int $userId, int $documentId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND document_id = ?");
        $stmt->execute([$userId, $documentId]);
        return (bool) $stmt->fetchColumn();
    }

    public function toggleFavorite(int $userId, int $documentId): void
    {
        if ($this->isFavorite($userId, $documentId)) {
            $stmt = $this->db->prepare("DELETE FROM favorites WHERE user_id = ? AND document_id = ?");
            $stmt->execute([$userId, $documentId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO favorites (user_id, document_id, created_at)
                                        VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $documentId]);
        }
    }
}
