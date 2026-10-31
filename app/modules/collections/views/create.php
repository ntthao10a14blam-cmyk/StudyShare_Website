<main class="container main-content py-4">
    <h1 class="page-title">Tạo bộ sưu tập</h1>
    <div class="card collection-form"><div class="card-body">
        <form method="post" action="index.php?module=collections&action=store">
            <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
            <div class="mb-3">
                <label class="form-label" for="collection-name">Tên bộ sưu tập *</label>
                <input class="form-control" id="collection-name" name="name" maxlength="150" required placeholder="Ví dụ: Tài liệu ôn thi Toán">
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Mô tả</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Ghi chú ngắn (không bắt buộc)"></textarea>
            </div>
            <button class="btn btn-primary">Lưu bộ sưu tập</button>
            <a class="btn btn-light-subtle" href="index.php?module=collections&action=index">Hủy</a>
        </form>
    </div></div>
</main>
