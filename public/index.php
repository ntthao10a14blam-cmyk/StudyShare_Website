<?php
/**
 * INDEX.PHP — Điểm vào duy nhất của website
 *
 * Toàn bộ request đều đi qua đây.
 * Chỉ thư mục public/ được phục vụ bởi web server.
 *
 * Routing đơn giản dựa theo query string:
 *   ?module=documents&action=list
 *   ?module=auth&action=login
 *
 * Hoặc dùng PATH_INFO nếu cấu hình Apache rewrite:
 *   /documents/list  →  module=documents, action=list
 */

// ============================================================
// BOOTSTRAP
// ============================================================
require_once __DIR__ . '/../app/bootstrap.php';

// ============================================================
// ROUTING ĐƠN GIẢN
// ============================================================

// Lấy module và action từ URL
$module = preg_replace('/[^a-z0-9_-]/i', '', get('module', 'home'));
$action = preg_replace('/[^a-z0-9_-]/i', '', get('action', 'index'));

// Ánh xạ module → file controller
$routes = [
    'home'        => __DIR__ . '/../app/modules/home/HomeController.php',
    'auth'        => __DIR__ . '/../app/modules/auth/AuthController.php',
    'documents'   => __DIR__ . '/../app/modules/documents/DocumentController.php',
    'community'   => __DIR__ . '/../app/modules/community/CommunityController.php',
    'collections' => __DIR__ . '/../app/modules/collections/CollectionController.php',
    'questions'   => __DIR__ . '/../app/modules/questions/QuestionController.php',
    'admin'       => __DIR__ . '/../app/modules/admin/AdminController.php',
    'serve'       => __DIR__ . '/../app/modules/documents/ServeController.php',
];

if (isset($routes[$module]) && file_exists($routes[$module])) {
    require_once $routes[$module];
    // Mỗi controller tự xử lý $action và render view
    $controllerClass = ucfirst($module === 'community' ? 'Community' : $module) . 'Controller';

    // Gọi hàm tương ứng action nếu class tồn tại
    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        $method = $action; // ví dụ: list, create, edit, delete
        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(404);
            require_once __DIR__ . '/../app/views/layouts/404.php';
        }
    }
} else {
    // Module không tồn tại
    http_response_code(404);
    require_once __DIR__ . '/../app/views/layouts/404.php';
}
