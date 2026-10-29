<?php

declare(strict_types=1);

namespace Database\Seeders;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use PDO;

/**
 * Seeder khởi tạo dữ liệu vận hành mẫu cho phòng đọc, ca đọc, chỗ ngồi và các phiếu mượn thực tế
 */
class OperationalSeeder
{
    public static function run(?PDO $db = null): void
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

        // 4. Lượt đặt phòng hôm nay (CURDATE())
        // Xóa đặt chỗ mẫu cũ của hôm nay để tránh trùng lặp
        $db->exec("DELETE FROM `dat_cho` WHERE `ngay_dat` = CURDATE();");

        $bookingValues = [];
        // Ca 1: 12 chỗ
        for ($s = 1; $s <= 12; $s++) {
            $user = ($s % 2 === 0) ? 3 : 4;
            $bookingValues[] = sprintf(
                "('DC-TODAY-1-%02d', %d, 1, %d, CURDATE(), NOW(), NOW(), NULL, NULL, 'DaDat', 'Đặt chỗ tự động')",
                $s, $s, $user
            );
        }
        // Ca 2: 8 chỗ
        for ($s = 13; $s <= 20; $s++) {
            $user = ($s % 2 === 0) ? 3 : 4;
            $bookingValues[] = sprintf(
                "('DC-TODAY-2-%02d', %d, 2, %d, CURDATE(), NOW(), NOW(), NULL, NULL, 'DaDat', 'Đặt chỗ tự động')",
                $s, $s, $user
            );
        }
        // Ca 3: 10 chỗ
        for ($s = 21; $s <= 30; $s++) {
            $user = ($s % 2 === 0) ? 3 : 4;
            $bookingValues[] = sprintf(
                "('DC-TODAY-3-%02d', %d, 3, %d, CURDATE(), NOW(), NOW(), NULL, NULL, 'DaDat', 'Đặt chỗ tự động')",
                $s, $s, $user
            );
        }
        // Ca 4: 6 chỗ
        for ($s = 31; $s <= 36; $s++) {
            $user = ($s % 2 === 0) ? 3 : 4;
            $bookingValues[] = sprintf(
                "('DC-TODAY-4-%02d', %d, 4, %d, CURDATE(), NOW(), NOW(), NULL, NULL, 'DaDat', 'Đặt chỗ tự động')",
                $s, $s, $user
            );
        }

        $db->exec("INSERT INTO `dat_cho` (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`, `ngay_dat`, `thoi_gian_tao`, `thoi_gian_xac_nhan`, `thoi_gian_checkin`, `thoi_gian_huy`, `trang_thai`, `ghi_chu`) VALUES \n" .
            implode(",\n", $bookingValues) . ";");

        // 5. Phiếu mượn và chi tiết mượn mẫu (Dữ liệu thực tế phản ánh lên Dashboard)
        $db->exec("DELETE FROM `chi_tiet_muon`;");
        $db->exec("DELETE FROM `phieu_muon`;");

        // Phiếu 1: Quá hạn (Mượn cách đây 20 ngày, hạn trả cách đây 6 ngày)
        $db->exec("INSERT INTO `phieu_muon` (`id`, `ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`) VALUES
            (1, 'PM001', 3, 2, DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY), 'DaDuyet', 'Mượn phục vụ chuyên đề văn học');");

        $db->exec("INSERT INTO `chi_tiet_muon` (`id`, `id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`) VALUES
            (1, 1, 'KV-ANT-0001.1', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'DangMuon', 'Đã quá hạn trả 6 ngày');");

        // Phiếu 2: Đã duyệt chờ sinh viên đến quầy nhận sách
        $db->exec("INSERT INTO `phieu_muon` (`id`, `ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`) VALUES
            (2, 'PM002', 4, 2, DATE_SUB(NOW(), INTERVAL 1 HOUR), NOW(), 'DaDuyet', 'Đã duyệt trực tuyến, chờ nhận tại quầy');");

        $db->exec("INSERT INTO `chi_tiet_muon` (`id`, `id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`) VALUES
            (2, 2, 'KV-ESS-0001.1', NULL, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'ChoNhan', 'Chờ sinh viên nhận sách');");

        // Phiếu 3: Chờ cán bộ duyệt mượn
        $db->exec("INSERT INTO `phieu_muon` (`id`, `ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`) VALUES
            (3, 'PM003', 3, NULL, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, 'ChoDuyet', 'Sinh viên đăng ký mượn qua portal');");

        $db->exec("INSERT INTO `chi_tiet_muon` (`id`, `id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`) VALUES
            (3, 3, 'KV-POE-0001.1', NULL, DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'ChoDuyet', 'Chờ duyệt');");

        // Phiếu 4: Đang mượn hợp lệ bình thường
        $db->exec("INSERT INTO `phieu_muon` (`id`, `ma_phieu`, `id_nguoi_dung`, `id_can_bo`, `ngay_dang_ky`, `ngay_duyet`, `trang_thai`, `ghi_chu`) VALUES
            (4, 'PM004', 4, 2, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 'DaDuyet', 'Mượn đọc nghiên cứu');");

        $db->exec("INSERT INTO `chi_tiet_muon` (`id`, `id_phieu_muon`, `id_cuon_sach`, `ngay_muon`, `han_tra`, `trang_thai`, `ghi_chu`) VALUES
            (4, 4, 'KV-REC-0001.1', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 'DangMuon', 'Đang mượn bình thường');");

        // Cập nhật trạng thái cuốn sách tương ứng
        $db->exec("UPDATE `cuon_sach` SET `trang_thai` = 'DangMuon' WHERE `id_cuon_sach` IN ('KV-ANT-0001.1', 'KV-REC-0001.1');");

        $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    OperationalSeeder::run();
    echo "Khởi tạo dữ liệu vận hành phòng đọc, ca đọc và phiếu mượn thành công!\n";
}
