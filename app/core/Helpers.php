<?php

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function getFlash(): array
{
    $message = $_SESSION['flash_message'] ?? '';
    $type    = $_SESSION['flash_type']    ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return ['message' => $message, 'type' => $type];
}

function setFlash(string $message, string $type = 'info'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function storageUrl(string $relativePath): string
{
    return BASE_URL . '/serve.php?file=' . urlencode($relativePath);
}

function redirect(string $path, string $message = '', string $type = 'info'): void
{
    if ($message !== '') setFlash($message, $type);
    header('Location: ' . url($path));
    exit;
}

function paginate(int $total, int $perPage = ITEMS_PER_PAGE): array
{
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $total = max(0, $total);
    $pages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;
    $page  = min($page, max(1, $pages));

    return [
        'page'   => $page,
        'pages'  => $pages,
        'offset' => ($page - 1) * $perPage,
        'limit'  => $perPage,
        'total'  => $total,
    ];
}

function formatFileSize(int $bytes): string
{
    if ($bytes < 1024)       return $bytes . ' B';
    if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

function formatDateTime(string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (empty($datetime)) return '—';
    try {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Ho_Chi_Minh'));
        return $dt->format($format);
    } catch (Exception) {
        return '—';
    }
}

function timeAgo(string $datetime): string
{
    if (empty($datetime)) return '—';
    try {
        $now  = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $past = new DateTime($datetime, new DateTimeZone('Asia/Ho_Chi_Minh'));
        $diff = $now->getTimestamp() - $past->getTimestamp();

        if ($diff < 60)      return 'vừa xong';
        if ($diff < 3600)    return floor($diff / 60)   . ' phút trước';
        if ($diff < 86400)   return floor($diff / 3600) . ' giờ trước';
        if ($diff < 604800)  return floor($diff / 86400). ' ngày trước';
        return formatDateTime($datetime, 'd/m/Y');
    } catch (Exception) {
        return '—';
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        die('Yêu cầu không hợp lệ (CSRF). Vui lòng tải lại trang và thử lại.');
    }
}

function post(string $key, mixed $default = ''): mixed
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function get(string $key, mixed $default = ''): mixed
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function notEmpty(string $value): bool
{
    return trim($value) !== '';
}
