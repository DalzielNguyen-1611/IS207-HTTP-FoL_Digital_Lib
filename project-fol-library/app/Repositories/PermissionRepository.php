<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn dữ liệu Quyền Hạn (Permission Repository)
 * 100% Prepared Statements qua PDO
 */
class PermissionRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id_quyen`, `ma_quyen`, `ten_quyen`, `mo_ta`, `created_at`
             FROM `quyen`
             WHERE `ma_quyen` = :code
             LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT `id_quyen`, `ma_quyen`, `ten_quyen`, `mo_ta`, `created_at`
             FROM `quyen`
             ORDER BY `ma_quyen` ASC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách các mã quyền (ma_quyen) thuộc về một tài khoản
     */
    public function getPermissionCodesByUserId(int $userId): array
    {
        $sql = 'SELECT DISTINCT q.`ma_quyen`
                FROM `quyen` q
                INNER JOIN `vai_tro_quyen` vtq ON q.`id_quyen` = vtq.`id_quyen`
                INNER JOIN `vai_tro` vt ON vtq.`id_vai_tro` = vt.`id_vai_tro`
                INNER JOIN `tai_khoan` tk ON tk.`id_vai_tro` = vt.`id_vai_tro`
                WHERE tk.`id_tai_khoan` = :user_id
                  AND tk.`trang_thai` = "HoatDong"';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    /**
     * Lấy danh sách các mã quyền (ma_quyen) thuộc về một vai trò
     */
    public function getPermissionCodesByRoleId(int $roleId): array
    {
        $sql = 'SELECT q.`ma_quyen`
                FROM `quyen` q
                INNER JOIN `vai_tro_quyen` vtq ON q.`id_quyen` = vtq.`id_quyen`
                WHERE vtq.`id_vai_tro` = :role_id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':role_id' => $roleId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }
}
