<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use App\Services\ImageOptimizerService;

/**
 * Controller Xử lý Hoàn thiện hồ sơ & Duyệt thẻ Sinh viên (Onboarding Controller)
 */
class OnboardingController
{
    private UserRepository $userRepo;
    private ImageOptimizerService $optimizer;
    private AuditService $auditService;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?ImageOptimizerService $optimizer = null,
        ?AuditService $auditService = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->optimizer = $optimizer ?? new ImageOptimizerService();
        $this->auditService = $auditService ?? new AuditService();
    }

    /**
     * Màn hình Đăng ký / Hoàn thiện hồ sơ sinh viên (/onboarding)
     */
    public function index(): void
    {
        Session::start();
        if (!Session::isAuthenticated()) {
            header('Location: /login?error=' . urlencode('Vui lòng đăng nhập để tiếp tục.'));
            exit;
        }

        $userId = Session::getUserId();
        if ($userId === null) {
            header('Location: /login');
            exit;
        }

        $user = $this->userRepo->findById($userId);
        if ($user === null) {
            Session::destroy();
            header('Location: /login');
            exit;
        }

        $classes = $this->userRepo->getClassesList();
        $faculties = $this->userRepo->getFacultiesList();
        $majors = $this->userRepo->getMajorsList();
        $error = isset($_GET['error']) ? trim((string)$_GET['error']) : null;
        $success = isset($_GET['success']) && $_GET['success'] === '1';

        $viewPath = dirname(__DIR__, 2) . '/resources/views/onboarding/index.php';
        require $viewPath;
    }

    /**
     * Tiếp nhận và xử lý gửi hồ sơ kèm nén ảnh thẻ sinh viên (POST /onboarding/submit)
     */
    public function submit(): void
    {
        Session::start();
        if (!Session::isAuthenticated()) {
            $this->redirectWithError('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
            return;
        }

        $userId = Session::getUserId();
        if ($userId === null) {
            $this->redirectWithError('Không xác định được danh tính người dùng.');
            return;
        }

        // Kiểm tra mã bảo mật chống tấn công CSRF
        $csrfToken = $_POST['csrf_token'] ?? null;
        if (!Session::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) {
            $this->redirectWithError('Mã bảo mật phiên làm việc (CSRF) không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang.');
            return;
        }

        // Điều tiết tốc độ gửi hồ sơ (Rate Limiting: tối đa 5 lần / phút)
        $rateKey = "onboarding:user:{$userId}";
        if (\App\Core\RateLimiter::tooManyAttempts($rateKey, 5)) {
            $wait = \App\Core\RateLimiter::availableIn($rateKey);
            $this->redirectWithError("Bạn đã gửi hồ sơ quá nhiều lần. Vui lòng thử lại sau {$wait} giây.");
            return;
        }
        \App\Core\RateLimiter::hit($rateKey, 60);

        $user = $this->userRepo->findById($userId);
        if ($user === null) {
            $this->redirectWithError('Tài khoản không tồn tại trong hệ thống.');
            return;
        }

        // 1. Trích xuất và chuẩn hóa dữ liệu biểu mẫu
        $mssv = strtoupper(trim((string)($_POST['mssv'] ?? '')));
        $hoTen = trim((string)($_POST['ho_ten'] ?? ''));
        $tenLop = trim((string)($_POST['ten_lop'] ?? $_POST['lop'] ?? ''));
        $idNganh = (int)($_POST['id_nganh'] ?? $_POST['nganh'] ?? 1);
        $khoa = trim((string)($_POST['khoa'] ?? 'Khoa Văn học'));
        $gioiTinh = trim((string)($_POST['gioi_tinh'] ?? 'Nam'));
        $ngaySinh = trim((string)($_POST['ngay_sinh'] ?? ''));
        $soDienThoai = trim((string)($_POST['so_dien_thoai'] ?? ''));

        // 2. Kiểm tra tính hợp lệ (Validation)
        if ($mssv === '') {
            $this->redirectWithError('Mã số sinh viên không được để trống.');
            return;
        }
        if (!preg_match('/^[A-Z0-9_\-\.]{4,20}$/i', $mssv)) {
            $this->redirectWithError('Mã số sinh viên phải từ 4-20 ký tự (chữ và số).');
            return;
        }
        if (mb_strlen($hoTen) < 2) {
            $this->redirectWithError('Họ và tên sinh viên phải có ít nhất 2 ký tự.');
            return;
        }
        if ($tenLop === '') {
            $this->redirectWithError('Vui lòng nhập tên Lớp sinh hoạt của bạn.');
            return;
        }
        if ($soDienThoai === '') {
            $this->redirectWithError('Số điện thoại liên hệ là bắt buộc, vui lòng không để trống.');
            return;
        }
        if (!preg_match('/^[0-9]{9,12}$/', $soDienThoai)) {
            $this->redirectWithError('Số điện thoại không hợp lệ (phải từ 9-12 chữ số).');
            return;
        }
        if (!in_array($gioiTinh, ['Nam', 'Nu', 'Khac'], true)) {
            $gioiTinh = 'Nam';
        }
        if ($ngaySinh === '' || !strtotime($ngaySinh)) {
            $this->redirectWithError('Ngày sinh không hợp lệ.');
            return;
        }

        // Tự động tìm hoặc tạo lớp theo tên lớp tự điền và ngành học đã chọn
        $idLop = $this->userRepo->findOrCreateClass($tenLop, $idNganh);

        // 3. Xử lý tải lên và tối ưu ảnh thẻ sinh viên lưu dưới tên {mssv}.jpg
        $cardUrl = null;
        $cleanMssv = preg_replace('/[^a-zA-Z0-9_-]/', '', $mssv);
        $cardFilename = "{$cleanMssv}.jpg";

        if (isset($_FILES['anh_the_sv']) && $_FILES['anh_the_sv']['error'] === UPLOAD_ERR_OK) {
            try {
                $optResult = $this->optimizer->optimizeUploadedFile(
                    file: $_FILES['anh_the_sv'],
                    subDir: 'student_cards',
                    prefix: 'card_' . $userId,
                    maxWidth: 1000,
                    maxHeight: 1000,
                    quality: 85,
                    customFilename: $cardFilename
                );

                if (!$optResult['success']) {
                    $this->redirectWithError($optResult['error'] ?? 'Lỗi xử lý ảnh thẻ sinh viên.');
                    return;
                }

                // Lưu tên file mssv.jpg vào cơ sở dữ liệu theo yêu cầu
                $cardUrl = $cardFilename;
            } catch (\Throwable $e) {
                $this->redirectWithError('Lỗi xử lý ảnh: ' . $e->getMessage());
                return;
            }
        } else {
            // Nếu không upload ảnh mới, kiểm tra xem đã có ảnh thẻ trước đó chưa
            if (!empty($user['anh_the_sv'])) {
                $cardUrl = $user['anh_the_sv'];
            } else {
                $this->redirectWithError('Vui lòng chụp và tải lên ảnh thẻ sinh viên để thư viện xét duyệt.');
                return;
            }
        }

        // 4. Lưu thông tin hồ sơ và đặt tài khoản ở trạng thái Chờ duyệt (ChoDuyet)
        $this->userRepo->submitStudentCardProfile(
            userId: $userId,
            mssv: $mssv,
            hoTen: $hoTen,
            idLop: $idLop,
            gioiTinh: $gioiTinh,
            ngaySinh: $ngaySinh,
            soDienThoai: $soDienThoai,
            anhTheSvUrl: $cardUrl
        );

        // 5. Ghi nhật ký kiểm toán hệ thống
        $this->auditService->log(
            userId: $userId,
            action: 'NopTheSinhVien',
            targetTable: 'ho_so_nguoi_dung',
            targetId: (string)$userId,
            description: "Sinh viên nộp hồ sơ thẻ ({$mssv} - {$hoTen}) chuyển sang trạng thái chờ duyệt"
        );

        // 6. Đồng bộ lại dữ liệu trong Session người dùng
        $updatedUser = $this->userRepo->findById($userId);
        if ($updatedUser !== null) {
            Session::setAuthUser($updatedUser);
        }

        header('Location: /onboarding?success=1');
        exit;
    }

    private function redirectWithError(string $message): void
    {
        header('Location: /onboarding?error=' . urlencode($message));
        exit;
    }
}
