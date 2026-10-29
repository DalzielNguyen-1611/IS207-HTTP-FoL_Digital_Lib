<?php

declare(strict_types=1);

namespace App\Services\Policies;

use App\Services\AuthorizationService;

/**
 * Chính sách phân quyền tài nguyên Đặt Chỗ Phòng Đọc (Booking Policy)
 * Kiểm tra quyền sở hữu bản ghi và điều kiện nghiệp vụ hợp lệ
 */
class BookingPolicy
{
    private AuthorizationService $authService;

    public function __construct(?AuthorizationService $authService = null)
    {
        $this->authService = $authService ?? new AuthorizationService();
    }

    /**
     * Kiểm tra người dùng có quyền hủy lượt đặt chỗ cụ thể hay không
     * Điều kiện:
     * 1. Có quyền room.cancel
     * 2. Là chính chủ sở hữu lượt đặt chỗ HOẶC có quyền quản lý phòng đọc (Thủ thư/Admin)
     * 3. Lượt đặt chỗ phải ở trạng thái cho phép hủy ('DaDat')
     */
    public function canCancel(int $userId, array $bookingRecord): bool
    {
        // 1. Kiểm tra quyền chức năng cơ bản
        if (!$this->authService->hasPermission($userId, 'room.cancel')) {
            return false;
        }

        // 2. Điều kiện nghiệp vụ: Trạng thái lượt đặt phải là 'DaDat' (chưa check-in, chưa hủy, chưa quá hạn)
        $status = $bookingRecord['trang_thai'] ?? '';
        if ($status !== 'DaDat') {
            return false;
        }

        // 3. Nếu là cán bộ có quyền room.manage -> được phép hủy mọi lượt đặt
        if ($this->authService->hasPermission($userId, 'room.manage')) {
            return true;
        }

        // 4. Nếu là sinh viên thông thường -> BẮT BUỘC phải là chính chủ sở hữu bản ghi
        $ownerId = (int)($bookingRecord['id_nguoi_dung'] ?? 0);
        return $ownerId === $userId;
    }

    /**
     * Kiểm tra người dùng có được xem chi tiết lượt đặt chỗ hay không
     */
    public function canView(int $userId, array $bookingRecord): bool
    {
        if ($this->authService->hasPermission($userId, 'room.manage')) {
            return true;
        }

        $ownerId = (int)($bookingRecord['id_nguoi_dung'] ?? 0);
        return $ownerId === $userId;
    }
}
