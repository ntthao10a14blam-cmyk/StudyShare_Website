<?php
$documents = $documents ?? $pendingDocuments ?? [];

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="admin-page">
    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-file-earmark-check"></i>
                    Duyệt tài liệu
                </h1>
                <p>Kiểm tra nội dung trước khi tài liệu được công khai trên hệ thống</p>
            </div>

            <span class="badge bg-warning text-dark p-2">
                <i class="bi bi-hourglass-split"></i>
                <?= count($documents) ?> tài liệu đang chờ
            </span>
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

        <!-- DANH SÁCH -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-inbox-fill"></i>
                        Danh sách tài liệu chờ duyệt
                    </h5>
                    <p>Các tài liệu đang chờ quản trị viên kiểm duyệt</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tài liệu</th>
                            <th>Người đăng</th>
                            <th>Môn học</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                                <strong>Không có tài liệu chờ duyệt</strong>
                                <p class="text-muted mb-0">
                                    Tất cả tài liệu đã được xử lý.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($documents as $doc): ?>
                            <?php
                                $docId = (int) ($doc['id'] ?? 0);
                                $title = $doc['title'] ?? 'Không có tiêu đề';
                                $fileName = $doc['original_file_name'] ?? '';
                                $description = $doc['description'] ?? '';
                                $fileSize = (int) ($doc['file_size'] ?? 0);
                            ?>

                            <tr>
                                <td>
                                    <span class="text-muted">#<?= $docId ?></span>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?= $e($title) ?>
                                    </div>

                                    <?php if ($fileName !== ''): ?>
                                        <small class="text-muted">
                                            <i class="bi bi-paperclip"></i>
                                            <?= $e($fileName) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= $e($doc['owner_name'] ?? $doc['user_name'] ?? 'Không rõ') ?>
                                </td>

                                <td>
                                    <?= $e($doc['subject_name'] ?? 'Chưa phân loại') ?>
                                </td>

                                <td>
                                    <?= !empty($doc['submitted_at'])
                                        ? $e(date('d/m/Y H:i', strtotime($doc['submitted_at'])))
                                        : '—' ?>
                                </td>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-clock"></i>
                                        Chờ duyệt
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2">

                                        <!-- XEM CHI TIẾT -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#documentModal"
                                            data-id="<?= $docId ?>"
                                            data-title="<?= $e($title) ?>"
                                            data-description="<?= $e($description) ?>"
                                            data-filename="<?= $e($fileName) ?>"
                                            data-size="<?= $fileSize ?>"
                                            data-owner="<?= $e($doc['owner_name'] ?? $doc['user_name'] ?? '') ?>"
                                            data-subject="<?= $e($doc['subject_name'] ?? '') ?>"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Chi tiết
                                        </button>

                                        <!-- DUYỆT -->
                                        <form
                                            method="POST"
                                            action="<?= url('?module=admin&action=reviewDoc') ?>"
                                            class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn duyệt và công khai tài liệu này không?');"
                                        >
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= $e($csrfToken) ?>">

                                            <input type="hidden" name="document_id"
                                                   value="<?= $docId ?>">

                                            <input type="hidden" name="action"
                                                   value="approved">

                                            <button type="submit"
                                                    class="btn btn-sm btn-success">
                                                <i class="bi bi-check-lg"></i>
                                                Duyệt
                                            </button>
                                        </form>

                                        <!-- TỪ CHỐI -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal"
                                            data-id="<?= $docId ?>"
                                            data-title="<?= $e($title) ?>"
                                        >
                                            <i class="bi bi-x-lg"></i>
                                            Từ chối
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

<!-- MODAL CHI TIẾT TÀI LIỆU -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    Chi tiết tài liệu
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="text-muted small">Tên tài liệu</label>
                    <h5 id="detailTitle"></h5>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Người đăng</label>
                        <div id="detailOwner" class="fw-semibold"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Môn học</label>
                        <div id="detailSubject" class="fw-semibold"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Tên file</label>
                        <div id="detailFilename"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Dung lượng</label>
                        <div id="detailSize"></div>
                    </div>
                </div>

                <hr>

                <label class="text-muted small">Mô tả tài liệu</label>
                <div id="detailDescription" class="p-3 bg-light rounded">
                    Chưa có mô tả.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Đóng
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL TỪ CHỐI -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=reviewDoc') ?>"
                  id="rejectDocumentForm">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Từ chối tài liệu
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="document_id"
                           id="rejectDocumentId">

                    <input type="hidden" name="action"
                           value="rejected">

                    <p>
                        Bạn đang từ chối tài liệu:
                        <strong id="rejectDocumentTitle"></strong>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Lý do từ chối <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="reason"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            placeholder="Nhập lý do từ chối tài liệu..."
                            required
                        ></textarea>

                        <small class="text-muted">
                            Người đăng cần biết lý do để chỉnh sửa và gửi lại.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i>
                        Xác nhận từ chối
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Hiển thị chi tiết tài liệu
    const documentModal = document.getElementById('documentModal');

    if (documentModal) {
        documentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) return;

            const getData = function (name) {
                return button.getAttribute('data-' + name) || '';
            };

            document.getElementById('detailTitle').textContent =
                getData('title');

            document.getElementById('detailOwner').textContent =
                getData('owner');

            document.getElementById('detailSubject').textContent =
                getData('subject');

            document.getElementById('detailFilename').textContent =
                getData('filename') || 'Không có';

            document.getElementById('detailDescription').textContent =
                getData('description') || 'Chưa có mô tả.';

            const size = parseInt(getData('size'), 10) || 0;

            document.getElementById('detailSize').textContent =
                size > 0 ? (size / 1024 / 1024).toFixed(2) + ' MB' : 'Không rõ';
        });
    }

    // Hiển thị modal từ chối
    const rejectModal = document.getElementById('rejectModal');

    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) return;

            document.getElementById('rejectDocumentId').value =
                button.getAttribute('data-id') || '';

            document.getElementById('rejectDocumentTitle').textContent =
                button.getAttribute('data-title') || '';
        });
    }

});
</script>
