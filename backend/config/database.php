<?php
/**
 * Database Connection Manager (PDO)
 * Tầng kết nối cơ sở dữ liệu (Data Tier Adapter)
 */

class Database {
    private static $host = 'localhost';
    private static $db_name = 'techcare_db';
    private static $username = 'root';
    private static $password = '';
    private static $port = '3306';
    private static $conn = null;
    private static $isConnected = false;

    /**
     * Khởi tạo kết nối PDO Singleton
     */
    public static function getConnection() {
        if (self::$conn !== null) {
            return self::$conn;
        }

        try {
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            self::$conn = new PDO($dsn, self::$username, self::$password, $options);
            self::$isConnected = true;
            return self::$conn;
        } catch (PDOException $e) {
            // Không ngắt hệ thống nếu chưa bật MySQL, cho phép model fallback về mock data
            self::$isConnected = false;
            return null;
        }
    }

    /**
     * Kiểm tra trạng thái kết nối
     */
    public static function isConnected() {
        if (self::$conn === null) {
            self::getConnection();
        }
        return self::$isConnected;
    }

    /**
     * Cấu hình lại thông số nếu cần
     */
    public static function configure($host, $db, $user, $pass, $port = '3306') {
        self::$host = $host;
        self::$db_name = $db;
        self::$username = $user;
        self::$password = $pass;
        self::$port = $port;
        self::$conn = null;
    }
}
