document.addEventListener('DOMContentLoaded', function () {

    // Mở hộp thoại
    document.querySelectorAll('[data-modal-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.dataset.modalOpen;
            const modal = document.getElementById(modalId);

            if (modal) {
                modal.classList.add('show');
            }
        });
    });

    // Đóng hộp thoại
    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = button.closest('.admin-modal');

            if (modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Đóng hộp thoại khi nhấn ra bên ngoài
    document.querySelectorAll('.admin-modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.classList.remove('show');
            }
        });
    });

    // Đóng hộp thoại bằng phím Escape
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            document.querySelectorAll('.admin-modal.show').forEach(function (modal) {
                modal.classList.remove('show');
            });
        }
    });

    // Hiển thị hộp thoại xác nhận trước khi thực hiện thao tác
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (event) {
            const message = element.dataset.confirm;

            if (message && !confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Tìm kiếm dữ liệu trên bảng hiện tại
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        input.addEventListener('input', function () {
            const tableId = input.dataset.tableSearch;
            const table = document.getElementById(tableId);

            if (!table) return;

            const keyword = input.value.trim().toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                const content = row.textContent.toLowerCase();
                row.style.display = content.includes(keyword) ? '' : 'none';
            });
        });
    });

    // Lọc dữ liệu theo trạng thái
    document.querySelectorAll('[data-status-filter]').forEach(function (select) {
        select.addEventListener('change', function () {
            const tableId = select.dataset.statusFilter;
            const table = document.getElementById(tableId);

            if (!table) return;

            const selectedStatus = select.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function (row) {
                const rowStatus = (row.dataset.status || '').toLowerCase();

                row.style.display =
                    selectedStatus === 'all' || rowStatus === selectedStatus
                        ? ''
                        : 'none';
            });
        });
    });

    // Tự động ẩn thông báo sau một khoảng thời gian
    document.querySelectorAll('.admin-toast').forEach(function (toast) {
        setTimeout(function () {
            toast.classList.remove('show');

            setTimeout(function () {
                toast.remove();
            }, 300);
        }, 3500);
    });

    // Ngăn biểu mẫu gửi nhiều lần
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.submitted === 'true') {
                return;
            }

            form.dataset.submitted = 'true';

            const submitButtons = form.querySelectorAll(
                'button[type="submit"], input[type="submit"]'
            );

            submitButtons.forEach(function (button) {
                button.disabled = true;

                if (button.tagName === 'BUTTON') {
                    button.dataset.originalText = button.textContent;
                    button.textContent = 'Đang xử lý...';
                } else {
                    button.dataset.originalValue = button.value;
                    button.value = 'Đang xử lý...';
                }
            });
        });
    });

    // Hiển thị dung lượng tệp được chọn
    document.querySelectorAll('[data-size-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = input.dataset.sizePreview;
            const preview = document.getElementById(previewId);

            if (!preview || !input.files.length) return;

            const file = input.files[0];
            const size = file.size / (1024 * 1024);

            preview.textContent = size.toFixed(2) + ' MB';
        });
    });

    // Đếm số ký tự đã nhập
    document.querySelectorAll('[data-max-length]').forEach(function (input) {
        const counterId = input.dataset.counter;
        const maxLength = parseInt(input.dataset.maxLength, 10);
        const counter = document.getElementById(counterId);

        if (!counter || !maxLength) return;

        function updateCounter() {
            const currentLength = input.value.length;

            counter.textContent = currentLength + '/' + maxLength;

            counter.classList.toggle(
                'text-danger',
                currentLength > maxLength
            );
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    });

});