<?php
$reports = $reports ?? [];
$currentStatus = $currentStatus ?? ($_GET['status'] ?? 'all');

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');

$statusLabels = [
    'pending' => 'Chờ xử lý',
    'resolved' => 'Đã xử lý',
    'dismissed' => 'Đã bỏ qua'
];

$statusClasses = [
    'pending' => 'bg-warning text-dark',
    'resolved' => 'bg-success',
    'dismissed' => 'bg-secondary'
];

$typeLabels = [
    'document' => 'Tài liệu',
    'question' => 'Câu hỏi',
    'answer' => 'Câu trả lời',
    'comment' => 'Bình luận'
];
?>

<div class="admin-page">
    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-flag-fill"></i>
                    Báo cáo vi phạm
                </h1>
                <p>Kiểm tra và xử lý các nội dung bị người dùng báo cáo</p>
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

        <!-- LỌC TRẠNG THÁI -->
        <div class="admin-panel mb-4">
            <form method="GET" action="" class="row g-3 align-items-end">

                <input type="hidden" name="module" value="admin">
                <input type="hidden" name="action" value="reports">

                <div class="col-md-8">
                    <label for="status" class="form-label fw-semibold">
                        Lọc báo cáo theo trạng thái
                    </label>

                    <select name="status" id="status" class="form-select">
                        <option value="all" <?= $currentStatus === 'all' ? 'selected' : '' ?>>
                            Tất cả báo cáo
                        </option>

                        <option value="pending" <?= $currentStatus === 'pending' ? 'selected' : '' ?>>
                            Chờ xử lý
                        </option>

                        <option value="resolved" <?= $currentStatus === 'resolved' ? 'selected' : '' ?>>
                            Đã xử lý
                        </option>

                        <option value="dismissed" <?= $currentStatus === 'dismissed' ? 'selected' : '' ?>>
                            Đã bỏ qua
                        </option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel"></i>
                        Lọc
                    </button>

                    <a href="<?= url('?module=admin&action=reports') ?>"
                       class="btn btn-outline-secondary">
                        Đặt lại
                    </a>
                </div>

            </form>
        </div>

        <!-- DANH SÁCH BÁO CÁO -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-list-check"></i>
                        Danh sách báo cáo
                    </h5>
                    <p>
                        <?= count($reports) ?> báo cáo được tìm thấy
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Người báo cáo</th>
                            <th>Loại nội dung</th>
                            <th>Lý do</th>
                            <th>Nội dung bị báo cáo</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bi bi-clipboard-check fs-1 text-muted d-block mb-2"></i>
                                <strong>Không có báo cáo nào</strong>
                                <p class="text-muted mb-0">
                                    Không tìm thấy báo cáo phù hợp với bộ lọc.
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($reports as $report): ?>
                            <?php
                                $reportId = (int) ($report['id'] ?? 0);
                                $status = $report['status'] ?? 'pending';
                                $contentType = $report['content_type'] ?? 'unknown';

                                $content = $report['reported_content']
                                    ?? $report['content']
                                    ?? $report['title']
                                    ?? '';

                                $contentTitle = $report['parent_question_title'] ?? '';

                                $isPending = $status === 'pending';
                            ?>

                            <tr>
                                <td>
                                    <span class="text-muted">#<?= $reportId ?></span>
                                </td>

                                <td>
                                    <strong>
                                        <?= $e($report['reporter_name'] ?? 'Không rõ') ?>
                                    </strong>
                                </td>

                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        <?= $e($typeLabels[$contentType] ?? $contentType) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="text-danger fw-semibold">
                                        <?= $e($report['reason'] ?? '') ?>
                                    </span>
                                </td>

                                <td style="min-width: 180px; max-width: 260px;">
                                    <?php if ($contentTitle !== ''): ?>
                                        <div class="fw-semibold">
                                            <?= $e($contentTitle) ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="text-muted small"
                                         style="white-space: normal;">
                                        <?= $e(mb_strimwidth((string) $content, 0, 100, '...')) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= !empty($report['created_at'])
                                        ? $e(date('d/m/Y H:i', strtotime($report['created_at'])))
                                        : '—' ?>
                                </td>

                                <td>
                                    <span class="badge <?= $statusClasses[$status] ?? 'bg-secondary' ?>">
                                        <?= $e($statusLabels[$status] ?? $status) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                        <!-- XEM CHI TIẾT -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportDetailModal"
                                            data-id="<?= $reportId ?>"
                                            data-reporter="<?= $e($report['reporter_name'] ?? '') ?>"
                                            data-type="<?= $e($typeLabels[$contentType] ?? $contentType) ?>"
                                            data-reason="<?= $e($report['reason'] ?? '') ?>"
                                            data-details="<?= $e($report['details'] ?? '') ?>"
                                            data-content="<?= $e($content) ?>"
                                            data-title="<?= $e($contentTitle) ?>"
                                            data-created="<?= $e($report['created_at'] ?? '') ?>"
                                            data-status="<?= $e($statusLabels[$status] ?? $status) ?>"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Xem
                                        </button>

                                        <?php if ($isPending): ?>

                                            <!-- XỬ LÝ VI PHẠM -->
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#resolveReportModal"
                                                data-id="<?= $reportId ?>"
                                                data-content="<?= $e($contentTitle ?: $content) ?>"
                                            >
                                                <i class="bi bi-shield-exclamation"></i>
                                                Xử lý
                                            </button>

                                            <!-- BỎ QUA -->
                                            <form
                                                method="POST"
                                                action="<?= url('?module=admin&action=handleReport') ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn bỏ qua báo cáo này không? Nội dung sẽ không bị thay đổi.');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                       value="<?= $e($csrfToken) ?>">

                                                <input type="hidden" name="report_id"
                                                       value="<?= $reportId ?>">

                                                <input type="hidden" name="action"
                                                       value="dismiss">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-x-circle"></i>
                                                    Bỏ qua
                                                </button>
                                            </form>

                                        <?php endif; ?>

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

<!-- MODAL CHI TIẾT BÁO CÁO -->
<div class="modal fade" id="reportDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-flag text-primary"></i>
                    Chi tiết báo cáo
                </h5>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Mã báo cáo</label>
                        <div class="fw-semibold" id="detailReportId"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Người báo cáo</label>
                        <div class="fw-semibold" id="detailReporter"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Loại nội dung</label>
                        <div id="detailType"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Thời gian</label>
                        <div id="detailCreated"></div>
                    </div>

                    <div class="col-md-6">
                        <label class="text-muted small">Trạng thái</label>
                        <div id="detailStatus"></div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="text-muted small">Lý do báo cáo</label>
                    <div class="fw-semibold text-danger" id="detailReason"></div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Chi tiết từ người báo cáo</label>
                    <div class="p-3 bg-light rounded" id="detailDetails"></div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">Tiêu đề nội dung liên quan</label>
                    <div class="fw-semibold" id="detailContentTitle"></div>
                </div>

                <div>
                    <label class="text-muted small">Nội dung bị báo cáo</label>
                    <div class="p-3 bg-light rounded"
                         style="white-space: pre-wrap;"
                         id="detailContent"></div>
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

<!-- MODAL XỬ LÝ VI PHẠM -->
<div class="modal fade" id="resolveReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=handleReport') ?>">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-shield-exclamation"></i>
                        Xử lý vi phạm
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="report_id"
                           id="resolveReportId">

                    <input type="hidden" name="action"
                           value="resolve">

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Nội dung vi phạm sẽ bị ẩn khỏi hệ thống và báo cáo
                        sẽ chuyển sang trạng thái đã xử lý.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nội dung được xử lý
                        </label>

                        <div class="p-2 bg-light rounded"
                             id="resolveContentTitle"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Ghi chú xử lý <span class="text-danger">*</span>
                        </label>

                        <textarea
                            name="resolution_note"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            placeholder="Nhập lý do ẩn nội dung..."
                            required
                        ></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-check-circle"></i>
                        Xác nhận xử lý
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const detailModal = document.getElementById('reportDetailModal');

    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) return;

            const getData = function (name) {
                return button.getAttribute('data-' + name) || '';
            };

            document.getElementById('detailReportId').textContent =
                '#' + getData('id');

            document.getElementById('detailReporter').textContent =
                getData('reporter');

            document.getElementById('detailType').textContent =
                getData('type');

            document.getElementById('detailReason').textContent =
                getData('reason');

            document.getElementById('detailDetails').textContent =
                getData('details') || 'Không có chi tiết bổ sung.';

            document.getElementById('detailContent').textContent =
                getData('content') || 'Không có nội dung.';

            document.getElementById('detailContentTitle').textContent =
                getData('title') || 'Không có tiêu đề.';

            document.getElementById('detailCreated').textContent =
                getData('created');

            document.getElementById('detailStatus').textContent =
                getData('status');
        });
    }

    const resolveModal = document.getElementById('resolveReportModal');

    if (resolveModal) {
        resolveModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) return;

            document.getElementById('resolveReportId').value =
                button.getAttribute('data-id') || '';

            document.getElementById('resolveContentTitle').textContent =
                button.getAttribute('data-content') || '';
        });
    }

});
</script>
