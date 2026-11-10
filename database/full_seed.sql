-- =============================================================
-- STUDYHUB — DỮ LIỆU MẪU ĐẦY ĐỦ CHO TẤT CẢ MODULE
-- =============================================================

USE studyhub_db;

-- 2. Dữ liệu tài liệu (documents)
INSERT INTO documents (id, title, description, file_path, original_file_name, file_size, visibility, moderation_status, submitted_at, published_at, download_count, user_id, subject_id) VALUES
(1, 'Giáo trình Lập trình Web với PHP & MySQL', 'Tài liệu chi tiết về lập trình web backend với PHP và cơ sở dữ liệu MySQL, xây dựng ứng dụng MVC từ cơ bản đến nâng cao.', 'documents/Dean_WebProgramming_HTML5-CSS-JavaScript.pdf', 'Giao_trinh_PHP_MySQL.pdf', 2458000, 'public', 'approved', NOW(), NOW(), 125, 2, 9),
(2, 'Đề thi và Đáp án Cơ sở dữ liệu', 'Đề thi cuối kỳ môn CSDL năm 2021 có lời giải chi tiết.', 'documents/DeKTraSo2_CSDL_ChieuThuNam-10062021.pdf', 'De_thi_CSDL_dap_an.pdf', 1580000, 'public', 'approved', NOW(), NOW(), 89, 2, 2),
(3, 'Tài liệu Mạng máy tính - Kiến trúc TCP/IP', 'lý thuyết mô hình TCP/IP, định tuyến IP, subnetting và các bài tập chia mạng con.', 'documents/Bài giảng_Bộ giao thức TCP_IP_23_7_2013 (1).pdf', 'Slide_Mang_May_Tinh.pdf', 3200000, 'public', 'approved', NOW(), NOW(), 45, 3, 3),
(4, 'Giải tích 1 & Giải tích 2', 'Bảng tra cứu nhanh đạo hàm, vi phân, tích phân bất định, chuỗi số và phương trình vi phân.', 'documents/Giai_tich.pdf', 'Cong_thuc_Giai_tich.pdf', 980000, 'public', 'approved', NOW(), NOW(), 210, 4, 5),
(5, 'Giới thiệu Cơ sở dữ liệu', 'Giới thiệu về môn Cơ sở dữ liệu.', 'documents/Introduction_to_Database_Systems.pdf', 'Do_an_KTPM.pdf', 4120000, 'public', 'approved', NOW(), NOW(), 67, 2, 4),
(6, 'Tài liệu ôn tập Trí tuệ nhân tạo (AI)', 'Tổng quan về thuật toán tìm kiếm A*, Minimax, học máy cơ bản và mạng nơ-ron tích chập (CNN).', 'documents/sample_ai.pdf', 'On_tap_AI.pdf', 2850000, 'public', 'approved', NOW(), NOW(), 34, 3, 10),
(7, 'Báo cáo thí nghiệm Vật lý đại cương', 'Bài thu hoạch thực hành đo gia tốc trọng trường, khảo sát mạch RLC và hiện tượng quang điện.', 'documents/sample_vat_ly.pdf', 'Thi_nghiem_Vat_ly.pdf', 1200000, 'private', 'not_submitted', NULL, NULL, 0, 2, 7),
(8, 'Bài tập Lập trình Căn bản có lời giải', 'Hơn 100 bài tập C/C++ từ cấu trúc điều khiển, mảng, con trỏ đến đệ quy và cấu trúc dữ liệu.', 'documents/sample_lap_trinh_cb.pdf', 'Bai_tap_C_Cplusplus.pdf', 1780000, 'public', 'pending', NOW(), NULL, 12, 4, 1)
ON DUPLICATE KEY UPDATE title = VALUES(title), moderation_status = VALUES(moderation_status), visibility = VALUES(visibility);

-- 3. Gắn thẻ (Tags) cho tài liệu
INSERT IGNORE INTO document_tags (document_id, tag_id) VALUES
(1, 9), (1, 4), (1, 5),
(2, 2), (2, 1), (2, 8),
(3, 5), (3, 3),
(4, 10), (4, 3), (4, 8),
(5, 7), (5, 6),
(6, 8), (6, 3),
(8, 1), (8, 9);

-- 4. Bộ sưu tập mẫu (collections)
INSERT INTO collections (id, name, description, user_id) VALUES
(1, 'Ôn thi cuối kỳ học kỳ 1', 'Các tài liệu cốt lõi chuẩn bị cho kỳ thi kết thúc học phần', 2),
(2, 'Lập trình chuyên sâu', 'Tập hợp giáo trình và bài tập lập trình web & C++', 2),
(3, 'Tài liệu tham khảo AI & CSDL', 'Tổng hợp kiến thức nền tảng cho khoa học dữ liệu', 3)
ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description);

-- 5. Chi tiết tài liệu trong Bộ sưu tập (collection_documents)
INSERT IGNORE INTO collection_documents (collection_id, document_id) VALUES
(1, 2),
(1, 4),
(2, 1),
(2, 5),
(3, 2),
(3, 6);

-- 6. Danh sách Yêu thích mẫu (favorites)
INSERT IGNORE INTO favorites (user_id, document_id) VALUES
(2, 1),
(2, 2),
(2, 4),
(3, 1),
(3, 3),
(4, 2);

-- 7. Câu hỏi học tập (questions)
INSERT INTO questions (id, title, content, status, user_id, subject_id, accepted_answer_id) VALUES
(1, 'Làm thế nào để phân biệt giữa mô hình OSI và TCP/IP?', 'Em đang ôn thi môn Mạng máy tính nhưng hay bị nhầm lẫn giữa số tầng và chức năng tương ứng của hai mô hình này. Anh/chị giải thích giúp em sự khác biệt cốt lõi với ạ!', 'visible', 3, 3, NULL),
(2, 'Chuẩn hóa CSDL dạng 3NF và BCNF khác nhau ở điểm nào?', 'Mọi người cho em hỏi trường hợp nào thì một bảng đạt 3NF nhưng chưa đạt BCNF ạ? Cho em xin một ví dụ cụ thể với.', 'visible', 4, 2, NULL),
(3, 'Sự khác nhau giữa PDO và MySQLi trong PHP?', 'Em mới bắt đầu học lập trình web, thấy có 2 cách kết nối cơ sở dữ liệu là PDO và MySQLi. Dự án thực tế nên ưu tiên sử dụng cái nào hơn và vì sao?', 'visible', 2, 9, NULL)
ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content);

-- 8. Gắn thẻ câu hỏi (question_tags)
INSERT IGNORE INTO question_tags (question_id, tag_id) VALUES
(1, 3), (1, 8),
(2, 1), (2, 8),
(3, 4), (3, 9);

-- 9. Câu trả lời (answers)
INSERT INTO answers (id, content, status, question_id, user_id) VALUES
(1, 'Mô hình OSI có 7 tầng (mang tính lý thuyết), còn TCP/IP có 4 tầng (mang tính thực tiễn và được dùng thực tế trên Internet). Điểm mấu chốt là TCP/IP gộp 3 tầng trên cùng của OSI (Application, Presentation, Session) thành 1 tầng Application duy nhất.', 'visible', 1, 2),
(2, 'Điểm khác biệt chính: BCNF đòi hỏi MỌI thuộc tính xác định (vế trái của phụ thuộc hàm X -> Y) đều PHẢI là siêu khóa (superkey). Trong khi 3NF vẫn chấp nhận ngoại lệ nếu thuộc tính bên phải (Y) là thuộc tính khóa nguyên tố.', 'visible', 2, 1),
(3, 'Bạn nên dùng PDO nhé! PDO hỗ trợ 12 hệ quản trị CSDL khác nhau (dễ chuyển từ MySQL sang PostgreSQL, SQLite...), hỗ trợ Named Parameters và cơ chế Prepared Statements rất an toàn chống SQL Injection.', 'visible', 3, 3)
ON DUPLICATE KEY UPDATE content = VALUES(content);
