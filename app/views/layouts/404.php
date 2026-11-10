<?php
$pageTitle = '404 — Không tìm thấy trang';
require_once __DIR__ . '/header.php';
?>

<div class="container d-flex align-items-center justify-content-center py-5" style="min-height: 60vh;">
    <div class="text-center">
        <div class="mb-3">
            <i class="bi bi-file-earmark-x display-1 text-muted"></i>
        </div>
        <h1 class="h2 fw-bold text-dark mb-2">404 — Không tìm thấy trang</h1>
        <p class="text-muted mb-4">Đường dẫn bạn yêu cầu không tồn tại hoặc đã được chuyển sang địa chỉ khác.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="<?= url('/') ?>" class="btn btn-primary">
                <i class="bi bi-house me-1"></i> Về trang chủ
            </a>
            <a href="<?= url('?module=community&action=index') ?>" class="btn btn-outline-primary">
                <i class="bi bi-files me-1"></i> Xem tài liệu
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
