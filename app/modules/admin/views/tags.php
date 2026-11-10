<?php
$tags = $tags ?? [];

$e = static function ($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};

$csrfToken = $csrfToken ?? ($_SESSION['csrf_token'] ?? '');
?>

<div class="admin-page">
    <div class="admin-content">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <h1>
                    <i class="bi bi-tags-fill"></i>
                    Quản lý Tags
                </h1>
                <p>Quản lý các thẻ dùng để phân loại tài liệu và câu hỏi</p>
            </div>

            <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addTagModal">
                <i class="bi bi-plus-circle"></i>
                Thêm Tag
            </button>
        </div>

        <!-- THÔNG BÁO -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill"></i>
                <?= $e($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $e($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- DANH SÁCH TAGS -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-collection-fill"></i>
                        Danh sách Tags
                    </h5>
                    <p>Tổng cộng <?= count($tags) ?> Tags</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Tag</th>
                            <th>Tài liệu liên kết</th>
                            <th>Câu hỏi liên kết</th>
                            <th>Ngày tạo</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($tags)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-tags fs-1 text-muted d-block mb-2"></i>
                                Chưa có Tag nào.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($tags as $tag): ?>
                            <?php
                                $tagId = (int) ($tag['id'] ?? 0);
                                $documentCount = (int) ($tag['document_count'] ?? $tag['documents_count'] ?? 0);
                                $questionCount = (int) ($tag['question_count'] ?? $tag['questions_count'] ?? 0);
                            ?>

                            <tr>
                                <td>
                                    <span class="text-muted">#<?= $tagId ?></span>
                                </td>

                                <td>
                                    <span class="badge bg-primary-subtle text-primary fs-6">
                                        <i class="bi bi-tag-fill"></i>
                                        <?= $e($tag['name'] ?? '') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        <?= number_format($documentCount) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        <?= number_format($questionCount) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($tag['created_at'])
                                        ? $e(date('d/m/Y', strtotime($tag['created_at'])))
                                        : '—' ?>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <!-- SỬA -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTagModal"
                                            data-id="<?= $tagId ?>"
                                            data-name="<?= $e($tag['name'] ?? '') ?>"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Sửa
                                        </button>

                                        <!-- XÓA -->
                                        <form
                                            method="POST"
                                            action="<?= url('?module=admin&action=deleteTag') ?>"
                                            class="d-inline"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa Tag này không?');"
                                        >
                                            <input type="hidden" name="csrf_token"
                                                   value="<?= $e($csrfToken) ?>">

                                            <input type="hidden" name="id"
                                                   value="<?= $tagId ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                                Xóa
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- MODAL THÊM TAG -->
<div class="modal fade" id="addTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=createTag') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary"></i>
                        Thêm Tag mới
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tên Tag <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            maxlength="50"
                            placeholder="Ví dụ: PHP, HTML, Database..."
                            required
                        >

                        <small class="text-muted">
                            Tên Tag không được vượt quá 50 ký tự.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Lưu Tag
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- MODAL SỬA TAG -->
<div class="modal fade" id="editTagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=editTag') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square text-primary"></i>
                        Chỉnh sửa Tag
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="id" id="editTagId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tên Tag <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="editTagName"
                            class="form-control"
                            maxlength="50"
                            required
                        >

                        <small class="text-muted">
                            Tối đa 50 ký tự.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Cập nhật
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editTagModal');

    if (!editModal) return;

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) return;

        document.getElementById('editTagId').value =
            button.getAttribute('data-id') || '';

        document.getElementById('editTagName').value =
            button.getAttribute('data-name') || '';
    });
});
</script>
