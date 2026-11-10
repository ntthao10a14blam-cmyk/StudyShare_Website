document.addEventListener('DOMContentLoaded', function () {

    const modalButtons = document.querySelectorAll('[data-modal-open]');
    const modalCloseButtons = document.querySelectorAll('[data-modal-close]');

    modalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.getAttribute('data-modal-open');
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    modalCloseButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            closeModal(button.closest('.admin-modal'));
        });
    });

    document.querySelectorAll('.admin-modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.admin-modal.show').forEach(function (modal) {
                closeModal(modal);
            });
        }
    });

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('show');
        document.body.style.overflow = '';
    }


    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (event) {

            const message =
                element.getAttribute('data-confirm') ||
                'Bạn có chắc chắn muốn thực hiện thao tác này?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });


    document.querySelectorAll('[data-table-search]').forEach(function (input) {

        const tableId = input.getAttribute('data-table-search');
        const table = document.getElementById(tableId);

        if (!table) return;

        input.addEventListener('input', function () {

            const keyword = input.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {

                const text = row.textContent.toLowerCase();

                if (text.includes(keyword)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    document.querySelectorAll('[data-status-filter]').forEach(function (select) {

        const tableId = select.getAttribute('data-status-filter');
        const table = document.getElementById(tableId);

        if (!table) return;

        select.addEventListener('change', function () {

            const value = select.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {

                if (value === '' || value === 'all') {
                    row.style.display = '';
                    return;
                }

                const status = row.querySelector('.status');

                if (!status) {
                    row.style.display = 'none';
                    return;
                }

                const statusText = status.textContent
                    .toLowerCase()
                    .trim();

                row.style.display =
                    statusText.includes(value) ? '' : 'none';
            });
        });
    });


    const toast = document.querySelector('.admin-toast');

    if (toast) {
        setTimeout(function () {
            toast.classList.remove('show');
        }, 3500);
    }
    document.querySelectorAll('form').forEach(function (form) {

        form.addEventListener('submit', function () {

            const submitButton =
                form.querySelector('button[type="submit"]');

            if (!submitButton) return;

            if (submitButton.dataset.submitted === 'true') {
                return;
            }

            submitButton.dataset.submitted = 'true';

            const originalText = submitButton.innerHTML;

            submitButton.innerHTML = 'Đang xử lý...';
            submitButton.disabled = true;

            setTimeout(function () {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                submitButton.dataset.submitted = 'false';
            }, 5000);
        });
    });

});