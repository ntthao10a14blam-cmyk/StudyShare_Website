
<?php

require_once BASE_PATH . '/app/modules/admin/AdminModel.php';

class AdminController
{
    private AdminModel $model;

    private const ITEMS_PER_PAGE = 20;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    // Lấy mã tài khoản quản trị đang đăng nhập
    private function getAdminId(): int
    {
        $id = $_SESSION['user_id']
            ?? $_SESSION['user']['id']
            ?? $_SESSION['user']['user_id']
            ?? 0;

        return (int) $id;
    }

    private function requireAdminId(): int
    {
        $id = $this->getAdminId();

        if ($id <= 0) {
            throw new RuntimeException(
                'Không xác định được tài khoản quản trị.'
            );
        }

        return $id;
    }

    // Chuyển đến trang quản trị
    private function redirect(string $action, array $params = []): void
    {
        $url = '?module=admin&action=' . urlencode($action);

        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode((string) $value);
        }

        header('Location: ' . $url);
        exit;
    }

    // Lưu thông báo
    private function flash(string $type, string $message): void
    {
        $_SESSION['admin_flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    // Kiểm tra phương thức gửi và mã bảo mật
    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Phương thức không được hỗ trợ.');
        }

        verifyCsrf();
    }

    // Kiểm tra tên nhập vào
    private function validName(string $name, int $maxLength = 150): bool
    {
        return $name !== ''
            && mb_strlen($name, 'UTF-8') <= $maxLength;
    }

    // Nạp giao diện Admin
    private function renderAdminView(string $view, array $data = []): void
    {
        $allowedViews = [
            'dashboard.php',
            'users.php',
            'subjects.php',
            'tags.php',
            'pending_docs.php',
            'public_docs.php',
            'reports.php'
        ];

        if (!in_array($view, $allowedViews, true)) {
            throw new InvalidArgumentException('View không hợp lệ.');
        }

        extract($data, EXTR_SKIP);

        $extraCss = ['admin.css'];

        require BASE_PATH . '/app/views/layouts/header.php';
        require BASE_PATH . '/app/modules/admin/views/' . $view;
        require BASE_PATH . '/app/views/layouts/footer.php';
    }

    // Trang tổng quan quản trị
    public function dashboard(): void
    {
        Auth::requireAdmin();

        $stats = [
            'users' => $this->model->countActiveUsers(),
            'documents' => $this->model->countPublicDocuments(),
            'pending' => $this->model->countPendingDocuments(),
            'downloads' => $this->model->countDownloads(),
            'questions' => $this->model->countQuestions(),
            'reports' => $this->model->countPendingReports()
        ];

        $latestPendingDocuments =
            $this->model->getLatestPendingDocuments(5);

        $latestPendingDocs = $latestPendingDocuments;
        $latestReports = $this->model->getLatestReports(5);

        $this->renderAdminView('dashboard.php', get_defined_vars());
    }

    // Quản lý tài khoản người dùng
    public function users(): void
    {
        Auth::requireAdmin();

        $keyword = trim($_GET['keyword'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $totalUsers = $this->model->countFilteredUsers($keyword);

        $totalPages = max(
            1,
            (int) ceil($totalUsers / self::ITEMS_PER_PAGE)
        );

        $page = min($page, $totalPages);

        $users = $this->model->getUsers(
            $keyword,
            $page,
            self::ITEMS_PER_PAGE
        );

        $currentAdminId = $this->getAdminId();
        $perPage = self::ITEMS_PER_PAGE;

        $this->renderAdminView('users.php', get_defined_vars());
    }

    // Khóa hoặc mở khóa tài khoản
    public function toggleLock(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $adminId = $this->requireAdminId();
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $this->flash('danger', 'Tài khoản không hợp lệ.');
            $this->redirect('users');
        }

        if ($userId === $adminId) {
            $this->flash(
                'danger',
                'Bạn không thể tự khóa tài khoản của mình.'
            );

            $this->redirect('users');
        }

        $user = $this->model->getUserById($userId);

        if (!$user) {
            $this->flash('danger', 'Không tìm thấy người dùng.');
            $this->redirect('users');
        }

        if ($this->model->toggleUserLock($userId)) {
            $message = $user['status'] === 'active'
                ? 'Đã khóa tài khoản người dùng.'
                : 'Đã mở khóa tài khoản người dùng.';

            $this->flash('success', $message);
        } else {
            $this->flash(
                'danger',
                'Không thể thay đổi trạng thái tài khoản.'
            );
        }

        $this->redirect('users');
    }

    // Quản lý môn học
    public function subjects(): void
    {
        Auth::requireAdmin();

        $subjects = $this->model->getSubjects();

        $this->renderAdminView('subjects.php', get_defined_vars());
    }

    public function createSubject(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$this->validName($name, 150)) {
            $this->flash(
                'danger',
                'Tên môn học không được để trống và tối đa 150 ký tự.'
            );

            $this->redirect('subjects');
        }

        if ($this->model->subjectExists($name)) {
            $this->flash('danger', 'Tên môn học này đã tồn tại!');
            $this->redirect('subjects');
        }

        try {
            $this->model->createSubject(
                $name,
                $description !== '' ? $description : null
            );

            $this->flash('success', 'Thêm môn học thành công.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Không thể thêm môn học.');
        }

        $this->redirect('subjects');
    }

    public function editSubject(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || !$this->validName($name, 150)) {
            $this->flash('danger', 'Thông tin môn học không hợp lệ.');
            $this->redirect('subjects');
        }

        if ($this->model->subjectExists($name, $id)) {
            $this->flash('danger', 'Tên môn học này đã tồn tại!');
            $this->redirect('subjects');
        }

        try {
            $this->model->updateSubject(
                $id,
                $name,
                $description !== '' ? $description : null
            );

            $this->flash('success', 'Cập nhật môn học thành công.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Không thể cập nhật môn học.');
        }

        $this->redirect('subjects');
    }

    public function deleteSubject(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->flash('danger', 'Môn học không hợp lệ.');
            $this->redirect('subjects');
        }

        $usage = $this->model->getSubjectUsage($id);

        if ($usage['documents'] > 0 || $usage['questions'] > 0) {
            $this->flash(
                'danger',
                'Không thể xóa: môn học đang có '
                . $usage['documents'] . ' tài liệu và '
                . $usage['questions'] . ' câu hỏi liên kết.'
            );

            $this->redirect('subjects');
        }

        if ($this->model->deleteSubject($id)) {
            $this->flash('success', 'Đã xóa môn học.');
        } else {
            $this->flash('danger', 'Không thể xóa môn học.');
        }

        $this->redirect('subjects');
    }

    // Quản lý thẻ
    public function tags(): void
    {
        Auth::requireAdmin();

        $tags = $this->model->getTags();

        $this->renderAdminView('tags.php', get_defined_vars());
    }

    public function createTag(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $name = trim($_POST['name'] ?? '');

        if (!$this->validName($name, 50)) {
            $this->flash(
                'danger',
                'Tên Tag không được để trống và tối đa 50 ký tự.'
            );

            $this->redirect('tags');
        }

        if ($this->model->tagExists($name)) {
            $this->flash('danger', 'Tên Tag này đã tồn tại!');
            $this->redirect('tags');
        }

        try {
            $this->model->createTag($name);
            $this->flash('success', 'Thêm Tag thành công.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Không thể thêm Tag.');
        }

        $this->redirect('tags');
    }

    public function editTag(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || !$this->validName($name, 50)) {
            $this->flash(
                'danger',
                'Tên Tag không được để trống và tối đa 50 ký tự.'
            );

            $this->redirect('tags');
        }

        if ($this->model->tagExists($name, $id)) {
            $this->flash('danger', 'Tên Tag này đã tồn tại!');
            $this->redirect('tags');
        }

        try {
            $this->model->updateTag($id, $name);
            $this->flash('success', 'Cập nhật Tag thành công.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Không thể cập nhật Tag.');
        }

        $this->redirect('tags');
    }

    public function deleteTag(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->flash('danger', 'Tag không hợp lệ.');
            $this->redirect('tags');
        }

        if (!$this->model->canDeleteTag($id)) {
            $this->flash(
                'danger',
                'Không thể xóa Tag đang được sử dụng trong tài liệu hoặc câu hỏi.'
            );

            $this->redirect('tags');
        }

        if ($this->model->deleteTag($id)) {
            $this->flash('success', 'Đã xóa Tag.');
        } else {
            $this->flash('danger', 'Không thể xóa Tag.');
        }

        $this->redirect('tags');
    }

    // Duyệt tài liệu đang chờ kiểm duyệt
    public function pendingDocs(): void
    {
        Auth::requireAdmin();

        $documents = $this->model->getPendingDocuments();

        $this->renderAdminView('pending_docs.php', get_defined_vars());
    }

    public function reviewDoc(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $documentId = (int) ($_POST['document_id'] ?? $_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if ($documentId <= 0) {
            $this->flash('danger', 'Tài liệu không hợp lệ.');
            $this->redirect('pendingDocs');
        }

        if (!in_array($action, ['approved', 'rejected'], true)) {
            $this->flash('danger', 'Thao tác duyệt không hợp lệ.');
            $this->redirect('pendingDocs');
        }

        if ($action === 'rejected' && $reason === '') {
            $this->flash(
                'danger',
                'Vui lòng nhập lý do từ chối tài liệu.'
            );

            $this->redirect('pendingDocs');
        }

        try {
            $this->model->reviewDocument(
                $documentId,
                $this->requireAdminId(),
                $action,
                $reason !== '' ? $reason : null
            );

            $this->flash(
                'success',
                $action === 'approved'
                    ? 'Đã duyệt và công khai tài liệu.'
                    : 'Đã từ chối tài liệu.'
            );
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }

        $this->redirect('pendingDocs');
    }

    // Quản lý tài liệu đã công khai
    public function publicDocs(): void
    {
        Auth::requireAdmin();

        $documents = $this->model->getPublicDocuments();

        $this->renderAdminView('public_docs.php', get_defined_vars());
    }

    public function hideDoc(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $documentId = (int) ($_POST['document_id'] ?? $_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($documentId <= 0 || $reason === '') {
            $this->flash(
                'danger',
                'Vui lòng chọn tài liệu và nhập lý do ẩn.'
            );

            $this->redirect('publicDocs');
        }

        try {
            $this->model->hidePublicDocument(
                $documentId,
                $this->requireAdminId(),
                $reason
            );

            $this->flash(
                'success',
                'Đã ẩn tài liệu khỏi hệ thống công khai.'
            );
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }

        $this->redirect('publicDocs');
    }

    // Quản lý báo cáo vi phạm
    public function reports(): void
    {
        Auth::requireAdmin();

        $status = $_GET['status'] ?? '';

        if (!in_array(
            $status,
            ['', 'pending', 'resolved', 'dismissed'],
            true
        )) {
            $status = '';
        }

        $reports = $this->model->getReports($status);

        $currentStatus = $status !== '' ? $status : 'all';

        $this->renderAdminView('reports.php', get_defined_vars());
    }

    public function handleReport(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $reportId = (int) ($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $note = trim($_POST['resolution_note'] ?? '');

        if (
            $reportId <= 0
            || !in_array($status, ['dismissed', 'resolved'], true)
        ) {
            $this->flash(
                'danger',
                'Thông tin xử lý báo cáo không hợp lệ.'
            );

            $this->redirect('reports');
        }

        if ($status === 'dismissed') {
            try {
                $ok = $this->model->handleReport(
                    $reportId,
                    $this->requireAdminId(),
                    'dismissed',
                    $note !== '' ? $note : 'Báo cáo không đủ căn cứ.'
                );

                $this->flash(
                    $ok ? 'success' : 'danger',
                    $ok
                        ? 'Đã bỏ qua báo cáo.'
                        : 'Báo cáo đã được xử lý hoặc không tồn tại.'
                );
            } catch (Throwable $e) {
                $this->flash('danger', $e->getMessage());
            }

            $this->redirect('reports');
        }

        $reason = trim($_POST['reason'] ?? $note);

        if ($reason === '') {
            $this->flash(
                'danger',
                'Vui lòng nhập lý do xử lý vi phạm.'
            );

            $this->redirect('reports');
        }

        try {
            $this->model->hideReportedContent(
                $reportId,
                $this->requireAdminId(),
                $reason
            );

            $this->flash(
                'success',
                'Đã ẩn nội dung và đóng báo cáo.'
            );
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }

        $this->redirect('reports');
    }

    // Ẩn nội dung từ báo cáo
    public function hideContent(): void
    {
        Auth::requireAdmin();
        $this->requirePost();

        $reportId = (int) ($_POST['report_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($reportId <= 0 || $reason === '') {
            $this->flash(
                'danger',
                'Thiếu mã báo cáo hoặc lý do ẩn nội dung.'
            );

            $this->redirect('reports');
        }

        try {
            $this->model->hideReportedContent(
                $reportId,
                $this->requireAdminId(),
                $reason
            );

            $this->flash('success', 'Đã ẩn nội dung vi phạm.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }

        $this->redirect('reports');
    }
}
