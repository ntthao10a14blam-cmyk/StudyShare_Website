<!DOCTYPE html>
<html lang="vi" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($pageTitle ?? 'StudyHub') ?> — StudyHub</title>
    <meta name="description" content="<?= h($pageDescription ?? 'Website cộng đồng học tập và chia sẻ tài liệu dành cho sinh viên.') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= url('assets/css/main.css') ?>">

    <?php
        $cssList = !empty($extraCss) ? (array) $extraCss : [];
        if (Auth::isAdmin() && !in_array('admin.css', $cssList, true)) {
            $cssList[] = 'admin.css';
        }
    ?>
    <?php foreach ($cssList as $css): ?>
        <?php $cssVer = @filemtime(BASE_PATH . '/public/assets/css/' . $css) ?: '1'; ?>
        <link rel="stylesheet" href="<?= url('assets/css/' . h($css)) ?>?v=<?= $cssVer ?>">
    <?php endforeach; ?>
</head>
<body class="d-flex flex-column min-vh-100">

<?php

require_once __DIR__ . '/navbar.php';
?>

<?php $flash = getFlash(); if (!empty($flash['message'])): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : h($flash['type']) ?> alert-dismissible fade show shadow-xs" role="alert">
        <i class="bi bi-info-circle me-1"></i>
        <?= h($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
    </div>
</div>
<?php endif; ?>

<main class="main-content flex-grow-1">
