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
                    ctm.`han_tra`,
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
     * Đã tối ưu hóa loại bỏ hoàn toàn lỗi N+1 queries (chỉ chạy 2 batch aggregate query thay vì 30 query lặp)
     */
    public function getReportsSummary(): array
    {
        // Thu thập các mốc ngày có hoạt động mượn hoặc trả thực tế từ CSDL
        $datesStmt = $this->db->query("
            SELECT DISTINCT `ngay_muon` AS `dt` FROM `chi_tiet_muon` WHERE `ngay_muon` IS NOT NULL
            UNION
            SELECT DISTINCT `ngay_tra_thuc_te` AS `dt` FROM `chi_tiet_muon` WHERE `ngay_tra_thuc_te` IS NOT NULL
        ");
        $existingDates = $datesStmt ? ($datesStmt->fetchAll(PDO::FETCH_COLUMN) ?: []) : [];

        // Đảm bảo luôn có các mốc tham chiếu gần nhất
        $standardOffsets = [0, -1, -7, -20, -35, -100, -365];
        foreach ($standardOffsets as $off) {
            $d = date('Y-m-d', strtotime("{$off} days"));
            if (!in_array($d, $existingDates, true)) {
                $existingDates[] = $d;
            }
        }

        // Sắp xếp ngày giảm dần
        rsort($existingDates);
        $topDates = array_slice($existingDates, 0, 15);

        if (empty($topDates)) {
            return [];
        }

        // Truy vấn gộp (Batch Aggregation) thay cho vòng lặp lồng nhau
        $placeholders = implode(',', array_fill(0, count($topDates), '?'));

        $borrowSql = "SELECT `ngay_muon` AS `dt`, COUNT(*) AS `cnt` 
                      FROM `chi_tiet_muon` 
                      WHERE `ngay_muon` IN ({$placeholders}) 
                      GROUP BY `ngay_muon`";
        $borrowStmt = $this->db->prepare($borrowSql);
        $borrowStmt->execute($topDates);
        $borrowMap = $borrowStmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        $returnSql = "SELECT `ngay_tra_thuc_te` AS `dt`, COUNT(*) AS `cnt` 
                      FROM `chi_tiet_muon` 
                      WHERE `ngay_tra_thuc_te` IN ({$placeholders}) 
                      GROUP BY `ngay_tra_thuc_te`";
        $returnStmt = $this->db->prepare($returnSql);
        $returnStmt->execute($topDates);
        $returnMap = $returnStmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        $todayTs = strtotime('today');
        $reports = [];

        foreach ($topDates as $date) {
            $diffDays = (int)round((strtotime($date) - $todayTs) / 86400);

            $reports[] = [
                'date' => $date,
                'offset' => $diffDays,
                'borrowed' => (int)($borrowMap[$date] ?? 0),
                'returned' => (int)($returnMap[$date] ?? 0),
            ];
        }

        return $reports;
    }

    /**
     * Kiểm tra số lượng sách đang mượn của một độc giả (Tối đa 3 cuốn theo Rule 19)
     */
    public function getActiveLoansCount(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM `chi_tiet_muon` ctm
             INNER JOIN `phieu_muon` pm ON ctm.`id_phieu_muon` = pm.`id`
             WHERE pm.`id_nguoi_dung` = :user_id 
               AND ctm.`trang_thai` IN ('DangMuon', 'ChoNhan')"
        );
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Kiểm tra xem độc giả có cuốn sách nào đang bị quá hạn không
     */
    public function hasOverdueLoans(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM `chi_tiet_muon` ctm
             INNER JOIN `phieu_muon` pm ON ctm.`id_phieu_muon` = pm.`id`
             WHERE pm.`id_nguoi_dung` = :user_id 
               AND ctm.`trang_thai` = 'DangMuon' 
               AND ctm.`han_tra` < CURDATE()"
        );
        $stmt->execute([':user_id' => $userId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Tạo phiếu mượn và chi tiết mượn sách mới (Rule 19, 20, 22)
     * - Khóa bản ghi cuốn sách bằng SELECT ... FOR UPDATE
     * - Ngăn chặn mượn vượt quá 3 cuốn
     * - Ngăn chặn mượn trùng cuốn sách đang có người mượn
     * - Tính hạn trả chuẩn 14 ngày
     */
    public function createLoan(int $userId, string $copyId, int $durationDays = 14, ?int $librarianId = null): array
    {
        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            // 1. Kiểm tra tài khoản sinh viên
            $userStmt = $this->db->prepare("SELECT `id_tai_khoan`, `trang_thai` FROM `tai_khoan` WHERE `id_tai_khoan` = :id");
            $userStmt->execute([':id' => $userId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new \DomainException('Tài khoản sinh viên không tồn tại.');
            }
            if ($user['trang_thai'] !== 'HoatDong') {
                throw new \DomainException('Tài khoản sinh viên chưa được kích hoạt hoặc đang bị tạm khóa.');
            }

            // 2. Kiểm tra giới hạn mượn (Borrow Limit Violation - tối đa 3 cuốn)
            if ($this->getActiveLoansCount($userId) >= 3) {
                throw new \DomainException('Độc giả đã mượn tối đa 3 cuốn sách theo quy chế thư viện.');
            }

            // 3. Kiểm tra sách quá hạn
            if ($this->hasOverdueLoans($userId)) {
                throw new \DomainException('Độc giả có sách quá hạn chưa hoàn trả. Vui lòng thanh toán và trả sách trước khi mượn tiếp.');
            }

            // 4. Khóa và kiểm tra cuốn sách (SELECT ... FOR UPDATE chống Race Condition / Duplicate Active Loan)
            $copyStmt = $this->db->prepare(
                "SELECT `id_cuon_sach`, `id_dau_sach`, `trang_thai` 
                 FROM `cuon_sach` 
                 WHERE `id_cuon_sach` = :copy_id 
                 FOR UPDATE"
            );
            $copyStmt->execute([':copy_id' => $copyId]);
            $copy = $copyStmt->fetch(PDO::FETCH_ASSOC);

            if (!$copy) {
                throw new \DomainException("Không tìm thấy cuốn sách có mã {$copyId}.");
            }
            if ($copy['trang_thai'] !== 'SanSang') {
                throw new \DomainException("Cuốn sách {$copyId} hiện không sẵn sàng cho mượn (Trạng thái: {$copy['trang_thai']}).");
            }

            // 5. Kiểm tra ràng buộc không có giao dịch mượn dở dang của cuốn sách này
            $activeLoanStmt = $this->db->prepare(
                "SELECT COUNT(*) FROM `chi_tiet_muon` 
                 WHERE `id_cuon_sach` = :copy_id AND `trang_thai` IN ('DangMuon', 'ChoNhan')"
            );
            $activeLoanStmt->execute([':copy_id' => $copyId]);
            if (((int)$activeLoanStmt->fetchColumn()) > 0) {
                throw new \DomainException("Cuốn sách {$copyId} đang có giao dịch mượn chưa hoàn tất.");
            }

            // 6. Tạo phiếu mượn
            $loanCode = 'PM-' . strtoupper(bin2hex(random_bytes(6)));
            if ($librarianId !== null) {
                $createPmStmt = $this->db->prepare(
                    "INSERT INTO `phieu_muon` (`ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`)
                     VALUES (:code, :user_id, :staff_id, NOW(), NOW(), 'DaDuyet', 'Mượn trực tiếp tại quầy')"
                );
                $createPmStmt->bindValue(':code', $loanCode, PDO::PARAM_STR);
                $createPmStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $createPmStmt->bindValue(':staff_id', $librarianId, PDO::PARAM_INT);
            } else {
                $createPmStmt = $this->db->prepare(
                    "INSERT INTO `phieu_muon` (`ma_phieu`, `id_nguoi_dung`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`)
                     VALUES (:code, :user_id, NOW(), NOW(), 'DaDuyet', 'Mượn trực tiếp tại quầy')"
                );
                $createPmStmt->bindValue(':code', $loanCode, PDO::PARAM_STR);
                $createPmStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            $createPmStmt->execute();
            $pmId = (int)$this->db->lastInsertId();

            // 7. Tạo chi tiết mượn (Hạn trả chuẩn: 14 ngày)
            $dueDate = date('Y-m-d', strtotime("+{$durationDays} days"));
            $createCtmStmt = $this->db->prepare(
                "INSERT INTO `chi_tiet_muon` (`id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`)
                 VALUES (:pm_id, :copy_id, CURDATE(), :due_date, 'DangMuon', 'Mượn 14 ngày')"
            );
            $createCtmStmt->execute([
                ':pm_id' => $pmId,
                ':copy_id' => $copyId,
                ':due_date' => $dueDate,
            ]);
            $ctmId = (int)$this->db->lastInsertId();

            // 8. Cập nhật trạng thái cuốn sách thành 'DangMuon'
            $updateCopyStmt = $this->db->prepare(
                "UPDATE `cuon_sach` SET `trang_thai` = 'DangMuon' WHERE `id_cuon_sach` = :copy_id"
            );
            $updateCopyStmt->execute([':copy_id' => $copyId]);

            if ($ownsTx) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'loan_id' => $pmId,
                'detail_id' => $ctmId,
                'code' => $loanCode,
                'copy_id' => $copyId,
                'due_date' => $dueDate,
            ];
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Xử lý trả sách và tính phí trễ hạn nếu có (Rule 21: Invalid Book Return)
     */
    public function returnBook(int $detailId, ?int $librarianId = null): array
    {
        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            // 1. Khóa bản ghi chi tiết mượn
            $stmt = $this->db->prepare(
                "SELECT ctm.*, pm.`id_nguoi_dung` 
                 FROM `chi_tiet_muon` ctm
                 INNER JOIN `phieu_muon` pm ON ctm.`id_phieu_muon` = pm.`id`
                 WHERE ctm.`id` = :id 
                 FOR UPDATE"
            );
            $stmt->execute([':id' => $detailId]);
            $ctm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ctm) {
                throw new \DomainException('Không tìm thấy bản ghi chi tiết mượn sách.');
            }
            if ($ctm['trang_thai'] === 'DaTra') {
                throw new \DomainException('Cuốn sách này đã được trả trước đó.');
            }
            if ($ctm['trang_thai'] !== 'DangMuon') {
                throw new \DomainException("Không thể trả cuốn sách ở trạng thái {$ctm['trang_thai']}.");
            }

            // 2. Tính số ngày quá hạn và tiền phạt (1.000 VNĐ / ngày)
            $today = date('Y-m-d');
            $overdueDays = max(0, (int)round((strtotime($today) - strtotime($ctm['han_tra'])) / 86400));
            $fineAmount = $overdueDays * 1000;

            // 3. Cập nhật chi tiết mượn sang 'DaTra'
            $updateCtm = $this->db->prepare(
                "UPDATE `chi_tiet_muon` 
                 SET `trang_thai` = 'DaTra', `ngay_tra_thuc_te` = CURDATE() 
                 WHERE `id` = :id AND `trang_thai` = 'DangMuon'"
            );
            $updateCtm->execute([':id' => $detailId]);

            // 4. Cập nhật cuốn sách sang 'SanSang'
            $updateCopy = $this->db->prepare(
                "UPDATE `cuon_sach` SET `trang_thai` = 'SanSang' WHERE `id_cuon_sach` = :copy_id"
            );
            $updateCopy->execute([':copy_id' => $ctm['id_cuon_sach']]);

            // 5. Nếu tất cả các cuốn trong phiếu mượn đã trả, cập nhật phiếu mượn sang 'HoanTat'
            $checkRemaining = $this->db->prepare(
                "SELECT COUNT(*) FROM `chi_tiet_muon` 
                 WHERE `id_phieu_muon` = :pm_id AND `trang_thai` != 'DaTra'"
            );
            $checkRemaining->execute([':pm_id' => $ctm['id_phieu_muon']]);
            if (((int)$checkRemaining->fetchColumn()) === 0) {
                $updatePm = $this->db->prepare(
                    "UPDATE `phieu_muon` SET `trang_thai` = 'HoanTat' WHERE `id` = :pm_id"
                );
                $updatePm->execute([':pm_id' => $ctm['id_phieu_muon']]);
            }

            if ($ownsTx) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'detail_id' => $detailId,
                'overdue_days' => $overdueDays,
                'fine_amount' => $fineAmount,
            ];
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Gia hạn sách mượn (Rule 23: Invalid Book Renewal)
     * - Chỉ gia hạn khi chưa quá hạn
     * - Tối đa gia hạn 1 lần (+7 ngày)
     */
    public function renewBook(int $detailId, int $renewDays = 7): array
    {
        $ownsTx = !$this->db->inTransaction();
        if ($ownsTx) {
            $this->db->beginTransaction();
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM `chi_tiet_muon` WHERE `id` = :id FOR UPDATE"
            );
            $stmt->execute([':id' => $detailId]);
            $ctm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ctm) {
                throw new \DomainException('Không tìm thấy bản ghi mượn sách.');
            }
            if ($ctm['trang_thai'] !== 'DangMuon') {
                throw new \DomainException('Chỉ có thể gia hạn cuốn sách đang trong trạng thái mượn.');
            }

            // Kiểm tra quá hạn
            $today = date('Y-m-d');
            if ($ctm['han_tra'] < $today) {
                throw new \DomainException('Sách đã quá hạn trả, không thể gia hạn. Vui lòng mang sách đến quầy trả.');
            }

            // Kiểm tra số lần gia hạn (đã gia hạn trước đó)
            $note = (string)($ctm['ghi_chu'] ?? '');
            if (str_contains($note, 'DaGiaHan')) {
                throw new \DomainException('Cuốn sách này đã được gia hạn 1 lần. Mỗi lượt mượn chỉ được gia hạn tối đa 1 lần.');
            }

            $newDueDate = date('Y-m-d', strtotime($ctm['han_tra'] . " +{$renewDays} days"));
            $update = $this->db->prepare(
                "UPDATE `chi_tiet_muon` 
                 SET `han_tra` = :new_due, 
                     `ghi_chu` = CONCAT(COALESCE(`ghi_chu`, ''), ' [DaGiaHan]') 
                 WHERE `id` = :id AND `trang_thai` = 'DangMuon'"
            );
            $update->execute([
                ':new_due' => $newDueDate,
                ':id' => $detailId,
            ]);

            if ($ownsTx) {
                $this->db->commit();
            }

            return [
                'success' => true,
                'detail_id' => $detailId,
                'old_due_date' => $ctm['han_tra'],
                'new_due_date' => $newDueDate,
            ];
        } catch (\Throwable $e) {
            if ($ownsTx && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
