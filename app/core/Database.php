<?php
/**
 * DATABASE.PHP — Lớp kết nối MySQL dùng chung
 *
 * Sử dụng PDO với prepared statements.
 * Tất cả truy vấn phải đi qua lớp này — không được viết
 * mysqli_connect() hay PDO riêng ở từng phân hệ.
 *
 * Cách dùng:
 *   $db = Database::getInstance();
 *   $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
 *   $stmt->execute([':id' => $id]);
 *   $row = $stmt->fetch();
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // Tải cấu hình database từ file local (không có trên Git)
        $localConfig = BASE_PATH . '/config/database.local.php';
        if (file_exists($localConfig)) {
            require_once $localConfig;
        } else {
            // Nếu chưa có file local, báo lỗi rõ ràng
            die('<b>Lỗi cấu hình:</b> Chưa có file <code>config/database.local.php</code>.<br>'
                . 'Sao chép từ <code>config/database.example.php</code> và điền thông tin database của bạn.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Không hiện thông tin kết nối khi production
            if (APP_ENV === 'development') {
                die('<b>Không thể kết nối database:</b> ' . $e->getMessage());
            } else {
                die('Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.');
            }
        }
    }

    /**
     * Lấy instance duy nhất (Singleton)
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Chuẩn bị câu truy vấn
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Chạy câu truy vấn đơn giản (không tham số)
     */
    public function query(string $sql): PDOStatement
    {
        return $this->pdo->query($sql);
    }

    /**
     * Lấy ID của bản ghi vừa INSERT
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Bắt đầu transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Xác nhận transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Hủy transaction
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Lấy đối tượng PDO gốc (dùng khi thực sự cần)
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // Ngăn clone và unserialize
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Không thể unserialize singleton."); }
}
