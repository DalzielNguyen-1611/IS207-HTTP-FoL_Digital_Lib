<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Tầng xử lý nghiệp vụ Bảng điều khiển Tổng quan Thư viện (Librarian Dashboard Service)
 * Tuân thủ Rule 8, 9, 10 (AGENTS.md)
 */
class LibrarianDashboardService
{
    private DashboardRepository $dashboardRepository;

    public function __construct(?DashboardRepository $dashboardRepository = null)
    {
        $this->dashboardRepository = $dashboardRepository ?? new DashboardRepository();
    }

    /**
     * Thu thập và chuẩn hóa toàn bộ dữ liệu thực tế cho trang Tổng quan
     */
    public function getOverviewData(): array
    {
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh'));

        // 1. Lấy số liệu thống kê thực tế từ DB
        $rawStats = $this->dashboardRepository->getOverviewStatistics();

        $statistics = [
            [
                'label' => 'Sách đang mượn',
                'value' => number_format($rawStats['borrowed_count']),
                'note' => 'Tổng cuốn sách đang phục vụ mượn',
            ],
            [
                'label' => 'Sách quá hạn',
                'value' => number_format($rawStats['overdue_count']),
                'note' => $rawStats['overdue_count'] > 0 ? 'Cần gửi nhắc nhở / xử lý' : 'Hiện không có sách quá hạn',
            ],
            [
                'label' => 'Lượt đặt phòng hôm nay',
                'value' => number_format($rawStats['booking_today_count']),
                'note' => 'Đã đăng ký trong các ca đọc',
            ],
            [
                'label' => 'Kho lưu trữ',
                'value' => number_format($rawStats['total_titles']) . ' đầu sách',
                'note' => number_format($rawStats['total_copies']) . ' bản sách vật lý',
            ],
        ];

        // 2. Lấy danh sách ca đọc thực tế
        $shifts = $this->dashboardRepository->getReadingShiftsToday();
        if (empty($shifts)) {
            // Dự phòng nếu chưa có ca đọc trong CSDL
            $shifts = [
                ['name' => 'Ca 1', 'time' => '07:30 – 09:30', 'booked' => 0, 'capacity' => 40],
                ['name' => 'Ca 2', 'time' => '09:45 – 11:45', 'booked' => 0, 'capacity' => 40],
                ['name' => 'Ca 3', 'time' => '13:00 – 15:00', 'booked' => 0, 'capacity' => 40],
                ['name' => 'Ca 4', 'time' => '15:15 – 17:15', 'booked' => 0, 'capacity' => 40],
            ];
        }

        // 3. Lấy danh sách phiếu cần chú ý từ DB
        $attentionLoans = $this->dashboardRepository->getAttentionLoans(10);

        return [
            'today' => $today,
            'statistics' => $statistics,
            'readingShifts' => $shifts,
            'attentionLoans' => $attentionLoans,
        ];
    }
}
