<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn dữ liệu Mượn trả sách & Báo cáo thống kê (Loan Repository)
 * Tuân thủ 100% Prepared Statements qua PDO theo Rule 8, 9, 11 (AGENTS.md)
 */
class LoanRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Lấy các nhóm phiếu mượn (Đang mượn & Lịch sử mượn) của một độc giả
     */
    public function getUserLoans(int $userId): array
    {
        $sql = "SELECT 
                    pm.`ma_phieu`,
                    ds.`id_dau_sach`,
                    ds.`tieu_de` AS `title`,
                    GROUP_CONCAT(DISTINCT tg.`ten_tac_gia` SEPARATOR ', ') AS `author`,
                    ctm.`id_cuon_sach`,
                    ctm.`ngay_muon`,
                    ctm.`han_tra`,
                    ctm.`ngay_tra_thuc_te`,
                    ctm.`trang_thai`,
                    CASE 
                        WHEN ctm.`trang_thai` = 'DangMuon' AND ctm.`han_tra` < CURDATE() THEN 1
                        ELSE 0
                    END AS `is_overdue`
                FROM `phieu_muon` pm
                INNER JOIN `chi_tiet_muon` ctm ON pm.`id` = ctm.`id_phieu_muon`
                INNER JOIN `cuon_sach` cs ON ctm.`id_cuon_sach` = cs.`id_cuon_sach`
                INNER JOIN `dau_sach` ds ON cs.`id_dau_sach` = ds.`id_dau_sach`
                LEFT JOIN `tac_gia_sach` tgs ON ds.`id_dau_sach` = tgs.`id_dau_sach`
                LEFT JOIN `tac_gia` tg ON tgs.`id_tac_gia` = tg.`id`
                WHERE pm.`id_nguoi_dung` = :user_id
                GROUP BY ctm.`id`, pm.`ma_phieu`, ds.`id_dau_sach`, ds.`tieu_de`, ctm.`id_cuon_sach`, ctm.`ngay_muon`, ctm.`han_tra`, ctm.`ngay_tra_thuc_te`, ctm.`trang_thai`
                ORDER BY ctm.`id` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $activeItems = [];
        $historyItems = [];

        foreach ($rows as $row) {
            $borrowedStr = $row['ngay_muon'] ? date('d/m/Y', strtotime($row['ngay_muon'])) : 'Chờ nhận';
            $dueStr = $row['han_tra'] ? date('d/m/Y', strtotime($row['han_tra'])) : '—';
            $returnedStr = $row['ngay_tra_thuc_te'] ? date('d/m/Y', strtotime($row['ngay_tra_thuc_te'])) : 'Chưa trả';

            $isOverdue = (bool)$row['is_overdue'];
            $statusText = match ($row['trang_thai']) {
                'DangMuon' => $isOverdue ? 'Quá hạn' : 'Trong hạn',
                'DaTra' => 'Đã trả',
                'ChoNhan' => 'Chờ nhận tại quầy',
                'Mat' => 'Báo mất',
                'Hong' => 'Hỏng',
                default => $row['trang_thai']
            };

            $formatted = [
                'title' => $row['title'],
                'author' => $row['author'] ?: 'Chưa rõ',
                'borrowed' => $borrowedStr,
                'due' => $dueStr,
                'status' => $statusText,
                'returned' => $returnedStr,
                'overdue' => $isOverdue,
            ];

            if ($row['trang_thai'] === 'DangMuon' || $row['trang_thai'] === 'ChoNhan') {
                $activeItems[] = $formatted;
            } else {
                $historyItems[] = $formatted;
            }
        }

        return [
            [
                'title' => 'Đang mượn',
                'items' => $activeItems,
            ],
            [
                'title' => 'Lịch sử mượn',
                'items' => $historyItems,
            ],
        ];
    }

    /**
     * Lấy danh sách sách yêu thích của người dùng từ CSDL
     */
    public function getUserFavorites(int $userId): array
    {
        $sql = "SELECT 
                    ds.`id_dau_sach` AS `id`,
                    ds.`tieu_de` AS `title`,
                    GROUP_CONCAT(DISTINCT tg.`ten_tac_gia` SEPARATOR ', ') AS `author`,
                    'Tài liệu văn học' AS `category`,
                    'Văn học' AS `major`,
                    'Nghiên cứu' AS `genre`,
                    COUNT(DISTINCT CASE WHEN cs.`trang_thai` = 'SanSang' THEN cs.`id_cuon_sach` END) AS `available`,
                    1 AS `shelf`,
                    1 AS `cell`,
                    'Kho lưu trữ Thư viện FoL' AS `location`,
                    COALESCE(ds.`mo_ta`, 'Tài liệu lưu trữ tại Thư viện FoL') AS `description`
                FROM `sach_yeu_thich` syt
                INNER JOIN `dau_sach` ds ON syt.`id_dau_sach` = ds.`id_dau_sach`
                LEFT JOIN `tac_gia_sach` tgs ON ds.`id_dau_sach` = tgs.`id_dau_sach`
                LEFT JOIN `tac_gia` tg ON tgs.`id_tac_gia` = tg.`id`
                LEFT JOIN `cuon_sach` cs ON ds.`id_dau_sach` = cs.`id_dau_sach`
                WHERE syt.`id_nguoi_dung` = :user_id
                GROUP BY ds.`id_dau_sach`, ds.`tieu_de`, ds.`mo_ta`
                ORDER BY syt.`ngay_them` DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $map = [];
        foreach ($rows as $r) {
            $map[$r['id']] = $r;
        }

        return $map;
    }

    /**
     * Lấy danh sách phiếu mượn chờ xử lý cho Quầy mượn trả (/librarian/counter)
     */
    public function getCounterLoans(): array
    {
        $sql = "SELECT 
                    pm.`ma_phieu`,
                    COALESCE(hs.`mssv`, CONCAT('ND', tk.`id_tai_khoan`)) AS `student_code`,
                    COALESCE(hs.`ho_ten`, tk.`email`) AS `student_name`,
                    ds.`tieu_de` AS `book_title`,
                    cs.`id_cuon_sach` AS `book_copy`,
                    ctm.`trang_thai`,
                    pm.`ngay_dang_ky`
                FROM `phieu_muon` pm
                INNER JOIN `tai_khoan` tk ON pm.`id_nguoi_dung` = tk.`id_tai_khoan`
                LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                INNER JOIN `chi_tiet_muon` ctm ON pm.`id` = ctm.`id_phieu_muon`
                INNER JOIN `cuon_sach` cs ON ctm.`id_cuon_sach` = cs.`id_cuon_sach`
                INNER JOIN `dau_sach` ds ON cs.`id_dau_sach` = ds.`id_dau_sach`
                WHERE pm.`trang_thai` IN ('ChoDuyet', 'DaDuyet')
                ORDER BY pm.`ngay_dang_ky` DESC
                LIMIT 20";

        $stmt = $this->db->query($sql);
        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    /**
     * Tổng hợp báo cáo số liệu mượn và trả theo các mốc thời gian cho /librarian/reports
     */
    public function getReportsSummary(): array
    {
        // 6 mốc thời gian thực tế: Hôm nay (0), Hôm qua (-1), 7 ngày trước (-7), 35 ngày (-35), 100 ngày (-100), 1 năm (-365)
        $offsets = [0, -1, -7, -35, -100, -365];
        $reports = [];

        foreach ($offsets as $offset) {
            $date = date('Y-m-d', strtotime("{$offset} days"));

            // Đếm số cuốn sách mượn trong ngày đó
            $borrowStmt = $this->db->prepare("SELECT COUNT(*) FROM `chi_tiet_muon` WHERE `ngay_muon` = :dt");
            $borrowStmt->execute([':dt' => $date]);
            $borrowed = (int)$borrowStmt->fetchColumn();

            // Đếm số cuốn sách trả trong ngày đó
            $returnStmt = $this->db->prepare("SELECT COUNT(*) FROM `chi_tiet_muon` WHERE `ngay_tra_thuc_te` = :dt");
            $returnStmt->execute([':dt' => $date]);
            $returned = (int)$returnStmt->fetchColumn();

            $reports[] = [
                'offset' => $offset,
                'borrowed' => $borrowed,
                'returned' => $returned,
            ];
        }

        return $reports;
    }
}
