<?php
/**
 * Direct Endpoint: /api/auth.php
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../controllers/AuthController.php';

Response::setCorsHeaders();

$action = $_GET['action'] ?? 'profile';
$method = $_SERVER['REQUEST_METHOD'];

if ($action === 'login' || ($method === 'POST' && $action !== 'register')) {
    AuthController::login();
} elseif ($action === 'register') {
    AuthController::register();
} elseif ($action === 'profile') {
    AuthController::profile();
} elseif ($action === 'verify') {
    AuthController::verify();
} else {
    Response::error("Hành động '$action' không hợp lệ.", 400);
}
