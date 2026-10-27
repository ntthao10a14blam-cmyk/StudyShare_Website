<?php
/**
 * SHOW.PHP — Chi tiết tài liệu cá nhân
 * Phụ trách: TV2
 */
$pageTitle = $pageTitle ?? $doc['title'];
require_once BASE_PATH . '/app/views/layouts/header.php';

// Trạng thái kiểm duyệt
$statusMap = [
    'approved'      => ['label' => 'Đã duyệt công khai', 'cls' => 'badge-status-approved',      'icon' => 'bi-check-circle-fill', 'alert' => 'alert-success'],
    'pending'       => ['label' => 'Chờ kiểm duyệt',    'cls' => 'badge-status-pending',       'icon' => 'bi-hourglass-split',   'alert' => 'alert-warning'],
    'rejected'      => ['label' => 'Bị từ chối duyệt',  'cls' => 'badge-status-rejected',      'icon' => 'bi-x-circle-fill',     'alert' => 'alert-danger'],
    'hidden'        => ['label' => 'Đã bị ẩn vi phạm',  'cls' => 'badge-status-hidden',        'icon' => 'bi-eye-slash-fill',    'alert' => 'alert-dark'],
    'not_submitted' => ['label' => 'Bản nháp riêng tư', 'cls' => 'badge-status-not_submitted', 'icon' => 'bi-file-lock-fill',   'alert' => 'alert-secondary'],
];
$statusInfo = $statusMap[$doc['moderation_status']] ?? $statusMap['not_submitted'];
?>

<div class="container py-4">

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('?module=documents&action=index') ?>">Tài liệu của tôi</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;" aria-current="page">
                <?= h($doc['title']) ?>
            </li>
        </ol>
    </nav>

    <!-- HEADER: TIÊU ĐỀ VÀ CÁC NÚT TÁC VỤ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge <?= $statusInfo['cls'] ?>">
                    <i class="bi <?= $statusInfo['icon'] ?> me-1"></i><?= $statusInfo['label'] ?>
                </span>
                <span class="badge <?= $doc['visibility'] === 'public' ? 'badge-public' : 'badge-private' ?>">
                    <i class="bi <?= $doc['visibility'] === 'public' ? 'bi-globe' : 'bi-lock' ?> me-1"></i>
                    <?= $doc['visibility'] === 'public' ? 'Công khai' : 'Riêng tư' ?>
                </span>
                <?php if (!empty($doc['subject_name'])): ?>
                    <span class="badge-tag">
                        <i class="bi bi-book me-1"></i><?= h($doc['subject_name']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="page-title fs-4 mb-0"><?= h($doc['title']) ?></h1>
        </div>

        <!-- CỤM NÚT TÁC VỤ -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Tải xuống PDF -->
            <a href="<?= storageUrl($doc['file_path']) . '&download=1' ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-xs">
                <i class="bi bi-download"></i>
                <span>Tải xuống (<?= formatFileSize($doc['file_size']) ?>)</span>
            </a>

            <!-- Sửa tài liệu (nếu không bị ẩn) -->
            <?php if ($doc['moderation_status'] !== 'hidden'): ?>
                <a href="<?= url('?module=documents&action=edit&id=' . $doc['id']) ?>" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-pencil-square"></i>
                    <span>Chỉnh sửa</span>
                </a>
            <?php endif; ?>

            <!-- Gửi duyệt / Thu hồi -->
            <?php if ($doc['moderation_status'] === 'not_submitted' || $doc['moderation_status'] === 'rejected'): ?>
                <form method="POST" action="<?= url('?module=documents&action=submit') ?>" class="d-inline"
                      onsubmit="return confirm('Bạn muốn gửi tài liệu này để Quản trị viên duyệt và hiển thị trên trang cộng đồng?');">
                    <?php csrfField(); ?>
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-send-check"></i>
                        <span>Gửi duyệt</span>
                    </button>
                </form>
            <?php elseif ($doc['moderation_status'] === 'pending' || $doc['moderation_status'] === 'approved'): ?>
                <form method="POST" action="<?= url('?module=documents&action=revoke') ?>" class="d-inline"
                      onsubmit="return confirm('Thu hồi tài liệu về chế độ riêng tư? Tài liệu sẽ không còn hiển thị với cộng đồng.');">
                    <?php csrfField(); ?>
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn btn-outline-warning btn-sm d-inline-flex align-items-center gap-1">
                        <i class="bi bi-lock"></i>
                        <span>Thu hồi</span>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Xóa tài liệu -->
            <button type="button" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#deleteDocModal">
                <i class="bi bi-trash3"></i>
                <span>Xóa</span>
            </button>
        </div>
    </div>

    <!-- KHỐI THÔNG BÁO TRẠNG THÁI KIỂM DUYỆT -->
    <?php if ($doc['moderation_status'] === 'pending'): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 small mb-4 py-2" role="alert">
            <i class="bi bi-hourglass-split fs-5"></i>
            <div>
                <strong>Tài liệu đang trong hàng đợi kiểm duyệt!</strong> Ban quản trị sẽ rà soát nội dung trước khi xuất bản ra trang cộng đồng sinh viên.
            </div>
        </div>
    <?php elseif ($doc['moderation_status'] === 'rejected'): ?>
        <div class="alert alert-danger small mb-4 py-2" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <strong>Tài liệu bị từ chối phê duyệt.</strong>
            </div>
            <?php if (!empty($reviews)): ?>
                <div class="ms-4">
                    <strong>Góp ý từ Ban quản trị:</strong> "<?= h($reviews[0]['reason'] ?? 'Không đạt chuẩn kiểm duyệt.') ?>"
                    <div class="mt-1">
                        <a href="<?= url('?module=documents&action=edit&id=' . $doc['id']) ?>" class="alert-link text-decoration-underline">
                            Chỉnh sửa tài liệu ngay
                        </a> để khắc phục và gửi duyệt lại.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($doc['moderation_status'] === 'hidden'): ?>
        <div class="alert alert-danger small mb-4 py-2" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-x fs-5"></i>
                <div>
                    <strong>Tài liệu đã bị ẩn bởi Quản trị viên.</strong>
                    <?php if (!empty($reviews)): ?>
                        Lý do: "<?= h($reviews[0]['reason'] ?? 'Vi phạm quy chuẩn cộng đồng.') ?>"
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif ($doc['moderation_status'] === 'approved'): ?>
        <div class="alert alert-success d-flex align-items-center justify-content-between gap-2 small mb-4 py-2" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>
                    <strong>Tài liệu đã được phê duyệt công khai!</strong> Mọi sinh viên đều có thể tìm kiếm, xem và tải về từ trang Cộng đồng.
                </div>
            </div>
            <a href="<?= url('?module=community&action=show&id=' . $doc['id']) ?>" class="btn btn-outline-success btn-sm py-0 px-2">
                Xem trên Cộng đồng <i class="bi bi-box-arrow-up-right ms-1"></i>
            </a>
        </div>
    <?php endif; ?>

    <!-- NỘI DUNG CHÍNH (2 CỘT) -->
    <div class="row g-4">
        
        <!-- CỘT TRÁI: XEM TRƯỚC VÀ NỘI DUNG MÔ TẢ -->
        <div class="col-12 col-lg-8">
            
            <!-- KHUNG PREVIEW FILE PDF -->
            <div class="card community-card mb-4 overflow-hidden">
                <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
                    <span class="small fw-semibold text-dark">
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i> Xem trước tài liệu PDF
                    </span>
                    <a href="<?= storageUrl($doc['file_path']) ?>" target="_blank" class="small text-decoration-none">
                        Mở toàn màn hình <i class="bi bi-arrows-fullscreen ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0 bg-dark" style="height: 600px;">
                    <iframe src="<?= storageUrl($doc['file_path']) ?>#toolbar=1"
                            class="w-100 h-100 border-0" title="Xem trước tài liệu PDF">
                        <div class="p-4 text-center text-white">
                            Trình duyệt không hỗ trợ xem trước PDF trực tiếp.<br>
                            <a href="<?= storageUrl($doc['file_path']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                Nhấn vào đây để xem hoặc tải về
                            </a>
                        </div>
                    </iframe>
                </div>
            </div>

            <!-- ẢNH MINH HOẠ NẾU CÓ -->
            <?php if (!empty($images)): ?>
                <div class="card community-card p-3 mb-4">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-images me-1 text-primary"></i> Ảnh trang xem trước (<?= count($images) ?> ảnh)
                    </h6>
                    <div class="row g-2">
                        <?php foreach ($images as $img): ?>
                            <div class="col-6 col-md-4 col-xl-3">
                                <a href="<?= storageUrl($img['image_path']) ?>" target="_blank" class="d-block border rounded overflow-hidden shadow-xs hover-primary" style="height: 140px;">
                                    <img src="<?= storageUrl($img['image_path']) ?>" alt="Ảnh minh hoạ" class="w-100 h-100 object-fit-cover">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- MÔ TẢ TÀI LIỆU -->
            <div class="card community-card p-4">
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-card-text me-1 text-primary"></i> Mô tả nội dung tài liệu
                </h6>
                <?php if (!empty($doc['description'])): ?>
                    <div class="text-secondary" style="line-height: 1.7; white-space: pre-line;">
                        <?= h($doc['description']) ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted small fst-italic mb-0">Chưa có thông tin mô tả chi tiết cho tài liệu này.</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- CỘT PHẢI: THÔNG SỐ CHI TIẾT VÀ LỊCH SỬ DUYỆT -->
        <div class="col-12 col-lg-4">
            
            <!-- THẺ THÔNG TIN TÀI LIỆU -->
            <div class="card community-card p-3 mb-3">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                    <i class="bi bi-info-circle me-1 text-primary"></i> Thông số tệp tin
                </h6>
                <dl class="row small mb-0">
                    <dt class="col-5 text-muted fw-normal">Tên file gốc:</dt>
                    <dd class="col-7 text-dark fw-semibold text-truncate" title="<?= h($doc['original_file_name']) ?>">
                        <?= h($doc['original_file_name']) ?>
                    </dd>

                    <dt class="col-5 text-muted fw-normal">Định dạng:</dt>
                    <dd class="col-7 text-dark fw-semibold">PDF Document</dd>

                    <dt class="col-5 text-muted fw-normal">Dung lượng:</dt>
                    <dd class="col-7 text-dark fw-semibold"><?= formatFileSize($doc['file_size']) ?></dd>

                    <dt class="col-5 text-muted fw-normal">Môn học:</dt>
                    <dd class="col-7 text-dark fw-semibold"><?= h($doc['subject_name'] ?? 'Chưa phân loại') ?></dd>

                    <dt class="col-5 text-muted fw-normal">Lượt tải về:</dt>
                    <dd class="col-7 text-dark fw-bold text-success"><?= number_format($doc['download_count']) ?> lượt</dd>

                    <dt class="col-5 text-muted fw-normal">Ngày tải lên:</dt>
                    <dd class="col-7 text-dark"><?= formatDateTime($doc['created_at']) ?></dd>

                    <?php if (!empty($doc['updated_at']) && $doc['updated_at'] !== $doc['created_at']): ?>
                        <dt class="col-5 text-muted fw-normal">Cập nhật lúc:</dt>
                        <dd class="col-7 text-dark"><?= formatDateTime($doc['updated_at']) ?></dd>
                    <?php endif; ?>

                    <?php if (!empty($doc['published_at'])): ?>
                        <dt class="col-5 text-muted fw-normal">Duyệt lúc:</dt>
                        <dd class="col-7 text-dark"><?= formatDateTime($doc['published_at']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- THẺ TỪ KHOÁ (TAGS) -->
            <div class="card community-card p-3 mb-3">
                <h6 class="fw-bold text-dark mb-2 pb-2 border-bottom">
                    <i class="bi bi-tags me-1 text-primary"></i> Từ khoá (Tags)
                </h6>
                <?php if (!empty($tags)): ?>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge-tag"># <?= h($tag['name']) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted small fst-italic">Chưa gắn từ khoá.</div>
                <?php endif; ?>
            </div>

            <!-- THẺ LỊCH SỬ KIỂM DUYỆT CỦA ADMIN (NẾU CÓ) -->
            <?php if (!empty($reviews)): ?>
                <div class="card community-card p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-2 pb-2 border-bottom">
                        <i class="bi bi-clipboard-check me-1 text-primary"></i> Lịch sử kiểm duyệt
                    </h6>
                    <ul class="list-unstyled mb-0 small">
                        <?php foreach ($reviews as $rev): ?>
                            <li class="pb-2 mb-2 border-bottom border-light-subtle">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold text-capitalize text-<?= $rev['action'] === 'approved' ? 'success' : 'danger' ?>">
                                        <?= $rev['action'] === 'approved' ? 'Phê duyệt' : ($rev['action'] === 'rejected' ? 'Từ chối' : 'Ẩn vi phạm') ?>
                                    </span>
                                    <span class="text-muted"><?= formatDateTime($rev['created_at'], 'd/m H:i') ?></span>
                                </div>
                                <div class="text-muted small">Bởi Admin: <?= h($rev['admin_name'] ?? 'Ban Quản Trị') ?></div>
                                <?php if (!empty($rev['reason'])): ?>
                                    <div class="p-2 mt-1 rounded bg-light text-dark fst-italic">
                                        "<?= h($rev['reason']) ?>"
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- LIÊN KẾT NHANH -->
            <div class="d-grid gap-2">
                <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Tải lên thêm tài liệu khác
                </a>
            </div>

        </div>

    </div>

</div>

<!-- MODAL XÁC NHẬN XÓA TÀI LIỆU -->
<div class="modal fade" id="deleteDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Xóa tài liệu này?
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body small py-2">
                Bạn có chắc chắn muốn xóa tài liệu <strong>"<?= h($doc['title']) ?>"</strong>?<br>
                Tài liệu sẽ được chuyển vào thùng rác và không còn truy cập được.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light-subtle btn-sm" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="<?= url('?module=documents&action=delete') ?>">
                    <?php csrfField(); ?>
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Xóa vĩnh viễn</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
