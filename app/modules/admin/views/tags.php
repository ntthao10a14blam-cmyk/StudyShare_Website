<?php
$pageTitle = $pageTitle ?? 'Quản lý từ khoá';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">
    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>Quản lý Tags</h1>
                <p>Quản lý các thẻ dùng để phân loại tài liệu.</p>
            </div>
        </div>

        <!-- Thêm Tag -->
        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Thêm Tag</h2>
                    <p>Tạo tag mới cho hệ thống.</p>
                </div>
            </div>

            <div class="table-card">

                <form
                    method="POST"
                    action="?module=admin&action=createTag"
                    class="admin-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= h(csrfToken()) ?>"
                    >

                    <div class="form-group">
                        <label for="tag_name">Tên Tag</label>

                        <input
                            type="text"
                            id="tag_name"
                            name="name"
                            class="form-control"
                            placeholder="Ví dụ: Python"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Thêm Tag
                    </button>

                </form>

            </div>

        </section>

        <!-- Danh sách Tag -->
        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Danh sách Tags</h2>
                    <p>Các tag đang được sử dụng trong hệ thống.</p>
                </div>
            </div>

            <div class="table-card">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tag</th>
                            <th>Số tài liệu</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($tags)): ?>

                        <tr>
                            <td colspan="5">

                                <div class="empty-state">
                                    <i class="bi bi-tags"></i>
                                    <h3>Chưa có Tag</h3>
                                    <p>Hãy thêm Tag đầu tiên.</p>
                                </div>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($tags as $index => $tag): ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <span class="tag">
                                        <?= h($tag['name'] ?? 'Không có tên') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int) ($tag['document_count'] ?? 0) ?>
                                </td>

                                <td>
                                    <?=
                                        !empty($tag['created_at'])
                                            ? date('d/m/Y', strtotime($tag['created_at']))
                                            : '—'
                                    ?>
                                </td>

                                <td>

                                    <!-- Sửa Tag -->
                                    <form
                                        method="POST"
                                        action="?module=admin&action=editTag"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= h(csrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $tag['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="name"
                                            value="<?= h($tag['name'] ?? '') ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-small"
                                        >
                                            Sửa
                                        </button>

                                    </form>

                                    <!-- Xóa Tag -->
                                    <form
                                        method="POST"
                                        action="?module=admin&action=deleteTag"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= h(csrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $tag['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-small danger"
                                            data-confirm="Bạn có chắc muốn xóa Tag này?"
                                        >
                                            Xóa
                                        </button>

                                    </form>

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