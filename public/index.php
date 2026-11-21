<?php

require_once __DIR__ . '/../app/bootstrap.php';

$module = preg_replace('/[^a-z0-9_-]/i', '', get('module', 'home'));
$action = preg_replace('/[^a-z0-9_-]/i', '', get('action', 'index'));

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

    $controllerMap = [
        'documents'   => 'DocumentController',
        'collections' => 'CollectionController',
        'questions'   => 'QuestionController',
        'community'   => 'CommunityController',
        'auth'        => 'AuthController',
        'admin'       => 'AdminController',
        'home'        => 'HomeController',
        'serve'       => 'ServeController',
    ];
    $controllerClass = $controllerMap[$module] ?? (ucfirst($module) . 'Controller');
    if (!class_exists($controllerClass)) {
        $controllerClass = ucfirst($module) . 'Controller';
    }

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        $method = $action;
        if (method_exists($controller, $method)) {
            $controller->$method();
        } else {
            http_response_code(404);
            require_once __DIR__ . '/../app/views/layouts/404.php';
        }
    }
} else {

    http_response_code(404);
    require_once __DIR__ . '/../app/views/layouts/404.php';
}
