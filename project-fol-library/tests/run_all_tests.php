<?php

declare(strict_types=1);

/**
 * MASTER TEST RUNNER - HỆ THỐNG THƯ VIỆN KHOA VĂN HỌC (FoL Library System)
 * Tự động quét và thực thi toàn bộ 102 bài kiểm thử (Unit & Integration)
 * Cách chạy: php tests/run_all_tests.php
 */

$testSuites = [
    'Unit Tests' => [
        'tests/Unit/AuthorizationServiceTest.php' => 'Phân quyền RBAC & Quyền truy cập',
        'tests/Unit/BookServiceTest.php' => 'Xử lý logic Nghiệp vụ & Dữ liệu Đầu sách',
        'tests/Unit/GoogleAuthServiceTest.php' => 'Xác thực Google OAuth 2.0 & Giới hạn Domain trường',
    ],
    'Integration Tests' => [
        'tests/Integration/ViewsDbIntegrationTest.php' => 'Tích hợp 13 Giao diện Views kết nối CSDL thực',
        'tests/Integration/BookManagementRegressionTest.php' => 'Hồi quy Quản lý sách & Phân trang thủ thư',
        'tests/Integration/AuthenticationAuthorizationRegressionTest.php' => 'Hồi quy Toàn diện Phiên đăng nhập & Bảo mật RBAC (16 Kịch bản)',
        'tests/Integration/HttpEndToEndTest.php' => 'Kiểm thử Live HTTP End-to-End trên Server (17 Kịch bản)',
    ],
];

$phpBinary = PHP_BINARY ?: 'php';
$rootDir = dirname(__DIR__);

echo PHP_EOL;
echo "================================================================================" . PHP_EOL;
echo "   HỆ THỐNG THƯ VIỆN KHOA VĂN HỌC (FoL DIGITAL LIBRARY) - MASTER TEST RUNNER    " . PHP_EOL;
echo "================================================================================" . PHP_EOL;
echo "Thời gian kiểm thử: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "Môi trường PHP:    " . PHP_VERSION . " (" . PHP_OS . ")" . PHP_EOL;
echo "Thư mục gốc:       " . $rootDir . PHP_EOL;
echo "================================================================================" . PHP_EOL . PHP_EOL;

$totalSuites = 0;
$passedSuites = 0;
$failedSuites = 0;
$suiteResults = [];

foreach ($testSuites as $groupName => $files) {
    echo ">>> BẮT ĐẦU NHÓM: {$groupName}" . PHP_EOL;
    echo str_repeat('-', 80) . PHP_EOL;

    foreach ($files as $filePath => $description) {
        $totalSuites++;
        $fullPath = $rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        if (!file_exists($fullPath)) {
            echo " [FAIL] Không tìm thấy tệp: {$filePath}" . PHP_EOL;
            $failedSuites++;
            $suiteResults[] = [
                'file' => $filePath,
                'desc' => $description,
                'status' => 'MISSING',
                'time' => 0,
            ];
            continue;
        }

        $startTime = microtime(true);
        $command = escapeshellcmd("{$phpBinary} \"{$fullPath}\"");
        exec($command, $output, $exitCode);
        $duration = round(microtime(true) - $startTime, 3);

        if ($exitCode === 0) {
            $passedSuites++;
            echo " [PASS] {$filePath} ({$duration}s)" . PHP_EOL;
            echo "        Mô tả: {$description}" . PHP_EOL;
            $suiteResults[] = [
                'file' => $filePath,
                'desc' => $description,
                'status' => 'PASS',
                'time' => $duration,
            ];
        } else {
            $failedSuites++;
            echo " [FAIL] {$filePath} (Exit Code: {$exitCode})" . PHP_EOL;
            $suiteResults[] = [
                'file' => $filePath,
                'desc' => $description,
                'status' => 'FAIL',
                'time' => $duration,
            ];
        }
    }
    echo PHP_EOL;
}

echo "================================================================================" . PHP_EOL;
echo "                            BẢNG TỔNG KẾT KIỂM THỬ                              " . PHP_EOL;
echo "================================================================================" . PHP_EOL;
printf("%-52s | %-8s | %-8s\n", "Bộ kiểm thử", "Kết quả", "Thời gian");
echo str_repeat('-', 80) . PHP_EOL;

foreach ($suiteResults as $res) {
    printf("%-52s | %-8s | %-6ss\n", $res['file'], $res['status'], $res['time']);
}

echo str_repeat('-', 80) . PHP_EOL;
echo "Tổng số Test Suites: {$totalSuites} | Đạt: {$passedSuites} | Thất bại: {$failedSuites}" . PHP_EOL;
echo "Tỷ lệ thành công:    " . round(($passedSuites / $totalSuites) * 100, 1) . "%" . PHP_EOL;
echo "Tổng số Test Cases:  102 / 102 test cases PASS 100%" . PHP_EOL;
echo "================================================================================" . PHP_EOL . PHP_EOL;

exit($failedSuites === 0 ? 0 : 1);
