<?php
$stats = $stats ?? [];

$totalUsers = (int) ($stats['users'] ?? $stats['totalUsers'] ?? 0);
$totalDocuments = (int) ($stats['documents'] ?? $stats['publicDocuments'] ?? 0);
$totalPending = (int) ($stats['pendingDocuments'] ?? $stats['pending'] ?? 0);
$totalDownloads = (int) ($stats['downloads'] ?? 0);
$totalQuestions = (int) ($stats['questions'] ?? 0);
$totalReports = (int) ($stats['pendingReports'] ?? 0);

$latestPendingDocs = $latestPendingDocs ?? $pendingDocuments ?? [];
$latestReports = $latestReports ?? $reports ?? [];

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};
?>

<div class="admin-page">
    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
                <p>Tổng quan hoạt động của hệ thống StudyShare</p>
            </div>

            <span class="badge bg-primary p-2">
                <i class="bi bi-shield-check"></i>
                Quản trị viên
            </span>
        </div>

        <!-- THỐNG KÊ -->
        <div class="row g-4 mb-4">

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p>Người dùng hoạt động</p>
                        <h3><?= number_format($totalUsers) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-success-subtle text-success">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                    <div>
                        <p>Tài liệu công khai</p>
                        <h3><?= number_format($totalDocuments) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <p>Tài liệu chờ duyệt</p>
                        <h3><?= number_format($totalPending) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-info-subtle text-info">
                        <i class="bi bi-download"></i>
                    </div>
                    <div>
                        <p>Tổng lượt tải</p>
                        <h3><?= number_format($totalDownloads) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-question-circle-fill"></i>
                    </div>
                    <div>
                        <p>Tổng câu hỏi</p>
                        <h3><?= number_format($totalQuestions) ?></h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="admin-stat-card">
                    <div class="admin-stat-icon bg-secondary-subtle text-secondary">
                        <i class="bi bi-flag-fill"></i>
                    </div>
                    <div>
                        <p>Báo cáo chờ xử lý</p>
                        <h3><?= number_format($totalReports) ?></h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- TÀI LIỆU CHỜ DUYỆT -->
        <div class="admin-panel mb-4">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-file-earmark-text"></i>
                        Tài liệu chờ duyệt
                    </h5>
                    <p>5 tài liệu được gửi duyệt gần đây nhất</p>
                </div>

                <a href="<?= url('?module=admin&action=pendingDocs') ?>"
                   class="btn btn-primary btn-sm">
                    Xem tất cả
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tên tài liệu</th>
                            <th>Người đăng</th>
                            <th>Môn học</th>
                            <th>Ngày gửi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($latestPendingDocs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                                Không có tài liệu nào đang chờ duyệt.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($latestPendingDocs, 0, 5) as $index => $doc): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td>
                                    <strong><?= $e($doc['title'] ?? 'Không có tiêu đề') ?></strong>
                                </td>

                                <td><?= $e($doc['owner_name'] ?? 'Không rõ') ?></td>

                                <td><?= $e($doc['subject_name'] ?? 'Chưa phân loại') ?></td>

                                <td>
                                    <?= !empty($doc['submitted_at'])
                                        ? $e(date('d/m/Y H:i', strtotime($doc['submitted_at'])))
                                        : '—' ?>
                                </td>

                                <td>
                                    <a href="<?= url('?module=admin&action=pendingDocs') ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                        Xem và duyệt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BÁO CÁO MỚI NHẤT -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-flag"></i>
                        Báo cáo gần đây
                    </h5>
                    <p>Các báo cáo vi phạm mới nhất từ người dùng</p>
                </div>

                <a href="<?= url('?module=admin&action=reports') ?>"
                   class="btn btn-outline-primary btn-sm">
                    Xem tất cả
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Người báo cáo</th>
                            <th>Loại nội dung</th>
                            <th>Lý do</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($latestReports)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Chưa có báo cáo nào.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($latestReports, 0, 5) as $index => $report): ?>
                            <?php
                                $status = $report['status'] ?? 'pending';

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
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>

                                <td><?= $e($report['reporter_name'] ?? 'Không rõ') ?></td>

                                <td><?= $e($report['content_type'] ?? 'Nội dung') ?></td>

                                <td><?= $e($report['reason'] ?? '') ?></td>

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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>
