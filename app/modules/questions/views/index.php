<?php

$pageTitle = $pageTitle ?? 'Hỏi đáp học tập';
require_once BASE_PATH . '/app/views/layouts/header.php';

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

<section class="text-white py-5" style="background-color: #075e57; border-bottom: 3px solid #f5d77a;">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h1 class="h2 fw-bold text-white mb-1">Hỏi Đáp Học Tập</h1>
                <p class="text-white text-opacity-75 small mb-0">
                    Đặt câu hỏi bài tập, trao đổi kiến thức chuyên ngành và cùng cộng đồng sinh viên StudyShare tìm lời giải chuẩn xác
                </p>
            </div>
            <?php if (Auth::isActive()): ?>
                <a href="<?= url('?module=questions&action=create') ?>"
                   class="btn home-v2-btn-accent rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 flex-shrink-0">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Đặt câu hỏi mới</span>
                </a>
            <?php elseif (!Auth::check()): ?>
                <a href="<?= url('?module=auth&action=login') ?>"
                   class="btn home-v2-btn-accent rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 flex-shrink-0">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Đăng nhập để đặt câu hỏi</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="p-2 px-md-3 py-md-2 rounded-pill" style="background-color: #0b766e; border: 1px solid rgba(245, 215, 122, 0.4);">
            <form method="get" action="<?= url('/') ?>" class="row g-2 align-items-center">
                <input type="hidden" name="module" value="questions">
                <input type="hidden" name="action" value="index">
                <input type="hidden" name="status" value="<?= h($filters['status']) ?>">

                <div class="col-12 col-lg-5">
                    <div class="community-search-box bg-white rounded-pill px-2">
                        <i class="bi bi-search search-icon"></i>
                        <input type="search" id="f-q" name="q"
                               class="form-control form-control-sm border-0 shadow-none bg-transparent rounded-pill"
                               placeholder="Tìm theo tiêu đề, nội dung câu hỏi..." value="<?= h($filters['q']) ?>">
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <select id="f-subject" name="subject" class="form-select form-select-sm border-0 rounded-pill px-3">
                        <option value="">Tất cả môn học</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" <?= (int) $filters['subject_id'] === (int) $s['id'] ? 'selected' : '' ?>>
                                <?= h($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <select id="f-tag" name="tag" class="form-select form-select-sm border-0 rounded-pill px-3">
                        <option value="">Tất cả từ khóa</option>
                        <?php foreach ($tags as $t): ?>
                            <option value="<?= (int) $t['id'] ?>" <?= (int) $filters['tag_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                <?= h($t['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-flex gap-1">
                    <button class="btn home-v2-btn-accent btn-sm px-3 fw-semibold rounded-pill flex-grow-1" type="submit">
                        <i class="bi bi-funnel me-1"></i> Lọc
                    </button>
                    <?php if ($hasFilter): ?>
                        <a class="btn btn-light btn-sm rounded-pill flex-shrink-0" href="<?= url('?module=questions&action=index') ?>" title="Xóa bộ lọc">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="container py-4">

    <ul class="nav nav-tabs q-tabs mb-3">
        <?php
        $tabs = ['' => 'Tất cả câu hỏi'];
        if (Auth::check()) {
            $tabs['mine'] = 'Câu hỏi của tôi';
        }
        foreach ($tabs as $val => $label):
            $qs = $baseQuery;
            if ($val === '') unset($qs['status']); else $qs['status'] = $val;
        ?>
            <li class="nav-item">
                <a class="nav-link <?= $filters['status'] === $val ? 'active' : '' ?>"
                   href="<?= url('?' . http_build_query($qs)) ?>"><?= h($label) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <p class="text-muted small mb-3"><?= (int) $total ?> câu hỏi</p>

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
                        <p class="text-muted small mb-2">
                            <?= h(mb_strimwidth(preg_replace('/[#*_`>~\[\]]+/u', '', $q['content']), 0, 220, '…')) ?>
                        </p>
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
