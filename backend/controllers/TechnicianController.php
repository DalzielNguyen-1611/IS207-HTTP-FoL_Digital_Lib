<?php
/**
 * Technician Controller
 * Xử lý lịch trực, hoàn tất ca sửa chữa và ví thu nhập KTV
 * 100% dữ liệu từ cơ sở dữ liệu MySQL
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Technician.php';
require_once __DIR__ . '/../models/Wallet.php';

class TechnicianController {
    public static function schedule($techId = 'TECH-204') {
        $tech = Technician::findByIdOrCode($techId);
        $jobs = Booking::all(['technician_id' => $tech ? $tech['id'] : $techId]);
        $pendingJobs = Booking::all(['status' => 'pending']);

        Response::json([
            'success' => true,
            'data' => [
                'technician' => $tech,
                'schedule_date' => date('Y-m-d'),
                'jobs' => $jobs,
                'pending_jobs' => $pendingJobs
            ]
        ]);
    }

    public static function acceptJob($bookingId, $techId = 'TECH-204') {
        $tech = Technician::findByIdOrCode($techId);
        if (!$tech) {
            Response::error('Không tìm thấy thông tin kỹ thuật viên.', 404);
        }

        $booking = Booking::findByIdOrCode($bookingId);
        if (!$booking) {
            Response::error('Không tìm thấy ca trực.', 404);
        }

        if (!empty($booking['assigned_technician_id']) && $booking['assigned_technician_id'] !== $tech['id']) {
            Response::error('Ca trực này đã được phân công cho kỹ thuật viên khác.', 400);
        }

        $updated = Booking::update($bookingId, [
            'assigned_technician_id' => $tech['id'],
            'status' => 'assigned',
            'technical_note' => "KTV {$tech['full_name']} ({$tech['code']}) đã tiếp nhận ca từ danh sách chờ."
        ]);

        Technician::updateStatus($tech['id'], 'busy');

        Response::success("Tiếp nhận ca {$updated['code']} thành công! Ca trực đã được thêm vào danh sách công tác của bạn.", $updated);
    }

    public static function updateJobStatus($bookingId) {
        $data = Response::getBody();
        $status = $data['status'] ?? 'in_progress';
        $note = $data['technical_note'] ?? null;

        $updateData = ['status' => $status];
        if ($note) $updateData['technical_note'] = $note;

        $updated = Booking::update($bookingId, $updateData);
        if (!$updated) {
            Response::error('Không tìm thấy ca trực để cập nhật.', 404);
        }

        Response::success("Đã cập nhật trạng thái ca {$updated['code']} thành: $status", $updated);
    }

    public static function completeJob($bookingId) {
        $data = Response::getBody();
        $booking = Booking::findByIdOrCode($bookingId);

        if (!$booking) {
            Response::error('Không tìm thấy ca trực.', 404);
        }

        $updated = Booking::update($bookingId, [
            'status' => 'completed',
            'actual_cost' => $data['actual_cost'] ?? $booking['estimated_cost'],
            'technical_note' => $data['technical_note'] ?? 'Đã khắc phục hoàn tất và bàn giao khách kiểm tra.'
        ]);

        if (!empty($booking['assigned_technician_id'])) {
            Technician::updateStatus($booking['assigned_technician_id'], 'ready');
        }

        Response::success("Nghiệm thu hoàn tất ca {$updated['code']}! Hóa đơn điện tử đã sẵn sàng.", $updated);
    }

    public static function wallet($techId = 'TECH-204') {
        $wallet = Wallet::findByTechnicianId($techId);
        Response::json([
            'success' => true,
            'data' => $wallet
        ]);
    }

    public static function withdraw($techId = 'TECH-204') {
        $data = Response::getBody();
        $amount = (float)($data['amount'] ?? 0);

        if ($amount <= 0) {
            Response::error('Số tiền rút không hợp lệ.', 400);
        }

        try {
            $result = Wallet::withdraw($techId, $amount);
            Response::success("Tạo lệnh rút " . number_format($amount, 0, ',', '.') . " VNĐ thành công! Tiền sẽ về tài khoản sau 5-10 phút.", $result);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }
}
