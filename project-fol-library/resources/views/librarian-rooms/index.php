<?php

declare(strict_types=1);

$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

// Đọc ca đọc và danh sách đặt chỗ thực tế từ CSDL
$roomRepo = new \App\Repositories\RoomRepository();
$selectedDate = isset($_GET['date']) && trim((string)$_GET['date']) !== ''
    ? trim((string)$_GET['date'])
    : $today->format('Y-m-d');

$rawShifts = $roomRepo->getShifts();
$shifts = [];
foreach ($rawShifts as $sh) {
    $shifts[(string)$sh['id_ca_doc']] = $sh['label'] . ' · ' . $sh['time'];
}

$bookings = $roomRepo->getBookingsByDate($selectedDate);

$attendanceOptions = [
    'reserved' => 'Chưa check-in',
    'checked-in' => 'Đã check-in',
    'no-show' => 'No-show',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý phòng đọc · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/librarian-rooms.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/" title="Về trang chủ Thư viện">
            <img
                class="brand-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >

            <span>
                <span class="brand-label">USSH · VNU-HANOI</span>
                <strong>Thư viện Khoa Văn học</strong>
            </span>
        </a>

        <nav class="navigation" aria-label="Điều hướng chính">
            <a href="/">Trang chủ</a>

            <!-- Menu thả xuống: Dịch vụ thư viện -->
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                    <span>Dịch vụ</span>
                    <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="nav-dropdown-menu" role="menu">
                    <a href="/books" class="nav-dropdown-item" role="menuitem">
                        <span class="nav-dropdown-icon">📚</span>
                        <div class="nav-dropdown-text">
                            <span class="nav-dropdown-title">Tra cứu sách</span>
                            <span class="nav-dropdown-desc">Kho 1.200+ giáo trình, chuyên khảo & luận văn</span>
                        </div>
                    </a>
                    <a href="/reading-room" class="nav-dropdown-item" role="menuitem">
                        <span class="nav-dropdown-icon">🪑</span>
                        <div class="nav-dropdown-text">
                            <span class="nav-dropdown-title">Phòng đọc & Chỗ ngồi</span>
                            <span class="nav-dropdown-desc">Đặt chỗ tự học theo ca, xem sơ đồ chỗ trống</span>
                        </div>
                    </a>
                </div>
            </div>

            <?php 
                $isAuthenticated = \App\Core\Session::isAuthenticated();
                $navRole = $isAuthenticated ? \App\Core\Session::getRoleCode() : null;
            ?>

            <?php if ($navRole !== 'ADMIN' && $navRole !== 'LIBRARIAN'): ?>
                <a href="/about">Giới thiệu</a>
                <a href="/rules">Nội quy</a>
                <a href="/feedback">Góp ý</a>
                <a href="/contact">Liên hệ</a>
            <?php endif; ?>

            <?php if ($isAuthenticated): ?>
                <?php if ($navRole === 'ADMIN'): ?>
                    <a href="/librarian" class="active" aria-current="page">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian" class="active" aria-current="page">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực người dùng / Đăng xuất -->
        <?php if ($isAuthenticated): ?>
            <?php
                $currentUser = \App\Core\Session::getAuthUser();
                $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8');
                $dashboardUrl = match ($navRole) {
                    'ADMIN' => '/admin/settings',
                    'LIBRARIAN' => '/librarian',
                    default => '/profile',
                };
            ?>
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
                <?php if ($navRole !== 'ADMIN'): ?>
                    <a href="<?= $dashboardUrl ?>" class="login-button" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;padding:8px 16px;text-decoration:none;" title="Tài khoản của bạn">
                        <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                        <span><?= $userName ?></span>
                    </a>
                <?php endif; ?>
                <a href="/logout" class="login-button" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:8px 14px;color:#dc2626;border-color:#fecaca;background:#fff5f5;text-decoration:none;" title="Đăng xuất khỏi hệ thống">
                    Đăng xuất
                </a>
            </div>
        <?php else: ?>
            <a class="login-button" href="/login">
                Đăng nhập
            </a>
        <?php endif; ?>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng nghiệp vụ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>

            <a href="/librarian">Tổng quan</a>
            <a href="/librarian/books">Quản lý sách</a>
            <a href="/librarian/counter">Mượn / trả</a>
            <a href="/librarian/rooms" aria-current="page">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings">Về trang quản trị</a>
                <a href="/profile">Về trang cá nhân (SV)</a>
            <?php endif; ?>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề trang -->
            <section class="staff-intro">
                <span class="staff-eyebrow">THEO DÕI CA ĐỌC</span>
                <h1>Quản lý phòng đọc</h1>
                <p>Theo dõi sinh viên và cập nhật trạng thái tham dự mẫu.</p>
            </section>

            <section class="staff-panel" aria-labelledby="room-list-title">
                <h2 id="room-list-title">Danh sách đặt chỗ</h2>

                <!-- Bộ lọc ngày và ca -->
                <div class="staff-room-filters">
                    <div>
                        <label for="staff-room-date">Ngày theo dõi</label>
                        <input
                            id="staff-room-date"
                            type="date"
                            value="<?= htmlspecialchars(
                                $today->format('Y-m-d'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >
                    </div>

                    <div>
                        <label for="staff-room-shift">Ca đọc</label>
                        <select id="staff-room-shift">
                            <option value="">Tất cả các ca</option>

                            <?php foreach ($shifts as $value => $label): ?>
                                <option value="<?= htmlspecialchars(
                                    (string) $value,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Kết quả lọc và thông báo cập nhật -->
                <p class="staff-note" id="staff-room-count" role="status"></p>
                <p class="staff-note" id="staff-room-feedback" role="status"></p>

                <p class="staff-note" id="staff-room-empty" hidden>
                    Không có lịch đặt phù hợp với ngày và ca đã chọn.
                </p>

                <!-- Bảng theo dõi có thể cuộn ngang -->
                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách sinh viên đặt phòng đọc"
                >
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th scope="col">Mã lịch</th>
                                <th scope="col">Sinh viên</th>
                                <th scope="col">Ca</th>
                                <th scope="col">Tham dự</th>
                                <th scope="col">Vi phạm nội quy</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <!-- Một lịch đặt mẫu -->
                                <tr
                                    data-room-row
                                    data-date="<?= htmlspecialchars($booking['date'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-shift="<?= htmlspecialchars($booking['shift'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-booking-code="<?= htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <th scope="row">
                                        <?= htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8') ?>
                                    </th>

                                    <td>
                                        <strong><?= htmlspecialchars(
                                            $booking['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></strong>
                                        <br>
                                        <?= htmlspecialchars($booking['code'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(
                                            $shifts[$booking['shift']],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <!-- Trạng thái tham dự độc lập với vi phạm -->
                                    <td>
                                        <select
                                            data-attendance
                                            aria-label="<?= htmlspecialchars(
                                                'Tham dự của ' . $booking['name'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <?php foreach ($attendanceOptions as $value => $label): ?>
                                                <option
                                                    value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
                                                    <?= $booking['attendance'] === $value ? 'selected' : '' ?>
                                                >
                                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>

                                    <!-- Đánh dấu vi phạm mẫu -->
                                    <td>
                                        <label class="staff-room-violation">
                                            <input
                                                type="checkbox"
                                                data-violation
                                                <?= $booking['violation'] ? 'checked' : '' ?>
                                            >
                                            Có vi phạm
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phạm vi bản thử -->
                <p class="staff-note">
                    Dữ liệu minh họa có 3 lịch hôm nay và 1 lịch hôm qua,
                    kể cả ngày nghỉ để thử giao diện.
                    Chưa liên kết lịch sinh viên hoặc áp dụng chế tài.
                    Tải lại trang sẽ khôi phục trạng thái mẫu.
                </p>
            </section>
        </div>
    </main>

    <!-- Tương tác lọc và cập nhật tham dự -->
    <script src="/assets/js/librarian-rooms.js" defer></script>
</body>
</html>