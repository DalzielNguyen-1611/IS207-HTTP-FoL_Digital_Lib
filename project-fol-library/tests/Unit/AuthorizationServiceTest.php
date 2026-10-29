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

        // 1. Kiểm tra quyền của Admin (User ID 1)
        $results['admin_has_setting_manage'] = $this->assert(
            $this->authService->hasPermission(1, 'setting.manage') === true,
            'Admin phải có quyền setting.manage'
        );

        $results['admin_has_user_manage'] = $this->assert(
            $this->authService->hasPermission(1, 'user.manage') === true,
            'Admin phải có quyền user.manage'
        );

        $results['admin_has_role_ADMIN'] = $this->assert(
            $this->authService->hasRole(1, 'ADMIN') === true,
            'Admin phải có vai trò ADMIN'
        );

        // 2. Kiểm tra quyền của Thủ thư (User ID 2)
        $results['librarian_has_loan_confirm'] = $this->assert(
            $this->authService->hasPermission(2, 'loan.confirm') === true,
            'Thủ thư phải có quyền xác nhận mượn trả loan.confirm'
        );

        $results['librarian_denied_setting_manage'] = $this->assert(
            $this->authService->hasPermission(2, 'setting.manage') === false,
            'Thủ thư KHÔNG ĐƯỢC có quyền setting.manage (Least privilege)'
        );

        $results['librarian_denied_user_manage'] = $this->assert(
            $this->authService->hasPermission(2, 'user.manage') === false,
            'Thủ thư KHÔNG ĐƯỢC có quyền user.manage'
        );

        // 3. Kiểm tra quyền của Sinh viên (User ID 3)
        $results['student_has_room_book'] = $this->assert(
            $this->authService->hasPermission(3, 'room.book') === true,
            'Sinh viên phải có quyền đặt chỗ room.book'
        );

        $results['student_denied_book_create'] = $this->assert(
            $this->authService->hasPermission(3, 'book.create') === false,
            'Sinh viên KHÔNG ĐƯỢC có quyền tạo sách book.create'
        );

        $results['student_denied_audit_view'] = $this->assert(
            $this->authService->hasPermission(3, 'audit.view') === false,
            'Sinh viên KHÔNG ĐƯỢC có quyền xem audit log audit.view'
        );

        // 4. Nguyên tắc Deny by default: Mã quyền không tồn tại hoặc ID âm/0
        $results['deny_unknown_permission'] = $this->assert(
            $this->authService->hasPermission(1, 'non_existent.action') === false,
            'Mã quyền không xác định phải bị từ chối mặc định'
        );

        $results['deny_invalid_user_id'] = $this->assert(
            $this->authService->hasPermission(-1, 'book.view') === false,
            'User ID không hợp lệ phải bị từ chối'
        );

        // 5. Kiểm tra Resource-level Policy: Đặt chỗ phòng đọc (BookingPolicy)
        $bookingStudentA = [
            'id_dat_cho' => 101,
            'id_nguoi_dung' => 3, // Của sinh viên ID 3
            'trang_thai' => 'DaDat'
        ];

        // Sinh viên 3 hủy đơn của chính mình -> ĐƯỢC PHÉP
        $results['student_can_cancel_own_booking'] = $this->assert(
            $this->bookingPolicy->canCancel(3, $bookingStudentA) === true,
            'Sinh viên được phép hủy lượt đặt chỗ còn hạn của chính mình'
        );

        // Giả sử có một User ID khác (ví dụ User 99) cố tình hủy đơn của Sinh viên 3 -> PHẢI BỊ CHẶN
        $results['student_cannot_cancel_other_booking'] = $this->assert(
            $this->bookingPolicy->canCancel(99, $bookingStudentA) === false,
            'Người dùng khác không được hủy lượt đặt chỗ của sinh viên khác'
        );

        // Thủ thư (User 2) hủy đơn của sinh viên -> ĐƯỢC PHÉP vì có quyền room.manage
        $results['librarian_can_cancel_any_booking'] = $this->assert(
            $this->bookingPolicy->canCancel(2, $bookingStudentA) === true,
            'Thủ thư có quyền điều phối quản lý và hủy lượt đặt của sinh viên'
        );

        // Đơn đã check-in ('DaNhanCho') thì sinh viên không thể hủy
        $bookingFinished = [
            'id_dat_cho' => 102,
            'id_nguoi_dung' => 3,
            'trang_thai' => 'DaNhanCho'
        ];
        $results['cannot_cancel_already_checkedin'] = $this->assert(
            $this->bookingPolicy->canCancel(3, $bookingFinished) === false,
            'Không được hủy lượt đặt chỗ đã check-in nhận chỗ'
        );

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
