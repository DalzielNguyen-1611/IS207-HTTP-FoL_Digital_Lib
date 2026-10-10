<?php
/**
 * Dispatch Controller
 * Xử lý nghiệp vụ trung tâm điều phối viên
 */

require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Technician.php';

class DispatchController {
    public static function dashboard() {
        $bookings = Booking::all();
        $technicians = Technician::all();

        $pending = array_filter($bookings, fn($b) => $b['status'] === 'pending');
        $inProgress = array_filter($bookings, fn($b) => in_array($b['status'], ['assigned', 'in_progress']));
        $readyTechs = array_filter($technicians, fn($t) => $t['shift_status'] === 'ready');

        Response::json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_tickets_today' => count($bookings),
                    'pending_count' => count($pending),
                    'in_progress_count' => count($inProgress),
                    'active_techs_count' => count($technicians),
                    'ready_techs_count' => count($readyTechs),
                    'sla_success_rate' => '94.2%'
                ],
                'pending_queue' => array_values($pending),
                'in_progress_tickets' => array_values($inProgress),
                'technicians' => $technicians
            ]
        ]);
    }

    public static function assign() {
        $data = Response::getBody();
        $bookingId = $data['booking_id'] ?? null;
        $techId = $data['technician_id'] ?? null;

        if (!$bookingId || !$techId) {
            Response::error('Vui lòng cung cấp mã đơn (booking_id) và mã kỹ thuật viên (technician_id).', 400);
        }

        $tech = Technician::findByIdOrCode($techId);
        if (!$tech) {
            Response::error('Không tìm thấy kỹ thuật viên này.', 404);
        }

        $updated = Booking::update($bookingId, [
            'status' => 'assigned',
            'assigned_technician_id' => $tech['id'],
            'technician_name' => "{$tech['full_name']} (#{$tech['code']})"
        ]);

        if (!$updated) {
            Response::error('Không tìm thấy phiếu yêu cầu.', 404);
        }

        Technician::updateStatus($tech['id'], 'busy');

        Response::success("Đã phân công thành công ca {$updated['code']} cho KTV {$tech['full_name']} (#{$tech['code']})", [
            'booking' => $updated,
            'technician' => $tech
        ]);
    }

    public static function liveTechnicians() {
        $technicians = Technician::all();
        Response::json([
            'success' => true,
            'total' => count($technicians),
            'data' => $technicians
        ]);
    }
}
