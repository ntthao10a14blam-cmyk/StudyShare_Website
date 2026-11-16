<main class="container main-content py-4">
    <a href="index.php?module=collections&action=index" class="small">← Quay lại bộ sưu tập</a>
    <h1 class="page-title mt-3"><?= h($collection['name']) ?></h1>
    <?php if (!empty($collection['description'])): ?>
        <p class="text-muted"><?= h($collection['description']) ?></p>
    <?php endif; ?>

    <div class="card mb-4"><div class="card-body">
        <h2 class="h6">Thêm tài liệu vào bộ sưu tập</h2>
        <?php if (empty($available)): ?>
            <p class="text-muted small mb-0">Không có tài liệu nào khác để thêm.</p>
        <?php else: ?>
            <form method="post" action="index.php?module=collections&action=addDocument" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                <input type="hidden" name="id" value="<?= (int) $collection['id'] ?>">
                <select class="form-select collection-select" name="document_id" required>
                    <option value="">-- Chọn tài liệu --</option>
                    <?php foreach ($available as $doc): ?>
                        <option value="<?= (int) $doc['id'] ?>"><?= h($doc['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary">Thêm</button>
            </form>
        <?php endif; ?>
    </div></div>

    <h2 class="h5 mb-3">Tài liệu trong bộ sưu tập (<?= count($documents) ?>)</h2>
    <?php if (empty($documents)): ?>
        <div class="empty-state">Bộ sưu tập chưa có tài liệu.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($documents as $doc): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <a href="index.php?module=<?= ((int) $doc['user_id'] === $tv4UserId) ? 'documents' : 'community' ?>&action=show&id=<?= (int) $doc['id'] ?>">
                        <?= h($doc['title']) ?>
                    </a>
                    <form method="post" action="index.php?module=collections&action=removeDocument" data-confirm="Bỏ tài liệu khỏi bộ sưu tập?">
                        <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                        <input type="hidden" name="id" value="<?= (int) $collection['id'] ?>">
                        <input type="hidden" name="document_id" value="<?= (int) $doc['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm">Bỏ ra</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
