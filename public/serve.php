<?php

require_once __DIR__ . '/../app/bootstrap.php';

$relativePath = get('file', '');

if (empty($relativePath) || str_contains($relativePath, '..') || str_contains($relativePath, "\0")) {
    http_response_code(400);
    exit('Đường dẫn không hợp lệ.');
}

$fullPath = STORAGE_PATH . '/' . $relativePath;

if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    exit('File không tìm thấy.');
}

if (str_starts_with($relativePath, 'images/avatars/')) {

}

elseif (str_starts_with($relativePath, 'documents/')) {

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

    if ($doc['visibility'] === 'private' || $doc['moderation_status'] !== 'approved') {
        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập file này.');
        }
    }

    else {
        $previewPage = isset($_GET['preview_page']) ? (int) $_GET['preview_page'] : 0;
        $isDownload  = isset($_GET['download']) && $_GET['download'] === '1';

        if (!Auth::check()) {
            if ($isDownload || $previewPage < 1 || $previewPage > 3) {
                Auth::requireLogin();
            }

            $tempDir = STORAGE_PATH . '/temp';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $prefix = $tempDir . '/preview_doc_' . (int) $doc['id'];
            $cachedFiles = glob($prefix . '-*.png') ?: [];

            if (empty($cachedFiles)) {
                $cmd = 'pdftoppm -png -r 120 -f 1 -l 3 ' . escapeshellarg($fullPath) . ' ' . escapeshellarg($prefix) . ' 2>&1';
                @exec($cmd);
                $cachedFiles = glob($prefix . '-*.png') ?: [];
            }

            if (!empty($cachedFiles)) {
                natsort($cachedFiles);
                $cachedFiles = array_values($cachedFiles);
                $idx = $previewPage - 1;
                if (!isset($cachedFiles[$idx]) || !is_file($cachedFiles[$idx])) {
                    http_response_code(404);
                    exit('Trang xem trước không tồn tại.');
                }
                header('Content-Type: image/png');
                header('Content-Length: ' . filesize($cachedFiles[$idx]));
                header('Content-Disposition: inline; filename="preview_p' . $previewPage . '.png"');
                header('Cache-Control: public, max-age=3600');
                readfile($cachedFiles[$idx]);
                exit;
            }

            http_response_code(404);
            exit('Không thể tạo trang xem trước.');
        }
    }
}

elseif (str_starts_with($relativePath, 'images/question_images/')) {
    Auth::requireLogin();
}

elseif (str_starts_with($relativePath, 'images/document_images/')) {
    $db   = Database::getInstance();
    $stmt = $db->prepare(
        "SELECT d.user_id, d.visibility, d.moderation_status, d.deleted_at
         FROM document_images di
         JOIN documents d ON d.id = di.document_id
         WHERE di.image_path = :path
         LIMIT 1"
    );
    $stmt->execute([':path' => $relativePath]);
    $imgDoc = $stmt->fetch();

    if (!$imgDoc || $imgDoc['deleted_at'] !== null) {
        http_response_code(404);
        exit('File không tìm thấy.');
    }

    $isPublicApproved = $imgDoc['visibility'] === 'public' && $imgDoc['moderation_status'] === 'approved';
    if (!$isPublicApproved) {
        $isOwner = Auth::check() && Auth::id() === (int) $imgDoc['user_id'];
        if (!$isOwner && !Auth::isAdmin()) {
            Auth::requireLogin();
        }
    }
}
else {
    http_response_code(403);
    exit('Không được phép truy cập.');
}

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

$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($download) {

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
