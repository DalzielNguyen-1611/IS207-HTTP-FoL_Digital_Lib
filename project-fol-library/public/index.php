<?php

/**
 * Điểm truy cập Web công khai (Front Controller)
 * Điều hướng hiển thị các trang giao diện Mock data UI chuẩn MVC
 */

$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rtrim($rawUri, '/');
if ($uri === '') {
    $uri = '/';
}
$page = $_GET['page'] ?? '';

if ($uri === '/collections' || $uri === '/community-list' || $page === 'collections' || $page === 'community-list') {
    $viewPath = dirname(__DIR__) . '/resources/views/collections/index.php';
} elseif ($uri === '/contact' || $uri === '/info-contact' || $uri === '/info/contact' || $page === 'contact' || $page === 'info-contact') {
    $viewPath = dirname(__DIR__) . '/resources/views/contact/index.php';
} else {
    $viewPath = dirname(__DIR__) . '/resources/views/home/index.php';
}

if (file_exists($viewPath)) {
    require_once $viewPath;
} else {
    http_response_code(404);
    echo "<h1>404 - Không tìm thấy trang</h1>";
}
