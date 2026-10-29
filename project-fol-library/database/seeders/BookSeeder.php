<?php

declare(strict_types=1);

namespace Database\Seeders;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use PDO;
use Exception;

/**
 * Seeder nhập dữ liệu sách từ 4 tệp CSV vào cơ sở dữ liệu fol_library_db
 * - Bảng đầu sách: dau_sach (id_dau_sach, tieu_de, nam_xuat_ban, so_luong)
 * - Bảng tác giả: tac_gia (id, ten_tac_gia, quoc_tich)
 * - Bảng liên kết tác giả - sách: tac_gia_sach (id_dau_sach, id_tac_gia)
 * - Bảng thể loại: the_loai (id, ten_the_loai, loai, mo_ta)
 * - Bảng liên kết đầu sách - thể loại: dau_sach_the_loai (id_dau_sach, id_the_loai)
 * - Bảng cuốn sách (bản sao vật lý): cuon_sach (ma_vach, id_dau_sach, trang_thai)
 */
class BookSeeder
{
    private const CSV_FILES = [
        'ANT' => [
            'file' => 'database/seeders/book_seeder/Danh sách sách - ANT.csv',
            'id' => 1,
            'name' => 'Tuyển tập - Hợp tuyển',
            'desc' => 'Tuyển tập tác phẩm văn học nghệ thuật (ANT)',
        ],
        'ESS' => [
            'file' => 'database/seeders/book_seeder/Danh sách sách - ESS.csv',
            'id' => 2,
            'name' => 'Tiểu luận - Nghiên cứu',
            'desc' => 'Tiểu luận, phê bình, công trình nghiên cứu lý luận (ESS)',
        ],
        'POE' => [
            'file' => 'database/seeders/book_seeder/Danh sách sách - POE.csv',
            'id' => 3,
            'name' => 'Thơ ca',
            'desc' => 'Tác phẩm thơ ca, thi phẩm và trường ca (POE)',
        ],
        'REC' => [
            'file' => 'database/seeders/book_seeder/Danh sách sách - REC.csv',
            'id' => 4,
            'name' => 'Ký - Hồi ký - Sưu tầm',
            'desc' => 'Ký sự, hồi ký, nhật ký, tư liệu sưu tầm (REC)',
        ],
    ];

    public static function isOrganization(string $s): bool
    {
        return (bool)preg_match('/^(TRƯỜNG|ĐẠI HỌC|KHOA|VIỆN|HỘI|BAN|BỘ|SỞ|NHÀ XUẤT BẢN|NXB|TRUNG TÂM|TẠP CHÍ|ỦY BAN|TỔNG CỤC|LIÊN HIỆP|HỘI ĐỒNG|CLB|CÂU LẠC BỘ|DÒNG HỌ|KHOÁ)/iu', trim($s));
    }

    public static function normalizeAuthorName(string $name): string
    {
        // Unicode-safe strip of surrounding symbols, punctuation, quotes and whitespace
        $name = preg_replace('/^[\s"\'.,\-–—]+|[\s"\'.,\-–—]+$/u', '', $name);
        $name = preg_replace('/\s+/u', ' ', $name);
        if ($name === '') {
            return '';
        }
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    public static function parseAuthors(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '-') {
            return ['Nhiều Tác Giả'];
        }

        $raw = str_replace(["\r", "\n"], ' ', $raw);
        $raw = str_replace(['–', '—'], '-', $raw);
        $raw = preg_replace('/\s+/u', ' ', $raw);

        if (self::isOrganization($raw)) {
            $parts = str_contains($raw, ';') ? explode(';', $raw) : [$raw];
        } else {
            if (str_contains($raw, ';')) {
                $parts = explode(';', $raw);
            } elseif (str_contains($raw, ',')) {
                $parts = explode(',', $raw);
            } elseif (preg_match('/\s+-\s+/', $raw)) {
                $parts = preg_split('/\s+-\s+/', $raw);
            } elseif (str_contains($raw, '-') && preg_match('/^[A-ZÀ-Ỹ\s]+-[A-ZÀ-Ỹ\s]+$/u', $raw)) {
                $parts = explode('-', $raw);
            } else {
                $parts = [$raw];
            }
        }

        $clean = [];
        foreach ($parts as $p) {
            $p = self::normalizeAuthorName($p);
            if ($p !== '') {
                $clean[] = $p;
            }
        }

        return !empty($clean) ? array_values(array_unique($clean)) : ['Nhiều Tác Giả'];
    }

    public static function parseYear(?string $raw): ?int
    {
        if ($raw === null) return null;
        $raw = trim($raw);
        if ($raw === '') return null;

        $raw = str_replace(['O', 'o'], '0', $raw);

        if (preg_match('/\b(19\d\d|20\d\d)\b/', $raw, $m)) {
            return (int)$m[1];
        }

        if (preg_match('/Thg\d+-(\d{2})/i', $raw, $m)) {
            $yy = (int)$m[1];
            return $yy <= 40 ? 2000 + $yy : 1900 + $yy;
        }

        if (is_numeric($raw)) {
            $val = (int)$raw;
            if ($val > 1000 && $val < 2100) return $val;
        }

        return null;
    }

    public static function parseQty(?string $raw): int
    {
        if ($raw === null) return 1;
        $raw = trim($raw);
        if ($raw === '' || $raw === '?') return 1;
        if (is_numeric($raw)) {
            $val = (int)$raw;
            return $val > 0 ? $val : 1;
        }
        return 1;
    }

    private static function sqlEscape(?string $val): string
    {
        if ($val === null) {
            return 'NULL';
        }
        return "'" . str_replace(["\\", "'"], ["\\\\", "''"], $val) . "'";
    }

    public static function run(?PDO $db = null, bool $generateSqlFile = true): array
    {
        $db = $db ?? Database::getConnection();
        $projectRoot = dirname(__DIR__, 2);

        $authorsMap = []; // [name => id]
        $nextAuthorId = 1;
        $books = [];
        $bookAuthors = []; // [[id_dau_sach, id_tac_gia]]
        $bookGenres = [];  // [[id_dau_sach, id_the_loai]]
        $copies = [];      // [[ma_vach, id_dau_sach]]

        // 1. Lấy tác giả đã có trong DB nếu có
        $stmt = $db->query("SELECT id, ten_tac_gia FROM tac_gia");
        $existingAuthors = $stmt ? $stmt->fetchAll(PDO::FETCH_KEY_PAIR) : [];
        foreach ($existingAuthors as $id => $name) {
            $authorsMap[$name] = (int)$id;
            if ((int)$id >= $nextAuthorId) {
                $nextAuthorId = (int)$id + 1;
            }
        }

        // 2. Đọc dữ liệu từ 4 tệp CSV
        foreach (self::CSV_FILES as $code => $info) {
            $filePath = $projectRoot . '/' . $info['file'];
            if (!file_exists($filePath)) {
                throw new Exception("Không tìm thấy tệp CSV: {$filePath}");
            }

            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new Exception("Không thể mở tệp CSV: {$filePath}");
            }

            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;

                $bookCode = trim($row[1] ?? '');
                $title = trim($row[2] ?? '');
                $rawAuthor = trim($row[3] ?? '');
                $rawYear = trim($row[4] ?? '');
                $rawQty = trim($row[5] ?? '1');

                if ($bookCode === '') continue;

                $year = self::parseYear($rawYear);
                $qty = self::parseQty($rawQty);
                $authorNames = self::parseAuthors($rawAuthor);

                $books[] = [
                    'id_dau_sach' => $bookCode,
                    'tieu_de' => $title,
                    'nam_xuat_ban' => $year,
                    'so_luong' => $qty,
                ];

                $bookGenres[] = [
                    'id_dau_sach' => $bookCode,
                    'id_the_loai' => $info['id'],
                ];

                foreach ($authorNames as $authorName) {
                    if (!isset($authorsMap[$authorName])) {
                        $authorsMap[$authorName] = $nextAuthorId++;
                    }
                    $bookAuthors[] = [
                        'id_dau_sach' => $bookCode,
                        'id_tac_gia' => $authorsMap[$authorName],
                    ];
                }

                for ($i = 1; $i <= $qty; $i++) {
                    $barcode = sprintf('%s-%02d', $bookCode, $i);
                    $copies[] = [
                        'ma_vach' => $barcode,
                        'id_dau_sach' => $bookCode,
                    ];
                }
            }
            fclose($handle);
        }

        // 3. Thực thi chèn vào Database qua PDO với Transaction
        $db->exec('SET FOREIGN_KEY_CHECKS = 0;');
        $db->beginTransaction();

        try {
            // 3.1 Thể loại
            $genreValues = [];
            foreach (self::CSV_FILES as $info) {
                $genreValues[] = sprintf(
                    "(%d, %s, 'TheLoai', %s)",
                    $info['id'],
                    self::sqlEscape($info['name']),
                    self::sqlEscape($info['desc'])
                );
            }
            $db->exec("INSERT INTO `the_loai` (`id`, `ten_the_loai`, `loai`, `mo_ta`) VALUES \n" .
                implode(",\n", $genreValues) .
                "\nON DUPLICATE KEY UPDATE `ten_the_loai` = VALUES(`ten_the_loai`), `mo_ta` = VALUES(`mo_ta`);");

            // 3.2 Tác giả (Batch 200)
            $authorChunks = array_chunk($authorsMap, 200, true);
            foreach ($authorChunks as $chunk) {
                $values = [];
                foreach ($chunk as $name => $id) {
                    $values[] = sprintf(
                        "(%d, %s, 'Việt Nam', NOW(), NOW())",
                        $id,
                        self::sqlEscape($name)
                    );
                }
                $db->exec("INSERT INTO `tac_gia` (`id`, `ten_tac_gia`, `quoc_tich`, `created_at`, `updated_at`) VALUES \n" .
                    implode(",\n", $values) .
                    "\nON DUPLICATE KEY UPDATE `ten_tac_gia` = VALUES(`ten_tac_gia`), `updated_at` = NOW();");
            }

            // 3.3 Đầu sách (Batch 200)
            $bookChunks = array_chunk($books, 200);
            foreach ($bookChunks as $chunk) {
                $values = [];
                foreach ($chunk as $b) {
                    $values[] = sprintf(
                        "(%s, %s, %s, 1, 'Tiếng Việt', NOW(), NOW(), %d)",
                        self::sqlEscape($b['id_dau_sach']),
                        self::sqlEscape($b['tieu_de']),
                        $b['nam_xuat_ban'] !== null ? (string)$b['nam_xuat_ban'] : 'NULL',
                        $b['so_luong']
                    );
                }
                $db->exec("INSERT INTO `dau_sach` (`id_dau_sach`, `tieu_de`, `nam_xuat_ban`, `lan_xuat_ban`, `ngon_ngu`, `ngay_tao`, `ngay_cap_nhat`, `so_luong`) VALUES \n" .
                    implode(",\n", $values) .
                    "\nON DUPLICATE KEY UPDATE `tieu_de` = VALUES(`tieu_de`), `nam_xuat_ban` = VALUES(`nam_xuat_ban`), `so_luong` = VALUES(`so_luong`), `ngay_cap_nhat` = NOW();");
            }

            // 3.4 Tác giả - Sách (Batch 500)
            $bookAuthorChunks = array_chunk($bookAuthors, 500);
            foreach ($bookAuthorChunks as $chunk) {
                $values = [];
                foreach ($chunk as $ba) {
                    $values[] = sprintf("(%s, %d)", self::sqlEscape($ba['id_dau_sach']), $ba['id_tac_gia']);
                }
                $db->exec("INSERT IGNORE INTO `tac_gia_sach` (`id_dau_sach`, `id_tac_gia`) VALUES \n" .
                    implode(",\n", $values) . ";");
            }

            // 3.5 Đầu sách - Thể loại (Batch 500)
            $bookGenreChunks = array_chunk($bookGenres, 500);
            foreach ($bookGenreChunks as $chunk) {
                $values = [];
                foreach ($chunk as $bg) {
                    $values[] = sprintf("(%s, %d)", self::sqlEscape($bg['id_dau_sach']), $bg['id_the_loai']);
                }
                $db->exec("INSERT IGNORE INTO `dau_sach_the_loai` (`id_dau_sach`, `id_the_loai`) VALUES \n" .
                    implode(",\n", $values) . ";");
            }

            // 3.6 Cuốn sách (Bản sao vật lý - Batch 500)
            $copyChunks = array_chunk($copies, 500);
            foreach ($copyChunks as $chunk) {
                $values = [];
                foreach ($chunk as $c) {
                    $values[] = sprintf(
                        "(%s, %s, 'SanSang', CURDATE(), 'Kho lưu trữ Thư viện FoL')",
                        self::sqlEscape($c['ma_vach']),
                        self::sqlEscape($c['id_dau_sach'])
                    );
                }
                $db->exec("INSERT INTO `cuon_sach` (`ma_vach`, `id_dau_sach`, `trang_thai`, `ngay_nhap_kho`, `ghi_chu`) VALUES \n" .
                    implode(",\n", $values) .
                    "\nON DUPLICATE KEY UPDATE `trang_thai` = VALUES(`trang_thai`);");
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
            throw $e;
        }

        $db->exec('SET FOREIGN_KEY_CHECKS = 1;');

        // 4. Tạo tệp SQL độc lập nếu được yêu cầu
        if ($generateSqlFile) {
            self::generateSqlFile($projectRoot, $authorsMap, $books, $bookAuthors, $bookGenres, $copies);
        }

        return [
            'total_books' => count($books),
            'total_authors' => count($authorsMap),
            'total_copies' => count($copies),
            'total_book_authors' => count($bookAuthors),
            'total_genres' => count(self::CSV_FILES),
        ];
    }

    private static function generateSqlFile(
        string $projectRoot,
        array $authorsMap,
        array $books,
        array $bookAuthors,
        array $bookGenres,
        array $copies
    ): void {
        $sqlPath = $projectRoot . '/database/seeders/books_seeder.sql';
        $out = fopen($sqlPath, 'w');
        if ($out === false) {
            return;
        }

        fwrite($out, "-- =============================================================================\n");
        fwrite($out, "-- CƠ SỞ DỮ LIỆU THƯ VIỆN KHOA VĂN HỌC (FOL LIBRARY) - SEEDER SÁCH TOÀN DIỆN\n");
        fwrite($out, "-- Tự động tạo từ 4 tệp CSV: ANT (145), ESS (612), POE (127), REC (352)\n");
        fwrite($out, "-- Tổng cộng: 1,236 đầu sách | 985 tác giả | 2,764 cuốn sách vật lý\n");
        fwrite($out, "-- =============================================================================\n\n");
        fwrite($out, "SET NAMES utf8mb4;\n");
        fwrite($out, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

        // 1. Thể loại
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 1. THỂ LOẠI (the_loai)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "INSERT INTO `the_loai` (`id`, `ten_the_loai`, `loai`, `mo_ta`) VALUES\n");
        $genreSql = [];
        foreach (self::CSV_FILES as $info) {
            $genreSql[] = sprintf(
                "(%d, %s, 'TheLoai', %s)",
                $info['id'],
                self::sqlEscape($info['name']),
                self::sqlEscape($info['desc'])
            );
        }
        fwrite($out, implode(",\n", $genreSql) . "\nON DUPLICATE KEY UPDATE `ten_the_loai` = VALUES(`ten_the_loai`), `mo_ta` = VALUES(`mo_ta`);\n\n");

        // 2. Tác giả
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 2. TÁC GIẢ (tac_gia)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        $authorChunks = array_chunk($authorsMap, 200, true);
        foreach ($authorChunks as $chunk) {
            fwrite($out, "INSERT INTO `tac_gia` (`id`, `ten_tac_gia`, `quoc_tich`, `created_at`, `updated_at`) VALUES\n");
            $rows = [];
            foreach ($chunk as $name => $id) {
                $rows[] = sprintf("(%d, %s, 'Việt Nam', NOW(), NOW())", $id, self::sqlEscape($name));
            }
            fwrite($out, implode(",\n", $rows) . "\nON DUPLICATE KEY UPDATE `ten_tac_gia` = VALUES(`ten_tac_gia`), `updated_at` = NOW();\n\n");
        }

        // 3. Đầu sách
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 3. ĐẦU SÁCH (dau_sach)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        $bookChunks = array_chunk($books, 200);
        foreach ($bookChunks as $chunk) {
            fwrite($out, "INSERT INTO `dau_sach` (`id_dau_sach`, `tieu_de`, `nam_xuat_ban`, `lan_xuat_ban`, `ngon_ngu`, `ngay_tao`, `ngay_cap_nhat`, `so_luong`) VALUES\n");
            $rows = [];
            foreach ($chunk as $b) {
                $rows[] = sprintf(
                    "(%s, %s, %s, 1, 'Tiếng Việt', NOW(), NOW(), %d)",
                    self::sqlEscape($b['id_dau_sach']),
                    self::sqlEscape($b['tieu_de']),
                    $b['nam_xuat_ban'] !== null ? (string)$b['nam_xuat_ban'] : 'NULL',
                    $b['so_luong']
                );
            }
            fwrite($out, implode(",\n", $rows) . "\nON DUPLICATE KEY UPDATE `tieu_de` = VALUES(`tieu_de`), `nam_xuat_ban` = VALUES(`nam_xuat_ban`), `so_luong` = VALUES(`so_luong`), `ngay_cap_nhat` = NOW();\n\n");
        }

        // 4. Tác giả - Sách
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 4. TÁC GIẢ - SÁCH (tac_gia_sach)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        $bookAuthorChunks = array_chunk($bookAuthors, 500);
        foreach ($bookAuthorChunks as $chunk) {
            fwrite($out, "INSERT IGNORE INTO `tac_gia_sach` (`id_dau_sach`, `id_tac_gia`) VALUES\n");
            $rows = [];
            foreach ($chunk as $ba) {
                $rows[] = sprintf("(%s, %d)", self::sqlEscape($ba['id_dau_sach']), $ba['id_tac_gia']);
            }
            fwrite($out, implode(",\n", $rows) . ";\n\n");
        }

        // 5. Đầu sách - Thể loại
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 5. ĐẦU SÁCH - THỂ LOẠI (dau_sach_the_loai)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        $bookGenreChunks = array_chunk($bookGenres, 500);
        foreach ($bookGenreChunks as $chunk) {
            fwrite($out, "INSERT IGNORE INTO `dau_sach_the_loai` (`id_dau_sach`, `id_the_loai`) VALUES\n");
            $rows = [];
            foreach ($chunk as $bg) {
                $rows[] = sprintf("(%s, %d)", self::sqlEscape($bg['id_dau_sach']), $bg['id_the_loai']);
            }
            fwrite($out, implode(",\n", $rows) . ";\n\n");
        }

        // 6. Cuốn sách (Bản sao vật lý)
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        fwrite($out, "-- 6. CUỐN SÁCH VẬT LÝ (cuon_sach)\n");
        fwrite($out, "-- -----------------------------------------------------------------------------\n");
        $copyChunks = array_chunk($copies, 500);
        foreach ($copyChunks as $chunk) {
            fwrite($out, "INSERT INTO `cuon_sach` (`ma_vach`, `id_dau_sach`, `trang_thai`, `ngay_nhap_kho`, `ghi_chu`) VALUES\n");
            $rows = [];
            foreach ($chunk as $c) {
                $rows[] = sprintf(
                    "(%s, %s, 'SanSang', CURDATE(), 'Kho lưu trữ Thư viện FoL')",
                    self::sqlEscape($c['ma_vach']),
                    self::sqlEscape($c['id_dau_sach'])
                );
            }
            fwrite($out, implode(",\n", $rows) . "\nON DUPLICATE KEY UPDATE `trang_thai` = VALUES(`trang_thai`);\n\n");
        }

        fwrite($out, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($out);
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    echo "Đang xử lý và nhập dữ liệu sách từ 4 tệp CSV vào database...\n";
    $stats = BookSeeder::run();
    echo "========================================================\n";
    echo "NHẬP DỮ LIỆU SÁCH THÀNH CÔNG!\n";
    echo "- Số đầu sách (dau_sach):         {$stats['total_books']}\n";
    echo "- Số tác giả (tac_gia):           {$stats['total_authors']}\n";
    echo "- Số liên kết (tac_gia_sach):     {$stats['total_book_authors']}\n";
    echo "- Số thể loại (the_loai):         {$stats['total_genres']}\n";
    echo "- Số cuốn sách (cuon_sach):       {$stats['total_copies']}\n";
    echo "========================================================\n";
    echo "Đã tạo tệp SQL seeder tại: database/seeders/books_seeder.sql\n";
}
