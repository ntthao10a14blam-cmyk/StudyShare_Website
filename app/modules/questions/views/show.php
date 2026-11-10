<?php
/**
 * Chi tiết câu hỏi + câu trả lời — TV5
 * Biến: $question, $tags, $images, $answers, $isOwner, $isAdmin, $canAnswer,
 *       $currentUid, $reportReasons
 */
$pageTitle = $pageTitle ?? 'Chi tiết câu hỏi';
require_once BASE_PATH . '/app/views/layouts/header.php';

$qid      = (int) $question['id'];
$accepted = $question['accepted_answer_id'] !== null;
$self     = url('?module=questions&action=show&id=' . $qid);

/** Avatar nhỏ: ảnh nếu có, không thì icon */
$avatar = function (?string $path, string $name): string {
    if ($path) {
        return '<img class="avatar avatar-sm" src="' . h(storageUrl($path)) . '" alt="' . h($name) . '">';
    }
    return '<i class="bi bi-person-circle fs-4 text-secondary"></i>';
};
?>
<div class="container py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('?module=questions&action=index') ?>">Hỏi đáp</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= h($question['subject_name']) ?></li>
        </ol>
    </nav>

    <!-- CÂU HỎI -->
    <article class="card card-body mb-4">
        <?php if ($question['status'] === 'hidden'): ?>
            <div class="alert alert-warning py-2">Câu hỏi này đang bị quản trị viên ẩn — chỉ bạn và admin nhìn thấy.</div>
        <?php endif; ?>

        <h1 class="h3 mb-2">
            <?= h($question['title']) ?>
            <?php if ($accepted): ?><span class="badge bg-success align-middle fs-6"><i class="bi bi-check-lg"></i> Đã có đáp án</span><?php endif; ?>
        </h1>

        <div class="d-flex flex-wrap align-items-center gap-2 text-muted small mb-3">
            <?= $avatar($question['author_avatar'], $question['author_name']) ?>
            <span><?= h($question['author_name']) ?></span>
            <span>· <?= h(formatDateTime($question['created_at'])) ?></span>
            <a class="badge badge-tag" href="<?= url('?module=questions&action=index&subject=' . (int) $question['subject_id']) ?>">
                <?= h($question['subject_name']) ?>
            </a>
            <?php foreach ($tags as $t): ?>
                <a class="badge text-bg-light border" href="<?= url('?module=questions&action=index&tag=' . (int) $t['id']) ?>">#<?= h($t['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <!-- Hiển thị an toàn: escape HTML rồi mới đổi xuống dòng -->
        <div class="q-body mb-3"><?= nl2br(h($question['content'])) ?></div>

        <?php if ($images): ?>
            <?php if (Auth::check()): ?>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($images as $im): ?>
                        <a href="<?= h(storageUrl($im['image_path'])) ?>" target="_blank" rel="noopener">
                            <img class="q-thumb" src="<?= h(storageUrl($im['image_path'])) ?>" alt="Ảnh đính kèm">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted small"><i class="bi bi-images"></i>
                    Câu hỏi có <?= count($images) ?> ảnh đính kèm.
                    <a href="<?= url('?module=auth&action=login') ?>">Đăng nhập</a> để xem.
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Thao tác -->
        <div class="d-flex flex-wrap gap-2 border-top pt-3">
            <?php if ($isOwner): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('?module=questions&action=edit&id=' . $qid) ?>">
                    <i class="bi bi-pencil"></i> Sửa
                </a>
            <?php endif; ?>
            <?php if ($isOwner || $isAdmin): ?>
                <form method="post" action="<?= url('?module=questions&action=delete') ?>"
                      data-confirm="Xóa câu hỏi này? Thao tác không thể hoàn tác.">
                    <?php csrfField(); ?>
                    <input type="hidden" name="id" value="<?= $qid ?>">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Xóa</button>
                </form>
            <?php endif; ?>
            <?php if (Auth::isActive() && !$isOwner): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-auto"
                        data-bs-toggle="modal" data-bs-target="#reportModal"
                        data-report-type="question" data-report-id="<?= $qid ?>" data-report-label="câu hỏi này">
                    <i class="bi bi-flag"></i> Báo cáo
                </button>
            <?php endif; ?>
        </div>
    </article>

    <!-- CÂU TRẢ LỜI -->
    <h2 class="h5 mb-3"><?= count($answers) ?> câu trả lời</h2>

    <div class="d-flex flex-column gap-3 mb-4">
    <?php foreach ($answers as $a): ?>
        <?php
            $aid       = (int) $a['id'];
            $isAccept  = (bool) $a['is_accepted'];
            $isMine    = $currentUid !== null && (int) $a['user_id'] === (int) $currentUid;
        ?>
        <article id="answer-<?= $aid ?>" class="card card-body <?= $isAccept ? 'q-answer-accepted' : '' ?>">
            <?php if ($isAccept): ?>
                <div class="text-success fw-semibold mb-2"><i class="bi bi-check-circle-fill"></i> Câu trả lời được chấp nhận</div>
            <?php endif; ?>

            <div class="q-body mb-3"><?= nl2br(h($a['content'])) ?></div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <?= $avatar($a['author_avatar'], $a['author_name']) ?>
                <span class="small"><?= h($a['author_name']) ?></span>
                <span class="small text-muted">· <?= h(timeAgo($a['created_at'])) ?><?= $a['updated_at'] !== $a['created_at'] ? ' (đã sửa)' : '' ?></span>

                <div class="ms-auto d-flex flex-wrap gap-2">
                    <?php if ($isOwner): ?>
                        <form method="post" action="<?= url('?module=questions&action=' . ($isAccept ? 'unacceptAnswer' : 'acceptAnswer')) ?>">
                            <?php csrfField(); ?>
                            <input type="hidden" name="question_id" value="<?= $qid ?>">
                            <input type="hidden" name="answer_id" value="<?= $aid ?>">
                            <?php if ($isAccept): ?>
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Bỏ chấp nhận</button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle"></i> Chấp nhận</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>

                    <?php if ($isMine): ?>
                        <button class="btn btn-sm btn-outline-primary" type="button"
                                data-bs-toggle="collapse" data-bs-target="#edit-answer-<?= $aid ?>">
                            <i class="bi bi-pencil"></i> Sửa
                        </button>
                    <?php endif; ?>
                    <?php if ($isMine || $isAdmin): ?>
                        <form method="post" action="<?= url('?module=questions&action=deleteAnswer') ?>"
                              data-confirm="Xóa câu trả lời này?">
                            <?php csrfField(); ?>
                            <input type="hidden" name="question_id" value="<?= $qid ?>">
                            <input type="hidden" name="answer_id" value="<?= $aid ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Xóa</button>
                        </form>
                    <?php endif; ?>
                    <?php if (Auth::isActive() && !$isMine): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal" data-bs-target="#reportModal"
                                data-report-type="answer" data-report-id="<?= $aid ?>" data-report-label="câu trả lời này">
                            <i class="bi bi-flag"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isMine): ?>
                <div class="collapse mt-3" id="edit-answer-<?= $aid ?>">
                    <form method="post" action="<?= url('?module=questions&action=editAnswer') ?>">
                        <?php csrfField(); ?>
                        <input type="hidden" name="question_id" value="<?= $qid ?>">
                        <input type="hidden" name="answer_id" value="<?= $aid ?>">
                        <textarea name="content" rows="5" class="form-control mb-2" required><?= h($a['content']) ?></textarea>
                        <button class="btn btn-sm btn-primary">Lưu</button>
                    </form>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    </div>

    <!-- FORM TRẢ LỜI -->
    <?php if ($canAnswer): ?>
        <div class="card card-body" id="answer-form">
            <h2 class="h5">Câu trả lời của bạn</h2>
            <form method="post" action="<?= url('?module=questions&action=answer') ?>">
                <?php csrfField(); ?>
                <input type="hidden" name="question_id" value="<?= $qid ?>">
                <textarea name="content" rows="6" class="form-control mb-2" required
                          placeholder="Viết câu trả lời của bạn..."></textarea>
                <button class="btn btn-primary"><i class="bi bi-send me-1"></i> Đăng trả lời</button>
            </form>
        </div>
    <?php elseif (!Auth::check()): ?>
        <div class="card card-body text-center">
            <a href="<?= url('?module=auth&action=login') ?>">Đăng nhập</a> để trả lời câu hỏi này.
        </div>
    <?php endif; ?>


<!-- MODAL BÁO CÁO (dùng chung cho câu hỏi và câu trả lời; questions.js điền target) -->
<?php if (Auth::isActive()): ?>
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="<?= url('?module=questions&action=report') ?>">
            <?php csrfField(); ?>
            <input type="hidden" name="question_id" value="<?= $qid ?>">
            <input type="hidden" name="target_type" id="report-type" value="question">
            <input type="hidden" name="target_id"   id="report-id"   value="<?= $qid ?>">
            <div class="modal-header">
                <h5 class="modal-title">Báo cáo <span id="report-label">câu hỏi này</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="report-reason">Lý do</label>
                <select name="reason" id="report-reason" class="form-select mb-3" required>
                    <option value="">-- Chọn lý do --</option>
                    <?php foreach ($reportReasons as $r): ?>
                        <option value="<?= h($r) ?>"><?= h($r) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label" for="report-details">Mô tả thêm (không bắt buộc)</label>
                <textarea name="details" id="report-details" rows="3" maxlength="1000" class="form-control"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-danger">Gửi báo cáo</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
