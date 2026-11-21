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

        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-flag-fill"></i>
                    Báo cáo vi phạm
                </h1>
                <p>Kiểm tra và xử lý các nội dung bị người dùng báo cáo</p>
            </div>
        </div>

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

        <div class="admin-panel p-4 mb-4">
            <form method="GET" action="" class="row g-3 align-items-end">

                <input type="hidden" name="module" value="admin">
                <input type="hidden" name="action" value="reports">

                <div class="col-lg-8 col-md-7">
                    <label for="status" class="form-label fw-semibold mb-2">
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

                <div class="col-lg-4 col-md-5 d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-funnel"></i>
                        Lọc
                    </button>

                    <a href="<?= url('?module=admin&action=reports') ?>"
                       class="btn btn-outline-secondary px-3">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Đặt lại
                    </a>
                </div>

            </form>
        </div>

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

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reportDetailModal"
                                            data-id="<?= $reportId ?>"
                                            data-reporter="<?= $e($report['reporter_name'] ?? '') ?>"
                                            data-content-type="<?= $e($contentType) ?>"
                                            data-type="<?= $e($typeLabels[$contentType] ?? $contentType) ?>"
                                            data-reason="<?= $e($report['reason'] ?? '') ?>"
                                            data-details="<?= $e($report['details'] ?? '') ?>"
                                            data-content="<?= $e($content) ?>"
                                            data-title="<?= $e($contentTitle) ?>"
                                            data-doc-id="<?= (int) ($report['target_document_id'] ?? $report['document_id'] ?? 0) ?>"
                                            data-doc-title="<?= $e($report['document_title'] ?? '') ?>"
                                            data-doc-filename="<?= $e($report['doc_file_name'] ?? '') ?>"
                                            data-doc-size="<?= (int) ($report['doc_file_size'] ?? 0) ?>"
                                            data-doc-owner="<?= $e($report['doc_owner_name'] ?? '') ?>"
                                            data-doc-subject="<?= $e($report['doc_subject_name'] ?? '') ?>"
                                            data-doc-desc="<?= $e($report['doc_description'] ?? '') ?>"
                                            data-created="<?= !empty($report['created_at']) ? $e(date('d/m/Y H:i', strtotime($report['created_at']))) : '—' ?>"
                                            data-status="<?= $e($statusLabels[$status] ?? $status) ?>"
                                            data-handler="<?= $e($report['handler_name'] ?? '') ?>"
                                            data-note="<?= $e($report['resolution_note'] ?? '') ?>"
                                            data-handled-at="<?= !empty($report['handled_at']) ? $e(date('d/m/Y H:i', strtotime($report['handled_at']))) : '' ?>"
                                        >
                                            <i class="bi bi-eye"></i>
                                            Xem
                                        </button>

                                        <?php if ($isPending): ?>

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

                                                <input type="hidden" name="status"
                                                       value="dismissed">

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

<div class="modal fade" id="reportDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
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
                <div class="row g-4">

                    <div id="detailLeftCol" class="col-lg-5">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded border h-100">
                                    <label class="text-muted small d-block">Mã báo cáo</label>
                                    <div class="fw-semibold" id="detailReportId"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2 bg-light rounded border h-100">
                                    <label class="text-muted small d-block">Trạng thái</label>
                                    <div class="fw-semibold" id="detailStatus"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2 bg-light rounded border h-100">
                                    <label class="text-muted small d-block">Người báo cáo</label>
                                    <div class="fw-semibold" id="detailReporter"></div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2 bg-light rounded border h-100">
                                    <label class="text-muted small d-block">Loại nội dung</label>
                                    <div id="detailType"></div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-2 bg-light rounded border">
                                    <label class="text-muted small d-block">Thời gian báo cáo</label>
                                    <div id="detailCreated"></div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded border border-danger-subtle bg-danger-subtle mb-3">
                            <label class="text-danger small fw-semibold d-block mb-1">
                                <i class="bi bi-exclamation-circle me-1"></i>Lý do bị báo cáo
                            </label>
                            <div class="fw-bold text-danger mb-2" id="detailReason"></div>
                            <label class="text-muted small d-block mb-1">Chi tiết từ người báo cáo</label>
                            <div class="small bg-white p-2 rounded border" id="detailDetails"></div>
                        </div>

                        <div id="detailFileInfoBox" class="mb-3 d-none">
                            <label class="text-muted small fw-semibold d-block mb-2">
                                <i class="bi bi-file-earmark-text me-1"></i>Thông tin tài liệu &amp; file
                            </label>
                            <div class="p-3 bg-light rounded border">
                                <div class="mb-2">
                                    <span class="text-muted small d-block">Tên tài liệu</span>
                                    <strong id="detailDocTitle" class="text-break"></strong>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <span class="text-muted small d-block">Người đăng</span>
                                        <span id="detailDocOwner" class="fw-semibold small"></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small d-block">Môn học</span>
                                        <span id="detailDocSubject" class="fw-semibold small"></span>
                                    </div>
                                    <div class="col-7">
                                        <span class="text-muted small d-block">Tên file</span>
                                        <span id="detailDocFilename" class="small text-break"></span>
                                    </div>
                                    <div class="col-5">
                                        <span class="text-muted small d-block">Dung lượng</span>
                                        <span id="detailDocSize" class="fw-semibold small"></span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-muted small d-block">Mô tả tài liệu</span>
                                    <div id="detailDocDesc" class="small text-muted" style="max-height: 110px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>

                        <div id="detailTextContentBox">
                            <div class="mb-2">
                                <label class="text-muted small">Tiêu đề nội dung liên quan</label>
                                <div class="fw-semibold" id="detailContentTitle"></div>
                            </div>

                            <div>
                                <label class="text-muted small">Nội dung bị báo cáo</label>
                                <div class="p-3 bg-light rounded border small"
                                     style="white-space: pre-wrap;"
                                     id="detailContent"></div>
                            </div>
                        </div>

                        <div id="detailResolutionBox" class="mt-3 p-3 rounded border bg-light-subtle d-none">
                            <label class="text-muted small fw-semibold d-block mb-1">Kết quả xử lý</label>
                            <div class="small mb-1">
                                <strong>Người xử lý:</strong> <span id="detailHandler"></span>
                                <span class="text-muted ms-2" id="detailHandledAt"></span>
                            </div>
                            <div class="small" id="detailResolutionNote"></div>
                        </div>
                    </div>

                    <div id="detailPreviewCol" class="col-lg-7 d-flex flex-column d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-muted small fw-semibold mb-0">
                                <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                Xem trước file tài liệu
                            </label>
                            <a id="reportOpenNewTab" href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right"></i>
                                Mở tab mới
                            </a>
                        </div>

                        <div class="border rounded bg-light overflow-hidden flex-grow-1" style="min-height: 480px;">
                            <iframe id="reportPreviewFrame"
                                    src="about:blank"
                                    title="Xem trước file bị báo cáo"
                                    style="width: 100%; height: 480px; border: 0; display: block;"></iframe>
                        </div>
                    </div>
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

                    <input type="hidden" name="status"
                           value="resolved">

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
    const previewBaseUrl = <?= json_encode(url('?module=admin&action=previewDoc&id='), JSON_UNESCAPED_SLASHES) ?>;

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

            const contentType = getData('content-type');
            const docId = parseInt(getData('doc-id'), 10) || 0;
            const leftCol = document.getElementById('detailLeftCol');
            const previewCol = document.getElementById('detailPreviewCol');
            const fileInfoBox = document.getElementById('detailFileInfoBox');
            const textContentBox = document.getElementById('detailTextContentBox');
            const frame = document.getElementById('reportPreviewFrame');
            const newTab = document.getElementById('reportOpenNewTab');

            if (contentType === 'document' && docId > 0) {
                if (leftCol) {
                    leftCol.classList.remove('col-lg-12');
                    leftCol.classList.add('col-lg-5');
                }
                if (fileInfoBox) fileInfoBox.classList.remove('d-none');
                if (textContentBox) textContentBox.classList.add('d-none');
                if (previewCol) previewCol.classList.remove('d-none');

                document.getElementById('detailDocTitle').textContent =
                    getData('doc-title') || getData('content') || 'Không có tiêu đề';
                document.getElementById('detailDocOwner').textContent =
                    getData('doc-owner') || 'Không rõ';
                document.getElementById('detailDocSubject').textContent =
                    getData('doc-subject') || 'Chưa phân loại';
                document.getElementById('detailDocFilename').textContent =
                    getData('doc-filename') || 'Không rõ';
                const size = parseInt(getData('doc-size'), 10) || 0;
                document.getElementById('detailDocSize').textContent =
                    size > 0 ? (size / 1024 / 1024).toFixed(2) + ' MB' : 'Không rõ';
                document.getElementById('detailDocDesc').textContent =
                    getData('doc-desc') || 'Chưa có mô tả.';

                const previewUrl = previewBaseUrl + encodeURIComponent(docId);
                if (frame) frame.src = previewUrl;
                if (newTab) newTab.href = previewUrl;
            } else {
                if (leftCol) {
                    leftCol.classList.remove('col-lg-5');
                    leftCol.classList.add('col-lg-12');
                }
                if (fileInfoBox) fileInfoBox.classList.add('d-none');
                if (textContentBox) textContentBox.classList.remove('d-none');
                if (previewCol) previewCol.classList.add('d-none');
                if (frame) frame.src = 'about:blank';
            }

            const resBox = document.getElementById('detailResolutionBox');
            const handler = getData('handler');
            const note = getData('note');
            const handledAt = getData('handled-at');
            if (resBox) {
                if (handler || note) {
                    resBox.classList.remove('d-none');
                    document.getElementById('detailHandler').textContent = handler || 'Quản trị viên';
                    document.getElementById('detailHandledAt').textContent = handledAt ? ('· ' + handledAt) : '';
                    document.getElementById('detailResolutionNote').textContent = note || 'Không có ghi chú.';
                } else {
                    resBox.classList.add('d-none');
                }
            }
        });

        detailModal.addEventListener('hidden.bs.modal', function () {
            const frame = document.getElementById('reportPreviewFrame');
            if (frame) frame.src = 'about:blank';
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
