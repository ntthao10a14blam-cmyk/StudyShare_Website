/**
 * AUTH.JS — Tương tác phía client cho module xác thực
 * Phụ trách: TV1
 *
 * Chức năng:
 *  1. Toggle hiện/ẩn mật khẩu (tất cả các form auth)
 *  2. Thanh đánh giá độ mạnh mật khẩu (register + password-change)
 *  3. Validate realtime: mật khẩu xác nhận khớp chưa + Email RFC regex
 *  4. Preview ảnh avatar trước khi upload (profile)
 *  5. Drag & Drop avatar (profile)
 *  6. Xoá ảnh đã chọn (profile)
 */

(function () {
    'use strict';

    /* ============================================================
       1. TOGGLE HIỆN / ẨN MẬT KHẨU
       ============================================================ */

    function initPasswordToggle() {
        document.querySelectorAll('.auth-toggle-pw').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var targetId = btn.getAttribute('data-target');
                var input;

                if (targetId) {
                    input = document.getElementById(targetId);
                } else {
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
        { cls: '',                label: '' },
        { cls: 'strength-weak',   label: 'Yếu' },
        { cls: 'strength-fair',   label: 'Trung bình' },
        { cls: 'strength-good',   label: 'Khá' },
        { cls: 'strength-strong', label: 'Mạnh' },
    ];

    function initPasswordStrength() {
        var pwInput = document.getElementById('password') || document.getElementById('new_password');
        var fill    = document.getElementById('pwStrengthFill');
        var label   = document.getElementById('pwStrengthLabel');

        if (!pwInput || !fill || !label) return;

        pwInput.addEventListener('input', function () {
            var score = calcStrength(this.value);
            var map   = STRENGTH_MAP[score];

            fill.className = 'password-strength-fill';
            if (map.cls) fill.classList.add(map.cls);
            label.textContent = map.label;
        });
    }

    /* ============================================================
       3. VALIDATE REALTIME: MẬT KHẨU XÁC NHẬN & EMAIL RFC REGEX
       ============================================================ */

    function initFormRealtimeValidation() {
        var pwInput      = document.getElementById('password') || document.getElementById('new_password');
        var confirmInput = document.getElementById('password_confirm') || document.getElementById('confirm_password');
        var emailInput   = document.getElementById('email');
        var regForm      = document.getElementById('registerForm') || document.getElementById('passwordForm');

        // Regex RFC 5322 chuẩn kiểm tra email
        function isValidEmail(email) {
            var re = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
            return re.test(email);
        }

        function getErrorElement(input) {
            var parent = input.closest('.mb-3') || input.closest('.mb-4') || input.parentElement;
            var errDiv = parent.querySelector('.client-error-msg');
            if (!errDiv) {
                errDiv = document.createElement('div');
                errDiv.className = 'client-error-msg invalid-feedback d-block mt-1';
                parent.appendChild(errDiv);
            }
            return errDiv;
        }

        function clearError(input) {
            var parent = input.closest('.mb-3') || input.closest('.mb-4') || input.parentElement;
            var errDiv = parent.querySelector('.client-error-msg');
            if (errDiv) errDiv.textContent = '';
            input.classList.remove('is-invalid');
        }

        function checkPasswordMatch() {
            if (!pwInput || !confirmInput) return true;
            var submitBtn = (regForm) ? regForm.querySelector('button[type="submit"]') : null;
            var errDiv = getErrorElement(confirmInput);

            if (confirmInput.value === '') {
                clearError(confirmInput);
                if (submitBtn) submitBtn.disabled = false;
                return true;
            }

            if (pwInput.value !== confirmInput.value) {
                confirmInput.classList.add('is-invalid');
                errDiv.textContent = 'Mật khẩu xác nhận không khớp.';
                errDiv.style.color = '#dc3545';
                if (submitBtn) submitBtn.disabled = true;
                return false;
            } else {
                confirmInput.classList.remove('is-invalid');
                errDiv.textContent = '';
                if (submitBtn) submitBtn.disabled = false;
                return true;
            }
        }

        function checkEmail() {
            if (!emailInput) return true;
            var val = emailInput.value.trim();
            var errDiv = getErrorElement(emailInput);

            if (val === '') {
                clearError(emailInput);
                return true;
            }

            if (!isValidEmail(val)) {
                emailInput.classList.add('is-invalid');
                errDiv.textContent = 'Định dạng email không hợp lệ (ví dụ: sinhvien@studyhub.vn).';
                errDiv.style.color = '#dc3545';
                return false;
            } else {
                clearError(emailInput);
                return true;
            }
        }

        if (pwInput && confirmInput) {
            pwInput.addEventListener('input', checkPasswordMatch);
            confirmInput.addEventListener('input', checkPasswordMatch);
        }

        if (emailInput) {
            emailInput.addEventListener('input', checkEmail);
        }

        if (regForm) {
            regForm.addEventListener('submit', function (e) {
                var emailOk = checkEmail();
                var passOk = checkPasswordMatch();
                if (!emailOk || !passOk) {
                    e.preventDefault();
                }
            });
        }
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

        function handleFile(file) {
            if (!file || !file.type.startsWith('image/')) return;

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
                    try {
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        fileInput.files = dt.files;
                    } catch (_) {}
                    handleFile(file);
                }
            });
        }

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
        initFormRealtimeValidation();
        initAvatarUpload();
    });

})();
