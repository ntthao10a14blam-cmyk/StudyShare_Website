<?php
$users = $users ?? [];
$keyword = $keyword ?? ($_GET['keyword'] ?? '');
$currentPage = max(1, (int) ($currentPage ?? $_GET['page'] ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$totalUsers = (int) ($totalUsers ?? 0);

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

// Lấy ID admin hiện tại từ các cấu trúc session phổ biến.
$currentAdminId = (int) (
    $_SESSION['user_id']
    ?? $_SESSION['user']['id']
    ?? $_SESSION['user']['user_id']
    ?? 0
);

// Token CSRF: sử dụng biến mà Controller truyền xuống nếu có.
$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');

$buildPageUrl = static function (int $page) use ($keyword): string {
    return url(
        '?module=admin&action=users&page=' . $page
        . '&keyword=' . urlencode((string) $keyword)
    );
};
?>

<div class="admin-page">
    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-people-fill"></i>
                    Quản lý người dùng
                </h1>
                <p>Quản lý tài khoản và trạng thái hoạt động của thành viên</p>
            </div>

            <div class="page-header-actions">
                <span class="badge bg-primary p-2">
                    Tổng: <?= number_format($totalUsers) ?> người dùng
                </span>
            </div>
        </div>

        <!-- THÔNG BÁO -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i>
                <?= $e($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $e($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- TÌM KIẾM -->
        <div class="admin-panel mb-4">
            <form method="GET" action="" class="row g-3 align-items-end">
                <input type="hidden" name="module" value="admin">
                <input type="hidden" name="action" value="users">

                <div class="col-md-8">
                    <label for="keyword" class="form-label fw-semibold">
                        Tìm kiếm người dùng
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-control"
                            placeholder="Nhập tên hoặc email..."
                            value="<?= $e($keyword) ?>"
                        >
                    </div>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search"></i>
                        Tìm kiếm
                    </button>

                    <a href="<?= url('?module=admin&action=users') ?>"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>

        <!-- DANH SÁCH NGƯỜI DÙNG -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-person-lines-fill"></i>
                        Danh sách người dùng
                    </h5>
                    <p>
                        Hiển thị <?= count($users) ?> người dùng
                        trên tổng số <?= number_format($totalUsers) ?> tài khoản
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thông tin người dùng</th>
                            <th>Vai trò</th>
                            <th>Ngày tham gia</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-person-x fs-1 text-muted d-block mb-2"></i>
                                <strong>Không tìm thấy người dùng</strong>
                                <p class="text-muted mb-0">
                                    Thử thay đổi từ khóa tìm kiếm.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($users as $user): ?>
                            <?php
                                $userId = (int) ($user['id'] ?? 0);
                                $isLocked = ($user['status'] ?? '') === 'locked';
                                $isSelf = $currentAdminId > 0
                                    && $userId === $currentAdminId;

                                $isUserAdmin = ($user['role'] ?? 'user') === 'admin';
                            ?>

                            <tr>
                                <td>
                                    <span class="text-muted">
                                        #<?= $userId ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="admin-user-avatar">
                                            <?= $e(mb_strtoupper(
                                                mb_substr($user['full_name'] ?? 'U', 0, 1)
                                            )) ?>
                                        </div>

                                        <div>
                                            <div class="fw-semibold">
                                                <?= $e($user['full_name'] ?? 'Chưa cập nhật') ?>

                                                <?php if ($isSelf): ?>
                                                    <span class="badge bg-primary ms-1">
                                                        Bạn
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <small class="text-muted">
                                                <?= $e($user['email'] ?? '') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($isUserAdmin): ?>
                                        <span class="badge bg-primary">
                                            <i class="bi bi-shield-check"></i>
                                            Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">
                                            Người dùng
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= !empty($user['created_at'])
                                        ? $e(date('d/m/Y', strtotime($user['created_at'])))
                                        : '—' ?>
                                </td>

                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-lock-fill"></i>
                                            Đã khóa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Hoạt động
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center">
                                    <?php if (!$isSelf): ?>

                                        <?php if ($isLocked): ?>
                                            <form
                                                method="POST"
                                                action="<?= url('?module=admin&action=toggleLock') ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn mở khóa tài khoản này không?');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                       value="<?= $e($csrfToken) ?>">

                                                <input type="hidden" name="user_id"
                                                       value="<?= $userId ?>">

                                                <input type="hidden" name="status"
                                                       value="active">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-success">
                                                    <i class="bi bi-unlock-fill"></i>
                                                    Mở khóa
                                                </button>
                                            </form>

                                        <?php else: ?>
                                            <form
                                                method="POST"
                                                action="<?= url('?module=admin&action=toggleLock') ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn khóa tài khoản này không? Người này sẽ không thể đăng nhập.');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                       value="<?= $e($csrfToken) ?>">

                                                <input type="hidden" name="user_id"
                                                       value="<?= $userId ?>">

                                                <input type="hidden" name="status"
                                                       value="locked">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-lock-fill"></i>
                                                    Khóa
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="bi bi-shield-lock"></i>
                                            Tài khoản hiện tại
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PHÂN TRANG -->
            <?php if ($totalPages > 1): ?>
                <div class="admin-pagination">
                    <div class="text-muted small">
                        Trang <?= $currentPage ?> / <?= $totalPages ?>
                    </div>

                    <nav aria-label="Phân trang người dùng">
                        <ul class="pagination mb-0">

                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="<?= $buildPageUrl(max(1, $currentPage - 1)) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);
                            ?>

                            <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                                <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link"
                                       href="<?= $buildPageUrl($page) ?>">
                                        <?= $page ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link"
                                   href="<?= $buildPageUrl(min($totalPages, $currentPage + 1)) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>
