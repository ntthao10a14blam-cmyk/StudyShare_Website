<?php
/**
 * ADMINCONTROLLER.PHP — Quản trị hệ thống
 * Phụ trách: TV6
 *
 * Actions:
 *   dashboard      — Thống kê tổng quan
 *   users          — Danh sách tài khoản
 *   toggleLock     — Khóa / mở khóa tài khoản
 *   subjects       — Quản lý môn học
 *   createSubject  — Thêm môn học
 *   editSubject    — Sửa môn học
 *   deleteSubject  — Xóa môn học (kiểm tra dữ liệu liên quan)
 *   tags           — Quản lý từ khoá
 *   createTag      — Thêm tag
 *   editTag        — Sửa tag
 *   deleteTag      — Xóa tag (kiểm tra dữ liệu liên quan)
 *   pendingDocs    — Danh sách tài liệu chờ duyệt
 *   reviewDoc      — Phê duyệt / từ chối tài liệu
 *   hideDoc        — Ẩn tài liệu vi phạm
 *   reports        — Danh sách báo cáo
 *   handleReport   — Xử lý báo cáo (resolved / dismissed)
 *   hideContent    — Ẩn câu hỏi / câu trả lời / bình luận
 *
 * Lưu ý bắt buộc:
 *   - TẤT CẢ action đều gọi Auth::requireAdmin() đầu tiên
 *   - Xóa môn học/tag: kiểm tra ràng buộc FK trước (ON DELETE RESTRICT)
 *   - Admin khóa tài khoản đang đăng nhập: session của người đó sẽ bị chặn bởi Auth::requireActiveUser()
 *   - Tài liệu bị Admin ẩn (hidden): chủ sở hữu KHÔNG được submit lại
 *   - Từ chối tài liệu: bắt buộc nhập reason
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
        // TODO: TV6 — thống kê: users, tài liệu public, chờ duyệt, lượt tải, câu hỏi
        $pageTitle = 'Bảng điều khiển Admin';
        require_once BASE_PATH . '/app/modules/admin/views/dashboard.php';
    }

    public function users(): void
    {
        Auth::requireAdmin();
        // TODO: TV6 — tìm kiếm, phân trang, hiển thị trạng thái
        $pageTitle = 'Quản lý tài khoản';
        require_once BASE_PATH . '/app/modules/admin/views/users.php';
    }

    public function toggleLock(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — đổi status active ↔ locked; không được tự khóa chính mình
        redirect('?module=admin&action=users');
    }

    public function subjects(): void
    {
        Auth::requireAdmin();
        // TODO: TV6
        $pageTitle = 'Quản lý môn học';
        require_once BASE_PATH . '/app/modules/admin/views/subjects.php';
    }

    public function createSubject(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6
        redirect('?module=admin&action=subjects');
    }

    public function editSubject(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6
        redirect('?module=admin&action=subjects');
    }

    public function deleteSubject(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — QUAN TRỌNG: kiểm tra còn tài liệu/câu hỏi dùng môn học này không
        redirect('?module=admin&action=subjects');
    }

    public function tags(): void
    {
        Auth::requireAdmin();
        // TODO: TV6
        $pageTitle = 'Quản lý từ khoá';
        require_once BASE_PATH . '/app/modules/admin/views/tags.php';
    }

    public function createTag(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        redirect('?module=admin&action=tags');
    }

    public function editTag(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        redirect('?module=admin&action=tags');
    }

    public function deleteTag(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — kiểm tra FK trước khi xóa
        redirect('?module=admin&action=tags');
    }

    public function pendingDocs(): void
    {
        Auth::requireAdmin();
        // TODO: TV6 — danh sách moderation_status = 'pending'
        $pageTitle = 'Tài liệu chờ duyệt';
        require_once BASE_PATH . '/app/modules/admin/views/pending_docs.php';
    }

    public function reviewDoc(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — action: approved (set visibility=public) hoặc rejected (bắt buộc reason)
        //          Ghi vào bảng document_reviews
        redirect('?module=admin&action=pendingDocs');
    }

    public function hideDoc(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — set moderation_status='hidden', visibility='private', bắt buộc reason
        redirect('?module=admin&action=pendingDocs');
    }

    public function reports(): void
    {
        Auth::requireAdmin();
        // TODO: TV6 — danh sách báo cáo, lọc theo trạng thái
        $pageTitle = 'Quản lý báo cáo';
        require_once BASE_PATH . '/app/modules/admin/views/reports.php';
    }

    public function handleReport(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — đánh dấu resolved hoặc dismissed, ghi resolution_note
        redirect('?module=admin&action=reports');
    }

    public function hideContent(): void
    {
        Auth::requireAdmin();
        verifyCsrf();
        // TODO: TV6 — ẩn câu hỏi / câu trả lời / bình luận (set status='hidden')
        redirect($_SERVER['HTTP_REFERER'] ?? url('?module=admin&action=reports'));
    }
}
