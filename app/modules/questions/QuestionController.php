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
 *   delete         — Xóa câu hỏi (chủ sở hữu hoặc admin)
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
 *
 * Về nội dung câu hỏi / câu trả lời:
 *   Lưu dạng văn bản thuần. Khi hiển thị luôn dùng nl2br(h($text)) nên không thể
 *   chèn HTML/JS (chống XSS). Nếu sau này đổi sang editor HTML (Quill...) thì
 *   BẮT BUỘC lọc HTML an toàn trước khi lưu hoặc trước khi hiển thị.
 */

require_once BASE_PATH . '/app/modules/questions/QuestionModel.php';

class QuestionsController
{
    private QuestionModel $model;

    /** Lý do báo cáo cho người dùng chọn. */
    private const REPORT_REASONS = [
        'Spam / quảng cáo',
        'Nội dung không phù hợp',
        'Sai môn học / lạc đề',
        'Ngôn từ thiếu văn minh',
        'Lý do khác',
    ];

    private const TITLE_MIN   = 5;
    private const TITLE_MAX   = 255;
    private const CONTENT_MIN = 10;
    private const CONTENT_MAX = 20000;
    private const MAX_TAGS    = 5;

    public function __construct()
    {
        $this->model = new QuestionModel();
    }

    // ============================================================
    // DANH SÁCH + CHI TIẾT (Guest được xem)
    // ============================================================

    public function index(): void
    {
        $filters = [
            'q'          => mb_substr((string) get('q'), 0, 100),
            'subject_id' => (int) get('subject'),
            'tag_id'     => (int) get('tag'),
            'status'     => in_array(get('status'), ['answered', 'unanswered'], true) ? get('status') : '',
        ];

        $total     = $this->model->countQuestions($filters);
        $pg        = paginate($total, ITEMS_PER_PAGE);
        $questions = $this->model->listQuestions($filters, $pg['offset'], $pg['limit']);
        $subjects  = $this->model->getSubjects();
        $tags      = $this->model->getTags();

        $pageTitle = 'Hỏi đáp học tập';
        $extraCss  = ['questions.css'];
        $extraJs   = ['questions.js'];
        require_once BASE_PATH . '/app/modules/questions/views/index.php';
    }

    public function show(): void
    {
        $question = $this->model->findQuestion((int) get('id'));
        if (!$question || !$this->canView($question)) {
            $this->notFound();
        }

        $tags         = $this->model->getQuestionTags((int) $question['id']);
        $images       = $this->model->getQuestionImages((int) $question['id']);
        $answers      = $this->model->getAnswers((int) $question['id']);
        $isOwner      = Auth::check() && Auth::owns((int) $question['user_id']);
        $isAdmin      = Auth::isAdmin();
        $canAnswer    = Auth::isActive() && $question['status'] === 'visible';
        $currentUid   = Auth::id();
        $reportReasons = self::REPORT_REASONS;

        $pageTitle = $question['title'];
        $extraCss  = ['questions.css'];
        $extraJs   = ['questions.js'];
        require_once BASE_PATH . '/app/modules/questions/views/show.php';
    }

    // ============================================================
    // ĐẶT CÂU HỎI
    // ============================================================

    public function create(): void
    {
        Auth::requireActiveUser();
        $this->renderCreate([], ['title' => '', 'content' => '', 'subject_id' => 0, 'tags' => []]);
    }

    public function store(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        [$data, $errors] = $this->validateQuestionInput();

        $files = isset($_FILES['images']) ? Upload::normalizeMultiple($_FILES['images']) : [];
        if (count($files) > MAX_IMAGES_PER_Q) {
            $errors['images'] = 'Chỉ được đính kèm tối đa ' . MAX_IMAGES_PER_Q . ' ảnh.';
        }
        if ($errors) {
            $this->renderCreate($errors, $data);
            return;
        }

        $db       = Database::getInstance();
        $uploaded = [];
        $db->beginTransaction();
        try {
            $id = $this->model->createQuestion(
                (int) Auth::id(), $data['subject_id'], $data['title'], $data['content'], $data['tags']
            );
            foreach ($files as $i => $file) {
                $res = Upload::image($file, STORAGE_Q_IMAGES);
                if (!$res['success']) {
                    throw new RuntimeException($res['error']);
                }
                $uploaded[] = $res['path'];
                $this->model->addImage($id, $res['path'], $i);
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->getPdo()->inTransaction()) $db->rollBack();
            foreach ($uploaded as $path) Upload::delete($path);   // dọn file đã lỡ lưu
            $errors['images'] = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Không thể lưu câu hỏi, vui lòng thử lại.';
            $this->renderCreate($errors, $data);
            return;
        }

        redirect('?module=questions&action=show&id=' . $id, 'Đã đăng câu hỏi của bạn.', 'success');
    }

    // ============================================================
    // SỬA / XÓA CÂU HỎI
    // ============================================================

    public function edit(): void
    {
        Auth::requireActiveUser();
        $question = $this->findOwnedQuestion((int) get('id'));

        $old = [
            'title'      => $question['title'],
            'content'    => $question['content'],
            'subject_id' => (int) $question['subject_id'],
            'tags'       => array_column($this->model->getQuestionTags((int) $question['id']), 'id'),
        ];
        $this->renderEdit($question, [], $old);
    }

    public function update(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        $question = $this->findOwnedQuestion((int) post('id'));
        $qid      = (int) $question['id'];

        [$data, $errors] = $this->validateQuestionInput();

        $removeIds = isset($_POST['remove_images']) && is_array($_POST['remove_images'])
            ? array_map('intval', $_POST['remove_images']) : [];
        $files     = isset($_FILES['images']) ? Upload::normalizeMultiple($_FILES['images']) : [];

        // Tổng số ảnh sau khi sửa không được vượt giới hạn
        $current = $this->model->getQuestionImages($qid);
        $keep    = count(array_filter($current, fn($im) => !in_array((int) $im['id'], $removeIds, true)));
        if ($keep + count($files) > MAX_IMAGES_PER_Q) {
            $errors['images'] = 'Mỗi câu hỏi chỉ có tối đa ' . MAX_IMAGES_PER_Q . ' ảnh.';
        }
        if ($errors) {
            $this->renderEdit($question, $errors, $data);
            return;
        }

        $db       = Database::getInstance();
        $uploaded = [];
        $removed  = [];
        $db->beginTransaction();
        try {
            $this->model->updateQuestion($qid, $data['subject_id'], $data['title'], $data['content'], $data['tags']);
            $removed = $this->model->deleteImages($qid, $removeIds);

            $sort = $this->model->countImages($qid);
            foreach ($files as $i => $file) {
                $res = Upload::image($file, STORAGE_Q_IMAGES);
                if (!$res['success']) {
                    throw new RuntimeException($res['error']);
                }
                $uploaded[] = $res['path'];
                $this->model->addImage($qid, $res['path'], $sort + $i);
            }
            $db->commit();
        } catch (Throwable $e) {
            if ($db->getPdo()->inTransaction()) $db->rollBack();
            foreach ($uploaded as $path) Upload::delete($path);
            $errors['images'] = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'Không thể lưu thay đổi, vui lòng thử lại.';
            $this->renderEdit($question, $errors, $data);
            return;
        }

        // Chỉ xóa file thật SAU KHI DB đã commit thành công
        foreach ($removed as $path) Upload::delete($path);

        redirect('?module=questions&action=show&id=' . $qid, 'Đã cập nhật câu hỏi.', 'success');
    }

    public function delete(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $question = $this->model->findQuestion((int) post('id'));
        if (!$question) {
            redirect('?module=questions&action=index', 'Câu hỏi không tồn tại.', 'error');
        }
        if (!Auth::owns((int) $question['user_id']) && !Auth::isAdmin()) {
            redirect('?module=questions&action=show&id=' . $question['id'], 'Bạn không có quyền xóa câu hỏi này.', 'error');
        }

        // Xóa mềm: giữ nguyên accepted_answer_id, câu hỏi biến mất khỏi mọi truy vấn thường
        $this->model->softDeleteQuestion((int) $question['id']);
        redirect('?module=questions&action=index', 'Đã xóa câu hỏi.', 'success');
    }

    // ============================================================
    // CÂU TRẢ LỜI
    // ============================================================

    public function answer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid      = (int) post('question_id');
        $question = $this->model->findQuestion($qid);
        if (!$question || $question['status'] !== 'visible') {
            redirect('?module=questions&action=index', 'Câu hỏi không tồn tại hoặc không nhận trả lời.', 'error');
        }

        $content = (string) post('content');
        $error   = $this->checkContent($content, 'Câu trả lời');
        if ($error) {
            $this->backToQuestion($qid, $error, 'error');
        }

        $aid = $this->model->createAnswer($qid, (int) Auth::id(), $content);
        redirect('?module=questions&action=show&id=' . $qid . '#answer-' . $aid, 'Đã đăng câu trả lời.', 'success');
    }

    public function editAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid    = (int) post('question_id');
        $answer = $this->model->findAnswer((int) post('answer_id'));

        // Phải tồn tại, thuộc đúng câu hỏi, và do chính người dùng viết
        if (!$answer || (int) $answer['question_id'] !== $qid || !Auth::owns((int) $answer['user_id'])) {
            $this->backToQuestion($qid, 'Bạn không có quyền sửa câu trả lời này.', 'error');
        }

        $content = (string) post('content');
        $error   = $this->checkContent($content, 'Câu trả lời');
        if ($error) {
            $this->backToQuestion($qid, $error, 'error', (int) $answer['id']);
        }

        $this->model->updateAnswer((int) $answer['id'], $content);
        $this->backToQuestion($qid, 'Đã cập nhật câu trả lời.', 'success', (int) $answer['id']);
    }

    public function deleteAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid    = (int) post('question_id');
        $answer = $this->model->findAnswer((int) post('answer_id'));

        if (!$answer || (int) $answer['question_id'] !== $qid) {
            $this->backToQuestion($qid, 'Câu trả lời không tồn tại.', 'error');
        }
        if (!Auth::owns((int) $answer['user_id']) && !Auth::isAdmin()) {
            $this->backToQuestion($qid, 'Bạn không có quyền xóa câu trả lời này.', 'error');
        }

        // Nếu đang là câu trả lời được chấp nhận → model tự gỡ chấp nhận trước khi xóa
        $this->model->softDeleteAnswer((int) $answer['id'], $qid);
        $this->backToQuestion($qid, 'Đã xóa câu trả lời.', 'success');
    }

    public function acceptAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid      = (int) post('question_id');
        $aid      = (int) post('answer_id');
        $question = $this->model->findQuestion($qid);

        if (!$question) {
            redirect('?module=questions&action=index', 'Câu hỏi không tồn tại.', 'error');
        }
        // QUAN TRỌNG: chỉ chủ câu hỏi mới được chấp nhận
        if (!Auth::owns((int) $question['user_id'])) {
            $this->backToQuestion($qid, 'Chỉ người đặt câu hỏi mới được chấp nhận câu trả lời.', 'error');
        }
        // QUAN TRỌNG: câu trả lời phải thuộc đúng câu hỏi này
        if (!$this->model->answerBelongsToQuestion($aid, $qid)) {
            $this->backToQuestion($qid, 'Câu trả lời không hợp lệ.', 'error');
        }

        $this->model->acceptAnswer($qid, $aid);
        $this->backToQuestion($qid, 'Đã chấp nhận câu trả lời.', 'success', $aid);
    }

    public function unacceptAnswer(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid      = (int) post('question_id');
        $question = $this->model->findQuestion($qid);

        if (!$question) {
            redirect('?module=questions&action=index', 'Câu hỏi không tồn tại.', 'error');
        }
        if (!Auth::owns((int) $question['user_id'])) {
            $this->backToQuestion($qid, 'Chỉ người đặt câu hỏi mới được bỏ chấp nhận.', 'error');
        }

        $this->model->unacceptAnswer($qid);
        $this->backToQuestion($qid, 'Đã bỏ đánh dấu chấp nhận.', 'success');
    }

    // ============================================================
    // BÁO CÁO
    // ============================================================

    public function report(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $qid      = (int) post('question_id');
        $type     = post('target_type') === 'answer' ? 'answer' : 'question';
        $targetId = (int) post('target_id');
        $reason   = (string) post('reason');
        $details  = mb_substr((string) post('details'), 0, 1000);

        $question = $this->model->findQuestion($qid);
        if (!$question || !$this->canView($question)) {
            redirect('?module=questions&action=index', 'Câu hỏi không tồn tại.', 'error');
        }
        if (!in_array($reason, self::REPORT_REASONS, true)) {
            $this->backToQuestion($qid, 'Vui lòng chọn lý do báo cáo.', 'error');
        }

        // Xác định đối tượng bị báo cáo và chủ của nó
        if ($type === 'answer') {
            $answer = $this->model->findAnswer($targetId);
            if (!$answer || (int) $answer['question_id'] !== $qid) {
                $this->backToQuestion($qid, 'Câu trả lời không hợp lệ.', 'error');
            }
            $ownerId = (int) $answer['user_id'];
        } else {
            $targetId = $qid;
            $ownerId  = (int) $question['user_id'];
        }

        if (Auth::owns($ownerId)) {
            $this->backToQuestion($qid, 'Bạn không thể báo cáo nội dung của chính mình.', 'error');
        }
        if ($this->model->hasPendingReport((int) Auth::id(), $type, $targetId)) {
            $this->backToQuestion($qid, 'Bạn đã báo cáo nội dung này, quản trị viên sẽ xem xét sớm.', 'info');
        }

        $this->model->createReport(
            (int) Auth::id(), $type, $targetId, $reason, $details !== '' ? $details : null
        );
        $this->backToQuestion($qid, 'Đã gửi báo cáo. Cảm ơn bạn!', 'success');
    }

    // ============================================================
    // HÀM PHỤ (private)
    // ============================================================

    /** Câu hỏi hiển thị được cho người xem hiện tại? (bị ẩn → chỉ chủ và admin thấy) */
    private function canView(array $question): bool
    {
        if ($question['status'] === 'visible') return true;
        return Auth::check() && (Auth::owns((int) $question['user_id']) || Auth::isAdmin());
    }

    private function notFound(): never
    {
        http_response_code(404);
        require_once BASE_PATH . '/app/views/layouts/404.php';
        exit;
    }

    /** Lấy câu hỏi và bắt buộc người dùng hiện tại là chủ; nếu không thì chuyển hướng. */
    private function findOwnedQuestion(int $id): array
    {
        $question = $this->model->findQuestion($id);
        if (!$question) {
            $this->notFound();
        }
        if (!Auth::owns((int) $question['user_id'])) {
            redirect('?module=questions&action=show&id=' . $id, 'Chỉ người đặt câu hỏi mới được sửa.', 'error');
        }
        return $question;
    }

    private function backToQuestion(int $questionId, string $message, string $type, ?int $answerId = null): never
    {
        $anchor = $answerId ? '#answer-' . $answerId : '';
        redirect('?module=questions&action=show&id=' . $questionId . $anchor, $message, $type);
    }

    /** @return string|null thông báo lỗi, hoặc null nếu hợp lệ */
    private function checkContent(string $content, string $label): ?string
    {
        $len = mb_strlen($content);
        if ($len < 3)                   return "$label quá ngắn.";
        if ($len > self::CONTENT_MAX)   return "$label quá dài (tối đa " . self::CONTENT_MAX . " ký tự).";
        return null;
    }

    /**
     * Đọc và kiểm tra dữ liệu form câu hỏi (dùng chung cho store và update).
     * @return array{0:array,1:array} [dữ liệu đã làm sạch, mảng lỗi theo tên field]
     */
    private function validateQuestionInput(): array
    {
        $errors = [];
        $title   = (string) post('title');
        $content = (string) post('content');
        $subject = (int) post('subject_id');
        $rawTags = (isset($_POST['tags']) && is_array($_POST['tags'])) ? $_POST['tags'] : [];
        $tagIds  = $this->model->filterExistingTagIds($rawTags);

        $titleLen = mb_strlen($title);
        if ($titleLen < self::TITLE_MIN)      $errors['title'] = 'Tiêu đề phải có ít nhất ' . self::TITLE_MIN . ' ký tự.';
        elseif ($titleLen > self::TITLE_MAX)  $errors['title'] = 'Tiêu đề tối đa ' . self::TITLE_MAX . ' ký tự.';

        $contentLen = mb_strlen($content);
        if ($contentLen < self::CONTENT_MIN)      $errors['content'] = 'Nội dung phải có ít nhất ' . self::CONTENT_MIN . ' ký tự.';
        elseif ($contentLen > self::CONTENT_MAX)  $errors['content'] = 'Nội dung tối đa ' . self::CONTENT_MAX . ' ký tự.';

        if ($subject <= 0 || !$this->model->subjectExists($subject)) {
            $errors['subject_id'] = 'Vui lòng chọn môn học.';
        }
        if (count($tagIds) > self::MAX_TAGS) {
            $errors['tags'] = 'Chọn tối đa ' . self::MAX_TAGS . ' từ khóa.';
        }

        return [
            ['title' => $title, 'content' => $content, 'subject_id' => $subject, 'tags' => $tagIds],
            $errors,
        ];
    }

    private function renderCreate(array $errors, array $old): void
    {
        $subjects  = $this->model->getSubjects();
        $tags      = $this->model->getTags();
        $pageTitle = 'Đặt câu hỏi';
        $extraCss  = ['questions.css'];
        $extraJs   = ['questions.js'];
        require_once BASE_PATH . '/app/modules/questions/views/create.php';
    }

    private function renderEdit(array $question, array $errors, array $old): void
    {
        $subjects  = $this->model->getSubjects();
        $tags      = $this->model->getTags();
        $images    = $this->model->getQuestionImages((int) $question['id']);
        $pageTitle = 'Sửa câu hỏi';
        $extraCss  = ['questions.css'];
        $extraJs   = ['questions.js'];
        require_once BASE_PATH . '/app/modules/questions/views/edit.php';
    }
}
