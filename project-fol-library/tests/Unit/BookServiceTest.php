<?php

declare(strict_types=1);

namespace Tests\Unit;

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

use App\Repositories\BookRepository;
use App\Services\BookService;
use App\Validators\BookValidator;

/**
 * Bộ kiểm thử đơn vị cho nghiệp vụ Quản lý Sách (Book Service Unit Test)
 * Tuân thủ Rule 12, 13, 14, 15 (AGENTS.md)
 */
class BookServiceTest
{
    private BookService $bookService;
    private BookRepository $bookRepo;

    public function __construct()
    {
        $this->bookRepo = new BookRepository();
        $this->bookService = new BookService($this->bookRepo);
    }

    public function runAll(): array
    {
        $results = [];

        // 1. Kiểm thử Validator
        $results['validator_rejects_empty_title'] = $this->assert(
            isset(BookValidator::validateCreate(['tieu_de' => '', 'tac_gia' => 'Nguyễn Du'])['tieu_de']),
            'Validator phải từ chối khi tên sách để trống'
        );

        $results['validator_rejects_empty_author'] = $this->assert(
            isset(BookValidator::validateCreate(['tieu_de' => 'Truyện Kiều', 'tac_gia' => ''])['tac_gia']),
            'Validator phải từ chối khi tác giả để trống'
        );

        $results['validator_rejects_invalid_year'] = $this->assert(
            isset(BookValidator::validateCreate(['tieu_de' => 'Sách Mới', 'tac_gia' => 'Tác giả A', 'nam_xuat_ban' => 99999])['nam_xuat_ban']),
            'Validator phải từ chối năm xuất bản không hợp lệ'
        );

        $results['validator_accepts_valid_payload'] = $this->assert(
            empty(BookValidator::validateCreate(['tieu_de' => 'Nghiên cứu văn học trung đại', 'tac_gia' => 'GS. Trần Đình Sử', 'nam_xuat_ban' => 2024, 'so_luong' => 2])),
            'Validator phải chấp thuận payload hợp lệ'
        );

        // 2. Kiểm thử lấy danh sách phân trang từ CSDL thực tế
        $page1 = $this->bookService->getPaginatedBooks(page: 1, perPage: 10);
        $results['pagination_has_expected_keys'] = $this->assert(
            isset($page1['items'], $page1['total'], $page1['page'], $page1['perPage'], $page1['totalPages']),
            'Phân trang phải có đầy đủ các trường cấu trúc'
        );

        $results['database_contains_imported_books'] = $this->assert(
            $page1['total'] >= 1200,
            "CSDL phải chứa trên 1.200 đầu sách đã nạp (Thực tế: {$page1['total']})"
        );

        $results['page1_items_count_correct'] = $this->assert(
            count($page1['items']) === 10,
            'Số lượng đầu sách trang 1 phải bằng perPage (10)'
        );

        // 3. Kiểm thử lọc theo chuyên mục (Prefix)
        $antBooks = $this->bookService->getPaginatedBooks(page: 1, perPage: 5, prefix: 'KV-ANT');
        $allAnt = true;
        foreach ($antBooks['items'] as $item) {
            if (!str_starts_with($item['id'], 'KV-ANT')) {
                $allAnt = false;
                break;
            }
        }
        $results['prefix_filter_works_properly'] = $this->assert(
            $allAnt && $antBooks['total'] > 0,
            "Bộ lọc tiền tố KV-ANT phải chỉ trả về sách thuộc chuyên mục ANT (Tổng: {$antBooks['total']})"
        );

        // 4. Kiểm thử tìm kiếm từ khóa
        $searchRes = $this->bookService->getPaginatedBooks(page: 1, perPage: 10, keyword: 'Nobel');
        $results['keyword_search_returns_results'] = $this->assert(
            $searchRes['total'] >= 1,
            "Tìm kiếm từ khóa 'Nobel' phải trả về ít nhất 1 đầu sách (Thực tế: {$searchRes['total']})"
        );

        // 5. Kiểm thử lấy chi tiết đầu sách
        $firstBookId = $page1['items'][0]['id'];
        $detail = $this->bookService->getBookDetail($firstBookId);
        $results['get_book_detail_success'] = $this->assert(
            $detail !== null && $detail->id === $firstBookId && count($detail->copies) > 0,
            "Lấy chi tiết đầu sách {$firstBookId} kèm danh sách bản sách thành công"
        );

        // 6. Kiểm thử vòng đời CRUD sách: Tạo mới -> Cập nhật -> Xóa
        $activeAdminId = (int)(\App\Core\Database::getConnection()->query("SELECT id_tai_khoan FROM tai_khoan WHERE id_vai_tro = 1 LIMIT 1")->fetchColumn() ?: 6);
        $testBookId = 'KV-TEST-' . time();
        $createRes = $this->bookService->createBook([
            'id_dau_sach' => $testBookId,
            'tieu_de' => 'Sách Kiểm Thử Tự Động Unit Test',
            'tac_gia' => 'Nhóm Kiểm Thử FoL, ThS. Nguyễn Văn A',
            'nam_xuat_ban' => 2026,
            'so_luong' => 3,
            'mo_ta' => 'Tài liệu phục vụ kiểm thử tự động hệ thống thư viện',
        ], $activeAdminId);

        $results['create_book_success'] = $this->assert(
            $createRes['success'] === true && $createRes['id'] === $testBookId,
            "Tạo mới đầu sách test {$testBookId} thành công"
        );

        // Kiểm tra trigger tự động cập nhật số lượng
        $createdBook = $this->bookService->getBookDetail($testBookId);
        $results['trigger_auto_set_so_luong'] = $this->assert(
            $createdBook !== null && $createdBook->totalQuantity === 3 && count($createdBook->copies) === 3,
            "Trigger phải tự động đồng bộ so_luong = 3 và sinh 3 cuốn sách (.1, .2, .3)"
        );

        // Cập nhật thông tin và bổ sung 1 cuốn sách
        $updateRes = $this->bookService->updateBook($testBookId, [
            'tieu_de' => 'Sách Kiểm Thử Tự Động (Đã Cập Nhật)',
            'tac_gia' => 'GS. Nguyễn Văn A',
            'nam_xuat_ban' => 2026,
            'mo_ta' => 'Nội dung cập nhật',
            'additional_copies' => 1,
        ], $activeAdminId);

        $results['update_book_success'] = $this->assert(
            $updateRes['success'] === true,
            'Cập nhật sách và in bổ sung 1 bản thành công'
        );

        $updatedBook = $this->bookService->getBookDetail($testBookId);
        $results['additional_copy_reflected'] = $this->assert(
            $updatedBook !== null && $updatedBook->totalQuantity === 4 && count($updatedBook->copies) === 4,
            "Số lượng sách sau khi bổ sung phải tăng lên 4 (Thực tế: {$updatedBook?->totalQuantity})"
        );

        // Xóa sách kiểm thử
        $deleteRes = $this->bookService->deleteBook($testBookId, $activeAdminId);
        $results['delete_book_success'] = $this->assert(
            $deleteRes['success'] === true,
            "Xóa đầu sách test {$testBookId} thành công"
        );

        $results['book_no_longer_exists'] = $this->assert(
            $this->bookService->getBookDetail($testBookId) === null,
            'Đầu sách test không còn tồn tại trong CSDL sau khi xóa'
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
    echo "=== CHẠY BỘ KIỂM THỬ ĐƠN VỊ: BOOK SERVICE UNIT TEST ===\n\n";
    $test = new BookServiceTest();
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
