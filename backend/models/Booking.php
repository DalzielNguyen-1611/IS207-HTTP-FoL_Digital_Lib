<?php
/**
 * Booking Model
 * Quản lý phiếu yêu cầu dịch vụ TechCare Desk & TechCare Home
 * 100% dữ liệu từ cơ sở dữ liệu MySQL (techcare_db.bookings)
 */

require_once __DIR__ . '/../config/database.php';

class Booking {
    /**
     * Lấy danh sách phiếu đặt lịch từ CSDL kèm thông tin thợ phụ trách
     */
    public static function all($filters = []) {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $sql = "
                SELECT b.*, 
                       u.full_name as technician_name, 
                       t.code as technician_code,
                       u.phone as technician_phone,
                       r.id as review_id,
                       r.rating as review_rating,
                       r.comment as review_comment
                FROM bookings b
                LEFT JOIN technicians t ON b.assigned_technician_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN reviews r ON r.booking_id = b.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($filters['platform'])) {
                $sql .= " AND b.platform = ?";
                $params[] = $filters['platform'];
            }
            if (!empty($filters['status'])) {
                $sql .= " AND b.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['technician_id'])) {
                $sql .= " AND (b.assigned_technician_id = ? OR t.user_id = ? OR t.code = ?)";
                $params[] = $filters['technician_id'];
                $params[] = $filters['technician_id'];
                $params[] = $filters['technician_id'];
            }
            if (!empty($filters['customer_id']) && !empty($filters['customer_phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $filters['customer_phone']);
                $sql .= " AND (b.customer_id = ? OR REPLACE(REPLACE(REPLACE(b.customer_phone, ' ', ''), '-', ''), '.', '') = ?)";
                $params[] = $filters['customer_id'];
                $params[] = $cleanPhone;
            } elseif (!empty($filters['customer_id'])) {
                $sql .= " AND b.customer_id = ?";
                $params[] = $filters['customer_id'];
            } elseif (!empty($filters['customer_phone'])) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $filters['customer_phone']);
                $sql .= " AND (REPLACE(REPLACE(REPLACE(b.customer_phone, ' ', ''), '-', ''), '.', '') = ? OR b.customer_phone = ?)";
                $params[] = $cleanPhone;
                $params[] = $filters['customer_phone'];
            }
            if (!empty($filters['customer_name'])) {
                $sql .= " AND b.customer_name LIKE ?";
                $params[] = '%' . $filters['customer_name'] . '%';
            }

            $sql .= " ORDER BY b.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in Booking::all: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tìm chi tiết phiếu theo ID hoặc Mã Code (kèm bảng kê linh kiện & đánh giá)
     */
    public static function findByIdOrCode($idOrCode) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT b.*, 
                       u.full_name as technician_name, 
                       t.code as technician_code,
                       u.phone as technician_phone
                FROM bookings b
                LEFT JOIN technicians t ON b.assigned_technician_id = t.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE b.id = ? OR b.code = ?
                LIMIT 1
            ");
            $stmt->execute([$idOrCode, $idOrCode]);
            $booking = $stmt->fetch();

            if ($booking) {
                $partsStmt = $db->prepare("
                    SELECT id, part_name as name, quantity, unit_price as price, total_price 
                    FROM booking_parts 
                    WHERE booking_id = ?
                ");
                $partsStmt->execute([$booking['id']]);
                $booking['parts'] = $partsStmt->fetchAll();

                $revStmt = $db->prepare("
                    SELECT id, rating, comment, created_at 
                    FROM reviews 
                    WHERE booking_id = ? 
                    LIMIT 1
                ");
                $revStmt->execute([$booking['id']]);
                $review = $revStmt->fetch();
                $booking['review'] = $review ?: null;

                return $booking;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error in Booking::findByIdOrCode: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo phiếu đặt lịch mới trực tiếp vào MySQL
     */
    public static function create($data) {
        $db = Database::getConnection();
        if (!$db) {
            throw new Exception("Không thể kết nối cơ sở dữ liệu MySQL.");
        }

        $platform = $data['platform'] ?? 'TechCare Home';
        $prefix = ($platform === 'TechCare Desk') ? 'TC-DESK' : 'TC-HOME';

        // Đảm bảo không trùng ID hoặc Code
        do {
            $random = rand(1000, 9999);
            $id = 'BK-' . $random;
            $code = $prefix . '-' . $random;
            $chk = $db->prepare("SELECT id FROM bookings WHERE id = ? OR code = ? LIMIT 1");
            $chk->execute([$id, $code]);
        } while ($chk->fetch());

        $customerName = trim($data['customer_name'] ?? '');
        $rawPhone = trim($data['customer_phone'] ?? '');
        $cleanDigits = preg_replace('/[^0-9]/', '', $rawPhone);
        $customerPhone = !empty($cleanDigits) ? $cleanDigits : $rawPhone;
        $address = trim($data['address'] ?? '');
        $deviceType = trim($data['device_type'] ?? 'Thiết bị gia dụng');
        $issueDesc = trim($data['issue_description'] ?? '');
        $scheduledTime = $data['scheduled_time'] ?? 'Trong ngày';
        $priority = $data['priority'] ?? 'normal';
        $status = 'pending';
        $estimatedCost = floatval($data['estimated_cost'] ?? 250000);
        $customerId = !empty($data['customer_id']) ? trim($data['customer_id']) : null;
        $serviceId = !empty($data['service_id']) ? trim($data['service_id']) : null;

        try {
            $stmt = $db->prepare("
                INSERT INTO bookings 
                (id, code, platform, service_id, customer_id, customer_name, customer_phone, address, device_type, issue_description, scheduled_time, priority, status, estimated_cost, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $id,
                $code,
                $platform,
                $serviceId,
                $customerId,
                $customerName,
                $customerPhone,
                $address,
                $deviceType,
                $issueDesc,
                $scheduledTime,
                $priority,
                $status,
                $estimatedCost
            ]);

            return self::findByIdOrCode($id);
        } catch (Exception $e) {
            error_log("Error in Booking::create: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cập nhật thông tin phiếu trong MySQL và trả về bản ghi mới nhất
     */
    public static function update($idOrCode, $updateData) {
        $db = Database::getConnection();
        if (!$db) return null;

        $allowedFields = [
            'platform', 'service_id', 'customer_id', 'customer_name', 'customer_phone', 
            'address', 'device_type', 'issue_description', 'scheduled_time', 'priority', 
            'status', 'assigned_technician_id', 'estimated_cost', 'actual_cost', 
            'technical_note', 'completed_at'
        ];

        try {
            $fields = [];
            $params = [];

            foreach ($updateData as $key => $val) {
                if (in_array($key, $allowedFields)) {
                    $fields[] = "$key = ?";
                    $params[] = $val;
                }
            }

            if (isset($updateData['status']) && $updateData['status'] === 'completed' && !isset($updateData['completed_at'])) {
                $fields[] = "completed_at = NOW()";
            }

            if (empty($fields)) {
                return self::findByIdOrCode($idOrCode);
            }

            $params[] = $idOrCode;
            $params[] = $idOrCode;
            $sql = "UPDATE bookings SET " . implode(", ", $fields) . " WHERE id = ? OR code = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            return self::findByIdOrCode($idOrCode);
        } catch (Exception $e) {
            error_log("Error in Booking::update: " . $e->getMessage());
            return null;
        }
    }
}
