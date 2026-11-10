# PHÂN CÔNG NHIỆM VỤ & QUY TRÌNH PHÁT TRIỂN DỰ ÁN STUDYHUB

Tài liệu này quy định chi tiết về **phân chia công việc giữa 6 thành viên**, xác định rõ vai trò **người nắm giữ SQL Schema chính**, và quy trình **chạy code tích hợp sau khi hoàn thành Giai đoạn 2**.

---

## 1. Phân công vai trò thành viên

| STT | Thành viên | Vai trò & Phụ trách chức năng | Nhánh Git | Ghi chú quan trọng |
|:---:|:---|:---|:---|:---|
| **TV1** | ** ** | **Nắm SQL Schema chính (Database Lead)**, Kiến trúc nền tảng, Xác thực (Auth), Phân quyền, Tích hợp hệ thống | `feature/tv1-Thao-auth` | **Chủ trì Schema & Chạy code tích hợp sau GD2** |
| **TV2** | Thành viên 2 | Phân hệ Tài liệu cá nhân (Tải lên PDF/ảnh, sửa, xoá, quản lý tài liệu riêng) | `feature/tv2-Luu-Quynh-doc` | Tuân thủ schema bảng `documents`, `document_images` |
| **TV3** | Thành viên 3 | Phân hệ Cộng đồng (Danh sách tài liệu công khai, tìm kiếm, bộ lọc, xem chi tiết, bình luận) | `feature/tv3-Quoc-Bao-community` | Tuân thủ schema bảng `documents`, `comments` |
| **TV4** | Thành viên 4 | Phân hệ Bộ sưu tập & Yêu thích (Tạo bộ sưu tập, lưu yêu thích, giao diện chung) | `feature/tv4-Tuan-Anh-collections` | Tuân thủ `collections`, `favorites`, `main.css` |
| **TV5** | Thành viên 5 | Phân hệ Hỏi đáp học tập (Đặt câu hỏi, trả lời, bình chọn câu trả lời đúng) | `feature/tv5-Kieu-Anh-questions` | Tuân thủ schema bảng `questions`, `answers` |
| **TV6** | Thành viên 6 | Phân hệ Quản trị viên (Admin Dashboard, duyệt/ẩn tài liệu, quản lý môn học, người dùng) | `feature/tv6-Tan-Loc-admin` | Tuân thủ quyền `role = admin`, `moderation_status` |

---

## 2. Quy định 2 Giai đoạn phát triển (GD1 & GD2)

### Giai đoạn 1 (GD1): Thiết kế CSDL & Xây dựng Khung ứng dụng
- **Người nắm SQL Schema chính (TV1):**
  - Khởi tạo `database/schema.sql`, thiết lập đúng kiểu dữ liệu, các quan hệ khóa ngoại (Foreign Keys) và `ON DELETE/ON UPDATE`.
  - Khởi tạo `database/seed.sql` với tài khoản demo và dữ liệu môn học, từ khóa ban đầu.
  - Xây dựng lớp kết nối chuẩn `app/core/Database.php` (PDO Singleton) và các helper an toàn (`app/core/Helpers.php`, `app/core/Auth.php`).
- **Các thành viên khác (TV2 → TV6):**
  - Rà soát các bảng thuộc phân hệ mình phụ trách, góp ý với bạn nắm SQL schema chính nếu thiếu cột hoặc quan hệ chưa hợp lý.
  - Clone dự án, thiết lập môi trường XAMPP local theo hướng dẫn trong `README.md`.

---

### Giai đoạn 2 (GD2): Lập trình phân hệ chức năng độc lập
- Mỗi thành viên làm việc trên nhánh `feature/...` được giao.
- **Quy tắc khi cần thay đổi CSDL trong GD2:**
  - **Không tự ý sửa đè vào `database/schema.sql` trên nhánh riêng.**
  - Tạo một file cập nhật trong thư mục `database/migrations/` (ví dụ: `001_add_column_xyz.sql`).
  - Ghi nhận vào file `database/migrations/MIGRATION_LOG.md` và **báo ngay cho bạn nắm SQL Schema chính (TV1)** để thống nhất.
- Thực hiện giao diện theo đúng [Quy chuẩn thiết kế UI](file:///d:/Xampp/htdocs/StudyShare_Website/docs/QUY_CHUAN_THIET_KE_UI.md) (Font Inter, bảng màu đồng bộ, header/footer thống nhất, format web cộng đồng gọn mắt).

---

## 3. Quy trình quan trọng: Chạy code tích hợp sau khi Giai đoạn 2 hoàn thành

> **Ý kiến thành viên nhóm:** *"chia bài ra b nào nắm sql schema chính thì lúc phần gd 2 xong b đó chạy code luôn"*

Đây là bước then chốt quyết định sự ổn định của toàn bộ dự án trước khi nghiệm thu hoặc nộp bài.

### 3.1. Trách nhiệm của bạn nắm SQL Schema chính (TV1):
Sau khi các thành viên hoàn thành code phân hệ của mình ở GD2 và tạo Pull Request (PR):
1. **Kiểm tra và tổng hợp Database:**
   - Gom các file migration từ các bạn vào `database/schema.sql` chính thức.
   - Cập nhật thêm dữ liệu mẫu vào `database/seed.sql` để có sẵn dữ liệu test cho tất cả phân hệ.
2. **Khởi tạo Database sạch (Clean State):**
   - Chạy lại toàn bộ schema trên MySQL:
     ```bash
     mysql -u root -p < database/schema.sql
     mysql -u root -p studyhub_db < database/seed.sql
     ```
3. **Trực tiếp chạy kiểm thử tích hợp (End-to-End Code Run):**
   - Khởi chạy mã nguồn trên môi trường XAMPP (`http://localhost/StudyShare_Website/public/`).
   - Lần lượt chạy và kiểm tra các kịch bản thực tế xuyên suốt giữa các phân hệ:
     - **Luồng 1 (Tài khoản):** Đăng ký tài khoản mới → Đăng nhập → Đổi thông tin cá nhân.
     - **Luồng 2 (Tài liệu):** User tải lên tài liệu cá nhân (TV2) → Chuyển trạng thái gửi duyệt → Admin (TV6) phê duyệt → Tài liệu hiển thị trên cộng đồng (TV3).
     - **Luồng 3 (Tương tác):** Sinh viên xem chi tiết, tải file PDF, lưu vào bộ sưu tập (TV4).
     - **Luồng 4 (Hỏi đáp):** Sinh viên đặt câu hỏi (TV5) → Người khác vào trả lời → Chủ câu hỏi chọn câu trả lời đúng.
     - **Luồng 5 (Kiểm duyệt):** Admin xem thống kê, khoá người dùng vi phạm hoặc ẩn tài liệu sai phạm (TV6).
4. **Xử lý xung đột & Fix lỗi:**
   - Bắt các lỗi sai lệch tên cột giữa Model và SQL, lỗi thiếu dữ liệu Foreign Key, lỗi session hoặc upload file.
   - Sau khi chạy code trơn tru, merge các PR vào nhánh `main` và thông báo toàn nhóm kéo code mới nhất (`git pull`).

---

## 4. Checklist kiểm tra sau Giai đoạn 2

| Hạng mục kiểm tra | Người thực hiện | Tiêu chí đạt |
|---|---|---|
| Khởi tạo CSDL không bị lỗi cú pháp | Bạn nắm SQL Schema chính | File `schema.sql` chạy trôi chảy không báo lỗi FK/Engine |
| Dữ liệu seed có đủ tài khoản mẫu & môn học | Bạn nắm SQL Schema chính | Đăng nhập được cả 3 tài khoản demo (Admin & Users) |
| Kiểm tra luồng upload & lưu file | Bạn nắm SQL Schema chính | File lưu đúng vào `storage/` và phục vụ qua `serve.php` |
| Giao diện đồng bộ toàn trang | Cả nhóm | Toàn bộ các trang có cùng Header/Footer, font Inter, màu Slate/Blue |
| Không còn đường dẫn cứng (hardcoded URL) | Cả nhóm | Toàn bộ link dùng hàm `url()` và `storageUrl()` |
