<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Lớp điều tiết lưu lượng (Rate Limiter) bảo vệ endpoint khỏi Brute Force / DoS
 * Hỗ trợ Sliding Window / Fixed Decay theo IP hoặc ID tài khoản
 */
class RateLimiter
{
    private static ?string $cacheDir = null;

    private static function getStorageDir(): string
    {
        if (self::$cacheDir === null) {
            self::$cacheDir = dirname(__DIR__, 2) . '/storage/cache/ratelimit';
            if (!is_dir(self::$cacheDir)) {
                @mkdir(self::$cacheDir, 0755, true);
            }
        }
        return self::$cacheDir;
    }

    private static function getFilePath(string $key): string
    {
        $hash = hash('sha256', $key);
        return self::getStorageDir() . '/' . $hash . '.json';
    }

    /**
     * Ghi nhận một lần gọi (hit) và trả về số lần đã gọi trong chu kỳ decay
     */
    public static function hit(string $key, int $decaySeconds = 60): int
    {
        $filePath = self::getFilePath($key);
        $now = time();
        $data = ['hits' => 0, 'expires_at' => $now + $decaySeconds];

        if (file_exists($filePath)) {
            $content = @file_get_contents($filePath);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['expires_at']) && $decoded['expires_at'] > $now) {
                    $data = $decoded;
                }
            }
        }

        $data['hits'] = ($data['hits'] ?? 0) + 1;
        if (!isset($data['expires_at']) || $data['expires_at'] <= $now) {
            $data['expires_at'] = $now + $decaySeconds;
        }

        @file_put_contents($filePath, json_encode($data), LOCK_EX);
        return (int)$data['hits'];
    }

    /**
     * Kiểm tra xem key đã vượt quá ngưỡng gọi tối đa chưa
     */
    public static function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $filePath = self::getFilePath($key);
        if (!file_exists($filePath)) {
            return false;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires_at'])) {
            return false;
        }

        if ($data['expires_at'] <= time()) {
            @unlink($filePath);
            return false;
        }

        return ($data['hits'] ?? 0) >= $maxAttempts;
    }

    /**
     * Lấy số giây còn lại cho đến khi bộ đếm được đặt lại
     */
    public static function availableIn(string $key): int
    {
        $filePath = self::getFilePath($key);
        if (!file_exists($filePath)) {
            return 0;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            return 0;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expires_at'])) {
            return 0;
        }

        $diff = (int)$data['expires_at'] - time();
        return max(0, $diff);
    }

    /**
     * Xóa bộ đếm cho một key (ví dụ khi đăng nhập thành công)
     */
    public static function clear(string $key): void
    {
        $filePath = self::getFilePath($key);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    /**
     * Lấy địa chỉ IP an toàn của client
     */
    public static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $first = trim($ips[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                $ip = $first;
            }
        }
        return $ip;
    }
}
