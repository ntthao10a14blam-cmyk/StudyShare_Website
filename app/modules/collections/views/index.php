<?php
$totalCollections = count($collections ?? []);
$totalSavedDocs   = array_sum(array_map(fn($c) => (int) ($c['total'] ?? 0), $collections ?? []));
?>

<!-- HERO BANNER ĐẦU TRANG (GIỐNG TÀI LIỆU CỦA TÔI) -->
<section class="text-white py-5" style="background-color: #075e57; border-bottom: 3px solid #f5d77a;">
    <div class="container">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <h1 class="h2 fw-bold text-white mb-3">Bộ Sưu Tập Của Tôi</h1>

                <!-- THANH TÌM KIẾM BỘ SƯU TẬP NẰM TRONG HERO (PILL TRẮNG + NÚT VÀNG) -->
                <form id="collectionSearchForm" onsubmit="return false;">
                    <div class="bg-white rounded-pill p-1 shadow-sm d-flex align-items-center" style="max-width: 720px;">
                        <span class="ps-3 text-muted"><i class="bi bi-search"></i></span>
                        <input type="search" id="collectionSearchInput"
                               class="form-control border-0 bg-transparent py-2 px-3 shadow-none text-dark"
                               placeholder="Tìm kiếm bộ sưu tập theo tên hoặc mô tả...">
                        <button type="submit" id="collectionSearchBtn"
                                class="btn btn-warning rounded-pill px-4 py-2 fw-semibold text-dark flex-shrink-0">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-4 d-flex flex-wrap justify-content-center justify-content-lg-end align-items-center gap-2">
                <a class="btn btn-outline-light rounded-3 px-3 py-2 fw-medium d-inline-flex align-items-center gap-2"
                   href="index.php?module=collections&action=favorites">
                    <i class="bi bi-heart-fill text-warning"></i>
                    <span>Tài liệu yêu thích</span>
                </a>
                <a class="btn home-v2-btn-accent rounded-3 px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2"
                   href="index.php?module=collections&action=create">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Tạo bộ sưu tập</span>
                </a>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <?php if (empty($collections)): ?>
        <div class="home-v2-card p-5 text-center">
            <div class="mb-3 text-primary">
                <i class="bi bi-collection fs-1"></i>
            </div>
            <h2 class="h5 fw-bold text-dark mb-2">Bạn chưa có bộ sưu tập nào</h2>
            <p class="text-muted small mb-3">Hãy tạo bộ sưu tập đầu tiên để lưu trữ tài liệu ôn tập theo từng môn học.</p>
            <a class="btn btn-primary rounded-pill px-4" href="index.php?module=collections&action=create">
                <i class="bi bi-plus-lg me-1"></i> Tạo bộ sưu tập ngay
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($collections as $item): ?>
                <div class="col-md-6 col-lg-4 collection-grid-item" data-search="<?= h($item['name'] . ' ' . ($item['description'] ?? '')) ?>">
                    <div class="home-v2-card overflow-hidden h-100 d-flex flex-column">
                        <div class="home-v2-doc-thumb d-flex justify-content-between align-items-center">
                            <span class="d-inline-flex align-items-center gap-2 text-primary fw-semibold small">
                                <i class="bi bi-folder2-open fs-5"></i>
                                <span>Bộ sưu tập</span>
                            </span>
                            <span class="badge bg-white text-primary border border-primary-subtle px-2 py-1">
                                <?= (int) $item['total'] ?> tài liệu
                            </span>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <h2 class="h5 fw-bold text-dark mb-2 text-truncate">
                                <a href="index.php?module=collections&action=show&id=<?= (int) $item['id'] ?>"
                                   class="text-decoration-none text-dark hover-primary">
                                    <?= h($item['name']) ?>
                                </a>
                            </h2>
                            <p class="text-muted small flex-grow-1 mb-4 text-truncate-2">
                                <?= h($item['description'] ?: 'Chưa có mô tả cho bộ sưu tập này.') ?>
                            </p>
                            <div class="d-flex align-items-center gap-2 pt-3 border-top">
                                <a class="btn btn-primary btn-sm px-3 fw-medium"
                                   href="index.php?module=collections&action=show&id=<?= (int) $item['id'] ?>">
                                    <i class="bi bi-eye me-1"></i> Xem
                                </a>
                                <a class="btn btn-outline-primary btn-sm px-3"
                                   href="index.php?module=collections&action=edit&id=<?= (int) $item['id'] ?>">
                                    <i class="bi bi-pencil me-1"></i> Sửa
                                </a>
                                <form class="ms-auto" method="post" action="index.php?module=collections&action=delete"
                                      data-confirm="Xóa bộ sưu tập này? Tài liệu gốc sẽ không bị xóa.">
                                    <input type="hidden" name="tv4_token" value="<?= h($tv4Token) ?>">
                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm px-2" title="Xóa bộ sưu tập">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('collectionSearchInput');
    if (!input) return;
    input.addEventListener('input', function () {
        const kw = this.value.trim().toLowerCase();
        document.querySelectorAll('.collection-grid-item').forEach(function (card) {
            const text = (card.dataset.search || '').toLowerCase();
            card.style.display = text.includes(kw) ? '' : 'none';
        });
    });
});
</script>
