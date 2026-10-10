<?php
/**
 * Wallet Model
 * Quản lý ví điện tử, số dư và lệnh rút tiền của KTV
 * 100% dữ liệu từ cơ sở dữ liệu MySQL (techcare_db.wallets & techcare_db.wallet_transactions)
 */

require_once __DIR__ . '/../config/database.php';

class Wallet {
    /**
     * Tìm thông tin ví theo technician_id (hoặc mã NV / User ID)
     */
    public static function findByTechnicianId($techId) {
        $db = Database::getConnection();
        if (!$db) return null;

        try {
            $stmt = $db->prepare("
                SELECT w.*, t.code as technician_code, u.full_name as technician_name 
                FROM wallets w
                JOIN technicians t ON w.technician_id = t.id
                JOIN users u ON t.user_id = u.id
                WHERE w.technician_id = ? OR t.code = ? OR t.user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$techId, $techId, $techId]);
            $wallet = $stmt->fetch();

            if ($wallet) {
                $txnStmt = $db->prepare("
                    SELECT id, type, title, amount, status, created_at 
                    FROM wallet_transactions 
                    WHERE wallet_id = ? 
                    ORDER BY created_at DESC
                ");
                $txnStmt->execute([$wallet['id']]);
                $wallet['transactions'] = $txnStmt->fetchAll();
                return $wallet;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error in Wallet::findByTechnicianId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo giao dịch rút tiền trực tiếp vào CSDL MySQL
     */
    public static function withdraw($techId, $amount) {
        $wallet = self::findByTechnicianId($techId);
        if (!$wallet) {
            throw new Exception("Không tìm thấy ví của kỹ thuật viên.");
        }
        if ($wallet['balance'] < $amount) {
            throw new Exception("Số dư khả dụng (" . number_format($wallet['balance'], 0, ',', '.') . " VNĐ) không đủ để rút.");
        }

        $db = Database::getConnection();
        if (!$db) {
            throw new Exception("Không thể kết nối cơ sở dữ liệu MySQL.");
        }

        $newTxnId = 'TXN-' . time() . '-' . rand(100, 999);
        $title = "Rút tiền về tài khoản ngân hàng ({$wallet['bank_name']} - " . substr($wallet['account_number'], -4) . ")";

        try {
            $db->beginTransaction();

            $updateStmt = $db->prepare("UPDATE wallets SET balance = balance - ? WHERE id = ?");
            $updateStmt->execute([$amount, $wallet['id']]);

            $txnStmt = $db->prepare("
                INSERT INTO wallet_transactions (id, wallet_id, type, title, amount, status, created_at) 
                VALUES (?, ?, 'withdraw', ?, ?, 'pending', NOW())
            ");
            $txnStmt->execute([$newTxnId, $wallet['id'], $title, -$amount]);

            $db->commit();

            return self::findByTechnicianId($techId);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error in Wallet::withdraw: " . $e->getMessage());
            throw $e;
        }
    }
}
