<?php
/**
 * NAVBAR.PHP — Header & Topline chuẩn giao diện Kho Tài Liệu Sinh Viên
 * Nguồn tham khảo: https://www.khotailieusinhvien.com/
 */
$currentUser = Auth::currentUser();
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
$isActive    = Auth::isActive();

// Xác định trang hiện tại để active menu
$currentModule = preg_replace('/[^a-z0-9_-]/i', '', get('module', 'home'));
?>

<!-- 1. TOPLINE (Thanh thông báo & Liên hệ trên cùng) -->
<!-- <div class="public-topline">
    <div class="container d-flex align-items-center justify-content-between h-100">
        <span class="topline-slogan d-flex align-items-center gap-2">
            <i class="bi bi-book-half text-warning"></i>
            <span>Theo trường · Theo môn học · Theo loại tài liệu</span>
        </span>
        <div class="topline-contact d-none d-md-flex align-items-center gap-3">
            <a href="mailto:support@khotailieusinhvien.com" class="text-decoration-none">
                <i class="bi bi-envelope me-1"></i> support@khotailieusinhvien.com
            </a>
            <span class="opacity-25">|</span>
            <a href="tel:0900000000" class="text-decoration-none">
                <i class="bi bi-telephone me-1"></i> 0900 000 000
            </a>
        </div>
    </div>
</div> -->

<!-- 2. MAIN HEADER (Header chính có viền vàng cam nổi bật) -->
<header class="public-header sticky-top" id="publicHeader">
    <div class="container header-inner d-flex align-items-center justify-content-between">
        
        <!-- Logo thương hiệu chuẩn Kho Tài Liệu Sinh Viên -->
        <a class="public-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= url('/') ?>">
            <div class="brand-symbol">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="brand-copy">
                <strong>StudyShare</strong>
                <small>CỘNG ĐỒNG CHIA SẺ TÀI LIỆU HỌC TẬP</small>
            </div>
        </a>

        <!-- Nút mở menu trên di động -->
        <button class="mobile-menu-btn d-lg-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#publicMenu"
                aria-controls="publicMenu" aria-expanded="false" aria-label="Mở menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- Menu điều hướng chính -->
        <nav class="public-menu collapse d-lg-flex align-items-center gap-1" id="publicMenu">
            <a class="<?= $currentModule === 'home' ? 'active' : '' ?>" href="<?= url('/') ?>">
                <i class="bi bi-house-door"></i> Trang chủ
            </a>
            <a class="<?= $currentModule === 'community' ? 'active' : '' ?>" href="<?= url('?module=community&action=index') ?>">
                <i class="bi bi-folder2-open"></i> Thư viện tài liệu
            </a>
            <a class="" href="<?= url('?module=community&action=index') ?>">
                <i class="bi bi-building"></i> Theo trường
            </a>
            <a class="" href="<?= url('?module=community&action=index') ?>">
                <i class="bi bi-book"></i> Môn học
            </a>
            <a class="<?= $currentModule === 'questions' ? 'active' : '' ?>" href="<?= url('?module=questions&action=index') ?>">
                <i class="bi bi-question-circle"></i> Hỏi đáp
            </a>

            <?php if ($isLoggedIn && $isActive): ?>
                <a class="<?= $currentModule === 'documents' ? 'active' : '' ?>" href="<?= url('?module=documents&action=index') ?>">
                    <i class="bi bi-file-earmark-person"></i> Tài liệu của tôi
                </a>
                <a class="<?= $currentModule === 'collections' ? 'active' : '' ?>" href="<?= url('?module=collections&action=index') ?>">
                    <i class="bi bi-collection"></i> Bộ sưu tập
                </a>
            <?php endif; ?>
        </nav>

        <!-- Nút CTA Tìm tài liệu & Khu vực tài khoản -->
        <div class="header-actions d-none d-lg-flex align-items-center gap-2">
            <!-- Nút CTA chuẩn khotailieusinhvien -->
            <a class="header-cta text-decoration-none" href="<?= url('?module=community&action=index') ?>">
                <i class="bi bi-search"></i> Tìm tài liệu
            </a>

            <?php if ($isLoggedIn): ?>
                <!-- Dropdown tài khoản khi đã đăng nhập -->
                <div class="dropdown ms-1">
                    <a class="dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 border rounded-pill text-decoration-none text-dark bg-white" href="#"
                       id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $currentUser['avatar'] ?? url('assets/images/default-avatar.png') ?>"
                             alt="Avatar" width="28" height="28"
                             class="rounded-circle object-fit-cover bg-light">
                        <span class="small fw-semibold text-truncate" style="max-width: 120px;"><?= h($currentUser['full_name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" aria-labelledby="userMenuDropdown">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <div class="fw-semibold small"><?= h($currentUser['full_name']) ?></div>
                                <div class="text-muted small"><?= h($currentUser['email'] ?? '') ?></div>
                                <span class="badge <?= $isAdmin ? 'bg-success' : 'bg-primary' ?>-subtle text-<?= $isAdmin ? 'success' : 'primary' ?> mt-1">
                                    <?= $isAdmin ? 'Quản trị viên' : 'Sinh viên' ?>
                                </span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=auth&action=profile') ?>">
                                <i class="bi bi-person-circle me-2 text-muted"></i>Hồ sơ cá nhân
                            </a>
                        </li>
                        <?php if ($isAdmin): ?>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=admin&action=dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2 text-muted"></i>Trang quản trị
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= url('?module=auth&action=logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- Nút đăng nhập/đăng ký gọn gàng -->
                <div class="d-flex align-items-center gap-2 ms-1">
                    <a class="text-secondary small fw-medium text-decoration-none px-2" href="<?= url('?module=auth&action=login') ?>">
                        Đăng nhập
                    </a>
                    <a class="btn btn-outline-primary btn-sm px-2 py-1" href="<?= url('?module=auth&action=register') ?>">
                        Đăng ký
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</header>
