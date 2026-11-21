

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 5000);
    });
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    const confirmMsg = form.dataset.confirm;
    if (confirmMsg) {
        if (!confirm(confirmMsg)) {
            e.preventDefault();
        }
    }
});

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

document.addEventListener('change', (e) => {
    const input = e.target;
    if (input.type !== 'file' || !input.dataset.maxFiles) return;

    const max = parseInt(input.dataset.maxFiles, 10);
    if (input.files.length > max) {
        alert(`Chỉ được chọn tối đa ${max} file.`);
        input.value = '';
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el));

    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
