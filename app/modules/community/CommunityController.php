<?php
/**
 * COMMUNITYCONTROLLER.PHP — Tài liệu công khai, chia sẻ, bình luận
 * Phụ trách: TV3
 *
 * Actions:
 *   index   — Danh sách tài liệu công khai (Guest + User)
 *   show    — Chi tiết tài liệu công khai, bình luận
 *   submit  — Gửi yêu cầu duyệt chia sẻ (User)
 *   revoke  — Thu hồi chia sẻ (User, chủ sở hữu)
 *   comment — Thêm bình luận (User)
 *   editComment   — Sửa bình luận (User, chủ sở hữu)
 *   deleteComment — Xóa bình luận (User, chủ sở hữu)
 *   report  — Báo cáo tài liệu hoặc bình luận (User)
 *
 * Lưu ý bắt buộc:
 *   - Guest có thể xem index và show (tài liệu approved)
 *   - submit/revoke yêu cầu Auth::requireActiveUser() + Auth::owns()
 *   - Khi thu hồi: chuyển visibility='private', moderation_status='not_submitted'
 *   - Admin ẩn tài liệu (hidden): chủ sở hữu không được submit lại
 */

require_once BASE_PATH . '/app/modules/community/CommunityModel.php';

class CommunityController
{
    private CommunityModel $model;

    public function __construct()
    {
        $this->model = new CommunityModel();
    }

    public function index(): void
    {
        // Guest được xem
        // TODO: TV3 — tìm kiếm, lọc theo subject/tag, phân trang
        $pageTitle = 'Tài liệu học tập';
        require_once BASE_PATH . '/app/modules/community/views/index.php';
    }

    public function show(): void
    {
        // Guest được xem; User được bình luận và yêu thích
        // TODO: TV3 — chi tiết tài liệu approved, bình luận, nút báo cáo
        $pageTitle = 'Chi tiết tài liệu';
        require_once BASE_PATH . '/app/modules/community/views/show.php';
    }

    public function submit(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3 — kiểm tra owns(), moderation_status != 'hidden', set pending
        redirect('?module=documents&action=index');
    }

    public function revoke(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3 — kiểm tra owns(), set private + not_submitted
        redirect('?module=documents&action=index');
    }

    public function comment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3
        redirect('?module=community&action=show&id=' . post('document_id'));
    }

    public function editComment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3 — kiểm tra owns comment
        redirect('?module=community&action=show&id=' . post('document_id'));
    }

    public function deleteComment(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3 — kiểm tra owns comment
        redirect('?module=community&action=show&id=' . post('document_id'));
    }

    public function report(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV3 — tạo report (document hoặc comment)
        redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
    }
}
