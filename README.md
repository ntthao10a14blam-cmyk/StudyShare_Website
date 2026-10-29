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
cd StudyShare_Website
```

### 2. Cấu hình database

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
- Hoặc truy cập: `http://localhost/StudyShare_Website/public/`

---

## Tài khoản demo (sau khi chạy seed.sql)

| Vai trò | Email              | Mật khẩu   |
|---------|--------------------|-------------|
| Admin   | admin@studyhub.vn  | Admin@123   |
| User    | user1@studyhub.vn  | User@123    |
| User    | user2@studyhub.vn  | User@123    |

---

## Cấu trúc thư mục

```
StudyShare_Website/
├── app/
│   ├── core/           # Kết nối DB, Auth, Upload, Helpers dùng chung
│   ├── modules/        # Từng phân hệ (auth, documents, community, ...)
│   └── views/layouts/  # Header, footer, navbar, 404 dùng chung
├── config/             # File cấu hình (config.php, database)
├── database/           # SQL schema, seed, migrations
├── docs/               # Tài liệu thiết kế UI, phân công, quy trình
│   ├── QUY_CHUAN_THIET_KE_UI.md  # Thống nhất font, màu, header/footer, web cộng đồng
│   └── PHAN_CONG_VA_QUY_TRINH.md # Phân công, quy định Lead DB & chạy code sau GD2
├── public/             # Điểm vào website (index.php, assets/css, assets/js)
└── storage/            # File upload (không đưa lên Git)
```

---

## Quy chuẩn giao diện (UI Standards)

Dựa trên phản hồi và thống nhất của nhóm, giao diện StudyHub tuân theo tiêu chuẩn:

1. **Font chữ thống nhất:** Dùng font `Inter` cho toàn bộ trang từ header đến footer.
2. **Màu sắc đồng bộ:** Bảng màu chủ đạo gồm Xanh lá ngọc (`#0b766e`), Vàng pastel nhấn (`#f5d77a`), Text Slate (`#203b38`), và Nền dịu mắt (`#f8faf7`).
3. **Header & Footer đồng bộ:** Cùng phong cách nền trắng sạch (`bg-white`), viền mỏng (`1px solid #d4e5e1`), logo và màu chữ tương đồng.
4. **Định dạng Web cộng đồng:** Định dạng basic, thoáng đãng, gọn mắt; hiển thị danh sách bài viết/feed rõ ràng, không hiệu ứng rườm rà.

> Xem chi tiết hướng dẫn tại: [docs/QUY_CHUAN_THIET_KE_UI.md](file:///d:/Xampp/htdocs/StudyShare_Website/docs/QUY_CHUAN_THIET_KE_UI.md)

---

## Phân công thành viên & Quy trình 2 Giai đoạn

| TV  | Thành viên phụ trách | Vai trò & Nhiệm vụ chính | Nhánh Git |
|-----|----------------------|--------------------------|-----------|
| **TV1** | **Thảo (Trưởng nhóm)** | **Nắm SQL Schema chính (Database Lead)**, Nền tảng, Auth, Phân quyền, Tích hợp hệ thống | `feature/tv1-Thao-auth` |
| **TV2** | TV2 | Phân hệ Tài liệu cá nhân (Upload, quản lý file) | `feature/documents` |
| **TV3** | TV3 | Phân hệ Tài liệu cộng đồng, tìm kiếm, bình luận | `feature/community` |
| **TV4** | TV4 | Phân hệ Bộ sưu tập, yêu thích, giao diện chung | `feature/collections` |
| **TV5** | TV5 | Phân hệ Hỏi đáp học tập (Q&A) | `feature/questions` |
| **TV6** | TV6 | Phân hệ Quản trị viên (Admin dashboard, kiểm duyệt) | `feature/admin` |

### Quy định quy trình phát triển:
- **Giai đoạn 1 (GD1):** TV1 phụ trách xây dựng cấu trúc database chuẩn (`schema.sql`, `seed.sql`) và khung dự án.
- **Giai đoạn 2 (GD2):** Các thành viên phát triển chức năng theo nhánh riêng. Nếu có phát sinh thay đổi database, tạo file migration trong `database/migrations/` và báo cho bạn phụ trách SQL Schema chính.
- **Sau khi Giai đoạn 2 hoàn thành:**
  - **Bạn nắm SQL Schema chính (TV1 - Thảo) sẽ tổng hợp lại database và trực tiếp chạy code kiểm thử tích hợp (end-to-end run)** trên toàn bộ hệ thống để đảm bảo dữ liệu, quan hệ khóa ngoại và các luồng xử lý chạy mượt mà trước khi bàn giao/nghiệm thu.

> Xem chi tiết hướng dẫn tại: [docs/PHAN_CONG_VA_QUY_TRINH.md](file:///d:/Xampp/htdocs/StudyShare_Website/docs/PHAN_CONG_VA_QUY_TRINH.md)

---

## Quy trình làm việc với Git

1. Nhận Issue / Nhiệm vụ
2. `git switch main && git pull --ff-only origin main`
3. `git switch -c feature/ten-chuc-nang`
4. Code theo quy chuẩn UI và Schema đã thống nhất, kiểm tra kỹ
5. `git add <file>` → `git commit -m "feat: mo ta"` → `git push`
6. Tạo Pull Request → người khác review → TV1 kiểm tra và merge
