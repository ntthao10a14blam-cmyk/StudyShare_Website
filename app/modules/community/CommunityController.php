<?php

require_once BASE_PATH . '/app/modules/community/CommunityModel.php';

class CommunityController
{
    private const REPORT_REASONS = [
        'Spam / quảng cáo',
        'Sai chuyên môn / thông tin không chính xác',
        'Nội dung nhạy cảm hoặc không phù hợp',
        'Vi phạm bản quyền',
        'Lý do khác',
    ];

    private CommunityModel $model;

    public function __construct()
    {
        $this->model = new CommunityModel();
    }

    public function index(): void
    {

        $keyword   = trim((string) get('keyword', ''));
        $subjectId = (int) get('subject_id', 0);
        $tagId     = (int) get('tag_id', 0);
        $sort      = (string) get('sort', 'newest');

        $filters = [
            'keyword'    => $keyword,
            'subject_id' => $subjectId > 0 ? $subjectId : null,
            'tag_id'     => $tagId > 0 ? $tagId : null,
            'sort'       => $sort,
        ];

        $totalDocs = $this->model->countPublicDocuments($filters);
        $perPage   = ITEMS_PER_PAGE;
        $pagination = paginate($totalDocs, $perPage);

        $documents = $this->model->getPublicDocuments($filters, $pagination['limit'], $pagination['offset']);

        $subjects = $this->model->getAllSubjectsWithCount();
        $tags     = $this->model->getPopularTags(15);
        $stats    = $this->model->getCommunityStats();

        $currentSubject = null;
        if ($subjectId > 0) {
            foreach ($subjects as $s) {
                if ((int) $s['id'] === $subjectId) {
                    $currentSubject = $s;
                    break;
                }
            }
        }

        $pageTitle = $currentSubject ? 'Tài liệu ' . $currentSubject['name'] : 'Kho Tài Liệu Học Tập Cộng Đồng';
        $pageDescription = 'Khám phá và tải về hàng ngàn tài liệu, giáo trình, đề thi và bài giảng chia sẻ bởi cộng đồng sinh viên StudyHub.';

        require_once BASE_PATH . '/app/modules/community/views/index.php';
    }

    public function show(): void
    {
        $id = (int) get('id', 0);
        if ($id <= 0) {
            redirect('?module=community&action=index', 'Tài liệu không hợp lệ.', 'error');
        }

        $document = $this->model->getDocumentById($id);

        if (!$document) {
            redirect('?module=community&action=index', 'Không tìm thấy tài liệu yêu cầu.', 'error');
        }

        $isOwner = Auth::check() && (int) Auth::id() === (int) $document['user_id'];
        $isAdmin = Auth::isAdmin();

        if ($document['visibility'] !== 'public' || $document['moderation_status'] !== 'approved') {
            if (!$isOwner && !$isAdmin) {
                redirect('?module=community&action=index', 'Tài liệu này chưa được duyệt hoặc đang ở chế độ riêng tư.', 'warning');
            }
        }

        $images       = $this->model->getDocumentImages($id);
        $tags         = $this->model->getDocumentTags($id);
        $comments     = $this->model->getComments($id);
        $relatedDocs  = $this->model->getRelatedDocuments((int) $document['subject_id'], $id, 5);

        $userCollections = [];
        $isFavorited     = false;
        $tv4Token        = '';
        if (Auth::check()) {
            $uid = (int) Auth::id();
            $db  = Database::getInstance();

            $stmtCol = $db->prepare("SELECT id, name FROM collections WHERE user_id = :uid ORDER BY created_at DESC");
            $stmtCol->execute([':uid' => $uid]);
            $userCollections = $stmtCol->fetchAll();

            $stmtFav = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND document_id = :doc_id LIMIT 1");
            $stmtFav->execute([':uid' => $uid, ':doc_id' => $id]);
            $isFavorited = (bool) $stmtFav->fetchColumn();

            if (empty($_SESSION['tv4_token'])) {
                $_SESSION['tv4_token'] = bin2hex(random_bytes(16));
            }
            $tv4Token = $_SESSION['tv4_token'];
        }

        $pageTitle       = $document['title'];
        $pageDescription = !empty($document['description'])
            ? mb_substr(strip_tags($document['description']), 0, 160) . '...'
            : 'Tài liệu học tập ' . $document['title'];
        $reportReasons = self::REPORT_REASONS;

        require_once BASE_PATH . '/app/modules/community/views/show.php';
    }

    public function download(): void
    {
        Auth::requireLogin();

        $id = (int) get('id', 0);
        $document = $id > 0 ? $this->model->getDocumentById($id) : false;

        if (
            !$document
            || $document['visibility'] !== 'public'
            || $document['moderation_status'] !== 'approved'
        ) {
            http_response_code(404);
            exit('Không tìm thấy tài liệu.');
        }

        $relativePath = str_replace('\\', '/', (string) $document['file_path']);
        if (str_starts_with($relativePath, 'documents/')) {
            $relativePath = substr($relativePath, strlen('documents/'));
        }

        if (
            $relativePath === ''
            || str_contains($relativePath, '..')
            || str_contains($relativePath, "\0")
            || basename($relativePath) !== $relativePath
            || strtolower(pathinfo($relativePath, PATHINFO_EXTENSION)) !== 'pdf'
        ) {
            http_response_code(404);
            exit('Không tìm thấy tài liệu.');
        }

        $documentsDirectory = realpath(STORAGE_DOCUMENTS);
        $filePath = $documentsDirectory === false
            ? false
            : realpath($documentsDirectory . DIRECTORY_SEPARATOR . $relativePath);

        if (
            $documentsDirectory === false
            || $filePath === false
            || !is_file($filePath)
            || !str_starts_with($filePath, $documentsDirectory . DIRECTORY_SEPARATOR)
        ) {
            http_response_code(404);
            exit('Không tìm thấy tài liệu.');
        }

        $file = fopen($filePath, 'rb');
        if ($file === false) {
            http_response_code(404);
            exit('Không thể đọc tài liệu.');
        }

        $this->model->incrementDownloadCount($id);

        $downloadName = basename(str_replace('\\', '/', (string) $document['original_file_name']));
        $downloadName = str_replace(["\r", "\n"], '', $downloadName);
        if ($downloadName === '') {
            $downloadName = 'document-' . $id . '.pdf';
        }
        $fallbackName = preg_replace('/[^A-Za-z0-9._-]/', '_', $downloadName);
        if ($fallbackName === '') {
            $fallbackName = 'document-' . $id . '.pdf';
        }

        header('Content-Type: application/pdf');
        header(
            'Content-Disposition: attachment; filename="' . $fallbackName
            . '"; filename*=UTF-8\'\'' . rawurlencode($downloadName)
        );
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');

        fpassthru($file);
        fclose($file);
        exit;
    }

    public function comment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $documentId = (int) post('document_id', 0);
        $content    = trim((string) post('content', ''));

        if ($documentId <= 0) {
            redirect('?module=community&action=index', 'Tài liệu không hợp lệ.', 'error');
        }

        if (empty($content)) {
            redirect('?module=community&action=show&id=' . $documentId, 'Nội dung bình luận không được để trống.', 'error');
        }

        $this->model->addComment($documentId, (int) Auth::id(), $content);
        redirect('?module=community&action=show&id=' . $documentId, 'Đã gửi bình luận thành công!', 'success');
    }

    public function editComment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $commentId  = (int) post('comment_id', 0);
        $documentId = (int) post('document_id', 0);
        $content    = trim((string) post('content', ''));

        $comment = $this->model->getCommentById($commentId);
        if (!$comment) {
            redirect('?module=community&action=show&id=' . $documentId, 'Bình luận không tồn tại.', 'error');
        }

        if (!Auth::owns((int) $comment['user_id']) && !Auth::isAdmin()) {
            redirect('?module=community&action=show&id=' . $documentId, 'Bạn không có quyền sửa bình luận này.', 'error');
        }

        if (empty($content)) {
            redirect('?module=community&action=show&id=' . $documentId, 'Nội dung bình luận không được để trống.', 'error');
        }

        $this->model->updateComment($commentId, $content);
        redirect('?module=community&action=show&id=' . $documentId, 'Cập nhật bình luận thành công!', 'success');
    }

    public function deleteComment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $commentId  = (int) post('comment_id', 0);
        $documentId = (int) post('document_id', 0);

        $comment = $this->model->getCommentById($commentId);
        if (!$comment) {
            redirect('?module=community&action=show&id=' . $documentId, 'Bình luận không tồn tại.', 'error');
        }

        if (!Auth::owns((int) $comment['user_id']) && !Auth::isAdmin()) {
            redirect('?module=community&action=show&id=' . $documentId, 'Bạn không có quyền xóa bình luận này.', 'error');
        }

        $this->model->deleteComment($commentId);
        redirect('?module=community&action=show&id=' . $documentId, 'Đã xóa bình luận.', 'info');
    }

    public function report(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $targetType = (string) post('target_type', '');
        $targetId   = (int) post('target_id', 0);
        $reason     = (string) post('reason', '');
        $details    = mb_substr(trim((string) post('details', '')), 0, 1000);

        if (!in_array($targetType, ['document', 'comment'], true) || $targetId <= 0) {
            redirect('?module=community&action=index', 'Đối tượng báo cáo không hợp lệ.', 'error');
        }

        if ($targetType === 'document') {
            $document = $this->model->getDocumentById($targetId);
            if (!$document) {
                redirect('?module=community&action=index', 'Không tìm thấy tài liệu cần báo cáo.', 'error');
            }
            $redirectDocId = $targetId;
            $ownerId = (int) ($document['user_id'] ?? 0);
            $reportDocId = $targetId;
            $reportCommentId = null;
        } else {
            $comment = $this->model->getCommentById($targetId);
            if (!$comment) {
                redirect('?module=community&action=index', 'Không tìm thấy bình luận cần báo cáo.', 'error');
            }
            $redirectDocId = (int) $comment['document_id'];
            $ownerId = (int) ($comment['user_id'] ?? 0);
            $reportDocId = null;
            $reportCommentId = $targetId;
        }

        $anchor = $targetType === 'comment' ? '#comments' : '';
        $backUrl = '?module=community&action=show&id=' . $redirectDocId . $anchor;

        if ($ownerId === (int) Auth::id()) {
            redirect($backUrl, 'Bạn không thể tự báo cáo nội dung của chính mình.', 'error');
        }

        if (!in_array($reason, self::REPORT_REASONS, true)) {
            redirect($backUrl, 'Vui lòng chọn lý do báo cáo hợp lệ.', 'error');
        }

        if ($this->model->hasPendingReport((int) Auth::id(), $targetType, $targetId)) {
            redirect($backUrl, 'Bạn đã gửi báo cáo cho nội dung này và đang chờ xử lý.', 'info');
        }

        $reportData = [
            'reason'      => $reason,
            'details'     => $details,
            'document_id' => $reportDocId,
            'comment_id'  => $reportCommentId,
            'reporter_id' => (int) Auth::id(),
        ];

        $this->model->createReport($reportData);

        redirect(
            $backUrl,
            'Cảm ơn bạn đã gửi báo cáo. Ban quản trị sẽ sớm xem xét.',
            'success'
        );
    }

    public function submit(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $documentId = (int) post('document_id', 0);
        $doc = $this->model->getDocumentById($documentId);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
        }

        if (!Auth::owns((int) $doc['user_id'])) {
            redirect('?module=documents&action=index', 'Bạn không có quyền thực hiện thao tác này.', 'error');
        }

        if ($doc['moderation_status'] === 'hidden') {
            redirect('?module=documents&action=index', 'Tài liệu này đã bị quản trị viên ẩn do vi phạm, không thể gửi duyệt lại.', 'error');
        }

        $this->model->submitForReview($documentId);
        redirect('?module=documents&action=index', 'Đã gửi yêu cầu duyệt chia sẻ tài liệu thành công!', 'success');
    }

    public function revoke(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();

        $documentId = (int) post('document_id', 0);
        $doc = $this->model->getDocumentById($documentId);

        if (!$doc) {
            redirect('?module=documents&action=index', 'Tài liệu không tồn tại.', 'error');
        }

        if (!Auth::owns((int) $doc['user_id'])) {
            redirect('?module=documents&action=index', 'Bạn không có quyền thực hiện thao tác này.', 'error');
        }

        $this->model->revokeShare($documentId);
        redirect('?module=documents&action=index', 'Đã thu hồi chia sẻ tài liệu về chế độ riêng tư.', 'info');
    }
}
