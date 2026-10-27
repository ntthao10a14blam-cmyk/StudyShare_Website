<?php
/**
 * CREATE.PHP — Form thêm tài liệu mới
 * Phụ trách: TV2
 */
$pageTitle = $pageTitle ?? 'Thêm tài liệu mới';
require_once BASE_PATH . '/app/views/layouts/header.php';

$errors   = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data']   ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>

<div class="container py-4">

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('?module=documents&action=index') ?>">Tài liệu của tôi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thêm tài liệu mới</li>
        </ol>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title mb-1">Thêm tài liệu mới</h1>
            <p class="text-muted small mb-0">Tải lên file PDF và ảnh minh hoạ để lưu trữ cá nhân hoặc chia sẻ với cộng đồng sinh viên.</p>
        </div>
        <a href="<?= url('?module=documents&action=index') ?>" class="btn btn-light-subtle btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <!-- KHUNG FORM UPLOAD -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <form method="POST" action="<?= url('?module=documents&action=store') ?>"
                  enctype="multipart/form-data" class="card community-card p-4 shadow-xs">
                <?php csrfField(); ?>

                <!-- 1. TIÊU ĐỀ VÀ MÔN HỌC -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label for="titleInput" class="form-label fw-semibold">
                            Tiêu đề tài liệu <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="titleInput" name="title"
                               class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                               placeholder="Ví dụ: Giáo trình Cấu trúc dữ liệu và giải thuật (Đầy đủ slide & bài tập)"
                               value="<?= h($formData['title'] ?? '') ?>" required maxlength="255">
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= h($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="subjectSelect" class="form-label fw-semibold">
                            Môn học <span class="text-danger">*</span>
                        </label>
                        <select id="subjectSelect" name="subject_id"
                                class="form-select <?= isset($errors['subject_id']) ? 'is-invalid' : '' ?>" required>
                            <option value="">-- Chọn môn học --</option>
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($formData['subject_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                    <?= h($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['subject_id'])): ?>
                            <div class="invalid-feedback"><?= h($errors['subject_id']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. TẢI FILE PDF (BẮT BUỘC) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        File tài liệu (PDF) <span class="text-danger">*</span>
                    </label>
                    <div class="p-4 border border-2 border-dashed rounded text-center bg-light" id="pdfDropzone" style="border-color: #cbd5e1 !important;">
                        <i class="bi bi-file-earmark-pdf text-danger fs-1 mb-2 d-block"></i>
                        <div class="fw-semibold text-dark mb-1" id="pdfFileName">Kéo thả file PDF vào đây hoặc nhấn để chọn file</div>
                        <div class="text-muted small mb-3">Định dạng hỗ trợ: PDF (Dung lượng tối đa: 20 MB)</div>
                        <label for="documentFileInput" class="btn btn-outline-primary btn-sm px-3">
                            <i class="bi bi-folder2-open me-1"></i> Chọn file PDF từ máy tính
                        </label>
                        <input type="file" id="documentFileInput" name="document_file"
                               accept="application/pdf,.pdf" class="d-none" required>
                    </div>
                    <?php if (isset($errors['document_file'])): ?>
                        <div class="text-danger small mt-1">
                            <i class="bi bi-exclamation-circle me-1"></i><?= h($errors['document_file']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. TẢI ẢNH MINH HOẠ / XEM TRƯỚC (NHIỀU ẢNH) -->
                <div class="mb-4">
                    <label for="imagesInput" class="form-label fw-semibold">
                        Ảnh chụp trang / Ảnh minh hoạ <small class="text-muted fw-normal">(Không bắt buộc, tối đa 10 ảnh)</small>
                    </label>
                    <input type="file" id="imagesInput" name="images[]" multiple
                           accept="image/jpeg,image/png,image/webp,image/gif" class="form-control">
                    <div class="form-text small">
                        Hỗ trợ: JPG, PNG, WEBP, GIF (Tối đa 5 MB/ảnh). Những ảnh này giúp người đọc xem trước chất lượng tài liệu.
                    </div>
                    <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>

                <!-- 4. MÔ TẢ TÀI LIỆU -->
                <div class="mb-4">
                    <label for="descriptionInput" class="form-label fw-semibold">
                        Mô tả nội dung & Ghi chú
                    </label>
                    <textarea id="descriptionInput" name="description" rows="5"
                              class="form-control"
                              placeholder="Mô tả tóm tắt tài liệu: gồm các chương nào, đề thi kỳ nào, tác giả biên soạn, lưu ý khi ôn tập..."><?= h($formData['description'] ?? '') ?></textarea>
                    <div class="form-text small">Mô tả rõ ràng sẽ giúp tài liệu được duyệt nhanh hơn và thu hút nhiều lượt quan tâm.</div>
                </div>

                <!-- 5. TỪ KHOÁ (TAGS) -->
                <div class="mb-4">
                    <label for="tagsInput" class="form-label fw-semibold">
                        Từ khoá tìm kiếm <small class="text-muted fw-normal">(phân cách bởi dấu phẩy)</small>
                    </label>
                    <input type="text" id="tagsInput" name="tags"
                           class="form-control"
                           placeholder="bài tập, ôn thi cuối kỳ, tóm tắt công thức, đề cương..."
                           value="<?= h($formData['tags'] ?? '') ?>">
                    
                    <!-- Gợi ý từ khoá phổ biến -->
                    <?php if (!empty($allTags)): ?>
                        <div class="mt-2 d-flex flex-wrap align-items-center gap-1">
                            <small class="text-muted me-1">Gợi ý:</small>
                            <?php foreach ($allTags as $tag): ?>
                                <button type="button" class="btn badge-tag border-0 py-0 px-2 tag-suggestion-btn"
                                        data-tag="<?= h($tag['name']) ?>" style="cursor: pointer;">
                                    + <?= h($tag['name']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 6. CHẾ ĐỘ HIỂN THỊ & GỬI DUYỆT -->
                <div class="p-3 mb-4 rounded border bg-light-subtle">
                    <div class="fw-semibold text-dark mb-2">Quyền riêng tư & Chia sẻ</div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="visibility" id="visPrivate" value="private"
                               <?= ($formData['visibility'] ?? 'private') === 'private' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="visPrivate">
                            <span class="fw-semibold text-dark"><i class="bi bi-lock me-1"></i> Riêng tư (Chỉ mình tôi)</span>
                            <div class="text-muted small">Lưu trữ trên đám mây cá nhân, không hiển thị trên bảng tin công cộng.</div>
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="visibility" id="visPublic" value="public"
                               <?= ($formData['visibility'] ?? '') === 'public' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="visPublic">
                            <span class="fw-semibold text-primary"><i class="bi bi-globe me-1"></i> Chia sẻ cộng đồng</span>
                            <div class="text-muted small">Tài liệu sẽ được hiển thị công khai tới toàn thể sinh viên sau khi Ban quản trị kiểm duyệt nội dung.</div>
                        </label>
                    </div>

                    <!-- Lựa chọn gửi duyệt ngay (khi chọn công khai) -->
                    <div class="ms-4 mt-2" id="submitForReviewBox" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="submit_for_review" id="submitReviewCheck" value="1" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="submitReviewCheck">
                                Gửi yêu cầu kiểm duyệt ngay sau khi tải lên
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 7. NÚT SUBMIT -->
                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="<?= url('?module=documents&action=index') ?>" class="btn btn-light-subtle btn-sm px-4">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Tải lên tài liệu</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Hiển thị tên file PDF khi chọn
    const pdfInput = document.getElementById('documentFileInput');
    const pdfFileName = document.getElementById('pdfFileName');
    if (pdfInput && pdfFileName) {
        pdfInput.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                const f = this.files[0];
                const sizeMb = (f.size / (1024 * 1024)).toFixed(2);
                pdfFileName.innerHTML = `<span class="text-success fw-bold"><i class="bi bi-file-earmark-check me-1"></i> Đã chọn: ${f.name} (${sizeMb} MB)</span>`;
            }
        });
    }

    // 2. Ẩn/hiện hộp gửi duyệt tuỳ theo Visibility
    const visPublic = document.getElementById('visPublic');
    const visPrivate = document.getElementById('visPrivate');
    const reviewBox = document.getElementById('submitForReviewBox');

    function toggleReviewBox() {
        if (visPublic && visPublic.checked) {
            reviewBox.style.display = 'block';
        } else {
            reviewBox.style.display = 'none';
        }
    }
    if (visPublic && visPrivate && reviewBox) {
        visPublic.addEventListener('change', toggleReviewBox);
        visPrivate.addEventListener('change', toggleReviewBox);
        toggleReviewBox();
    }

    // 3. Click gợi ý tag
    const tagsInput = document.getElementById('tagsInput');
    document.querySelectorAll('.tag-suggestion-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tag = this.getAttribute('data-tag');
            let current = tagsInput.value.trim();
            if (current === '') {
                tagsInput.value = tag;
            } else {
                let tags = current.split(',').map(t => t.trim());
                if (!tags.includes(tag)) {
                    tags.push(tag);
                    tagsInput.value = tags.join(', ');
                }
            }
        });
    });

    // 4. Preview ảnh tải lên
    const imagesInput = document.getElementById('imagesInput');
    const previewContainer = document.getElementById('imagePreviewContainer');
    if (imagesInput && previewContainer) {
        imagesInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach((file, index) => {
                    if (index >= 10) return;
                    if (!file.type.startsWith('image/')) return;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'rounded border';
                        img.style.width = '70px';
                        img.style.height = '70px';
                        img.style.objectFit = 'cover';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }
});
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
