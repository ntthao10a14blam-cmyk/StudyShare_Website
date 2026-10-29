/**
 * AUTH.JS — Tương tác phía client cho module xác thực
 * Phụ trách: TV1
 *
 * Chức năng:
 *  1. Toggle hiện/ẩn mật khẩu (tất cả các form auth)
 *  2. Thanh đánh giá độ mạnh mật khẩu (register + password-change)
 *  3. Validate realtime: mật khẩu xác nhận khớp chưa
 *  4. Preview ảnh avatar trước khi upload (profile)
 *  5. Drag & Drop avatar (profile)
 *  6. Xoá ảnh đã chọn (profile)
 */

(function () {
    'use strict';

    /* ============================================================
       1. TOGGLE HIỆN / ẨN MẬT KHẨU
       ============================================================ */

    /**
     * Khai báo chung: mỗi nút `.auth-toggle-pw` toggle input ngay trước nó.
     * Hỗ trợ cả hai pattern:
     *   a) data-target="input_id"  → dùng ở password.php (nhiều nút)
     *   b) Không có data-target   → tìm input trong cùng .input-group
     */
    function initPasswordToggle() {
        document.querySelectorAll('.auth-toggle-pw').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = btn.getAttribute('data-target');
                var input;

                if (targetId) {
                    input = document.getElementById(targetId);
                } else {
                    // Tìm input[type=password] trong cùng .input-group
                    var group = btn.closest('.input-group');
                    input = group ? group.querySelector('input[type="password"], input[type="text"]') : null;
                }

                if (!input) return;

                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';

                var icon = btn.querySelector('i');
                if (icon) {
                    icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
                }
            });
        });
    }

    /* ============================================================
       2. ĐỘ MẠNH MẬT KHẨU
       ============================================================ */

    /**
     * Trả về điểm 0-4 dựa trên các tiêu chí:
     *   +1: ít nhất 8 ký tự
     *   +1: có chữ thường
     *   +1: có chữ in hoa hoặc số
     *   +1: có ký tự đặc biệt
     */
    function calcStrength(pwd) {
        if (!pwd) return 0;
        var score = 0;
        if (pwd.length >= 8)               score++;
        if (/[a-z]/.test(pwd))             score++;
        if (/[A-Z0-9]/.test(pwd))          score++;
        if (/[^A-Za-z0-9]/.test(pwd))      score++;
        return score;
    }

    var STRENGTH_MAP = [
        { cls: '',               label: ''        },
        { cls: 'strength-weak',  label: 'Yếu'     },
        { cls: 'strength-fair',  label: 'Trung bình' },
        { cls: 'strength-good',  label: 'Khá'     },
        { cls: 'strength-strong',label: 'Mạnh'    },
    ];

    function initPasswordStrength() {
        // Trang đăng ký dùng #password, trang đổi mật khẩu dùng #new_password
        var pwInput = document.getElementById('password') || document.getElementById('new_password');
        var fill    = document.getElementById('pwStrengthFill');
        var label   = document.getElementById('pwStrengthLabel');

        if (!pwInput || !fill || !label) return;

        pwInput.addEventListener('input', function () {
            var score = calcStrength(this.value);
            var map   = STRENGTH_MAP[score];

            // Reset class
            fill.className = 'password-strength-fill';
            if (map.cls) fill.classList.add(map.cls);
            label.textContent = map.label;
        });
    }

    /* ============================================================
       3. VALIDATE REALTIME: MẬT KHẨU XÁC NHẬN
       ============================================================ */

    function initPasswordConfirmValidation() {
        var pwInput      = document.getElementById('password') || document.getElementById('new_password');
        var confirmInput = document.getElementById('password_confirm') || document.getElementById('confirm_password');

        if (!pwInput || !confirmInput) return;

        function check() {
            if (confirmInput.value === '') {
                confirmInput.setCustomValidity('');
                return;
            }
            if (pwInput.value !== confirmInput.value) {
                confirmInput.setCustomValidity('Mật khẩu xác nhận không khớp.');
                confirmInput.classList.add('is-invalid');
            } else {
                confirmInput.setCustomValidity('');
                confirmInput.classList.remove('is-invalid');
            }
        }

        pwInput.addEventListener('input', check);
        confirmInput.addEventListener('input', check);
    }

    /* ============================================================
       4. PREVIEW + DRAG & DROP + XÓA ẢNH AVATAR (profile.php)
       ============================================================ */

    function initAvatarUpload() {
        var fileInput    = document.getElementById('avatar');
        var dropZone     = document.getElementById('avatarDropZone');
        var preview      = document.getElementById('avatarPreview');
        var placeholder  = document.getElementById('avatarPlaceholder');
        var fileInfo     = document.getElementById('fileInfo');
        var fileNameSpan = document.getElementById('fileName');
        var clearBtn     = document.getElementById('clearFile');

        if (!fileInput) return;

        // Hiển thị preview ảnh + tên file
        function handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;

            // Kiểm tra kích thước (tối đa 2 MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 2 MB.');
                fileInput.value = '';
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                if (placeholder) placeholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);

            if (fileNameSpan) fileNameSpan.textContent = file.name;
            if (fileInfo)     fileInfo.classList.remove('d-none');
        }

        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                handleFile(this.files[0]);
            }
        });

        // Drag & Drop
        if (dropZone) {
            dropZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            dropZone.addEventListener('dragleave', function () {
                dropZone.classList.remove('drag-over');
            });
            dropZone.addEventListener('drop', function (e) {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                var file = e.dataTransfer.files[0];
                if (file) {
                    // Gán file vào input (DataTransfer)
                    try {
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        fileInput.files = dt.files;
                    } catch (_) { /* Trình duyệt cũ không hỗ trợ */ }
                    handleFile(file);
                }
            });
        }

        // Nút xóa ảnh đã chọn
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                fileInput.value = '';
                if (preview)     { preview.src = ''; preview.classList.add('d-none'); }
                if (placeholder) placeholder.classList.remove('d-none');
                if (fileInfo)    fileInfo.classList.add('d-none');
            });
        }
    }

    /* ============================================================
       KHỞI ĐỘNG
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        initPasswordToggle();
        initPasswordStrength();
        initPasswordConfirmValidation();
        initAvatarUpload();
    });

})();

