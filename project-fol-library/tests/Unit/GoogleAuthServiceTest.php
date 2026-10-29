<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Services\GoogleAuthService;

echo "========================================================\n";
echo "KIỂM THỬ ĐƠN VỊ GOOGLE AUTH SERVICE (OAUTH 2.0)\n";
echo "========================================================\n";

$passCount = 0;
$totalCount = 0;

function assertTest(bool $condition, string $message): void {
    global $passCount, $totalCount;
    $totalCount++;
    if ($condition) {
        $passCount++;
        echo "[PASS] {$message}\n";
    } else {
        echo "[FAIL] {$message}\n";
    }
}

// 1. Kiểm tra isConfigured()
$unconfigured = new GoogleAuthService([
    'client_id' => '',
    'client_secret' => '',
    'redirect_uri' => 'http://localhost:8000/auth/google/callback'
]);
assertTest(!$unconfigured->isConfigured(), "GoogleAuthService chưa cấu hình phải trả về false");

$configured = new GoogleAuthService([
    'client_id' => 'test-id.apps.googleusercontent.com',
    'client_secret' => 'test-secret',
    'redirect_uri' => 'http://localhost:8000/auth/google/callback',
    'allowed_domains' => ['gm.uit.edu.vn', 'uit.edu.vn', 'ussh.edu.vn'],
    'prompt' => 'select_account'
]);
assertTest($configured->isConfigured(), "GoogleAuthService đã cấu hình phải trả về true");

// 2. Kiểm tra createAuthUrl() có đầy đủ tham số OAuth 2.0 chuẩn của Google
$authUrl = $configured->createAuthUrl('test_random_state_xyz');
assertTest(str_starts_with($authUrl, 'https://accounts.google.com/o/oauth2/v2/auth?'), "Auth URL phải trỏ tới Google Accounts OAuth 2.0 endpoint");
assertTest(str_contains($authUrl, 'prompt=select_account'), "Auth URL bắt buộc phải có prompt=select_account để hiện bảng chọn tài khoản");
assertTest(str_contains($authUrl, 'state=test_random_state_xyz'), "Auth URL phải truyền state CSRF token");
assertTest(str_contains($authUrl, 'client_id=test-id.apps.googleusercontent.com'), "Auth URL phải truyền client_id");
assertTest(str_contains($authUrl, 'scope=openid+email+profile') || str_contains($authUrl, 'scope=openid%20email%20profile'), "Auth URL phải yêu cầu scope openid email profile");

// 3. Kiểm tra kiểm tra domain email
assertTest($configured->isAllowedDomain('24520500@gm.uit.edu.vn'), "Chấp nhận domain sinh viên gm.uit.edu.vn");
assertTest($configured->isAllowedDomain('teacher@uit.edu.vn'), "Chấp nhận domain uit.edu.vn");
assertTest($configured->isAllowedDomain('STUDENT@GM.UIT.EDU.VN'), "Chấp nhận domain không phân biệt hoa thường");
assertTest(!$configured->isAllowedDomain('hacker@unknown-mail.com'), "Từ chối domain lạ không nằm trong danh sách trường");
assertTest(!$configured->isAllowedDomain('fake@fake-uit.edu.vn'), "Từ chối domain mạo danh có chứa chuỗi gần giống");

echo "========================================================\n";
echo "TỔNG KẾT: {$passCount}/{$totalCount} TEST CASES ĐẠT CHUẨN!\n";
if ($passCount === $totalCount) {
    echo "100% PASS - HOÀN THÀNH KIỂM THỬ GOOGLE AUTH SERVICE!\n";
} else {
    exit(1);
}
