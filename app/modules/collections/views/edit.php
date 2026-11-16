<main class="container main-content py-4">
    <h1 class="page-title">Sửa bộ sưu tập</h1>
    <div class="card collection-form"><div class="card-body">
        <form method="post" action="index.php?module=collections&action=update">
            <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
            <input type="hidden" name="id" value="<?= (int) $collection['id'] ?>">
            <div class="mb-3">
                <label class="form-label" for="collection-name">Tên bộ sưu tập *</label>
                <input class="form-control" id="collection-name" name="name" maxlength="150" required value="<?= h($collection['name']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?= h($collection['description'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary">Lưu thay đổi</button>
            <a class="btn btn-light-subtle" href="index.php?module=collections&action=index">Hủy</a>
        </form>
    </div></div>
</main>
