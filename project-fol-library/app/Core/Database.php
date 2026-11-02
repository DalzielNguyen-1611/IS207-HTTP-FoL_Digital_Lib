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
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+07:00'"
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

    public static function getInstance(): PDO
    {
        return self::getConnection();
    }

    /**
     * Thực thi một closure bên trong database transaction với cơ chế tự động thử lại khi gặp Deadlock
     * Mã lỗi MariaDB/MySQL: 1213 (Deadlock) hoặc SQLSTATE 40001
     *
     * @param callable $callback function(PDO $db): mixed
     * @param int $maxRetries Số lần thử lại tối đa khi gặp deadlock (mặc định 3)
     * @return mixed Kết quả trả về từ callback
     * @throws \Throwable
     */
    public static function transactionWithRetry(callable $callback, int $maxRetries = 3): mixed
    {
        $db = self::getConnection();
        $attempts = 0;

        while ($attempts < $maxRetries) {
            $attempts++;
            try {
                $db->beginTransaction();
                $result = $callback($db);
                $db->commit();
                return $result;
            } catch (PDOException $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }

                $errorCode = (int)($e->errorInfo[1] ?? 0);
                $sqlState = (string)($e->errorInfo[0] ?? $e->getCode());
                $excCode = (int)$e->getCode();

                // 1213: Deadlock found when trying to get lock
                // 1205: Lock wait timeout exceeded
                // 40001: Serialization failure / deadlock
                $isDeadlock = ($errorCode === 1213 || $errorCode === 1205 || $sqlState === '40001' || $excCode === 1213 || $excCode === 1205);

                if ($isDeadlock && $attempts < $maxRetries) {
                    // Exponential backoff với jitter nhẹ: 50ms, 100ms, 200ms...
                    $delayMicros = (int)((50000 * (2 ** ($attempts - 1))) + random_int(1000, 20000));
                    usleep($delayMicros);
                    continue;
                }

                throw $e;
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }
        }

        throw new RuntimeException("Thực thi giao dịch thất bại sau {$maxRetries} lần thử.");
    }
}
