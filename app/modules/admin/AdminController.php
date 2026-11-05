<?php
/**
 * ADMINCONTROLLER.PHP — Quản trị hệ thống
 * Phụ trách: TV6
 */

require_once BASE_PATH . '/app/modules/admin/AdminModel.php';

class AdminController
{
    private AdminModel $model;

    public function __construct()
    {
        $this->model = new AdminModel();
    }

    public function dashboard(): void
{
    Auth::requireAdmin();
    $stats = [
        'users' => $this->model->countUsers(),
        'documents' => $this->model->countDocuments(),
        'subjects' => $this->model->countSubjects(),
        'pending' => $this->model->countPendingDocuments(),
        'downloads' => $this->model->countDownloads(),
        'questions' => $this->model->countQuestions(),
    ];

        $pendingDocuments = $this->model->getPendingDocuments();

        $pageTitle = 'Bảng điều khiển Admin';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

    require_once BASE_PATH . '/app/modules/admin/views/dashboard.php';
}

    public function users(): void
    {
        Auth::requireAdmin();

        $users = $this->model->getUsers();

        $pageTitle = 'Quản lý tài khoản';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

        require_once BASE_PATH . '/app/modules/admin/views/users.php';
    }

    public function toggleLock(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            redirect('?module=admin&action=users');
        }

        if ($userId === (int) ($_SESSION['user_id'] ?? 0)) {
            redirect('?module=admin&action=users');
        }

        $this->model->toggleUserLock($userId);

        redirect('?module=admin&action=users');
    }

    public function subjects(): void
    {
        Auth::requireAdmin();

        $subjects = $this->model->getSubjects();

        $pageTitle = 'Quản lý môn học';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

        require_once BASE_PATH . '/app/modules/admin/views/subjects.php';
    }

    public function createSubject(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            redirect('?module=admin&action=subjects');
        }

        $this->model->createSubject(
            $name,
            $description !== '' ? $description : null
        );

        redirect('?module=admin&action=subjects');
    }

    public function editSubject(): void
    {
       Auth::requireAdmin();
        verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '') {
            redirect('?module=admin&action=subjects');
        }

        $this->model->updateSubject(
            $id,
            $name,
            $description !== '' ? $description : null
        );

        redirect('?module=admin&action=subjects');
    }

    public function deleteSubject(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            redirect('?module=admin&action=subjects');
        }

        if (!$this->model->canDeleteSubject($id)) {
            redirect('?module=admin&action=subjects');
        }

        $this->model->deleteSubject($id);

        redirect('?module=admin&action=subjects');
    }

    public function tags(): void
    {
        Auth::requireAdmin();

        $tags = $this->model->getTags();

        $pageTitle = 'Quản lý từ khoá';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

        require_once BASE_PATH . '/app/modules/admin/views/tags.php';
    }

    public function createTag(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            redirect('?module=admin&action=tags');
        }

        $this->model->createTag($name);

        redirect('?module=admin&action=tags');
    }

    public function editTag(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id <= 0 || $name === '') {
            redirect('?module=admin&action=tags');
        }

        $this->model->updateTag($id, $name);

        redirect('?module=admin&action=tags');
    }

    public function deleteTag(): void
    {
       Auth::requireAdmin();
        verifyCsrf();

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            redirect('?module=admin&action=tags');
        }

        if (!$this->model->canDeleteTag($id)) {
            redirect('?module=admin&action=tags');
        }

        $this->model->deleteTag($id);

        redirect('?module=admin&action=tags');
    }

    public function pendingDocs(): void
    {
       Auth::requireAdmin();

        $documents = $this->model->getPendingDocuments();

        $pageTitle = 'Tài liệu chờ duyệt';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

        require_once BASE_PATH . '/app/modules/admin/views/pending_docs.php';
    }

    public function reviewDoc(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $documentId = (int) ($_POST['document_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $reason = trim($_POST['reason'] ?? '');

        if ($documentId <= 0) {
            redirect('?module=admin&action=pendingDocs');
        }

        if (!in_array($action, ['approved', 'rejected'], true)) {
            redirect('?module=admin&action=pendingDocs');
        }

        if ($action === 'rejected' && $reason === '') {
            redirect('?module=admin&action=pendingDocs');
        }

        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        $this->model->reviewDocument(
            $documentId,
            $adminId,
            $action,
            $reason !== '' ? $reason : null
        );

        redirect('?module=admin&action=pendingDocs');
    }

    public function hideDoc(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $documentId = (int) ($_POST['document_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($documentId <= 0 || $reason === '') {
            redirect('?module=admin&action=pendingDocs');
        }

        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        $this->model->reviewDocument(
            $documentId,
            $adminId,
            'hidden',
            $reason
        );

        redirect('?module=admin&action=pendingDocs');
    }

    public function reports(): void
    {
       Auth::requireAdmin();

        $reports = $this->model->getReports();

        $pageTitle = 'Quản lý báo cáo';
        $extraCss = ['admin.css'];
        $extraJs = ['admin.js'];

        require_once BASE_PATH . '/app/modules/admin/views/reports.php';
    }

    public function handleReport(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $reportId = (int) ($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $resolutionNote = trim($_POST['resolution_note'] ?? '');

        if ($reportId <= 0) {
            redirect('?module=admin&action=reports');
        }

        if (!in_array($status, ['resolved', 'dismissed'], true)) {
            redirect('?module=admin&action=reports');
        }

        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        $this->model->handleReport(
            $reportId,
            $adminId,
            $status,
            $resolutionNote !== '' ? $resolutionNote : null
        );

        redirect('?module=admin&action=reports');
    }

    public function hideContent(): void
    {
        Auth::requireAdmin();
        verifyCsrf();

        $type = $_POST['type'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            redirect(
                $_SERVER['HTTP_REFERER']
                ?? url('?module=admin&action=reports')
            );
        }

        switch ($type) {
            case 'question':
                $this->model->hideQuestion($id);
                break;

            case 'answer':
                $this->model->hideAnswer($id);
                break;

            case 'comment':
                $this->model->hideComment($id);
                break;
        }

        redirect(
            $_SERVER['HTTP_REFERER']
            ?? url('?module=admin&action=reports')
        );
    }
}