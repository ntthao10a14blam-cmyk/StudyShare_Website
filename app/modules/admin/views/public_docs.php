<?php
$documents = $documents ?? [];
$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="admin-page">
    <div class="page-header">
        <div>
            <h1>Quản lý tài liệu công khai</h1>
            <p>Danh sách tài liệu đã được duyệt và đang hiển thị trên hệ thống.</p>
        </div>
    </div>

    <?php if (!empty($_SESSION['admin_flash'])): ?>
        <?php
        $flash = $_SESSION['admin_flash'];
        unset($_SESSION['admin_flash']);
        ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="admin-toolbar">
        <div class="search-box">
            <input
                type="text"
                placeholder="Tìm kiếm tài liệu..."
                data-table-search="publicDocumentsTable"
            >
        </div>
    </div>

    <div class="table-card">
        <div class="section-header">
            <h3>Danh sách tài liệu</h3>
            <span><?= count($documents) ?> tài liệu</span>
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="publicDocumentsTable">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên tài liệu</th>
                        <th>Người đăng</th>
                        <th>Môn học</th>
                        <th>Lượt tải</th>
                        <th>Ngày công khai</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <p>Hiện chưa có tài liệu công khai.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $index => $document): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($document['title'] ?? 'Không có tiêu đề') ?>
                                    </strong>
                                    <div class="text-muted">
                                        <?= htmlspecialchars($document['original_file_name'] ?? '') ?>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($document['owner_name'] ?? 'Không xác định') ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($document['subject_name'] ?? 'Chưa phân loại') ?>
                                </td>

                                <td>
                                    <?= number_format((int) ($document['download_count'] ?? 0)) ?>
                                </td>

                                <td>
                                    <?= !empty($document['published_at'])
                                        ? date('d/m/Y H:i', strtotime($document['published_at']))
                                        : 'Chưa xác định' ?>
                                </td>

                                <td>
                                    <span class="status approved">Đang công khai</span>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-small"
                                            data-modal-open="hideDocument<?= (int) $document['id'] ?>"
                                        >
                                            Ẩn tài liệu
                                        </button>
                                    </div>

                                    <div
                                        class="admin-modal"
                                        id="hideDocument<?= (int) $document['id'] ?>"
                                    >
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3>Ẩn tài liệu</h3>

                                                <button
                                                    type="button"
                                                    class="modal-close"
                                                    data-modal-close
                                                >
                                                    &times;
                                                </button>
                                            </div>

                                            <form
                                                method="POST"
                                                action="?module=admin&action=hideDoc"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="document_id"
                                                    value="<?= (int) $document['id'] ?>"
                                                >

                                                <div class="form-group">
                                                    <label>
                                                        Tài liệu
                                                    </label>

                                                    <input
                                                        type="text"
                                                        value="<?= htmlspecialchars($document['title'] ?? '') ?>"
                                                        readonly
                                                    >
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        Lý do ẩn tài liệu
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    <textarea
                                                        name="reason"
                                                        rows="4"
                                                        required
                                                        maxlength="1000"
                                                        placeholder="Nhập lý do ẩn tài liệu..."
                                                    ></textarea>
                                                </div>

                                                <div class="modal-actions">
                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary"
                                                        data-modal-close
                                                    >
                                                        Hủy
                                                    </button>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger"
                                                    >
                                                        Xác nhận ẩn
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>