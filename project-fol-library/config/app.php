<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'FoL Digital Library',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Ho_Chi_Minh',

    // Paths
    'base_path' => dirname(__DIR__),
    'app_path' => dirname(__DIR__) . '/app',
    'view_path' => dirname(__DIR__) . '/resources/views',
    'storage_path' => dirname(__DIR__) . '/storage',
    'upload_path' => dirname(__DIR__) . '/storage/uploads',
    'log_path' => dirname(__DIR__) . '/storage/logs',

    // Security & Session
    'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
    'session_cookie_name' => $_ENV['SESSION_COOKIE_NAME'] ?? 'fol_lib_session',
    'app_key' => $_ENV['APP_KEY'] ?? 'fol_secret_key_2026',
];
