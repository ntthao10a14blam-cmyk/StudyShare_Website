<?php
/**
 * DOCUMENTCONTROLLER.PHP — Quản lý tài liệu cá nhân
 * Phụ trách: TV2
 *
 * Actions:
 *   index   — Danh sách tài liệu của tôi (tìm kiếm, lọc, phân trang)
 *   create  — Form thêm tài liệu mới
 *   store   — Xử lý lưu tài liệu mới
 *   show    — Chi tiết tài liệu
 *   edit    — Form chỉnh sửa
 *   update  — Xử lý cập nhật
 *   delete  — Xóa tài liệu
 *
 * Lưu ý bắt buộc:
 *   - Mọi action đều require Auth::requireActiveUser()
 *   - Kiểm tra Auth::owns($doc['user_id']) trước khi edit/delete
 *   - Khi sửa file_path hoặc description của tài liệu APPROVED → đặt lại pending
 *   - PDF trong storage/ phục vụ qua serve.php, không link trực tiếp
 */

require_once BASE_PATH . '/app/modules/documents/DocumentModel.php';

class DocumentController
{
    private DocumentModel $model;

    public function __construct()
    {
        $this->model = new DocumentModel();
    }

    public function index(): void
    {
        Auth::requireActiveUser();
        // TODO: TV2 — danh sách tài liệu cá nhân, tìm kiếm, lọc, phân trang
        $pageTitle = 'Tài liệu của tôi';
        require_once BASE_PATH . '/app/modules/documents/views/index.php';
    }

    public function create(): void
    {
        Auth::requireActiveUser();
        // TODO: TV2 — form upload PDF + nhiều ảnh + editor mô tả
        $pageTitle = 'Thêm tài liệu';
        require_once BASE_PATH . '/app/modules/documents/views/create.php';
    }

    public function store(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV2 — validate, upload PDF, upload ảnh, lưu DB
        redirect('?module=documents&action=index');
    }

    public function show(): void
    {
        Auth::requireActiveUser();
        // TODO: TV2 — chi tiết tài liệu, kiểm tra quyền xem
        $pageTitle = 'Chi tiết tài liệu';
        require_once BASE_PATH . '/app/modules/documents/views/show.php';
    }

    public function edit(): void
    {
        Auth::requireActiveUser();
        // TODO: TV2 — form sửa, kiểm tra Auth::owns()
        $pageTitle = 'Sửa tài liệu';
        require_once BASE_PATH . '/app/modules/documents/views/edit.php';
    }

    public function update(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV2 — validate, cập nhật, nếu đổi file/description & đang approved → đặt pending
        redirect('?module=documents&action=index');
    }

    public function delete(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV2 — kiểm tra Auth::owns(), xóa mềm (deleted_at)
        redirect('?module=documents&action=index');
    }
}
