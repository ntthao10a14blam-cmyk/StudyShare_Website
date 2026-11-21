<?php

$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

if (!defined('BASE_URL')) {
    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = rtrim($scriptDir, '/');
        define('BASE_URL', $scheme . $_SERVER['HTTP_HOST'] . $scriptDir);
    } else {
        define('BASE_URL', 'http://localhost:8080/StudyShare_Website/public');
    }
}
define('BASE_PATH', dirname(__DIR__));

define('STORAGE_PATH', BASE_PATH . '/storage');
define('STORAGE_DOCUMENTS', STORAGE_PATH . '/documents');
define('STORAGE_AVATARS',   STORAGE_PATH . '/images/avatars');
define('STORAGE_DOC_IMAGES', STORAGE_PATH . '/images/document_images');
define('STORAGE_Q_IMAGES',   STORAGE_PATH . '/images/question_images');
define('STORAGE_TEMP',       STORAGE_PATH . '/temp');

define('MAX_PDF_SIZE',   100 * 1024 * 1024);
define('MAX_IMAGE_SIZE', 5  * 1024 * 1024);
define('MAX_AVATAR_SIZE', 2 * 1024 * 1024);
define('MAX_IMAGES_PER_DOC', 10);
define('MAX_IMAGES_PER_Q',   5);
define('ALLOWED_PDF_TYPES', ['application/pdf']);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

define('SESSION_LIFETIME', 3600 * 8);
define('SESSION_NAME', 'studyhub_session');

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('APP_ENV', 'development');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
