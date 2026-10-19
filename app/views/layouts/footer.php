    <!-- Kết thúc nội dung trang -->
    </main>
</div><!-- /.container hoặc wrapper từ từng trang -->

<!-- FOOTER -->
<footer class="footer bg-dark text-light mt-auto py-4">
    <div class="container">
        <div class="row gy-3">
            <div class="col-md-4">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-book-half me-1"></i> StudyHub
                </h6>
                <p class="text-muted small mb-0">
                    Nền tảng học tập và chia sẻ tài liệu dành cho sinh viên.
                </p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-bold mb-2">Liên kết nhanh</h6>
                <ul class="list-unstyled small mb-0">
                    <li><a href="<?= url('?module=community&action=index') ?>" class="text-muted text-decoration-none">Tài liệu công khai</a></li>
                    <li><a href="<?= url('?module=questions&action=index') ?>" class="text-muted text-decoration-none">Hỏi đáp học tập</a></li>
                </ul>
            </div>
            <div class="col-md-4 text-md-end">
                <p class="text-muted small mb-0">
                    &copy; <?= date('Y') ?> StudyHub.<br>
                    Được xây dựng bởi nhóm sinh viên.
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS chung -->
<script src="<?= url('assets/js/main.js') ?>"></script>

<!-- JS riêng từng module (controller set $extraJs nếu cần) -->
<?php if (!empty($extraJs)): ?>
    <?php foreach ((array) $extraJs as $js): ?>
        <script src="<?= url('assets/js/' . h($js)) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
