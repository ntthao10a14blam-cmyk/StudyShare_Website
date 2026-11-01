-- =============================================================
-- STUDYHUB — DỮ LIỆU MẪU (SEED)
-- File: database/seed.sql
-- Chạy SAU schema.sql.
-- KHÔNG chạy lại file này trên database đang có dữ liệu thật.
-- =============================================================

USE studyhub_db;

-- -------------------------------------------------------------
-- Tài khoản mẫu
-- Password "Admin@123" và "User@123" — đã hash bằng PASSWORD_BCRYPT
-- Trong code PHP dùng: password_hash('Admin@123', PASSWORD_BCRYPT)
-- Khi đăng nhập: password_verify($input, $row['password'])
-- -------------------------------------------------------------
INSERT INTO users (full_name, email, password, role, status) VALUES
('Quản Trị Viên',  'admin@studyhub.vn', '123456789', 'admin', 'active'),
('Nguyễn Văn An',  'user1@studyhub.vn', '123456789', 'user',  'active'),
('Trần Thị Bình',  'user2@studyhub.vn', '123456789', 'user',  'active'),
('Lê Hoàng Cường', 'user3@studyhub.vn', '123456789', 'user',  'active');

-- Môn học mẫu
-- -------------------------------------------------------------
INSERT INTO subjects (name, description) VALUES
('Lập trình căn bản',      'Nhập môn lập trình, thuật toán và cấu trúc dữ liệu'),
('Cơ sở dữ liệu',          'Thiết kế và quản trị cơ sở dữ liệu quan hệ'),
('Mạng máy tính',          'Kiến trúc mạng, giao thức TCP/IP'),
('Kỹ thuật phần mềm',      'Quy trình phát triển và kiểm thử phần mềm'),
('Giải tích',              'Giải tích 1 và 2'),
('Đại số tuyến tính',      'Ma trận, không gian vectơ, ứng dụng'),
('Vật lý đại cương',       'Cơ học, nhiệt học, điện từ học'),
('Tiếng Anh chuyên ngành', 'Tiếng Anh kỹ thuật và học thuật'),
('Lập trình Web',          'HTML, CSS, JavaScript, PHP, MySQL'),
('Trí tuệ nhân tạo',       'Học máy, mạng nơ-ron, ứng dụng AI');

-- -------------------------------------------------------------
-- Từ khoá mẫu
-- -------------------------------------------------------------
INSERT INTO tags (name) VALUES
('bài tập'),
('đề thi'),
('tóm tắt lý thuyết'),
('thực hành'),
('slide bài giảng'),
('báo cáo'),
('đồ án'),
('ôn tập'),
('chương trình mẫu'),
('công thức');
