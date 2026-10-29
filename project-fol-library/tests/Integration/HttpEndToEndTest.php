<?php

declare(strict_types=1);

/**
 * Kiểm thử End-to-End thông qua HTTP Request thực tế đến máy chủ http://localhost:8000
 * Sử dụng cURL để duy trì Cookie jar và kiểm tra chu trình Session thực tế trên mạng
 */

$baseUrl = 'http://localhost:8000';
$cookieFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fol_test_cookies_' . uniqid() . '.txt';

function httpRequest(string $method, string $url, array $data = [], ?string $cookieFile = null, array $headers = []): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Không tự động follow để kiểm tra mã redirect 302
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if ($cookieFile !== null) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return [
            'code' => 0,
            'headers' => '',
            'body' => '',
            'error' => $err
        ];
    }

    $resHeaders = substr($response, 0, $headerSize);
    $resBody = substr($response, $headerSize);

    return [
        'code' => $httpCode,
        'headers' => $resHeaders,
        'body' => $resBody,
        'error' => null
    ];
}

$results = [];

// 1. Khách vãng lai truy cập /profile
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}
$res = httpRequest('GET', "{$baseUrl}/profile", [], $cookieFile);
$hasRedirectToLogin = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /login'));
$results['E2E-01: Khách truy cập /profile bị chuyển hướng đến /login'] = [
    'pass' => $hasRedirectToLogin,
    'detail' => "HTTP {$res['code']}, Redirect to login"
];

// 2. Đăng nhập tài khoản Quản trị viên (Admin)
$loginData = [
    'email' => 'admin@fol.uit.edu.vn',
    'password' => 'Password@123'
];
$res = httpRequest('POST', "{$baseUrl}/login", $loginData, $cookieFile);
$adminLoginSuccess = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /admin/settings'));
$results['E2E-02: Đăng nhập Admin thành công chuyển hướng đến /admin/settings'] = [
    'pass' => $adminLoginSuccess,
    'detail' => "HTTP {$res['code']}, Location: /admin/settings"
];

// 3. Admin truy cập /admin/settings
$res = httpRequest('GET', "{$baseUrl}/admin/settings", [], $cookieFile);
$adminSettingsAccess = ($res['code'] === 200 && str_contains($res['body'], 'Cấu hình hệ thống'));
$results['E2E-03: Admin truy cập bảng cấu hình /admin/settings thành công (HTTP 200)'] = [
    'pass' => $adminSettingsAccess,
    'detail' => "HTTP {$res['code']}, Đã hiển thị trang cấu hình"
];

// 4. Admin truy cập /profile (Admin có thể đảm nhận cả 3 role để test)
$res = httpRequest('GET', "{$baseUrl}/profile", [], $cookieFile);
$adminProfileAccess = ($res['code'] === 200 && str_contains($res['body'], 'Trang cá nhân'));
$results['E2E-04: Admin truy cập /profile thành công để test vai trò Sinh viên (HTTP 200)'] = [
    'pass' => $adminProfileAccess,
    'detail' => "HTTP {$res['code']}, Admin đảm nhận được role Sinh viên để kiểm thử"
];

// 5. Admin quay về trang chủ / (Kiểm tra lỗi mất Session & hiển thị đầy đủ nav link)
$res = httpRequest('GET', "{$baseUrl}/", [], $cookieFile);
$adminSessionPersistsOnHome = (
    $res['code'] === 200
    && str_contains($res['body'], 'Cán bộ')
    && str_contains($res['body'], 'Quản trị')
    && str_contains($res['body'], 'Đăng xuất')
);
$results['E2E-05: Admin quay về trang chủ / VẪN GIỮ NGUYÊN SESSION (Hiện Cán bộ, Quản trị & Đăng xuất)'] = [
    'pass' => $adminSessionPersistsOnHome,
    'detail' => "HTTP {$res['code']}, Session duy trì hoàn hảo"
];

// 6. Admin truy cập /login khi đã đăng nhập (Guest Guard)
$res = httpRequest('GET', "{$baseUrl}/login", [], $cookieFile);
$adminLoginRedirect = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /admin/settings'));
$results['E2E-06: Admin truy cập /login tự động được chuyển về /admin/settings (Guest Guard)'] = [
    'pass' => $adminLoginRedirect,
    'detail' => "HTTP {$res['code']}, Location: /admin/settings"
];

// 7. Admin đăng xuất
$res = httpRequest('GET', "{$baseUrl}/logout", [], $cookieFile);
$adminLogoutSuccess = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /login'));
$results['E2E-07: Admin đăng xuất thành công chuyển hướng về /login'] = [
    'pass' => $adminLogoutSuccess,
    'detail' => "HTTP {$res['code']}, Đã xóa phiên"
];

// 8. Đăng nhập tài khoản Sinh viên
$studentLoginData = [
    'email' => 'student@gm.uit.edu.vn',
    'password' => 'Password@123'
];
$res = httpRequest('POST', "{$baseUrl}/login", $studentLoginData, $cookieFile);
$studentLoginSuccess = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /profile'));
$results['E2E-08: Đăng nhập Sinh viên thành công chuyển hướng đến /profile'] = [
    'pass' => $studentLoginSuccess,
    'detail' => "HTTP {$res['code']}, Location: /profile"
];

// 9. Sinh viên truy cập /profile
$res = httpRequest('GET', "{$baseUrl}/profile", [], $cookieFile);
$studentProfileAccess = ($res['code'] === 200 && str_contains($res['body'], 'Trang cá nhân'));
$results['E2E-09: Sinh viên truy cập trang cá nhân /profile thành công (HTTP 200)'] = [
    'pass' => $studentProfileAccess,
    'detail' => "HTTP {$res['code']}, Đã hiển thị trang sinh viên"
];

// 10. Sinh viên truy cập /admin/settings (Trang quản trị)
$res = httpRequest('GET', "{$baseUrl}/admin/settings", [], $cookieFile);
$studentAdminForbidden = ($res['code'] === 403 && str_contains($res['body'], 'HTTP 403 Forbidden'));
$results['E2E-10: Sinh viên truy cập /admin/settings bị từ chối với mã HTTP 403 Forbidden'] = [
    'pass' => $studentAdminForbidden,
    'detail' => "HTTP {$res['code']}, Bị chặn bởi AuthorizationMiddleware"
];

// 11. Sinh viên quay về trang chủ /
$res = httpRequest('GET', "{$baseUrl}/", [], $cookieFile);
$studentSessionPersistsOnHome = (
    $res['code'] === 200
    && str_contains($res['body'], 'Cá nhân')
    && str_contains($res['body'], 'Đăng xuất')
    && !str_contains($res['body'], 'Quản trị')
);
$results['E2E-11: Sinh viên quay về trang chủ / VẪN GIỮ NGUYÊN SESSION (Hiện Cá nhân & Đăng xuất)'] = [
    'pass' => $studentSessionPersistsOnHome,
    'detail' => "HTTP {$res['code']}, Session sinh viên duy trì an toàn"
];

// 12. Sinh viên đăng xuất
$res = httpRequest('GET', "{$baseUrl}/logout", [], $cookieFile);
$studentLogoutSuccess = ($res['code'] === 302 && str_contains($res['headers'], 'Location: /login'));
$results['E2E-12: Sinh viên đăng xuất thành công chuyển hướng về /login'] = [
    'pass' => $studentLogoutSuccess,
    'detail' => "HTTP {$res['code']}, Đã thu hồi phiên sinh viên"
];

// Dọn dẹp tệp cookie tạm
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// In kết quả
echo "\n";
echo "========================================================================================\n";
echo "KIỂM THỬ END-TO-END HTTP THỰC TẾ TRÊN MÁY CHỦ LIVE (http://localhost:8000)\n";
echo "========================================================================================\n";
$allPass = true;
foreach ($results as $name => $item) {
    $badge = $item['pass'] ? "\033[32m[PASS]\033[0m" : "\033[31m[FAIL]\033[0m";
    echo "{$badge} {$name} ({$item['detail']})\n";
    if (!$item['pass']) {
        $allPass = false;
    }
}
echo "========================================================================================\n";
if ($allPass) {
    echo "\033[32mKẾT QUẢ: 100% KIỂM THỬ LIVE HTTP E2E ĐẠT CHUẨN XUẤT SẮC!\033[0m\n";
} else {
    echo "\033[31mKẾT QUẢ: CÓ TÌNH HUỐNG CHƯA ĐẠT!\033[0m\n";
}
echo "========================================================================================\n\n";

exit($allPass ? 0 : 1);
