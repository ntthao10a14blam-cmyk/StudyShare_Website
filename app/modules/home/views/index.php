<?php
/**
 * TRANG CHỦ — StudyShare
 * Phong cách chuẩn định hướng https://www.khotailieusinhvien.com/:
 * - 3 Lối vào cốt lõi: Theo Trường | Theo Môn Học | Theo Loại Tài Liệu
 * - Thanh tìm kiếm tài liệu lớn, trực diện, gợi ý từ khoá & bộ lọc nhanh
 * - Bảng tài liệu mới đăng tải, thẻ định dạng tài liệu rõ ràng (PDF, Word, Slide)
 * - Tác vụ đóng góp, hỏi đáp và tiêu chuẩn cộng đồng sinh viên
 */
$pageTitle = $pageTitle ?? 'Trang chủ — Kho Tài Liệu Sinh Viên & Cộng Đồng Học Tập';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">

    <!-- ============================================================
         1. HERO BANNER CHUẨN KHOTAILIEUSINHVIEN.COM
         "Kho Học Liệu Rõ Ràng Cho Hành Trình Đại Học"
         ============================================================ -->
    <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #075e57 0%, #0b766e 60%, #115e59 100%);">

        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8 pe-lg-4">
                <!-- Badge định hướng -->
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" 
                     style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill text-warning"></i>
                    <span class="text-white fw-medium">Tài liệu đúng trường, đúng môn, đúng nhu cầu</span>
                </div>

                <h1 class="display-6 fw-bold mb-2 text-white">
                    Kho Học Liệu Rõ Ràng Cho Hành Trình Đại Học
                </h1>
                <p class="lead fs-6 text-white text-opacity-75 mb-4" style="max-width: 650px;">
                    Tìm giáo trình, bài giảng, đề thi và tài liệu tham khảo nhanh chóng, có cấu trúc khoa học dành riêng cho sinh viên các trường đại học.
                </p>

                <!-- THANH TÌM KIẾM TRỰC DIỆN -->
                <form action="<?= url() ?>" method="GET" class="mb-3">
                    <input type="hidden" name="module" value="community">
                    <input type="hidden" name="action" value="index">
                    <div class="bg-white rounded-pill p-1 shadow-sm d-flex align-items-center">
                        <span class="ps-3 text-muted"><i class="bi bi-search fs-5"></i></span>
                        <input type="text" name="keyword" class="form-control border-0 bg-transparent py-2 px-3 shadow-none text-dark"
                               placeholder="Tìm giáo trình, đề thi, slide bài giảng, môn học...">
                        <button type="submit" class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark flex-shrink-0">
                            Tìm kiếm
                        </button>
                    </div>
                </form>

                <!-- TỪ KHÓA GỢI Ý NHANH -->
                <div class="d-flex align-items-center gap-2 flex-wrap small">
                    <span class="text-white text-opacity-75"><i class="bi bi-fire text-warning me-1"></i>Từ khoá phổ biến:</span>
                    <?php if (!empty($popularTags)): ?>
                        <?php foreach (array_slice($popularTags, 0, 6) as $tag): ?>
                            <a href="<?= url('?module=community&action=index&tag_id=' . (int)$tag['id']) ?>"
                               class="text-decoration-none rounded-pill px-2 py-1 text-white"
                               style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.8rem;">
                                #<?= h($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="rounded-pill px-2 py-1 text-white" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.8rem;">#đề thi</span>
                        <span class="rounded-pill px-2 py-1 text-white" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.8rem;">#bài tập</span>
                        <span class="rounded-pill px-2 py-1 text-white" style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.25); font-size: 0.8rem;">#tóm tắt lý thuyết</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CỘT THỐNG KÊ NHANH -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="d-flex flex-column gap-3">
                    <!-- Ô 1: Tài liệu học tập -->
                    <div class="p-3 rounded-4 d-flex align-items-center gap-3 transition-all"
                         style="background: rgba(255, 255, 255, 0.16); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.18);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 50px; height: 50px; background: rgba(255, 193, 7, 0.22); border: 1.5px solid #ffc107; box-shadow: 0 0 16px rgba(255, 193, 7, 0.45);">
                            <i class="bi bi-file-earmark-pdf-fill fs-4 text-warning"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-white lh-1 mb-1"><?= number_format($stats['documents']) ?>+</div>
                            <div class="small text-white text-opacity-90 fw-medium">Tài liệu học tập</div>
                        </div>
                    </div>

                    <!-- Ô 2: Câu hỏi thảo luận -->
                    <div class="p-3 rounded-4 d-flex align-items-center gap-3 transition-all"
                         style="background: rgba(255, 255, 255, 0.16); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.18);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 50px; height: 50px; background: rgba(13, 202, 240, 0.22); border: 1.5px solid #0dcaf0; box-shadow: 0 0 16px rgba(13, 202, 240, 0.45);">
                            <i class="bi bi-question-circle-fill fs-4 text-info"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-white lh-1 mb-1"><?= number_format($stats['questions']) ?>+</div>
                            <div class="small text-white text-opacity-90 fw-medium">Câu hỏi thảo luận</div>
                        </div>
                    </div>

                    <!-- Ô 3: Sinh viên tham gia -->
                    <div class="p-3 rounded-4 d-flex align-items-center gap-3 transition-all"
                         style="background: rgba(255, 255, 255, 0.16); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 8px 24px -4px rgba(0, 0, 0, 0.18);">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 50px; height: 50px; background: rgba(25, 135, 84, 0.28); border: 1.5px solid #20c997; box-shadow: 0 0 16px rgba(32, 201, 151, 0.45);">
                            <i class="bi bi-people-fill fs-4" style="color: #20c997;"></i>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-white lh-1 mb-1"><?= number_format($stats['users']) ?>+</div>
                            <div class="small text-white text-opacity-90 fw-medium">Sinh viên tham gia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ============================================================
         3. DANH SÁCH TÀI LIỆU (FEED CHÍNH)
         ============================================================ -->
    <div class="row g-4 mb-5">
        <!-- Cột trái (8): Feed Tài liệu & Diễn đàn -->
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="<?= url('?module=community&action=index') ?>" class="btn btn-sm btn-light border text-decoration-none ms-auto">
                    Xem tất cả tài liệu <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>

            <?php if (!empty($recentDocs)): ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentDocs as $doc): ?>
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white hover-shadow transition-all">
                            <div class="d-flex gap-3 align-items-start">
                                <!-- Icon định dạng file chuẩn -->
                                <div class="rounded-3 bg-danger bg-opacity-10 text-danger p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                    <i class="bi bi-file-earmark-pdf fs-3"></i>
                                </div>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <a href="<?= url('?module=community&action=show&id=' . (int)$doc['id']) ?>"
                                           class="fw-bold text-dark text-decoration-none text-truncate-2 hover-primary fs-6">
                                            <?= h($doc['title']) ?>
                                        </a>
                                        <?php if (!empty($doc['subject_name'])): ?>
                                            <a href="<?= url('?module=community&action=index&subject_id=' . (int)($doc['subject_id'] ?? 0)) ?>" 
                                               class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none flex-shrink-0 py-1 px-2">
                                                <?= h($doc['subject_name']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Metadata trước khi tải: Tác giả, Thời gian, Dung lượng, Lượt tải -->
                                    <div class="d-flex align-items-center gap-3 text-muted small mt-2 flex-wrap">
                                        <span><i class="bi bi-person me-1"></i><?= h($doc['author_name'] ?? 'Thành viên StudyShare') ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= timeAgo($doc['created_at']) ?></span>
                                        <span><i class="bi bi-hdd me-1"></i><?= formatFileSize((int)($doc['file_size'] ?? 0)) ?></span>
                                        <span class="text-success fw-medium"><i class="bi bi-download me-1"></i><?= number_format((int)($doc['download_count'] ?? 0)) ?> lượt tải</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 text-center p-5 bg-white">
                    <i class="bi bi-inbox text-muted fs-1 mb-2"></i>
                    <p class="text-muted mb-0">Chưa có tài liệu công khai nào trong hệ thống.</p>
                </div>
            <?php endif; ?>


            <!-- ============================================================
                 4. GÓC HỎI ĐÁP HỌC TẬP SINH VIÊN
                 ============================================================ -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
                <a href="<?= url('?module=questions&action=index') ?>" class="btn btn-sm btn-light border text-decoration-none ms-auto">
                    Xem tất cả câu hỏi <i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>

            <?php if (!empty($recentQuestions)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($recentQuestions as $q): ?>
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white hover-shadow transition-all">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="<?= url('?module=questions&action=show&id=' . (int)$q['id']) ?>"
                                   class="fw-semibold text-dark text-decoration-none hover-primary">
                                    <i class="bi bi-question-circle text-primary me-1"></i>
                                    <?= h($q['title']) ?>
                                </a>
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle flex-shrink-0">
                                    <i class="bi bi-chat-left-text me-1"></i><?= (int)($q['answer_count'] ?? 0) ?> trả lời
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-3 text-muted small mt-2">
                                <?php if (!empty($q['subject_name'])): ?>
                                    <span class="badge bg-light text-secondary border"><?= h($q['subject_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="bi bi-person me-1"></i><?= h($q['author_name'] ?? 'Sinh viên') ?></span>
                                <span><i class="bi bi-clock me-1"></i><?= timeAgo($q['created_at']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 text-center p-4 bg-white">
                    <p class="text-muted small mb-0">Chưa có câu hỏi thảo luận nào.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cột phải (4): Tác vụ đóng góp, Môn học nổi bật & Tiêu chuẩn học liệu -->
        <div class="col-lg-4">
            
            <!-- Hộp Đóng góp / Chia sẻ tài liệu -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-cloud-arrow-up-fill fs-5"></i>
                    </div>
                    <h3 class="h6 fw-bold text-dark mb-0">Chia sẻ tài liệu của bạn</h3>
                </div>
                <p class="text-muted small mb-3">
                    Đóng góp bài giảng, đề thi hoặc đồ án đã học để xây dựng kho học liệu hữu ích cho các bạn sinh viên khóa sau.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-primary rounded-pill btn-sm py-2">
                        <i class="bi bi-upload me-1"></i> Tải lên tài liệu mới
                    </a>
                    <a href="<?= url('?module=questions&action=create') ?>" class="btn btn-outline-secondary rounded-pill btn-sm py-2">
                        <i class="bi bi-pencil-square me-1"></i> Đặt câu hỏi thắc mắc
                    </a>
                </div>
            </div>

            <!-- Danh mục môn học trọng tâm -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill text-primary"></i> Môn học trọng tâm
                </h3>
                <div class="d-flex flex-column gap-2">
                    <?php if (!empty($popularSubjects)): ?>
                        <?php foreach ($popularSubjects as $sub): ?>
                            <a href="<?= url('?module=community&action=index&subject_id=' . (int)$sub['id']) ?>"
                               class="d-flex justify-content-between align-items-center text-decoration-none p-2 rounded-2 hover-bg-light text-dark small border-bottom border-light">
                                <span class="fw-medium"><i class="bi bi-bookmark text-muted me-2"></i><?= h($sub['name']) ?></span>
                                <i class="bi bi-chevron-right text-muted" style="font-size: 11px;"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tiêu chuẩn tài liệu (Chuẩn hoá theo khotailieusinhvien.com) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success"></i> Tiêu chuẩn học liệu
                </h3>
                <ul class="list-unstyled small text-muted d-flex flex-column gap-2 mb-0">
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle text-primary mt-1"></i>
                        <span><strong>Đúng trường, đúng môn:</strong> Tài liệu gắn chính xác theo chương trình học đại học.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle text-primary mt-1"></i>
                        <span><strong>Minh bạch thông tin:</strong> Tóm tắt nội dung, nguồn tài liệu và tác giả rõ ràng trước khi tải.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="bi bi-check-circle text-primary mt-1"></i>
                        <span><strong>Đã qua kiểm duyệt:</strong> Admin kiểm duyệt tính xác thực và an toàn nội dung trước khi xuất bản.</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
