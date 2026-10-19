<?php
/**
 * SERVE.PHP — Phục vụ file an toàn (có kiểm tra quyền)
 *
 * URL: /serve.php?file=documents/pdf_abc123.pdf&type=pdf
 * URL: /serve.php?file=images/document_images/img_xyz.jpg&type=image
 *
 * Quy tắc quyền truy cập:
 * - PDF của tài liệu riêng tư: chỉ chủ sở hữu và admin
 * - PDF của tài liệu công khai (approved): user đã đăng nhập
 * - Ảnh minh hoạ tài liệu: theo quyền của tài liệu
 * - Avatar: công khai
 * - Ảnh câu hỏi: user đã đăng nhập
 *
 * KHÔNG cho phép truy cập trực tiếp thư mục storage/ từ URL.
 */

require_once __DIR__ . '/../app/bootstrap.php';

$relativePath = get('file', '');

// 1. Kiểm tra path không chứa ../ (directory traversal)
if (empty($relativePath) || str_contains($relativePath, '..') || str_contains($relativePath, "\0")) {
    http_response_code(400);
    exit('Đường dẫn không hợp lệ.');
}

$fullPath = STORAGE_PATH . '/' . $relativePath;

// 2. File phải tồn tại
if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    exit('File không tìm thấy.');
}

// 3. Kiểm tra quyền truy cập theo loại file
// Avatar: ai cũng xem được
if (str_starts_with($relativePath, 'images/avatars/')) {
    // Công khai — tiếp tục phục vụ
}
// PDF tài liệu: phải kiểm tra quyền
elseif (str_starts_with($relativePath, 'documents/')) {
    // Lấy tài liệu dựa theo file_path
    $db   = Database::getInstance();
    $stmt = $db->prepare(
        "SELECT id, user_id, visibility, moderation_status, deleted_at FROM documents WHERE file_path = :path LIMIT 1"
    );
    $stmt->execute([':path' => $relativePath]);
    $doc = $stmt->fetch();

    if (!$doc || $doc['deleted_at'] !== null) {
        http_response_code(404);
        exit('File không tìm thấy.');
    }

    $isOwner = Auth::check() && Auth::id() === (int) $doc['user_id'];
    $isAdmin = Auth::isAdmin();

    // Tài liệu riêng tư hoặc chờ duyệt: chỉ chủ sở hữu và admin
    if ($doc['visibility'] === 'private' || $doc['moderation_status'] !== 'approved') {
        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập file này.');
        }
    }
    // Tài liệu công khai: phải đăng nhập
    else {
        Auth::requireLogin();
    }
}
// Ảnh câu hỏi: phải đăng nhập
elseif (str_starts_with($relativePath, 'images/question_images/')) {
    Auth::requireLogin();
}
// Ảnh tài liệu: phải đăng nhập
elseif (str_starts_with($relativePath, 'images/document_images/')) {
    Auth::requireLogin();
}
else {
    http_response_code(403);
    exit('Không được phép truy cập.');
}

// 4. Phục vụ file
$mimeTypes = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
];

$ext      = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

// Xem trước hay tải xuống?
$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($download) {
    // Cập nhật download_count (chỉ cho PDF tài liệu)
    if (str_starts_with($relativePath, 'documents/') && isset($doc)) {
        $db->prepare("UPDATE documents SET download_count = download_count + 1 WHERE id = :id")
           ->execute([':id' => $doc['id']]);
    }
    header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
} else {
    header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=3600');
readfile($fullPath);
exit;
