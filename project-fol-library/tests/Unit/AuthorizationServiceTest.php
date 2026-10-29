<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\PermissionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthorizationService;
use App\Services\Policies\BookingPolicy;
use App\Services\Policies\LoanPolicy;

/**
 * Bộ kiểm thử đơn vị cho hệ thống phân quyền RBAC
 * Kiểm tra các nguyên tắc: Deny by default, Least privilege, Resource ownership
 */
class AuthorizationServiceTest
{
    private AuthorizationService $authService;
    private BookingPolicy $bookingPolicy;
    private LoanPolicy $loanPolicy;

    public function __construct()
    {
        $this->authService = new AuthorizationService();
        $this->bookingPolicy = new BookingPolicy($this->authService);
        $this->loanPolicy = new LoanPolicy($this->authService);
    }

    public function runAll(): array
    {
        $results = [];

        $pdo = \App\Core\Database::getConnection();
        $adminId = (int)($pdo->query("SELECT id_tai_khoan FROM tai_khoan WHERE id_vai_tro = 1 LIMIT 1")->fetchColumn() ?: 1);
        $studentId = (int)($pdo->query("SELECT id_tai_khoan FROM tai_khoan WHERE id_vai_tro = 3 LIMIT 1")->fetchColumn() ?: 2);

        $librarianId = (int)$pdo->query("SELECT id_tai_khoan FROM tai_khoan WHERE id_vai_tro = 2 LIMIT 1")->fetchColumn();
        $isTempLibrarian = false;
        if ($librarianId === 0) {
            $pdo->exec("INSERT INTO `tai_khoan` (`id_tai_khoan`, `id_vai_tro`, `email`, `trang_thai`) VALUES (8888, 2, 'temp_lib@test.fol', 'HoatDong') ON DUPLICATE KEY UPDATE `id_vai_tro` = 2, `trang_thai` = 'HoatDong'");
            $librarianId = 8888;
            $isTempLibrarian = true;
        }

        try {

        // 1. Kiểm tra quyền của Admin
        $results['admin_has_setting_manage'] = $this->assert(
            $this->authService->hasPermission($adminId, 'setting.manage') === true,
            'Admin phải có quyền setting.manage'
        );

        $results['admin_has_user_manage'] = $this->assert(
            $this->authService->hasPermission($adminId, 'user.manage') === true,
            'Admin phải có quyền user.manage'
        );

        $results['admin_has_role_ADMIN'] = $this->assert(
            $this->authService->hasRole($adminId, 'ADMIN') === true,
            'Admin phải có vai trò ADMIN'
        );

        // 2. Kiểm tra quyền của Thủ thư
        $results['librarian_has_loan_confirm'] = $this->assert(
            $this->authService->hasPermission($librarianId, 'loan.confirm') === true,
            'Thủ thư phải có quyền xác nhận mượn trả loan.confirm'
        );

        $results['librarian_denied_setting_manage'] = $this->assert(
            $this->authService->hasPermission($librarianId, 'setting.manage') === false,
            'Thủ thư KHÔNG ĐƯỢC có quyền setting.manage (Least privilege)'
        );

        $results['librarian_denied_user_manage'] = $this->assert(
            $this->authService->hasPermission($librarianId, 'user.manage') === false,
            'Thủ thư KHÔNG ĐƯỢC có quyền user.manage'
        );

        // 3. Kiểm tra quyền của Sinh viên
        $results['student_has_room_book'] = $this->assert(
            $this->authService->hasPermission($studentId, 'room.book') === true,
            'Sinh viên phải có quyền đặt chỗ room.book'
        );

        $results['student_denied_book_create'] = $this->assert(
            $this->authService->hasPermission($studentId, 'book.create') === false,
            'Sinh viên KHÔNG ĐƯỢC có quyền tạo sách book.create'
        );

        $results['student_denied_audit_view'] = $this->assert(
            $this->authService->hasPermission($studentId, 'audit.view') === false,
            'Sinh viên KHÔNG ĐƯỢC có quyền xem audit log audit.view'
        );

        // 4. Nguyên tắc Deny by default: Mã quyền không tồn tại hoặc ID âm/0
        $results['deny_unknown_permission'] = $this->assert(
            $this->authService->hasPermission($adminId, 'non_existent.action') === false,
            'Mã quyền không xác định phải bị từ chối mặc định'
        );

        $results['deny_invalid_user_id'] = $this->assert(
            $this->authService->hasPermission(-1, 'book.view') === false,
            'User ID không hợp lệ phải bị từ chối'
        );

        // 5. Kiểm tra Resource-level Policy: Đặt chỗ phòng đọc (BookingPolicy)
        $bookingStudentA = [
            'id_dat_cho' => 101,
            'id_nguoi_dung' => $studentId, // Của sinh viên
            'trang_thai' => 'DaDat'
        ];

        // Sinh viên hủy đơn của chính mình -> ĐƯỢC PHÉP
        $results['student_can_cancel_own_booking'] = $this->assert(
            $this->bookingPolicy->canCancel($studentId, $bookingStudentA) === true,
            'Sinh viên được phép hủy lượt đặt chỗ còn hạn của chính mình'
        );

        // Giả sử có một User ID khác (ví dụ User 9999) cố tình hủy đơn của Sinh viên -> PHẢI BỊ CHẶN
        $results['student_cannot_cancel_other_booking'] = $this->assert(
            $this->bookingPolicy->canCancel(9999, $bookingStudentA) === false,
            'Người dùng khác không được hủy lượt đặt chỗ của sinh viên khác'
        );

        // Thủ thư hủy đơn của sinh viên -> ĐƯỢC PHÉP vì có quyền room.manage
        $results['librarian_can_cancel_any_booking'] = $this->assert(
            $this->bookingPolicy->canCancel($librarianId, $bookingStudentA) === true,
            'Thủ thư có quyền điều phối quản lý và hủy lượt đặt của sinh viên'
        );

        // Đơn đã check-in ('DaNhanCho') thì sinh viên không thể hủy
        $bookingFinished = [
            'id_dat_cho' => 102,
            'id_nguoi_dung' => $studentId,
            'trang_thai' => 'DaNhanCho'
        ];
        $results['cannot_cancel_already_checkedin'] = $this->assert(
            $this->bookingPolicy->canCancel($studentId, $bookingFinished) === false,
            'Không được hủy lượt đặt chỗ đã check-in nhận chỗ'
        );
        } finally {
            if ($isTempLibrarian) {
                $pdo->exec("DELETE FROM `tai_khoan` WHERE `id_tai_khoan` = 8888");
                $pdo->exec("ALTER TABLE `tai_khoan` AUTO_INCREMENT = 3");
            }
        }

        return $results;
    }

    private function assert(bool $condition, string $message): array
    {
        return [
            'passed' => $condition,
            'message' => $message
        ];
    }
}

// Chạy test nếu gọi trực tiếp qua CLI
if (php_sapi_name() === 'cli') {
    $test = new AuthorizationServiceTest();
    $results = $test->runAll();

    $allPassed = true;
    echo "========================================================\n";
    echo "KIỂM THỬ ĐƠN VỊ HỆ THỐNG PHÂN QUYỀN RBAC (FO_LIBRARY)\n";
    echo "========================================================\n";
    foreach ($results as $name => $res) {
        $status = $res['passed'] ? "\033[32m[PASS]\033[0m" : "\033[31m[FAIL]\033[0m";
        echo "{$status} {$name}: {$res['message']}\n";
        if (!$res['passed']) {
            $allPassed = false;
        }
    }
    echo "========================================================\n";
    echo $allPassed ? "TỔNG KẾT: 100% TEST CASES ĐẠT CHUẨN PASS!\n" : "TỔNG KẾT: CÓ TEST CASE THẤT BẠI!\n";
    exit($allPassed ? 0 : 1);
}
