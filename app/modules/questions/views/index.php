<?php
/**
 * Danh sách câu hỏi — TV5
 * Biến từ controller: $questions, $subjects, $tags, $filters, $pg, $total
 */
$pageTitle = $pageTitle ?? 'Hỏi đáp học tập';
require_once BASE_PATH . '/app/views/layouts/header.php';

// Query string giữ nguyên bộ lọc khi chuyển trang
$baseQuery = array_filter([
    'module'  => 'questions',
    'action'  => 'index',
    'q'       => $filters['q'],
    'subject' => $filters['subject_id'] ?: '',
    'tag'     => $filters['tag_id'] ?: '',
    'status'  => $filters['status'],
]);
$hasFilter = $filters['q'] !== '' || $filters['subject_id'] || $filters['tag_id'] || $filters['status'] !== '';
?>
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="page-title mb-0">Hỏi đáp học tập</h1>
        <?php if (Auth::isActive()): ?>
            <a href="<?= url('?module=questions&action=create') ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Đặt câu hỏi
            </a>
        <?php elseif (!Auth::check()): ?>
            <a href="<?= url('?module=auth&action=login') ?>" class="btn btn-outline-primary">
                Đăng nhập để đặt câu hỏi
            </a>
        <?php endif; ?>
    </div>

    <!-- BỘ LỌC -->
    <form method="get" action="<?= url('/') ?>" class="card card-body mb-4 q-filter">
        <input type="hidden" name="module" value="questions">
        <input type="hidden" name="action" value="index">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small mb-1" for="f-q">Từ khóa</label>
                <input type="search" id="f-q" name="q" class="form-control"
                       placeholder="Tìm theo tiêu đề, nội dung..." value="<?= h($filters['q']) ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small mb-1" for="f-subject">Môn học</label>
                <select id="f-subject" name="subject" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int) $s['id'] ?>" <?= (int) $filters['subject_id'] === (int) $s['id'] ? 'selected' : '' ?>>
                            <?= h($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small mb-1" for="f-tag">Từ khóa gắn</label>
                <select id="f-tag" name="tag" class="form-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($tags as $t): ?>
                        <option value="<?= (int) $t['id'] ?>" <?= (int) $filters['tag_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                            <?= h($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small mb-1" for="f-status">Trạng thái</label>
                <select id="f-status" name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="answered"   <?= $filters['status'] === 'answered'   ? 'selected' : '' ?>>Đã có đáp án</option>
                    <option value="unanswered" <?= $filters['status'] === 'unanswered' ? 'selected' : '' ?>>Chưa có đáp án</option>
                </select>
            </div>
            <div class="col-6 col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search"></i> Lọc</button>
                <?php if ($hasFilter): ?>
                    <a class="btn btn-outline-secondary" href="<?= url('?module=questions&action=index') ?>" title="Xóa bộ lọc">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <p class="text-muted small mb-3"><?= (int) $total ?> câu hỏi</p>

    <!-- DANH SÁCH -->
    <?php if (!$questions): ?>
        <div class="card card-body text-center text-muted py-5">
            <i class="bi bi-chat-square-dots display-5"></i>
            <p class="mt-3 mb-0">
                <?= $hasFilter ? 'Không tìm thấy câu hỏi nào phù hợp bộ lọc.' : 'Chưa có câu hỏi nào. Hãy là người đầu tiên đặt câu hỏi!' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
        <?php foreach ($questions as $q): ?>
            <?php $answered = $q['accepted_answer_id'] !== null; ?>
            <article class="feed-item q-item">
                <div class="d-flex gap-3">
                    <div class="q-stats text-center flex-shrink-0">
                        <div class="q-count <?= $answered ? 'q-count-accepted' : ($q['answer_count'] > 0 ? 'q-count-has' : '') ?>">
                            <div class="fw-bold fs-5"><?= (int) $q['answer_count'] ?></div>
                            <div class="small">trả lời</div>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h2 class="h5 mb-1">
                            <a href="<?= url('?module=questions&action=show&id=' . (int) $q['id']) ?>" class="q-title">
                                <?= h($q['title']) ?>
                            </a>
                            <?php if ($answered): ?>
                                <span class="badge bg-success ms-1"><i class="bi bi-check-lg"></i> Đã có đáp án</span>
                            <?php endif; ?>
                        </h2>
                        <p class="text-muted mb-2 text-truncate-2"><?= h(mb_strimwidth($q['content'], 0, 220, '…')) ?></p>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a class="badge badge-tag"
                               href="<?= url('?module=questions&action=index&subject=' . (int) $q['subject_id']) ?>">
                                <i class="bi bi-journal-bookmark"></i> <?= h($q['subject_name']) ?>
                            </a>
                            <?php foreach ($q['tags'] as $t): ?>
                                <a class="badge text-bg-light border"
                                   href="<?= url('?module=questions&action=index&tag=' . (int) $t['id']) ?>">#<?= h($t['name']) ?></a>
                            <?php endforeach; ?>
                            <span class="ms-auto small text-muted">
                                <?= h($q['author_name']) ?> · <?= h(timeAgo($q['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($pg['pages'] > 1): ?>
            <nav class="mt-4" aria-label="Phân trang">
                <ul class="pagination justify-content-center flex-wrap">
                    <?php for ($p = 1; $p <= $pg['pages']; $p++): ?>
                        <li class="page-item <?= $p === $pg['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('?' . http_build_query($baseQuery + ['page' => $p])) ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
