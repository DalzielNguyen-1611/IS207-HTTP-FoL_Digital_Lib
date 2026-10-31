<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\UserRepository;
use PDO;

/**
 * Kiểm thử Quy trình Xét duyệt Thẻ Sinh viên & Lưu trữ Nén Ảnh (Student Card Approval Test)
 */
class StudentCardApprovalTest
{
    private UserRepository $userRepo;
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
        $this->userRepo = new UserRepository($this->db);
    }

    public function runAll(): array
    {
        $results = [];

        // 1. Kiểm thử tạo tài khoản sinh viên từ Google -> trạng thái ChoDuyet
        $testEmail = 'test.student.' . time() . '@gm.uit.edu.vn';
        $googleProfile = [
            'email' => $testEmail,
            'name' => 'Sinh Viên Kiểm Thử',
            'sub' => 'google_sub_' . time(),
            'picture' => 'https://lh3.googleusercontent.com/test.jpg'
        ];

        $userId = $this->userRepo->createFromGoogle($googleProfile, 3);
        $user = $this->userRepo->findById($userId);

        $results['create_student_defaults_to_choduyet'] = [
            'passed' => ($user !== null && $user['trang_thai'] === 'ChoDuyet'),
            'message' => 'Tài khoản sinh viên mới tạo từ Google phải có trạng thái ChoDuyet'
        ];

        // 2. Kiểm thử danh sách lớp học
        $classes = $this->userRepo->getClassesList();
        $results['get_classes_list_returns_data'] = [
            'passed' => !empty($classes) && isset($classes[0]['id_lop'], $classes[0]['ten_lop'], $classes[0]['ten_nganh']),
            'message' => 'Lấy danh mục Lớp và Chuyên ngành thành công để hiển thị dropdown'
        ];

        // 3. Kiểm thử nộp hồ sơ và ảnh thẻ sinh viên
        $cardUrl = '/uploads/student_cards/card_' . $userId . '_test123.webp';
        $submitOk = $this->userRepo->submitStudentCardProfile(
            userId: $userId,
            mssv: 'SV99999',
            hoTen: 'Nguyễn Văn Test',
            idLop: (int)($classes[0]['id_lop'] ?? 1),
            gioiTinh: 'Nam',
            ngaySinh: '2004-05-15',
            soDienThoai: '0987654321',
            anhTheSvUrl: $cardUrl
        );

        $updatedUser = $this->userRepo->findById($userId);
        $results['submit_student_card_saves_profile'] = [
            'passed' => $submitOk
                && $updatedUser['mssv'] === 'SV99999'
                && $updatedUser['anh_the_sv'] === $cardUrl
                && $updatedUser['trang_thai'] === 'ChoDuyet'
                && !empty($updatedUser['ngay_gui_duyet']),
            'message' => 'Lưu hồ sơ và đường dẫn ảnh thẻ WebP thành công, ghi nhận thời gian gửi duyệt'
        ];

        // 4. Kiểm thử danh sách chờ duyệt của cán bộ
        $pendingList = $this->userRepo->getPendingStudents();
        $found = false;
        foreach ($pendingList as $p) {
            if ((int)$p['id_tai_khoan'] === $userId) {
                $found = true;
                break;
            }
        }
        $results['pending_students_includes_submitted_card'] = [
            'passed' => $found,
            'message' => 'Hồ sơ đã nộp ảnh thẻ xuất hiện chính xác trong danh sách chờ duyệt của thủ thư'
        ];

        // 5. Kiểm thử yêu cầu chụp lại thẻ (Reject with reason)
        $rejectReason = 'Ảnh thẻ bị lóa đèn flash, vui lòng chụp lại rõ nét';
        $rejectOk = $this->userRepo->rejectStudent($userId, $rejectReason);
        $rejectedUser = $this->userRepo->findById($userId);

        $results['reject_student_sets_rejection_reason'] = [
            'passed' => $rejectOk
                && $rejectedUser['ghi_chu_duyet'] === $rejectReason
                && $rejectedUser['trang_thai'] === 'ChoDuyet',
            'message' => 'Ghi nhận lý do yêu cầu chụp lại thẻ, tài khoản vẫn ở trạng thái ChoDuyet'
        ];

        // 6. Kiểm thử phê duyệt kích hoạt thẻ sinh viên (Approve)
        $approveOk = $this->userRepo->approveStudent($userId);
        $approvedUser = $this->userRepo->findById($userId);

        $results['approve_student_activates_account'] = [
            'passed' => $approveOk
                && $approvedUser['trang_thai'] === 'HoatDong'
                && empty($approvedUser['ghi_chu_duyet']),
            'message' => 'Phê duyệt chuyển trạng thái sang HoatDong và xóa trắng ghi chú từ chối'
        ];

        // 7. Sau khi duyệt, sinh viên không còn nằm trong danh sách chờ duyệt
        $pendingAfter = $this->userRepo->getPendingStudents();
        $foundAfter = false;
        foreach ($pendingAfter as $p) {
            if ((int)$p['id_tai_khoan'] === $userId) {
                $foundAfter = true;
                break;
            }
        }
        $results['approved_student_removed_from_pending'] = [
            'passed' => !$foundAfter,
            'message' => 'Sinh viên đã duyệt không còn nằm trong hàng đợi chờ duyệt'
        ];

        // Dọn dẹp tài khoản test
        $this->db->prepare('DELETE FROM `ho_so_nguoi_dung` WHERE `id_tai_khoan` = :id')->execute([':id' => $userId]);
        $this->db->prepare('DELETE FROM `tai_khoan` WHERE `id_tai_khoan` = :id')->execute([':id' => $userId]);

        return $results;
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    echo "=== CHẠY BỘ KIỂM THỬ ĐƠN VỊ: STUDENT CARD APPROVAL TEST ===\n\n";
    $test = new StudentCardApprovalTest();
    $results = $test->runAll();

    $passed = 0;
    $failed = 0;
    foreach ($results as $name => $item) {
        $status = $item['passed'] ? '[PASS]' : '[FAIL]';
        echo sprintf("%-6s %-45s : %s\n", $status, $name, $item['message']);
        if ($item['passed']) {
            $passed++;
        } else {
            $failed++;
        }
    }

    echo "\n----------------------------------------------------\n";
    echo sprintf("Tổng số test cases: %d | Đạt: %d | Thất bại: %d\n", count($results), $passed, $failed);
    echo "----------------------------------------------------\n";

    if ($failed > 0) {
        exit(1);
    }
}
