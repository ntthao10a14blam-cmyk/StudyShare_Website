<?php
$pageTitle = $pageTitle ?? 'Bảng điều khiển Admin';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">

    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>Bảng điều khiển</h1>
                <p>Quản lý và theo dõi hoạt động của StudyShare.</p>
            </div>
        </div>


        <!-- THỐNG KÊ -->
        <div class="stats-grid">

            <!-- TỔNG TÀI LIỆU -->
            <div class="stat-card">
                <div class="stat-icon">📄</div>

                <div>
                    <span>Tổng tài liệu</span>

                    <strong>
                        <?= number_format($stats['documents']) ?>
                    </strong>
                </div>
            </div>


            <!-- NGƯỜI DÙNG -->
            <div class="stat-card">
                <div class="stat-icon">👥</div>

                <div>
                    <span>Người dùng</span>

                    <strong>
                        <?= number_format($stats['users']) ?>
                    </strong>
                </div>
            </div>


            <!-- MÔN HỌC -->
            <div class="stat-card">
                <div class="stat-icon">📚</div>

                <div>
                    <span>Môn học</span>

                    <strong>
                        <?= number_format($stats['subjects']) ?>
                    </strong>
                </div>
            </div>


            <!-- CHỜ DUYỆT -->
            <div class="stat-card">
                <div class="stat-icon">⏳</div>

                <div>
                    <span>Chờ duyệt</span>

                    <strong>
                        <?= number_format($stats['pending']) ?>
                    </strong>
                </div>
            </div>


            <!-- LƯỢT TẢI -->
            <div class="stat-card">
                <div class="stat-icon">⬇️</div>

                <div>
                    <span>Lượt tải</span>

                    <strong>
                        <?= number_format($stats['downloads']) ?>
                    </strong>
                </div>
            </div>


            <!-- CÂU HỎI -->
            <div class="stat-card">
                <div class="stat-icon">💬</div>

                <div>
                    <span>Câu hỏi</span>

                    <strong>
                        <?= number_format($stats['questions']) ?>
                    </strong>
                </div>
            </div>

        </div>


        <!-- TÀI LIỆU CHỜ DUYỆT -->
        <section class="admin-section">

            <div class="section-header">

                <div>
                    <h2>Tài liệu chờ duyệt</h2>

                    <p>
                        Các tài liệu mới được người dùng tải lên và đang chờ quản trị viên xử lý.
                    </p>
                </div>

                <a href="?module=admin&action=pendingDocs" class="view-all">
                    Xem tất cả →
                </a>

            </div>


            <div class="table-card">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Tài liệu</th>
                            <th>Môn học</th>
                            <th>Người đăng</th>
                            <th>Ngày gửi</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>


                    <tbody>

                    <?php if (empty($pendingDocuments)): ?>

                        <tr>
                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="bi bi-check-circle"></i>

                                    <h3>
                                        Không có tài liệu chờ duyệt
                                    </h3>

                                    <p>
                                        Hiện tại không có tài liệu nào đang chờ xử lý.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($pendingDocuments as $document): ?>

                            <tr>

                                <!-- TÊN TÀI LIỆU -->
                                <td>
                                    <strong>
                                        <?= h($document['title'] ?? 'Không có tên') ?>
                                    </strong>
                                </td>


                                <!-- MÔN HỌC -->
                                <td>
                                    <?= h($document['subject_name'] ?? '—') ?>
                                </td>


                                <!-- NGƯỜI ĐĂNG -->
                                <td>
                                    <?= h($document['owner_name'] ?? '—') ?>
                                </td>


                                <!-- NGÀY GỬI -->
                                <td>

                                    <?=
                                        !empty($document['submitted_at'])
                                            ? date(
                                                'd/m/Y',
                                                strtotime($document['submitted_at'])
                                            )
                                            : '—'
                                    ?>

                                </td>


                                <!-- TRẠNG THÁI -->
                                <td>

                                    <span class="status pending">
                                        Chờ duyệt
                                    </span>

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