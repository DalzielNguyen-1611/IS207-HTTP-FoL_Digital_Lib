<?php

declare(strict_types=1);

// Dữ liệu thời gian thực được cung cấp từ LibrarianController
// Tự động khởi tạo qua LibrarianDashboardService nếu được truy cập hoặc require độc lập
if (!isset($statistics) || !isset($readingShifts) || !isset($attentionLoans) || !isset($today)) {
    $dashboardService = new \App\Services\LibrarianDashboardService();
    $overviewData = $dashboardService->getOverviewData();
    $today = $overviewData['today'];
    $statistics = $overviewData['statistics'];
    $readingShifts = $overviewData['readingShifts'];
    $attentionLoans = $overviewData['attentionLoans'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tổng quan cán bộ · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header khu vực cán bộ -->
    <header class="staff-header">
        <a class="staff-brand" href="/" title="Về trang chủ Thư viện">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Cán bộ</small>
            </span>
        </a>

        <div class="staff-header-actions">
            <nav class="staff-nav" aria-label="Điều hướng">
                <a href="/">Trang chủ</a>
                <?php $currentRole = \App\Core\Session::getRoleCode(); ?>
                <?php if ($currentRole === 'ADMIN'): ?>
                    <a href="/profile">Cá nhân</a>
                    <a href="/librarian" class="active">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($currentRole === 'LIBRARIAN'): ?>
                    <a href="/librarian" class="active">Cán bộ</a>
                <?php endif; ?>
            </nav>

            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
                <?php
                    $currentUser = \App\Core\Session::getAuthUser();
                    $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Tài khoản', ENT_QUOTES, 'UTF-8');
                    $dashUrl = ($currentRole === 'ADMIN') ? '/admin/settings' : '/librarian';
                ?>
                <a href="<?= $dashUrl ?>" class="staff-btn staff-btn-user" title="Tài khoản của bạn">
                    <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                    <span><?= $userName ?></span>
                </a>
                <a href="/logout" class="staff-btn staff-btn-logout" title="Đăng xuất khỏi hệ thống">
                    Đăng xuất
                </a>
            </div>
        </div>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng nghiệp vụ, các mục chưa xây dựng được đánh dấu rõ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>
            <a href="/librarian" aria-current="page">Tổng quan</a>
            <a href="/librarian/books">Quản lý sách</a>
            <a href="/librarian/counter">Mượn / trả</a>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings">Về trang quản trị</a>
                <a href="/profile">Về trang cá nhân (SV)</a>
            <?php endif; ?>
        </nav>

                <div class="staff-content">
            <!-- Tiêu đề Dashboard -->
            <section class="staff-intro">
                <span class="staff-eyebrow">KHÔNG GIAN LÀM VIỆC</span>
                <h1>Tổng quan thư viện</h1>
                <p>
                    Ngày <?= htmlspecialchars(
                        $today->format('d/m/Y'),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> · Dữ liệu thời gian thực từ CSDL Thư viện FoL
                </p>
            </section>

            <!-- Thống kê nhanh từ dữ liệu mẫu -->
            <section class="staff-stats" aria-label="Thống kê nhanh">
                <?php foreach ($statistics as $statistic): ?>
                    <article class="staff-stat">
                        <h2><?= htmlspecialchars(
                            $statistic['label'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></h2>

                        <strong><?= htmlspecialchars(
                            $statistic['value'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></strong>

                        <p><?= htmlspecialchars(
                            $statistic['note'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></p>
                    </article>
                <?php endforeach; ?>
            </section>

            <!-- Các ca phòng đọc mẫu -->
            <section class="staff-panel" aria-labelledby="shift-title">
                <h2 id="shift-title">Phòng đọc hôm nay</h2>

                <div class="staff-shifts">
                    <?php foreach ($readingShifts as $shift): ?>
                        <article class="staff-shift">
                            <h3><?= htmlspecialchars(
                                $shift['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></h3>

                            <p><?= htmlspecialchars(
                                $shift['time'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></p>

                            <strong>
                                <?= htmlspecialchars(
                                    (string) ($shift['booked'] ?? 0),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> / <?= htmlspecialchars(
                                    (string) ($shift['capacity'] ?? 40),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> chỗ đã đặt
                            </strong>
                        </article>
                    <?php endforeach; ?>
                </div>

                <p class="staff-note">
                    Số liệu ghi nhận theo các ca đọc và lượt đặt chỗ thực tế trong ngày tại Thư viện FoL.
                </p>
            </section>

            <!-- Phiếu cần theo dõi -->
            <section class="staff-panel" aria-labelledby="attention-title">
                <h2 id="attention-title">Phiếu cần chú ý</h2>

                <?php if ($attentionLoans === []): ?>
                    <!-- Trạng thái danh sách rỗng -->
                    <p class="staff-note">Không có phiếu cần xử lý.</p>
                <?php else: ?>
                    <!-- Bảng phiếu có thể cuộn ngang -->
                    <div
                        class="staff-table-wrap"
                        tabindex="0"
                        role="region"
                        aria-label="Danh sách phiếu cần chú ý"
                    >
                        <table class="staff-table">
                            <thead>
                                <tr>
                                    <th scope="col">Mã phiếu</th>
                                    <th scope="col">Sinh viên</th>
                                    <th scope="col">Sách</th>
                                    <th scope="col">Trạng thái</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($attentionLoans as $loan): ?>
                                    <!-- Một phiếu mẫu -->
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars(
                                            $loan['code'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></th>

                                        <td><?= htmlspecialchars(
                                            $loan['student'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></td>

                                        <td><?= htmlspecialchars(
                                            $loan['book'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></td>

                                        <td>
                                            <?php
                                                $status = $loan['status'] ?? 'Chờ xử lý';
                                                $badgeStyle = match ($status) {
                                                    'Quá hạn' => 'background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;',
                                                    'Chờ nhận sách' => 'background:#e0f2fe;color:#0369a1;border:1px solid #7dd3fc;',
                                                    'Chờ duyệt' => 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;',
                                                    'Đang mượn' => 'background:#dcfce7;color:#15803d;border:1px solid #86efac;',
                                                    default => 'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;',
                                                };
                                            ?>
                                            <span class="staff-badge" style="<?= $badgeStyle ?>font-weight:600;">
                                                <?= htmlspecialchars(
                                                    $status,
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Ghi chú vận hành -->
            <p class="staff-note">
                Hệ thống đồng bộ dữ liệu thời gian thực giữa phiếu mượn, bản sách vật lý và phòng đọc.
            </p>
        </div>
    </main>
</body>
</html>