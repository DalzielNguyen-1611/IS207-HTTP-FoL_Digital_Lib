<?php

declare(strict_types=1);

// Nạp Autoloader độc lập chuẩn PSR-4
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Khởi động Session bảo mật
\App\Core\Session::start();

// Chuẩn hóa đường dẫn yêu cầu
$requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

$path = '/' . trim(
    is_string($requestPath) ? $requestPath : '/',
    '/'
);

// Phục vụ tài nguyên tĩnh trong public/assets khi chạy PHP server
if (PHP_SAPI === 'cli-server') {
    $assetRoot = realpath(__DIR__ . '/assets');
    $assetPath = realpath(__DIR__ . rawurldecode($path));

    if (
        $assetRoot !== false
        && $assetPath !== false
        && str_starts_with(
            $assetPath,
            $assetRoot . DIRECTORY_SEPARATOR
        )
        && is_file($assetPath)
    ) {
        return false;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ XÁC THỰC VÀ PHÂN QUYỀN RBAC TẠI BACKEND
// -----------------------------------------------------------------------------

// 1. Chuyển đổi vai trò trải nghiệm nhanh (Mock Auth kết nối CSDL thực tế)
$switchRole = $_GET['switch_role'] ?? null;
if ($switchRole !== null && in_array(strtolower($switchRole), ['admin', 'librarian', 'student'], true)) {
    $emailMap = [
        'admin' => 'admin@fol.uit.edu.vn',
        'librarian' => 'librarian@fol.uit.edu.vn',
        'student' => 'student@gm.uit.edu.vn'
    ];
    $email = $emailMap[strtolower($switchRole)];

    try {
        $userRepo = new \App\Repositories\UserRepository();
        $user = $userRepo->findByEmail($email);
        if ($user !== null) {
            \App\Core\Session::setAuthUser($user);

            $auditService = new \App\Services\AuditService();
            $auditService->log(
                userId: (int)$user['id_tai_khoan'],
                action: 'ChuyenVaiTroThuNghiem',
                targetTable: 'tai_khoan',
                targetId: (string)$user['id_tai_khoan'],
                description: 'Đăng nhập vai trò ' . $user['ma_vai_tro'] . ' qua cổng chuyển vai trò'
            );
        }
    } catch (\Throwable $e) {
        // Fallback session nếu DB chưa chạy
        \App\Core\Session::setAuthUser([
            'id_tai_khoan' => match(strtolower($switchRole)) { 'admin' => 1, 'librarian' => 2, default => 3 },
            'id_vai_tro' => match(strtolower($switchRole)) { 'admin' => 1, 'librarian' => 2, default => 3 },
            'email' => $email,
            'ma_vai_tro' => strtoupper($switchRole),
            'trang_thai' => 'HoatDong'
        ]);
    }

    $redirectUrl = match (strtolower($switchRole)) {
        'admin' => '/admin/settings',
        'librarian' => '/librarian',
        default => '/profile',
    };
    header("Location: {$redirectUrl}");
    exit;
}

// 2. Xử lý Đăng xuất
if ($path === '/logout') {
    $authController = new \App\Controllers\AuthController();
    $authController->logout();
}

// 3. Xử lý Đăng nhập POST thật
if ($path === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $authController = new \App\Controllers\AuthController();
    $result = $authController->login($_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
    } else {
        $msg = $result['message'] ?? (!empty($result['errors']) ? reset($result['errors']) : 'Đăng nhập thất bại.');
        $email = urlencode($_POST['email'] ?? '');
        header("Location: /login?error=" . urlencode($msg) . "&email={$email}");
    }
    exit;
}

// -----------------------------------------------------------------------------
// MA TRẬN PHÂN QUYỀN ROUTE (RBAC ENFORCEMENT)
// Mặc định từ chối nếu không đủ quyền tại Backend
// -----------------------------------------------------------------------------
$routePermissions = [
    '/profile' => 'authenticated',
    '/librarian' => 'loan.confirm',
    '/librarian/books' => 'book.inventory',
    '/librarian/counter' => 'loan.confirm',
    '/librarian/rooms' => 'room.manage',
    '/librarian/reports' => 'loan.confirm',
    '/admin/users' => 'user.view',
    '/admin/audit' => 'audit.view',
    '/admin/settings' => 'setting.manage',
];

if (isset($routePermissions[$path])) {
    $requiredPermission = $routePermissions[$path];

    // Kiểm tra danh tính & trạng thái tài khoản
    $authMiddleware = new \App\Middleware\AuthenticationMiddleware();
    if ($authMiddleware->handle()) {
        // Nếu route yêu cầu quyền chuyên biệt <resource>.<action>
        if ($requiredPermission !== 'authenticated') {
            $rbacMiddleware = new \App\Middleware\AuthorizationMiddleware();
            $rbacMiddleware->handle($requiredPermission);
        }
    }
}

// -----------------------------------------------------------------------------
// ÁNH XẠ GIAO DIỆN VIEWS
// -----------------------------------------------------------------------------
$routes = [
    '/' => dirname(__DIR__) . '/resources/views/home/index.php',
    '/books' => dirname(__DIR__) . '/resources/views/books/index.php',
    '/book-detail' => dirname(__DIR__) . '/resources/views/book-detail/index.php',
    '/login' => dirname(__DIR__) . '/resources/views/login/index.php',
    '/register' => dirname(__DIR__) . '/resources/views/register/index.php',
    '/reading-room' => dirname(__DIR__) . '/resources/views/reading-room/index.php',
    '/profile' => dirname(__DIR__) . '/resources/views/profile/index.php',
    '/librarian' => dirname(__DIR__) . '/resources/views/librarian/index.php',
    '/librarian/books' => dirname(__DIR__) . '/resources/views/librarian-books/index.php',
    '/librarian/counter' => dirname(__DIR__) . '/resources/views/librarian-counter/index.php',
    '/librarian/rooms' => dirname(__DIR__) . '/resources/views/librarian-rooms/index.php',
    '/librarian/reports' => dirname(__DIR__) . '/resources/views/librarian-reports/index.php',
    '/admin/users' => dirname(__DIR__) . '/resources/views/admin-users/index.php',
    '/admin/audit' => dirname(__DIR__) . '/resources/views/admin-audit/index.php',
    '/admin/settings' => dirname(__DIR__) . '/resources/views/admin-settings/index.php',
];

$view = $routes[$path] ?? null;

header('Content-Type: text/html; charset=UTF-8');

// Thông báo thân thiện khi route hoặc View không tồn tại
if ($view === null || !is_file($view)) {
    http_response_code(404);

    echo '<!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Không tìm thấy trang</title>
    </head>
    <body>
        <!-- Thông báo trang không tồn tại -->
        <h1>Không tìm thấy trang</h1>
        <p>Đường dẫn này chưa có trong thư viện.</p>
        <a href="/">Về trang chủ</a>
    </body>
    </html>';

    exit;
}

// Giải phóng khóa session trước khi nạp template giao diện
\App\Core\Session::writeClose();

// Hiển thị View của trang được yêu cầu
require $view;