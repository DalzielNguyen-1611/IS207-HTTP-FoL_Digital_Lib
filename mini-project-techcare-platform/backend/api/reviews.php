<?php
/**
 * Direct Endpoint: /api/reviews.php
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../controllers/ReviewController.php';

Response::setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];
$bookingId = $_GET['booking_id'] ?? $_GET['id'] ?? null;

if ($method === 'GET') {
    if (!$bookingId) {
        Response::error('Vui lòng cung cấp mã booking_id để tra cứu đánh giá.', 400);
    }
    ReviewController::show($bookingId);
} elseif ($method === 'POST') {
    ReviewController::store();
} else {
    Response::error('Phương thức không được hỗ trợ.', 405);
}
