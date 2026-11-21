
'use strict';

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('textarea[data-counter]').forEach(area => {
        const out = document.querySelector(area.dataset.counter);
        if (!out) return;
        const update = () => { out.textContent = area.value.length; };
        area.addEventListener('input', update);
        update();
    });

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

    document.querySelectorAll('input[type=file][data-previews]').forEach(input => {
        const box = document.querySelector(input.dataset.previews);
        if (!box) return;
        input.addEventListener('change', () => {
            box.innerHTML = '';

            const max = parseInt(input.dataset.maxFiles, 10);
            if (max && input.files.length > max) return;
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

    const editors = [];
    if (window.EasyMDE) {
        document.querySelectorAll('textarea.md-editor').forEach(area => {
            const mde = new EasyMDE({
                element: area,
                spellChecker: false,
                status: false,
                forceSync: true,
                minHeight: '160px',
                placeholder: area.getAttribute('placeholder') || '',
                toolbar: ['bold', 'italic', 'heading', '|', 'quote', 'unordered-list',
                          'ordered-list', '|', 'link', 'code', '|', 'preview']
            });

            mde.codemirror.on('change', () => area.dispatchEvent(new Event('input')));
            editors.push(mde);
        });

        document.addEventListener('shown.bs.collapse', () => {
            editors.forEach(m => m.codemirror.refresh());
        });
    }

    if (window.marked && window.DOMPurify) {
        document.querySelectorAll('.md-render').forEach(el => {
            if (el.dataset.rendered) return;
            el.innerHTML = DOMPurify.sanitize(marked.parse(el.textContent, { breaks: true }));
            el.dataset.rendered = '1';
        });
    }
});
