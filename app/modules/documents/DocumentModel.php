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

    // TODO: TV2 — thêm các method cần thiết
    // Gợi ý các method cần có:
    //   findById(int $id): array|false
    //   findByUser(int $userId, array $filters, int $limit, int $offset): array
    //   countByUser(int $userId, array $filters): int
    //   create(array $data): int
    //   update(int $id, array $data): void
    //   softDelete(int $id): void
    //   findPublicApproved(array $filters, int $limit, int $offset): array
    //   countPublicApproved(array $filters): int
    //   submitForReview(int $id): void
    //   revokeShare(int $id): void
    //   updateModerationStatus(int $id, string $status): void
}
