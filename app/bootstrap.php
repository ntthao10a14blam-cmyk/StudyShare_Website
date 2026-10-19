<?php
/**
 * BOOTSTRAP.PHP — Khởi tạo ứng dụng
 *
 * File này được require một lần duy nhất từ public/index.php.
 * Thứ tự load rất quan trọng — không thay đổi tùy ý.
 */

// 1. Cấu hình chung (hằng số, timezone, error reporting)
require_once __DIR__ . '/../config/config.php';

// 2. Các lớp core dùng chung
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Upload.php';
require_once __DIR__ . '/core/Helpers.php';

// 3. Bắt đầu session
Auth::startSession();

// 4. Nếu cần: autoload thêm các lớp khác ở đây
