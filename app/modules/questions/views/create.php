<?php
/** Form đặt câu hỏi — TV5. Biến: $subjects, $tags, $old, $errors */
$pageTitle = $pageTitle ?? 'Đặt câu hỏi';
require_once BASE_PATH . '/app/views/layouts/header.php';
?>
<div class="container py-4">
    <h1 class="page-title">Đặt câu hỏi mới</h1>

    <div class="card card-body">
        <form method="post" action="<?= url('?module=questions&action=store') ?>" enctype="multipart/form-data">
            <?php csrfField(); ?>
            <?php require __DIR__ . '/_form.php'; ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Đăng câu hỏi</button>
                <a href="<?= url('?module=questions&action=index') ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
