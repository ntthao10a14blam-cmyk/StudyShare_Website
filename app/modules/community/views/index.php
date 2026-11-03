<?php
/**
 * COMMUNITY VIEWS: INDEX.PHP — Kho Tài Liệu Học Tập Cộng Đồng
 * Phong cách thiết kế: tailieu.vn
 * Tuân thủ quy chuẩn: QUY_CHUAN_THIET_KE_UI.md
 *
 * @var string $keyword
 * @var int $subjectId
 * @var int $tagId
 * @var string $sort
 * @var array $documents
 * @var array $subjects
 * @var array $tags
 * @var array $stats
 * @var array $pagination
 * @var array|null $currentSubject
 * @var int $totalDocs
 */

// Đảm bảo không bị cảnh báo undefined variable khi IDE quét tĩnh
$keyword        = $keyword ?? '';
$subjectId      = $subjectId ?? 0;
$tagId          = $tagId ?? 0;
$sort           = $sort ?? 'newest';
$documents      = $documents ?? [];
$subjects       = $subjects ?? [];
$tags           = $tags ?? [];
$stats          = $stats ?? ['total_docs' => 0, 'total_downloads' => 0, 'total_users' => 0];
$pagination     = $pagination ?? ['page' => 1, 'pages' => 1];
$currentSubject = $currentSubject ?? null;
$totalDocs      = $totalDocs ?? 0;

$extraCss = ['community.css'];
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">

    <!-- HERO SEARCH BANNER (Phong cách tailieu.vn) -->
    <div class="community-hero shadow-sm">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="community-hero-title">
                    <i class="bi bi-book-half text-primary me-2"></i>Kho Tài Liệu Học Tập Cộng Đồng
                </h1>
                <p class="community-hero-subtitle">
                    Tìm kiếm và tải về miễn phí giáo trình, đề thi trắc nghiệm, bài tập lớn và tài liệu ôn thi chất lượng cao do cộng đồng sinh viên đóng góp.
                </p>

                <!-- Thanh tìm kiếm tài liệu -->
                <form action="<?= url() ?>" method="GET" class="community-search-box">
                    <input type="hidden" name="module" value="community">
                    <input type="hidden" name="action" value="index">
                    <?php if ($subjectId > 0): ?>
                        <input type="hidden" name="subject_id" value="<?= (int) $subjectId ?>">
                    <?php endif; ?>

                    <i class="bi bi-search text-muted ms-2 fs-5"></i>
                    <input type="text" name="keyword" class="form-control" 
                           placeholder="Nhập tên giáo trình, đề thi, môn học hoặc từ khóa cần tìm..."
                           value="<?= h($keyword) ?>">

                    <?php if (!empty($keyword)): ?>
                        <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '')) ?>" 
                           class="btn btn-sm btn-link text-muted px-2" title="Xóa từ khóa">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary px-4 fw-medium">
                        <i class="bi bi-search me-1"></i> Tìm kiếm
                    </button>
                </form>

                <!-- Gợi ý từ khóa hot ngay dưới ô tìm kiếm (chuẩn tailieu.vn) -->
                <div class="mt-2 d-flex flex-wrap align-items-center gap-1 small text-muted">
                    <span class="me-1">Từ khóa hot:</span>
                    <?php foreach (array_slice($tags, 0, 5) as $hotTag): ?>
                        <a href="<?= url('?module=community&action=index&tag_id=' . $hotTag['id']) ?>" 
                           class="badge bg-white text-dark border text-decoration-none py-1 px-2">
                            <?= h($hotTag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Box kêu gọi đóng góp tài liệu bên phải -->
            <div class="col-lg-4 d-none d-lg-block text-end">
                <div class="p-3 bg-white rounded-3 border d-inline-block text-start shadow-sm" style="max-width: 290px;">
                    <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-up-fill text-primary fs-5"></i>
                        <span>Đóng góp tài liệu học tập</span>
                    </div>
                    <p class="text-muted small mb-2" style="font-size: 0.8125rem;">
                        Chia sẻ bài giảng, đề thi của bạn để giúp đỡ hàng nghìn bạn bè cùng khóa ôn tập hiệu quả.
                    </p>
                    <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-outline-primary btn-sm w-100 fw-medium">
                        <i class="bi bi-upload me-1"></i> Tải tài liệu lên ngay
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- BỐ CỤC 2 CỘT CHUẨN TAILIEU.VN -->
    <div class="row g-4">

        <!-- CỘT TRÁI (SIDEBAR 3 COLS): MÔN HỌC & THỐNG KÊ -->
        <aside class="col-lg-3 col-md-4">

            <!-- Card: Danh mục môn học -->
            <div class="community-sidebar-card shadow-sm">
                <div class="community-sidebar-header">
                    <i class="bi bi-journal-bookmark-fill text-primary"></i>
                    <span>Danh mục môn học</span>
                </div>
                <div class="community-subject-list">
                    <a href="<?= url('?module=community&action=index' . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '')) ?>" 
                       class="community-subject-item <?= $subjectId === 0 ? 'active' : '' ?>">
                        <span><i class="bi bi-grid-fill me-2 text-muted"></i>Tất cả môn học</span>
                        <span class="community-subject-count"><?= array_sum(array_column($subjects, 'doc_count')) ?></span>
                    </a>

                    <?php foreach ($subjects as $s): ?>
                        <a href="<?= url('?module=community&action=index&subject_id=' . $s['id'] . (!empty($keyword) ? '&keyword=' . urlencode($keyword) : '')) ?>" 
                           class="community-subject-item <?= $subjectId === (int) $s['id'] ? 'active' : '' ?>">
                            <span class="text-truncate" title="<?= h($s['name']) ?>">
                                <i class="bi bi-book me-2 <?= $subjectId === (int) $s['id'] ? 'text-primary' : 'text-muted' ?>"></i>
                                <?= h($s['name']) ?>
                            </span>
                            <span class="community-subject-count"><?= (int) $s['doc_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card: Chủ đề / Từ khóa nổi bật -->
            <div class="community-sidebar-card shadow-sm">
                <div class="community-sidebar-header">
                    <i class="bi bi-tags-fill text-primary"></i>
                    <span>Chủ đề tài liệu quan tâm</span>
                </div>
                <div class="p-3 d-flex flex-wrap gap-2">
                    <?php foreach ($tags as $t): ?>
                        <a href="<?= url('?module=community&action=index&tag_id=' . $t['id']) ?>" 
                           class="badge-tag text-decoration-none <?= $tagId === (int) $t['id'] ? 'bg-primary text-white' : '' ?>">
                            #<?= h($t['name']) ?>
                            <?php if (!empty($t['doc_count'])): ?>
                                <small class="opacity-75">(<?= (int) $t['doc_count'] ?>)</small>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card: Thống kê nhanh StudyHub -->
            <div class="community-sidebar-card shadow-sm p-3">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size: 0.9375rem;">
                    <i class="bi bi-bar-chart-line-fill text-primary"></i>
                    <span>Thống kê cộng đồng</span>
                </h6>
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 fs-5">
                        <i class="bi bi-files"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-6"><?= number_format($stats['total_docs']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Tài liệu đã chia sẻ</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 fs-5">
                        <i class="bi bi-cloud-arrow-down"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-6"><?= number_format($stats['total_downloads']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Lượt tải về thành công</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 pt-2">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3 fs-5">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-6"><?= number_format($stats['total_users']) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Sinh viên tham gia</div>
                    </div>
                </div>
            </div>

        </aside>

        <!-- CỘT PHẢI (MAIN CONTENT 9 COLS): FEED TÀI LIỆU -->
        <main class="col-lg-9 col-md-8">

            <!-- Toolbar tiêu đề danh sách & Bộ lọc sắp xếp -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h2 class="fs-5 fw-bold text-dark mb-1">
                        <?php if ($currentSubject): ?>
                            <i class="bi bi-folder2-open text-primary me-2"></i><?= h($currentSubject['name']) ?>
                        <?php elseif (!empty($keyword)): ?>
                            <i class="bi bi-search text-primary me-2"></i>Kết quả tìm kiếm: "<?= h($keyword) ?>"
                        <?php else: ?>
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>Tài liệu mới cập nhật
                        <?php endif; ?>
                    </h2>
                    <span class="text-muted small">
                        Hiển thị <strong><?= count($documents) ?></strong> / <strong><?= $totalDocs ?></strong> tài liệu
                    </span>
                </div>

                <!-- Tabs sắp xếp theo kiểu tailieu.vn -->
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <span class="small text-muted d-none d-sm-inline">Sắp xếp:</span>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=newest') ?>" 
                           class="btn <?= $sort === 'newest' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            Mới nhất
                        </a>
                        <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=downloads') ?>" 
                           class="btn <?= $sort === 'downloads' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            Tải nhiều
                        </a>
                        <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=title') ?>" 
                           class="btn <?= $sort === 'title' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            Tên A-Z
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trạng thái lọc hiện tại -->
            <?php if ($subjectId > 0 || !empty($keyword) || $tagId > 0): ?>
                <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="small text-muted me-1">Đang lọc:</span>
                    <?php if ($currentSubject): ?>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            Môn: <?= h($currentSubject['name']) ?>
                            <a href="<?= url('?module=community&action=index' . ($keyword ? '&keyword=' . urlencode($keyword) : '')) ?>" class="text-danger ms-1 text-decoration-none">&times;</a>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($keyword)): ?>
                        <span class="badge bg-light text-dark border px-2 py-1">
                            Từ khóa: <?= h($keyword) ?>
                            <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '')) ?>" class="text-danger ms-1 text-decoration-none">&times;</a>
                        </span>
                    <?php endif; ?>
                    <a href="<?= url('?module=community&action=index') ?>" class="small text-danger text-decoration-none ms-2">
                        <i class="bi bi-trash"></i> Xóa tất cả bộ lọc
                    </a>
                </div>
            <?php endif; ?>

            <!-- DANH SÁCH CARD TÀI LIỆU CHUẨN TAILIEU.VN -->
            <?php if (!empty($documents)): ?>
                <div class="tailieu-feed-list">
                    <?php foreach ($documents as $doc): ?>
                        <article class="tailieu-card">
                            <!-- Thumbnail bìa sách mô phỏng tài liệu tailieu.vn -->
                            <a href="<?= url('?module=community&action=show&id=' . $doc['id']) ?>" 
                               class="tailieu-thumb-book text-decoration-none" title="Xem chi tiết tài liệu">
                                <span class="pdf-badge">PDF</span>
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </a>

                            <!-- Nội dung chi tiết tài liệu -->
                            <div class="tailieu-card-content">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <h3 class="tailieu-card-title mb-0">
                                        <a href="<?= url('?module=community&action=show&id=' . $doc['id']) ?>" 
                                           class="tailieu-card-title">
                                            <?= h($doc['title']) ?>
                                        </a>
                                    </h3>
                                    <?php if (!empty($doc['subject_name'])): ?>
                                        <a href="<?= url('?module=community&action=index&subject_id=' . $doc['subject_id']) ?>" 
                                           class="badge-tag text-nowrap text-decoration-none">
                                            <?= h($doc['subject_name']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Đoạn trích mô tả tóm tắt (2 dòng) -->
                                <p class="tailieu-card-desc">
                                    <?= !empty($doc['description']) ? h($doc['description']) : 'Tài liệu học tập hữu ích dành cho sinh viên, bấm vào để xem nội dung và tải file miễn phí.' ?>
                                </p>

                                <!-- Dòng Meta thông tin chuẩn tailieu.vn -->
                                <div class="tailieu-card-meta">
                                    <span>
                                        <i class="bi bi-person text-primary"></i>
                                        <?= h($doc['author_name'] ?? 'Thành viên') ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-clock"></i>
                                        <?= timeAgo($doc['published_at'] ?? $doc['created_at']) ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-hdd"></i>
                                        <?= formatFileSize((int) $doc['file_size']) ?>
                                    </span>
                                    <span>
                                        <i class="bi bi-cloud-arrow-down text-success"></i>
                                        <strong><?= number_format((int) $doc['download_count']) ?></strong> tải
                                    </span>
                                    <span>
                                        <i class="bi bi-chat-dots text-info"></i>
                                        <strong><?= (int) $doc['comment_count'] ?></strong> bình luận
                                    </span>

                                    <!-- Nút Xem chi tiết -->
                                    <span class="ms-auto d-none d-sm-inline">
                                        <a href="<?= url('?module=community&action=show&id=' . $doc['id']) ?>" 
                                           class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.8125rem;">
                                            Xem chi tiết <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- PHÂN TRANG (Pagination) -->
                <?php if ($pagination['pages'] > 1): ?>
                    <nav class="mt-4 d-flex justify-content-center" aria-label="Phân trang tài liệu">
                        <ul class="pagination pagination-sm">
                            <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= url('?module=community&action=index&page=' . ($pagination['page'] - 1) . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=' . $sort) ?>">
                                    <i class="bi bi-chevron-left"></i> Trang trước
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                                <?php if ($i == 1 || $i == $pagination['pages'] || ($i >= $pagination['page'] - 2 && $i <= $pagination['page'] + 2)): ?>
                                    <li class="page-item <?= $pagination['page'] === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('?module=community&action=index&page=' . $i . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=' . $sort) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php elseif ($i == 2 || $i == $pagination['pages'] - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <li class="page-item <?= $pagination['page'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= url('?module=community&action=index&page=' . ($pagination['page'] + 1) . ($subjectId ? '&subject_id=' . $subjectId : '') . ($keyword ? '&keyword=' . urlencode($keyword) : '') . '&sort=' . $sort) ?>">
                                    Trang sau <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- Trạng thái trống (Empty State) -->
                <div class="text-center py-5 bg-white rounded-3 border p-4 shadow-sm">
                    <i class="bi bi-folder2-open display-4 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="fw-bold text-dark mb-2">Chưa tìm thấy tài liệu phù hợp</h5>
                    <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">
                        Hiện chưa có tài liệu nào phù hợp với từ khóa hoặc bộ lọc đã chọn. Hãy thử tìm kiếm bằng từ khóa khác hoặc bấm nút bên dưới để xem toàn bộ tài liệu.
                    </p>
                    <a href="<?= url('?module=community&action=index') ?>" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-arrow-repeat me-1"></i> Xem tất cả tài liệu
                    </a>
                </div>
            <?php endif; ?>

        </main>
    </div>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
