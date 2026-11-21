<?php

require_once BASE_PATH . '/app/modules/documents/DocumentModel.php';

class DocumentController
{
    private DocumentModel $model;

    public function __construct()
    {
        $this->model = new DocumentModel();
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function validatePdfUpload(array $pdfFile): ?string
    {
        if (($pdfFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return 'Lỗi trong quá trình tải file. Mã lỗi: ' . (int) ($pdfFile['error'] ?? -1);
        }

        $fileExt = strtolower(pathinfo($pdfFile['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'pdf') {
            return 'Chỉ chấp nhận file có định dạng .pdf. Đã chặn file không hợp lệ hoặc giả mạo đuôi.';
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($pdfFile['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            return 'Nội dung file không đúng định dạng PDF hợp lệ (phát hiện file giả mạo MIME type).';
        }

        return null;
    }

    public function index(): void
    {
        Auth::requireActiveUser();
        $userId = Auth::id();

        $filters = [
            'q'          => get('q', ''),
            'subject_id' => get('subject_id', ''),
            'status'     => 'all',
            'visibility' => get('visibility', 'all'),
            'sort'       => get('sort', 'newest'),
        ];

        $totalDocs  = $this->model->countByUser($userId, $filters);
        $pagination = paginate($totalDocs, 200);

        $documents = $this->model->findByUser(
            $userId,
            $filters,
            $pagination['limit'],
            $pagination['offset']
        );

        $stats = $this->model->getUserStats($userId);

        $subjects = $this->model->getAllSubjects();

        $pageTitle       = 'Tài liệu của tôi';
        $pageDescription = 'Quản lý tài liệu học tập cá nhân, theo dõi trạng thái duyệt và chia sẻ với cộng đồng.';

        require_once BASE_PATH . '/app/modules/documents/views/index.php';
    }

    public function create(): void
    {
        Auth::requireActiveUser();

        $subjects = $this->model->getAllSubjects();
        $allTags  = $this->model->getAllTags(20);

        $pageTitle       = 'Thêm tài liệu mới';
        $pageDescription = 'Tải lên tài liệu học tập, slide bài giảng hoặc đề thi định dạng PDF kèm ảnh minh hoạ.';

        require_once BASE_PATH . '/app/modules/documents/views/create.php';
    }

    public function store(): void
    {
        Auth::requireActiveUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && empty($_POST)
            && empty($_FILES)
            && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
        ) {
            redirect('?module=documents&action=create', 'Dung lượng file tải lên vượt quá giới hạn cho phép của máy chủ.', 'error');
        }

        verifyCsrf();

        $userId = Auth::id();
        $errors = [];

        $title            = post('title');
        $subjectId        = post('subject_id');
        $description      = post('description');
        $visibility       = post('visibility', 'private');
        $submitForReview  = post('submit_for_review') === '1';
        $tagsInput        = post('tags', '');

        if (empty($title)) {
            $errors['title'] = 'Vui lòng nhập tiêu đề tài liệu.';
        } elseif (mb_strlen($title, 'UTF-8') > 255) {
            $errors['title'] = 'Tiêu đề không được vượt quá 255 ký tự.';
        }

        if (empty($subjectId) || !is_numeric($subjectId)) {
            $errors['subject_id'] = 'Vui lòng chọn môn học phù hợp.';
        }

        if (!in_array($visibility, ['private', 'public'], true)) {
            $visibility = 'private';
        }

        $pdfFile = $_FILES['document_file'] ?? null;
        if (empty($pdfFile['name'])) {
            $errors['document_file'] = 'Vui lòng chọn file PDF để tải lên.';
        } else {
            $pdfError = $this->validatePdfUpload($pdfFile);
            if ($pdfError !== null) {
                $errors['document_file'] = $pdfError;
            }
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data']   = [
                'title'       => $title,
                'subject_id'  => $subjectId,
                'description' => $description,
                'visibility'  => $visibility,
                'tags'        => $tagsInput,
            ];
            redirect('?module=documents&action=create', 'Vui lòng kiểm tra lại thông tin biểu mẫu.', 'error');
            return;
        }

        $this->ensureDir(STORAGE_DOCUMENTS);
        $pdfResult = Upload::pdf($pdfFile, STORAGE_DOCUMENTS);
        if (!$pdfResult['success']) {
            $_SESSION['form_errors'] = ['document_file' => $pdfResult['error']];
            $_SESSION['form_data']   = [
                'title'       => $title,
                'subject_id'  => $subjectId,
                'description' => $description,
                'visibility'  => $visibility,
                'tags'        => $tagsInput,
            ];
            redirect('?module=documents&action=create', 'Không thể lưu file PDF.', 'error');
            return;
        }

        $moderationStatus = 'not_submitted';
        $submittedAt      = null;

        if ($visibility === 'public' && $submitForReview) {
            $moderationStatus = 'pending';
            $submittedAt      = date('Y-m-d H:i:s');
        }

        $documentId = $this->model->create([
            'title'              => $title,
            'description'        => $description,
            'file_path'          => $pdfResult['path'],
            'original_file_name' => $pdfResult['original'],
            'file_size'          => $pdfResult['size'],
            'visibility'         => $visibility,
            'moderation_status'  => $moderationStatus,
            'submitted_at'       => $submittedAt,
            'user_id'            => $userId,
            'subject_id'         => (int) $subjectId,
        ]);

        if (!empty($_FILES['images']['name'])) {
            $this->ensureDir(STORAGE_DOC_IMAGES);
            $imageFiles = Upload::normalizeMultiple($_FILES['images']);
            $sortOrder  = 0;
            foreach ($imageFiles as $imgFile) {
                if ($sortOrder >= MAX_IMAGES_PER_DOC) break;
                $imgResult = Upload::image($imgFile, STORAGE_DOC_IMAGES);
                if ($imgResult['success']) {
                    $this->model->addImage($documentId, $imgResult['path'], $sortOrder++);
                }
            }
        }

        if (!empty($tagsInput)) {
            $this->model->syncTags($documentId, $tagsInput);
        }

        $msg = ($moderationStatus === 'pending')
            ? 'Đã tải lên tài liệu và gửi yêu cầu duyệt tới ban quản trị!'
            : 'Đã thêm tài liệu thành công vào kho lưu trữ cá nhân!';

        redirect('?module=documents&action=show&id=' . $documentId, $msg, 'success');
    }

    public function show(): void
    {
        Auth::requireActiveUser();

        $id  = (int) get('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại hoặc đã bị xóa.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        $images  = $this->model->getImages($id);
        $tags    = $this->model->getTags($id);
        $reviews = $this->model->getReviews($id);

        $pageTitle       = $doc['title'];
        $pageDescription = mb_substr(strip_tags((string) $doc['description']), 0, 160, 'UTF-8');

        require_once BASE_PATH . '/app/modules/documents/views/show.php';
    }

    public function edit(): void
    {
        Auth::requireActiveUser();

        $id  = (int) get('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại hoặc đã bị xóa.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        if ($doc['moderation_status'] === 'hidden') {
            redirect(
                '?module=documents&action=show&id=' . $id,
                'Tài liệu này đã bị Quản trị viên ẩn do vi phạm quy chuẩn cộng đồng và không thể sửa đổi.',
                'error'
            );
            return;
        }

        $subjects = $this->model->getAllSubjects();
        $tags     = $this->model->getTags($id);
        $images   = $this->model->getImages($id);

        $tagString = implode(', ', array_column($tags, 'name'));

        $pageTitle       = 'Chỉnh sửa: ' . $doc['title'];
        $pageDescription = 'Cập nhật nội dung, thay thế file tài liệu hoặc bổ sung ảnh minh hoạ.';

        require_once BASE_PATH . '/app/modules/documents/views/edit.php';
    }

    public function update(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $id  = (int) post('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        if ($doc['moderation_status'] === 'hidden') {
            redirect('?module=documents&action=show&id=' . $id, 'Tài liệu đã bị ẩn không thể sửa đổi.', 'error');
            return;
        }

        $title       = post('title');
        $subjectId   = post('subject_id');
        $description = post('description');
        $visibility  = post('visibility', 'private');
        $tagsInput   = post('tags', '');

        $errors = [];
        if (empty($title)) {
            $errors['title'] = 'Vui lòng nhập tiêu đề tài liệu.';
        }
        if (empty($subjectId) || !is_numeric($subjectId)) {
            $errors['subject_id'] = 'Vui lòng chọn môn học.';
        }
        if (!in_array($visibility, ['private', 'public'], true)) {
            $visibility = 'private';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['form_data']   = [
                'title'       => $title,
                'subject_id'  => $subjectId,
                'description' => $description,
                'visibility'  => $visibility,
                'tags'        => $tagsInput,
            ];
            redirect('?module=documents&action=edit&id=' . $id, 'Dữ liệu không hợp lệ.', 'error');
            return;
        }

        $updateData = [
            'title'       => $title,
            'subject_id'  => (int) $subjectId,
            'description' => $description,
            'visibility'  => $visibility,
        ];

        $fileChanged = false;
        $newPdfFile  = null;

        if (!empty($_FILES['document_file']['name'])) {
            $newPdfFile = $_FILES['document_file'];
            $pdfError   = $this->validatePdfUpload($newPdfFile);
            if ($pdfError !== null) {
                $_SESSION['form_errors'] = ['document_file' => $pdfError];
                $_SESSION['form_data']   = [
                    'title'       => $title,
                    'subject_id'  => $subjectId,
                    'description' => $description,
                    'visibility'  => $visibility,
                    'tags'        => $tagsInput,
                ];
                redirect('?module=documents&action=edit&id=' . $id, $pdfError, 'error');
                return;
            }
        }

        if ($newPdfFile !== null) {
            $this->ensureDir(STORAGE_DOCUMENTS);
            $pdfResult = Upload::pdf($newPdfFile, STORAGE_DOCUMENTS);
            if (!$pdfResult['success']) {
                redirect('?module=documents&action=edit&id=' . $id, $pdfResult['error'], 'error');
                return;
            }

            Upload::delete($doc['file_path']);

            $updateData['file_path']          = $pdfResult['path'];
            $updateData['original_file_name'] = $pdfResult['original'];
            $updateData['file_size']          = $pdfResult['size'];
            $fileChanged                      = true;
        }

        $oldDesc = trim(str_replace(["\r\n", "\r"], "\n", (string) $doc['description']));
        $newDesc = trim(str_replace(["\r\n", "\r"], "\n", (string) $description));
        $descriptionChanged = ($oldDesc !== $newDesc);

        $statusNotice = '';
        if ($visibility === 'private') {
            if ($doc['moderation_status'] !== 'not_submitted') {
                $updateData['moderation_status'] = 'not_submitted';
                $updateData['submitted_at']      = null;
            }
        } elseif ($doc['moderation_status'] === 'approved' && ($fileChanged || $descriptionChanged)) {
            $updateData['moderation_status'] = 'pending';
            $updateData['submitted_at']      = date('Y-m-d H:i:s');
            $changedReason = ($fileChanged && $descriptionChanged)
                ? 'file PDF mới và nội dung mô tả'
                : ($fileChanged ? 'file PDF mới' : 'nội dung mô tả');
            $statusNotice = ' Tài liệu đã được tự động đưa về trạng thái "Chờ duyệt" (Pending) do bạn đã thay đổi '
                . $changedReason . '. Tài liệu cần Ban quản trị duyệt lại trước khi tiếp tục hiển thị công khai.';
        } elseif ($doc['moderation_status'] === 'not_submitted' && $visibility === 'public') {
            $updateData['moderation_status'] = 'pending';
            $updateData['submitted_at']      = date('Y-m-d H:i:s');
            $statusNotice = ' Tài liệu đã được gửi yêu cầu duyệt vì bạn chuyển sang chế độ công khai. Tài liệu sẽ hiển thị công khai sau khi Ban quản trị phê duyệt.';
        }

        $this->model->update($id, $updateData);

        $deleteImageIds = $_POST['delete_images'] ?? [];
        if (is_array($deleteImageIds)) {
            foreach ($deleteImageIds as $imgId) {
                $this->model->deleteImage((int) $imgId, $id);
            }
        }

        if (!empty($_FILES['images']['name'])) {
            $this->ensureDir(STORAGE_DOC_IMAGES);
            $currentCount = $this->model->countImages($id);
            $newImages    = Upload::normalizeMultiple($_FILES['images']);
            $sortOrder    = $currentCount;

            foreach ($newImages as $imgFile) {
                if ($sortOrder >= MAX_IMAGES_PER_DOC) break;
                $imgResult = Upload::image($imgFile, STORAGE_DOC_IMAGES);
                if ($imgResult['success']) {
                    $this->model->addImage($id, $imgResult['path'], $sortOrder++);
                }
            }
        }

        $this->model->syncTags($id, $tagsInput);

        $flashType = !empty($statusNotice) ? 'warning' : 'success';
        redirect('?module=documents&action=show&id=' . $id, 'Đã cập nhật thông tin tài liệu thành công!' . $statusNotice, $flashType);
    }

    public function delete(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $id  = (int) post('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        $this->model->softDelete($id);
        redirect('?module=documents&action=index', 'Tài liệu đã được xóa vào thùng rác thành công.', 'success');
    }

    public function submit(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $id  = (int) post('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        if ($doc['moderation_status'] === 'hidden') {
            redirect('?module=documents&action=show&id=' . $id, 'Tài liệu này đã bị quản trị viên khóa do vi phạm.', 'error');
            return;
        }

        $this->model->submitForReview($id);

        $redirectTo = post('redirect_to');
        $target = !empty($redirectTo) ? $redirectTo : ('?module=documents&action=show&id=' . $id);
        redirect($target, 'Đã gửi tài liệu yêu cầu ban quản trị phê duyệt!', 'success');
    }

    public function revoke(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $id  = (int) post('id', 0);
        $doc = $this->model->findById($id);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        $this->model->revokeShare($id);

        $redirectTo = post('redirect_to');
        $target = !empty($redirectTo) ? $redirectTo : ('?module=documents&action=show&id=' . $id);
        redirect($target, 'Đã thu hồi tài liệu về chế độ riêng tư.', 'info');
    }

    public function deleteImage(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $documentId = (int) post('document_id', 0);
        $imageId    = (int) post('image_id', 0);

        $doc = $this->model->findById($documentId);
        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
            return;
        }

        Auth::requireOwnerOrAdmin((int) $doc['user_id']);

        $this->model->deleteImage($imageId, $documentId);
        redirect('?module=documents&action=edit&id=' . $documentId, 'Đã xoá ảnh minh hoạ.', 'success');
    }
}

if (!class_exists('DocumentsController')) {
    class_alias('DocumentController', 'DocumentsController');
}
