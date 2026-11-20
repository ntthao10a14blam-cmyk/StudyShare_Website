<?php
$documents = $documents ?? [];
$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="admin-page">
    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-file-earmark-check-fill"></i>
                    Quản lý tài liệu công khai
                </h1>
                <p>Danh sách tài liệu đã được duyệt và đang hiển thị trên hệ thống.</p>
            </div>

            <div class="page-header-actions">
                <span class="badge bg-primary p-2">
                    Tổng: <?= number_format(count($documents)) ?> tài liệu
                </span>
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

        <div class="admin-panel p-3 mb-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                </span>
                <input
                    type="text"
                    class="form-control border-start-0 ps-1"
                    placeholder="Tìm kiếm tài liệu theo tên, người đăng, môn học..."
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
                <table class="table admin-table align-middle mb-0" id="publicDocumentsTable">
                    <thead>
                        <tr>
                            <th class="text-nowrap">STT</th>
                            <th>Tên tài liệu</th>
                            <th class="text-nowrap">Người đăng</th>
                            <th class="text-nowrap">Môn học</th>
                            <th class="text-nowrap text-center">Lượt tải</th>
                            <th class="text-nowrap">Ngày công khai</th>
                            <th class="text-nowrap text-center">Trạng thái</th>
                            <th class="text-nowrap text-center">Thao tác</th>
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
                                    <td class="text-nowrap text-muted"><?= $index + 1 ?></td>

                                    <td style="min-width: 240px;">
                                        <strong>
                                            <?= htmlspecialchars($document['title'] ?? 'Không có tiêu đề') ?>
                                        </strong>
                                        <div class="text-muted small text-break">
                                            <?= htmlspecialchars($document['original_file_name'] ?? '') ?>
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= htmlspecialchars($document['owner_name'] ?? 'Không xác định') ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= htmlspecialchars($document['subject_name'] ?? 'Chưa phân loại') ?>
                                    </td>

                                    <td class="text-nowrap text-center fw-semibold">
                                        <?= number_format((int) ($document['download_count'] ?? 0)) ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?= !empty($document['published_at'])
                                            ? date('d/m/Y H:i', strtotime($document['published_at']))
                                            : 'Chưa xác định' ?>
                                    </td>

                                    <td class="text-nowrap text-center">
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                            Đang công khai
                                        </span>
                                    </td>

                                    <td class="text-nowrap text-center">
                                        <div class="action-buttons justify-content-center">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger text-nowrap"
                                                data-bs-toggle="modal"
                                                data-bs-target="#hideDocumentModal"
                                                data-id="<?= (int) $document['id'] ?>"
                                                data-title="<?= htmlspecialchars($document['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            >
                                                <i class="bi bi-eye-slash"></i>
                                                Ẩn tài liệu
                                            </button>
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
</div>

<!-- MODAL ẨN TÀI LIỆU -->
<div class="modal fade" id="hideDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('?module=admin&action=hideDoc') ?>">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-eye-slash"></i>
                        Ẩn tài liệu
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="document_id" id="hideDocumentId">

                    <p class="mb-3">
                        Bạn đang ẩn tài liệu:
                        <strong id="hideDocumentTitle"></strong>
                    </p>

                    <div class="mb-2">
                        <label for="hideDocumentReason" class="form-label fw-semibold">
                            Lý do ẩn tài liệu <span class="text-danger">*</span>
                        </label>
                        <textarea
                            id="hideDocumentReason"
                            name="reason"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            placeholder="Nhập lý do ẩn tài liệu..."
                            required
                        ></textarea>
                        <small class="text-muted">
                            Tác giả sẽ nhận được thông báo lý do khi tài liệu bị ẩn khỏi cộng đồng.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-eye-slash"></i>
                        Xác nhận ẩn
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hideModal = document.getElementById('hideDocumentModal');
    if (hideModal) {
        hideModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            document.getElementById('hideDocumentId').value =
                button.getAttribute('data-id') || '';
            document.getElementById('hideDocumentTitle').textContent =
                button.getAttribute('data-title') || '';
            const reasonEl = document.getElementById('hideDocumentReason');
            if (reasonEl) reasonEl.value = '';
        });
    }
});
</script>