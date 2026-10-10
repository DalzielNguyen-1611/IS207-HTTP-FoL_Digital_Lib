<?php
/**
 * Direct Endpoint: /api/technicians.php
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../controllers/TechnicianController.php';

Response::setCorsHeaders();

// Kiểm soát phân quyền (RBAC): Chỉ Kỹ Thuật Viên, Điều Phối Viên và Admin mới được truy cập
$currentUser = AuthHelper::requireRole(['technician', 'dispatcher', 'admin']);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'schedule';

// Ràng buộc bảo mật: Nếu là KTV thì chỉ được thao tác trên tài khoản của chính mình
if ($currentUser['role'] === 'technician') {
    $techId = $currentUser['technician_id'] ?? 'TECH-204';
} else {
    $techId = $_GET['tech_id'] ?? ($currentUser['technician_id'] ?? 'TECH-204');
}

$bookingId = $_GET['booking_id'] ?? null;

if ($action === 'schedule') {
    TechnicianController::schedule($techId);
} elseif ($action === 'wallet') {
    TechnicianController::wallet($techId);
} elseif ($action === 'update-status' && $method === 'POST') {
    if (!$bookingId) Response::error('Thiếu mã booking_id để cập nhật trạng thái.', 400);
    TechnicianController::updateJobStatus($bookingId);
} elseif ($action === 'complete' && $method === 'POST') {
    if (!$bookingId) Response::error('Thiếu mã booking_id để nghiệm thu.', 400);
    TechnicianController::completeJob($bookingId);
} elseif ($action === 'accept' && $method === 'POST') {
    if (!$bookingId) Response::error('Thiếu mã booking_id để nhận ca.', 400);
    TechnicianController::acceptJob($bookingId, $techId);
} elseif ($action === 'shift-status' && $method === 'POST') {
    $data = Response::getBody();
    $status = $data['shift_status'] ?? 'ready';
    Technician::updateStatus($techId, $status);
    Response::success("Cập nhật trạng thái ca trực thành công: $status", ['shift_status' => $status]);
} elseif ($action === 'withdraw' && $method === 'POST') {
    TechnicianController::withdraw($techId);
} else {
    Response::error("Hành động '$action' không hợp lệ.", 400);
}
