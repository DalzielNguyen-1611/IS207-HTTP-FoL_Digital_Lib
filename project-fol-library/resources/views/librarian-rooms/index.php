<?php

declare(strict_types=1);

// Ngày mẫu theo giờ Việt Nam.
$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

// Các ca đọc dùng để lọc.
$shifts = [
    '1' => 'Ca 1 · 07:30 – 09:30',
    '2' => 'Ca 2 · 09:45 – 11:45',
    '3' => 'Ca 3 · 13:00 – 15:00',
    '4' => 'Ca 4 · 15:15 – 17:15',
];

// Danh sách đặt chỗ minh họa, không đọc dữ liệu sinh viên thật.
$bookings = [
    [
        'id' => 'PD001',
        'code' => 'SV2026001',
        'name' => 'Nguyễn Minh Anh',
        'date' => $today->format('Y-m-d'),
        'shift' => '1',
        'attendance' => 'reserved',
        'violation' => false,
    ],
    [
        'id' => 'PD002',
        'code' => 'SV2026002',
        'name' => 'Trần Hoàng Nam',
        'date' => $today->format('Y-m-d'),
        'shift' => '1',
        'attendance' => 'checked-in',
        'violation' => false,
    ],
    [
        'id' => 'PD003',
        'code' => 'SV2026003',
        'name' => 'Lê Thu Hà',
        'date' => $today->format('Y-m-d'),
        'shift' => '3',
        'attendance' => 'no-show',
        'violation' => false,
    ],
    [
        'id' => 'PD004',
        'code' => 'SV2026004',
        'name' => 'Phạm Gia Huy',
        'date' => $today->modify('-1 day')->format('Y-m-d'),
        'shift' => '2',
        'attendance' => 'checked-in',
        'violation' => true,
    ],
];

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

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/librarian-rooms.css">
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