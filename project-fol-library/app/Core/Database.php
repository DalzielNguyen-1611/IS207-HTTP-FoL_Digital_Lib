<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Quản lý kết nối Cơ sở dữ liệu (Singleton PDO)
 * Đọc cấu hình từ config/database.php, sử dụng Prepared Statements an toàn
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $configPath = dirname(__DIR__, 2) . '/config/database.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException("Tập tin cấu hình database không tồn tại: {$configPath}");
        }

        $config = require $configPath;
        $default = $config['default'] ?? 'mysql';
        $connectionConfig = $config['connections'][$default] ?? null;

        if ($connectionConfig === null) {
            throw new RuntimeException("Cấu hình kết nối '{$default}' không hợp lệ.");
        }

        $host = $connectionConfig['host'] ?? '127.0.0.1';
        $port = $connectionConfig['port'] ?? 3306;
        $database = $connectionConfig['database'] ?? 'fol_library_db';
        $username = $connectionConfig['username'] ?? 'root';
        $password = $connectionConfig['password'] ?? '';
        $charset = $connectionConfig['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = $connectionConfig['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            self::$instance = new PDO($dsn, $username, $password, $options);
            return self::$instance;
        } catch (PDOException $e) {
            throw new RuntimeException("Kết nối cơ sở dữ liệu thất bại: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public static function setConnection(?PDO $pdo): void
    {
        self::$instance = $pdo;
    }
}
