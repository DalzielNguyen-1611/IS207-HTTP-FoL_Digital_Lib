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
                hs.`ho_ten`, hs.`mssv`, hs.`id_lop`, hs.`gioi_tinh`, hs.`ngay_sinh`,
                hs.`so_dien_thoai`, hs.`anh_dai_dien`, hs.`anh_the_sv`,
                hs.`ngay_gui_duyet`, hs.`ghi_chu_duyet`,
                l.`ten_lop`, ng.`id_nganh`, ng.`ten_nganh`, k.`id_khoa`, k.`ten_khoa`
             FROM `tai_khoan` tk
             INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
             LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
             LEFT JOIN `id_lop` l ON hs.`id_lop` = l.`id_lop`
             LEFT JOIN `nganh` ng ON l.`id_nganh` = ng.`id_nganh`
             LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
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
                hs.`ho_ten`, hs.`mssv`, hs.`id_lop`, hs.`gioi_tinh`, hs.`ngay_sinh`,
                hs.`so_dien_thoai`, hs.`anh_dai_dien`, hs.`anh_the_sv`,
                hs.`ngay_gui_duyet`, hs.`ghi_chu_duyet`,
                l.`ten_lop`, ng.`id_nganh`, ng.`ten_nganh`, k.`id_khoa`, k.`ten_khoa`
             FROM `tai_khoan` tk
             INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
             LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
             LEFT JOIN `id_lop` l ON hs.`id_lop` = l.`id_lop`
             LEFT JOIN `nganh` ng ON l.`id_nganh` = ng.`id_nganh`
             LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
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
                hs.`ho_ten`, hs.`mssv`, hs.`id_lop`, hs.`gioi_tinh`, hs.`ngay_sinh`,
                hs.`so_dien_thoai`, hs.`anh_dai_dien`, hs.`anh_the_sv`,
                hs.`ngay_gui_duyet`, hs.`ghi_chu_duyet`,
                l.`ten_lop`, ng.`id_nganh`, ng.`ten_nganh`, k.`id_khoa`, k.`ten_khoa`
             FROM `tai_khoan` tk
             INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
             LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
             LEFT JOIN `id_lop` l ON hs.`id_lop` = l.`id_lop`
             LEFT JOIN `nganh` ng ON l.`id_nganh` = ng.`id_nganh`
             LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
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
        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            $status = ($roleId === 3) ? 'ChoDuyet' : 'HoatDong';

            $stmt = $this->db->prepare(
                'INSERT INTO `tai_khoan` (`id_vai_tro`, `email`, `mat_khau`, `google_sub`, `trang_thai`, `created_at`, `updated_at`)
                 VALUES (:role_id, :email, NULL, :sub, :status, NOW(), NOW())'
            );
            $stmt->execute([
                ':role_id' => $roleId,
                ':email' => $profile['email'],
                ':sub' => $profile['sub'],
                ':status' => $status
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

            if ($ownsTx) {
                $this->db->commit();
            }

            return $userId;
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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

    /**
     * Sinh viên nộp thông tin hồ sơ và ảnh thẻ sinh viên chờ duyệt
     */
    public function submitStudentCardProfile(
        int $userId,
        string $mssv,
        string $hoTen,
        int $idLop,
        string $gioiTinh,
        string $ngaySinh,
        string $soDienThoai,
        ?string $anhTheSvUrl = null
    ): bool {
        $sql = 'UPDATE `ho_so_nguoi_dung`
                SET `mssv` = :mssv,
                    `ho_ten` = :ho_ten,
                    `id_lop` = :id_lop,
                    `gioi_tinh` = :gioi_tinh,
                    `ngay_sinh` = :ngay_sinh,
                    `so_dien_thoai` = :so_dien_thoai,
                    `ngay_gui_duyet` = NOW(),
                    `ghi_chu_duyet` = NULL';
        $params = [
            ':id' => $userId,
            ':mssv' => $mssv,
            ':ho_ten' => $hoTen,
            ':id_lop' => $idLop,
            ':gioi_tinh' => $gioiTinh,
            ':ngay_sinh' => $ngaySinh,
            ':so_dien_thoai' => $soDienThoai,
        ];

        if ($anhTheSvUrl !== null) {
            $sql .= ', `anh_the_sv` = :anh_the_sv';
            $params[':anh_the_sv'] = $anhTheSvUrl;
        }

        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            $sql .= ' WHERE `id_tai_khoan` = :id';
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $stmtAccount = $this->db->prepare(
                'UPDATE `tai_khoan`
                 SET `trang_thai` = "ChoDuyet", `updated_at` = NOW()
                 WHERE `id_tai_khoan` = :id'
            );
            $stmtAccount->execute([':id' => $userId]);

            if ($ownsTx) {
                $this->db->commit();
            }

            return true;
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Lấy danh sách sinh viên đang chờ duyệt thẻ
     */
    public function getPendingStudents(): array
    {
        $sql = 'SELECT 
                    tk.`id_tai_khoan`, tk.`email`, tk.`trang_thai`, tk.`created_at`,
                    hs.`mssv`, hs.`ho_ten`, hs.`gioi_tinh`, hs.`ngay_sinh`,
                    hs.`so_dien_thoai`, hs.`anh_dai_dien`, hs.`anh_the_sv`,
                    hs.`ngay_gui_duyet`, hs.`ghi_chu_duyet`,
                    l.`ten_lop`, ng.`id_nganh`, ng.`ten_nganh`, k.`id_khoa`, k.`ten_khoa`
                FROM `tai_khoan` tk
                INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
                INNER JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                LEFT JOIN `id_lop` l ON hs.`id_lop` = l.`id_lop`
                LEFT JOIN `nganh` ng ON l.`id_nganh` = ng.`id_nganh`
                LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
                WHERE vt.`ma_vai_tro` = "STUDENT"
                  AND tk.`trang_thai` = "ChoDuyet"
                  AND hs.`anh_the_sv` IS NOT NULL
                ORDER BY hs.`ngay_gui_duyet` ASC, tk.`id_tai_khoan` ASC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Đếm số lượng thẻ sinh viên đang chờ duyệt
     */
    public function countPendingStudents(): int
    {
        $sql = 'SELECT COUNT(*) 
                FROM `tai_khoan` tk
                INNER JOIN `vai_tro` vt ON tk.`id_vai_tro` = vt.`id_vai_tro`
                INNER JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                WHERE vt.`ma_vai_tro` = "STUDENT"
                  AND tk.`trang_thai` = "ChoDuyet"
                  AND hs.`anh_the_sv` IS NOT NULL';
        $res = $this->db->query($sql);
        return (int)($res ? $res->fetchColumn() : 0);
    }

    /**
     * Cán bộ duyệt thẻ sinh viên -> Chuyển trạng thái sang HoatDong
     */
    public function approveStudent(int $userId): bool
    {
        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE `tai_khoan`
                 SET `trang_thai` = "HoatDong", `updated_at` = NOW()
                 WHERE `id_tai_khoan` = :id'
            );
            $stmt->execute([':id' => $userId]);

            $stmtHs = $this->db->prepare(
                'UPDATE `ho_so_nguoi_dung`
                 SET `ghi_chu_duyet` = NULL
                 WHERE `id_tai_khoan` = :id'
            );
            $stmtHs->execute([':id' => $userId]);

            if ($ownsTx) {
                $this->db->commit();
            }

            return true;
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Cán bộ yêu cầu chụp lại thẻ sinh viên kèm lý do phản hồi
     */
    public function rejectStudent(int $userId, string $reason): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE `ho_so_nguoi_dung`
             SET `ghi_chu_duyet` = :reason
             WHERE `id_tai_khoan` = :id'
        );
        return $stmt->execute([
            ':id' => $userId,
            ':reason' => $reason
        ]);
    }

    /**
     * Lấy danh sách lớp và ngành để hiển thị select box
     */
    public function getClassesList(): array
    {
        $sql = 'SELECT l.`id_lop`, l.`ten_lop`, ng.`ten_nganh`
                FROM `id_lop` l
                INNER JOIN `nganh` ng ON l.`id_nganh` = ng.`id_nganh`
                ORDER BY ng.`ten_nganh` ASC, l.`ten_lop` ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lấy danh sách các Khoa / Viện đào tạo trong trường
     */
    public function getFacultiesList(): array
    {
        $sql = 'SELECT `id_khoa`, `ten_khoa` FROM `khoa` ORDER BY `id_khoa` ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lấy danh mục các chuyên ngành đào tạo, kèm id_khoa và ten_khoa
     */
    public function getMajorsList(?int $idKhoa = null): array
    {
        if ($idKhoa !== null && $idKhoa > 0) {
            $stmt = $this->db->prepare(
                'SELECT ng.`id_nganh`, ng.`ten_nganh`, ng.`id_khoa`, k.`ten_khoa`
                 FROM `nganh` ng
                 LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
                 WHERE ng.`id_khoa` = :id_khoa
                 ORDER BY ng.`id_nganh` ASC'
            );
            $stmt->execute([':id_khoa' => $idKhoa]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $sql = 'SELECT ng.`id_nganh`, ng.`ten_nganh`, ng.`id_khoa`, k.`ten_khoa`
                FROM `nganh` ng
                LEFT JOIN `khoa` k ON ng.`id_khoa` = k.`id_khoa`
                ORDER BY k.`id_khoa` ASC, ng.`id_nganh` ASC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Tìm mã lớp hiện có hoặc tự động thêm lớp mới khi sinh viên tự điền
     */
    public function findOrCreateClass(string $tenLop, int $idNganh = 1): int
    {
        $trimmed = trim($tenLop);
        if ($trimmed === '') {
            $trimmed = 'VH2024.1';
        }

        $stmt = $this->db->prepare(
            'SELECT `id_lop` FROM `id_lop` WHERE LOWER(TRIM(`ten_lop`)) = LOWER(:ten_lop) LIMIT 1'
        );
        $stmt->execute([':ten_lop' => $trimmed]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && !empty($existing['id_lop'])) {
            return (int)$existing['id_lop'];
        }

        $insert = $this->db->prepare(
            'INSERT INTO `id_lop` (`ten_lop`, `id_nganh`) VALUES (:ten_lop, :id_nganh)'
        );
        $insert->execute([
            ':ten_lop' => $trimmed,
            ':id_nganh' => $idNganh > 0 ? $idNganh : 1
        ]);

        return (int)$this->db->lastInsertId();
    }
}


