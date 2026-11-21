<?php

$document    = $document ?? [];
$comments    = $comments ?? [];
$tags        = $tags ?? [];
$images      = $images ?? [];
$relatedDocs = $relatedDocs ?? [];
$reportReasons = $reportReasons ?? [];

$extraCss = ['community.css'];
require_once BASE_PATH . '/app/views/layouts/header.php';

$currentUser = Auth::currentUser();
$isLoggedIn  = Auth::check();
$isAdmin     = Auth::isAdmin();
$isOwner     = $isLoggedIn && ((int) Auth::id() === (int) $document['user_id']);

$docFilePath = $document['file_path'];
if (!str_starts_with($docFilePath, 'documents/')) {
    $docFilePath = 'documents/' . $docFilePath;
}
$serveUrl    = storageUrl($docFilePath);
$downloadUrl = url('?module=community&action=download&id=' . (int) $document['id']);
?>

<div class="container py-4">

    <nav aria-label="breadcrumb" class="tailieu-breadcrumb">
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item">
                <a href="<?= url('/') ?>">
                    <i class="bi bi-house-door me-1"></i>Trang chủ
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= url('?module=community&action=index') ?>">Tài liệu học tập</a>
            </li>
            <?php if (!empty($document['subject_name'])): ?>
                <li class="breadcrumb-item">
                    <a href="<?= url('?module=community&action=index&subject_id=' . $document['subject_id']) ?>">
                        <?= h($document['subject_name']) ?>
                    </a>
                </li>
            <?php endif; ?>
            <li class="breadcrumb-item active text-truncate" style="max-width: 340px;" aria-current="page">
                <?= h($document['title']) ?>
            </li>
        </ol>
    </nav>

    <div class="row g-4">

        <div class="col-lg-8 col-xl-9">

            <div class="tailieu-detail-box shadow-sm">

                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <?php if (!empty($document['subject_name'])): ?>
                        <a href="<?= url('?module=community&action=index&subject_id=' . $document['subject_id']) ?>"
                            class="badge-tag text-decoration-none">
                            <i class="bi bi-bookmark-fill me-1"></i><?= h($document['subject_name']) ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($document['moderation_status'] === 'approved'): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 small">
                            <i class="bi bi-shield-check me-1"></i>Đã kiểm duyệt an toàn
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 small">
                            Trạng thái: <?= h($document['moderation_status']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="tailieu-detail-title">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i><?= h($document['title']) ?>
                </h1>

                <div class="tailieu-metric-bar">
                    <span>
                        <i class="bi bi-person-circle text-primary"></i>
                        Người chia sẻ: <strong><?= h($document['author_name'] ?? 'Thành viên') ?></strong>
                    </span>
                    <span>
                        <i class="bi bi-calendar3"></i>
                        Ngày đăng: <?= formatDateTime($document['published_at'] ?? $document['created_at'], 'd/m/Y') ?>
                    </span>
                    <span>
                        <i class="bi bi-hdd"></i>
                        Dung lượng: <?= formatFileSize((int) $document['file_size']) ?>
                    </span>
                    <span>
                        <i class="bi bi-cloud-arrow-down text-success"></i>
                        Lượt tải: <strong><?= number_format((int) $document['download_count']) ?></strong>
                    </span>
                    <span>
                        <i class="bi bi-chat-text text-info"></i>
                        Bình luận: <strong><?= count($comments) ?></strong>
                    </span>
                </div>

                <div class="tailieu-action-bar">
                    <?php if ($isLoggedIn): ?>

                        <a href="<?= $downloadUrl ?>"
                            class="btn btn-primary fw-semibold px-4 py-2">
                            <i class="bi bi-download me-2"></i>Tải xuống tài liệu
                            <span class="opacity-75 ms-1 fw-normal">(<?= formatFileSize((int) $document['file_size']) ?>)</span>
                        </a>

                        <a href="<?= $serveUrl ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Đọc toàn màn hình
                        </a>

                        <form action="<?= url('?module=collections&action=toggleFavorite') ?>" method="POST" class="d-inline">
                            <input type="hidden" name="tv4_token" value="<?= h($tv4Token ?? '') ?>">
                            <input type="hidden" name="document_id" value="<?= (int) $document['id'] ?>">
                            <input type="hidden" name="return_to" value="community">
                            <button type="submit" class="btn <?= !empty($isFavorited) ? 'btn-danger' : 'btn-outline-danger' ?>">
                                <i class="bi <?= !empty($isFavorited) ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i>
                                <?= !empty($isFavorited) ? 'Đã thích' : 'Yêu thích' ?>
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#saveCollectionModal">
                            <i class="bi bi-bookmark-plus me-1"></i> Lưu vào bộ sưu tập
                        </button>

                        <button type="button" class="btn btn-light-subtle text-danger ms-auto btn-report-target"
                            data-bs-toggle="modal" data-bs-target="#reportModal"
                            data-report-type="document"
                            data-report-id="<?= (int) $document['id'] ?>"
                            data-report-label="tài liệu này">
                            <i class="bi bi-flag me-1"></i> Báo cáo vi phạm
                        </button>
                    <?php else: ?>

                        <a href="<?= url('?module=auth&action=login') ?>"
                            class="btn btn-primary fw-semibold px-4 py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để tải tài liệu
                            <span class="opacity-75 ms-1 fw-normal">(<?= formatFileSize((int) $document['file_size']) ?>)</span>
                        </a>
                        <a href="<?= url('?module=auth&action=register') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-1"></i> Đăng ký tài khoản
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tailieu-reader-wrapper">
                <div class="tailieu-reader-toolbar">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-pdf text-danger fs-5"></i>
                        <span class="text-truncate fw-medium tailieu-reader-filename">
                            <?= h($document['original_file_name'] ?? $document['title']) ?>
                        </span>
                    </div>
                    <div>
                        <?php if ($isLoggedIn): ?>
                            <a href="<?= $serveUrl ?>" target="_blank"
                                rel="noopener" class="btn btn-sm btn-outline-primary py-0 px-2">
                                <i class="bi bi-arrows-fullscreen me-1"></i> Phóng to
                            </a>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                <i class="bi bi-eye me-1"></i> Xem trước 3 trang đầu
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($isLoggedIn): ?>
                    <iframe src="<?= $serveUrl ?>"
                        class="tailieu-reader-frame" title="Xem trước PDF: <?= h($document['title']) ?>">
                    </iframe>
                <?php else: ?>
                    <div class="p-3 bg-light" style="max-height: 750px; overflow-y: auto;" oncontextmenu="return false;">
                        <?php for ($p = 1; $p <= 3; $p++): ?>
                            <?php $previewImgUrl = url('serve.php?file=' . urlencode($document['file_path']) . '&preview_page=' . $p); ?>
                            <div class="mb-3 text-center bg-white rounded shadow-sm p-2 border">
                                <div class="text-muted small mb-1 text-start px-2">Trang <?= $p ?> / 3 (Xem trước)</div>
                                <img src="<?= $previewImgUrl ?>"
                                    alt="Xem trước trang <?= $p ?> tài liệu <?= h($document['title']) ?>"
                                    class="img-fluid rounded border-top"
                                    style="user-select: none; pointer-events: none;"
                                    onerror="this.closest('.mb-3').style.display='none';">
                            </div>
                        <?php endfor; ?>

                        <div class="tailieu-reader-login text-center bg-white rounded border p-4 mt-2">
                            <i class="bi bi-file-earmark-lock display-5 text-warning mb-2 d-block"></i>
                            <h5 class="fw-bold text-dark mb-2">Bạn đang xem trước 3 trang đầu tiên</h5>
                            <p class="small text-muted mb-3 tailieu-reader-login-copy">
                                Vui lòng đăng nhập tài khoản StudyShare để xem toàn bộ nội dung tài liệu trực tuyến và tải file PDF về máy.
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="<?= url('?module=auth&action=login') ?>" class="btn btn-primary btn-sm px-4 fw-medium">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập để xem đầy đủ
                                </a>
                                <a href="<?= url('?module=auth&action=register') ?>" class="btn btn-outline-secondary btn-sm px-3">
                                    Đăng ký
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($images)): ?>
                <div class="tailieu-content-box shadow-sm p-3">
                    <h5 class="tailieu-content-title fs-6 mb-3">
                        <i class="bi bi-images text-primary me-2"></i>Hình ảnh trích lục / trang xem trước
                    </h5>
                    <div class="row g-2">
                        <?php foreach ($images as $img): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="tailieu-preview-card">
                                    <a href="<?= h(storageUrl($img['image_path'])) ?>" target="_blank" rel="noopener"
                                        aria-label="Mở ảnh minh họa kích thước đầy đủ">
                                        <img src="<?= h(storageUrl($img['image_path'])) ?>"
                                            alt="Ảnh minh họa tài liệu: <?= h($document['title']) ?>"
                                            class="tailieu-preview-image" loading="lazy">
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="tailieu-content-box shadow-sm">
                <h5 class="tailieu-content-title">
                    <i class="bi bi-card-text text-primary me-2"></i>Tóm tắt & Giới thiệu nội dung tài liệu
                </h5>
                <div class="tailieu-text-body">
                    <?php if (!empty($document['description'])): ?>
                        <?= nl2br(h($document['description'])) ?>
                    <?php else: ?>
                        <p class="text-muted fst-italic mb-0">Tài liệu chưa có phần tóm tắt chi tiết từ tác giả tải lên.</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tags)): ?>
                    <div class="mt-4 pt-3 border-top">
                        <span class="small fw-semibold text-muted me-2">
                            <i class="bi bi-tags me-1"></i>Từ khóa liên quan:
                        </span>
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?= url('?module=community&action=index&tag_id=' . $tag['id']) ?>"
                                class="badge-tag text-decoration-none me-1 mb-1">
                                #<?= h($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tailieu-content-box shadow-sm" id="comments">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="tailieu-content-title mb-0 border-0 p-0">
                        <i class="bi bi-chat-square-dots-fill text-primary me-2"></i>Bình luận & Thảo luận (<?= count($comments) ?>)
                    </h5>
                    <span class="small text-muted">Vui lòng thảo luận lịch sự và văn minh</span>
                </div>

                <?php if ($isLoggedIn): ?>
                    <form action="<?= url('?module=community&action=comment') ?>" method="POST" class="mb-4">
                        <?php csrfField(); ?>
                        <input type="hidden" name="document_id" value="<?= (int) $document['id'] ?>">

                        <div class="d-flex gap-3 align-items-start">
                            <img src="<?= $currentUser['avatar'] ?? url('assets/images/default-avatar.png') ?>"
                                alt="Avatar" class="avatar avatar-sm">
                            <div class="flex-grow-1">
                                <textarea name="content" class="form-control" rows="3"
                                    placeholder="Viết nhận xét, góp ý hoặc cảm ơn người chia sẻ tài liệu này..."
                                    required></textarea>
                                <div class="mt-2 text-end">
                                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-medium">
                                        <i class="bi bi-send me-1"></i> Gửi bình luận
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-light border d-flex justify-content-between align-items-center p-3 mb-4 rounded-3">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                            <span>Bạn cần đăng nhập tài khoản để tham gia bình luận thảo luận.</span>
                        </div>
                        <a href="<?= url('?module=auth&action=login') ?>" class="btn btn-primary btn-sm px-3 fw-medium">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($comments)): ?>
                    <div class="comment-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <img src="<?= !empty($comment['avatar_path']) ? 'serve.php?file=' . urlencode($comment['avatar_path']) : url('assets/images/default-avatar.png') ?>"
                                    alt="Avatar" class="comment-avatar">
                                <div class="comment-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="comment-author"><?= h($comment['full_name']) ?></span>
                                            <?php if ($comment['role'] === 'admin'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-shield-check"></i> Quản trị viên
                                                </span>
                                            <?php endif; ?>
                                            <span class="comment-time ms-2">
                                                <i class="bi bi-clock"></i> <?= timeAgo($comment['created_at']) ?>
                                            </span>
                                        </div>

                                        <?php if ($isLoggedIn): ?>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 py-1" style="font-size: 0.85rem;">
                                                    <?php if (Auth::owns((int) $comment['user_id']) || $isAdmin): ?>
                                                        <li>
                                                            <button type="button" class="dropdown-item py-1 text-primary btn-edit-comment"
                                                                data-id="<?= (int) $comment['id'] ?>"
                                                                data-content="<?= h($comment['content']) ?>">
                                                                <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <form action="<?= url('?module=community&action=deleteComment') ?>" method="POST"
                                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                                                                <?php csrfField(); ?>
                                                                <input type="hidden" name="document_id" value="<?= (int) $document['id'] ?>">
                                                                <input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>">
                                                                <button type="submit" class="dropdown-item py-1 text-danger">
                                                                    <i class="bi bi-trash3 me-2"></i>Xóa
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>

                                                    <li>
                                                        <button type="button" class="dropdown-item py-1 text-muted btn-report-target"
                                                            data-bs-toggle="modal" data-bs-target="#reportModal"
                                                            data-report-type="comment"
                                                            data-report-id="<?= (int) $comment['id'] ?>"
                                                            data-report-label="bình luận này">
                                                            <i class="bi bi-flag me-2"></i>Báo cáo vi phạm
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="comment-content">
                                        <?= nl2br(h($comment['content'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-chat-square-dots display-6 mb-2 d-block opacity-50"></i>
                        Chưa có bình luận nào cho tài liệu này. Hãy là người đầu tiên để lại ý kiến đóng góp!
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <aside class="col-lg-4 col-xl-3">

            <div class="community-sidebar-card shadow-sm p-3 text-center mb-3">
                <img src="<?= !empty($document['author_avatar']) ? 'serve.php?file=' . urlencode($document['author_avatar']) : url('assets/images/default-avatar.png') ?>"
                    alt="Avatar" class="rounded-circle border border-2 border-primary mb-2 object-fit-cover"
                    style="width: 72px; height: 72px;">
                <h6 class="fw-bold text-dark mb-0"><?= h($document['author_name'] ?? 'Thành viên') ?></h6>
                <div class="small text-muted mb-2">
                    <?= ($document['author_role'] ?? 'user') === 'admin' ? '<i class="bi bi-shield-check text-success"></i> Quản trị viên' : '<i class="bi bi-mortarboard text-primary"></i> Sinh viên chia sẻ' ?>
                </div>
                <div class="small text-muted border-top pt-2 mt-2">
                    <span>Thành viên từ: <?= formatDateTime($document['author_joined_at'] ?? 'now', 'm/Y') ?></span>
                </div>
            </div>

            <div class="community-sidebar-card shadow-sm mb-3">
                <div class="community-sidebar-header">
                    <i class="bi bi-info-circle-fill text-primary"></i>
                    <span>Thông tin chi tiết tệp tin</span>
                </div>
                <div class="p-3">
                    <table class="w-100 tailieu-spec-table">
                        <tbody>
                            <tr>
                                <td class="spec-label">Định dạng file:</td>
                                <td class="spec-value text-danger font-monospace">PDF</td>
                            </tr>
                            <tr>
                                <td class="spec-label">Dung lượng:</td>
                                <td class="spec-value"><?= formatFileSize((int) $document['file_size']) ?></td>
                            </tr>
                            <tr>
                                <td class="spec-label">Lượt tải về:</td>
                                <td class="spec-value text-success"><?= number_format((int) $document['download_count']) ?></td>
                            </tr>
                            <tr>
                                <td class="spec-label">Ngày đăng tải:</td>
                                <td class="spec-value"><?= formatDateTime($document['created_at'], 'd/m/Y') ?></td>
                            </tr>
                            <tr>
                                <td class="spec-label">Trạng thái duyệt:</td>
                                <td class="spec-value text-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>Đã phê duyệt
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($relatedDocs)): ?>
                <div class="community-sidebar-card shadow-sm mb-3">
                    <div class="community-sidebar-header">
                        <i class="bi bi-collection-fill text-primary"></i>
                        <span>Tài liệu cùng chủ đề</span>
                    </div>
                    <div class="p-3">
                        <?php foreach ($relatedDocs as $rel): ?>
                            <a href="<?= url('?module=community&action=show&id=' . $rel['id']) ?>"
                                class="tailieu-related-item">
                                <div class="tailieu-related-thumb">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                                <div class="tailieu-related-info">
                                    <div class="tailieu-related-title">
                                        <?= h($rel['title']) ?>
                                    </div>
                                    <div class="tailieu-related-meta">
                                        <span><i class="bi bi-download me-1"></i><?= number_format((int) $rel['download_count']) ?></span>
                                        <span><?= formatFileSize((int) $rel['file_size']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="community-sidebar-card shadow-sm p-3 bg-light">
                <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;">
                    <i class="bi bi-shield-lock-fill text-primary me-1"></i> Trách nhiệm học thuật
                </h6>
                <p class="text-muted mb-0" style="font-size: 0.8125rem; line-height: 1.5;">
                    Tài liệu được chia sẻ phi thương mại để phục vụ mục đích học tập cá nhân. Nếu phát hiện vi phạm bản quyền hoặc nội dung sai lệch, bạn hãy bấm nút Báo cáo để ban quản trị xử lý.
                </p>
            </div>

        </aside>

    </div>

</div>

<?php if ($isLoggedIn): ?>
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="<?= url('?module=community&action=report') ?>" method="POST" class="modal-content">
                <?php csrfField(); ?>
                <input type="hidden" name="target_type" id="report-type" value="">
                <input type="hidden" name="target_id" id="report-id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold text-danger" id="reportModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Báo cáo <span id="report-label">nội dung này</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="report-reason">Lý do báo cáo: <span class="text-danger">*</span></label>
                        <select name="reason" id="report-reason" class="form-select" required>
                            <option value="">-- Chọn lý do --</option>
                            <?php foreach ($reportReasons as $reason): ?>
                                <option value="<?= h($reason) ?>"><?= h($reason) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="report-details">Chi tiết phản ánh:</label>
                        <textarea name="details" id="report-details" class="form-control" rows="3" maxlength="1000"
                            placeholder="Mô tả cụ thể sự việc để ban quản trị hỗ trợ giải quyết..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-subtle btn-sm" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">Gửi báo cáo</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="<?= url('?module=community&action=editComment') ?>" method="POST" class="modal-content">
                <?php csrfField(); ?>
                <input type="hidden" name="document_id" value="<?= (int) $document['id'] ?>">
                <input type="hidden" name="comment_id" id="edit_comment_id" value="">

                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold text-dark">
                        <i class="bi bi-pencil-square text-primary me-1"></i> Chỉnh sửa bình luận
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nội dung bình luận:</label>
                        <textarea name="content" id="edit_comment_content" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-subtle btn-sm" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtns = document.querySelectorAll('.btn-edit-comment');
            const editModal = new bootstrap.Modal(document.getElementById('editCommentModal'));
            const editIdInput = document.getElementById('edit_comment_id');
            const editContentInput = document.getElementById('edit_comment_content');

            editBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    editIdInput.value = this.dataset.id;
                    editContentInput.value = this.dataset.content;
                    editModal.show();
                });
            });

            const reportModal = document.getElementById('reportModal');
            if (reportModal) {
                reportModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    document.getElementById('report-type').value = button.dataset.reportType;
                    document.getElementById('report-id').value = button.dataset.reportId;
                    document.getElementById('report-label').textContent = button.dataset.reportLabel;
                    document.getElementById('report-reason').value = '';
                    document.getElementById('report-details').value = '';
                });
            }
        });
    </script>
<?php endif; ?>

<?php if ($isLoggedIn): ?>
    <div class="modal fade" id="saveCollectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6 fw-bold text-dark">
                        <i class="bi bi-bookmark-plus-fill text-primary me-1"></i> Lưu tài liệu vào Bộ sưu tập
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <?php if (empty($userCollections)): ?>
                    <div class="modal-body text-center py-4">
                        <p class="text-muted small mb-3">Bạn chưa có bộ sưu tập nào. Hãy tạo bộ sưu tập mới để lưu tài liệu này!</p>
                        <a href="<?= url('?module=collections&action=create') ?>" class="btn btn-primary btn-sm px-4">
                            <i class="bi bi-plus-circle me-1"></i> Tạo bộ sưu tập ngay
                        </a>
                    </div>
                <?php else: ?>
                    <form action="<?= url('?module=collections&action=addDocument') ?>" method="POST">
                        <input type="hidden" name="tv4_token" value="<?= h($tv4Token ?? '') ?>">
                        <input type="hidden" name="document_id" value="<?= (int) $document['id'] ?>">
                        <input type="hidden" name="return_to" value="community">
                        <div class="modal-body">
                            <label class="form-label fw-medium" for="select-collection-id">Chọn bộ sưu tập:</label>
                            <select name="id" id="select-collection-id" class="form-select" required>
                                <?php foreach ($userCollections as $col): ?>
                                    <option value="<?= (int) $col['id'] ?>"><?= h($col['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <a href="<?= url('?module=collections&action=create') ?>" class="btn btn-link btn-sm text-decoration-none me-auto">
                                + Tạo bộ sưu tập mới
                            </a>
                            <button type="button" class="btn btn-light-subtle btn-sm" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-medium">
                                <i class="bi bi-bookmark-check me-1"></i> Lưu lại
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>