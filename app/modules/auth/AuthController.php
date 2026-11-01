<?php
/**
 * AUTHCONTROLLER.PHP — Xử lý tài khoản người dùng
 * Phụ trách: TV1
 *
 * Actions:
 *   login    — Hiển thị form và xử lý đăng nhập
 *   register — Hiển thị form và xử lý đăng ký
 *   logout   — Đăng xuất
 *   profile  — Xem và cập nhật hồ sơ
 *   password — Đổi mật khẩu
 */

require_once BASE_PATH . '/app/modules/auth/AuthModel.php';

class AuthController
{
    private AuthModel $model;

    public function __construct()
    {
        $this->model = new AuthModel();
    }

    // ----------------------------------------------------------
    // ĐĂNG NHẬP
    // ----------------------------------------------------------
    public function login(): void
    {
        // Nếu đã đăng nhập → về trang chủ
        if (Auth::check()) {
            redirect('/');
        }

        $errors = [];
        $email  = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $email    = post('email');
            $password = post('password');

            // Validate cơ bản
            if (empty($email))    $errors['email']    = 'Vui lòng nhập email.';
            if (empty($password)) $errors['password'] = 'Vui lòng nhập mật khẩu.';

            if (empty($errors)) {
                $user = $this->model->findByEmail($email);

                if (!$user || !password_verify($password, $user['password'])) {
                    $errors['general'] = 'Email hoặc mật khẩu không đúng.';
                } elseif ($user['status'] === 'locked') {
                    $errors['general'] = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
                } else {
                    Auth::login($user);
                    redirect($user['role'] === 'admin' ? '?module=admin&action=dashboard' : '/', 'Đăng nhập thành công!', 'success');
                }
            }
        }

        $pageTitle = 'Đăng nhập';
        require_once BASE_PATH . '/app/modules/auth/views/login.php';
    }

    // ----------------------------------------------------------
    // ĐĂNG KÝ
    // ----------------------------------------------------------
    public function register(): void
    {
        if (Auth::check()) redirect('/');

        $errors = [];
        $data   = ['full_name' => '', 'email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $data['full_name'] = post('full_name');
            $data['email']     = post('email');
            $password          = post('password');
            $passwordConfirm   = post('password_confirm');

            // Validate
            if (empty($data['full_name']))           $errors['full_name'] = 'Vui lòng nhập họ tên.';
            if (strlen($data['full_name']) > 100)    $errors['full_name'] = 'Họ tên không quá 100 ký tự.';
            if (empty($data['email']))               $errors['email']     = 'Vui lòng nhập email.';
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Email không hợp lệ.';
            if (strlen($password) < 8)               $errors['password']  = 'Mật khẩu ít nhất 8 ký tự.';
            if ($password !== $passwordConfirm)      $errors['password_confirm'] = 'Mật khẩu xác nhận không khớp.';

            if (empty($errors)) {
                if ($this->model->emailExists($data['email'])) {
                    $errors['email'] = 'Email này đã được sử dụng.';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $this->model->createUser($data['full_name'], $data['email'], $hashedPassword);
                    redirect('?module=auth&action=login', 'Đăng ký thành công! Vui lòng đăng nhập.', 'success');
                }
            }
        }

        $pageTitle = 'Đăng ký tài khoản';
        require_once BASE_PATH . '/app/modules/auth/views/register.php';
    }

    // ----------------------------------------------------------
    // ĐĂNG XUẤT
    // ----------------------------------------------------------
    public function logout(): void
    {
        Auth::logout();
        redirect('?module=auth&action=login', 'Bạn đã đăng xuất.', 'info');
    }

    // ----------------------------------------------------------
    // HỒ SƠ CÁ NHÂN
    // ----------------------------------------------------------
    public function profile(): void
    {
        Auth::requireActiveUser();

        $user   = $this->model->findById(Auth::id());
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $fullName = post('full_name');
            if (empty($fullName))          $errors['full_name'] = 'Vui lòng nhập họ tên.';
            if (strlen($fullName) > 100)   $errors['full_name'] = 'Họ tên không quá 100 ký tự.';

            // Upload avatar nếu có
            $avatarPath = $user['avatar_path'];
            if (!empty($_FILES['avatar']['name'])) {
                $result = Upload::avatar($_FILES['avatar'], STORAGE_AVATARS);
                if ($result['success']) {
                    // Xóa avatar cũ nếu có
                    if ($avatarPath) Upload::delete($avatarPath);
                    $avatarPath = $result['path'];
                } else {
                    $errors['avatar'] = $result['error'];
                }
            }

            if (empty($errors)) {
                $this->model->updateProfile(Auth::id(), $fullName, $avatarPath);
                // Cập nhật tên trong session
                $_SESSION['auth_full_name'] = $fullName;
                redirect('?module=auth&action=profile', 'Cập nhật hồ sơ thành công!', 'success');
            }
        }

        $pageTitle = 'Hồ sơ cá nhân';
        require_once BASE_PATH . '/app/modules/auth/views/profile.php';
    }

    // ----------------------------------------------------------
    // ĐỔI MẬT KHẨU
    // ----------------------------------------------------------
    public function password(): void
    {
        Auth::requireActiveUser();

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();

            $current  = post('current_password');
            $new      = post('new_password');
            $confirm  = post('confirm_password');

            $user = $this->model->findById(Auth::id());

            if (!password_verify($current, $user['password'])) {
                $errors['current_password'] = 'Mật khẩu hiện tại không đúng.';
            }
            if (strlen($new) < 8) {
                $errors['new_password'] = 'Mật khẩu mới ít nhất 8 ký tự.';
            }
            if ($new !== $confirm) {
                $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
            }

            if (empty($errors)) {
                $this->model->updatePassword(Auth::id(), password_hash($new, PASSWORD_BCRYPT));
                redirect('?module=auth&action=profile', 'Đổi mật khẩu thành công!', 'success');
            }
        }

        $pageTitle = 'Đổi mật khẩu';
        require_once BASE_PATH . '/app/modules/auth/views/password.php';
    }
}
