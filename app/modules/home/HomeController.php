<?php
/**
 * HOMECONTROLLER.PHP — Trang chủ
 * Phụ trách: TV4 (giao diện) + TV1 (tích hợp)
 */

class HomeController
{
    public function index(): void
    {
        // TODO: hiển thị tài liệu nổi bật, câu hỏi mới, thống kê ngắn
        $pageTitle = 'Trang chủ';
        require_once BASE_PATH . '/app/modules/home/views/index.php';
    }
}
