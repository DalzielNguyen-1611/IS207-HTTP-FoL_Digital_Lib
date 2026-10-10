<?php
/**
 * Master RESTful API Router
 * Định tuyến toàn bộ yêu cầu API trong hệ thống TechCare
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../controllers/DispatchController.php';
require_once __DIR__ . '/../controllers/TechnicianController.php';
require_once __DIR__ . '/../controllers/ReviewController.php';

Response::setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['route'] ?? $_SERVER['PATH_INFO'] ?? '';
$path = trim($path, '/');
$segments = explode('/', $path);

$resource = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? $segments[1] ?? null;

// Health check endpoint
if ($resource === 'health' || $path === '') {
    Response::json([
        'status' => 'OK',
        'service' => 'TechCare RESTful API Service (PHP OOP / PDO)',
        'architecture' => '3-Tier Architecture',
        'php_version' => PHP_VERSION,
        'database_connected' => Database::isConnected(),
        'endpoints' => [
            'auth' => ['POST /api/auth.php?action=login', 'POST /api/auth.php?action=register', 'GET /api/auth.php?action=profile'],
            'bookings' => ['GET /api/bookings.php', 'POST /api/bookings.php', 'GET /api/bookings.php?id={id}', 'PUT /api/bookings.php?id={id}&action=status'],
            'dispatch' => ['GET /api/dispatch.php?action=dashboard', 'POST /api/dispatch.php?action=assign', 'GET /api/dispatch.php?action=technicians-live'],
            'technicians' => ['GET /api/technicians.php?action=schedule', 'POST /api/technicians.php?action=complete&booking_id={id}', 'GET /api/technicians.php?action=wallet', 'POST /api/technicians.php?action=withdraw']
        ]
    ]);
}

// Router phân luồng
switch ($resource) {
    case 'auth':
        if ($action === 'login' && $method === 'POST') AuthController::login();
        if ($action === 'register' && $method === 'POST') AuthController::register();
        if ($action === 'profile' && $method === 'GET') AuthController::profile();
        break;

    case 'bookings':
        if ($method === 'GET') {
            if ($action && $action !== 'index') {
                BookingController::show($action);
            } else {
                BookingController::index();
            }
        } elseif ($method === 'POST') {
            BookingController::store();
        } elseif ($method === 'PUT') {
            BookingController::updateStatus($action);
        }
        break;

    case 'dispatch':
        if ($action === 'dashboard' && $method === 'GET') DispatchController::dashboard();
        if ($action === 'assign' && $method === 'POST') DispatchController::assign();
        if ($action === 'technicians-live' && $method === 'GET') DispatchController::liveTechnicians();
        break;

    case 'technicians':
        if ($action === 'schedule') TechnicianController::schedule($segments[2] ?? 'TECH-204');
        if ($action === 'complete') TechnicianController::completeJob($segments[2] ?? '');
        if ($action === 'wallet') TechnicianController::wallet($segments[2] ?? 'TECH-204');
        if ($action === 'withdraw') TechnicianController::withdraw($segments[2] ?? 'TECH-204');
        break;

    case 'reviews':
        if ($method === 'GET') {
            ReviewController::show($action ?: ($segments[1] ?? ''));
        } elseif ($method === 'POST') {
            ReviewController::store();
        }
        break;

    default:
        Response::error("Endpoint '$path' không tồn tại.", 404);
}
