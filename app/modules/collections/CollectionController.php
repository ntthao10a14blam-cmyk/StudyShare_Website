<?php
/**
 * COLLECTIONCONTROLLER.PHP — Bộ sưu tập và yêu thích
 * Phụ trách: TV4
 *
 * Actions:
 *   index       — Danh sách bộ sưu tập của tôi
 *   create      — Form tạo bộ sưu tập
 *   store       — Lưu bộ sưu tập mới
 *   show        — Chi tiết bộ sưu tập (danh sách tài liệu)
 *   edit        — Sửa tên/mô tả bộ sưu tập
 *   update      — Cập nhật
 *   delete      — Xóa bộ sưu tập
 *   addDoc      — Thêm tài liệu vào bộ sưu tập
 *   removeDoc   — Bỏ tài liệu khỏi bộ sưu tập
 *   favorites   — Danh sách tài liệu yêu thích
 *   toggleFav   — Lưu / bỏ lưu yêu thích
 *
 * Lưu ý bắt buộc:
 *   - Khi mở tài liệu trong bộ sưu tập: kiểm tra tài liệu còn approved không
 *   - Lưu bộ sưu tập không thay đổi quyền truy cập tài liệu
 *   - Tài liệu bị thu hồi / ẩn: vẫn còn trong DB collection_documents nhưng không xem được
 */

require_once BASE_PATH . '/app/modules/collections/CollectionModel.php';

class CollectionController
{
    private CollectionModel $model;

    public function __construct()
    {
        $this->model = new CollectionModel();
    }

    public function index(): void
    {
        Auth::requireActiveUser();
        // TODO: TV4
        $pageTitle = 'Bộ sưu tập của tôi';
        require_once BASE_PATH . '/app/modules/collections/views/index.php';
    }

    public function create(): void
    {
        Auth::requireActiveUser();
        // TODO: TV4
        $pageTitle = 'Tạo bộ sưu tập';
        require_once BASE_PATH . '/app/modules/collections/views/create.php';
    }

    public function store(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4
        redirect('?module=collections&action=index');
    }

    public function show(): void
    {
        Auth::requireActiveUser();
        // TODO: TV4 — kiểm tra owns collection
        $pageTitle = 'Chi tiết bộ sưu tập';
        require_once BASE_PATH . '/app/modules/collections/views/show.php';
    }

    public function edit(): void
    {
        Auth::requireActiveUser();
        // TODO: TV4
        $pageTitle = 'Sửa bộ sưu tập';
        require_once BASE_PATH . '/app/modules/collections/views/edit.php';
    }

    public function update(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4
        redirect('?module=collections&action=index');
    }

    public function delete(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4 — kiểm tra owns, xóa collection + cascade collection_documents
        redirect('?module=collections&action=index');
    }

    public function addDoc(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4 — kiểm tra tài liệu approved và user có quyền xem
        redirect($_SERVER['HTTP_REFERER'] ?? url('?module=collections&action=index'));
    }

    public function removeDoc(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4
        redirect($_SERVER['HTTP_REFERER'] ?? url('?module=collections&action=index'));
    }

    public function favorites(): void
    {
        Auth::requireActiveUser();
        // TODO: TV4 — danh sách tài liệu đã lưu yêu thích
        $pageTitle = 'Tài liệu yêu thích';
        require_once BASE_PATH . '/app/modules/collections/views/favorites.php';
    }

    public function toggleFav(): void
    {
        Auth::requireActiveUser();
        verifyCsrf();
        // TODO: TV4 — thêm hoặc xóa khỏi favorites
        redirect($_SERVER['HTTP_REFERER'] ?? url('/'));
    }
}
