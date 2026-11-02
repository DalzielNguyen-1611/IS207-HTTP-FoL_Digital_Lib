<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\UserRepository;
use App\Services\AuditService;
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
    private UserRepository $userRepo;
    private AuditService $auditService;

    public function __construct(
        ?LibrarianDashboardService $dashboardService = null,
        ?BookService $bookService = null,
        ?UserRepository $userRepo = null,
        ?AuditService $auditService = null
    ) {
        $this->dashboardService = $dashboardService ?? new LibrarianDashboardService();
        $this->bookService = $bookService ?? new BookService();
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->auditService = $auditService ?? new AuditService();
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
        $pendingCount = $data['pendingCount'];

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

        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Mã bảo mật CSRF không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

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

        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Mã bảo mật CSRF không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

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

        if (!$this->validateCsrf()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Mã bảo mật CSRF không hợp lệ hoặc phiên làm việc đã hết hạn. Vui lòng tải lại trang.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

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
     * Màn hình Duyệt thẻ Sinh viên (/librarian/students)
     */
    public function students(): void
    {
        $pendingStudents = $this->userRepo->getPendingStudents();
        $message = isset($_GET['msg']) ? trim((string)$_GET['msg']) : null;
        $error = isset($_GET['error']) ? trim((string)$_GET['error']) : null;

        $viewPath = dirname(__DIR__, 2) . '/resources/views/librarian-students/index.php';
        require $viewPath;
    }

    /**
     * Phê duyệt kích hoạt thẻ sinh viên (POST /librarian/students/approve)
     */
    public function approveStudent(): void
    {
        $payload = $this->getRequestPayload();
        $targetUserId = (int)($payload['user_id'] ?? 0);
        $currentUserId = Session::getUserId();

        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!$this->validateCsrf()) {
            $msg = 'Mã bảo mật CSRF không hợp lệ hoặc phiên làm việc đã hết hạn.';
            if ($isJson) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        if ($targetUserId <= 0) {
            $msg = 'Mã tài khoản sinh viên không hợp lệ.';
            if ($isJson) {
                http_response_code(422);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        $targetStudent = $this->userRepo->findById($targetUserId);
        if ($targetStudent === null) {
            $msg = 'Không tìm thấy hồ sơ sinh viên.';
            if ($isJson) {
                http_response_code(404);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        // Kích hoạt tài khoản
        $this->userRepo->approveStudent($targetUserId);

        // Ghi nhật ký kiểm toán
        $this->auditService->log(
            userId: $currentUserId ?? $targetUserId,
            action: 'DuyetTheSinhVien',
            targetTable: 'tai_khoan',
            targetId: (string)$targetUserId,
            description: "Cán bộ thư viện đã phê duyệt thẻ sinh viên cho {$targetStudent['mssv']} - {$targetStudent['ho_ten']}"
        );

        $successMsg = "Đã phê duyệt thành công thẻ sinh viên {$targetStudent['mssv']} ({$targetStudent['ho_ten']}).";
        if ($isJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'message' => $successMsg], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: /librarian/students?msg=' . urlencode($successMsg));
        exit;
    }

    /**
     * Yêu cầu chụp lại thẻ sinh viên (POST /librarian/students/reject)
     */
    public function rejectStudent(): void
    {
        $payload = $this->getRequestPayload();
        $targetUserId = (int)($payload['user_id'] ?? 0);
        $reason = trim((string)($payload['reason'] ?? 'Ảnh mờ hoặc không rõ nét, vui lòng chụp lại rõ thông tin thẻ.'));
        $currentUserId = Session::getUserId();

        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!$this->validateCsrf()) {
            $msg = 'Mã bảo mật CSRF không hợp lệ hoặc phiên làm việc đã hết hạn.';
            if ($isJson) {
                http_response_code(403);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        if ($targetUserId <= 0) {
            $msg = 'Mã tài khoản sinh viên không hợp lệ.';
            if ($isJson) {
                http_response_code(422);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        $targetStudent = $this->userRepo->findById($targetUserId);
        if ($targetStudent === null) {
            $msg = 'Không tìm thấy hồ sơ sinh viên.';
            if ($isJson) {
                http_response_code(404);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_UNICODE);
                exit;
            }
            header('Location: /librarian/students?error=' . urlencode($msg));
            exit;
        }

        // Lưu lý do yêu cầu chụp lại
        $this->userRepo->rejectStudent($targetUserId, $reason);

        // Ghi nhật ký kiểm toán
        $this->auditService->log(
            userId: $currentUserId ?? $targetUserId,
            action: 'TuChoiTheSinhVien',
            targetTable: 'ho_so_nguoi_dung',
            targetId: (string)$targetUserId,
            description: "Cán bộ thư viện yêu cầu chụp lại thẻ cho sinh viên {$targetStudent['mssv']}. Lý do: {$reason}"
        );

        $infoMsg = "Đã gửi yêu cầu chụp lại thẻ cho sinh viên {$targetStudent['mssv']}.";
        if ($isJson) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => true, 'message' => $infoMsg], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: /librarian/students?msg=' . urlencode($infoMsg));
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

    /**
     * Xác thực CSRF token từ POST body hoặc HTTP Header X-CSRF-TOKEN
     */
    private function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? null;
        if ($token === null && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if ($token === null) {
            $payload = $this->getRequestPayload();
            $token = $payload['csrf_token'] ?? null;
        }
        return Session::validateCsrfToken(is_string($token) ? $token : null);
    }
}

