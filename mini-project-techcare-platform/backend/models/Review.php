<?php
/**
 * Review Model
 * Quản lý đánh giá và nghiệm thu dịch vụ
 * 100% dữ liệu từ cơ sở dữ liệu MySQL (techcare_db.reviews)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Technician.php';

class Review {
    /**
     * Lấy đánh giá theo booking_id hoặc code
     */
    public static function findByBookingId($bookingIdOrCode) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT r.*, 
                       u.full_name as customer_name, 
                       u.phone as customer_phone,
                       b.code as booking_code,
                       b.device_type,
                       b.scheduled_time
                FROM reviews r
                JOIN bookings b ON r.booking_id = b.id
                LEFT JOIN users u ON r.customer_id = u.id
                WHERE r.booking_id = ? OR b.code = ?
                LIMIT 1
            ");
            $stmt->execute([$bookingIdOrCode, $bookingIdOrCode]);
            $review = $stmt->fetch();
            return $review ?: null;
        } catch (Exception $e) {
            error_log("Error in Review::findByBookingId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo mới hoặc cập nhật đánh giá
     */
    public static function saveOrUpdate($data) {
        $db = Database::getConnection();
        if (!$db) throw new Exception("Không thể kết nối CSDL MySQL.");

        $bookingId = trim($data['booking_id'] ?? '');
        if (!$bookingId) throw new Exception("Thiếu mã booking_id.");

        // Tìm thông tin booking để lấy technician_id và customer_id nếu chưa có
        $bStmt = $db->prepare("SELECT id, code, assigned_technician_id, customer_id FROM bookings WHERE id = ? OR code = ? LIMIT 1");
        $bStmt->execute([$bookingId, $bookingId]);
        $booking = $bStmt->fetch();

        if (!$booking) {
            throw new Exception("Không tìm thấy phiếu yêu cầu dịch vụ với mã: $bookingId");
        }

        $realBookingId = $booking['id'];
        $techId = !empty($data['technician_id']) ? $data['technician_id'] : $booking['assigned_technician_id'];
        $customerId = !empty($data['customer_id']) ? $data['customer_id'] : $booking['customer_id'];
        $rating = intval($data['rating'] ?? 5);
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        $comment = trim($data['comment'] ?? '');

        // Kiểm tra xem đã có đánh giá cho booking này chưa
        $chk = $db->prepare("SELECT id FROM reviews WHERE booking_id = ? LIMIT 1");
        $chk->execute([$realBookingId]);
        $existing = $chk->fetch();

        if ($existing) {
            $stmt = $db->prepare("
                UPDATE reviews 
                SET rating = ?, comment = ?, created_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$rating, $comment, $existing['id']]);
            $revId = $existing['id'];
        } else {
            $revId = 'REV-' . rand(1000, 9999);
            $stmt = $db->prepare("
                INSERT INTO reviews (id, booking_id, technician_id, customer_id, rating, comment, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$revId, $realBookingId, $techId, $customerId, $rating, $comment]);
        }

        // Cập nhật lại rating & review_count của KTV trong bảng technicians
        if ($techId) {
            Technician::recalculateRating($techId);
        }

        return self::findByBookingId($realBookingId);
    }
}
