<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Quản lý Session bảo mật theo chuẩn RBAC
 * Hỗ trợ HttpOnly, SameSite, tự động tái tạo session ID chống Session Fixation
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        session_set_cookie_params([
            'lifetime' => 7200, // 2 giờ
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('fol_lib_session');
        session_start();
        self::$started = true;
    }

    public static function writeClose(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
            self::$started = false;
        }
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }

    public static function setAuthUser(array $userData): void
    {
        self::start();
        self::regenerate();
        $_SESSION['user'] = $userData;
        $_SESSION['authenticated_at'] = time();
    }

    public static function getAuthUser(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function getUserId(): ?int
    {
        $user = self::getAuthUser();
        return isset($user['id_tai_khoan']) ? (int)$user['id_tai_khoan'] : null;
    }

    public static function getRoleId(): ?int
    {
        $user = self::getAuthUser();
        return isset($user['id_vai_tro']) ? (int)$user['id_vai_tro'] : null;
    }

    public static function getRoleCode(): ?string
    {
        $user = self::getAuthUser();
        return $user['ma_vai_tro'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return self::getUserId() !== null;
    }
}
