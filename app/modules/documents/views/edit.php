<?php
/**
 * EDIT.PHP — Chỉnh sửa tài liệu cá nhân
 * Phụ trách: TV2
 */
$pageTitle = $pageTitle ?? ('Sửa: ' . $doc['title']);
require_once BASE_PATH . '/app/views/layouts/header.php';

$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>

<div class="container py-4">

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url('/') ?>">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('?module=documents&action=index') ?>">Tài liệu của tôi</a></li>
            <li class="breadcrumb-item"><a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>"><?= h($doc['title']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
        </ol>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title mb-1">Chỉnh sửa tài liệu</h1>
            <p class="text-muted small mb-0">Cập nhật thông tin, thay thế tệp PDF hoặc quản lý ảnh minh hoạ.</p>
        </div>
        <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>" class="btn btn-light-subtle btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Hủy & Quay lại
        </a>
    </div>

    <!-- CẢNH BÁO QUY ĐỊNH DUYỆT LẠI (NẾU TÀI LIỆU ĐANG APPROVED) -->
    <?php if ($doc['moderation_status'] === 'approved'): ?>
        <div class="alert alert-info d-flex align-items-center gap-2 small mb-4 py-2" role="alert">
            <i class="bi bi-info-circle-fill fs-5"></i>
            <div>
                <strong>Lưu ý quan trọng:</strong> Tài liệu này đang ở trạng thái <strong>Đã duyệt công khai</strong>.
                Nếu bạn thay đổi <strong>File PDF</strong> hoặc <strong>Mô tả tài liệu</strong>, hệ thống sẽ tự động đưa tài liệu về trạng thái <strong>Chờ duyệt lại</strong> để đảm bảo an toàn nội dung.
            </div>
        </div>
    <?php endif; ?>

    <!-- KHUNG FORM CHỈNH SỬA -->
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <form method="POST" action="<?= url('?module=documents&action=update') ?>"
                  enctype="multipart/form-data" class="card community-card p-4 shadow-xs">
                <?php csrfField(); ?>
                <input type="hidden" name="id" value="<?= $doc['id'] ?>">

                <!-- 1. TIÊU ĐỀ VÀ MÔN HỌC -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label for="titleInput" class="form-label fw-semibold">
                            Tiêu đề tài liệu <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="titleInput" name="title"
                               class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                               value="<?= h($doc['title']) ?>" required maxlength="255">
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
                                <option value="<?= $s['id'] ?>" <?= $doc['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= h($s['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['subject_id'])): ?>
                            <div class="invalid-feedback"><?= h($errors['subject_id']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. THAY THẾ FILE PDF (NẾU CẦN) -->
                <div class="mb-4 p-3 rounded border bg-light-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0">File tài liệu hiện tại</label>
                        <span class="badge bg-secondary font-monospace"><?= formatFileSize($doc['file_size']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3 text-truncate">
                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-4"></i>
                        <span class="fw-semibold text-dark text-truncate"><?= h($doc['original_file_name']) ?></span>
                        <a href="<?= storageUrl($doc['file_path']) ?>" target="_blank" class="ms-auto btn btn-outline-secondary btn-sm py-0 px-2 small">
                            Xem file hiện tại
                        </a>
                    </div>

                    <label for="documentFileInput" class="form-label small fw-semibold text-muted">
                        Tải lên file PDF mới để thay thế (để trống nếu không muốn đổi file):
                    </label>
                    <input type="file" id="documentFileInput" name="document_file"
                           accept="application/pdf,.pdf" class="form-control form-control-sm">
                </div>

                <!-- 3. QUẢN LÝ ẢNH MINH HOẠ ĐÃ CÓ -->
                <?php if (!empty($images)): ?>
                    <div class="mb-4 p-3 rounded border bg-light-subtle">
                        <label class="form-label fw-semibold mb-2">
                            Ảnh minh hoạ hiện có (Tích chọn để xoá ảnh)
                        </label>
                        <div class="row g-2">
                            <?php foreach ($images as $img): ?>
                                <div class="col-4 col-md-3 col-lg-2">
                                    <div class="border rounded p-1 bg-white text-center position-relative shadow-xs">
                                        <img src="<?= storageUrl($img['image_path']) ?>" alt="Minh hoạ"
                                             class="w-100 rounded object-fit-cover" style="height: 80px;">
                                        <div class="form-check mt-1 d-flex justify-content-center align-items-center gap-1">
                                            <input class="form-check-input" type="checkbox" name="delete_images[]"
                                                   value="<?= $img['id'] ?>" id="delImg<?= $img['id'] ?>">
                                            <label class="form-check-label text-danger small" for="delImg<?= $img['id'] ?>" style="font-size: 11px;">
                                                Xóa ảnh
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 4. THÊM ẢNH MINH HOẠ MỚI -->
                <div class="mb-4">
                    <label for="imagesInput" class="form-label fw-semibold">
                        Thêm ảnh minh hoạ mới <small class="text-muted fw-normal">(Tối đa 10 ảnh toàn bộ)</small>
                    </label>
                    <input type="file" id="imagesInput" name="images[]" multiple
                           accept="image/jpeg,image/png,image/webp,image/gif" class="form-control form-control-sm">
                </div>

                <!-- 5. MÔ TẢ NỘI DUNG -->
                <div class="mb-4">
                    <label for="descriptionInput" class="form-label fw-semibold">
                        Mô tả tài liệu
                    </label>
                    <textarea id="descriptionInput" name="description" rows="5"
                              class="form-control"
                              placeholder="Mô tả nội dung chi tiết..."><?= h($doc['description'] ?? '') ?></textarea>
                </div>

                <!-- 6. TỪ KHOÁ (TAGS) -->
                <div class="mb-4">
                    <label for="tagsInput" class="form-label fw-semibold">
                        Từ khoá (phân cách bởi dấu phẩy)
                    </label>
                    <input type="text" id="tagsInput" name="tags"
                           class="form-control"
                           value="<?= h($tagString ?? '') ?>"
                           placeholder="bài tập, ôn thi, lý thuyết...">
                </div>

                <!-- 7. CHẾ ĐỘ HIỂN THỊ -->
                <div class="p-3 mb-4 rounded border bg-light-subtle">
                    <div class="fw-semibold text-dark mb-2">Chế độ chia sẻ</div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="visibility" id="visPrivate" value="private"
                               <?= $doc['visibility'] === 'private' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="visPrivate">
                            <span class="fw-semibold text-dark"><i class="bi bi-lock me-1"></i> Riêng tư (Chỉ mình tôi)</span>
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="visibility" id="visPublic" value="public"
                               <?= $doc['visibility'] === 'public' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="visPublic">
                            <span class="fw-semibold text-primary"><i class="bi bi-globe me-1"></i> Chia sẻ cộng đồng</span>
                        </label>
                    </div>
                </div>

                <!-- 8. NÚT SUBMIT -->
                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="<?= url('?module=documents&action=show&id=' . $doc['id']) ?>" class="btn btn-light-subtle btn-sm px-4">
                        Hủy
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-lg"></i>
                        <span>Lưu thay đổi</span>
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
