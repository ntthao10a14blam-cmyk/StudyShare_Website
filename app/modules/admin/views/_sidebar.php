<?php
$currentAction = $_GET['action'] ?? 'dashboard';
$pendingCount = (int) ($pendingCount ?? 0);
$pendingReportsCount = (int) ($pendingReportsCount ?? $totalReports ?? 0);

$isSidebarActive = static function (string $action) use ($currentAction): string {
    return $currentAction === $action ? 'active' : '';
};
?>
<aside class="admin-dash-sidebar" aria-label="Điều hướng quản trị">
    <div>
        <div class="dash-nav-group-title">Tổng quan</div>
        <ul class="dash-nav-list">
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('dashboard') ?>" href="<?= url('?module=admin&action=dashboard') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </span>
                </a>
            </li>
            <li>
                <a class="dash-nav-link" href="<?= url('?module=home') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-house-door"></i>
                        <span>Trang chủ User</span>
                    </span>
                </a>
            </li>
        </ul>
    </div>

    <div>
        <div class="dash-nav-group-title">Kiểm duyệt &amp; Nội dung</div>
        <ul class="dash-nav-list">
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('pendingDocs') ?>" href="<?= url('?module=admin&action=pendingDocs') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-hourglass-split"></i>
                        <span>Duyệt tài liệu</span>
                    </span>
                    <?php if ($pendingCount > 0): ?>
                        <span class="dash-nav-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('publicDocs') ?>" href="<?= url('?module=admin&action=publicDocs') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-file-earmark-check"></i>
                        <span>Tài liệu công khai</span>
                    </span>
                </a>
            </li>
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('reports') ?>" href="<?= url('?module=admin&action=reports') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-flag"></i>
                        <span>Báo cáo vi phạm</span>
                    </span>
                    <?php if ($pendingReportsCount > 0): ?>
                        <span class="dash-nav-badge"><?= $pendingReportsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>
    </div>

    <div>
        <div class="dash-nav-group-title">Quản lý hệ thống</div>
        <ul class="dash-nav-list">
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('users') ?>" href="<?= url('?module=admin&action=users') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-people"></i>
                        <span>Người dùng</span>
                    </span>
                </a>
            </li>
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('subjects') ?>" href="<?= url('?module=admin&action=subjects') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-book"></i>
                        <span>Môn học</span>
                    </span>
                </a>
            </li>
            <li>
                <a class="dash-nav-link <?= $isSidebarActive('tags') ?>" href="<?= url('?module=admin&action=tags') ?>">
                    <span class="dash-nav-link-inner">
                        <i class="bi bi-tags"></i>
                        <span>Từ khóa (Tags)</span>
                    </span>
                </a>
            </li>
        </ul>
    </div>
</aside>
