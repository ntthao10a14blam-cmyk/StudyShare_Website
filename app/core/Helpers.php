<?php
/**
 * HELPERS.PHP — Các hàm tiện ích dùng chung
 *
 * Không import hay khai báo class — chỉ là các hàm toàn cục.
 * Gọi trực tiếp ở bất cứ đâu sau khi bootstrap đã require file này.
 */

// ============================================================
// OUTPUT & SECURITY
// ============================================================

/**
 * Escape HTML an toàn — dùng khi in dữ liệu từ DB ra view.
 * Thay thế echo $var bằng echo h($var).
 */
function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Lấy và xóa flash message khỏi session.
 * Dùng trong layout để hiển thị thông báo một lần.
 */
function getFlash(): array
{
    $message = $_SESSION['flash_message'] ?? '';
    $type    = $_SESSION['flash_type']    ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return ['message' => $message, 'type' => $type];
}

/**
 * Set flash message mà không redirect.
 */
function setFlash(string $message, string $type = 'info'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;
}

// ============================================================
// URL & REDIRECT
// ============================================================

/**
 * Tạo URL đầy đủ từ path tương đối.
 * Ví dụ: url('/documents/list') → http://localhost/study_website/public/documents/list
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Tạo URL tới file trong storage/ thông qua file-serve.php (có kiểm tra quyền).
 * KHÔNG dùng để trỏ thẳng vào storage/.
 */
function storageUrl(string $relativePath): string
{
    return BASE_URL . '/serve.php?file=' . urlencode($relativePath);
}

/**
 * Redirect và thoát.
 */
function redirect(string $path, string $message = '', string $type = 'info'): void
{
    if ($message !== '') setFlash($message, $type);
    header('Location: ' . url($path));
    exit;
}

// ============================================================
// PHÂN TRANG
// ============================================================

/**
 * Tính số trang và offset từ query string.
 *
 * @return array{page:int, offset:int, limit:int}
 */
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

// ============================================================
// ĐỊNH DẠNG
// ============================================================

/**
 * Định dạng kích thước file cho người dùng.
 */
function formatFileSize(int $bytes): string
{
    if ($bytes < 1024)       return $bytes . ' B';
    if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

/**
 * Định dạng ngày giờ tiếng Việt.
 * Ví dụ: '2025-06-01 14:30:00' → '01/06/2025 14:30'
 */
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

/**
 * Trả về thời gian tương đối.
 * Ví dụ: '3 phút trước', '2 ngày trước'
 */
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

// ============================================================
// CSRF (BẢO VỆ FORM)
// ============================================================

/**
 * Tạo và lưu CSRF token vào session.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * In hidden input chứa CSRF token — gọi trong mỗi form.
 */
function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

/**
 * Xác minh CSRF token từ POST request.
 * Gọi đầu mỗi xử lý POST — sẽ exit nếu không hợp lệ.
 */
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        die('Yêu cầu không hợp lệ (CSRF). Vui lòng tải lại trang và thử lại.');
    }
}

// ============================================================
// INPUT VALIDATION
// ============================================================

/**
 * Lấy giá trị POST, loại bỏ khoảng trắng thừa.
 */
function post(string $key, mixed $default = ''): mixed
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

/**
 * Lấy giá trị GET, loại bỏ khoảng trắng thừa.
 */
function get(string $key, mixed $default = ''): mixed
{
    return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

/**
 * Kiểm tra chuỗi không rỗng sau khi trim.
 */
function notEmpty(string $value): bool
{
    return trim($value) !== '';
}
