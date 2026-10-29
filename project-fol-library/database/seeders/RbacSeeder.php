<?php

declare(strict_types=1);

namespace Database\Seeders;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use PDO;

/**
 * Seeder khởi tạo dữ liệu vai trò, quyền hạn và tài khoản mẫu cho RBAC
 */
class RbacSeeder
{
    public static function run(?PDO $db = null): void
    {
        $db = $db ?? Database::getConnection();

        $db->exec('SET FOREIGN_KEY_CHECKS = 0;');

        // 1. Vai trò
        $db->exec("INSERT INTO `vai_tro` (`id_vai_tro`, `ma_vai_tro`, `ten_vai_tro`, `mo_ta`, `created_at`, `updated_at`) VALUES
            (1, 'ADMIN', 'Quản trị viên', 'Toàn quyền cấu hình hệ thống, quản lý tài khoản, phân quyền và nhật ký kiểm toán', NOW(), NOW()),
            (2, 'LIBRARIAN', 'Thủ thư', 'Quản lý kho ấn phẩm, xử lý phiếu mượn/trả, thu phí phạt và điều phối phòng đọc', NOW(), NOW()),
            (3, 'STUDENT', 'Sinh viên', 'Tra cứu tài liệu, mượn sách, quản lý yêu thích và đăng ký đặt chỗ phòng đọc', NOW(), NOW())
            ON DUPLICATE KEY UPDATE `ten_vai_tro` = VALUES(`ten_vai_tro`), `mo_ta` = VALUES(`mo_ta`), `updated_at` = NOW();");

        // 2. Quyền
        $db->exec("INSERT INTO `quyen` (`id_quyen`, `ma_quyen`, `ten_quyen`, `mo_ta`, `created_at`) VALUES
            (1, 'book.view', 'Xem danh mục sách', 'Xem danh sách và chi tiết thông tin đầu sách, tác giả', CURDATE()),
            (2, 'book.create', 'Thêm đầu sách mới', 'Tạo mới đầu sách trong hệ thống', CURDATE()),
            (3, 'book.update', 'Cập nhật đầu sách', 'Chỉnh sửa thông tin chi tiết của đầu sách', CURDATE()),
            (4, 'book.delete', 'Xóa đầu sách', 'Xóa đầu sách khỏi hệ thống theo chính sách', CURDATE()),
            (5, 'book.inventory', 'Quản lý cuốn sách và vị trí', 'Thêm cuốn sách, in mã vạch, xếp vị trí kệ ô', CURDATE()),
            (6, 'loan.view', 'Xem phiếu mượn', 'Xem danh sách các phiếu mượn sách', CURDATE()),
            (7, 'loan.create', 'Đăng ký mượn sách', 'Tạo yêu cầu mượn ấn phẩm', CURDATE()),
            (8, 'loan.confirm', 'Xác nhận mượn/trả', 'Cán bộ duyệt cấp sách và xác nhận nhận lại sách', CURDATE()),
            (9, 'fine.view', 'Xem phiếu phạt', 'Xem thông tin các khoản phí phạt trễ hạn hoặc hỏng sách', CURDATE()),
            (10, 'fine.manage', 'Quản lý phiếu phạt', 'Lập phiếu phạt và xác nhận thu tiền thanh toán phạt', CURDATE()),
            (11, 'room.view', 'Xem phòng đọc & chỗ ngồi', 'Xem danh sách phòng, ca đọc và sơ đồ ghế ngồi', CURDATE()),
            (12, 'room.book', 'Đặt chỗ phòng đọc', 'Đăng ký giữ chỗ ngồi trong ca đọc', CURDATE()),
            (13, 'room.cancel', 'Hủy đặt chỗ phòng đọc', 'Hủy lượt đặt chỗ theo điều kiện nghiệp vụ', CURDATE()),
            (14, 'room.manage', 'Điều phối phòng đọc', 'Điểm danh, kiểm tra vi phạm và quản lý trạng thái chỗ ngồi', CURDATE()),
            (15, 'user.view', 'Xem danh sách người dùng', 'Xem danh sách tài khoản sinh viên và cán bộ', CURDATE()),
            (16, 'user.manage', 'Quản lý tài khoản', 'Tạo mới, khóa, mở khóa và đình chỉ tài khoản', CURDATE()),
            (17, 'role.manage', 'Quản lý vai trò', 'Cấu hình danh mục vai trò và phân quyền vai trò', CURDATE()),
            (18, 'permission.manage', 'Quản lý quyền hạn', 'Quản trị danh mục quyền hạn trong hệ thống', CURDATE()),
            (19, 'audit.view', 'Xem nhật ký hoạt động', 'Truy vết audit logs các thao tác người dùng', CURDATE()),
            (20, 'setting.manage', 'Cấu hình hệ thống', 'Thiết lập tham số mượn trả, sơ đồ kệ và mẫu thông báo', CURDATE())
            ON DUPLICATE KEY UPDATE `ten_quyen` = VALUES(`ten_quyen`), `mo_ta` = VALUES(`mo_ta`);");

        // 3. Phân quyền vai trò
        $db->exec("DELETE FROM `vai_tro_quyen` WHERE `id_vai_tro` IN (1, 2, 3);");

        $db->exec("INSERT INTO `vai_tro_quyen` (`id_vai_tro`, `id_quyen`) VALUES
            (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10),
            (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20),
            (2, 1), (2, 2), (2, 3), (2, 5), (2, 6), (2, 8), (2, 9), (2, 10), (2, 11), (2, 13), (2, 14),
            (3, 1), (3, 6), (3, 7), (3, 9), (3, 11), (3, 12), (3, 13);");

        // 4. Tài khoản mẫu
        $hash = password_hash('Password@123', PASSWORD_BCRYPT);
        $userStmt = $db->prepare("INSERT INTO `tai_khoan` (`id_tai_khoan`, `id_vai_tro`, `email`, `mat_khau`, `trang_thai`, `created_at`, `updated_at`) VALUES
            (1, 1, 'admin@fol.uit.edu.vn', :h1, 'HoatDong', NOW(), NOW()),
            (2, 2, 'librarian@fol.uit.edu.vn', :h2, 'HoatDong', NOW(), NOW()),
            (3, 3, 'student@gm.uit.edu.vn', :h3, 'HoatDong', NOW(), NOW())
            ON DUPLICATE KEY UPDATE `id_vai_tro` = VALUES(`id_vai_tro`), `mat_khau` = VALUES(`mat_khau`), `trang_thai` = VALUES(`trang_thai`);");
        $userStmt->execute(['h1' => $hash, 'h2' => $hash, 'h3' => $hash]);

        // 5. Ngành, lớp và hồ sơ
        $db->exec("INSERT INTO `nganh` (`id_nganh`, `ten_nganh`) VALUES (1, 'Văn học'), (2, 'Ngôn ngữ học') ON DUPLICATE KEY UPDATE `ten_nganh` = VALUES(`ten_nganh`);");
        $db->exec("INSERT INTO `id_lop` (`id_lop`, `ten_lop`, `id_nganh`) VALUES (1, 'VH2024.1', 1), (2, 'NNH2024.1', 2) ON DUPLICATE KEY UPDATE `ten_lop` = VALUES(`ten_lop`);");
        $db->exec("INSERT INTO `ho_so_nguoi_dung` (`id_ho_so`, `id_tai_khoan`, `mssv`, `ho_ten`, `id_lop`, `gioi_tinh`, `ngay_sinh`, `so_dien_thoai`, `anh_dai_dien`) VALUES
            (1, 1, 'AD0001', 'Quản trị viên Hệ thống', 1, 'Nam', '1990-01-01', '0901234567', '/assets/images/logo.png'),
            (2, 2, 'CB0001', 'Nguyễn Thị Thu Thư', 1, 'Nữ', '1992-05-15', '0912345678', '/assets/images/logo.png'),
            (3, 3, '24520500', 'Nguyễn Đoàn Đức Hiếu', 1, 'Nam', '2006-11-16', '0987654321', '/assets/images/logo.png')
            ON DUPLICATE KEY UPDATE `ho_ten` = VALUES(`ho_ten`), `so_dien_thoai` = VALUES(`so_dien_thoai`);");

        $db->exec('SET FOREIGN_KEY_CHECKS = 1;');
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    RbacSeeder::run();
    echo "Khởi tạo dữ liệu phân quyền RBAC thành công!\n";
}
