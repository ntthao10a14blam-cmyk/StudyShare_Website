<?php
/**
 * Phần form dùng chung cho create.php và edit.php — TV5
 * Biến cần có: $subjects, $tags, $old, $errors, $images (chỉ khi sửa)
 */
$errors = $errors ?? [];
$images = $images ?? [];
$err = fn(string $k) => isset($errors[$k]) ? '<div class="invalid-feedback d-block">' . h($errors[$k]) . '</div>' : '';
?>
<div class="mb-3">
    <label class="form-label" for="q-title">Tiêu đề <span class="text-danger">*</span></label>
    <input type="text" id="q-title" name="title" maxlength="255" required
           class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
           placeholder="Ví dụ: Khác nhau giữa INNER JOIN và LEFT JOIN?"
           value="<?= h($old['title']) ?>">
    <?= $err('title') ?>
</div>

<div class="mb-3">
    <label class="form-label" for="q-subject">Môn học <span class="text-danger">*</span></label>
    <select id="q-subject" name="subject_id" required
            class="form-select <?= isset($errors['subject_id']) ? 'is-invalid' : '' ?>">
        <option value="">-- Chọn môn học --</option>
        <?php foreach ($subjects as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= (int) $old['subject_id'] === (int) $s['id'] ? 'selected' : '' ?>>
                <?= h($s['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?= $err('subject_id') ?>
</div>

<div class="mb-3">
    <label class="form-label" for="q-content">Nội dung <span class="text-danger">*</span></label>
    <textarea id="q-content" name="content" rows="9" required data-counter="#q-content-count" data-counter-max="20000"
              class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>"
              placeholder="Mô tả chi tiết vấn đề bạn gặp phải..."><?= h($old['content']) ?></textarea>
    <div class="form-text text-end"><span id="q-content-count">0</span>/20000</div>
    <?= $err('content') ?>
</div>

<div class="mb-3">
    <label class="form-label">Từ khóa <span class="text-muted fw-normal">(tối đa 5)</span></label>
    <div class="d-flex flex-wrap gap-2" data-max-checked="5">
        <?php foreach ($tags as $t): ?>
            <input type="checkbox" class="btn-check q-tag-check" name="tags[]" id="tag-<?= (int) $t['id'] ?>"
                   value="<?= (int) $t['id'] ?>" <?= in_array((int) $t['id'], array_map('intval', $old['tags']), true) ? 'checked' : '' ?>>
            <label class="btn btn-outline-primary btn-sm rounded-pill" for="tag-<?= (int) $t['id'] ?>">
                #<?= h($t['name']) ?>
            </label>
        <?php endforeach; ?>
    </div>
    <?= $err('tags') ?>
</div>

<?php if ($images): ?>
    <div class="mb-3">
        <label class="form-label">Ảnh hiện có <span class="text-muted fw-normal">(tick để xóa)</span></label>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($images as $im): ?>
                <label class="q-thumb-existing">
                    <img src="<?= h(storageUrl($im['image_path'])) ?>" alt="Ảnh đính kèm">
                    <span class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="remove_images[]" value="<?= (int) $im['id'] ?>">
                        <span class="form-check-label small">Xóa</span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="mb-4">
    <label class="form-label" for="q-images">Đính kèm ảnh
        <span class="text-muted fw-normal">(tối đa <?= (int) MAX_IMAGES_PER_Q ?> ảnh, mỗi ảnh ≤ <?= (int) (MAX_IMAGE_SIZE / 1048576) ?> MB)</span>
    </label>
    <input type="file" id="q-images" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
           class="form-control <?= isset($errors['images']) ? 'is-invalid' : '' ?>"
           data-max-files="<?= (int) MAX_IMAGES_PER_Q ?>" data-previews="#q-image-previews">
    <?= $err('images') ?>
    <div id="q-image-previews" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>
