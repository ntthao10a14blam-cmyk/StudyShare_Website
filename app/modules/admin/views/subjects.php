<?php
$subjects = $subjects ?? [];

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
                    <i class="bi bi-book-half"></i>
                    Quản lý môn học
                </h1>
                <p>Quản lý danh sách môn học trong hệ thống StudyShare</p>
            </div>

            <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addSubjectModal">
                <i class="bi bi-plus-circle"></i>
                Thêm môn học
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

        <!-- DANH SÁCH MÔN HỌC -->
        <div class="admin-panel">

            <div class="admin-panel-header">
                <div>
                    <h5>
                        <i class="bi bi-journal-bookmark-fill"></i>
                        Danh sách môn học
                    </h5>
                    <p>Tổng cộng <?= count($subjects) ?> môn học</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên môn học</th>
                            <th>Mô tả</th>
                            <th>Tài liệu</th>
                            <th>Câu hỏi</th>
                            <th>Ngày tạo</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-journal-x fs-1 text-muted d-block mb-2"></i>
                                Chưa có môn học nào.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($subjects as $subject): ?>
                            <?php
                                $subjectId = (int) ($subject['id'] ?? 0);
                                $documentCount = (int) ($subject['document_count'] ?? $subject['documents_count'] ?? 0);
                                $questionCount = (int) ($subject['question_count'] ?? $subject['questions_count'] ?? 0);
                                $canDelete = $documentCount === 0 && $questionCount === 0;
                            ?>

                            <tr>
                                <td>
                                    <span class="text-muted">#<?= $subjectId ?></span>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <i class="bi bi-book text-primary me-1"></i>
                                        <?= $e($subject['name'] ?? '') ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        <?= $e($subject['description'] ?: 'Chưa có mô tả') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        <?= number_format($documentCount) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        <?= number_format($questionCount) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($subject['created_at'])
                                        ? $e(date('d/m/Y', strtotime($subject['created_at'])))
                                        : '—' ?>
                                </td>

                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <!-- SỬA -->
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editSubjectModal"
                                            data-id="<?= $subjectId ?>"
                                            data-name="<?= $e($subject['name'] ?? '') ?>"
                                            data-description="<?= $e($subject['description'] ?? '') ?>"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Sửa
                                        </button>

                                        <!-- XÓA -->
                                        <?php if ($canDelete): ?>
                                            <form
                                                method="POST"
                                                action="<?= url('?module=admin&action=deleteSubject') ?>"
                                                class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa môn học này không?');"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                       value="<?= $e($csrfToken) ?>">

                                                <input type="hidden" name="id"
                                                       value="<?= $subjectId ?>">

                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                    Xóa
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                title="Môn học đang có dữ liệu liên kết"
                                            >
                                                <i class="bi bi-lock"></i>
                                                Không thể xóa
                                            </button>
                                        <?php endif; ?>

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

<!-- MODAL THÊM MÔN HỌC -->
<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=createSubject') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary"></i>
                        Thêm môn học mới
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tên môn học <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            maxlength="150"
                            placeholder="Ví dụ: Lập trình Web"
                            required
                        >
                        <small class="text-muted">
                            Tối đa 150 ký tự.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="4"
                            placeholder="Nhập mô tả môn học..."
                        ></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        Lưu môn học
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- MODAL CHỈNH SỬA MÔN HỌC -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST"
                  action="<?= url('?module=admin&action=editSubject') ?>">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square text-primary"></i>
                        Chỉnh sửa môn học
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="csrf_token"
                           value="<?= $e($csrfToken) ?>">

                    <input type="hidden" name="id" id="editSubjectId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Tên môn học <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            id="editSubjectName"
                            class="form-control"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Mô tả
                        </label>

                        <textarea
                            name="description"
                            id="editSubjectDescription"
                            class="form-control"
                            rows="4"
                        ></textarea>
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
    const editModal = document.getElementById('editSubjectModal');

    if (!editModal) return;

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) return;

        document.getElementById('editSubjectId').value =
            button.getAttribute('data-id') || '';

        document.getElementById('editSubjectName').value =
            button.getAttribute('data-name') || '';

        document.getElementById('editSubjectDescription').value =
            button.getAttribute('data-description') || '';
    });
});
</script>
