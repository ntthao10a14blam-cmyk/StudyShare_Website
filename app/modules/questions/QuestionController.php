<?php
/**
 * QUESTIONCONTROLLER.PHP — Hỏi đáp học tập
 * Phụ trách: TV5
 *
 * Actions:
 *   index          — Danh sách câu hỏi (Guest + User)
 *   show           — Chi tiết câu hỏi và câu trả lời
 *   create         — Form tạo câu hỏi (User)
 *   store          — Lưu câu hỏi mới
 *   edit           — Sửa câu hỏi
 *   update         — Cập nhật câu hỏi
 *   delete         — Xóa câu hỏi (chủ sở hữu)
 *   answer         — Đăng câu trả lời (User)
 *   editAnswer     — Sửa câu trả lời
 *   deleteAnswer   — Xóa câu trả lời
 *   acceptAnswer   — Đánh dấu câu trả lời được chấp nhận (chủ câu hỏi)
 *   unacceptAnswer — Bỏ đánh dấu
 *   report         — Báo cáo câu hỏi hoặc câu trả lời (User)
 *
 * Lưu ý bắt buộc:
 *   - Chỉ chủ câu hỏi mới được acceptAnswer/unacceptAnswer
 *   - Câu trả lời phải thuộc câu hỏi đó (tránh giả mạo ID)
 *   - Guest xem được index và show
 *   - Xóa câu hỏi: soft delete (deleted_at), cần kiểm tra không làm hỏng accepted_answer_id
 */

require_once BASE_PATH . '/app/modules/questions/QuestionModel.php';

class QuestionController
{
    private QuestionModel $model;

    public function __construct()
    {
        $this->model = new QuestionModel();
    }

    public function index(): void
    {
        // Guest được xem
        // TODO: TV5 — tìm kiếm, lọc theo subject/tag/trạng thái answered, phân trang
        $pageTitle = 'Hỏi đáp học tập';
        require_once BASE_PATH . '/app/modules/questions/views/index.php';
    }

    public function show(): void
    {
        // Guest được xem
        // TODO: TV5 — chi tiết, danh sách câu trả lời, nút chấp nhận
        $pageTitle = 'Chi tiết câu hỏi';
        require_once BASE_PATH . '/app/modules/questions/views/show.php';
    }

    public function create(): void
    {
        Auth::requireActiveUser();
        // TODO: TV5 — form + editor + nhiều ảnh
        $pageTitle = 'Đặt câu hỏi';
        require_once BASE_PATH . '/app/modules/questions/views/create.php';
    }

    public function store(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5
        redirect('?module=questions&action=index');
    }

    public function edit(): void
    {
        Auth::requireActiveUser();
        // TODO: TV5 — kiểm tra owns
        $pageTitle = 'Sửa câu hỏi';
        require_once BASE_PATH . '/app/modules/questions/views/edit.php';
    }

    public function update(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5
        redirect('?module=questions&action=index');
    }

    public function delete(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — soft delete, kiểm tra owns
        redirect('?module=questions&action=index');
    }

    public function answer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — thêm câu trả lời
        redirect('?module=questions&action=show&id=' . post('question_id'));
    }

    public function editAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — kiểm tra owns answer
        redirect('?module=questions&action=show&id=' . post('question_id'));
    }

    public function deleteAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — soft delete, kiểm tra owns
        redirect('?module=questions&action=show&id=' . post('question_id'));
    }

    public function acceptAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — QUAN TRỌNG: kiểm tra user là chủ câu hỏi, answer thuộc câu hỏi đó
        redirect('?module=questions&action=show&id=' . post('question_id'));
    }

    public function unacceptAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — kiểm tra chủ câu hỏi
        redirect('?module=questions&action=show&id=' . post('question_id'));
    }

    public function report(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV5 — tạo report (question hoặc answer)
        redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
    }
}
