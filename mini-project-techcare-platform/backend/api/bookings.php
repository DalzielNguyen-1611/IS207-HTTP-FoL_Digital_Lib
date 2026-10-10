<?php
/**
 * Direct Endpoint: /api/bookings.php
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../controllers/BookingController.php';

Response::setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($method === 'GET') {
    if ($id) {
        BookingController::show($id);
    } else {
        BookingController::index();
    }
} elseif ($method === 'POST') {
    BookingController::store();
} elseif ($method === 'PUT' || ($method === 'POST' && $action === 'status')) {
    if (!$id) Response::error('Thiếu mã id để cập nhật.', 400);
    BookingController::updateStatus($id);
} else {
    Response::error('Phương thức không được hỗ trợ.', 405);
}
