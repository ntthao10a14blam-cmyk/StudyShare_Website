<?php
/**
 * TRANG CHỦ — StudyHub
 * Phong cách Web Cộng đồng: Tối giản, gọn mắt, đồng bộ màu sắc và font chữ
 */
$pageTitle = $pageTitle ?? 'Trang chủ';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>

<div class="container py-4">

    <!-- HERO / BANNER TÌM KIẾM CỘNG ĐỒNG (BASIC, GỌN MẮT) -->
    <div class="card community-card p-4 p-md-5 mb-4 bg-white">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-primary-subtle text-primary fw-medium px-2 py-1 mb-2">
                    <i class="bi bi-mortarboard me-1"></i> Cộng đồng học tập sinh viên
                </span>
                <h1 class="h3 fw-bold text-dark mb-2">Chia sẻ tài liệu & Trao đổi tri thức</h1>
                <p class="text-muted mb-4 small">
                    Khám phá kho bài giảng, đề thi, đồ án mẫu và trao đổi bài vở cùng bạn bè sinh viên trong trường.
                </p>

                <!-- Thanh tìm kiếm tối giản -->
                <form action="<?= url() ?>" method="GET" class="community-search-box mb-3">
                    <input type="hidden" name="module" value="community">
                    <input type="hidden" name="action" value="index">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0"
                               placeholder="Tìm kiếm giáo trình, đề thi, câu hỏi theo môn học...">
                        <button type="submit" class="btn btn-primary px-3">
                            Tìm kiếm
                        </button>
                    </div>
                </form>

                <!-- Tag gợi ý nhanh -->
                <div class="d-flex align-items-center gap-2 flex-wrap small">
                    <span class="text-muted"><i class="bi bi-lightning-charge me-1"></i>Từ khóa gợi ý:</span>
                    <?php if (!empty($popularTags)): ?>
                        <?php foreach (array_slice($popularTags, 0, 5) as $tag): ?>
                            <a href="<?= url('?module=community&action=index&tag=' . urlencode($tag['name'])) ?>"
                               class="badge-tag text-decoration-none">
                                #<?= h($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thống kê súc tích -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="d-flex flex-column gap-2">
                    <div class="stat-box p-3">
                        <div class="stat-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-dark"><?= number_format($stats['documents']) ?></div>
                            <div class="text-muted small">Tài liệu học tập</div>
                        </div>
                    </div>
                    <div class="stat-box p-3">
                        <div class="stat-icon">
                            <i class="bi bi-question-circle"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-dark"><?= number_format($stats['questions']) ?></div>
                            <div class="text-muted small">Câu hỏi thảo luận</div>
                        </div>
                    </div>
                    <div class="stat-box p-3">
                        <div class="stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-dark"><?= number_format($stats['users']) ?></div>
                            <div class="text-muted small">Sinh viên tham gia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NỘI DUNG CHÍNH (2 CỘT GỌN MẮT) -->
    <div class="row g-4">
        <!-- Cột trái: Feed tài liệu & Câu hỏi mới nhất -->
        <div class="col-lg-8">

            <!-- PHẦN 1: TÀI LIỆU MỚI ĐĂNG -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-arrow-down text-primary"></i> Tài liệu mới chia sẻ
                </h2>
                <a href="<?= url('?module=community&action=index') ?>" class="small text-decoration-none hover-primary">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if (!empty($recentDocs)): ?>
                <div class="d-flex flex-column gap-2 mb-4">
                    <?php foreach ($recentDocs as $doc): ?>
                        <div class="feed-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="<?= url('?module=community&action=show&id=' . (int)$doc['id']) ?>"
                                   class="feed-title text-decoration-none">
                                    <i class="bi bi-file-pdf text-danger me-1"></i>
                                    <?= h($doc['title']) ?>
                                </a>
                                <?php if (!empty($doc['subject_name'])): ?>
                                    <span class="badge-tag flex-shrink-0">
                                        <?= h($doc['subject_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="feed-meta mt-2">
                                <span><i class="bi bi-person me-1"></i><?= h($doc['author_name'] ?? 'Ẩn danh') ?></span>
                                <span><i class="bi bi-clock me-1"></i><?= timeAgo($doc['created_at']) ?></span>
                                <span><i class="bi bi-hdd me-1"></i><?= formatFileSize((int)($doc['file_size'] ?? 0)) ?></span>
                                <span><i class="bi bi-download me-1"></i><?= number_format((int)($doc['download_count'] ?? 0)) ?> lượt tải</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card community-card text-center p-4 mb-4">
                    <i class="bi bi-inbox text-muted fs-3 mb-2"></i>
                    <p class="text-muted small mb-0">Chưa có tài liệu nào được công khai.</p>
                </div>
            <?php endif; ?>

            <!-- PHẦN 2: CÂU HỎI HỌC TẬP GẦN ĐÂY -->
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-chat-dots text-primary"></i> Hỏi đáp gần đây
                </h2>
                <a href="<?= url('?module=questions&action=index') ?>" class="small text-decoration-none hover-primary">
                    Xem tất cả <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if (!empty($recentQuestions)): ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($recentQuestions as $q): ?>
                        <div class="feed-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="<?= url('?module=questions&action=show&id=' . (int)$q['id']) ?>"
                                   class="feed-title text-decoration-none">
                                    <i class="bi bi-question-circle text-primary me-1"></i>
                                    <?= h($q['title']) ?>
                                </a>
                                <span class="badge-subtle flex-shrink-0">
                                    <i class="bi bi-chat-text me-1"></i><?= (int)($q['answer_count'] ?? 0) ?> trả lời
                                </span>
                            </div>
                            <div class="feed-meta mt-2">
                                <?php if (!empty($q['subject_name'])): ?>
                                    <span class="badge-tag"><?= h($q['subject_name']) ?></span>
                                <?php endif; ?>
                                <span><i class="bi bi-person me-1"></i><?= h($q['author_name'] ?? 'Thành viên') ?></span>
                                <span><i class="bi bi-clock me-1"></i><?= timeAgo($q['created_at']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card community-card text-center p-4">
                    <i class="bi bi-chat-square-text text-muted fs-3 mb-2"></i>
                    <p class="text-muted small mb-0">Chưa có câu hỏi học tập nào được đặt.</p>
                </div>
            <?php endif; ?>

        </div>

        <!-- Cột phải: Sidebar điều hướng & môn học nổi bật -->
        <div class="col-lg-4">

            <!-- Thẻ tác vụ nhanh -->
            <div class="card community-card p-3 mb-3">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="bi bi-plus-circle text-primary me-1"></i> Đóng góp nội dung
                </h6>
                <p class="text-muted small mb-3">
                    Chia sẻ bài giảng hoặc đặt câu hỏi để nhận trợ giúp từ bạn bè trong trường.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?= url('?module=documents&action=create') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-cloud-upload me-1"></i> Tải lên tài liệu
                    </a>
                    <a href="<?= url('?module=questions&action=create') ?>" class="btn btn-light-subtle btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Đặt câu hỏi học tập
                    </a>
                </div>
            </div>

            <!-- Danh mục môn học -->
            <div class="card community-card p-3 mb-3">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="bi bi-bookmark text-primary me-1"></i> Môn học quan tâm
                </h6>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <?php if (!empty($popularSubjects)): ?>
                        <?php foreach ($popularSubjects as $sub): ?>
                            <a href="<?= url('?module=community&action=index&subject=' . (int)$sub['id']) ?>"
                               class="badge-subtle text-decoration-none py-1 px-2 hover-primary">
                                <?= h($sub['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quy tắc cộng đồng basic -->
            <div class="card community-card p-3">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="bi bi-shield-check text-success me-1"></i> Tiêu chuẩn cộng đồng
                </h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-2">
                    <li><i class="bi bi-check2 text-primary me-1"></i> Chia sẻ tài liệu học tập chính xác, rõ ràng.</li>
                    <li><i class="bi bi-check2 text-primary me-1"></i> Ghi rõ môn học và từ khoá để dễ tìm kiếm.</li>
                    <li><i class="bi bi-check2 text-primary me-1"></i> Thảo luận văn minh, tôn trọng ý kiến thành viên.</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
