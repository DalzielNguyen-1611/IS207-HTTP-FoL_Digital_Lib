<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuditService;
use App\Validators\AuthValidator;

/**
 * Controller Xử lý Xác thực Người dùng (Auth Controller)
 */
class AuthController
{
    private UserRepository $userRepo;
    private AuditService $auditService;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?AuditService $auditService = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->auditService = $auditService ?? new AuditService();
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
     * Xử lý đăng nhập bằng tài khoản Google (dành riêng cho sinh viên)
     */
    public function loginWithGoogle(?string $email = null): array
    {
        $targetEmail = $email ? trim($email) : 'student@gm.uit.edu.vn';

        $user = $this->userRepo->findByEmail($targetEmail);
        if ($user === null) {
            // Tự động khởi tạo tài khoản Sinh viên cho tài khoản Google mới
            try {
                $db = \App\Core\Database::getInstance();
                $stmt = $db->prepare("INSERT INTO `tai_khoan` (`id_vai_tro`, `email`, `mat_khau`, `trang_thai`, `created_at`, `updated_at`) VALUES (3, :email, NULL, 'HoatDong', NOW(), NOW())");
                $stmt->execute(['email' => $targetEmail]);
                $newUserId = (int)$db->lastInsertId();

                $namePart = explode('@', $targetEmail)[0];
                $cleanName = ucwords(str_replace(['.', '_', '-'], ' ', $namePart));
                $mssvGen = 'SV' . str_pad((string)$newUserId, 5, '0', STR_PAD_LEFT);

                $profileStmt = $db->prepare("INSERT INTO `ho_so_nguoi_dung` (`id_tai_khoan`, `mssv`, `ho_ten`, `id_lop`, `gioi_tinh`, `ngay_sinh`, `anh_dai_dien`) VALUES (:id, :mssv, :name, 1, 'Nam', '2005-01-01', '/assets/images/logo.png')");
                $profileStmt->execute([
                    'id' => $newUserId,
                    'mssv' => $mssvGen,
                    'name' => $cleanName
                ]);

                $user = $this->userRepo->findById($newUserId);
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'message' => "Không thể khởi tạo tài khoản cho {$targetEmail}: " . $e->getMessage()
                ];
            }
        }

        // Kiểm tra trạng thái tài khoản
        if (($user['trang_thai'] ?? '') !== 'HoatDong') {
            return [
                'success' => false,
                'message' => 'Tài khoản sinh viên của bạn đang bị khóa hoặc đình chỉ.'
            ];
        }

        // Thiết lập Session đăng nhập an toàn
        Session::setAuthUser($user);

        // Ghi nhận nhật ký kiểm toán
        $this->auditService->log(
            userId: (int)$user['id_tai_khoan'],
            action: 'DangNhapGoogle',
            targetTable: 'tai_khoan',
            targetId: (string)$user['id_tai_khoan'],
            description: "Sinh viên đăng nhập thành công qua tài khoản Google ({$targetEmail})"
        );

        return [
            'success' => true,
            'message' => 'Đăng nhập Google thành công.',
            'redirect' => '/profile',
            'user' => [
                'id' => $user['id_tai_khoan'],
                'email' => $user['email'],
                'role' => $user['ma_vai_tro'],
                'name' => $user['ho_ten'] ?? $user['email']
            ]
        ];
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
