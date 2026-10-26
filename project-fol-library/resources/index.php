<?php

declare(strict_types=1);

//Chuẩn hóa đường dẫn yêu cầu
$requestPath = parse_url(
    $_SERVER['REQUEST_URL'] ?? '/',
    PHP_URL_PATH
);

$path = '/' . trim(
    is_string($requestPath) ? $requestPath : '/',
    '/'
);

//Phục vụ tài nguyên tĩnh trong public/assets khi chạy PHP server
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

//Ánh xạ các route được cho phép tới View
$routes = [
    '/' => dirname(__DIR__) . '/resources/views/home/index.php',
];

$view = $routes[$path] ?? null;

header('Content-Type: text/html; charset=UTF-8');

//Thông báo thân thiện khi route hoặc View không tồn tại
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

//Hiển thị View của trang được yêu cầu
require $view;