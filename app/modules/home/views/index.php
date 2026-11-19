<?php
/**
 * TRANG CHỦ — StudyShare
 * Bố cục hiện đại 7 phân khu, giữ nguyên bảng màu (Xanh ngọc #0b766e, Vàng pastel #f5d77a) và font Inter.
 */
$pageTitle = $pageTitle ?? 'Trang chủ — Kho Tài Liệu Sinh Viên & Cộng Đồng Học Tập';
require_once BASE_PATH . '/app/views/layouts/header.php';

$isLoggedInHome = Auth::check();
$shareDocUrl    = url($isLoggedInHome ? '?module=documents&action=create' : '?module=auth&action=login');
$askQuestionUrl = url($isLoggedInHome ? '?module=questions&action=create' : '?module=auth&action=login');
$featuredDoc    = $recentDocs[0] ?? null;
?>

<!-- =========================================================
     SECTION 1: HERO BANNER (Nền xanh ngọc đậm, 2 cột + Bento)
     ========================================================= -->
<section class="home-v2-hero py-5">
    <div class="container py-lg-3 position-relative" style="z-index: 2;">
        <div class="row align-items-center g-5">
            <!-- Cột trái: Tiêu đề, tìm kiếm & chỉ số -->
            <div class="col-lg-6">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3"
                     style="background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.8rem;">
                    <i class="bi bi-stars text-warning"></i>
                    <span>Cộng đồng chia sẻ học liệu sinh viên StudyShare</span>
                </div>

                <h1 class="display-5 fw-bold text-white mb-3 lh-sm">
                    Kho Học Liệu <span class="home-v2-accent-italic fw-semibold">Tin Cậy</span> Cho Hành Trình Đại Học
                </h1>

                <p class="text-white text-opacity-75 mb-4" style="max-width: 540px; font-size: 0.98rem;">
                    Tìm kiếm giáo trình, đề thi có đáp án, slide bài giảng và trao đổi hỏi đáp học thuật cùng hàng nghìn sinh viên các trường đại học.
                </p>

                <!-- Thanh tìm kiếm nhanh -->
                <form action="<?= url() ?>" method="GET" class="mb-3">
                    <input type="hidden" name="module" value="community">
                    <input type="hidden" name="action" value="index">
                    <div class="bg-white rounded-pill p-1 shadow-sm d-flex align-items-center">
                        <span class="ps-3 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword"
                               class="form-control border-0 bg-transparent py-2 px-3 shadow-none text-dark"
                               placeholder="Tìm giáo trình, đề thi, môn học...">
                        <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark flex-shrink-0">
                            Tìm kiếm
                        </button>
                    </div>
                </form>

                <!-- 2 Nút CTA -->
                <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                    <a href="<?= url('?module=community&action=index') ?>" class="btn home-v2-btn-accent rounded-pill px-4 py-2 fw-semibold">
                        Khám phá tài liệu <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="<?= $shareDocUrl ?>" class="btn btn-outline-light rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Đóng góp tài liệu
                    </a>
                </div>

                <!-- 3 Chỉ số thống kê ngang -->
                <div class="d-flex flex-wrap gap-4 pt-3 border-top border-white border-opacity-25">
                    <div>
                        <div class="fs-4 fw-bold text-white lh-1"><?= number_format($stats['documents']) ?>+</div>
                        <small class="text-white text-opacity-75">Tài liệu đã duyệt</small>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-white lh-1"><?= number_format($stats['questions']) ?>+</div>
                        <small class="text-white text-opacity-75">Câu hỏi thảo luận</small>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-white lh-1"><?= number_format($stats['users']) ?>+</div>
                        <small class="text-white text-opacity-75">Sinh viên tham gia</small>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Bento Grid trực quan -->
            <div class="col-lg-6">
                <div class="row g-3 position-relative">
                    <div class="col-12">
                        <div class="home-v2-bento-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge rounded-pill px-3 py-2 fw-semibold"
                                      style="background: rgba(245, 215, 122, 0.2); color: #f5d77a; border: 1px solid rgba(245, 215, 122, 0.45);">
                                    <i class="bi bi-shield-check me-1"></i> Kiểm duyệt chất lượng
                                </span>
                                <i class="bi bi-file-earmark-pdf-fill fs-2 text-warning"></i>
                            </div>
                            <h2 class="h5 fw-bold text-white mb-2">Xem trước trực tuyến &amp; Tải PDF chuẩn xác</h2>
                            <p class="small text-white text-opacity-75 mb-0">
                                Tài liệu được phân loại rõ ràng theo từng môn học và từ khóa, hỗ trợ xem trước trực tiếp trước khi lưu về máy.
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="home-v2-bento-card p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                      style="width: 36px; height: 36px; background: rgba(255, 255, 255, 0.16);">
                                    <i class="bi bi-chat-dots-fill text-warning"></i>
                                </span>
                                <strong class="text-white small">Hỏi đáp học tập</strong>
                            </div>
                            <p class="small text-white text-opacity-75 mb-0" style="font-size: 0.8rem;">
                                Giải đáp bài tập nhanh chóng, đánh dấu câu trả lời chính xác nhất.
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="home-v2-bento-card p-3 h-100">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                      style="width: 36px; height: 36px; background: rgba(255, 255, 255, 0.16);">
                                    <i class="bi bi-collection-fill text-warning"></i>
                                </span>
                                <strong class="text-white small">Bộ sưu tập riêng</strong>
                            </div>
                            <p class="small text-white text-opacity-75 mb-0" style="font-size: 0.8rem;">
                                Lưu trữ tài liệu yêu thích và sắp xếp theo từng học kỳ ôn thi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dải Trust Bar: Môn học nhanh -->
<section class="home-v2-trust-bar py-3">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <span class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 0.06em;">
                <i class="bi bi-bookmark-check text-primary me-1"></i> Lĩnh vực trọng tâm:
            </span>
            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-4">
                <?php if (!empty($popularSubjects)): ?>
                    <?php foreach (array_slice($popularSubjects, 0, 5) as $sub): ?>
                        <a href="<?= url('?module=community&action=index&subject_id=' . (int)$sub['id']) ?>"
                           class="text-decoration-none small fw-semibold text-dark hover-primary">
                            <?= h($sub['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SECTION 2: TÀI LIỆU VỪA CHIA SẺ (3 Card Top Lượt Tải + Danh sách)
     ========================================================= -->
<?php
$topDocs = !empty($topDownloadedDocs) ? array_slice($topDownloadedDocs, 0, 3) : [];
if (empty($topDocs) && !empty($recentDocs)) {
    $sortedDocs = $recentDocs;
    usort($sortedDocs, fn($a, $b) => (int)($b['download_count'] ?? 0) <=> (int)($a['download_count'] ?? 0));
    $topDocs = array_slice($sortedDocs, 0, 3);
}
$topDocIds = array_map(fn($d) => (int)$d['id'], $topDocs);
$otherDocs = array_values(array_filter($recentDocs ?? [], fn($d) => !in_array((int)$d['id'], $topDocIds, true)));
?>
<section class="home-v2-section-light py-5">
    <div class="container py-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
            <div>
                <span class="home-v2-eyebrow">MỚI CẬP NHẬT</span>
                <h2 class="h2 fw-bold text-dark mb-0">
                    Tài Liệu <span class="home-v2-accent-italic">Vừa Chia Sẻ</span>
                </h2>
            </div>
            <a href="<?= url('?module=community&action=index') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-medium flex-shrink-0">
                Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (!empty($topDocs)): ?>
            <!-- 3 Card Tài liệu có lượt tải nhiều nhất -->
            <div class="row g-4 mb-4">
                <?php foreach ($topDocs as $doc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="home-v2-card overflow-hidden h-100 d-flex flex-column">
                            <div class="home-v2-doc-thumb d-flex justify-content-between align-items-center gap-2">
                                <span class="badge bg-white text-primary border border-primary-subtle px-2 py-1 small text-truncate">
                                    <?= h($doc['subject_name'] ?? 'Tài liệu') ?>
                                </span>
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 small flex-shrink-0">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i><?= formatFileSize((int)$doc['file_size']) ?>
                                </span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="small text-muted mb-2 d-flex align-items-center flex-wrap gap-2">
                                    <span><i class="bi bi-person me-1"></i><?= h($doc['author_name'] ?? 'Sinh viên') ?></span>
                                    <span>•</span>
                                    <span class="fw-semibold text-primary"><i class="bi bi-download me-1"></i><?= number_format((int)$doc['download_count']) ?> lượt tải</span>
                                </div>
                                <h3 class="h6 fw-bold text-dark mb-3 flex-grow-1 text-truncate-2">
                                    <a href="<?= url('?module=community&action=show&id=' . (int)$doc['id']) ?>"
                                       class="text-decoration-none text-dark hover-primary">
                                        <?= h($doc['title']) ?>
                                    </a>
                                </h3>
                                <a href="<?= url('?module=community&action=show&id=' . (int)$doc['id']) ?>"
                                   class="text-decoration-none small fw-semibold text-primary">
                                    Xem tài liệu <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Các tài liệu còn lại hiển thị dạng List -->
            <?php if (!empty($otherDocs)): ?>
                <div class="home-v2-card overflow-hidden">
                    <div class="list-group list-group-flush">
                        <?php foreach ($otherDocs as $doc): ?>
                            <a href="<?= url('?module=community&action=show&id=' . (int)$doc['id']) ?>"
                               class="list-group-item list-group-item-action px-4 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    <span class="rounded-3 bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                          style="width: 40px; height: 40px;">
                                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark text-truncate"><?= h($doc['title']) ?></div>
                                        <div class="small text-muted d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge bg-light text-primary border"><?= h($doc['subject_name'] ?? 'Tài liệu') ?></span>
                                            <span><i class="bi bi-person me-1"></i><?= h($doc['author_name'] ?? 'Sinh viên') ?></span>
                                            <span>•</span>
                                            <span><?= formatFileSize((int)$doc['file_size']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-shrink-0">
                                    <span class="small text-muted">
                                        <i class="bi bi-download me-1 text-primary"></i><?= number_format((int)$doc['download_count']) ?> lượt tải
                                    </span>
                                    <span class="small fw-semibold text-primary">Xem tài liệu <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">Chưa có tài liệu nào được chia sẻ.</div>
        <?php endif; ?>
    </div>
</section>

<!-- =========================================================
     SECTION 3: DARK SPLIT HIGHLIGHT (Nền tối xanh ngọc)
     ========================================================= -->
<section class="home-v2-dark-section py-4">
    <div class="container py-1">
        <div class="row align-items-center g-4">
            <!-- Trái: Vòng tròn trung tâm + thông số -->
            <div class="col-lg-4 text-center">
                <div class="home-v2-circle-showcase p-3">
                    <i class="bi bi-mortarboard-fill fs-1 text-warning mb-2"></i>
                    <div class="fs-2 fw-bold text-white lh-1 mb-1"><?= number_format($stats['documents']) ?>+</div>
                    <div class="text-white text-opacity-75 small">Học liệu đã kiểm duyệt</div>
                </div>
            </div>

            <!-- Phải: Nội dung + 4 thanh danh sách (2 cột gọn) -->
            <div class="col-lg-8">
                <span class="home-v2-eyebrow home-v2-eyebrow-light">CAM KẾT CHẤT LƯỢNG</span>
                <h2 class="h3 fw-bold text-white mb-2">
                    Nền Tảng <span class="home-v2-accent-italic">Chuẩn Hóa</span> Tri Thức Sinh Viên
                </h2>
                <p class="small text-white text-opacity-75 mb-3">
                    Mỗi tài liệu đăng tải lên cộng đồng StudyShare đều trải qua quy trình kiểm duyệt kỹ lưỡng nhằm đảm bảo tính chính xác, an toàn và giá trị học thuật cao.
                </p>

                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <div class="home-v2-dark-pill d-flex align-items-center gap-2 h-100">
                            <i class="bi bi-check-circle-fill text-warning"></i>
                            <span class="small text-white fw-medium">Kiểm tra định dạng PDF thực tế</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="home-v2-dark-pill d-flex align-items-center gap-2 h-100">
                            <i class="bi bi-check-circle-fill text-warning"></i>
                            <span class="small text-white fw-medium">Xem trước 3 trang đầu trên web</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="home-v2-dark-pill d-flex align-items-center gap-2 h-100">
                            <i class="bi bi-check-circle-fill text-warning"></i>
                            <span class="small text-white fw-medium">Báo cáo &amp; xử lý vi phạm nhanh</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="home-v2-dark-pill d-flex align-items-center gap-2 h-100">
                            <i class="bi bi-check-circle-fill text-warning"></i>
                            <span class="small text-white fw-medium">Phân loại theo môn học &amp; từ khóa</span>
                        </div>
                    </div>
                </div>

                <a href="<?= url('?module=community&action=index') ?>" class="btn home-v2-btn-accent btn-sm rounded-pill px-4 py-2 fw-semibold">
                    Tìm hiểu kho tài liệu <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SECTION 4: ĐÓNG GÓP HỌC LIỆU & MÔN HỌC TRỌNG TÂM (Nền trắng)
     ========================================================= -->
<section class="home-v2-section-white py-5">
    <div class="container py-2">
        <div class="row align-items-center g-5">
            <!-- Cột trái: Chia sẻ tài liệu của bạn (giữ nguyên logic điều hướng login khi chưa đăng nhập) -->
            <div class="col-lg-5">
                <span class="home-v2-eyebrow">CHIA SẺ CÙNG CỘNG ĐỒNG</span>
                <h2 class="h2 fw-bold text-dark mb-3">
                    Chia Sẻ Tài Liệu <span class="home-v2-accent-italic">Của Bạn</span>
                </h2>
                <p class="text-muted mb-4">
                    Đóng góp bài giảng, đề thi hoặc đồ án đã học để xây dựng kho học liệu hữu ích cho các bạn sinh viên khóa sau.
                </p>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="<?= $shareDocUrl ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-upload me-1"></i> Tải lên tài liệu mới
                    </a>
                    <a href="<?= $askQuestionUrl ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-pencil-square me-1"></i> Đặt câu hỏi thắc mắc
                    </a>
                </div>

                <!-- Từ khóa gợi ý -->
                <?php if (!empty($popularTags)): ?>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="small text-muted fw-medium me-1">Từ khóa:</span>
                        <?php foreach (array_slice($popularTags, 0, 6) as $tag): ?>
                            <a href="<?= url('?module=community&action=index&tag_id=' . (int)$tag['id']) ?>"
                               class="badge bg-light text-dark border text-decoration-none py-1 px-2">
                                #<?= h($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cột phải: Lưới Môn học trọng tâm -->
            <div class="col-lg-7">
                <div class="p-4 rounded-4 border" style="background: var(--color-bg); border-color: var(--color-border) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 fw-bold text-dark mb-0">
                            <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Môn Học Trọng Tâm
                        </h3>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">
                            <?= count($popularSubjects) ?> môn phổ biến
                        </span>
                    </div>
                    <div class="row g-2">
                        <?php if (!empty($popularSubjects)): ?>
                            <?php foreach ($popularSubjects as $sub): ?>
                                <div class="col-sm-6">
                                    <a href="<?= url('?module=community&action=index&subject_id=' . (int)$sub['id']) ?>"
                                       class="home-v2-card p-3 d-flex justify-content-between align-items-center text-decoration-none text-dark">
                                        <span class="small fw-semibold text-truncate pe-2">
                                            <i class="bi bi-bookmark-fill text-primary me-2"></i><?= h($sub['name']) ?>
                                        </span>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SECTION 5: ĐÁNH GIÁ TỪ SINH VIÊN (3 Cards: 1 Xanh nổi bật + 2 Trắng)
     ========================================================= -->
<section class="home-v2-section-light py-5">
    <div class="container py-2">
        <div class="text-center mb-5">
            <span class="home-v2-eyebrow">PHẢN HỒI CỘNG ĐỒNG</span>
            <h2 class="h2 fw-bold text-dark mb-2">
                Được Tin Dùng Bởi <span class="home-v2-accent-italic">Sinh Viên</span>
            </h2>
            <p class="text-muted small mb-0">Những chia sẻ thực tế từ các bạn sinh viên sử dụng StudyShare mỗi ngày.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="home-v2-testimonial-featured p-4 h-100 d-flex flex-column justify-content-between shadow-sm">
                    <div>
                        <div class="text-warning mb-3 small">
                            <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small lh-lg mb-4 text-white text-opacity-90">
                            "Kho đề thi Cơ sở dữ liệu và Lập trình Web ở đây cực kỳ sát với chương trình học. Mình có thể xem trước nội dung rồi tải về ôn tập rất nhanh."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top border-white border-opacity-25">
                        <div class="rounded-circle bg-white text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">A</div>
                        <div>
                            <div class="fw-semibold small text-white">Nguyễn Văn An</div>
                            <div class="text-white text-opacity-75" style="font-size: 0.75rem;">Sinh viên Công nghệ thông tin</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="home-v2-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-warning mb-3 small">
                            <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small lh-lg mb-4 text-muted">
                            "Mục Hỏi đáp học tập giúp mình giải quyết nhiều bài tập Mạng máy tính hóc búa nhờ có phần đánh dấu câu trả lời đúng rất dễ theo dõi."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">B</div>
                        <div>
                            <div class="fw-semibold small text-dark">Trần Thị Bình</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Sinh viên An toàn thông tin</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="home-v2-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-warning mb-3 small">
                            <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i> <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small lh-lg mb-4 text-muted">
                            "Tính năng Bộ sưu tập rất tiện lợi để gom toàn bộ slide và tài liệu ôn thi cuối kỳ vào một chỗ, không phải mất công tìm lại nhiều lần."
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-3 border-top">
                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">C</div>
                        <div>
                            <div class="fw-semibold small text-dark">Lê Hoàng Cường</div>
                            <div class="text-muted" style="font-size: 0.75rem;">Sinh viên Khoa học dữ liệu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     SECTION 6: HỎI ĐÁP HỌC TẬP (Dark Mockup trái + Mô tả phải)
     ========================================================= -->
<section class="home-v2-section-white py-5">
    <div class="container py-2">
        <div class="row align-items-center g-5">
            <!-- Trái: Mockup danh sách câu hỏi gần đây -->
            <div class="col-lg-6">
                <div class="home-v2-mockup-frame p-4 text-white">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom border-white border-opacity-10">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-warning d-inline-block" style="width: 10px; height: 10px;"></span>
                            <span class="small fw-semibold">Câu hỏi học tập gần đây</span>
                        </div>
                        <a href="<?= url('?module=questions&action=index') ?>" class="text-warning small text-decoration-none">
                            Xem tất cả →
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php if (!empty($recentQuestions)): ?>
                            <?php foreach (array_slice($recentQuestions, 0, 4) as $q): ?>
                                <a href="<?= url('?module=questions&action=show&id=' . (int)$q['id']) ?>"
                                   class="home-v2-dark-pill text-decoration-none d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="small fw-semibold text-white mb-1"><?= h($q['title']) ?></div>
                                        <div class="text-white text-opacity-75" style="font-size: 0.75rem;">
                                            <span class="text-warning"><?= h($q['subject_name'] ?? 'Chung') ?></span> • <?= h($q['author_name'] ?? 'Sinh viên') ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-white text-dark rounded-pill flex-shrink-0">
                                        <?= (int)($q['answer_count'] ?? 0) ?> trả lời
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-white text-opacity-75 small py-3 text-center">Chưa có câu hỏi nào gần đây.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Phải: Giới thiệu Hỏi đáp -->
            <div class="col-lg-6">
                <span class="home-v2-eyebrow">TRAO ĐỔI HỌC THUẬT</span>
                <h2 class="h2 fw-bold text-dark mb-3">
                    Thảo Luận <span class="home-v2-accent-italic">Hỏi Đáp</span> Nhanh Chóng
                </h2>
                <p class="text-muted mb-4">
                    Gặp bài tập khó hoặc vấn đề chưa rõ khi đọc tài liệu? Hãy đặt câu hỏi để nhận hỗ trợ trực tiếp từ cộng đồng sinh viên cùng chuyên ngành.
                </p>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-2 flex-shrink-0">
                            <i class="bi bi-funnel-fill"></i>
                        </div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Lọc theo trạng thái &amp; môn học</h3>
                            <p class="small text-muted mb-0">Tìm ngay các câu hỏi đã có lời giải chuẩn hoặc câu hỏi mới đang chờ giải đáp.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-2 flex-shrink-0">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Xác nhận câu trả lời đúng</h3>
                            <p class="small text-muted mb-0">Tác giả có thể đánh dấu câu trả lời chính xác nhất để các bạn khác dễ tham khảo.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-3 bg-primary-subtle text-primary p-2 flex-shrink-0">
                            <i class="bi bi-markdown-fill"></i>
                        </div>
                        <div>
                            <h3 class="h6 fw-bold text-dark mb-1">Trình soạn thảo Markdown &amp; Ảnh</h3>
                            <p class="small text-muted mb-0">Hỗ trợ chèn công thức, đoạn mã nguồn và hình ảnh đề bài trực quan.</p>
                        </div>
                    </div>
                </div>

                <a href="<?= url('?module=questions&action=index') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                    Tham gia Hỏi đáp <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
$hideFooterCta = true;
require_once BASE_PATH . '/app/views/layouts/footer.php';
?>
