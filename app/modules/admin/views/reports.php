<?php
$pageTitle = $pageTitle ?? 'Quản lý báo cáo';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">
    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>Báo cáo</h1>
                <p>Kiểm tra và xử lý các nội dung được người dùng báo cáo.</p>
            </div>
        </div>

        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Báo cáo đang chờ xử lý</h2>
                    <p>Các nội dung cần quản trị viên kiểm tra.</p>
                </div>
            </div>

            <div class="table-card">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Nội dung</th>
                            <th>Người báo cáo</th>
                            <th>Lý do</th>
                            <th>Ngày báo cáo</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($reports)): ?>

                        <tr>
                            <td colspan="6">

                                <div class="empty-state">
                                    <i class="bi bi-flag"></i>
                                    <h3>Không có báo cáo</h3>
                                    <p>Hiện tại không có báo cáo nào cần xử lý.</p>
                                </div>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($reports as $report): ?>

                            <?php
                            $reportId = (int) ($report['id'] ?? 0);

                            $status = $report['status'] ?? 'pending';

                            $documentId = (int) ($report['document_id'] ?? 0);
                            $questionId = (int) ($report['question_id'] ?? 0);
                            $answerId = (int) ($report['answer_id'] ?? 0);
                            $commentId = (int) ($report['comment_id'] ?? 0);

                            if ($documentId > 0) {
                                $contentType = 'document';
                                $contentId = $documentId;
                            } elseif ($questionId > 0) {
                                $contentType = 'question';
                                $contentId = $questionId;
                            } elseif ($answerId > 0) {
                                $contentType = 'answer';
                                $contentId = $answerId;
                            } elseif ($commentId > 0) {
                                $contentType = 'comment';
                                $contentId = $commentId;
                            } else {
                                $contentType = '';
                                $contentId = 0;
                            }

                            $contentTitle =
                                $report['content_title']
                                ?? $report['title']
                                ?? 'Nội dung được báo cáo';

                            $reporterName =
                                $report['reporter_name']
                                ?? $report['full_name']
                                ?? 'Không xác định';
                            ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= h($contentTitle) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= h($reporterName) ?>
                                </td>

                                <td>
                                    <?= h($report['reason'] ?? '—') ?>
                                </td>

                                <td>
                                    <?=
                                        !empty($report['created_at'])
                                            ? date(
                                                'd/m/Y',
                                                strtotime($report['created_at'])
                                            )
                                            : '—'
                                    ?>
                                </td>

                                <td>

                                    <?php if ($status === 'pending'): ?>

                                        <span class="status pending">
                                            Chờ xử lý
                                        </span>

                                    <?php elseif ($status === 'resolved'): ?>

                                        <span class="status approved">
                                            Đã xử lý
                                        </span>

                                    <?php else: ?>

                                        <span class="status rejected">
                                            Đã bỏ qua
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($status === 'pending'): ?>

                                        <!-- Xử lý báo cáo: Đã giải quyết -->
                                        <form
                                            method="POST"
                                            action="?module=admin&action=handleReport"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h(csrfToken()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="report_id"
                                                value="<?= $reportId ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="resolved"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-small"
                                                data-confirm="Bạn có chắc muốn đánh dấu báo cáo này là đã xử lý?"
                                            >
                                                Xử lý
                                            </button>

                                        </form>

                                        <!-- Bỏ qua báo cáo -->
                                        <form
                                            method="POST"
                                            action="?module=admin&action=handleReport"
                                            style="display:inline;"
                                        >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= h(csrfToken()) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="report_id"
                                                value="<?= $reportId ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="dismissed"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-small"
                                                data-confirm="Bạn có chắc muốn bỏ qua báo cáo này?"
                                            >
                                                Bỏ qua
                                            </button>

                                        </form>

                                        <?php if ($contentId > 0 && in_array(
                                            $contentType,
                                            ['question', 'answer', 'comment'],
                                            true
                                        )): ?>

                                            <!-- Ẩn nội dung -->
                                            <form
                                                method="POST"
                                                action="?module=admin&action=hideContent"
                                                style="display:inline;"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= h(csrfToken()) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="type"
                                                    value="<?= h($contentType) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $contentId ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-reject"
                                                    data-confirm="Bạn có chắc muốn ẩn nội dung này?"
                                                >
                                                    Ẩn
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Đã xử lý
                                        </span>

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