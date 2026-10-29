<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Tầng truy vấn cơ sở dữ liệu cho Đầu sách và Cuốn sách (Book Repository)
 * Tuân thủ 100% Prepared Statements qua PDO theo Rule 8, 9, 11 (AGENTS.md)
 */
class BookRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Lấy danh sách đầu sách có phân trang, tìm kiếm và lọc chuyên mục
     *
     * @return array{
     *     items: list<array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     perPage: int,
     *     totalPages: int
     * }
     */
    public function getPaginated(
        int $page = 1,
        int $perPage = 15,
        ?string $keyword = null,
        ?string $prefix = null
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $conditions = [];
        $params = [];

        if ($keyword !== null && trim($keyword) !== '') {
            $kw = '%' . trim($keyword) . '%';
            $conditions[] = "(ds.`id_dau_sach` LIKE :kw1 OR ds.`tieu_de` LIKE :kw2 OR tg.`ten_tac_gia` LIKE :kw3)";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
        }

        if ($prefix !== null && trim($prefix) !== '' && $prefix !== 'ALL') {
            $conditions[] = "ds.`id_dau_sach` LIKE :prefix";
            $params[':prefix'] = trim($prefix) . '%';
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // 1. Đếm tổng số đầu sách khớp điều kiện
        $countSql = "SELECT COUNT(DISTINCT ds.`id_dau_sach`)
                     FROM `dau_sach` ds
                     LEFT JOIN `tac_gia_sach` tgs ON ds.`id_dau_sach` = tgs.`id_dau_sach`
                     LEFT JOIN `tac_gia` tg ON tgs.`id_tac_gia` = tg.`id`
                     {$whereClause}";

        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

        if ($total === 0) {
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 1,
            ];
        }

        // 2. Lấy dữ liệu phân trang
        $dataSql = "SELECT 
                        ds.`id_dau_sach`,
                        ds.`tieu_de`,
                        ds.`isbn`,
                        ds.`mo_ta`,
                        ds.`nam_xuat_ban`,
                        ds.`lan_xuat_ban`,
                        ds.`ngon_ngu`,
                        ds.`so_luong`,
                        GROUP_CONCAT(DISTINCT tg.`ten_tac_gia` ORDER BY tg.`id` ASC SEPARATOR ', ') AS `tac_gia`,
                        COUNT(DISTINCT cs.`id_cuon_sach`) AS `tong_cuon`,
                        COUNT(DISTINCT CASE WHEN cs.`trang_thai` = 'SanSang' THEN cs.`id_cuon_sach` END) AS `co_the_muon`,
                        CONCAT(
                            'Kệ ',
                            COALESCE(LPAD(ks.`ma_ke`, 2, '0'), '01'),
                            ' · Ô ',
                            COALESCE(LPAD(os.`ma_o`, 2, '0'), '01')
                        ) AS `vi_tri`
                    FROM `dau_sach` ds
                    LEFT JOIN `tac_gia_sach` tgs ON ds.`id_dau_sach` = tgs.`id_dau_sach`
                    LEFT JOIN `tac_gia` tg ON tgs.`id_tac_gia` = tg.`id`
                    LEFT JOIN `cuon_sach` cs ON ds.`id_dau_sach` = cs.`id_dau_sach`
                    LEFT JOIN `o_sach` os ON cs.`id_o_sach` = os.`id_o_sach`
                    LEFT JOIN `ke_sach` ks ON os.`id_ke_sach` = ks.`id_ke_sach`
                    {$whereClause}
                    GROUP BY ds.`id_dau_sach`, ds.`tieu_de`, ds.`isbn`, ds.`mo_ta`, ds.`nam_xuat_ban`, ds.`lan_xuat_ban`, ds.`ngon_ngu`, ds.`so_luong`
                    ORDER BY ds.`id_dau_sach` ASC
                    LIMIT :offset, :limit";

        $dataStmt = $this->db->prepare($dataSql);
        foreach ($params as $key => $val) {
            $dataStmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStmt->execute();

        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items' => $rows ?: [],
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Tìm chi tiết đầu sách theo mã, kèm danh sách cuốn sách vật lý
     */
    public function findById(string $id): ?array
    {
        $sql = "SELECT 
                    ds.`id_dau_sach`,
                    ds.`tieu_de`,
                    ds.`isbn`,
                    ds.`mo_ta`,
                    ds.`nam_xuat_ban`,
                    ds.`lan_xuat_ban`,
                    ds.`ngon_ngu`,
                    ds.`so_luong`,
                    ds.`ngay_tao`,
                    ds.`ngay_cap_nhat`,
                    nxb.`ten_nha_xuat_ban`,
                    GROUP_CONCAT(DISTINCT tg.`ten_tac_gia` ORDER BY tg.`id` ASC SEPARATOR ', ') AS `tac_gia`,
                    COUNT(DISTINCT cs.`id_cuon_sach`) AS `tong_cuon`,
                    COUNT(DISTINCT CASE WHEN cs.`trang_thai` = 'SanSang' THEN cs.`id_cuon_sach` END) AS `co_the_muon`
                FROM `dau_sach` ds
                LEFT JOIN `nha_xuat_ban` nxb ON ds.`id_nha_xuat_ban` = nxb.`id`
                LEFT JOIN `tac_gia_sach` tgs ON ds.`id_dau_sach` = tgs.`id_dau_sach`
                LEFT JOIN `tac_gia` tg ON tgs.`id_tac_gia` = tg.`id`
                LEFT JOIN `cuon_sach` cs ON ds.`id_dau_sach` = cs.`id_dau_sach`
                WHERE ds.`id_dau_sach` = :id
                GROUP BY ds.`id_dau_sach`, ds.`tieu_de`, ds.`isbn`, ds.`mo_ta`, ds.`nam_xuat_ban`, ds.`lan_xuat_ban`, ds.`ngon_ngu`, ds.`so_luong`, ds.`ngay_tao`, ds.`ngay_cap_nhat`, nxb.`ten_nha_xuat_ban`";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            return null;
        }

        // Lấy danh sách các bản sách vật lý
        $copiesStmt = $this->db->prepare(
            "SELECT 
                cs.`id_cuon_sach`,
                cs.`ma_vach`,
                cs.`trang_thai`,
                cs.`ngay_nhap_kho`,
                cs.`ghi_chu`,
                os.`ma_o`,
                ks.`ma_ke`
             FROM `cuon_sach` cs
             LEFT JOIN `o_sach` os ON cs.`id_o_sach` = os.`id_o_sach`
             LEFT JOIN `ke_sach` ks ON os.`id_ke_sach` = ks.`id_ke_sach`
             WHERE cs.`id_dau_sach` = :id
             ORDER BY cs.`id_cuon_sach` ASC"
        );
        $copiesStmt->execute([':id' => $id]);
        $book['copies'] = $copiesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $book;
    }

    /**
     * Kiểm tra tồn tại mã đầu sách
     */
    public function exists(string $id): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM `dau_sach` WHERE `id_dau_sach` = :id");
        $stmt->execute([':id' => $id]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Tạo mới một đầu sách cùng các cuốn sách vật lý ban đầu
     */
    public function create(array $data): string
    {
        $id = trim((string)($data['id_dau_sach'] ?? ''));
        if ($id === '') {
            $prefix = (string)($data['prefix'] ?? 'KV-ANT');
            $id = $this->generateNextId($prefix);
        }

        $title = trim((string)($data['tieu_de'] ?? ''));
        $year = !empty($data['nam_xuat_ban']) ? (int)$data['nam_xuat_ban'] : (int)date('Y');
        $desc = !empty($data['mo_ta']) ? trim((string)$data['mo_ta']) : null;
        $copiesCount = max(1, (int)($data['so_luong'] ?? 1));
        $authorName = trim((string)($data['tac_gia'] ?? 'Chưa rõ tác giả'));

        $this->db->beginTransaction();

        try {
            // 1. Thêm đầu sách
            $insertBookSql = "INSERT INTO `dau_sach` (`id_dau_sach`, `tieu_de`, `nam_xuat_ban`, `mo_ta`, `ngon_ngu`, `ngay_tao`, `so_luong`)
                              VALUES (:id, :title, :year, :desc, 'Tiếng Việt', NOW(), 0)";
            $stmtBook = $this->db->prepare($insertBookSql);
            $stmtBook->execute([
                ':id' => $id,
                ':title' => $title,
                ':year' => $year,
                ':desc' => $desc,
            ]);

            // 2. Gán tác giả
            $this->assignAuthorsToBook($id, $authorName);

            // 3. Tạo các cuốn sách vật lý (Trigger tg_cuon_sach_after_insert sẽ tự động cập nhật dau_sach.so_luong)
            $insertCopySql = "INSERT INTO `cuon_sach` (`id_cuon_sach`, `ma_vach`, `id_dau_sach`, `trang_thai`, `ngay_nhap_kho`, `ghi_chu`)
                              VALUES (:id_cuon, :ma_vach, :id_dau, 'SanSang', CURDATE(), 'Kho lưu trữ Thư viện FoL')";
            $stmtCopy = $this->db->prepare($insertCopySql);

            for ($i = 1; $i <= $copiesCount; $i++) {
                $copyId = "{$id}.{$i}";
                $stmtCopy->execute([
                    ':id_cuon' => $copyId,
                    ':ma_vach' => $copyId,
                    ':id_dau' => $id,
                ]);
            }

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật thông tin đầu sách
     */
    public function update(string $id, array $data): bool
    {
        if (!$this->exists($id)) {
            throw new InvalidArgumentException("Không tìm thấy đầu sách với mã: {$id}");
        }

        $this->db->beginTransaction();

        try {
            // 1. Cập nhật đầu sách
            $updateSql = "UPDATE `dau_sach` 
                          SET `tieu_de` = :title,
                              `nam_xuat_ban` = :year,
                              `mo_ta` = :desc,
                              `ngay_cap_nhat` = NOW()
                          WHERE `id_dau_sach` = :id";
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute([
                ':title' => trim((string)$data['tieu_de']),
                ':year' => !empty($data['nam_xuat_ban']) ? (int)$data['nam_xuat_ban'] : null,
                ':desc' => !empty($data['mo_ta']) ? trim((string)$data['mo_ta']) : null,
                ':id' => $id,
            ]);

            // 2. Cập nhật tác giả nếu được cung cấp
            if (isset($data['tac_gia']) && trim((string)$data['tac_gia']) !== '') {
                $delRelStmt = $this->db->prepare("DELETE FROM `tac_gia_sach` WHERE `id_dau_sach` = :id");
                $delRelStmt->execute([':id' => $id]);
                $this->assignAuthorsToBook($id, (string)$data['tac_gia']);
            }

            // 3. Bổ sung cuốn sách mới nếu người dùng yêu cầu tăng số lượng
            $additionalCopies = (int)($data['additional_copies'] ?? 0);
            if ($additionalCopies > 0) {
                // Tìm chỉ số cuốn sách lớn nhất hiện tại
                $maxStmt = $this->db->prepare("SELECT `id_cuon_sach` FROM `cuon_sach` WHERE `id_dau_sach` = :id ORDER BY `id_cuon_sach` DESC");
                $maxStmt->execute([':id' => $id]);
                $allCopies = $maxStmt->fetchAll(PDO::FETCH_COLUMN);

                $maxIndex = 0;
                foreach ($allCopies as $copyId) {
                    $parts = explode('.', (string)$copyId);
                    $num = (int)end($parts);
                    if ($num > $maxIndex) {
                        $maxIndex = $num;
                    }
                }

                $insertCopySql = "INSERT INTO `cuon_sach` (`id_cuon_sach`, `ma_vach`, `id_dau_sach`, `trang_thai`, `ngay_nhap_kho`, `ghi_chu`)
                                  VALUES (:id_cuon, :ma_vach, :id_dau, 'SanSang', CURDATE(), 'Kho lưu trữ Thư viện FoL')";
                $stmtCopy = $this->db->prepare($insertCopySql);

                for ($j = 1; $j <= $additionalCopies; $j++) {
                    $newIndex = $maxIndex + $j;
                    $copyId = "{$id}.{$newIndex}";
                    $stmtCopy->execute([
                        ':id_cuon' => $copyId,
                        ':ma_vach' => $copyId,
                        ':id_dau' => $id,
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Xóa đầu sách nếu không có giao dịch mượn dở dang
     */
    public function delete(string $id): bool
    {
        // 1. Kiểm tra xem có cuốn sách nào đang được mượn không
        $checkStmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM `chi_tiet_muon` ctm
             INNER JOIN `cuon_sach` cs ON ctm.`id_cuon_sach` = cs.`id_cuon_sach`
             WHERE cs.`id_dau_sach` = :id AND ctm.`trang_thai` = 'DangMuon'"
        );
        $checkStmt->execute([':id' => $id]);
        $activeLoans = (int)$checkStmt->fetchColumn();

        if ($activeLoans > 0) {
            throw new RuntimeException("Không thể xóa đầu sách {$id} vì hiện có {$activeLoans} cuốn sách đang trong trạng thái được mượn.");
        }

        $this->db->beginTransaction();

        try {
            // Xóa chi tiết mượn và phiếu mượn liên quan tới cuốn sách này nếu có (lịch sử đã hoàn tất)
            $delCtmStmt = $this->db->prepare(
                "DELETE ctm FROM `chi_tiet_muon` ctm
                 INNER JOIN `cuon_sach` cs ON ctm.`id_cuon_sach` = cs.`id_cuon_sach`
                 WHERE cs.`id_dau_sach` = :id"
            );
            $delCtmStmt->execute([':id' => $id]);

            // Xóa cuốn sách
            $delCopiesStmt = $this->db->prepare("DELETE FROM `cuon_sach` WHERE `id_dau_sach` = :id");
            $delCopiesStmt->execute([':id' => $id]);

            // Xóa liên kết tác giả
            $delRelStmt = $this->db->prepare("DELETE FROM `tac_gia_sach` WHERE `id_dau_sach` = :id");
            $delRelStmt->execute([':id' => $id]);

            // Xóa đầu sách
            $delBookStmt = $this->db->prepare("DELETE FROM `dau_sach` WHERE `id_dau_sach` = :id");
            $delBookStmt->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Tự động sinh mã đầu sách tiếp theo theo tiền tố (Ví dụ: KV-ANT-0016)
     */
    public function generateNextId(string $prefix = 'KV-ANT'): string
    {
        $cleanPrefix = strtoupper(trim($prefix));
        if ($cleanPrefix === '' || $cleanPrefix === 'ALL') {
            $cleanPrefix = 'KV-GEN';
        }

        $sql = "SELECT `id_dau_sach` FROM `dau_sach` WHERE `id_dau_sach` LIKE :prefix ORDER BY `id_dau_sach` DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':prefix' => $cleanPrefix . '-%']);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $maxSeq = 0;
        foreach ($rows as $bookId) {
            // Tách phần số cuối
            if (preg_match('/-(\d+)$/', (string)$bookId, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxSeq) {
                    $maxSeq = $num;
                }
            }
        }

        $nextSeq = $maxSeq + 1;
        return sprintf('%s-%04d', $cleanPrefix, $nextSeq);
    }

    /**
     * Gán tác giả vào đầu sách, tự tạo tác giả mới nếu chưa tồn tại
     */
    private function assignAuthorsToBook(string $bookId, string $authorsString): void
    {
        $authorNames = array_filter(array_map('trim', explode(',', $authorsString)));
        if (empty($authorNames)) {
            $authorNames = ['Chưa rõ tác giả'];
        }

        $findAuthorStmt = $this->db->prepare("SELECT `id` FROM `tac_gia` WHERE `ten_tac_gia` = :name LIMIT 1");
        $insertAuthorStmt = $this->db->prepare(
            "INSERT INTO `tac_gia` (`ten_tac_gia`, `quoc_tich`, `created_at`, `updated_at`)
             VALUES (:name, 'Việt Nam', NOW(), NOW())"
        );
        $linkStmt = $this->db->prepare(
            "INSERT IGNORE INTO `tac_gia_sach` (`id_dau_sach`, `id_tac_gia`) VALUES (:book_id, :author_id)"
        );

        foreach ($authorNames as $authorName) {
            $findAuthorStmt->execute([':name' => $authorName]);
            $authorId = $findAuthorStmt->fetchColumn();

            if (!$authorId) {
                $insertAuthorStmt->execute([':name' => $authorName]);
                $authorId = (int)$this->db->lastInsertId();
            } else {
                $authorId = (int)$authorId;
            }

            $linkStmt->execute([
                ':book_id' => $bookId,
                ':author_id' => $authorId,
            ]);
        }
    }
}
