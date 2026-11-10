<?php
$pendingCount = (int) ($pendingCount ?? 0);

$adminName = $adminName
    ?? $currentUser['full_name']
    ?? 'Quản trị viên';

$currentModule = $_GET['module'] ?? '';
$currentAction = $_GET['action'] ?? '';

$isActive = function (string $action) use ($currentModule, $currentAction): string {
    return ($currentModule === 'admin' && $currentAction === $action)
        ? 'active'
        : '';
};
?>

<nav class="navbar navbar-expand-lg admin-navbar">
    <div class="container-fluid">

        <a class="navbar-brand admin-brand"
           href="<?= url('?module=admin&action=dashboard') ?>">
            <i class="bi bi-mortarboard-fill"></i>
            StudyShare
            <span class="admin-brand-label">ADMIN</span>
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#adminNavbarContent"
                aria-controls="adminNavbarContent"
                aria-expanded="false"
                aria-label="Mở menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNavbarContent">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('dashboard') ?>"
                       href="<?= url('?module=admin&action=dashboard') ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('pendingDocs') ?>"
                       href="<?= url('?module=admin&action=pendingDocs') ?>">
                        <i class="bi bi-hourglass-split"></i>
                        Duyệt tài liệu

                        <?php if ($pendingCount > 0): ?>
                            <span class="badge bg-danger ms-1">
                                <?= $pendingCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('publicDocs') ?>"
                       href="<?= url('?module=admin&action=publicDocs') ?>">
                        <i class="bi bi-file-earmark-check"></i>
                        Tài liệu công khai
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('users') ?>"
                       href="<?= url('?module=admin&action=users') ?>">
                        <i class="bi bi-people"></i>
                        Người dùng
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('subjects') ?>"
                       href="<?= url('?module=admin&action=subjects') ?>">
                        <i class="bi bi-book"></i>
                        Môn học
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('tags') ?>"
                       href="<?= url('?module=admin&action=tags') ?>">
                        <i class="bi bi-tags"></i>
                        Tags
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $isActive('reports') ?>"
                       href="<?= url('?module=admin&action=reports') ?>">
                        <i class="bi bi-flag"></i>
                        Báo cáo
                    </a>
                </li>

            </ul>

            <ul class="navbar-nav align-items-lg-center">

                <li class="nav-item me-lg-3">
                    <a class="btn btn-outline-primary btn-sm"
                       href="<?= url('?module=home') ?>">
                        <i class="bi bi-house"></i>
                        Xem giao diện User
                    </a>
                </li>

                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle admin-account"
                       href="#"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">

                        <i class="bi bi-person-circle"></i>

                        <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>

                        <span class="badge bg-warning text-dark">
                            Quản trị viên
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">

                        <li>
                            <a class="dropdown-item"
                               href="<?= url('?module=admin&action=dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Trang quản trị
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item text-danger"
                               href="<?= url('?module=auth&action=logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Đăng xuất
                            </a>
                        </li>

                    </ul>
                </li>

            </ul>

        </div>
    </div>
</nav>