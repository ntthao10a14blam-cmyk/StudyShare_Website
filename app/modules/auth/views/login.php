<?php
/**
 * VIEW: Đăng nhập
 * Phụ trách: TV1
 */
$pageTitle  = $pageTitle ?? 'Đăng nhập';
$extraCss   = ['auth.css'];
$extraJs    = ['auth.js'];
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="auth-page">
    <div class="auth-card">

        <!-- Logo / Brand -->
        <div class="auth-brand">
            <a href="<?= url('/') ?>" class="text-decoration-none d-flex align-items-center justify-content-center gap-2">
                <div class="auth-brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span class="auth-brand-name">StudyShare</span>
            </a>
            <h1 class="auth-title">Đăng nhập</h1>
            <p class="auth-subtitle">Chào mừng bạn trở lại! Đăng nhập để tiếp tục học tập.</p>
        </div>

        <!-- Lỗi chung -->
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                <span><?= h($errors['general']) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form đăng nhập -->
        <form method="POST" action="<?= url('?module=auth&action=login') ?>" novalidate id="loginForm">
            <?php csrfField(); ?>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= h($email ?? '') ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback"><?= h($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mật khẩu -->
            <div class="mb-4">
                <label for="password" class="form-label">
                    Mật khẩu <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                        placeholder="Nhập mật khẩu"
                        autocomplete="current-password"
                        required
                    >
                    <button class="btn auth-toggle-pw" type="button" id="togglePassword" tabindex="-1" aria-label="Hiện/ẩn mật khẩu">
                        <i class="bi bi-eye" id="togglePwIcon"></i>
                    </button>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback"><?= h($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nút đăng nhập -->
            <button type="submit" class="btn btn-primary w-100 auth-btn-submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
            </button>
        </form>

        <!-- Divider -->
        <div class="auth-divider"><span>hoặc</span></div>

        <!-- Chuyển sang đăng ký -->
        <p class="auth-switch-text">
            Chưa có tài khoản?
            <a href="<?= url('?module=auth&action=register') ?>" class="fw-semibold">Đăng ký ngay</a>
        </p>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
