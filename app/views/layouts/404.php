<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Không tìm thấy trang | StudyHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('assets/css/main.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100">
<?php require_once __DIR__ . '/navbar.php'; ?>
<div class="container flex-grow-1 d-flex align-items-center justify-content-center py-5">
    <div class="text-center">
        <i class="bi bi-file-earmark-x display-1 text-muted"></i>
        <h1 class="mt-3">404</h1>
        <p class="text-muted fs-5">Trang bạn tìm không tồn tại.</p>
        <a href="<?= url('/') ?>" class="btn btn-primary mt-2">
            <i class="bi bi-house me-1"></i> Về trang chủ
        </a>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
