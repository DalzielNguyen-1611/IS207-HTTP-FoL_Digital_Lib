<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoomRepository;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Service quét và xử lý giải phóng các lượt đặt chỗ bị quá hạn / vắng mặt (Ghost Booking & No-Show Worker)
 * Tự động hóa qua Cronjob hoặc gọi định kỳ
 */
class BookingExpirationService
{
    private RoomRepository $roomRepo;

    public function __construct(?RoomRepository $roomRepo = null)
    {
        $this->roomRepo = $roomRepo ?? new RoomRepository();
    }

    /**
     * Quét và chuyển trạng thái 'DaDat' thành 'QuaHanKhongDen'
     *
     * @param DateTimeImmutable|null $now Thời điểm hiện tại (mặc định theo Asia/Ho_Chi_Minh)
     * @return int Số lượt đặt bị giải phóng
     */
    public function expireAbandonedBookings(?DateTimeImmutable $now = null): int
    {
        $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
        return $this->roomRepo->expireNoShowBookings($now);
    }
}
