<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Services\AuthorizationService;

/**
 * Middleware Phân Quyền Tập Trung (Authorization Middleware)
 * Kiểm tra quyền bắt buộc của route theo quy chuẩn RBAC (<resource>.<action>)
 */
class AuthorizationMiddleware
{
    private AuthorizationService $authService;

    public function __construct(?AuthorizationService $authService = null)
    {
        $this->authService = $authService ?? new AuthorizationService();
    }

    /**
     * Kiểm tra quyền truy cập route
     * @param string $requiredRule Quy tắc kiểm tra (ví dụ: 'role:STUDENT', 'permission:setting.manage', hoặc 'setting.manage')
     */
    public function handle(string $requiredRule): bool
    {
        Session::start();

        if (empty($requiredRule)) {
            $this->forbidden('Yêu cầu quy tắc phân quyền hợp lệ.');
            return false;
        }

        $userId = Session::getUserId();
        if ($userId === null) {
            $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
                || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

            http_response_code(401);
            if ($isJson) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'error' => 'Vui lòng đăng nhập để tiếp tục.', 'code' => 401], JSON_UNESCAPED_UNICODE);
            } else {
                header('Location: /login?error=' . urlencode('Vui lòng đăng nhập để tiếp tục.'));
            }
            exit;
        }

        $currentUserRole = Session::getRoleCode() ?? 'UNKNOWN';

        // 1. Kiểm tra theo Vai trò (Role guard, ví dụ: "role:STUDENT")
        if (str_starts_with($requiredRule, 'role:')) {
            $allowedRoles = array_map('strtoupper', array_map('trim', explode(',', substr($requiredRule, 5))));
            if (!in_array($currentUserRole, $allowedRoles, true)) {
                $roleDesc = match($currentUserRole) {
                    'ADMIN' => 'Quản trị viên (ADMIN)',
                    'LIBRARIAN' => 'Thủ thư (LIBRARIAN)',
                    'STUDENT' => 'Sinh viên (STUDENT)',
                    default => $currentUserRole
                };
                $requiredDesc = implode(', ', $allowedRoles);
                $this->forbidden("Trang này chỉ dành cho vai trò [{$requiredDesc}]. Vai trò hiện tại của bạn là [{$roleDesc}].");
                return false;
            }
            return true;
        }

        // 2. Kiểm tra theo Quyền (Permission guard, ví dụ: "permission:setting.manage" hoặc "setting.manage")
        $permissionCode = str_starts_with($requiredRule, 'permission:')
            ? substr($requiredRule, 11)
            : $requiredRule;

        if (!$this->authService->hasPermission($userId, $permissionCode)) {
            $this->forbidden('Bạn không có quyền thực hiện chức năng này (yêu cầu quyền: ' . htmlspecialchars($permissionCode, ENT_QUOTES, 'UTF-8') . ').');
            return false;
        }

        return true;
    }

    private function forbidden(string $message): void
    {
        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        http_response_code(403);

        if ($isJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'error' => $message, 'code' => 403], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Với HTML: Lấy URL Dashboard an toàn tương ứng với vai trò của người dùng
        $roleCode = Session::getRoleCode();
        $dashboardUrl = match ($roleCode) {
            'ADMIN' => '/admin/settings',
            'LIBRARIAN' => '/librarian',
            'STUDENT' => '/profile',
            default => '/',
        };
        $dashboardLabel = match ($roleCode) {
            'ADMIN' => 'Quay lại Bảng Quản trị (Admin)',
            'LIBRARIAN' => 'Quay lại Bảng điều khiển Thủ thư',
            'STUDENT' => 'Quay lại Trang Cá nhân Sinh viên',
            default => 'Quay về Trang chủ',
        };

        // Render giao diện 403 trực quan, bảo toàn Session hoàn toàn
        echo '<!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>403 Cấm truy cập · Thư viện Khoa Văn học</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; color: #1e293b; }
                .card { background: white; padding: 48px 40px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center; max-width: 520px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
                .icon { width: 64px; height: 64px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #dc2626; font-size: 28px; }
                h1 { color: #0f172a; font-size: 26px; margin: 0 0 10px; font-weight: 700; }
                .code { color: #dc2626; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
                p { color: #64748b; font-size: 15px; line-height: 1.6; margin: 0 0 28px; }
                .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
                .btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 22px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; transition: all 0.2s; }
                .btn-primary { background: #1e3a8a; color: white; }
                .btn-primary:hover { background: #172554; transform: translateY(-1px); }
                .btn-secondary { background: #f1f5f9; color: #334155; }
                .btn-secondary:hover { background: #e2e8f0; }
            </style>
        </head>
        <body>
            <div class="card">
                <div class="icon">⛔</div>
                <div class="code">HTTP 403 Forbidden</div>
                <h1>Không đủ quyền truy cập</h1>
                <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
                <div class="actions">
                    <a href="' . htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-primary">← ' . htmlspecialchars($dashboardLabel, ENT_QUOTES, 'UTF-8') . '</a>
                    <a href="/" class="btn btn-secondary">Về trang chủ</a>
                </div>
            </div>
        </body>
        </html>';

        exit;
    }
}
