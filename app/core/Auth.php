<?php

class Auth
{

    private const KEY_USER_ID   = 'auth_user_id';
    private const KEY_ROLE      = 'auth_role';
    private const KEY_STATUS    = 'auth_status';
    private const KEY_FULL_NAME = 'auth_full_name';
    private const KEY_AVATAR    = 'auth_avatar';
    private const KEY_EMAIL     = 'auth_email';

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    public static function login(array $user): void
    {
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
        $_SESSION[self::KEY_USER_ID]   = (int) $user['id'];
        $_SESSION[self::KEY_ROLE]      = $user['role'];
        $_SESSION[self::KEY_STATUS]    = $user['status'];
        $_SESSION[self::KEY_FULL_NAME] = $user['full_name'];
        $_SESSION[self::KEY_AVATAR]    = $user['avatar_path'] ?? null;
        $_SESSION[self::KEY_EMAIL]     = $user['email'] ?? null;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function refreshStatus(string $status): void
    {
        $_SESSION[self::KEY_STATUS] = $status;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::KEY_USER_ID]);
    }

    public static function isActive(): bool
    {
        return self::check() && $_SESSION[self::KEY_STATUS] === 'active';
    }

    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION[self::KEY_ROLE] === 'admin';
    }

    public static function isUser(): bool
    {
        return self::check() && $_SESSION[self::KEY_ROLE] === 'user';
    }

    public static function id(): ?int
    {
        return $_SESSION[self::KEY_USER_ID] ?? null;
    }

    public static function owns(int $ownerId): bool
    {
        $currentId = self::id();
        return $currentId !== null && $currentId === $ownerId;
    }

    public static function currentUser(): ?array
    {
        if (!self::check()) return null;
        $avatar = $_SESSION[self::KEY_AVATAR] ?? null;
        return [
            'id'        => $_SESSION[self::KEY_USER_ID],
            'full_name' => $_SESSION[self::KEY_FULL_NAME],
            'role'      => $_SESSION[self::KEY_ROLE],
            'status'    => $_SESSION[self::KEY_STATUS],
            'avatar'    => $avatar ? storageUrl($avatar) : null,
            'email'     => $_SESSION[self::KEY_EMAIL] ?? null,
        ];
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::redirect('/?module=auth&action=login', 'Vui lòng đăng nhập để tiếp tục.');
        }
    }

    public static function requireActiveUser(): void
    {
        self::requireLogin();
        if ($_SESSION[self::KEY_STATUS] === 'locked') {
            self::logout();
            self::redirect('/?module=auth&action=login', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            self::redirect('/', 'Bạn không có quyền truy cập trang này.');
        }
    }

    public static function requireRole(string $role): void
    {
        if ($role === 'admin') {
            self::requireAdmin();
        } else {
            self::requireActiveUser();
        }
    }

    public static function requireOwnerOrAdmin(int $ownerId): void
    {
        self::requireLogin();
        if (!self::owns($ownerId) && !self::isAdmin()) {
            http_response_code(403);
            self::redirect('/', 'Bạn không có quyền thực hiện thao tác này.');
        }
    }

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
