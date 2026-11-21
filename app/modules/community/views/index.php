<?php

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

<section class="py-4 text-white" style="background-color: #075e57; border-bottom: 4px solid #f5d77a;">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="h3 fw-bold text-white mb-3">Tìm Kiếm Tài Liệu</h1>

                <form action="<?= url() ?>" method="GET" class="bg-white rounded-pill p-1 shadow-sm d-flex align-items-center mb-3" style="max-width: 680px;">
                    <input type="hidden" name="module" value="community">
                    <input type="hidden" name="action" value="index">
                    <?php if ($subjectId > 0): ?>
                        <input type="hidden" name="subject_id" value="<?= (int) $subjectId ?>">
                    <?php endif; ?>

                    <i class="bi bi-search text-muted ms-3 fs-5"></i>
                    <input type="text" name="keyword" class="form-control border-0 bg-transparent shadow-none text-dark px-3"
                           placeholder="Nhập tên giáo trình, đề thi, môn học hoặc từ khóa cần tìm..."
                           value="<?= h($keyword) ?>">

                    <?php if (!empty($keyword)): ?>
                        <a href="<?= url('?module=community&action=index' . ($subjectId ? '&subject_id=' . $subjectId : '')) ?>"
                           class="btn btn-sm btn-link text-muted px-2" title="Xóa từ khóa">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>

                    <button type="submit" class="btn home-v2-btn-accent rounded-pill px-4 py-2 fw-semibold flex-shrink-0 text-nowrap">
                        <i class="bi bi-search me-1"></i> Tìm kiếm
                    </button>
                </form>

                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <span class="text-white text-opacity-75">Từ khóa hot:</span>
                    <?php foreach (array_slice($tags, 0, 5) as $hotTag): ?>
                        <a href="<?= url('?module=community&action=index&tag_id=' . $hotTag['id']) ?>"
                           class="badge text-white text-decoration-none py-1 px-2 rounded-pill"
                           style="background-color: #0b766e; border: 1px solid rgba(245, 215, 122, 0.45);">
                            #<?= h($hotTag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-lg-4 d-none d-lg-block text-end">
                <div class="p-4 rounded-4 d-inline-block text-start shadow-sm"
                     style="max-width: 320px; background-color: #0b766e; border: 1px solid rgba(245, 215, 122, 0.5);">
                    <div class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-up-fill text-warning fs-5"></i>
                        <span>Đóng góp tài liệu học tập</span>
                    </div>
                    <p class="text-white text-opacity-75 small mb-3" style="font-size: 0.825rem;">
                        Chia sẻ bài giảng, đề thi của bạn để giúp đỡ hàng nghìn bạn bè cùng khóa ôn tập hiệu quả.
                    </p>
                    <a href="<?= url(Auth::check() ? '?module=documents&action=create' : '?module=auth&action=login') ?>"
                       class="btn home-v2-btn-accent btn-sm w-100 rounded-pill fw-semibold py-2">
                        <i class="bi bi-upload me-1"></i> Tải tài liệu lên ngay
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">

    <div class="row g-4">

        <aside class="col-lg-3 col-md-4">

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

        </aside>

        <main class="col-lg-9 col-md-8">

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

            <?php if (!empty($documents)): ?>
                <div class="tailieu-feed-list">
                    <?php foreach ($documents as $doc): ?>
                        <article class="tailieu-card">

                            <a href="<?= url('?module=community&action=show&id=' . $doc['id']) ?>"
                               class="tailieu-thumb-book text-decoration-none" title="Xem chi tiết tài liệu">
                                <span class="pdf-badge">PDF</span>
                                <i class="bi bi-file-earmark-pdf-fill"></i>
                            </a>

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

                                <p class="tailieu-card-desc">
                                    <?= !empty($doc['description']) ? h($doc['description']) : 'Tài liệu học tập hữu ích dành cho sinh viên, bấm vào để xem nội dung và tải file miễn phí.' ?>
                                </p>

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

                <!-- <?php if ($pagination['pages'] > 1): ?>
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
                <?php endif; ?> -->

            <?php else: ?>

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
