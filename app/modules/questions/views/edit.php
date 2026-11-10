<?php
/** Form sửa câu hỏi — TV5. Biến: $question, $subjects, $tags, $images, $old, $errors */
$pageTitle = $pageTitle ?? 'Sửa câu hỏi';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>
<div class="container py-4">
    <h1 class="page-title">Sửa câu hỏi</h1>

    <div class="card card-body">
        <form method="post" action="<?= url('?module=questions&action=update') ?>" enctype="multipart/form-data">
            <?php csrfField(); ?>
            <input type="hidden" name="id" value="<?= (int) $question['id'] ?>">
            <?php require __DIR__ . '/_form.php'; ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Lưu thay đổi</button>
                <a href="<?= url('?module=questions&action=show&id=' . (int) $question['id']) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
