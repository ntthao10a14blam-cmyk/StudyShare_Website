<main class="container main-content py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="page-title mb-0">Tài liệu yêu thích</h1>
        <a class="btn btn-outline-primary" href="index.php?module=collections&action=index">Bộ sưu tập của tôi</a>
    </div>

    <div class="card mb-4"><div class="card-body">
        <h2 class="h6">Thêm tài liệu yêu thích</h2>
        <?php if (empty($available)): ?>
            <p class="text-muted small mb-0">Không có tài liệu mới để thêm.</p>
        <?php else: ?>
            <form method="post" action="index.php?module=collections&action=toggleFavorite" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                <select class="form-select collection-select" name="document_id" required>
                    <option value="">-- Chọn tài liệu --</option>
                    <?php foreach ($available as $doc): ?>
                        <option value="<?= (int) $doc['id'] ?>"><?= h($doc['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary">Yêu thích</button>
            </form>
        <?php endif; ?>
    </div></div>

    <?php if (empty($favorites)): ?>
        <div class="empty-state">Bạn chưa yêu thích tài liệu nào.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($favorites as $doc): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <a href="index.php?module=<?= ((int) $doc['user_id'] === $tv4UserId) ? 'documents' : 'community' ?>&action=show&id=<?= (int) $doc['id'] ?>">
                        <?= h($doc['title']) ?>
                    </a>
                    <form method="post" action="index.php?module=collections&action=toggleFavorite" data-confirm="Bỏ yêu thích tài liệu này?">
                        <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                        <input type="hidden" name="document_id" value="<?= (int) $doc['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm">Bỏ yêu thích</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
