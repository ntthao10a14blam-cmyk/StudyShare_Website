# MIGRATION LOG — Theo dõi cập nhật database

Mỗi khi có thay đổi cấu trúc database sau khi đã chạy `schema.sql`,
tạo file SQL mới trong thư mục này và ghi vào bảng dưới đây.

## Cách tạo migration

```bash
# Đặt tên theo số thứ tự và mô tả ngắn
# Ví dụ: 001_add_view_count_to_documents.sql
```

Nội dung file chỉ chứa câu lệnh thay đổi cần thiết:

```sql
USE studyhub_db;
ALTER TABLE documents ADD COLUMN view_count INT NOT NULL DEFAULT 0;
```

## Danh sách migration

| STT | File | Mô tả | Người tạo | Ngày |
|-----|------|--------|-----------|------|
| — | (chưa có migration nào) | — | — | — |

## Quy tắc

- Một file migration = một thay đổi có ý nghĩa
- Đặt số thứ tự tăng dần: `001_`, `002_`, ...
- Báo nhóm khi thêm migration mới
- Ghi rõ trong PR: migration nào cần chạy
- **Không dùng DROP TABLE hoặc TRUNCATE** trừ khi được cả nhóm đồng ý
- Nếu cần xóa cột/bảng: thảo luận trước, sao lưu database
