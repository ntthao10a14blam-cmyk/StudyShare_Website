<?php
/**
 * VIEW: Đổi mật khẩu
 * Phụ trách: TV1
 */
$pageTitle  = $pageTitle ?? 'Đổi mật khẩu';
$extraCss   = ['auth.css'];
$extraJs    = ['auth.js'];
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <!-- Tiêu đề -->
            <div class="d-flex align-items-center gap-2 mb-4">
                <a href="<?= url('?module=auth&action=profile') ?>" class="btn btn-light-subtle btn-sm" aria-label="Quay lại hồ sơ">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1 class="page-title mb-0">Đổi mật khẩu</h1>
            </div>

            <div class="card community-card p-4">
                <form method="POST" action="<?= url('?module=auth&action=password') ?>" novalidate id="passwordForm">
                    <?php csrfField(); ?>

                    <!-- Mật khẩu hiện tại -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-medium">
                            Mật khẩu hiện tại <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text auth-input-icon">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control <?= !empty($errors['current_password']) ? 'is-invalid' : '' ?>"
                                placeholder="Nhập mật khẩu đang dùng"
                                autocomplete="current-password"
                                required
                                autofocus
                            >
                            <button class="btn auth-toggle-pw" type="button" data-target="current_password" tabindex="-1" aria-label="Hiện/ẩn">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (!empty($errors['current_password'])): ?>
                                <div class="invalid-feedback"><?= h($errors['current_password']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Mật khẩu mới -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-medium">
                            Mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text auth-input-icon">
                                <i class="bi bi-key"></i>
                            </span>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control <?= !empty($errors['new_password']) ? 'is-invalid' : '' ?>"
                                placeholder="Ít nhất 8 ký tự"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >
                            <button class="btn auth-toggle-pw" type="button" data-target="new_password" tabindex="-1" aria-label="Hiện/ẩn">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (!empty($errors['new_password'])): ?>
                                <div class="invalid-feedback"><?= h($errors['new_password']) ?></div>
                            <?php endif; ?>
                        </div>
                        <!-- Thanh độ mạnh -->
                        <div class="password-strength mt-1" id="pwStrengthBar" aria-hidden="true">
                            <div class="password-strength-track">
                                <div class="password-strength-fill" id="pwStrengthFill"></div>
                            </div>
                            <span class="password-strength-label" id="pwStrengthLabel"></span>
                        </div>
                    </div>

                    <!-- Xác nhận mật khẩu mới -->
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-medium">
                            Xác nhận mật khẩu mới <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text auth-input-icon">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                placeholder="Nhập lại mật khẩu mới"
                                autocomplete="new-password"
                                required
                            >
                            <button class="btn auth-toggle-pw" type="button" data-target="confirm_password" tabindex="-1" aria-label="Hiện/ẩn">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if (!empty($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= h($errors['confirm_password']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Nút hành động -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Lưu mật khẩu mới
                        </button>
                        <a href="<?= url('?module=auth&action=profile') ?>" class="btn btn-light-subtle btn-sm">
                            Hủy
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>

