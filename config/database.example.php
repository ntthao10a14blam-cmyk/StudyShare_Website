<?php
/**
 * CẤU HÌNH DATABASE — FILE MẪU
 *
 * Sao chép file này thành database.local.php và điền thông tin thật.
 * KHÔNG đưa database.local.php lên Git.
 *
 * cp config/database.example.php config/database.local.php
 */

// Cài đặt kết nối MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'studyhub_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
