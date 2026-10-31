<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Repositories\UserRepository;

/**
 * Middleware Xác Thực Danh Tính (Authentication Middleware)
 * Xác minh phiên đăng nhập và bảo đảm tài khoản đang ở trạng thái HoatDong
 */
class AuthenticationMiddleware
{
    private UserRepository $userRepo;

    public function __construct(?UserRepository $userRepo = null)
    {
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    /**
     * Thực thi kiểm tra xác thực
     * @return bool True nếu hợp lệ, False nếu bị chặn
     */
    public function handle(): bool
    {
        Session::start();

        if (!Session::isAuthenticated()) {
            $this->unauthorized('Bạn cần đăng nhập để truy cập trang này.');
            return false;
        }

        $userId = Session::getUserId();
        if ($userId === null) {
            $this->unauthorized('Phiên làm việc không hợp lệ.');
            return false;
        }

        // Kiểm tra trạng thái tài khoản thời gian thực trong CSDL
        $user = $this->userRepo->findById($userId);
        $status = $user['trang_thai'] ?? '';
        if ($user === null || $status === 'Khoa') {
            Session::destroy();
            $this->unauthorized('Tài khoản của bạn đã bị khóa hoặc không tồn tại.');
            return false;
        }

        // Nếu tài khoản sinh viên đang ở trạng thái chờ duyệt thẻ
        if ($status === 'ChoDuyet' && ($user['ma_vai_tro'] ?? '') === 'STUDENT') {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
            $allowedPendingUris = ['/onboarding', '/onboarding/submit', '/logout'];
            if (!in_array($uri, $allowedPendingUris, true)) {
                $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
                    || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

                if ($isJson) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=UTF-8');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Tài khoản của bạn đang trong trạng thái chờ xét duyệt thẻ sinh viên. Vui lòng hoàn tất hồ sơ tại trang Onboarding.',
                        'redirect' => '/onboarding'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                header('Location: /onboarding');
                exit;
            }
        } elseif ($status !== 'HoatDong') {
            Session::destroy();
            $this->unauthorized('Tài khoản không hợp lệ.');
            return false;
        }

        // Tự động đồng bộ vai trò mới nhất từ CSDL vào session nếu có thay đổi
        $sessionUser = Session::getAuthUser();
        if ($sessionUser !== null) {
            $dbRole = strtoupper(trim((string)($user['ma_vai_tro'] ?? '')));
            if ($dbRole !== '' && ($sessionUser['ma_vai_tro'] ?? '') !== $dbRole) {
                $sessionUser['id_vai_tro'] = $user['id_vai_tro'];
                $sessionUser['ma_vai_tro'] = $dbRole;
                Session::set('user', $sessionUser);
            }
        }

        return true;
    }

    private function unauthorized(string $message): void
    {
        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        http_response_code(401);

        if ($isJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'error' => $message, 'code' => 401], JSON_UNESCAPED_UNICODE);
        } else {
            header('Location: /login?error=' . urlencode($message));
        }

        exit;
    }
}
