/**
 * MAIN.JS — JavaScript dùng chung
 *
 * TV4 phụ trách file này.
 * Tạo file js/<module>.js riêng cho logic phức tạp của từng module.
 */

'use strict';

/* ============================================================
   CSRF token helper (lấy từ meta tag nếu cần gọi fetch/ajax)
   ============================================================ */
// Lấy từ form hidden input — đã được csrfField() in ra

/* ============================================================
   FLASH MESSAGE — tự ẩn sau 5 giây
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});

/* ============================================================
   XÁC NHẬN XÓA — gán data-confirm="Bạn có chắc?" trên form
   ============================================================ */
document.addEventListener('submit', (e) => {
    const form = e.target;
    const confirmMsg = form.dataset.confirm;
    if (confirmMsg) {
        if (!confirm(confirmMsg)) {
            e.preventDefault();
        }
    }
});

/* ============================================================
   XEM TRƯỚC ẢNH KHI CHỌN FILE — dùng chung cho avatar và ảnh minh hoạ
   input[type=file][data-preview="#preview-img"]
   ============================================================ */
document.addEventListener('change', (e) => {
    const input = e.target;
    if (input.type !== 'file' || !input.dataset.preview) return;

    const previewEl = document.querySelector(input.dataset.preview);
    if (!previewEl) return;

    const file = input.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
        previewEl.src = ev.target.result;
        previewEl.classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});

/* ============================================================
   GIỚI HẠN SỐ FILE UPLOAD
   input[type=file][data-max-files="5"]
   ============================================================ */
document.addEventListener('change', (e) => {
    const input = e.target;
    if (input.type !== 'file' || !input.dataset.maxFiles) return;

    const max = parseInt(input.dataset.maxFiles, 10);
    if (input.files.length > max) {
        alert(`Chỉ được chọn tối đa ${max} file.`);
        input.value = '';
    }
});

/* ============================================================
   TOOLTIP — kích hoạt tất cả tooltip Bootstrap
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el));
});
