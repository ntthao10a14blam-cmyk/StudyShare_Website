<?php
/**
 * TV4 – Nút Yêu thích và modal Lưu vào bộ sưu tập.
 * Dùng trong trang chủ (TV1) hoặc trang chi tiết tài liệu (TV3).
 * Nếu TV3 đã có sẵn hai nút này, KHÔNG gọi thêm để tránh lặp.
 */
require_once __DIR__ . '/CollectionModel.php';

function tv4Model(): CollectionModel
{
    static $model = null;
    if ($model === null) {
        $model = new CollectionModel(Database::getInstance()->getPdo());
    }
    return $model;
}

function tv4Token(): string
{
    if (empty($_SESSION['tv4_token'])) {
        $_SESSION['tv4_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['tv4_token'];
}

function tv4FavoriteButton(int $documentId, string $source = 'home'): void
{
    if ($documentId < 1) return;
    if (!Auth::check()) {
        echo '<a class="btn btn-outline-danger btn-sm" href="' . h(url('?module=auth&action=login')) . '">' .
             '<i class="bi bi-heart"></i> Đăng nhập để thích</a>';
        return;
    }
    if (!Auth::isActive() || !Auth::isUser()) return;

    $model = tv4Model();
    $userId = (int) Auth::id();
    if (!$model->accessibleDocument($documentId, $userId)) return;

    $liked = $model->isFavorite($userId, $documentId);
    $source = ($source === 'community') ? 'community' : 'home';
    ?>
    <form method="post" class="d-inline"
          action="<?= h(url('?module=collections&action=toggleFavorite')) ?>">
        <input type="hidden" name="tv4_token" value="<?= h(tv4Token()) ?>">
        <input type="hidden" name="document_id" value="<?= $documentId ?>">
        <input type="hidden" name="return_to" value="<?= h($source) ?>">
        <button type="submit" class="btn btn-sm <?= $liked ? 'btn-danger' : 'btn-outline-danger' ?>">
            <i class="bi <?= $liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            <?= $liked ? 'Bỏ yêu thích' : 'Yêu thích' ?>
        </button>
    </form>
    <?php
}

function tv4SaveCollectionModal(int $documentId): void
{
    if ($documentId < 1) return;
    if (!Auth::check()) {
        echo '<a class="btn btn-outline-primary btn-sm" href="' . h(url('?module=auth&action=login')) . '">' .
             'Đăng nhập để lưu</a>';
        return;
    }
    if (!Auth::isActive() || !Auth::isUser()) return;

    $model = tv4Model();
    $userId = (int) Auth::id();
    if (!$model->accessibleDocument($documentId, $userId)) return;

    $collections = $model->all($userId);
    $modalId = 'tv4SaveModal' . $documentId;
    ?>
    <button type="button" class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal" data-bs-target="#<?= h($modalId) ?>">
        <i class="bi bi-bookmark-plus"></i> Lưu vào bộ sưu tập
    </button>

    <div class="modal fade" id="<?= h($modalId) ?>" tabindex="-1"
         aria-hidden="true" aria-labelledby="<?= h($modalId) ?>Title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="<?= h($modalId) ?>Title">Lưu tài liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <?php if (!$collections): ?>
                        <p>Bạn chưa có bộ sưu tập nào.</p>
                        <a href="<?= h(url('?module=collections&action=create')) ?>"
                           class="btn btn-primary">Tạo bộ sưu tập</a>
                    <?php else: ?>
                        <form method="post" action="<?= h(url('?module=collections&action=addDocument')) ?>">
                            <input type="hidden" name="tv4_token" value="<?= h(tv4Token()) ?>">
                            <input type="hidden" name="document_id" value="<?= $documentId ?>">
                            <input type="hidden" name="return_to" value="community">
                            <label class="form-label" for="tv4Collection<?= $documentId ?>">Chọn bộ sưu tập</label>
                            <select class="form-select mb-3" id="tv4Collection<?= $documentId ?>"
                                    name="id" required>
                                <option value="">-- Chọn bộ sưu tập --</option>
                                <?php foreach ($collections as $collection): ?>
                                    <option value="<?= (int) $collection['id'] ?>">
                                        <?= h($collection['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Lưu tài liệu</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
