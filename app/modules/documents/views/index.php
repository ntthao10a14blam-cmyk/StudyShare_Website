<?php

$pageTitle = $pageTitle ?? 'Tài liệu của tôi';
require_once BASE_PATH . '/app/views/layouts/header.php';

$queryParams = $_GET;
unset($queryParams['page']);
$queryString = http_build_query($queryParams);
$paginationBaseUrl = url('?' . ($queryString ? $queryString . '&' : '') . 'page=');

if (!function_exists('mdPlainPreview')) {
    function mdPlainPreview(?string $md): string
    {
        $t = strip_tags((string) $md);
        $t = preg_replace('/```.*?```/s', ' ', $t);
        $t = preg_replace('/!?\[([^\]]*)\]\([^)]*\)/u', '$1', $t);
        $t = preg_replace('/^\s{0,3}(#{1,6}|>|[-*+]|\d+\.)\s+/mu', '', $t);
        $t = preg_replace('/[*_`~|]+/u', '', $t);
        return trim(preg_replace('/\s+/u', ' ', $t));
    }
}
?>

<section class="text-white py-5" style="background-color: #075e57; border-bottom: 3px solid #f5d77a;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h1 class="h2 fw-bold text-white mb-1">Tài Liệu Của Tôi</h1>
                <p class="text-white text-opacity-75 small mb-0">
                    Quản lý kho tài liệu cá nhân, theo dõi tiến độ kiểm duyệt và chia sẻ học liệu tới cộng đồng sinh viên StudyShare
                </p>
            </div>
            <a href="<?= url('?module=documents&action=create') ?>"
               class="btn home-v2-btn-accent rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 flex-shrink-0">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <span>Tải lên tài liệu mới</span>
            </a>
        </div>

        <div class="p-2 px-md-3 py-md-2 rounded-pill" style="background-color: #0b766e; border: 1px solid rgba(245, 215, 122, 0.4);">
            <form method="GET" action="<?= url('/') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="module" value="documents">
                <input type="hidden" name="action" value="index">

                <div class="col-12 col-md-5">
                    <div class="community-search-box bg-white rounded-pill px-2">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" class="form-control form-control-sm border-0 shadow-none bg-transparent rounded-pill"
                               placeholder="Tìm kiếm tài liệu..."
                               value="<?= h(get('q', '')) ?>">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="subject_id" class="form-select form-select-sm border-0 rounded-pill px-3">
                        <option value="">Tất cả môn học</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= get('subject_id') == $s['id'] ? 'selected' : '' ?>>
                                <?= h($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="visibility" class="form-select form-select-sm border-0 rounded-pill px-3">
                        <option value="all">Tất cả hiển thị</option>
                        <option value="public" <?= get('visibility') === 'public' ? 'selected' : '' ?>>Công khai</option>
                        <option value="private" <?= get('visibility') === 'private' ? 'selected' : '' ?>>Riêng tư</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1">
                    <select name="sort" class="form-select form-select-sm border-0 rounded-pill px-3">
                        <option value="newest" <?= get('sort') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="oldest" <?= get('sort') === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                        <option value="downloads" <?= get('sort') === 'downloads' ? 'selected' : '' ?>>Lượt tải</option>
                        <option value="title" <?= get('sort') === 'title' ? 'selected' : '' ?>>Tiêu đề A-Z</option>
                    </select>
                    <button type="submit" class="btn home-v2-btn-accent btn-sm px-3 fw-semibold rounded-pill flex-shrink-0" title="Lọc dữ liệu">
                        <i class="bi bi-funnel"></i>
                    </button>
                    <?php if (!empty($_GET['q']) || !empty($_GET['subject_id']) || (isset($_GET['visibility']) && $_GET['visibility'] !== 'all')): ?>
                        <a href="<?= url('?module=documents&action=index') ?>" class="btn btn-light btn-sm rounded-pill flex-shrink-0" title="Đặt lại bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container py-4">

    <?php
    $docLists = [
        'pending' => [
            'label' => '1. Đang chờ duyệt',
            'empty' => 'Không có tài liệu nào đang chờ duyệt.',
            'icon'  => 'bi-hourglass-split',
            'bg'    => '#fef3c7',
            'color' => '#d97706',
            'items' => [],
        ],
        'approved' => [
            'label' => '2. Duyệt',
            'empty' => 'Chưa có tài liệu nào được duyệt.',
            'icon'  => 'bi-check-circle-fill',
            'bg'    => '#d7efeb',
            'color' => '#0b766e',
            'items' => [],
        ],
        'rejected' => [
            'label' => '3. Từ chối',
            'empty' => 'Không có tài liệu nào bị từ chối.',
            'icon'  => 'bi-x-circle-fill',
            'bg'    => '#fee2e2',
            'color' => '#dc2626',
            'items' => [],
        ],
        'not_submitted' => [
            'label' => '4. Bản nháp',
            'empty' => 'Chưa có bản nháp nào.',
            'icon'  => 'bi-file-earmark-text',
            'bg'    => '#f1f5f9',
            'color' => '#475569',
            'items' => [],
        ],
        'hidden' => [
            'label' => '5. Bị ẩn do vi phạm',
            'empty' => 'Không có tài liệu nào bị ẩn.',
            'icon'  => 'bi-eye-slash-fill',
            'bg'    => '#e5e7eb',
            'color' => '#374151',
            'items' => [],
        ],
    ];
    foreach ($documents as $docItem) {
        $stKey = $docItem['moderation_status'] ?? 'not_submitted';
        if (isset($docLists[$stKey])) {
            $docLists[$stKey]['items'][] = $docItem;
        }
    }
    ?>

    <div class="d-flex flex-column gap-4">
        <?php foreach ($docLists as $stKey => $listGroup): ?>
            <section class="home-v2-card rounded-4 overflow-hidden shadow-sm"
                     style="border-top: 3px solid <?= $listGroup['color'] ?>;">

                <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi <?= $listGroup['icon'] ?> fs-5" style="color: <?= $listGroup['color'] ?>;"></i>
                        <h2 class="h6 fw-bold text-dark mb-0"><?= $listGroup['label'] ?></h2>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                        <?= count($listGroup['items']) ?> tài liệu
                    </span>
                </div>

                <?php if (empty($listGroup['items'])): ?>
                    <div class="px-4 py-3 text-muted small fst-italic bg-white">
                        <?= $listGroup['empty'] ?>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($listGroup['items'] as $doc): ?>
                            <div class="list-group-item px-4 py-3 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                                    <span class="rounded-2 bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0 mt-1"
                                          style="width: 38px; height: 38px;">
                                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                    </span>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>"
                                               class="fw-bold text-dark text-decoration-none hover-primary text-truncate"
                                               style="max-width: 520px;">
                                                <?= h($doc['title']) ?>
                                            </a>
                                            <span class="badge bg-light text-primary border" style="font-size: 11px;">
                                                <?= h($doc['subject_name'] ?? 'Chưa phân loại') ?>
                                            </span>
                                            <?php if ($doc['visibility'] === 'public'): ?>
                                                <span class="badge badge-public" style="font-size: 11px;">
                                                    <i class="bi bi-globe me-1"></i>Công khai
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-private" style="font-size: 11px;">
                                                    <i class="bi bi-lock me-1"></i>Riêng tư
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($stKey === 'rejected'): ?>
                                            <div class="text-danger small mb-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                <strong>Lý do từ chối:</strong> <?= h($doc['rejection_reason'] ?: 'Không đạt chuẩn kiểm duyệt.') ?>
                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-danger fw-semibold"
                                                        data-bs-toggle="modal" data-bs-target="#rejectionModal<?= $doc['id'] ?>">
                                                    Chi tiết
                                                </button>
                                            </div>
                                        <?php elseif (!empty($doc['description'])): ?>
                                            <div class="text-muted small text-truncate mb-1" style="max-width: 680px;">
                                                <?= h(mdPlainPreview($doc['description'])) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="small text-muted d-flex flex-wrap gap-3">
                                            <span><i class="bi bi-hdd me-1"></i><?= formatFileSize($doc['file_size']) ?></span>
                                            <span><i class="bi bi-download me-1"></i><?= number_format($doc['download_count']) ?> lượt tải</span>
                                            <span><i class="bi bi-clock me-1"></i><?= timeAgo($doc['created_at']) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                    <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>"
                                       class="btn btn-outline-primary btn-sm py-1 px-2 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-eye"></i><span>Xem</span>
                                    </a>
                                    <?php if ($doc['moderation_status'] !== 'hidden'): ?>
                                        <a href="<?= url('?module=documents&action=edit&id=' . $doc['id']) ?>"
                                           class="btn btn-light-subtle btn-sm py-1 px-2" title="Chỉnh sửa">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($doc['moderation_status'] === 'not_submitted' || $doc['moderation_status'] === 'rejected'): ?>
                                        <form method="POST" action="<?= url('?module=documents&action=submit') ?>" class="d-inline"
                                              onsubmit="return confirm('Bạn có chắc muốn gửi tài liệu này để ban quản trị phê duyệt?');">
                                            <?php csrfField(); ?>
                                            <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="<?= h('?' . ($_SERVER['QUERY_STRING'] ?? 'module=documents&action=index')) ?>">
                                            <button type="submit" class="btn btn-outline-success btn-sm py-1 px-2" title="Gửi yêu cầu duyệt">
                                                <i class="bi bi-send-check"></i>
                                            </button>
                                        </form>
                                    <?php elseif ($doc['moderation_status'] === 'pending' || $doc['moderation_status'] === 'approved'): ?>
                                        <form method="POST" action="<?= url('?module=documents&action=revoke') ?>" class="d-inline"
                                              onsubmit="return confirm('Thu hồi tài liệu về chế độ riêng tư?');">
                                            <?php csrfField(); ?>
                                            <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                            <input type="hidden" name="redirect_to" value="<?= h('?' . ($_SERVER['QUERY_STRING'] ?? 'module=documents&action=index')) ?>">
                                            <button type="submit" class="btn btn-outline-warning btn-sm py-1 px-2" title="Thu hồi về riêng tư">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2"
                                            data-bs-toggle="modal" data-bs-target="#deleteDocModal<?= $doc['id'] ?>" title="Xóa tài liệu">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>
    </div>

    <?php foreach ($documents as $doc): ?>

                <?php if ($doc['moderation_status'] === 'rejected'): ?>
                    <div class="modal fade" id="rejectionModal<?= $doc['id'] ?>" tabindex="-1" aria-labelledby="rejectionModalLabel<?= $doc['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-danger-subtle border-0">
                                    <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="rejectionModalLabel<?= $doc['id'] ?>">
                                        <i class="bi bi-shield-x fs-5"></i>
                                        Lý do từ chối từ Ban Quản Trị
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                </div>
                                <div class="modal-body p-3">
                                    <div class="mb-3">
                                        <span class="text-muted small d-block mb-1">Tài liệu:</span>
                                        <strong class="text-dark fs-6"><?= h($doc['title']) ?></strong>
                                    </div>

                                    <div class="p-3 bg-danger-subtle rounded border border-danger-subtle mb-3">
                                        <div class="fw-bold text-danger mb-1 small d-flex align-items-center gap-1">
                                            <i class="bi bi-chat-left-dots-fill"></i> Phản hồi từ Quản trị viên:
                                        </div>
                                        <div class="text-dark small" style="white-space: pre-line; line-height: 1.6;">
                                            <?= h($doc['rejection_reason'] ?: 'Tài liệu chưa đạt tiêu chuẩn nội dung hoặc vi phạm quy định cộng đồng. Vui lòng rà soát lại thông tin mô tả và tệp PDF.') ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($doc['rejected_at'])): ?>
                                        <div class="text-muted small mb-3">
                                            <i class="bi bi-clock me-1"></i> Thời điểm từ chối: <strong><?= formatDateTime($doc['rejected_at']) ?></strong>
                                            <?php if (!empty($doc['reviewer_name'])): ?>
                                                · Người duyệt: <strong><?= h($doc['reviewer_name']) ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="alert alert-info py-2 px-3 small mb-0 d-flex align-items-start gap-2">
                                        <i class="bi bi-lightbulb-fill text-warning fs-5 flex-shrink-0"></i>
                                        <div>
                                            <strong>Gợi ý khắc phục:</strong> Bạn có thể chỉnh sửa lại tài liệu (thay đổi file PDF hoặc mô tả) theo phản hồi của Admin, sau đó gửi duyệt lại để được xuất bản công khai.
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light-subtle btn-sm px-3" data-bs-dismiss="modal">Đóng</button>
                                    <a href="<?= url('?module=documents&action=edit&id=' . $doc['id']) ?>" class="btn btn-primary btn-sm px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Chỉnh sửa tài liệu ngay</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

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
                                    <button type="submit" class="btn btn-danger btn-sm">Xóa tài liệu</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
