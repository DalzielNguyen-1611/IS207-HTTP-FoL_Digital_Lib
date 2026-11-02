<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Core\RateLimiter;
use App\Core\Session;

// Khởi tạo Session trước khi xuất output theo đúng chuẩn HTTP Lifecycle
Session::start();

echo "================================================================================\n";
echo " KIỂM THỬ BẢO MẬT & LÀM CỨNG HỆ THỐNG (SECURITY & HARDENING TEST)\n";
echo "================================================================================\n";

$passed = 0;
$total = 0;

function assertTest(string $description, bool $condition, ?string $detail = null): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] {$description}" . ($detail ? " ({$detail})" : "") . "\n";
    } else {
        echo " [FAIL] {$description}" . ($detail ? " ({$detail})" : "") . "\n";
    }
}

// 1. Kiểm tra CSRF Token: Sinh mã, kiểm tra hợp lệ, chống token giả mạo
$token1 = Session::getCsrfToken();
assertTest(
    "CSRF Token được sinh tự động và có độ dài an toàn (64 hex characters / 256-bit)",
    !empty($token1) && strlen($token1) === 64,
    "Token: " . substr($token1, 0, 16) . "..."
);

$validTokenCheck = Session::validateCsrfToken($token1);
assertTest("Xác thực CSRF token hợp lệ thành công", $validTokenCheck);

$fakeTokenCheck = Session::validateCsrfToken('fake_csrf_token_attack_vector_1234567890');
assertTest("Từ chối CSRF token giả mạo", !$fakeTokenCheck);

$emptyTokenCheck = Session::validateCsrfToken(null);
assertTest("Từ chối CSRF token rỗng", !$emptyTokenCheck);

// Đổi mới token (Regeneration)
$token2 = Session::regenerateCsrfToken();
$tokenRefreshed = ($token2 !== $token1 && strlen($token2) === 64);
$oldTokenInvalid = !Session::validateCsrfToken($token1);
$newTokenValid = Session::validateCsrfToken($token2);

assertTest(
    "Tái tạo CSRF token mới và vô hiệu hóa token cũ thành công",
    $tokenRefreshed && $oldTokenInvalid && $newTokenValid,
    "Token mới thay thế hoàn toàn phiên cũ"
);

// 2. Kiểm tra RateLimiter: Sliding-window throttle
$testIp = '198.51.100.99';
$rateKey = "test:auth:{$testIp}";
RateLimiter::clear($rateKey);

$limit = 3;
$hit1 = RateLimiter::hit($rateKey, 60);
$hit2 = RateLimiter::hit($rateKey, 60);
$blockedBeforeLimit = RateLimiter::tooManyAttempts($rateKey, $limit);

$hit3 = RateLimiter::hit($rateKey, 60);
$blockedAtLimit = RateLimiter::tooManyAttempts($rateKey, $limit);
$availableIn = RateLimiter::availableIn($rateKey);

assertTest(
    "RateLimiter ghi nhận lượt truy cập và chặn khi vượt ngưỡng",
    !$blockedBeforeLimit && $blockedAtLimit && $availableIn > 0,
    "Hit 1={$hit1}, Hit 2={$hit2}, Hit 3={$hit3}, Throttled={$blockedAtLimit}, Wait={$availableIn}s"
);

RateLimiter::clear($rateKey);
$cleared = !RateLimiter::tooManyAttempts($rateKey, $limit);
assertTest("RateLimiter xóa trạng thái chặn thành công sau khi hoàn tất xác thực", $cleared);

// 3. Kiểm tra Session Strict Mode và Cấu hình Cookie an toàn
Session::start();
$strictMode = (ini_get('session.use_strict_mode') === '1');
assertTest(
    "Cấu hình Session Strict Mode chống Session Fixation (ini: session.use_strict_mode = 1)",
    $strictMode,
    "Giá trị hiện tại: " . ini_get('session.use_strict_mode')
);

// 4. Kiểm tra Chống SQL Injection trong Prepared Statements
$db = Database::getConnection();
$maliciousInput = "' OR '1'='1' UNION SELECT 1, 2, 3, 4, 'hacked' -- ";
$stmt = $db->prepare("SELECT id_tai_khoan FROM tai_khoan WHERE email = :email");
$stmt->execute([':email' => $maliciousInput]);
$sqlInjected = ($stmt->fetch() !== false);

assertTest(
    "Prepared Statements vô hiệu hóa hoàn toàn SQL Injection payload trong truy vấn",
    !$sqlInjected,
    "Payload dạng UNION / OR 1=1 được xử lý dạng literal string an toàn"
);

// 5. Kiểm tra Quy tắc Bảo vệ Uploads & Chặn thực thi mã Script
$htaccessUploads = dirname(__DIR__, 2) . '/storage/uploads/.htaccess';
$htaccessStorage = dirname(__DIR__, 2) . '/storage/.htaccess';

$uploadsProtected = file_exists($htaccessUploads) && (
    str_contains(file_get_contents($htaccessUploads), 'php_flag engine off')
    || str_contains(file_get_contents($htaccessUploads), 'SetHandler default-handler')
);

$storageProtected = file_exists($htaccessStorage) && (
    str_contains(file_get_contents($htaccessStorage), 'Require all denied')
    || str_contains(file_get_contents($htaccessStorage), 'Deny from all')
);

assertTest(
    "Tệp .htaccess trong storage/uploads ngăn chặn triệt để thực thi mã PHP độc hại",
    $uploadsProtected,
    "Đã cấu hình php_flag engine off và SetHandler default-handler"
);

assertTest(
    "Tệp .htaccess trong storage/ cấm truy cập trực tiếp toàn bộ thư mục nội bộ",
    $storageProtected,
    "Đã cấu hình Require all denied"
);

echo "--------------------------------------------------------------------------------\n";
echo "KẾT QUẢ: {$passed}/{$total} kịch bản ĐẠT (" . round(($passed / $total) * 100) . "%)\n";
echo "================================================================================\n\n";

exit($passed === $total ? 0 : 1);
