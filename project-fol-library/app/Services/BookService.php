<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Book;
use App\Repositories\BookRepository;
use App\Validators\BookValidator;
use Throwable;

/**
 * Tầng xử lý nghiệp vụ Quản lý Sách (Book Service)
 * Tuân thủ Rule 8, 9, 10, 11 (AGENTS.md)
 */
class BookService
{
    private BookRepository $bookRepo;
    private AuditService $auditService;

    public function __construct(
        ?BookRepository $bookRepo = null,
        ?AuditService $auditService = null
    ) {
        $this->bookRepo = $bookRepo ?? new BookRepository();
        $this->auditService = $auditService ?? new AuditService();
    }

    /**
     * Lấy danh sách đầu sách có phân trang và tìm kiếm
     */
    public function getPaginatedBooks(
        int $page = 1,
        int $perPage = 15,
        ?string $keyword = null,
        ?string $prefix = null
    ): array {
        $result = $this->bookRepo->getPaginated(
            page: $page,
            perPage: $perPage,
            keyword: $keyword,
            prefix: $prefix
        );

        $bookModels = [];
        foreach ($result['items'] as $item) {
            $bookModels[] = Book::fromArray($item)->toArray();
        }

        return [
            'items' => $bookModels,
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'totalPages' => $result['totalPages'],
        ];
    }

    /**
     * Lấy chi tiết đầu sách
     */
    public function getBookDetail(string $id): ?Book
    {
        $raw = $this->bookRepo->findById(trim($id));
        if (!$raw) {
            return null;
        }

        return Book::fromArray($raw);
    }

    /**
     * Tạo mới đầu sách và các bản sách vật lý
     */
    public function createBook(array $data, ?int $userId = null): array
    {
        $errors = BookValidator::validateCreate($data, $this->bookRepo);
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => reset($errors),
                'errors' => $errors,
            ];
        }

        try {
            $bookId = $this->bookRepo->create($data);

            $this->auditService->log(
                userId: $userId,
                action: 'book.create',
                targetTable: 'dau_sach',
                targetId: $bookId,
                description: sprintf(
                    'Thêm mới đầu sách %s: "%s" (%d bản sách)',
                    $bookId,
                    $data['tieu_de'] ?? '',
                    (int)($data['so_luong'] ?? 1)
                )
            );

            return [
                'success' => true,
                'message' => "Thêm sách '{$bookId}' thành công!",
                'id' => $bookId,
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống khi thêm sách: ' . $e->getMessage(),
                'errors' => ['general' => $e->getMessage()],
            ];
        }
    }

    /**
     * Cập nhật thông tin đầu sách
     */
    public function updateBook(string $id, array $data, ?int $userId = null): array
    {
        $id = trim($id);
        if (!$this->bookRepo->exists($id)) {
            return [
                'success' => false,
                'message' => "Không tìm thấy đầu sách với mã: {$id}",
                'errors' => ['id' => 'Đầu sách không tồn tại.'],
            ];
        }

        $errors = BookValidator::validateUpdate($data);
        if (!empty($errors)) {
            return [
                'success' => false,
                'message' => reset($errors),
                'errors' => $errors,
            ];
        }

        try {
            $this->bookRepo->update($id, $data);

            $this->auditService->log(
                userId: $userId,
                action: 'book.update',
                targetTable: 'dau_sach',
                targetId: $id,
                description: sprintf('Cập nhật đầu sách %s: "%s"', $id, $data['tieu_de'] ?? '')
            );

            return [
                'success' => true,
                'message' => "Cập nhật đầu sách '{$id}' thành công!",
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Lỗi khi cập nhật sách: ' . $e->getMessage(),
                'errors' => ['general' => $e->getMessage()],
            ];
        }
    }

    /**
     * Xóa đầu sách khỏi hệ thống
     */
    public function deleteBook(string $id, ?int $userId = null): array
    {
        $id = trim($id);
        if (!$this->bookRepo->exists($id)) {
            return [
                'success' => false,
                'message' => "Không tìm thấy đầu sách với mã: {$id}",
            ];
        }

        try {
            $this->bookRepo->delete($id);

            $this->auditService->log(
                userId: $userId,
                action: 'book.delete',
                targetTable: 'dau_sach',
                targetId: $id,
                description: "Xóa đầu sách {$id} khỏi hệ thống"
            );

            return [
                'success' => true,
                'message' => "Đã xóa đầu sách '{$id}' thành công!",
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Lấy mã sách tiếp theo cho form thêm
     */
    public function getNextBookId(string $prefix = 'KV-ANT'): string
    {
        return $this->bookRepo->generateNextId($prefix);
    }
}
