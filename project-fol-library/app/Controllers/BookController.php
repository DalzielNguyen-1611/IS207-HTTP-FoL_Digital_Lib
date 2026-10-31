<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Services\BookService;
use Throwable;

/**
 * Controller điều phối chức năng Tra cứu sách (Server-Side Pagination & Filtering)
 * Tuân thủ mô hình kiến trúc PHP MVC và các nguyên tắc bảo mật, hiệu năng hệ thống
 */
class BookController
{
    private BookService $bookService;

    public function __construct(?BookService $bookService = null)
    {
        $this->bookService = $bookService ?? new BookService();
    }

    /**
     * Trang chủ tra cứu tài liệu với phân trang và bộ lọc phía server
     */
    public function index(): void
    {
        // 1. Khởi tạo và kiểm tra trạng thái phiên làm việc
        Session::start();
        $userName = Session::get('user_name', 'Người dùng');
        $userRole = Session::getRoleCode();
        $isLoggedIn = Session::isAuthenticated();

        // 2. Tiếp nhận và chuẩn hóa tham số đầu vào (Prepared Input Validation)
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        // Mặc định 10 đầu sách/trang; giới hạn hợp lệ từ 5 đến 50 cuốn để chống quá tải
        $limit = isset($_GET['limit']) 
            ? max(5, min(50, (int)$_GET['limit'])) 
            : (isset($_GET['per_page']) ? max(5, min(50, (int)$_GET['per_page'])) : 10);

        $keyword = isset($_GET['q']) 
            ? trim((string)$_GET['q']) 
            : (isset($_GET['keyword']) ? trim((string)$_GET['keyword']) : null);
        if ($keyword === '') {
            $keyword = null;
        }

        $major = isset($_GET['major']) && trim((string)$_GET['major']) !== '' && strtoupper(trim((string)$_GET['major'])) !== 'ALL'
            ? trim((string)$_GET['major'])
            : null;

        $author = isset($_GET['author']) && trim((string)$_GET['author']) !== ''
            ? trim((string)$_GET['author'])
            : null;

        $year = isset($_GET['year']) && (int)$_GET['year'] > 0
            ? (int)$_GET['year']
            : null;

        $status = isset($_GET['status']) && in_array($_GET['status'], ['available', 'all'], true)
            ? $_GET['status']
            : null;

        $sort = isset($_GET['sort']) && in_array($_GET['sort'], ['id_asc', 'newest', 'oldest', 'title_asc', 'title_desc'], true)
            ? $_GET['sort']
            : 'id_asc';

        $prefix = isset($_GET['prefix']) && trim((string)$_GET['prefix']) !== '' && strtoupper(trim((string)$_GET['prefix'])) !== 'ALL'
            ? strtoupper(trim((string)$_GET['prefix']))
            : null;

        // 3. Thực hiện truy vấn dữ liệu an toàn kèm bắt lỗi ngoại lệ
        $errorMessage = null;
        try {
            $bookData = $this->bookService->getPaginatedBooks(
                page: $page,
                perPage: $limit,
                keyword: $keyword,
                prefix: $prefix,
                major: $major,
                author: $author,
                year: $year,
                status: $status,
                sort: $sort
            );

            $filterMetadata = $this->bookService->getFilterMetadata();
        } catch (Throwable $e) {
            $errorMessage = 'Hệ thống tra cứu đang bận hoặc có lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.';
            $bookData = [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $limit,
                'totalPages' => 1
            ];
            $filterMetadata = [
                'majors' => ['Nhân học', 'Tiểu luận', 'Thơ ca', 'Tuyển tập'],
                'authors' => [],
                'years' => [],
                'statuses' => ['all' => 'Tất cả tình trạng', 'available' => 'Còn sách có thể mượn'],
                'sorts' => ['id_asc' => 'Mã sách tăng dần (Mặc định)']
            ];
        }

        // 4. Đóng gói dữ liệu chuyển giao sang View
        $books = $bookData['items'];
        $totalBooks = $bookData['total'];
        $totalPages = $bookData['totalPages'];
        $currentPage = $bookData['page'];
        $perPage = $bookData['perPage'];

        $filters = [
            'q' => $keyword,
            'major' => $major,
            'author' => $author,
            'year' => $year,
            'status' => $status,
            'sort' => $sort,
            'prefix' => $prefix
        ];
        $filterOptions = $filterMetadata;

        // 5. Render Giao diện View
        require dirname(__DIR__, 2) . '/resources/views/books/index.php';
    }
}
