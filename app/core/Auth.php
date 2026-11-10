<?php
/**
 * AUTH.PHP — Xác thực và phân quyền dùng chung
 *
 * Tất cả thành viên PHẢI dùng các hàm này để kiểm tra quyền.
 * Không tự kiểm tra $_SESSION['role'] hay $_SESSION['user_id'] riêng lẻ.
 *
 * Cách dùng:
 *   Auth::requireLogin();               // Chặn nếu chưa đăng nhập
 *   Auth::requireRole('admin');         // Chặn nếu không phải admin
 *   Auth::requireActiveUser();          // Chặn nếu bị khóa
 *   $user = Auth::currentUser();        // Lấy thông tin user hiện tại
 *   Auth::check();                      // true nếu đã đăng nhập
 *   Auth::isAdmin();                    // true nếu là admin
 *   Auth::id();                         // ID user hiện tại (hoặc null)
 *   Auth::owns($ownerId);               // true nếu user hiện tại là chủ sở hữu
 */

class Auth
{
    // Tên key trong session — không thay đổi tùy ý
    private const KEY_USER_ID   = 'auth_user_id';
    private const KEY_ROLE      = 'auth_role';
    private const KEY_STATUS    = 'auth_status';
    private const KEY_FULL_NAME = 'auth_full_name';

    // ============================================================
    // KHỞI TẠO SESSION
    // ============================================================

    /**
     * Bắt đầu session nếu chưa bắt đầu.
     * Gọi một lần trong bootstrap (public/index.php).
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,   // Đổi sang true khi dùng HTTPS
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    // ============================================================
    // ĐĂNG NHẬP / ĐĂNG XUẤT
    // ============================================================

    /**
     * Lưu thông tin user vào session sau khi xác thực thành công.
     * Gọi trong AuthController sau khi kiểm tra password.
     */
    public static function login(array $user): void
    {
        if (!headers_sent()) {
            session_regenerate_id(true); // Chống session fixation
        }
        $_SESSION[self::KEY_USER_ID]   = (int) $user['id'];
        $_SESSION[self::KEY_ROLE]      = $user['role'];
        $_SESSION[self::KEY_STATUS]    = $user['status'];
        $_SESSION[self::KEY_FULL_NAME] = $user['full_name'];
    }

    /**
     * Xóa session — đăng xuất.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    /**
     * Cập nhật trạng thái trong session (dùng khi Admin khóa tài khoản đang đăng nhập).
     */
    public static function refreshStatus(string $status): void
    {
        $_SESSION[self::KEY_STATUS] = $status;
    }

    // ============================================================
    // KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN
    // ============================================================

    /**
     * Trả về true nếu đã đăng nhập (bất kể bị khóa hay không).
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::KEY_USER_ID]);
    }

    /**
     * Trả về true nếu đã đăng nhập VÀ tài khoản đang active.
     */
    public static function isActive(): bool
    {
        return self::check() && $_SESSION[self::KEY_STATUS] === 'active';
    }

    /**
     * Trả về true nếu là admin.
     */
    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION[self::KEY_ROLE] === 'admin';
    }

    /**
     * Trả về true nếu là user thông thường (không phải admin).
     */
    public static function isUser(): bool
    {
        return self::check() && $_SESSION[self::KEY_ROLE] === 'user';
    }

    /**
     * ID người dùng hiện tại. Null nếu chưa đăng nhập.
     */
    public static function id(): ?int
    {
        return $_SESSION[self::KEY_USER_ID] ?? null;
    }

    /**
     * Trả về true nếu $ownerId trùng với ID user hiện tại.
     * Dùng để kiểm tra quyền sở hữu nội dung.
     */
    public static function owns(int $ownerId): bool
    {
        return self::id() === $ownerId;
    }

    /**
     * Thông tin cơ bản của user đang đăng nhập.
     * Trả về null nếu chưa đăng nhập.
     */
    public static function currentUser(): ?array
    {
        if (!self::check()) return null;
        return [
            'id'        => $_SESSION[self::KEY_USER_ID],
            'full_name' => $_SESSION[self::KEY_FULL_NAME],
            'role'      => $_SESSION[self::KEY_ROLE],
            'status'    => $_SESSION[self::KEY_STATUS],
        ];
    }

    // ============================================================
    // MIDDLEWARE — GỌI ĐẦU MỖI TRANG CẦN BẢO VỆ
    // ============================================================

    /**
     * Chuyển hướng về trang đăng nhập nếu chưa đăng nhập.
     */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::redirect('/auth/login', 'Vui lòng đăng nhập để tiếp tục.');
        }
    }

    /**
     * Chặn tài khoản bị khóa.
     * Gọi SAU requireLogin() trên các trang User.
     */
    public static function requireActiveUser(): void
    {
        self::requireLogin();
        if ($_SESSION[self::KEY_STATUS] === 'locked') {
            self::logout();
            self::redirect('/auth/login', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }
    }

    /**
     * Chặn nếu không phải admin.
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            self::redirect('/', 'Bạn không có quyền truy cập trang này.');
        }
    }

    /**
     * Hàm chung kiểm tra role. Có thể mở rộng sau.
     * $role: 'admin' | 'user'
     */
    public static function requireRole(string $role): void
    {
        if ($role === 'admin') {
            self::requireAdmin();
        } else {
            self::requireActiveUser();
        }
    }

    /**
     * Kiểm tra quyền sở hữu. Nếu không phải chủ sở hữu VÀ không phải admin → 403.
     */
    public static function requireOwnerOrAdmin(int $ownerId): void
    {
        self::requireLogin();
        if (!self::owns($ownerId) && !self::isAdmin()) {
            http_response_code(403);
            self::redirect('/', 'Bạn không có quyền thực hiện thao tác này.');
        }
    }

    // ============================================================
    // HELPER
    // ============================================================

    /**
     * Lưu thông báo flash vào session và chuyển hướng.
     */
    public static function redirect(string $path, string $message = '', string $type = 'error'): void
    {
        if ($message !== '') {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type']    = $type;
        }
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
