<?php
$pageTitle = $pageTitle ?? 'Tài liệu chờ duyệt';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="admin-page">

    <div class="admin-content">

        <div class="page-header">
            <div>
                <h1>Duyệt tài liệu</h1>
                <p>Kiểm tra và xử lý các tài liệu đang chờ duyệt.</p>
            </div>
        </div>

        <section class="admin-section">

            <div class="section-header">
                <div>
                    <h2>Tài liệu chờ duyệt</h2>
                    <p>Các tài liệu được người dùng gửi lên hệ thống.</p>
                </div>
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
                            <th>Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($documents)): ?>

                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-check-circle"></i>
                                    <h3>Không có tài liệu chờ duyệt</h3>
                                    <p>Tất cả tài liệu hiện đã được xử lý.</p>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($documents as $document): ?>

                            <tr>

                                <td>
                                    <strong>
                                        <?= h($document['title'] ?? 'Không có tên') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= h($document['subject_name'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= h($document['owner_name'] ?? '—') ?>
                                </td>

                                <td>
                                    <?= !empty($document['submitted_at'])
                                        ? date('d/m/Y', strtotime($document['submitted_at']))
                                        : '—'
                                    ?>
                                </td>

                                <td>
                                    <span class="status pending">
                                        Chờ duyệt
                                    </span>
                                </td>

                                <td>

                                    <div class="d-flex gap-2">

                                        <!-- DUYỆT -->
                                        <form method="POST"action="?module=admin&action=reviewDoc">

                                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

                                            <input type="hidden"
                                                   name="document_id"
                                                   value="<?= (int) $document['id'] ?>">

                                            <input type="hidden"
                                                   name="action"
                                                   value="approved">

                                            <button type="submit"
                                                    class="btn-approve"
                                                    data-confirm="Bạn có chắc muốn duyệt tài liệu này?">

                                                <i class="bi bi-check-circle"></i>
                                                Duyệt

                                            </button>

                                        </form>


                                        <!-- TỪ CHỐI -->
                                        <form method="POST"
                                              action="?module=admin&action=reviewDoc">

                                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

                                            <input type="hidden"
                                                   name="document_id"
                                                   value="<?= (int) $document['id'] ?>">

                                            <input type="hidden"
                                                   name="action"
                                                   value="rejected">

                                            <input type="text"
                                                   name="reason"
                                                   placeholder="Lý do từ chối"
                                                   required
                                                   class="form-control form-control-sm">

                                            <button type="submit"
                                                    class="btn-reject"
                                                    data-confirm="Bạn có chắc muốn từ chối tài liệu này?">

                                                <i class="bi bi-x-circle"></i>
                                                Từ chối

                                            </button>

                                        </form>

                                    </div>

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