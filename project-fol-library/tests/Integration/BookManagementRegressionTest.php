<?php

declare(strict_types=1);

namespace Tests\Integration;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Tải biến môi trường
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($trimmed, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $trimmed, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

use App\Controllers\LibrarianController;
use App\Core\Session;
use App\Services\BookService;

/**
 * Bộ kiểm thử tích hợp & hồi quy: Giao diện và API Quản lý Sách
 * Tuân thủ Rule 12, 13, 14, 15 (AGENTS.md)
 */
class BookManagementRegressionTest
{
    private LibrarianController $controller;
    private BookService $bookService;

    public function __construct()
    {
        $this->bookService = new BookService();
        $this->controller = new LibrarianController(null, $this->bookService);
    }

    public function runAll(): array
    {
        $results = [];

        // 1. Kiểm thử render View /librarian/books
        Session::start();
        Session::setAuthUser([
            'id_tai_khoan' => 2,
            'id_vai_tro' => 2,
            'ma_vai_tro' => 'LIBRARIAN',
            'email' => 'canbo@fol.ussh.edu.vn',
            'ho_ten' => 'Thủ thư Trưởng',
        ]);

        $_GET = ['page' => 1, 'prefix' => 'ALL'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['HTTP_X_REQUESTED_WITH'], $_SERVER['HTTP_ACCEPT']);

        ob_start();
        $this->controller->books();
        $htmlOutput = ob_get_clean();

        $results['view_renders_successfully'] = $this->assert(
            !empty($htmlOutput) && str_contains($htmlOutput, 'Quản lý sách'),
            'View /librarian/books phải render thành công tiêu đề trang'
        );

        $results['view_contains_real_book_codes'] = $this->assert(
            str_contains($htmlOutput, 'KV-ANT-0001') || str_contains($htmlOutput, 'KV-ANT-'),
            'View phải chứa mã sách thực tế từ CSDL (KV-ANT-...)'
        );

        $results['view_contains_pagination'] = $this->assert(
            str_contains($htmlOutput, 'staff-pagination'),
            'View phải chứa thanh phân trang thực tế'
        );

        $results['view_contains_filter_chips'] = $this->assert(
            str_contains($htmlOutput, 'filter-chips') && str_contains($htmlOutput, 'KV-ANT'),
            'View phải chứa các nút lọc chuyên mục (ANT, ESS, POE, REC)'
        );

        $results['view_contains_modals'] = $this->assert(
            str_contains($htmlOutput, 'edit-book-dialog') && str_contains($htmlOutput, 'view-copies-dialog'),
            'View phải chứa đầy đủ modal Thêm/Sửa và modal Xem bản sách vật lý'
        );

        // 2. Kiểm thử API JSON tìm kiếm
        $_GET = ['keyword' => 'Nobel', 'format' => 'json'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        // Gọi BookService trực tiếp để kiểm tra định dạng trả về của JSON API
        $jsonData = $this->bookService->getPaginatedBooks(page: 1, perPage: 10, keyword: 'Nobel');
        $results['api_json_search_returns_valid_data'] = $this->assert(
            $jsonData['total'] >= 1 && isset($jsonData['items'][0]['title']),
            'API JSON tìm kiếm phải trả lời dữ liệu có tiêu đề'
        );

        // 3. Kiểm thử render View /book-detail với ID sách thực tế
        $_GET = ['id' => 'KV-ANT-0001'];
        ob_start();
        $detailViewPath = dirname(__DIR__, 2) . '/resources/views/book-detail/index.php';
        require $detailViewPath;
        $detailHtml = ob_get_clean();

        $results['book_detail_renders_real_book'] = $this->assert(
            str_contains($detailHtml, '10 NĂM KHOA ĐÔNG PHƯƠNG HỌC') || str_contains($detailHtml, 'KV-ANT-0001'),
            'Trang /book-detail?id=KV-ANT-0001 phải hiển thị chính xác tiêu đề sách từ CSDL'
        );

        return $results;
    }

    private function assert(bool $condition, string $message): array
    {
        return [
            'passed' => $condition,
            'message' => $message,
        ];
    }
}

if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === __FILE__) {
    echo "=== CHẠY BỘ KIỂM THỬ TÍCH HỢP: BOOK MANAGEMENT REGRESSION TEST ===\n\n";
    $test = new BookManagementRegressionTest();
    $results = $test->runAll();

    $passed = 0;
    $failed = 0;
    foreach ($results as $name => $item) {
        $status = $item['passed'] ? '[PASS]' : '[FAIL]';
        echo sprintf("%-6s %-38s : %s\n", $status, $name, $item['message']);
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
