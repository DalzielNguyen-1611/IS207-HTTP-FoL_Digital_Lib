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
     * @param string $requiredPermission Mã quyền định dạng <resource>.<action>
     */
    public function handle(string $requiredPermission): bool
    {
        Session::start();

        if (empty($requiredPermission)) {
            $this->forbidden('Yêu cầu cấu hình quyền truy cập hợp lệ.');
            return false;
        }

        $userId = Session::getUserId();
        if ($userId === null) {
            http_response_code(401);
            header('Location: /login?error=' . urlencode('Vui lòng đăng nhập để tiếp tục.'));
            exit;
        }

        if (!$this->authService->hasPermission($userId, $requiredPermission)) {
            $this->forbidden('Bạn không có quyền truy cập chức năng này (yêu cầu: ' . htmlspecialchars($requiredPermission, ENT_QUOTES, 'UTF-8') . ').');
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
        } else {
            echo '<!DOCTYPE html>
            <html lang="vi">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>403 Cấm truy cập</title>
                <style>
                    body { font-family: sans-serif; background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; color: #212529; }
                    .card { background: white; padding: 40px; border-radius: 16px; border: 1px solid #dee2e6; text-align: center; max-width: 480px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
                    h1 { color: #dc3545; font-size: 42px; margin: 0 0 12px; }
                    p { color: #555; line-height: 1.6; margin-bottom: 24px; }
                    a { display: inline-block; background: #1b8846; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h1>403</h1>
                    <h2>Không có quyền truy cập</h2>
                    <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
                    <a href="/">← Quay về Trang chủ</a>
                </div>
            </body>
            </html>';
        }

        exit;
    }
}
