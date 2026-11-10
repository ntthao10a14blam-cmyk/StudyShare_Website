<!DOCTYPE html>
<html lang="vi" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO & Title -->
    <title><?= h($pageTitle ?? 'StudyHub') ?> — StudyHub</title>
    <meta name="description" content="<?= h($pageDescription ?? 'Website cộng đồng học tập và chia sẻ tài liệu dành cho sinh viên.') ?>">

    <!-- Google Fonts: Inter đồng bộ toàn website -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS chung — Quy chuẩn font, màu sắc, layout cộng đồng -->
    <link rel="stylesheet" href="<?= url('assets/css/main.css') ?>">

    <!-- CSS riêng từng module (override từ controller nếu cần) -->
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array) $extraCss as $css): ?>
            <link rel="stylesheet" href="<?= url('assets/css/' . h($css)) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">

<?php
// ===== NAVBAR (Header đồng bộ với Footer) =====
require_once __DIR__ . '/navbar.php';
?>

<!-- Flash message (Thông báo hệ thống ngắn gọn) -->
<?php $flash = getFlash(); if (!empty($flash['message'])): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : h($flash['type']) ?> alert-dismissible fade show shadow-xs" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        <?= h($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
</div>
<?php endif; ?>

<!-- Main Content Area: Chứa nội dung trang và liên kết tới Footer -->
<main class="main-content flex-grow-1">
