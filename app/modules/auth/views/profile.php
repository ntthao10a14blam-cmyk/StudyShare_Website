<?php
/**
 * VIEW: Hồ sơ cá nhân
 * Phụ trách: TV1
 */
$pageTitle  = $pageTitle ?? 'Hồ sơ cá nhân';
$extraCss   = ['auth.css'];
$extraJs    = ['auth.js'];
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">

    <!-- Tiêu đề trang -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">
            <i class="bi bi-person-circle me-2" style="color: var(--color-primary);"></i>
            Hồ sơ cá nhân
        </h1>
        <a href="<?= url('?module=auth&action=password') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-key me-1"></i> Đổi mật khẩu
        </a>
    </div>

    <div class="row g-4">

        <!-- ===== CỘT TRÁI: Thẻ thông tin / Avatar ===== -->
        <div class="col-lg-4">
            <div class="card community-card p-4 text-center">

                <!-- Avatar -->
                <div class="profile-avatar-wrap mx-auto mb-3" id="avatarPreviewWrap">
                    <?php if (!empty($user['avatar_path'])): ?>
                        <img
                            src="<?= storageUrl($user['avatar_path']) ?>"
                            alt="Ảnh đại diện"
                            class="profile-avatar"
                            id="avatarPreview"
                        >
                    <?php else: ?>
                        <div class="profile-avatar-placeholder" id="avatarPlaceholder">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <img src="" alt="" class="profile-avatar d-none" id="avatarPreview">
                    <?php endif; ?>
                </div>

                <!-- Tên & vai trò -->
                <h2 class="fs-5 fw-bold mb-1" style="color: var(--color-text);">
                    <?= h($user['full_name']) ?>
                </h2>
                <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?> mb-2">
                    <?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Thành viên' ?>
                </span>

                <!-- Trạng thái -->
                <?php if ($user['status'] === 'active'): ?>
                    <div class="d-flex align-items-center justify-content-center gap-1 small" style="color: var(--color-success);">
                        <i class="bi bi-check-circle-fill"></i> Tài khoản hoạt động
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center gap-1 small text-danger">
                        <i class="bi bi-x-circle-fill"></i> Tài khoản bị khóa
                    </div>
                <?php endif; ?>

                <hr class="my-3">

                <!-- Thông tin meta -->
                <ul class="list-unstyled text-start small d-flex flex-column gap-2 mb-0" style="color: var(--color-text-muted);">
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope flex-shrink-0" style="color: var(--color-primary);"></i>
                        <span class="text-break"><?= h($user['email']) ?></span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 flex-shrink-0" style="color: var(--color-primary);"></i>
                        <span>Tham gia: <?= formatDateTime($user['created_at'], 'd/m/Y') ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ===== CỘT PHẢI: Form chỉnh sửa hồ sơ ===== -->
        <div class="col-lg-8">
            <div class="card community-card p-4">
                <h2 class="fs-6 fw-bold mb-4" style="color: var(--color-text);">
                    <i class="bi bi-pencil-square me-1" style="color: var(--color-primary);"></i>
                    Cập nhật thông tin
                </h2>

                <form
                    method="POST"
                    action="<?= url('?module=auth&action=profile') ?>"
                    enctype="multipart/form-data"
                    novalidate
                    id="profileForm"
                >
                    <?php csrfField(); ?>

                    <!-- Họ và tên -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-medium">
                            Họ và tên <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control <?= !empty($errors['full_name']) ? 'is-invalid' : '' ?>"
                            value="<?= h($user['full_name']) ?>"
                            maxlength="100"
                            required
                        >
                        <?php if (!empty($errors['full_name'])): ?>
                            <div class="invalid-feedback"><?= h($errors['full_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email (chỉ đọc) -->
                    <div class="mb-4">
                        <label for="email_display" class="form-label fw-medium">Email</label>
                        <input
                            type="email"
                            id="email_display"
                            class="form-control"
                            value="<?= h($user['email']) ?>"
                            disabled
                            readonly
                        >
                        <div class="form-text" style="color: var(--color-text-light);">
                            <i class="bi bi-info-circle me-1"></i>
                            Email không thể thay đổi.
                        </div>
                    </div>

                    <!-- Upload avatar -->
                    <div class="mb-4">
                        <label for="avatar" class="form-label fw-medium">Ảnh đại diện</label>

                        <!-- Vùng kéo thả / chọn file -->
                        <div class="profile-upload-area" id="avatarDropZone">
                            <input
                                type="file"
                                id="avatar"
                                name="avatar"
                                class="profile-upload-input <?= !empty($errors['avatar']) ? 'is-invalid' : '' ?>"
                                accept="image/jpeg,image/png,image/webp"
                                aria-label="Chọn ảnh đại diện"
                            >
                            <div class="profile-upload-body">
                                <i class="bi bi-cloud-arrow-up fs-3 mb-1" style="color: var(--color-primary);"></i>
                                <p class="mb-1 fw-medium" style="color: var(--color-text);">Kéo ảnh vào đây hoặc</p>
                                <label for="avatar" class="btn btn-outline-primary btn-sm">Chọn ảnh</label>
                                <p class="mb-0 mt-1" style="color: var(--color-text-light); font-size: var(--font-size-xs);">
                                    JPG, PNG, WebP — tối đa 2 MB
                                </p>
                            </div>
                        </div>

                        <?php if (!empty($errors['avatar'])): ?>
                            <div class="text-danger small mt-1">
                                <i class="bi bi-exclamation-circle me-1"></i><?= h($errors['avatar']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Preview tên file được chọn -->
                        <div class="profile-file-info mt-2 d-none" id="fileInfo">
                            <i class="bi bi-image me-1" style="color: var(--color-primary);"></i>
                            <span id="fileName" class="small fw-medium"></span>
                            <button type="button" class="btn-close btn-close-sm ms-2" id="clearFile" aria-label="Xóa ảnh đã chọn"></button>
                        </div>
                    </div>

                    <!-- Nút lưu -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-lg me-1"></i> Lưu thay đổi
                        </button>
                        <a href="<?= url('/') ?>" class="btn btn-light-subtle btn-sm">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>

            <!-- ===== Liên kết đổi mật khẩu (phụ) ===== -->
            <div class="card community-card p-4 mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fs-6 fw-bold mb-1" style="color: var(--color-text);">
                            <i class="bi bi-shield-lock me-1" style="color: var(--color-primary);"></i>
                            Bảo mật tài khoản
                        </h3>
                        <p class="mb-0 small" style="color: var(--color-text-muted);">
                            Cập nhật mật khẩu định kỳ để bảo vệ tài khoản của bạn.
                        </p>
                    </div>
                    <a href="<?= url('?module=auth&action=password') ?>" class="btn btn-outline-primary btn-sm flex-shrink-0">
                        <i class="bi bi-key me-1"></i> Đổi mật khẩu
                    </a>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>

