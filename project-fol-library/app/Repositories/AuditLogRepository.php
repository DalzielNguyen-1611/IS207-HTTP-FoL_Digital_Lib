<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn Nhật ký hoạt động & Kiểm toán (Audit Log Repository)
 * 100% Prepared Statements qua PDO
 */
class AuditLogRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function log(
        ?int $userId,
        string $action,
        string $targetTable,
        ?string $targetId = null,
        ?string $description = null,
        ?string $ipAddress = null
    ): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO `nhat_ky_hoat_dong`
             (`id_nguoi_dung`, `hanh_dong`, `doi_tuong`, `id_doi_tuong`, `mo_ta`, `dia_chi_ip`, `thoi_gian`)
             VALUES (:user_id, :action, :target_table, :target_id, :description, :ip, NOW())'
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':target_table' => $targetTable,
            ':target_id' => $targetId,
            ':description' => $description,
            ':ip' => $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')
        ]);
    }

    public function getLatest(int $limit = 50, ?int $userId = null): array
    {
        $sql = 'SELECT 
                    nk.`id`, nk.`id_nguoi_dung`, nk.`hanh_dong`, nk.`doi_tuong`,
                    nk.`id_doi_tuong`, nk.`mo_ta`, nk.`dia_chi_ip`, nk.`thoi_gian`,
                    tk.`email`, hs.`ho_ten`
                FROM `nhat_ky_hoat_dong` nk
                LEFT JOIN `tai_khoan` tk ON nk.`id_nguoi_dung` = tk.`id_tai_khoan`
                LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`';

        $params = [];
        if ($userId !== null) {
            $sql .= ' WHERE nk.`id_nguoi_dung` = :user_id';
            $params[':user_id'] = $userId;
        }

        $sql .= ' ORDER BY nk.`thoi_gian` DESC LIMIT ' . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
