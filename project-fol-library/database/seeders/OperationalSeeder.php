<?php

declare(strict_types=1);

namespace Database\Seeders;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use PDO;

/**
 * Seeder khởi tạo cơ sở vật chất phòng đọc, ca đọc, chỗ ngồi thư viện
 */
class OperationalSeeder
{
    public static function run(?PDO $db = null, bool $withSampleTransactions = false): void
    {
        $db = $db ?? Database::getConnection();

        $db->exec('SET FOREIGN_KEY_CHECKS = 0;');

        // 1. Phòng đọc
        $db->exec("INSERT INTO `phong` (`id_phong`, `ma_phong`, `ten_phong`, `mo_ta`, `trang_thai`) VALUES
            (1, 'P101', 'Phòng đọc Tổng hợp Khoa Văn học', 'Tầng 1 - Sức chứa 40 chỗ ngồi học tập và tra cứu', 'Đang hoạt động')
            ON DUPLICATE KEY UPDATE `ten_phong` = VALUES(`ten_phong`), `trang_thai` = VALUES(`trang_thai`);");

        // 2. Chỗ ngồi (40 ghế: G01 -> G40)
        $seatValues = [];
        for ($i = 1; $i <= 40; $i++) {
            $seatCode = sprintf('G%02d', $i);
            $seatValues[] = sprintf("(%d, 1, '%s', 'Ghế bàn tiêu chuẩn %s', 'HoatDong')", $i, $seatCode, $seatCode);
        }
        $db->exec("INSERT INTO `cho_ngoi` (`id_cho_ngoi`, `id_phong`, `ma_cho_ngoi`, `mo_ta`, `trang_thai`) VALUES \n" .
            implode(",\n", $seatValues) .
            "\nON DUPLICATE KEY UPDATE `trang_thai` = VALUES(`trang_thai`);");

        // 3. Ca đọc trong ngày
        $db->exec("INSERT INTO `ca_doc` (`id_ca_doc`, `ten_ca`, `gio_bat_dau`, `gio_ket_thuc`, `trang_thai`) VALUES
            (1, 'Ca 1', '07:30:00', '09:30:00', 'HoatDong'),
            (2, 'Ca 2', '09:45:00', '11:45:00', 'HoatDong'),
            (3, 'Ca 3', '13:00:00', '15:00:00', 'HoatDong'),
            (4, 'Ca 4', '15:15:00', '17:15:00', 'HoatDong')
            ON DUPLICATE KEY UPDATE `gio_bat_dau` = VALUES(`gio_bat_dau`), `gio_ket_thuc` = VALUES(`gio_ket_thuc`), `trang_thai` = VALUES(`trang_thai`);");

        // 4. Giao dịch mượn/đặt phòng mẫu (chỉ nạp khi được yêu cầu rõ)
        if ($withSampleTransactions) {
            $db->exec("DELETE FROM `dat_cho` WHERE `ngay_dat` = CURDATE();");
            $bookingValues = [];
            for ($s = 1; $s <= 12; $s++) {
                $user = ($s % 2 === 0) ? 3 : 4;
                $bookingValues[] = sprintf(
                    "('DC-TODAY-1-%02d', %d, 1, %d, CURDATE(), NOW(), NOW(), NULL, NULL, 'DaDat', 'Đặt chỗ tự động')",
                    $s, $s, $user
                );
            }
            $db->exec("INSERT INTO `dat_cho` (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`, `ngay_dat`, `thoi_gian_tao`, `thoi_gian_xac_nhan`, `thoi_gian_checkin`, `thoi_gian_huy`, `trang_thai`, `ghi_chu`) VALUES \n" .
                implode(",\n", $bookingValues) . ";");

            $db->exec("DELETE FROM `chi_tiet_muon`;");
            $db->exec("DELETE FROM `phieu_muon`;");

            $db->exec("INSERT INTO `phieu_muon` (`id`, `ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`) VALUES
                (1, 'PM001', 3, 2, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 'DaDuyet', 'Mượn chuyên đề'),
                (2, 'PM002', 4, 2, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), 'DaDuyet', 'Chờ nhận sách');");

            $db->exec("INSERT INTO `chi_tiet_muon` (`id`, `id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`) VALUES
                (1, 1, 'KV-ANT-0001.1', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'DangMuon', 'Quá hạn'),
                (2, 2, 'KV-ESS-0001.1', NULL, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'ChoNhan', 'Chờ nhận');");

            $db->exec("UPDATE `cuon_sach` SET `trang_thai` = 'DangMuon' WHERE `id_cuon_sach` = 'KV-ANT-0001.1';");
        } else {
            // Đảm bảo dữ liệu giao dịch sạch và toàn bộ sách ở trạng thái sẵn sàng
            $db->exec("TRUNCATE TABLE `thanh_toan_phat`;");
            $db->exec("TRUNCATE TABLE `phieu_phat`;");
            $db->exec("TRUNCATE TABLE `chi_tiet_muon`;");
            $db->exec("TRUNCATE TABLE `phieu_muon`;");
            $db->exec("TRUNCATE TABLE `dat_cho`;");
            $db->exec("UPDATE `cuon_sach` SET `trang_thai` = 'SanSang';");
        }

        $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    OperationalSeeder::run();
    echo "Khởi tạo cơ sở vật chất phòng đọc, ca đọc, chỗ ngồi sạch thành công (không có giao dịch mẫu)!\n";
}
