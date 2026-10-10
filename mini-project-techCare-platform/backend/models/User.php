<?php
/**
 * User Model
 * Quản lý người dùng: Dispatcher, Technician, Customer
 * 100% dữ liệu từ cơ sở dữ liệu MySQL (techcare_db.users)
 */

require_once __DIR__ . '/../config/database.php';

class User {
    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];
        try {
            $stmt = $db->query("SELECT id, username, email, full_name, role, phone, status, created_at FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in User::all: " . $e->getMessage());
            return [];
        }
    }

    public static function findById($id) {
        $db = Database::getConnection();
        if (!$db) return null;
        try {
            $stmt = $db->prepare("SELECT id, username, email, full_name, role, phone, status, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Error in User::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tìm user theo Username, Email hoặc Số điện thoại từ CSDL
     */
    public static function findByLogin($login) {
        $cleanLogin = trim($login);
        $phoneClean = preg_replace('/[^0-9]/', '', $cleanLogin);

        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT id, username, email, password_hash, full_name, role, phone, status 
                FROM users 
                WHERE username = ? 
                   OR email = ? 
                   OR phone = ? 
                   OR (phone IS NOT NULL AND REPLACE(phone, ' ', '') = ?)
                LIMIT 1
            ");
            $stmt->execute([$cleanLogin, $cleanLogin, $cleanLogin, $phoneClean]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (Exception $e) {
            error_log("Error in User::findByLogin: " . $e->getMessage());
            return null;
        }
    }

    public static function findByUsernameOrEmail($login) {
        return self::findByLogin($login);
    }

    /**
     * Tạo tài khoản mới trực tiếp trong CSDL MySQL với mật khẩu Bcrypt
     */
    public static function create($data) {
        $id = 'USR-' . time() . '-' . rand(100, 999);
        $plainPassword = (string)$data['password'];
        $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
        
        $newUser = [
            'id' => $id,
            'username' => trim($data['username']),
            'email' => trim($data['email']),
            'password_hash' => $hashedPassword,
            'full_name' => trim($data['full_name']),
            'role' => $data['role'] ?? 'customer',
            'phone' => trim($data['phone'] ?? ''),
            'status' => 'active'
        ];

        $db = Database::getConnection();
        if (!$db) {
            throw new Exception("Không thể kết nối cơ sở dữ liệu MySQL.");
        }

        try {
            $stmt = $db->prepare("
                INSERT INTO users (id, username, email, password_hash, full_name, role, phone, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $newUser['id'],
                $newUser['username'],
                $newUser['email'],
                $newUser['password_hash'],
                $newUser['full_name'],
                $newUser['role'],
                $newUser['phone'],
                $newUser['status']
            ]);
            return $newUser;
        } catch (Exception $e) {
            error_log("Error in User::create: " . $e->getMessage());
            throw $e;
        }
    }
}
