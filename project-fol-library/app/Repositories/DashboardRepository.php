<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn dữ liệu cho Bảng điều khiển Tổng quan Thư viện (Dashboard Repository)
 * Tuân thủ 100% Prepared Statements qua PDO theo Rule 8 & 9 (AGENTS.md)
 */
class DashboardRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Thống kê tổng hợp số lượng sách mượn, quá hạn, đặt phòng và quy mô kho sách
     */
    public function getOverviewStatistics(): array
    {
        // 1. Số cuốn sách đang mượn
        $stmtBorrow = $this->db->query("SELECT COUNT(*) FROM `chi_tiet_muon` WHERE `trang_thai` = 'DangMuon'");
        $borrowCount = (int)($stmtBorrow ? $stmtBorrow->fetchColumn() : 0);

        // 2. Số cuốn sách quá hạn
        $stmtOverdue = $this->db->query("SELECT COUNT(*) FROM `chi_tiet_muon` WHERE `trang_thai` = 'DangMuon' AND `han_tra` < CURDATE()");
        $overdueCount = (int)($stmtOverdue ? $stmtOverdue->fetchColumn() : 0);

        // 3. Số lượt đặt phòng đọc hôm nay
        $stmtBookings = $this->db->query("SELECT COUNT(*) FROM `dat_cho` WHERE `ngay_dat` = CURDATE() AND `trang_thai` != 'Huy'");
        $bookingTodayCount = (int)($stmtBookings ? $stmtBookings->fetchColumn() : 0);

        // 4. Tổng đầu sách trong thư viện
        $stmtTitles = $this->db->query("SELECT COUNT(*) FROM `dau_sach`");
        $titleCount = (int)($stmtTitles ? $stmtTitles->fetchColumn() : 0);

        // 5. Tổng cuốn sách vật lý trong kho
        $stmtCopies = $this->db->query("SELECT COUNT(*) FROM `cuon_sach`");
        $copyCount = (int)($stmtCopies ? $stmtCopies->fetchColumn() : 0);

        return [
            'borrowed_count' => $borrowCount,
            'overdue_count' => $overdueCount,
            'booking_today_count' => $bookingTodayCount,
            'total_titles' => $titleCount,
            'total_copies' => $copyCount,
        ];
    }

    /**
     * Lấy danh sách các ca phòng đọc hôm nay kèm số chỗ đã đặt thực tế
     */
    public function getReadingShiftsToday(): array
    {
        // Lấy tổng số chỗ ngồi hoạt động của phòng đọc
        $stmtCapacity = $this->db->query("SELECT COUNT(*) FROM `cho_ngoi` WHERE `trang_thai` = 'HoatDong'");
        $totalCapacity = (int)($stmtCapacity ? $stmtCapacity->fetchColumn() : 40);
        if ($totalCapacity <= 0) {
            $totalCapacity = 40;
        }

        $sql = "SELECT 
                    cd.`id_ca_doc`,
                    cd.`ten_ca` AS `name`,
                    CONCAT(TIME_FORMAT(cd.`gio_bat_dau`, '%H:%i'), ' – ', TIME_FORMAT(cd.`gio_ket_thuc`, '%H:%i')) AS `time`,
                    COUNT(dc.`id_dat_cho`) AS `booked`,
                    :capacity AS `capacity`
                FROM `ca_doc` cd
                LEFT JOIN `dat_cho` dc ON cd.`id_ca_doc` = dc.`id_ca_doc` 
                    AND dc.`ngay_dat` = CURDATE() 
                    AND dc.`trang_thai` != 'Huy'
                WHERE cd.`trang_thai` = 'HoatDong'
                GROUP BY cd.`id_ca_doc`, cd.`ten_ca`, cd.`gio_bat_dau`, cd.`gio_ket_thuc`
                ORDER BY cd.`gio_bat_dau` ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':capacity' => $totalCapacity]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows ?: [];
    }

    /**
     * Lấy danh sách phiếu cần chú ý (quá hạn, chờ nhận sách, chờ duyệt)
     */
    public function getAttentionLoans(int $limit = 10): array
    {
        $sql = "SELECT 
                    pm.`ma_phieu` AS `code`,
                    COALESCE(hs.`mssv`, hs.`ho_ten`, tk.`email`) AS `student`,
                    ds.`tieu_de` AS `book`,
                    CASE 
                        WHEN ctm.`trang_thai` = 'DangMuon' AND ctm.`han_tra` < CURDATE() THEN 'Quá hạn'
                        WHEN pm.`trang_thai` = 'ChoDuyet' THEN 'Chờ duyệt'
                        WHEN pm.`trang_thai` = 'DaDuyet' AND (ctm.`ngay_muon` IS NULL OR ctm.`trang_thai` = 'ChoNhan') THEN 'Chờ nhận sách'
                        WHEN ctm.`trang_thai` = 'DangMuon' THEN 'Đang mượn'
                        ELSE ctm.`trang_thai`
                    END AS `status`,
                    ctm.`han_tra`,
                    pm.`ngay_dang_ky`
                FROM `phieu_muon` pm
                INNER JOIN `tai_khoan` tk ON pm.`id_nguoi_dung` = tk.`id_tai_khoan`
                LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                INNER JOIN `chi_tiet_muon` ctm ON pm.`id` = ctm.`id_phieu_muon`
                INNER JOIN `cuon_sach` cs ON ctm.`id_cuon_sach` = cs.`id_cuon_sach`
                INNER JOIN `dau_sach` ds ON cs.`id_dau_sach` = ds.`id_dau_sach`
                WHERE (ctm.`trang_thai` = 'DangMuon' AND ctm.`han_tra` < CURDATE())
                   OR pm.`trang_thai` IN ('ChoDuyet', 'DaDuyet')
                ORDER BY 
                    (CASE WHEN ctm.`trang_thai` = 'DangMuon' AND ctm.`han_tra` < CURDATE() THEN 1 ELSE 2 END) ASC,
                    pm.`ngay_dang_ky` DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows ?: [];
    }
}
