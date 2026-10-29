<?php
/**
 * VIEW: Đăng ký tài khoản
 * Phụ trách: TV1
 */
$pageTitle  = $pageTitle ?? 'Đăng ký tài khoản';
$extraCss   = ['auth.css'];
$extraJs    = ['auth.js'];
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="auth-page">
    <div class="auth-card auth-card-wide">

        <!-- Logo / Brand -->
        <div class="auth-brand">
            <a href="<?= url('/') ?>" class="text-decoration-none d-flex align-items-center justify-content-center gap-2">
                <div class="auth-brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span class="auth-brand-name">StudyShare</span>
            </a>
            <h1 class="auth-title">Tạo tài khoản</h1>
            <p class="auth-subtitle">Tham gia cộng đồng học tập và chia sẻ tài liệu miễn phí.</p>
        </div>

        <!-- Form đăng ký -->
        <form method="POST" action="<?= url('?module=auth&action=register') ?>" novalidate id="registerForm">
            <?php csrfField(); ?>

            <!-- Họ và tên -->
            <div class="mb-3">
                <label for="full_name" class="form-label">
                    Họ và tên <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon">
                        <i class="bi bi-person"></i>
                    </span>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>"
                        value="<?= h($data['full_name'] ?? '') ?>"
                        placeholder="Nguyễn Văn A"
                        maxlength="100"
                        autocomplete="name"
                        required
                        autofocus
                    >
                    <?php if (!empty($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?= h($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

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
                        value="<?= h($data['email'] ?? '') ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required
                    >
                    <?php if (!empty($errors['email'])): ?>
                        <div class="invalid-feedback"><?= h($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mật khẩu -->
            <div class="mb-3">
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
                        placeholder="Ít nhất 8 ký tự"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                    <button class="btn auth-toggle-pw" type="button" id="togglePassword" tabindex="-1" aria-label="Hiện/ẩn mật khẩu">
                        <i class="bi bi-eye" id="togglePwIcon"></i>
                    </button>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="invalid-feedback"><?= h($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
                <!-- Thanh độ mạnh mật khẩu -->
                <div class="password-strength mt-1" id="pwStrengthBar" aria-hidden="true">
                    <div class="password-strength-track">
                        <div class="password-strength-fill" id="pwStrengthFill"></div>
                    </div>
                    <span class="password-strength-label" id="pwStrengthLabel"></span>
                </div>
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="mb-4">
                <label for="password_confirm" class="form-label">
                    Xác nhận mật khẩu <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        class="form-control <?= !empty($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                        placeholder="Nhập lại mật khẩu"
                        autocomplete="new-password"
                        required
                    >
                    <button class="btn auth-toggle-pw" type="button" id="toggleConfirm" tabindex="-1" aria-label="Hiện/ẩn xác nhận">
                        <i class="bi bi-eye" id="toggleConfirmIcon"></i>
                    </button>
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <div class="invalid-feedback"><?= h($errors['password_confirm']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nút đăng ký -->
            <button type="submit" class="btn btn-primary w-100 auth-btn-submit">
                <i class="bi bi-person-plus me-1"></i> Tạo tài khoản
            </button>
        </form>

        <!-- Divider -->
        <div class="auth-divider"><span>hoặc</span></div>

        <!-- Chuyển sang đăng nhập -->
        <p class="auth-switch-text">
            Đã có tài khoản?
            <a href="<?= url('?module=auth&action=login') ?>" class="fw-semibold">Đăng nhập</a>
        </p>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>

