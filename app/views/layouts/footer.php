
</main>

<footer class="public-footer">
    <div class="container">

        <div class="tlga-footer-ribbon d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3">
            <div>
                <small class="d-block text-warning fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.12em;">TÀI LIỆU DÀNH CHO SINH VIÊN</small>
                <strong class="text-white fs-6">Đúng trường - Đúng môn - Đúng nhu cầu</strong>
            </div>
            <nav class="footer-formats d-flex gap-2 gap-md-3 mt-2 mt-md-0" aria-label="Định dạng tài liệu">
                <span><i class="bi bi-file-earmark-word text-info"></i> Word</span>
                <span><i class="bi bi-file-earmark-excel text-success"></i> Excel</span>
                <span><i class="bi bi-file-earmark-pdf text-danger"></i> PDF</span>
                <span><i class="bi bi-google text-warning"></i> Drive</span>
            </nav>
        </div>

        <div class="footer-main py-5">
            <div class="row g-4">

                <div class="col-lg-4 col-md-6 footer-about">
                    <a class="public-brand footer-brand text-decoration-none d-flex align-items-center gap-2 mb-3" href="<?= url('/') ?>">
                        <div class="brand-symbol">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div class="brand-copy">
                            <strong class="text-white fs-5">StudyShare</strong>
                            <small class="text-white-50 d-block" style="font-size: 10px;">PLATFORM</small>
                        </div>
                    </a>
                    <p class="text-white-50 small pe-lg-3 lh-base">
                        Website cộng đồng học tập và chia sẻ tài liệu dành cho sinh viên.
                    </p>
                    <div class="footer-social d-flex gap-2 mt-3">
                        <span class="social-icon" title="Facebook"><i class="bi bi-facebook"></i></span>
                        <span class="social-icon" title="YouTube"><i class="bi bi-youtube"></i></span>
                        <span class="social-icon" title="TikTok"><i class="bi bi-tiktok"></i></span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 footer-col">
                    <h3 class="footer-heading">Tài liệu</h3>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="<?= url('?module=community&action=index') ?>">Thư viện tài liệu</a></li>
                        <li><a href="<?= url('?module=community&action=index') ?>">Theo trường</a></li>
                        <li><a href="<?= url('?module=community&action=index') ?>">Theo môn học</a></li>
                        <li><a href="<?= url('?module=community&action=index') ?>">Theo loại tài liệu</a></li>
                        <li><a href="<?= url('?module=questions&action=index') ?>">Góc hỏi đáp học tập</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 footer-col">
                    <h3 class="footer-heading">Về StudyShare</h3>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="<?= url('/') ?>">Giới thiệu dự án</a></li>
                        <li><a href="<?= url('/') ?>">Đội ngũ biên soạn</a></li>
                        <li><a href="<?= url('/') ?>">Hướng dẫn tải tài liệu</a></li>
                        <li><a href="<?= url('/') ?>">Chính sách nội dung</a></li>
                        <li><a href="<?= url('/') ?>">Trách nhiệm sử dụng</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 footer-contact">
                    <h3 class="footer-heading">Liên hệ &amp; hỗ trợ</h3>
                    <div class="d-flex flex-column gap-2 small text-white-50 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope text-warning"></i>
                            <a href="mailto:[EMAIL_ADDRESS]" class="text-white-50 text-decoration-none">[EMAIL_ADDRESS]</a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-telephone text-warning"></i>
                            <a href="tel:0900000000" class="text-white-50 text-decoration-none">0900 000 000</a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-warning"></i>
                            <span>Việt Nam</span>
                        </div>
                    </div>

                    <a class="footer-contact-action d-flex align-items-center gap-2 p-2 rounded mb-2 text-decoration-none"
                       href="mailto:[EMAIL_ADDRESS]?subject=Bao%20link%20loi">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <div>
                            <b class="d-block text-white" style="font-size: 12px;">Báo link tài liệu lỗi</b>
                            <small class="text-white-50" style="font-size: 11px;">Gửi URL để được kiểm tra</small>
                        </div>
                    </a>

                    <div class="footer-support-hours d-flex align-items-center gap-2 text-white-50 small mt-2">
                        <i class="bi bi-clock-history text-info"></i>
                        <span>Thời gian phản hồi: <b>1–2 ngày làm việc</b></span>
                    </div>
                </div>

            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
            <span class="text-white-50 small mb-2 mb-md-0">
                &copy; <?= date('Y') ?> StudyShare. All rights reserved.
            </span>
            <div class="d-flex gap-3 small">
                <a href="<?= url('/') ?>">Bảo mật</a>
                <a href="<?= url('/') ?>">Điều khoản</a>
                <a href="<?= url('/') ?>">Miễn trừ trách nhiệm</a>
            </div>
        </div>
    </div>
</footer>

<button class="back-to-top" id="backToTop" type="button" aria-label="Lên đầu trang">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= url('assets/js/main.js') ?>"></script>

<?php if (!empty($extraJs)): ?>
    <?php foreach ((array) $extraJs as $js): ?>
        <script src="<?= url('assets/js/' . h($js)) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
