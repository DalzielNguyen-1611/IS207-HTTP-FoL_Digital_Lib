<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Autoloader độc lập chuẩn PSR-4
 * Cho phép hệ thống tự nạp các class trong namespace App\ mà không bắt buộc Composer
 */
class Autoloader
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        spl_autoload_register(function (string $class): void {
            $prefix = 'App\\';
            $baseDir = dirname(__DIR__) . '/';

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });

        self::$registered = true;
    }
}
