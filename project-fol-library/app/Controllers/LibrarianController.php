<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Services\BookService;
use App\Services\LibrarianDashboardService;

/**
 * Controller nghiệp vụ cán bộ thư viện (Librarian Controller)
 * Tuân thủ Rule 8, 9, 10, 11 (AGENTS.md)
 */
class LibrarianController
{
    private LibrarianDashboardService $dashboardService;
    private BookService $bookService;

    public function __construct(
        ?LibrarianDashboardService $dashboardService = null,
        ?BookService $bookService = null
    ) {
        $this->dashboardService = $dashboardService ?? new LibrarianDashboardService();
        $this->bookService = $bookService ?? new BookService();
    }

    /**
     * Màn hình Tổng quan Thư viện (/librarian)
     */
    public function index(): void
    {
        // 1. Gọi Service lấy dữ liệu thực tế từ cơ sở dữ liệu
        $data = $this->dashboardService->getOverviewData();

        // 2. Trích xuất biến sạch cho View
        $today = $data['today'];
        $statistics = $data['statistics'];
        $readingShifts = $data['readingShifts'];
        $attentionLoans = $data['attentionLoans'];

        // 3. Render View
        $viewPath = dirname(__DIR__, 2) . '/resources/views/librarian/index.php';
        require $viewPath;
    }

    /**
     * Màn hình Quản lý Sách (/librarian/books)
     */
    public function books(): void
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(5, min(100, (int)$_GET['per_page'])) : 15;
        $keyword = isset($_GET['keyword']) && trim((string)$_GET['keyword']) !== ''
            ? trim((string)$_GET['keyword'])
            : null;
        $prefix = isset($_GET['prefix']) && trim((string)$_GET['prefix']) !== ''
            ? strtoupper(trim((string)$_GET['prefix']))
            : null;

        $isJson = (isset($_GET['format']) && $_GET['format'] === 'json')
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        $bookData = $this->bookService->getPaginatedBooks(
            page: $page,
            perPage: $perPage,
            keyword: $keyword,
            prefix: $prefix
        );

        if ($isJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => true,
                'data' => $bookData,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Truyền biến sạch vào View
        $books = $bookData['items'];
        $pagination = [
            'total' => $bookData['total'],
            'page' => $bookData['page'],
            'perPage' => $bookData['perPage'],
            'totalPages' => $bookData['totalPages'],
            'keyword' => $keyword ?? '',
            'prefix' => $prefix ?? 'ALL',
        ];

        $viewPath = dirname(__DIR__, 2) . '/resources/views/librarian-books/index.php';
        require $viewPath;
    }

    /**
     * API Lấy chi tiết sách (/librarian/books/detail)
     */
    public function getBookDetail(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $id = trim((string)($_GET['id'] ?? ''));
        if ($id === '') {
            echo json_encode(['success' => false, 'message' => 'Mã sách không hợp lệ.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $book = $this->bookService->getBookDetail($id);
        if (!$book) {
            echo json_encode(['success' => false, 'message' => "Không tìm thấy sách với mã: {$id}"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $book->toArray(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * API Gợi ý mã sách tiếp theo (/librarian/books/next-id)
     */
    public function getNextBookId(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        $prefix = (string)($_GET['prefix'] ?? 'KV-ANT');
        $nextId = $this->bookService->getNextBookId($prefix);

        echo json_encode([
            'success' => true,
            'next_id' => $nextId,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xử lý Thêm sách mới (POST /librarian/books/create)
     */
    public function createBook(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $input = $this->getRequestPayload();
        $userId = Session::getUserId();

        $result = $this->bookService->createBook($input, $userId);

        if (!$result['success']) {
            http_response_code(422);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xử lý Cập nhật sách (POST /librarian/books/update)
     */
    public function updateBook(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $input = $this->getRequestPayload();
        $id = trim((string)($input['id_dau_sach'] ?? $input['id'] ?? ''));
        $userId = Session::getUserId();

        if ($id === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Mã sách không được để trống.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = $this->bookService->updateBook($id, $input, $userId);

        if (!$result['success']) {
            http_response_code(422);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xử lý Xóa sách (POST /librarian/books/delete)
     */
    public function deleteBook(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $input = $this->getRequestPayload();
        $id = trim((string)($input['id_dau_sach'] ?? $input['id'] ?? ''));
        $userId = Session::getUserId();

        if ($id === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'message' => 'Mã sách không được để trống.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = $this->bookService->deleteBook($id, $userId);

        if (!$result['success']) {
            http_response_code(400);
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Trích xuất dữ liệu yêu cầu từ $_POST hoặc php://input (JSON)
     */
    private function getRequestPayload(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $rawBody = file_get_contents('php://input');
        if ($rawBody !== false && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
