<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\LibrarianDashboardService;

/**
 * Controller nghiệp vụ cán bộ thư viện (Librarian Controller)
 * Tuân thủ Rule 8, 9, 10 (AGENTS.md)
 */
class LibrarianController
{
    private LibrarianDashboardService $dashboardService;

    public function __construct(?LibrarianDashboardService $dashboardService = null)
    {
        $this->dashboardService = $dashboardService ?? new LibrarianDashboardService();
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
}
