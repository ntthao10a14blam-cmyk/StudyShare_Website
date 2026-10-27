<?php
/**
 * INDEX.PHP — Danh sách tài liệu cá nhân
 * Phụ trách: TV2
 */
$pageTitle = $pageTitle ?? 'Tài liệu của tôi';
require_once BASE_PATH . '/app/views/layouts/header.php';

// Giữ lại các tham số query khi phân trang
$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
$paginationBaseUrl = url('?' . ($queryString ? $queryString . '&' : '') . 'page=');
?>

<div class="container py-4">

    <!-- 1. TIÊU ĐỀ TRANG VÀ NÚT TẢI LÊN -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Tài liệu của tôi</h1>
            <p class="text-muted small mb-0">Quản lý kho tài liệu, theo dõi trạng thái duyệt và chia sẻ học tập.</p>
        </div>
        <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-xs">
            <i class="bi bi-cloud-arrow-up-fill fs-6"></i>
            <span>Tải lên tài liệu mới</span>
        </a>
    </div>

    <!-- 2. THỐNG KÊ NHANH CỦA CÁ NHÂN -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-box h-100">
                <div class="stat-icon">
                    <i class="bi bi-files"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng tài liệu</div>
                    <div class="fs-4 fw-bold text-dark"><?= number_format($stats['total'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-box h-100">
                <div class="stat-icon" style="background-color: #e0f2fe; color: #0284c7;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <div class="text-muted small">Đã duyệt công khai</div>
                    <div class="fs-4 fw-bold text-success"><?= number_format($stats['approved'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-box h-100">
                <div class="stat-icon" style="background-color: #fef3c7; color: #d97706;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="text-muted small">Chờ duyệt</div>
                    <div class="fs-4 fw-bold text-warning"><?= number_format($stats['pending'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-box h-100">
                <div class="stat-icon" style="background-color: #f1f5f9; color: #475569;">
                    <i class="bi bi-download"></i>
                </div>
                <div>
                    <div class="text-muted small">Lượt tải về</div>
                    <div class="fs-4 fw-bold text-dark"><?= number_format($stats['downloads'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. THANH TÌM KIẾM VÀ BỘ LỌC DỮ LIỆU -->
    <div class="card community-card p-3 mb-4">
        <form method="GET" action="<?= url('/') ?>" class="row g-2 align-items-center">
            <input type="hidden" name="module" value="documents">
            <input type="hidden" name="action" value="index">

            <!-- Ô tìm kiếm từ khoá -->
            <div class="col-12 col-md-4">
                <div class="community-search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" name="q" class="form-control form-control-sm"
                           placeholder="Tìm kiếm tài liệu..."
                           value="<?= h(get('q', '')) ?>">
                </div>
            </div>

            <!-- Lọc môn học -->
            <div class="col-6 col-md-2">
                <select name="subject_id" class="form-select form-select-sm">
                    <option value="">Tất cả môn học</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= get('subject_id') == $s['id'] ? 'selected' : '' ?>>
                            <?= h($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Lọc trạng thái duyệt -->
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="approved" <?= get('status') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                    <option value="pending" <?= get('status') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                    <option value="not_submitted" <?= get('status') === 'not_submitted' ? 'selected' : '' ?>>Chưa gửi duyệt</option>
                    <option value="rejected" <?= get('status') === 'rejected' ? 'selected' : '' ?>>Bị từ chối</option>
                    <option value="hidden" <?= get('status') === 'hidden' ? 'selected' : '' ?>>Bị ẩn</option>
                </select>
            </div>

            <!-- Lọc hiển thị -->
            <div class="col-6 col-md-2">
                <select name="visibility" class="form-select form-select-sm">
                    <option value="all">Tất cả hiển thị</option>
                    <option value="public" <?= get('visibility') === 'public' ? 'selected' : '' ?>>Công khai</option>
                    <option value="private" <?= get('visibility') === 'private' ? 'selected' : '' ?>>Riêng tư</option>
                </select>
            </div>

            <!-- Sắp xếp & Nút lọc -->
            <div class="col-6 col-md-2 d-flex gap-1">
                <select name="sort" class="form-select form-select-sm">
                    <option value="newest" <?= get('sort') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="oldest" <?= get('sort') === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                    <option value="downloads" <?= get('sort') === 'downloads' ? 'selected' : '' ?>>Lượt tải</option>
                    <option value="title" <?= get('sort') === 'title' ? 'selected' : '' ?>>Tiêu đề A-Z</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm px-3" title="Lọc dữ liệu">
                    <i class="bi bi-funnel"></i>
                </button>
                <?php if (!empty($_GET['q']) || !empty($_GET['subject_id']) || (isset($_GET['status']) && $_GET['status'] !== 'all') || (isset($_GET['visibility']) && $_GET['visibility'] !== 'all')): ?>
                    <a href="<?= url('?module=documents&action=index') ?>" class="btn btn-light-subtle btn-sm" title="Đặt lại bộ lọc">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 4. DANH SÁCH TÀI LIỆU -->
    <?php if (empty($documents)): ?>
        <div class="card community-card text-center py-5">
            <div class="card-body">
                <div class="mb-3 text-muted">
                    <i class="bi bi-folder-x" style="font-size: 3.5rem; opacity: 0.5;"></i>
                </div>
                <h5 class="fw-semibold text-dark">Chưa có tài liệu nào phù hợp</h5>
                <p class="text-muted small mx-auto" style="max-width: 420px;">
                    Bạn chưa tải lên tài liệu nào hoặc không có tài liệu nào khớp với bộ lọc tìm kiếm hiện tại.
                </p>
                <div class="mt-3">
                    <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Tải lên tài liệu đầu tiên
                    </a>
                    <?php if (!empty($_GET['q']) || !empty($_GET['subject_id'])): ?>
                        <a href="<?= url('?module=documents&action=index') ?>" class="btn btn-outline-secondary btn-sm ms-2">
                            Xóa bộ lọc tìm kiếm
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($documents as $doc): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card community-card h-100 d-flex flex-column">
                        <div class="card-body d-flex flex-column p-3">
                            
                            <!-- Header thẻ: Môn học & Badges trạng thái -->
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge-tag text-truncate" style="max-width: 160px;" title="<?= h($doc['subject_name'] ?? 'Chưa phân loại') ?>">
                                    <i class="bi bi-journal-bookmark me-1"></i><?= h($doc['subject_name'] ?? 'Chưa phân loại') ?>
                                </span>

                                <div class="d-flex align-items-center gap-1">
                                    <!-- Badge Visibility -->
                                    <?php if ($doc['visibility'] === 'public'): ?>
                                        <span class="badge badge-public" style="font-size: 11px;">
                                            <i class="bi bi-globe me-1"></i>Công khai
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-private" style="font-size: 11px;">
                                            <i class="bi bi-lock me-1"></i>Riêng tư
                                        </span>
                                    <?php endif; ?>

                                    <!-- Badge Moderation Status -->
                                    <?php
                                    $statusMap = [
                                        'approved'      => ['label' => 'Đã duyệt',      'cls' => 'badge-status-approved',      'icon' => 'bi-check-lg'],
                                        'pending'       => ['label' => 'Chờ duyệt',    'cls' => 'badge-status-pending',       'icon' => 'bi-hourglass-split'],
                                        'rejected'      => ['label' => 'Bị từ chối',   'cls' => 'badge-status-rejected',      'icon' => 'bi-x-lg'],
                                        'hidden'        => ['label' => 'Bị ẩn',        'cls' => 'badge-status-hidden',        'icon' => 'bi-eye-slash'],
                                        'not_submitted' => ['label' => 'Chưa gửi',     'cls' => 'badge-status-not_submitted', 'icon' => 'bi-pencil'],
                                    ];
                                    $st = $statusMap[$doc['moderation_status']] ?? $statusMap['not_submitted'];
                                    ?>
                                    <span class="badge <?= $st['cls'] ?>" style="font-size: 11px;" title="Trạng thái kiểm duyệt">
                                        <i class="bi <?= $st['icon'] ?> me-1"></i><?= $st['label'] ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Tiêu đề tài liệu -->
                            <h6 class="card-title fw-bold mb-2">
                                <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>"
                                   class="text-dark text-decoration-none hover-primary line-clamp-2"
                                   title="<?= h($doc['title']) ?>">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i><?= h($doc['title']) ?>
                                </a>
                            </h6>

                            <!-- Mô tả tóm tắt nếu có -->
                            <?php if (!empty($doc['description'])): ?>
                                <p class="text-muted small mb-2 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 12.5px;">
                                    <?= h(strip_tags($doc['description'])) ?>
                                </p>
                            <?php else: ?>
                                <p class="text-muted small fst-italic mb-2 flex-grow-1" style="font-size: 12.5px;">Không có mô tả chi tiết.</p>
                            <?php endif; ?>

                            <!-- Thumbnail ảnh nếu có -->
                            <?php if (!empty($doc['thumbnail_path'])): ?>
                                <div class="mb-2 rounded overflow-hidden border bg-light text-center" style="height: 100px;">
                                    <img src="<?= storageUrl($doc['thumbnail_path']) ?>" alt="Thumbnail"
                                         class="w-100 h-100 object-fit-cover">
                                </div>
                            <?php endif; ?>

                            <!-- Thông tin Meta: Dung lượng, Ngày tạo, Lượt tải, Số ảnh -->
                            <div class="feed-meta pt-2 border-top mt-auto small">
                                <span title="Dung lượng file">
                                    <i class="bi bi-hdd me-1"></i><?= formatFileSize($doc['file_size']) ?>
                                </span>
                                <span title="Lượt tải về">
                                    <i class="bi bi-download me-1"></i><?= number_format($doc['download_count']) ?>
                                </span>
                                <?php if (!empty($doc['image_count']) && $doc['image_count'] > 0): ?>
                                    <span title="Ảnh minh hoạ">
                                        <i class="bi bi-images me-1"></i><?= $doc['image_count'] ?> ảnh
                                    </span>
                                <?php endif; ?>
                                <span title="Ngày tải lên" class="ms-auto text-muted">
                                    <i class="bi bi-clock me-1"></i><?= timeAgo($doc['created_at']) ?>
                                </span>
                            </div>

                        </div>

                        <!-- Footer thẻ: Các nút tác vụ nhanh -->
                        <div class="card-footer bg-white border-top px-3 py-2 d-flex justify-content-between align-items-center">
                            <!-- Xem chi tiết -->
                            <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>"
                               class="btn btn-outline-primary btn-sm py-1 px-2 d-flex align-items-center gap-1">
                                <i class="bi bi-eye"></i>
                                <span>Xem</span>
                            </a>

                            <div class="d-flex align-items-center gap-1">
                                <!-- Chỉnh sửa (nếu không bị ẩn) -->
                                <?php if ($doc['moderation_status'] !== 'hidden'): ?>
                                    <a href="<?= url('?module=documents&action=edit&id=' . $doc['id']) ?>"
                                       class="btn btn-light-subtle btn-sm py-1 px-2" title="Chỉnh sửa">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Thao tác Duyệt nhanh (Submit / Revoke) -->
                                <?php if ($doc['moderation_status'] === 'not_submitted' || $doc['moderation_status'] === 'rejected'): ?>
                                    <form method="POST" action="<?= url('?module=documents&action=submit') ?>" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc muốn gửi tài liệu này để ban quản trị phê duyệt?');">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm py-1 px-2" title="Gửi yêu cầu duyệt">
                                            <i class="bi bi-send-check"></i>
                                        </button>
                                    </form>
                                <?php elseif ($doc['moderation_status'] === 'pending' || $doc['moderation_status'] === 'approved'): ?>
                                    <form method="POST" action="<?= url('?module=documents&action=revoke') ?>" class="d-inline"
                                          onsubmit="return confirm('Thu hồi tài liệu về chế độ riêng tư?');">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                        <button type="submit" class="btn btn-outline-warning btn-sm py-1 px-2" title="Thu hồi về riêng tư">
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Nút Xóa (Mở modal xác nhận) -->
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2"
                                        data-bs-toggle="modal" data-bs-target="#deleteDocModal<?= $doc['id'] ?>" title="Xóa tài liệu">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- MODAL XÁC NHẬN XÓA TÀI LIỆU -->
                <div class="modal fade" id="deleteDocModal<?= $doc['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h6 class="modal-title fw-bold text-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Xóa tài liệu?
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body small py-2">
                                Bạn có chắc chắn muốn xóa tài liệu <strong>"<?= h($doc['title']) ?>"</strong>?<br>
                                Tài liệu sẽ được chuyển vào thùng rác.
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

            <?php endforeach; ?>
        </div>

        <!-- 5. PHÂN TRANG -->
        <?php if ($pagination['pages'] > 1): ?>
            <nav class="mt-4 d-flex justify-content-center" aria-label="Phân trang tài liệu">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Nút Trang trước -->
                    <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $paginationBaseUrl . ($pagination['page'] - 1) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <!-- Các trang số -->
                    <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
                        <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $paginationBaseUrl . $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Nút Trang sau -->
                    <li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $paginationBaseUrl . ($pagination['page'] + 1) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
