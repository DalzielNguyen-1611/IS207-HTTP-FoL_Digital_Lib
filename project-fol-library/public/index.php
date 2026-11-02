<?php

declare(strict_types=1);

// Nạp Autoloader độc lập chuẩn PSR-4
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Tải biến môi trường từ .env nếu tồn tại
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $trimmed, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!isset($_ENV[$key])) {
            $_ENV[$key] = $value;
        }
        putenv("{$key}={$value}");
    }
}

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

// Phục vụ tài nguyên tải lên (ảnh thẻ sinh viên, ảnh bìa) từ storage/uploads/ an toàn
if (str_starts_with($path, '/uploads/')) {
    $uploadRoot = realpath(dirname(__DIR__) . '/storage/uploads');
    $subPath = substr($path, strlen('/uploads/'));
    $targetFile = realpath(dirname(__DIR__) . '/storage/uploads/' . rawurldecode($subPath));

    if (
        $uploadRoot !== false
        && $targetFile !== false
        && str_starts_with($targetFile, $uploadRoot . DIRECTORY_SEPARATOR)
        && is_file($targetFile)
    ) {
        $mime = mime_content_type($targetFile) ?: 'application/octet-stream';
        header("Content-Type: {$mime}");
        header('Cache-Control: public, max-age=86400');
        readfile($targetFile);
        exit;
    }
}

// Phục vụ trực tiếp ảnh thẻ sinh viên lưu theo định dạng {mssv}.jpg
if (preg_match('#^/([a-zA-Z0-9_\-\.]+\.(jpg|jpeg|png|webp))$#i', $path, $matches)) {
    $cardPath = dirname(__DIR__) . '/storage/uploads/student_cards/' . $matches[1];
    if (file_exists($cardPath) && is_file($cardPath)) {
        $mime = mime_content_type($cardPath) ?: 'image/jpeg';
        header("Content-Type: {$mime}");
        header('Cache-Control: public, max-age=86400');
        readfile($cardPath);
        exit;
    }
}

// -----------------------------------------------------------------------------
// XỬ LÝ XÁC THỰC VÀ PHÂN QUYỀN RBAC TẠI BACKEND
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
// XỬ LÝ XÁC THỰC VÀ PHÂN QUYỀN RBAC TẠI BACKEND
// -----------------------------------------------------------------------------

// 1. Chống leo thang đặc quyền / giả mạo vai trò qua request (AUTH-10)
// Không chấp nhận bất kỳ thay đổi vai trò hoặc tài khoản tùy tiện từ client
if (isset($_GET['switch_role']) || isset($_POST['role']) || isset($_GET['role']) || isset($_POST['id_vai_tro'])) {
    // Quyền hạn luôn được xác định nghiêm ngặt từ CSDL và Session máy chủ
}

// 2. Xử lý Đăng xuất
if ($path === '/logout') {
    $authController = new \App\Controllers\AuthController();
    $authController->logout();
}

// 2.1. Khởi tạo luồng Google OAuth 2.0 (Redirect sang accounts.google.com)
if ($path === '/auth/google') {
    $authController = new \App\Controllers\AuthController();
    $authController->googleRedirect();
}

// 2.2. Tiếp nhận phản hồi xác thực từ Google (Google OAuth Callback)
if ($path === '/auth/google/callback') {
    $authController = new \App\Controllers\AuthController();
    $authController->googleCallback();
}

// 2.3. Điều hướng người dùng đã đăng nhập nếu truy cập trang /login hoặc /register (Guest Guard)
if (($path === '/login' || $path === '/register') && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (\App\Core\Session::isAuthenticated()) {
        $role = \App\Core\Session::getRoleCode();
        $redirectUrl = match ($role) {
            'ADMIN' => '/admin/settings',
            'LIBRARIAN' => '/librarian',
            default => '/profile',
        };
        header("Location: {$redirectUrl}");
        exit;
    }
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
// Mặc định từ chối nếu không đủ quyền tại Backend (AUTH-01 đến AUTH-05)
// -----------------------------------------------------------------------------
$routeGuards = [
    // Trang dành riêng cho Sinh viên
    '/profile' => 'role:STUDENT',
    '/onboarding' => 'role:STUDENT',
    '/onboarding/submit' => 'role:STUDENT',
    '/api/reading-room/bookings' => 'role:STUDENT',
    '/api/reading-room/bookings/create' => 'role:STUDENT',
    '/api/reading-room/bookings/cancel' => 'role:STUDENT',

    // Trang nghiệp vụ Thủ thư
    '/librarian' => 'permission:loan.confirm',
    '/librarian/students' => 'permission:loan.confirm',
    '/librarian/students/approve' => 'permission:loan.confirm',
    '/librarian/students/reject' => 'permission:loan.confirm',
    '/librarian/books' => 'permission:book.inventory',
    '/librarian/books/detail' => 'permission:book.view',
    '/librarian/books/next-id' => 'permission:book.view',
    '/librarian/books/create' => 'permission:book.create',
    '/librarian/books/update' => 'permission:book.update',
    '/librarian/books/delete' => 'permission:book.delete',
    '/librarian/counter' => 'permission:loan.confirm',
    '/librarian/rooms' => 'permission:room.manage',
    '/librarian/reports' => 'permission:loan.confirm',

    // Trang Quản trị viên
    '/admin/users' => 'permission:user.view',
    '/admin/audit' => 'permission:audit.view',
    '/admin/settings' => 'permission:setting.manage',
];

if (isset($routeGuards[$path])) {
    $requiredGuard = $routeGuards[$path];

    // 1. Kiểm tra danh tính & trạng thái tài khoản thời gian thực (AUTH-01, AUTH-12)
    $authMiddleware = new \App\Middleware\AuthenticationMiddleware();
    if ($authMiddleware->handle()) {
        // 2. Kiểm tra phân quyền RBAC (vai trò hoặc mã quyền) (AUTH-02 đến AUTH-05)
        $rbacMiddleware = new \App\Middleware\AuthorizationMiddleware();
        $rbacMiddleware->handle($requiredGuard);
    }
}

// -----------------------------------------------------------------------------
// ĐIỀU PHỐI CONTROLLERS
// -----------------------------------------------------------------------------
if ($path === '/api/reading-room/bookings' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new \App\Controllers\RoomBookingController())->index();
    exit;
}

if ($path === '/api/reading-room/bookings/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new \App\Controllers\RoomBookingController())->create();
    exit;
}

if ($path === '/api/reading-room/bookings/cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new \App\Controllers\RoomBookingController())->cancel();
    exit;
}

if ($path === '/librarian' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->index();
    exit;
}

if ($path === '/librarian/books' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->books();
    exit;
}

if ($path === '/librarian/books/detail' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->getBookDetail();
    exit;
}

if ($path === '/librarian/books/next-id' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->getNextBookId();
    exit;
}

if ($path === '/librarian/books/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->createBook();
    exit;
}

if ($path === '/librarian/books/update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->updateBook();
    exit;
}

if ($path === '/librarian/books/delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->deleteBook();
    exit;
}

if ($path === '/librarian/students' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->students();
    exit;
}

if ($path === '/librarian/students/approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->approveStudent();
    exit;
}

if ($path === '/librarian/students/reject' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $librarianController = new \App\Controllers\LibrarianController();
    $librarianController->rejectStudent();
    exit;
}

// -----------------------------------------------------------------------------
// ĐIỀU PHỐI ONBOARDING SINH VIÊN (ONBOARDING CONTROLLER)
// -----------------------------------------------------------------------------
if ($path === '/onboarding' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $onboardingController = new \App\Controllers\OnboardingController();
    $onboardingController->index();
    exit;
}

if ($path === '/onboarding/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $onboardingController = new \App\Controllers\OnboardingController();
    $onboardingController->submit();
    exit;
}

// -----------------------------------------------------------------------------
// ĐIỀU PHỐI CONTROLLER TRA CỨU SÁCH (BOOK CONTROLLER)
// -----------------------------------------------------------------------------
if ($path === '/books' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookController = new \App\Controllers\BookController();
    $bookController->index();
    exit;
}

// -----------------------------------------------------------------------------
// ÁNH XẠ GIAO DIỆN VIEWS
// -----------------------------------------------------------------------------
$routes = [
    '/' => dirname(__DIR__) . '/resources/views/home/index.php',
    '/about' => dirname(__DIR__) . '/resources/views/about/index.php',
    '/rules' => dirname(__DIR__) . '/resources/views/rules/index.php',
    '/feedback' => dirname(__DIR__) . '/resources/views/feedback/index.php',
    '/books' => dirname(__DIR__) . '/resources/views/books/index.php',
    '/book-detail' => dirname(__DIR__) . '/resources/views/book-detail/index.php',
    '/collections' => dirname(__DIR__) . '/resources/views/collections/index.php',
    '/contact' => dirname(__DIR__) . '/resources/views/contact/index.php',
    '/login' => dirname(__DIR__) . '/resources/views/login/index.php',
    '/register' => dirname(__DIR__) . '/resources/views/register/index.php',
    '/reading-room' => dirname(__DIR__) . '/resources/views/reading-room/index.php',
    '/profile' => dirname(__DIR__) . '/resources/views/profile/index.php',
    '/onboarding' => dirname(__DIR__) . '/resources/views/onboarding/index.php',
    '/librarian' => dirname(__DIR__) . '/resources/views/librarian/index.php',
    '/librarian/students' => dirname(__DIR__) . '/resources/views/librarian-students/index.php',
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

// Hiển thị View của trang được yêu cầu
require $view;