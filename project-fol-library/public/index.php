<?php

/**
 * Điểm truy cập Web công khai (Front Controller)
 * Điều hướng hiển thị các trang giao diện Mock data UI
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$page = $_GET['page'] ?? '';

if ($uri === '/collections' || $uri === '/community-list' || $page === 'collections' || $page === 'community-list') {
    $viewPath = dirname(__DIR__) . '/resources/views/collections/index.php';
} elseif ($uri === '/contact' || $uri === '/info/contact' || $page === 'contact' || $page === 'info-contact') {
    $viewPath = dirname(__DIR__) . '/resources/views/contact/index.php';
} else {
    $viewPath = dirname(__DIR__) . '/resources/views/home/index.php';
}

if (file_exists($viewPath)) {
    require_once $viewPath;
} else {
    echo "<h1>404 - View Not Found</h1>";
}
