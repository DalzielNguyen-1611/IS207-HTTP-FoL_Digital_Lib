<?php
/**
 * Review Controller
 * Tiếp nhận và xử lý đánh giá từ khách hàng
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/AuthHelper.php';
require_once __DIR__ . '/../models/Review.php';

class ReviewController {
    public static function show($bookingId) {
        $review = Review::findByBookingId($bookingId);
        if (!$review) {
            Response::json(['success' => false, 'message' => 'Chưa có đánh giá cho đơn này.'], 404);
            return;
        }
        Response::json(['success' => true, 'data' => $review]);
    }

    public static function store() {
        $data = Response::getBody();
        if (empty($data['booking_id'])) {
            Response::error('Thiếu mã booking_id để lưu đánh giá.', 400);
            return;
        }
        if (!isset($data['rating'])) {
            Response::error('Vui lòng chọn mức điểm đánh giá (1-5 sao).', 400);
            return;
        }

        // Tự động gán ID khách hàng nếu có phiên đăng nhập
        $token = AuthHelper::getTokenFromRequest();
        if ($token) {
            $verify = AuthHelper::verifyToken($token);
            if ($verify['valid']) {
                $data['customer_id'] = $verify['payload']['user_id'];
            }
        }

        try {
            $review = Review::saveOrUpdate($data);
            Response::success('Đã lưu đánh giá & kích hoạt bảo hành điện tử thành công!', $review);
        } catch (Exception $e) {
            Response::error('Lỗi khi lưu đánh giá: ' . $e->getMessage(), 500);
        }
    }
}
