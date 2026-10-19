<?php
/**
 * NAVBAR.PHP — Thanh điều hướng dùng chung
 *
 * Hiển thị khác nhau tuỳ theo vai trò:
 *   - Guest: Đăng nhập / Đăng ký
 *   - User:  Tài liệu của tôi, Bộ sưu tập, Hỏi đáp, hồ sơ
 *   - Admin: Các menu quản trị
 *
 * TV4 phụ trách giao diện file này.
 * Các thành viên KHÔNG sửa trực tiếp — tạo PR nếu cần thêm link.
 */
$currentUser = Auth::currentUser();
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
$isActive    = Auth::isActive();

// Xác định trang hiện tại để active menu
$currentModule = preg_replace('/[^a-z0-9_-]/i', '', get('module', 'home'));
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= url('/') ?>">
            <i class="bi bi-book-half fs-4"></i>
            <span>StudyHub</span>
        </a>

        <!-- Toggle mobile -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Menu chính -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentModule === 'home' ? 'active' : '' ?>"
                       href="<?= url('/') ?>">
                        <i class="bi bi-house"></i> Trang chủ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentModule === 'community' ? 'active' : '' ?>"
                       href="<?= url('?module=community&action=index') ?>">
                        <i class="bi bi-files"></i> Tài liệu
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentModule === 'questions' ? 'active' : '' ?>"
                       href="<?= url('?module=questions&action=index') ?>">
                        <i class="bi bi-question-circle"></i> Hỏi đáp
                    </a>
                </li>
            </ul>

            <!-- Menu phải -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">

                <?php if ($isLoggedIn && $isActive): ?>
                    <!-- Tài liệu của tôi -->
                    <li class="nav-item">
                        <a class="nav-link <?= $currentModule === 'documents' ? 'active' : '' ?>"
                           href="<?= url('?module=documents&action=index') ?>">
                            <i class="bi bi-folder2-open"></i> Tài liệu của tôi
                        </a>
                    </li>
                    <!-- Bộ sưu tập -->
                    <li class="nav-item">
                        <a class="nav-link <?= $currentModule === 'collections' ? 'active' : '' ?>"
                           href="<?= url('?module=collections&action=index') ?>">
                            <i class="bi bi-collection"></i> Bộ sưu tập
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <!-- Dropdown tài khoản -->
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#"
                           id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= $currentUser['avatar'] ?? url('assets/images/default-avatar.png') ?>"
                                 alt="Avatar" width="28" height="28"
                                 class="rounded-circle border border-light object-fit-cover">
                            <span class="d-none d-lg-inline"><?= h($currentUser['full_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                            <li>
                                <span class="dropdown-item-text text-muted small">
                                    <?= $isAdmin ? '<i class="bi bi-shield-check text-success"></i> Quản trị viên' : '<i class="bi bi-person"></i> Sinh viên' ?>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= url('?module=auth&action=profile') ?>">
                                    <i class="bi bi-person-circle me-2"></i>Hồ sơ cá nhân
                                </a>
                            </li>
                            <?php if ($isAdmin): ?>
                            <li>
                                <a class="dropdown-item" href="<?= url('?module=admin&action=dashboard') ?>">
                                    <i class="bi bi-speedometer2 me-2"></i>Trang quản trị
                                </a>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= url('?module=auth&action=logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Chưa đăng nhập -->
                    <li class="nav-item ms-lg-1">
                        <a class="nav-link" href="<?= url('?module=auth&action=login') ?>">
                            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a class="btn btn-light btn-sm ms-lg-2"
                           href="<?= url('?module=auth&action=register') ?>">
                            <i class="bi bi-person-plus"></i> Đăng ký
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
