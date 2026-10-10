<?php
/**
 * Direct Endpoint: /api/dispatch.php
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../controllers/DispatchController.php';

Response::setCorsHeaders();

// Kiểm soát phân quyền (RBAC): Chỉ Điều Phối Viên và Quản Trị Viên mới được truy cập
$currentUser = AuthHelper::requireRole(['dispatcher', 'admin']);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'dashboard';

if ($action === 'dashboard') {
    DispatchController::dashboard();
} elseif ($action === 'assign' && $method === 'POST') {
    DispatchController::assign();
} elseif ($action === 'technicians-live') {
    DispatchController::liveTechnicians();
} else {
    Response::error("Hành động '$action' không hợp lệ.", 400);
}
