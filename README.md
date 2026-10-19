# StudyHub — Website Cộng Đồng Học Tập

Website cộng đồng học tập và chia sẻ tài liệu dành cho sinh viên.

---

## Yêu cầu hệ thống

| Phần mềm | Phiên bản tối thiểu |
|----------|---------------------|
| PHP      | 8.1                 |
| MySQL    | 8.0                 |
| XAMPP    | 8.1+                |

---

## Cài đặt lần đầu

### 1. Clone repository

```bash
git clone <URL-repository>
cd study_website
```

### 2. Cấu hình database

Sao chép file cấu hình mẫu và điền thông tin máy của bạn:

```bash
copy config\database.example.php config\database.local.php
```

Mở `config/database.local.php` và sửa:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'studyhub_db');
define('DB_USER', 'root');       // username MySQL của bạn
define('DB_PASS', '');           // password MySQL của bạn
define('DB_PORT', '3306');
```

### 3. Tạo database

Mở phpMyAdmin hoặc MySQL Workbench, chạy theo thứ tự:

```sql
-- Bước 1: Tạo bảng
source database/schema.sql

-- Bước 2: Dữ liệu mẫu
source database/seed.sql
```

Hoặc qua terminal MySQL:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p studyhub_db < database/seed.sql
```

### 4. Tạo thư mục lưu file (nếu chưa có)

```bash
mkdir storage\documents
mkdir storage\images\avatars
mkdir storage\images\document_images
mkdir storage\images\question_images
mkdir storage\temp
```

### 5. Cấu hình XAMPP

- DocumentRoot trỏ vào thư mục `public/` của dự án
- Hoặc truy cập: `http://localhost/study_website/public/`

---

## Tài khoản demo (sau khi chạy seed.sql)

| Vai trò | Email              | Mật khẩu   |
|---------|--------------------|-------------|
| Admin   | admin@studyhub.vn  | Admin@123   |
| User    | user1@studyhub.vn  | User@123    |
| User    | user2@studyhub.vn  | User@123    |

---

## Cập nhật database (khi có migration mới)

Khi nhận thông báo từ nhóm, chạy file migration mới nhất:

```bash
mysql -u root -p studyhub_db < database/migrations/00X_ten_migration.sql
```

Xem danh sách migration cần chạy trong file: `database/migrations/MIGRATION_LOG.md`

---

## Cấu trúc thư mục

```
study_website/
├── app/
│   ├── core/           # Kết nối DB, Auth, Upload dùng chung
│   ├── modules/        # Từng phân hệ (auth, documents, ...)
│   └── views/layouts/  # Header, footer, menu dùng chung
├── config/             # File cấu hình
├── database/           # SQL schema, seed, migrations
├── docs/               # Tài liệu thiết kế, ERD, phân công
├── public/             # Điểm vào website (index.php, assets)
└── storage/            # File upload (không đưa lên Git)
```

---

## Quy trình làm việc với Git

1. Nhận Issue trên GitHub
2. `git switch main && git pull --ff-only origin main`
3. `git switch -c feature/ten-chuc-nang`
4. Code, kiểm tra
5. `git add <file>` → `git commit -m "feat: mo ta"` → `git push`
6. Tạo Pull Request → người khác review → TV1 merge

**Không push trực tiếp vào `main`.**

---

## Phân công thành viên

| TV  | Phụ trách                                    | Nhánh chính          |
|-----|----------------------------------------------|----------------------|
| TV1 | Nền tảng, tài khoản, phân quyền, tích hợp   | feature/auth         |
| TV2 | Upload và quản lý tài liệu cá nhân          | feature/documents    |
| TV3 | Chia sẻ tài liệu, bình luận                 | feature/community    |
| TV4 | Bộ sưu tập, yêu thích, giao diện chung      | feature/collections  |
| TV5 | Hỏi đáp học tập                             | feature/questions    |
| TV6 | Quản trị, kiểm duyệt, thống kê             | feature/admin        |
