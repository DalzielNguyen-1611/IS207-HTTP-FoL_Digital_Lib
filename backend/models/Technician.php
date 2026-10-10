<?php
/**
 * Technician Model
 * Quản lý thông tin hồ sơ KTV hiện trường, trạng thái trực và chỉ số KPI
 * 100% dữ liệu từ cơ sở dữ liệu MySQL (techcare_db.technicians & techcare_db.users)
 */

require_once __DIR__ . '/../config/database.php';

class Technician {
    /**
     * Lấy toàn bộ danh sách KTV kèm họ tên, số điện thoại từ bảng users
     */
    public static function all() {
        $db = Database::getConnection();
        if (!$db) return [];

        try {
            $sql = "
                SELECT t.*, 
                       u.full_name, 
                       u.phone, 
                       u.email, 
                       u.avatar_url,
                       (SELECT COUNT(*) FROM bookings b WHERE b.assigned_technician_id = t.id AND DATE(b.created_at) = CURDATE()) as today_jobs
                FROM technicians t
                JOIN users u ON t.user_id = u.id
                ORDER BY t.rating DESC, t.code ASC
            ";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in Technician::all: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tìm hồ sơ KTV theo ID, Mã NV (ví dụ NV-204) hoặc User ID
     */
    public static function findByIdOrCode($idOrCode) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $sql = "
                SELECT t.*, 
                       u.full_name, 
                       u.phone, 
                       u.email, 
                       u.avatar_url,
                       (SELECT COUNT(*) FROM bookings b WHERE b.assigned_technician_id = t.id AND DATE(b.created_at) = CURDATE()) as today_jobs
                FROM technicians t
                JOIN users u ON t.user_id = u.id
                WHERE t.id = ? OR t.code = ? OR t.user_id = ?
                LIMIT 1
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$idOrCode, $idOrCode, $idOrCode]);
            $tech = $stmt->fetch();
            return $tech ?: null;
        } catch (Exception $e) {
            error_log("Error in Technician::findByIdOrCode: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tìm hồ sơ KTV theo User ID liên kết
     */
    public static function findByUserId($userId) {
        return self::findByIdOrCode($userId);
    }

    /**
     * Cập nhật trạng thái ca trực trong CSDL ('ready' | 'busy' | 'offline')
     */
    public static function updateStatus($idOrCode, $status) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                UPDATE technicians 
                SET shift_status = ? 
                WHERE id = ? OR code = ? OR user_id = ?
            ");
            $stmt->execute([$status, $idOrCode, $idOrCode, $idOrCode]);
            return self::findByIdOrCode($idOrCode);
        } catch (Exception $e) {
            error_log("Error in Technician::updateStatus: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tự động tính toán lại điểm rating trung bình & số lượt review từ CSDL
     */
    public static function recalculateRating($techId) {
        $db = Database::getConnection();
        if (!$db) return false;

        try {
            $stmt = $db->prepare("
                SELECT COUNT(*) as review_count, AVG(rating) as avg_rating 
                FROM reviews 
                WHERE technician_id = ?
            ");
            $stmt->execute([$techId]);
            $row = $stmt->fetch();

            if ($row && intval($row['review_count']) > 0) {
                $count = intval($row['review_count']);
                $avg = round(floatval($row['avg_rating']), 2);

                $upd = $db->prepare("UPDATE technicians SET rating = ?, review_count = ? WHERE id = ?");
                $upd->execute([$avg, $count, $techId]);
                return true;
            }
        } catch (Exception $e) {
            error_log("Error in Technician::recalculateRating: " . $e->getMessage());
        }
        return false;
    }
}

