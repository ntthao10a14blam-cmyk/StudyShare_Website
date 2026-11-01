<?php
$pageTitle = $pageTitle ?? 'Quản lý tài khoản';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">
    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>Quản lý người dùng</h1>
                <p>Quản lý tài khoản và trạng thái người dùng trong hệ thống.</p>
            </div>
        </div>

        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Danh sách người dùng</h2>
                    <p>Theo dõi tài khoản người dùng StudyShare.</p>
                </div>

                <input
                    type="text"
                    id="userSearch"
                    class="admin-search"
                    placeholder="Tìm kiếm..."
                >
            </div>

            <div class="table-card">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Người dùng</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Ngày tham gia</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody id="userTable">

                    <?php if (empty($users)): ?>

                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h3>Chưa có người dùng</h3>
                                    <p>Hệ thống chưa có tài khoản nào.</p>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($users as $user): ?>

                            <?php
                            $userId = (int) ($user['id'] ?? 0);
                            $role = $user['role'] ?? 'user';

                            /*
                             * Tùy schema project:
                             * is_locked = 1 → đã khóa
                             * is_locked = 0 → đang hoạt động
                             */
                            $isLocked = (int) ($user['is_locked'] ?? 0) === 1;
                            ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= h($user['full_name'] ?? 'Không có tên') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= h($user['email'] ?? '—') ?>
                                </td>

                                <td>
                                    <?php if ($role === 'admin'): ?>
                                        <span class="tag">Admin</span>
                                    <?php else: ?>
                                        Người dùng
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?=
                                        !empty($user['created_at'])
                                            ? date('d/m/Y', strtotime($user['created_at']))
                                            : '—'
                                    ?>
                                </td>

                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="status rejected">
                                            Đã khóa
                                        </span>
                                    <?php else: ?>
                                        <span class="status approved">
                                            Hoạt động
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <td>

                                    <?php if ($role === 'admin'): ?>

                                        <button
                                            class="btn-small"
                                            disabled
                                        >
                                            Admin
                                        </button>

                                    <?php else: ?>

                                        <form
                                            method="POST"
                                            action="?module=admin&action=toggleLock"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h(csrfToken()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="user_id"
                                                value="<?= $userId ?>"
                                            >

                                            <?php if ($isLocked): ?>

                                                <button
                                                    type="submit"
                                                    class="btn-small"
                                                    data-confirm="Bạn có chắc muốn mở khóa tài khoản này?"
                                                >
                                                    Mở khóa
                                                </button>

                                            <?php else: ?>

                                                <button
                                                    type="submit"
                                                    class="btn-small"
                                                    data-confirm="Bạn có chắc muốn khóa tài khoản này?"
                                                >
                                                    Khóa
                                                </button>

                                            <?php endif; ?>

                                        </form>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>