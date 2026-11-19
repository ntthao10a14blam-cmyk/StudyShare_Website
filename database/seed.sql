-- =============================================================
-- STUDYHUB — DỮ LIỆU MẪU (SEED)
-- File: database/seed.sql
-- Mật khẩu chuẩn Bcrypt cho các tài khoản mẫu: 123456789
-- =============================================================

USE studyhub_db;

-- 1. Tài khoản người dùng mẫu
INSERT INTO users (id, full_name, email, password, role, status) VALUES
(1, 'Quản Trị Viên', 'admin@studyhub.vn', '$2y$10$eozmIFIVPDoknCzOU/.fduOJNdK5EB/AbfiNwF0fp7zjEzzUZ6DdC', 'admin', 'active'),
(2, 'Nguyễn Văn An', 'user1@studyhub.vn', '$2y$10$eozmIFIVPDoknCzOU/.fduOJNdK5EB/AbfiNwF0fp7zjEzzUZ6DdC', 'user', 'active'),
(3, 'Trần Thị Bình', 'user2@studyhub.vn', '$2y$10$eozmIFIVPDoknCzOU/.fduOJNdK5EB/AbfiNwF0fp7zjEzzUZ6DdC', 'user', 'active'),
(4, 'Lê Hoàng Cường', 'user3@studyhub.vn', '$2y$10$eozmIFIVPDoknCzOU/.fduOJNdK5EB/AbfiNwF0fp7zjEzzUZ6DdC', 'user', 'active')
ON DUPLICATE KEY UPDATE 
    password = VALUES(password), 
    role = VALUES(role), 
    status = VALUES(status);

-- 2. Danh mục môn học mẫu
INSERT INTO subjects (id, name, description) VALUES
(1, 'Lập trình căn bản', 'Nhập môn lập trình, thuật toán và cấu trúc dữ liệu cơ bản'),
(2, 'Cơ sở dữ liệu', 'Thiết kế, chuẩn hóa và quản trị cơ sở dữ liệu quan hệ'),
(3, 'Mạng máy tính', 'Kiến trúc mạng máy tính, giao thức TCP/IP và mô hình OSI'),
(4, 'Kỹ thuật phần mềm', 'Quy trình phát triển phần mềm, kiểm thử và thiết kế kiến trúc'),
(5, 'Giải tích', 'Giải tích 1 và 2, vi tích phân và phương trình vi phân'),
(6, 'Đại số tuyến tính', 'Ma trận, không gian vectơ, hệ phương trình và ứng dụng'),
(7, 'Vật lý đại cương', 'Cơ học, điện từ học, quang học và nhiệt động lực học'),
(8, 'Tiếng Anh chuyên ngành', 'Tiếng Anh kỹ thuật CNTT và giao tiếp học thuật'),
(9, 'Lập trình Web', 'HTML, CSS, JavaScript, PHP MVC và MySQL nâng cao'),
(10, 'Trí tuệ nhân tạo', 'Học máy, mạng nơ-ron nhân tạo và xử lý ngôn ngữ tự nhiên')
ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    description = VALUES(description);

-- 3. Từ khóa (Tags) mẫu
INSERT INTO tags (id, name) VALUES
(1, 'bài tập'),
(2, 'đề thi'),
(3, 'tóm tắt lý thuyết'),
(4, 'thực hành'),
(5, 'slide bài giảng'),
(6, 'báo cáo'),
(7, 'đồ án'),
(8, 'ôn tập'),
(9, 'chương trình mẫu'),
(10, 'công thức')
ON DUPLICATE KEY UPDATE 
    name = VALUES(name);

-- 4. Dữ liệu tài liệu (documents)
INSERT INTO documents (id, title, description, file_path, original_file_name, file_size, visibility, moderation_status, submitted_at, published_at, download_count, user_id, subject_id) VALUES
(1, 'Giáo trình Lập trình Web với PHP & MySQL', 'Tài liệu chi tiết về lập trình web backend với PHP và cơ sở dữ liệu MySQL, xây dựng ứng dụng MVC từ cơ bản đến nâng cao.', 'documents/Dean_WebProgramming_HTML5-CSS-JavaScript.pdf', 'Giao_trinh_PHP_MySQL.pdf', 19589050, 'public', 'approved', NOW(), NOW(), 125, 2, 9),
(2, 'Tổng hợp Đề thi và Đáp án Cơ sở dữ liệu', 'Bộ sưu tập đề thi cuối kỳ môn CSDL các năm có lời giải chi tiết, bài tập chuẩn hóa 1NF, 2NF, 3NF, BCNF.', 'documents/DeKTraSo2_CSDL_ChieuThuNam-10062021.pdf', 'De_thi_CSDL_dap_an.pdf', 310244, 'public', 'approved', NOW(), NOW(), 89, 2, 2),
(3, 'Slide bài giảng Mạng máy tính - Kiến trúc TCP/IP', 'Tóm tắt lý thuyết 7 tầng OSI, mô hình TCP/IP, định tuyến IP, subnetting và các bài tập chia mạng con.', 'documents/Bài giảng_Bộ giao thức TCP_IP_23_7_2013 (1).pdf', 'Slide_Mang_May_Tinh.pdf', 3529656, 'public', 'approved', NOW(), NOW(), 45, 3, 3),
(4, 'Tóm tắt công thức Giải tích 1 & Giải tích 2', 'Bảng tra cứu nhanh đạo hàm, vi phân, tích phân bất định, chuỗi số và phương trình vi phân.', 'documents/Giai_tich.pdf', 'Cong_thuc_Giai_tich.pdf', 41375116, 'public', 'approved', NOW(), NOW(), 210, 4, 5),
(5, 'Giới thiệu Hệ quản trị Cơ sở dữ liệu', 'Tài liệu tổng quan về hệ quản trị CSDL, mô hình quan hệ và thiết kế lược đồ dữ liệu.', 'documents/Introduction_to_Database_Systems.pdf', 'Introduction_to_Database_Systems.pdf', 408546, 'public', 'approved', NOW(), NOW(), 67, 2, 2),
(6, 'Tài liệu ôn tập Trí tuệ nhân tạo (AI)', 'Tổng quan về thuật toán tìm kiếm A*, Minimax, học máy cơ bản và mạng nơ-ron tích chập (CNN).', 'documents/Mang_may_tinh.pdf', 'On_tap_AI.pdf', 203190, 'public', 'approved', NOW(), NOW(), 34, 3, 10),
(7, 'Báo cáo thí nghiệm Vật lý đại cương', 'Bài thu hoạch thực hành đo gia tốc trọng trường, khảo sát mạch RLC và hiện tượng quang điện.', 'documents/sample_vat_ly.pdf', 'Thi_nghiem_Vat_ly.pdf', 1200000, 'private', 'not_submitted', NULL, NULL, 0, 2, 7),
(8, 'Bài tập Lập trình Căn bản có lời giải', 'Hơn 100 bài tập C/C++ từ cấu trúc điều khiển, mảng, con trỏ đến đệ quy và cấu trúc dữ liệu.', 'documents/sample_lap_trinh_cb.pdf', 'Bai_tap_C_Cplusplus.pdf', 1780000, 'public', 'pending', NOW(), NULL, 12, 4, 1)
ON DUPLICATE KEY UPDATE 
    title = VALUES(title), 
    file_path = VALUES(file_path),
    file_size = VALUES(file_size),
    moderation_status = VALUES(moderation_status), 
    visibility = VALUES(visibility);

-- 5. Gắn thẻ (Tags) cho tài liệu
INSERT IGNORE INTO document_tags (document_id, tag_id) VALUES
(1, 9), (1, 4), (1, 5),
(2, 2), (2, 1), (2, 8),
(3, 5), (3, 3),
(4, 10), (4, 3), (4, 8),
(5, 7), (5, 6),
(6, 8), (6, 3),
(8, 1), (8, 9);

-- 6. Bộ sưu tập mẫu (collections)
INSERT INTO collections (id, name, description, user_id) VALUES
(1, 'Ôn thi cuối kỳ học kỳ 1', 'Các tài liệu cốt lõi chuẩn bị cho kỳ thi kết thúc học phần', 2),
(2, 'Lập trình chuyên sâu', 'Tập hợp giáo trình và bài tập lập trình web & C++', 2),
(3, 'Tài liệu tham khảo AI & CSDL', 'Tổng hợp kiến thức nền tảng cho khoa học dữ liệu', 3)
ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    description = VALUES(description);

-- 7. Chi tiết tài liệu trong Bộ sưu tập (collection_documents)
INSERT IGNORE INTO collection_documents (collection_id, document_id) VALUES
(1, 2),
(1, 4),
(2, 1),
(2, 5),
(3, 2),
(3, 6);

-- 8. Danh sách Yêu thích mẫu (favorites)
INSERT IGNORE INTO favorites (user_id, document_id) VALUES
(2, 1),
(2, 2),
(2, 4),
(3, 1),
(3, 3),
(4, 2);

-- 9. Câu hỏi học tập (questions)
INSERT INTO questions (id, title, content, status, user_id, subject_id, accepted_answer_id) VALUES
(1, 'Làm thế nào để phân biệt giữa mô hình OSI và TCP/IP?', 'Em đang ôn thi môn Mạng máy tính nhưng hay bị nhầm lẫn giữa số tầng và chức năng tương ứng của hai mô hình này. Anh/chị giải thích giúp em sự khác biệt cốt lõi với ạ!', 'visible', 3, 3, NULL),
(2, 'Chuẩn hóa CSDL dạng 3NF và BCNF khác nhau ở điểm nào?', 'Mọi người cho em hỏi trường hợp nào thì một bảng đạt 3NF nhưng chưa đạt BCNF ạ? Cho em xin một ví dụ cụ thể với.', 'visible', 4, 2, NULL),
(3, 'Sự khác nhau giữa PDO và MySQLi trong PHP?', 'Em mới bắt đầu học lập trình web, thấy có 2 cách kết nối cơ sở dữ liệu là PDO và MySQLi. Dự án thực tế nên ưu tiên sử dụng cái nào hơn và vì sao?', 'visible', 2, 9, NULL)
ON DUPLICATE KEY UPDATE 
    title = VALUES(title), 
    content = VALUES(content);

-- 10. Gắn thẻ câu hỏi (question_tags)
INSERT IGNORE INTO question_tags (question_id, tag_id) VALUES
(1, 3), (1, 8),
(2, 1), (2, 8),
(3, 4), (3, 9);

-- 11. Câu trả lời (answers)
INSERT INTO answers (id, content, status, question_id, user_id) VALUES
(1, 'Mô hình OSI có 7 tầng (mang tính lý thuyết), còn TCP/IP có 4 tầng (mang tính thực tiễn và được dùng thực tế trên Internet). Điểm mấu chốt là TCP/IP gộp 3 tầng trên cùng của OSI (Application, Presentation, Session) thành 1 tầng Application duy nhất.', 'visible', 1, 2),
(2, 'Điểm khác biệt chính: BCNF đòi hỏi MỌI thuộc tính xác định (vế trái của phụ thuộc hàm X -> Y) đều PHẢI là siêu khóa (superkey). Trong khi 3NF vẫn chấp nhận ngoại lệ nếu thuộc tính bên phải (Y) là thuộc tính khóa nguyên tố.', 'visible', 2, 1),
(3, 'Bạn nên dùng PDO nhé! PDO hỗ trợ 12 hệ quản trị CSDL khác nhau (dễ chuyển từ MySQL sang PostgreSQL, SQLite...), hỗ trợ Named Parameters và cơ chế Prepared Statements rất an toàn chống SQL Injection.', 'visible', 3, 3)
ON DUPLICATE KEY UPDATE 
    content = VALUES(content);

-- 12. Bình luận & Thảo luận tài liệu mẫu (document_comments)
INSERT INTO document_comments (id, content, status, document_id, user_id) VALUES
(1, 'Giáo trình trình bày rất dễ hiểu, phần ví dụ HTML5/CSS3 và JavaScript ở các chương đầu giúp mình ôn tập nhanh trước khi làm đồ án web.', 'visible', 1, 3),
(2, 'Cảm ơn bạn đã chia sẻ tài liệu! Cho mình hỏi phần kết nối MySQL có dùng chuẩn PDO hay MySQLi vậy ạ?', 'visible', 1, 4),
(3, 'Đề thi CSDL này sát với cấu trúc đề thi cuối kỳ của trường mình lắm. Có thêm phần đáp án chuẩn hóa BCNF rất chi tiết.', 'visible', 2, 3),
(4, 'Slide giải thích rõ sự khác nhau giữa TCP và UDP, đặc biệt là bảng chia subnetting ở trang 12 rất hữu ích.', 'visible', 3, 2),
(5, 'Mình tải về để ôn thi giữa kỳ môn Mạng máy tính, cảm ơn tác giả rất nhiều!', 'visible', 3, 4),
(6, 'Bảng tổng hợp công thức đạo hàm và tích phân cực kỳ đầy đủ, in ra làm tài liệu ôn tập rất tiện.', 'visible', 4, 2),
(7, 'Tài liệu nền tảng về hệ quản trị CSDL rất chuẩn, phù hợp cho các bạn mới bắt đầu học thiết kế ERD.', 'visible', 5, 4),
(8, 'Nội dung cô đọng, dễ tra cứu khi làm bài tập lớn.', 'visible', 6, 2)
ON DUPLICATE KEY UPDATE 
    content = VALUES(content),
    status = VALUES(status);
