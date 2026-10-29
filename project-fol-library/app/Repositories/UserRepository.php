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

    public function findByGoogleSub(string $googleSub): ?array
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
             WHERE tk.`google_sub` = :sub
             LIMIT 1'
        );
        $stmt->execute([':sub' => $googleSub]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateGoogleSub(int $userId, string $googleSub): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `tai_khoan`
             SET `google_sub` = :sub, `updated_at` = NOW()
             WHERE `id_tai_khoan` = :id'
        );

        return $stmt->execute([
            ':sub' => $googleSub,
            ':id' => $userId
        ]);
    }

    public function createFromGoogle(array $profile, int $roleId = 3): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO `tai_khoan` (`id_vai_tro`, `email`, `mat_khau`, `google_sub`, `trang_thai`, `created_at`, `updated_at`)
             VALUES (:role_id, :email, NULL, :sub, "HoatDong", NOW(), NOW())'
        );
        $stmt->execute([
            ':role_id' => $roleId,
            ':email' => $profile['email'],
            ':sub' => $profile['sub']
        ]);
        $userId = (int)$this->db->lastInsertId();

        $name = trim((string)($profile['name'] ?? explode('@', $profile['email'])[0]));
        $picture = (string)($profile['picture'] ?? '/assets/images/logo.png');
        $prefix = ($roleId === 1) ? 'AD' : 'SV';
        $mssv = $prefix . str_pad((string)$userId, 5, '0', STR_PAD_LEFT);

        $profileStmt = $this->db->prepare(
            'INSERT INTO `ho_so_nguoi_dung` (`id_tai_khoan`, `mssv`, `ho_ten`, `id_lop`, `gioi_tinh`, `ngay_sinh`, `so_dien_thoai`, `anh_dai_dien`)
             VALUES (:id, :mssv, :name, 1, "Nam", "2005-01-01", "", :avatar)'
        );
        $profileStmt->execute([
            ':id' => $userId,
            ':mssv' => $mssv,
            ':name' => $name,
            ':avatar' => $picture
        ]);

        return $userId;
    }

    /**
     * Lấy toàn bộ danh sách tài khoản kèm vai trò và hồ sơ
     */
    public function getAllUsers(?string $role = null): array
    {
        $sql = 'SELECT 
                    tk.`id_tai_khoan`, tk.`id_vai_tro`, tk.`email`,
                    tk.`trang_thai`, tk.`created_at`, tk.`updated_at`,
                    vt.`ma_vai_tro`, vt.`ten_vai_tro`,
                    COALESCE(hs.`mssv`, CONCAT("ND", LPAD(tk.`id_tai_khoan`, 4, "0"))) AS `code`,
                    COALESCE(hs.`ho_ten`, tk.`email`) AS `name`,
                    hs.`so_dien_thoai`, hs.`anh_dai_dien`,
                    CASE 
                        WHEN vt.`ma_vai_tro` = "ADMIN" THEN "admin"
                        WHEN vt.`ma_vai_tro` = "LIBRARIAN" THEN "librarian"
                        ELSE "student"
                    END AS `role_slug`,
                    CASE 
                        WHEN tk.`trang_thai` = "HoatDong" THEN "active"
                        ELSE "suspended"
                    END AS `status_slug`
                FROM `tai_khoan` tk
                INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
                LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`';

        $params = [];
        if ($role !== null && $role !== '') {
            $sql .= ' WHERE vt.`ma_vai_tro` = :role';
            $params[':role'] = strtoupper($role);
        }

        $sql .= ' ORDER BY tk.`id_tai_khoan` ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lấy danh sách bạn đọc sinh viên phục vụ mượn sách tại quầy
     */
    public function getStudents(): array
    {
        $sql = 'SELECT 
                    hs.`mssv`,
                    hs.`ho_ten` AS `name`,
                    tk.`id_tai_khoan`,
                    tk.`email`
                FROM `tai_khoan` tk
                INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
                INNER JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                WHERE vt.`ma_vai_tro` = "STUDENT" AND tk.`trang_thai` = "HoatDong"
                ORDER BY hs.`mssv` ASC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

