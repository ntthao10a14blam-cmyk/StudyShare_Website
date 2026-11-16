<main class="container main-content py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-title mb-0">Bộ sưu tập của tôi</h1>
        <div>
            <a class="btn btn-outline-primary" href="index.php?module=collections&action=favorites">Tài liệu yêu thích</a>
            <a class="btn btn-primary" href="index.php?module=collections&action=create">+ Tạo bộ sưu tập</a>
        </div>
    </div>

    <?php if (empty($collections)): ?>
        <div class="empty-state">Bạn chưa có bộ sưu tập nào. Hãy tạo bộ sưu tập đầu tiên!</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($collections as $item): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card collection-card h-100">
                        <div class="card-body">
                            <h2 class="h5"><?= h($item['name']) ?></h2>
                            <p class="text-muted small"><?= h($item['description'] ?: 'Chưa có mô tả') ?></p>
                            <p class="small mb-3"><?= (int) $item['total'] ?> tài liệu</p>
                            <a class="btn btn-primary btn-sm" href="index.php?module=collections&action=show&id=<?= (int) $item['id'] ?>">Xem</a>
                            <a class="btn btn-outline-primary btn-sm" href="index.php?module=collections&action=edit&id=<?= (int) $item['id'] ?>">Sửa</a>
                            <form class="d-inline" method="post" action="index.php?module=collections&action=delete" data-confirm="Xóa bộ sưu tập này? Tài liệu gốc sẽ không bị xóa.">
                                <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm">Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
