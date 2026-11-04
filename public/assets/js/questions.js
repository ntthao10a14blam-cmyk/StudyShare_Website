/**
 * QUESTIONS.JS — JavaScript riêng cho module Hỏi đáp (TV5)
 * Nạp qua $extraJs = ['questions.js'] trong QuestionController.
 * (Xác nhận xóa dùng data-confirm và giới hạn số file dùng data-max-files
 *  đã có sẵn trong main.js — không viết lại ở đây.)
 */
'use strict';

document.addEventListener('DOMContentLoaded', () => {

    /* 1. Bộ đếm ký tự: <textarea data-counter="#id" data-counter-max="20000"> */
    document.querySelectorAll('textarea[data-counter]').forEach(area => {
        const out = document.querySelector(area.dataset.counter);
        if (!out) return;
        const update = () => { out.textContent = area.value.length; };
        area.addEventListener('input', update);
        update();
    });

    /* 2. Giới hạn số từ khóa được tick (tối đa theo data-max-checked) */
    document.querySelectorAll('[data-max-checked]').forEach(group => {
        const max = parseInt(group.dataset.maxChecked, 10);
        group.addEventListener('change', e => {
            if (!e.target.matches('input[type=checkbox]')) return;
            if (group.querySelectorAll('input[type=checkbox]:checked').length > max) {
                e.target.checked = false;
                alert(`Chỉ được chọn tối đa ${max} từ khóa.`);
            }
        });
    });

    /* 3. Xem trước nhiều ảnh: <input type=file multiple data-previews="#box"> */
    document.querySelectorAll('input[type=file][data-previews]').forEach(input => {
        const box = document.querySelector(input.dataset.previews);
        if (!box) return;
        input.addEventListener('change', () => {
            box.innerHTML = '';
            Array.from(input.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const img = document.createElement('img');
                img.className = 'q-preview';
                img.alt = file.name;
                img.src = URL.createObjectURL(file);
                img.onload = () => URL.revokeObjectURL(img.src);
                box.appendChild(img);
            });
        });
    });

    /* 4. Modal báo cáo: điền loại + id đối tượng khi bấm nút cờ */
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            if (!btn) return;
            modal.querySelector('#report-type').value = btn.dataset.reportType;
            modal.querySelector('#report-id').value   = btn.dataset.reportId;
            modal.querySelector('#report-label').textContent = btn.dataset.reportLabel;
            modal.querySelector('#report-reason').value = '';
            modal.querySelector('#report-details').value = '';
        });
    }
});
