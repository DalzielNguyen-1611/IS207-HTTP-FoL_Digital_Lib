<?php

declare(strict_types=1);

// Chuẩn hóa đường dẫn yêu cầu.
$requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$path = '/' . trim(
    is_string($requestPath) ? $requestPath : '/',
    '/'
);

// Phục vụ tài nguyên tĩnh trong public/assets khi chạy PHP server.
if (PHP_SAPI === 'cli-server') {
    $assetRoot = realpath(__DIR__ . '/assets');
    $assetPath = realpath(__DIR__ . rawurldecode($path));

    if (
        $assetRoot !== false
        && $assetPath !== false
        && str_starts_with(
            $assetPath,
            $assetRoot . DIRECTORY_SEPARATOR
        )
        && is_file($assetPath)
    ) {
        return false;
    }
}

// Các  route giao diện được phép truy cập
$routes = [
    '/' => dirname(__DIR__) . '/resources/views/home/index.php',
    '/books' => dirname(__DIR__) . '/resources/views/books/index.php',
    '/book-detail' => dirname(__DIR__) . '/resources/views/book-detail/index.php',
    '/login' => dirname(__DIR__) . '/resources/views/login/index.php',
    '/register' => dirname(__DIR__) . '/resources/views/register/index.php',
    '/reading-room' => dirname(__DIR__) . '/resources/views/reading-room/index.php',
    '/profile' => dirname(__DIR__) . '/resources/views/profile/index.php',
    '/librarian' => dirname(__DIR__) . '/resources/views/librarian/index.php',
    '/librarian/books' => dirname(__DIR__) . '/resources/views/librarian-books/index.php',
    '/librarian/counter' => dirname(__DIR__) . '/resources/views/librarian-counter/index.php',
    '/librarian/rooms' => dirname(__DIR__) . '/resources/views/librarian-rooms/index.php',
    '/librarian/reports' => dirname(__DIR__) . '/resources/views/librarian-reports/index.php',
    '/admin/users' => dirname(__DIR__) . '/resources/views/admin-users/index.php',
];

$view = $routes[$path] ?? null;

header('Content-Type: text/html; charset=UTF-8');

// Thông báo thân thiện khi route hoặc View không tồn tại.
if ($view === null || !is_file($view)) {
    http_response_code(404);

    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Không tìm thấy trang</title>
    </head>
    <body>
        <!-- Thông báo trang không tồn tại -->
        <h1>Không tìm thấy trang</h1>
        <p>Đường dẫn này chưa có trong thư viện.</p>
        <a href="/">Về trang chủ</a>
    </body>
    </html>';

    exit;
}

// Hiển thị View của trang được yêu cầu.
require $view;