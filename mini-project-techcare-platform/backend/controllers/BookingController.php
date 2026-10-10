<?php
/**
 * Booking Controller
 * Xử lý đơn đặt lịch & ticket cứu hộ
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/Booking.php';

class BookingController {
    public static function index() {
        $platform = $_GET['platform'] ?? null;
        $status = $_GET['status'] ?? null;

        $filters = [];
        if ($platform) $filters['platform'] = $platform;
        if ($status) $filters['status'] = $status;
        if (!empty($_GET['customer_id'])) $filters['customer_id'] = $_GET['customer_id'];
        if (!empty($_GET['customer_phone'])) $filters['customer_phone'] = $_GET['customer_phone'];
        if (!empty($_GET['customer_name'])) $filters['customer_name'] = $_GET['customer_name'];

        // Nếu người dùng gửi Token và là khách hàng, chỉ trả về đơn của chính khách hàng đó
        $token = AuthHelper::getTokenFromRequest();
        if ($token) {
            $verify = AuthHelper::verifyToken($token);
            if ($verify['valid'] && $verify['payload']['role'] === 'customer') {
                $filters['customer_id'] = $verify['payload']['user_id'];
                if (!empty($verify['payload']['phone'])) {
                    $filters['customer_phone'] = $verify['payload']['phone'];
                }
            }
        }

        $list = Booking::all($filters);
        Response::json([
            'success' => true,
            'total' => count($list),
            'data' => $list
        ]);
    }

    public static function show($id) {
        $booking = Booking::findByIdOrCode($id);
        if (!$booking) {
            Response::error("Không tìm thấy phiếu yêu cầu với mã: $id", 404);
        }
        Response::json([
            'success' => true,
            'data' => $booking
        ]);
    }

    public static function store() {
        $data = Response::getBody();

        if (empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['address'])) {
            Response::error('Vui lòng cung cấp tên khách hàng, số điện thoại và địa chỉ nhận dịch vụ.', 400);
        }

        // Tự động gắn ID người dùng nếu đã đăng nhập
        $token = AuthHelper::getTokenFromRequest();
        if ($token) {
            $verify = AuthHelper::verifyToken($token);
            if ($verify['valid']) {
                $data['customer_id'] = $verify['payload']['user_id'];
            }
        }

        try {
            $newBooking = Booking::create($data);
            Response::success('Đặt lịch dịch vụ thành công! Hệ thống đang điều phối kỹ thuật viên.', $newBooking, 201);
        } catch (Exception $e) {
            Response::error('Lỗi khi lưu đơn vào cơ sở dữ liệu: ' . $e->getMessage(), 500);
        }
    }

    public static function updateStatus($id) {
        // Chỉ Kỹ thuật viên, Điều phối viên hoặc Admin mới được phép đổi trạng thái ca trực
        $currentUser = AuthHelper::requireRole(['technician', 'dispatcher', 'admin']);

        $data = Response::getBody();
        $status = $data['status'] ?? null;

        if (!$status) {
            Response::error('Vui lòng cung cấp trạng thái mới.', 400);
        }

        $updated = Booking::update($id, ['status' => $status]);
        if (!$updated) {
            Response::error('Không tìm thấy phiếu yêu cầu để cập nhật.', 404);
        }

        Response::success("Cập nhật trạng thái phiếu {$updated['code']} thành công: $status", $updated);
    }
}
