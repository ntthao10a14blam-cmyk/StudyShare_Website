<?php
$pageTitle = $pageTitle ?? 'Quản lý môn học';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">
    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>Quản lý môn học</h1>
                <p>Quản lý các môn học được sử dụng trên StudyShare.</p>
            </div>
        </div>

        <!-- Thêm môn học -->
        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Thêm môn học</h2>
                    <p>Tạo môn học mới cho hệ thống.</p>
                </div>
            </div>

            <div class="table-card">
                <form
                    method="POST"
                    action="?module=admin&action=createSubject"
                    class="admin-form"
                >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= h(csrfToken()) ?>"
                    >

                    <div class="form-group">
                        <label for="subject_name">Tên môn học</label>
                        <input
                            type="text"
                            id="subject_name"
                            name="name"
                            class="form-control"
                            placeholder="Ví dụ: Lập trình Web"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="subject_description">Mô tả</label>
                        <textarea
                            id="subject_description"
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Nhập mô tả môn học..."
                        ></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-plus-circle"></i>
                        Thêm môn học
                    </button>

                </form>
            </div>

        </section>

        <!-- Danh sách môn học -->
        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Danh sách môn học</h2>
                    <p>Các môn học hiện đang có trong hệ thống.</p>
                </div>
            </div>

            <div class="table-card">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên môn học</th>
                            <th>Mô tả</th>
                            <th>Số tài liệu</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($subjects)): ?>

                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-book"></i>
                                    <h3>Chưa có môn học</h3>
                                    <p>Hãy thêm môn học đầu tiên.</p>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($subjects as $index => $subject): ?>

                            <tr>

                                <td>
                                    <?= $index + 1 ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= h($subject['name'] ?? 'Không có tên') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= h($subject['description'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= (int) ($subject['document_count'] ?? 0) ?>
                                </td>

                                <td>

                                    <!-- Sửa -->
                                    <form
                                        method="POST"
                                        action="?module=admin&action=editSubject"
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
                                            value="<?= (int) $subject['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="name"
                                            value="<?= h($subject['name'] ?? '') ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="description"
                                            value="<?= h($subject['description'] ?? '') ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-small"
                                        >
                                            Sửa
                                        </button>

                                    </form>

                                    <!-- Xóa -->
                                    <form
                                        method="POST"
                                        action="?module=admin&action=deleteSubject"
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
                                            value="<?= (int) $subject['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn-small danger"
                                            data-confirm="Bạn có chắc muốn xóa môn học này?"
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