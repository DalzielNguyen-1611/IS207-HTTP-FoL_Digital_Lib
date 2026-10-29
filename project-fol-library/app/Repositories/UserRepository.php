<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn dữ liệu Tài khoản Người dùng (User Repository)
 * 100% Prepared Statements qua PDO
 */
class UserRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                tk.`id_tai_khoan`, tk.`id_vai_tro`, tk.`email`, tk.`google_sub`,
                tk.`trang_thai`, tk.`created_at`, tk.`updated_at`,
                vt.`ma_vai_tro`, vt.`ten_vai_tro`,
                hs.`ho_ten`, hs.`mssv`, hs.`so_dien_thoai`, hs.`anh_dai_dien`
             FROM `tai_khoan` tk
             INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
             LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
             WHERE tk.`id_tai_khoan` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT 
                tk.`id_tai_khoan`, tk.`id_vai_tro`, tk.`email`, tk.`mat_khau`,
                tk.`google_sub`, tk.`trang_thai`, tk.`created_at`, tk.`updated_at`,
                vt.`ma_vai_tro`, vt.`ten_vai_tro`,
                hs.`ho_ten`, hs.`mssv`, hs.`so_dien_thoai`, hs.`anh_dai_dien`
             FROM `tai_khoan` tk
             INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
             LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
             WHERE tk.`email` = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateRole(int $userId, int $roleId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `tai_khoan`
             SET `id_vai_tro` = :role_id, `updated_at` = NOW()
             WHERE `id_tai_khoan` = :user_id'
        );

        return $stmt->execute([
            ':role_id' => $roleId,
            ':user_id' => $userId
        ]);
    }

    public function updateStatus(int $userId, string $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `tai_khoan`
             SET `trang_thai` = :status, `updated_at` = NOW()
             WHERE `id_tai_khoan` = :user_id'
        );

        return $stmt->execute([
            ':status' => $status,
            ':user_id' => $userId
        ]);
    }
}
