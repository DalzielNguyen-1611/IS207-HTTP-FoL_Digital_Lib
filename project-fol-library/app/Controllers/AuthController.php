<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use App\Services\GoogleAuthService;
use App\Validators\AuthValidator;
use DomainException;
use Throwable;

/**
 * Controller Xử lý Xác thực Người dùng (Auth Controller)
 */
class AuthController
{
    private UserRepository $userRepo;
    private AuditService $auditService;
    private GoogleAuthService $googleAuthService;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?AuditService $auditService = null,
        ?GoogleAuthService $googleAuthService = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->auditService = $auditService ?? new AuditService();
        $this->googleAuthService = $googleAuthService ?? new GoogleAuthService();
    }

    /**
     * Xử lý đăng nhập bằng email và mật khẩu
     */
    public function login(array $data): array
    {
        $errors = AuthValidator::validateLogin($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $email = trim($data['email']);
        $password = (string)($data['mat_khau'] ?? $data['password'] ?? '');

        $user = $this->userRepo->findByEmail($email);
        if ($user === null) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.'];
        }

        // Kiểm tra trạng thái tài khoản
        if (($user['trang_thai'] ?? '') !== 'HoatDong') {
            return ['success' => false, 'message' => 'Tài khoản của bạn đang bị khóa hoặc đình chỉ.'];
        }

        // Kiểm tra mật khẩu (hỗ trợ hash bcrypt)
        if (!password_verify($password, $user['mat_khau'] ?? '')) {
            $this->auditService->log(
                userId: (int)$user['id_tai_khoan'],
                action: 'DangNhapThatBai',
                targetTable: 'tai_khoan',
                targetId: (string)$user['id_tai_khoan'],
                description: 'Đăng nhập sai mật khẩu'
            );
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.'];
        }

        // Thiết lập Session đăng nhập an toàn
        Session::setAuthUser($user);

        // Ghi nhận nhật ký kiểm toán
        $this->auditService->log(
            userId: (int)$user['id_tai_khoan'],
            action: 'DangNhap',
            targetTable: 'tai_khoan',
            targetId: (string)$user['id_tai_khoan'],
            description: 'Đăng nhập thành công với vai trò ' . ($user['ma_vai_tro'] ?? '')
        );

        // Xác định đường dẫn điều hướng theo vai trò
        $redirectUrl = match ($user['ma_vai_tro'] ?? '') {
            'ADMIN' => '/admin/settings',
            'LIBRARIAN' => '/librarian',
            default => '/profile',
        };

        return [
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'redirect' => $redirectUrl,
            'user' => [
                'id' => $user['id_tai_khoan'],
                'email' => $user['email'],
                'role' => $user['ma_vai_tro'],
                'name' => $user['ho_ten'] ?? $user['email']
            ]
        ];
    }

    /**
     * Bắt đầu luồng Google OAuth 2.0 (Redirect đến accounts.google.com với prompt=select_account)
     */
    public function googleRedirect(): void
    {
        if (!$this->googleAuthService->isConfigured()) {
            // Nếu chưa điền Client ID và Secret trong config/auth.php, hiển thị thông báo hướng dẫn rõ ràng
            header('Location: /login?error=' . urlencode('Chưa cấu hình GOOGLE_CLIENT_ID và GOOGLE_CLIENT_SECRET trong config/auth.php hoặc .env. Vui lòng thiết lập để kết nối Google Cloud.'));
            exit;
        }

        // Tạo mã CSRF state ngẫu nhiên cho mỗi phiên đăng nhập
        $state = bin2hex(random_bytes(32));
        $_SESSION['google_oauth_state'] = $state;
        Session::writeClose();

        $authUrl = $this->googleAuthService->createAuthUrl($state);
        header("Location: {$authUrl}");
        exit;
    }

    /**
     * Xử lý callback trả về từ Google (accounts.google.com/o/oauth2/v2/auth)
     */
    public function googleCallback(): void
    {
        $expectedState = (string)($_SESSION['google_oauth_state'] ?? '');
        $receivedState = (string)($_GET['state'] ?? '');
        unset($_SESSION['google_oauth_state']);

        // 1. Chống tấn công giả mạo yêu cầu (CSRF Protection)
        if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
            header('Location: /login?error=' . urlencode('Yêu cầu xác thực không hợp lệ hoặc đã hết hạn (CSRF error). Vui lòng thử lại.'));
            exit;
        }

        // 2. Kiểm tra nếu người dùng hủy tại màn hình chọn tài khoản Google
        if (isset($_GET['error']) || empty($_GET['code'])) {
            header('Location: /login?error=' . urlencode('Bạn đã hủy quá trình chọn tài khoản Google.'));
            exit;
        }

        try {
            // 3. Trao đổi authorization code và xác minh token từ Google
            $payload = $this->googleAuthService->exchangeAndVerify((string)$_GET['code']);
            $email = $payload['email'];
            $googleSub = $payload['sub'];

            // 4. Kiểm tra quyền truy cập:
            // - Tài khoản sinh viên: Chỉ cho phép các domain sinh viên được cấu hình (@gm.uit.edu.vn, @sv.ussh.edu.vn)
            // - Tài khoản quản trị viên: Khai báo trong GOOGLE_ADMIN_EMAILS hoặc đã được gán vai trò ADMIN trong CSDL
            $isStudentDomain = $this->googleAuthService->isStudentDomain($email);
            $isAdminConfig = $this->googleAuthService->isAdminEmail($email);

            // 5. Kiểm tra tài khoản trong CSDL (theo google_sub trước, sau đó theo email)
            $user = $this->userRepo->findByGoogleSub($googleSub);
            if ($user === null) {
                $userByEmail = $this->userRepo->findByEmail($email);
                if ($userByEmail !== null) {
                    $this->userRepo->updateGoogleSub((int)$userByEmail['id_tai_khoan'], $googleSub);
                    $user = $this->userRepo->findById((int)$userByEmail['id_tai_khoan']);
                }
            }

            // Kiểm tra xem tài khoản trong CSDL có vai trò ADMIN hay không
            $isDbAdmin = ($user !== null && ($user['ma_vai_tro'] ?? '') === 'ADMIN');
            $isAdmin = $isAdminConfig || $isDbAdmin;

            // Nếu không phải domain sinh viên được phép và cũng không phải tài khoản quản trị viên -> Từ chối
            if (!$isStudentDomain && !$isAdmin) {
                header('Location: /login?error=' . urlencode("Email ({$email}) không thuộc danh sách tên miền sinh viên (@gm.uit.edu.vn, @sv.ussh.edu.vn) hoặc chưa được cấp quyền quản trị."));
                exit;
            }

            // Nếu chưa có tài khoản trong hệ thống, tự động tạo mới
            if ($user === null) {
                $targetRole = $isAdmin ? 1 : 3;
                $newId = $this->userRepo->createFromGoogle($payload, $targetRole);
                $user = $this->userRepo->findById($newId);
            } else {
                // Nếu được cấu hình là Admin nhưng DB chưa cập nhật thì đồng bộ vai trò ADMIN
                if ($isAdmin && (int)$user['id_vai_tro'] !== 1) {
                    $this->userRepo->updateRole((int)$user['id_tai_khoan'], 1);
                    $user = $this->userRepo->findById((int)$user['id_tai_khoan']);
                }
            }

            if ($user === null) {
                throw new DomainException('Không thể tải hoặc tạo hồ sơ người dùng từ tài khoản Google.');
            }

            // 6. Kiểm tra trạng thái hoạt động của tài khoản
            if (($user['trang_thai'] ?? '') !== 'HoatDong') {
                header('Location: /login?error=' . urlencode('Tài khoản của bạn đang bị khóa hoặc đình chỉ.'));
                exit;
            }

            // 7. Tái tạo Session ID chống Session Fixation và lưu phiên đăng nhập
            Session::regenerate();
            Session::setAuthUser($user);

            // 8. Ghi nhận nhật ký kiểm toán
            $roleCode = $user['ma_vai_tro'] ?? 'STUDENT';
            $this->auditService->log(
                userId: (int)$user['id_tai_khoan'],
                action: 'DangNhapGoogleOAuth',
                targetTable: 'tai_khoan',
                targetId: (string)$user['id_tai_khoan'],
                description: "Đăng nhập thành công qua Google OAuth với vai trò {$roleCode} ({$email})"
            );

            // 9. Điều hướng theo vai trò (ADMIN -> /admin/settings, LIBRARIAN -> /librarian, STUDENT -> /profile)
            $redirectUrl = match ($roleCode) {
                'ADMIN' => '/admin/settings',
                'LIBRARIAN' => '/librarian',
                default => '/profile',
            };

            Session::writeClose();
            header('Location: ' . $redirectUrl);
            exit;
        } catch (DomainException $e) {
            header('Location: /login?error=' . urlencode($e->getMessage()));
            exit;
        } catch (Throwable $e) {
            error_log('Google OAuth callback error: ' . $e->getMessage());
            $msg = $e->getMessage();
            if (str_contains(strtolower($msg), 'timed out') || str_contains(strtolower($msg), 'could not resolve host')) {
                $friendlyMsg = 'Không thể kết nối đến máy chủ xác thực Google (quá thời gian chờ). Vui lòng kiểm tra lại đường truyền mạng, tắt/bật VPN hoặc đăng nhập bằng tài khoản và mật khẩu bên dưới.';
            } else {
                $friendlyMsg = 'Đăng nhập Google thất bại: ' . $msg;
            }
            header('Location: /login?error=' . urlencode($friendlyMsg));
            exit;
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(): void
    {
        $userId = Session::getUserId();
        if ($userId !== null) {
            $this->auditService->log(
                userId: $userId,
                action: 'DangXuat',
                targetTable: 'tai_khoan',
                targetId: (string)$userId,
                description: 'Người dùng chủ động đăng xuất'
            );
        }

        Session::destroy();
        header('Location: /login');
        exit;
    }
}
