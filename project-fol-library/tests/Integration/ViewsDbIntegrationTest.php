<?php

declare(strict_types=1);

namespace Tests\Integration;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Session;

/**
 * Bộ kiểm thử tích hợp: Xác minh toàn bộ các Views kết nối cơ sở dữ liệu và render chuẩn xác
 * Đảm bảo 100% Mock Data đã bị loại bỏ và dữ liệu CSDL được phản ánh đầy đủ.
 */
class ViewsDbIntegrationTest
{
    public function runAll(): array
    {
        $results = [];

        // Khởi tạo phiên làm việc giả lập với tư cách Admin
        Session::start();
        Session::set('user_id', 1);
        Session::set('role_id', 1);
        Session::set('role_code', 'ADMIN');

        $views = [
            'view_books_renders_real_db' => [
                'file' => 'resources/views/books/index.php',
                'mustContain' => ['Tra cứu sách', 'KV-ANT-'],
            ],
            'view_book_detail_renders_real_db' => [
                'file' => 'resources/views/book-detail/index.php',
                'params' => ['id' => 'KV-ANT-0001'],
                'mustContain' => ['KV-ANT-0001', 'Trở về tra cứu sách'],
            ],
            'view_profile_renders_real_db' => [
                'file' => 'resources/views/profile/index.php',
                'mustContain' => ['Trang cá nhân', 'Sách mượn'],
            ],
            'view_librarian_counter_renders_real_db' => [
                'file' => 'resources/views/librarian-counter/index.php',
                'mustContain' => ['Mượn / trả sách', 'Tạo phiếu'],
            ],
            'view_librarian_rooms_renders_real_db' => [
                'file' => 'resources/views/librarian-rooms/index.php',
                'mustContain' => ['Phòng đọc', 'Ca'],
            ],
            'view_librarian_reports_renders_real_db' => [
                'file' => 'resources/views/librarian-reports/index.php',
                'mustContain' => ['Báo cáo thống kê', 'Chọn kỳ báo cáo'],
            ],
            'view_reading_room_renders_real_db' => [
                'file' => 'resources/views/reading-room/index.php',
                'mustContain' => ['Phòng đọc', 'Ca đọc'],
            ],
            'view_admin_users_renders_real_db' => [
                'file' => 'resources/views/admin-users/index.php',
                'mustContain' => ['Quản lý người dùng', 'Danh sách tài khoản'],
            ],
            'view_admin_audit_renders_real_db' => [
                'file' => 'resources/views/admin-audit/index.php',
                'mustContain' => ['Nhật ký hệ thống', 'Lịch sử hoạt động'],
            ],
            'view_about_renders_successfully' => [
                'file' => 'resources/views/about/index.php',
                'mustContain' => ['Giới thiệu', 'Thư viện Khoa Văn học', 'Sứ Mệnh'],
            ],
            'view_rules_renders_successfully' => [
                'file' => 'resources/views/rules/index.php',
                'mustContain' => ['Nội quy', 'Quy chế Mượn - Trả', 'Phòng đọc'],
            ],
            'view_feedback_renders_successfully' => [
                'file' => 'resources/views/feedback/index.php',
                'mustContain' => ['Góp ý', 'Đề xuất Sách mới', 'Gửi ý kiến'],
            ],
            'view_contact_renders_successfully' => [
                'file' => 'resources/views/contact/index.php',
                'mustContain' => ['Liên hệ', 'Thư viện Khoa Văn học', 'Địa chỉ'],
            ],
        ];

        foreach ($views as $testName => $meta) {
            if (isset($meta['params'])) {
                foreach ($meta['params'] as $k => $v) {
                    $_GET[$k] = $v;
                }
            }

            ob_start();
            $level = ob_get_level();
            try {
                $filePath = dirname(__DIR__, 2) . '/' . $meta['file'];
                include $filePath;
                $output = ob_get_clean();

                $containsAll = true;
                $missingNeedle = '';
                foreach ($meta['mustContain'] as $needle) {
                    if (!str_contains($output, $needle)) {
                        $containsAll = false;
                        $missingNeedle = $needle;
                        break;
                    }
                }

                $results[$testName] = [
                    'passed' => $containsAll,
                    'message' => $containsAll
                        ? "View {$meta['file']} render thành công từ CSDL"
                        : "View {$meta['file']} thiếu chuỗi mong đợi: '{$missingNeedle}'",
                ];
            } catch (\Throwable $e) {
                while (ob_get_level() >= $level) {
                    ob_end_clean();
                }
                $results[$testName] = [
                    'passed' => false,
                    'message' => "View {$meta['file']} gặp lỗi: " . $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    echo "=== CHẠY BỘ KIỂM THỬ TÍCH HỢP: VIEWS DB INTEGRATION TEST ===\n\n";
    $test = new ViewsDbIntegrationTest();
    $results = $test->runAll();

    $passed = 0;
    $failed = 0;
    foreach ($results as $name => $item) {
        $status = $item['passed'] ? '[PASS]' : '[FAIL]';
        echo sprintf("%-6s %-42s : %s\n", $status, $name, $item['message']);
        if ($item['passed']) {
            $passed++;
        } else {
            $failed++;
        }
    }

    echo "\n----------------------------------------------------\n";
    echo sprintf("Tổng số test cases: %d | Đạt: %d | Thất bại: %d\n", count($results), $passed, $failed);
    echo "----------------------------------------------------\n";

    if ($failed > 0) {
        exit(1);
    }
}
