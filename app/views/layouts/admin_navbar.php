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

            <ul class="navbar-nav align-items-lg-center ms-auto">

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