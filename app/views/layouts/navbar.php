<?php
/**
 * NAVBAR.PHP — Thanh điều hướng chuẩn hóa theo 6 chức năng của StudyShare
 */
$currentUser = Auth::currentUser();
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
$isActive    = Auth::isActive();

// Xác định trang hiện tại để đánh dấu active
$currentModule = preg_replace('/[^a-z0-9_-]/i', '', get('module', 'home'));
$currentAction = preg_replace('/[^a-z0-9_-]/i', '', get('action', 'index'));
?>

<!-- HEADER CHÍNH -->
<header class="public-header sticky-top" id="publicHeader">
    <div class="container header-inner d-flex align-items-center justify-content-between">
        
        <!-- 1. LOGO THƯƠNG HIỆU -->
        <a class="public-brand d-flex align-items-center gap-2 text-decoration-none" href="<?= url('/') ?>">
            <div class="brand-symbol">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div class="brand-copy">
                <strong>StudyShare</strong>
                <small>CỘNG ĐỒNG CHIA SẺ TÀI LIỆU HỌC TẬP</small>
            </div>
        </a>

        <!-- Nút toggle menu mobile -->
        <button class="mobile-menu-btn d-lg-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#publicMenu"
                aria-controls="publicMenu" aria-expanded="false" aria-label="Mở menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- 2. MENU ĐIỀU HƯỚNG CHÍNH -->
        <nav class="public-menu collapse d-lg-flex align-items-center gap-1" id="publicMenu">
            <!-- Trang chủ -->
            <a class="<?= $currentModule === 'home' ? 'active' : '' ?>" href="<?= url('/') ?>">
                <i class="bi bi-house-door"></i> Trang chủ
            </a>

            <!-- Kho tài liệu cộng đồng (Chức năng 3) -->
            <a class="<?= $currentModule === 'community' ? 'active' : '' ?>" href="<?= url('?module=community&action=index') ?>">
                <i class="bi bi-folder2-open"></i> Tài liệu
            </a>

            <!-- Hỏi đáp học tập (Chức năng 5) -->
            <a class="<?= $currentModule === 'questions' ? 'active' : '' ?>" href="<?= url('?module=questions&action=index') ?>">
                <i class="bi bi-question-circle"></i> Hỏi đáp
            </a>

            <!-- Lối tắt riêng cho User đã đăng nhập (Chức năng 2 & 4) -->
            <?php if ($isLoggedIn && $isActive): ?>
                <a class="<?= $currentModule === 'documents' ? 'active' : '' ?>" href="<?= url('?module=documents&action=index') ?>">
                    <i class="bi bi-file-earmark-person"></i> Tài liệu của tôi
                </a>
                <a class="<?= $currentModule === 'collections' ? 'active' : '' ?>" href="<?= url('?module=collections&action=index') ?>">
                    <i class="bi bi-collection"></i> Bộ sưu tập
                </a>
            <?php endif; ?>
        </nav>

        <!-- 3. KHU VỰC TÁC VỤ & TÀI KHOẢN -->
        <div class="header-actions d-none d-lg-flex align-items-center gap-2">
            
            <?php if ($isLoggedIn && $isActive): ?>
                <!-- NÚT ĐĂNG TẢI NHANH (Upload tài liệu hoặc Đặt câu hỏi) -->
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle d-flex align-items-center gap-1 px-3 py-2 fw-semibold"
                            type="button" id="quickPostDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus-lg"></i>
                        <span>Đăng bài</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" aria-labelledby="quickPostDropdown">
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= url('?module=documents&action=create') ?>">
                                <i class="bi bi-cloud-arrow-up text-primary fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Tải lên tài liệu</div>
                                    <small class="text-muted" style="font-size: 11px;">Định dạng PDF & ảnh minh họa</small>
                                </div>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?= url('?module=questions&action=create') ?>">
                                <i class="bi bi-question-square text-warning fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Đặt câu hỏi học tập</div>
                                    <small class="text-muted" style="font-size: 11px;">Thảo luận và nhận trợ giúp</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- DROPDOWN TÀI KHOẢN USER / ADMIN -->
                <div class="dropdown ms-1">
                    <a class="dropdown-toggle d-flex align-items-center gap-2 py-1 px-2 border rounded-pill text-decoration-none text-dark bg-white" href="#"
                       id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $currentUser['avatar'] ?? url('assets/images/default-avatar.png') ?>"
                             alt="Avatar" width="30" height="30"
                             class="rounded-circle object-fit-cover bg-light">
                        <span class="small fw-semibold text-truncate" style="max-width: 120px;">
                            <?= h($currentUser['full_name']) ?>
                        </span>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2" style="min-width: 240px;" aria-labelledby="userMenuDropdown">
                        <!-- Header thông tin tài khoản -->
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold small text-dark"><?= h($currentUser['full_name']) ?></div>
                            <div class="text-muted small text-truncate"><?= h($currentUser['email'] ?? '') ?></div>
                            <span class="badge <?= $isAdmin ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' ?> mt-1 font-monospace">
                                <?= $isAdmin ? '🛡️ Quản trị viên' : '🎓 Sinh viên' ?>
                            </span>
                        </li>

                        <!-- Nhóm chức năng cá nhân (Chức năng 1, 2, 4, 5) -->
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=auth&action=profile') ?>">
                                <i class="bi bi-person-circle me-2 text-muted"></i>Hồ sơ cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=documents&action=index') ?>">
                                <i class="bi bi-folder2 me-2 text-muted"></i>Quản lý tài liệu cá nhân
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=collections&action=favorites') ?>">
                                <i class="bi bi-heart me-2 text-danger"></i>Tài liệu yêu thích
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=collections&action=index') ?>">
                                <i class="bi bi-bookmarks me-2 text-muted"></i>Bộ sưu tập của tôi
                            </a>
                        </li>

                        <!-- Nhóm chức năng dành riêng cho Admin (Chức năng 6) -->
                        <?php if ($isAdmin): ?>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li class="dropdown-header small text-uppercase fw-bold text-danger pb-1">
                                <i class="bi bi-shield-lock me-1"></i>Khu vực Quản trị
                            </li>
                            <li>
                                <a class="dropdown-item py-1" href="<?= url('?module=admin&action=dashboard') ?>">
                                    <i class="bi bi-speedometer2 me-2 text-muted"></i>Bảng điều khiển & Thống kê
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-1" href="<?= url('?module=admin&action=pendingDocs') ?>">
                                    <i class="bi bi-hourglass-split me-2 text-warning"></i>Duyệt tài liệu
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-1" href="<?= url('?module=admin&action=users') ?>">
                                    <i class="bi bi-people me-2 text-muted"></i>Quản lý người dùng
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-1" href="<?= url('?module=admin&action=subjects') ?>">
                                    <i class="bi bi-book-half me-2 text-muted"></i>Môn học & Từ khóa
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-1" href="<?= url('?module=admin&action=reports') ?>">
                                    <i class="bi bi-flag me-2 text-danger"></i>Xử lý báo cáo vi phạm
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Đổi mật khẩu & Đăng xuất -->
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= url('?module=auth&action=changePassword') ?>">
                                <i class="bi bi-key me-2 text-muted"></i>Đổi mật khẩu
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= url('?module=auth&action=logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>

            <?php else: ?>
                <!-- Nút cho Khách (Chưa đăng nhập) -->
                <div class="d-flex align-items-center gap-2">
                    <a class="text-secondary small fw-semibold text-decoration-none px-2" href="<?= url('?module=auth&action=login') ?>">
                        Đăng nhập
                    </a>
                    <a class="btn btn-primary btn-sm px-3 py-1 fw-semibold" href="<?= url('?module=auth&action=register') ?>">
                        Đăng ký
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</header>
