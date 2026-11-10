<?php
/**
 * HOMECONTROLLER.PHP — Trang chủ StudyHub
 * Phụ trách: TV4 (giao diện) + TV1 (tích hợp)
 *
 * Giao diện định dạng chuẩn Web cộng đồng học tập:
 * Basic, gọn mắt, đồng bộ màu sắc và font chữ.
 */

class HomeController
{
    public function index(): void
    {
        $pageTitle = 'Trang chủ — Cộng đồng học tập & Chia sẻ tài liệu';
        $pageDescription = 'Nền tảng chia sẻ tài liệu, bài giảng, đề thi và giải đáp thắc mắc học tập cho sinh viên.';

        $stats = [
            'documents' => 0,
            'questions' => 0,
            'users'     => 0,
        ];
        $recentDocs = [];
        $recentQuestions = [];
        $popularSubjects = [];
        $popularTags = [];

        // Thử lấy dữ liệu từ database (an toàn nếu chưa import schema)
        try {
            $db = Database::getInstance();

            // Thống kê
            $stats['documents'] = (int) $db->query("SELECT COUNT(*) FROM documents WHERE visibility = 'public' AND moderation_status = 'approved'")->fetchColumn();
            $stats['questions'] = (int) $db->query("SELECT COUNT(*) FROM questions WHERE status = 'visible'")->fetchColumn();
            $stats['users']     = (int) $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();

            // Tài liệu mới nhất
            $stmt = $db->query("
                SELECT d.id, d.title, d.file_size, d.download_count, d.created_at,
                       s.name AS subject_name, u.full_name AS author_name
                FROM documents d
                LEFT JOIN subjects s ON d.subject_id = s.id
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.visibility = 'public' AND d.moderation_status = 'approved'
                ORDER BY d.created_at DESC
                LIMIT 6
            ");
            $recentDocs = $stmt->fetchAll();

            // Câu hỏi mới nhất
            $stmt = $db->query("
                SELECT q.id, q.title, q.created_at,
                       s.name AS subject_name, u.full_name AS author_name,
                       (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) AS answer_count
                FROM questions q
                LEFT JOIN subjects s ON q.subject_id = s.id
                LEFT JOIN users u ON q.user_id = u.id
                WHERE q.status = 'visible'
                ORDER BY q.created_at DESC
                LIMIT 5
            ");
            $recentQuestions = $stmt->fetchAll();

            // Danh sách môn học
            $popularSubjects = $db->query("SELECT id, name FROM subjects ORDER BY id ASC LIMIT 8")->fetchAll();

            // Từ khóa
            $popularTags = $db->query("SELECT id, name FROM tags ORDER BY id ASC LIMIT 10")->fetchAll();
        } catch (Throwable $e) {
            // Trường hợp DB chưa khởi tạo hoặc đang trong giai đoạn 1, cung cấp dữ liệu mẫu trực quan
            $stats = ['documents' => 128, 'questions' => 45, 'users' => 320];
            $recentDocs = [
                [
                    'id' => 1,
                    'title' => 'Tổng hợp Slide & Bài tập Lập trình C++ Cơ bản đến Nâng cao',
                    'file_size' => 3450000,
                    'download_count' => 142,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'subject_name' => 'Lập trình căn bản',
                    'author_name' => 'Nguyễn Văn An'
                ],
                [
                    'id' => 2,
                    'title' => 'Đề thi trắc nghiệm & Tự luận Cơ sở dữ liệu kỳ 2 (có đáp án chi tiết)',
                    'file_size' => 1890000,
                    'download_count' => 89,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                    'subject_name' => 'Cơ sở dữ liệu',
                    'author_name' => 'Trần Thị Bình'
                ],
                [
                    'id' => 3,
                    'title' => 'Tóm tắt công thức Giải tích 2 — Chuỗi số & Tích phân bội',
                    'file_size' => 1250000,
                    'download_count' => 210,
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'subject_name' => 'Giải tích',
                    'author_name' => 'Lê Hoàng Cường'
                ],
            ];

            $recentQuestions = [
                [
                    'id' => 1,
                    'title' => 'Cách chuẩn hóa dạng 3NF và BCNF trong bảng quan hệ có nhiều khoá?',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'subject_name' => 'Cơ sở dữ liệu',
                    'author_name' => 'Nguyễn Văn An',
                    'answer_count' => 3
                ],
                [
                    'id' => 2,
                    'title' => 'Phân biệt cơ chế bắt tay 3 bước của TCP so với UDP trong truyền tải gói tin?',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                    'subject_name' => 'Mạng máy tính',
                    'author_name' => 'Lê Hoàng Cường',
                    'answer_count' => 2
                ],
            ];

            $popularSubjects = [
                ['id' => 1, 'name' => 'Lập trình căn bản'],
                ['id' => 2, 'name' => 'Cơ sở dữ liệu'],
                ['id' => 3, 'name' => 'Mạng máy tính'],
                ['id' => 4, 'name' => 'Giải tích'],
                ['id' => 5, 'name' => 'Lập trình Web'],
                ['id' => 6, 'name' => 'Kỹ thuật phần mềm'],
            ];

            $popularTags = [
                ['id' => 1, 'name' => 'đề thi'],
                ['id' => 2, 'name' => 'bài tập'],
                ['id' => 3, 'name' => 'tóm tắt lý thuyết'],
                ['id' => 4, 'name' => 'thực hành'],
                ['id' => 5, 'name' => 'ôn tập'],
                ['id' => 6, 'name' => 'slide bài giảng'],
            ];
        }

        require_once BASE_PATH . '/app/modules/home/views/index.php';
    }
}
