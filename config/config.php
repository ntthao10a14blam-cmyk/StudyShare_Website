<?php
/**
 * CẤU HÌNH CHUNG CỦA ỨNG DỤNG
 *
 * Các hằng số dùng chung toàn dự án.
 * File này được đưa lên Git — không chứa thông tin nhạy cảm.
 */

// ============================================================
// URL GỐC CỦA WEBSITE
// ============================================================
// Điều chỉnh nếu cần (ví dụ deploy lên server)
define('BASE_URL', 'http://localhost/study_website/public');
define('BASE_PATH', dirname(__DIR__));          // thư mục gốc dự án

// ============================================================
// ĐƯỜNG DẪN LƯU FILE UPLOAD
// ============================================================
define('STORAGE_PATH', BASE_PATH . '/storage');
define('STORAGE_DOCUMENTS', STORAGE_PATH . '/documents');
define('STORAGE_AVATARS',   STORAGE_PATH . '/images/avatars');
define('STORAGE_DOC_IMAGES', STORAGE_PATH . '/images/document_images');
define('STORAGE_Q_IMAGES',   STORAGE_PATH . '/images/question_images');
define('STORAGE_TEMP',       STORAGE_PATH . '/temp');

// ============================================================
// GIỚI HẠN FILE UPLOAD
// ============================================================
define('MAX_PDF_SIZE',   20 * 1024 * 1024);   // 20 MB
define('MAX_IMAGE_SIZE', 5  * 1024 * 1024);   // 5 MB
define('MAX_AVATAR_SIZE', 2 * 1024 * 1024);   // 2 MB
define('MAX_IMAGES_PER_DOC', 10);              // số ảnh tối đa 1 tài liệu
define('MAX_IMAGES_PER_Q',   5);               // số ảnh tối đa 1 câu hỏi
define('ALLOWED_PDF_TYPES', ['application/pdf']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ============================================================
// PHÂN TRANG
// ============================================================
define('ITEMS_PER_PAGE', 12);                  // số item mỗi trang mặc định
define('ADMIN_ITEMS_PER_PAGE', 20);

// ============================================================
// SESSION
// ============================================================
define('SESSION_LIFETIME', 3600 * 8);          // 8 tiếng
define('SESSION_NAME', 'studyhub_session');

// ============================================================
// TIMEZONE
// ============================================================
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============================================================
// MÔI TRƯỜNG
// ============================================================
// 'development' | 'production'
define('APP_ENV', 'development');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
