<?php

declare(strict_types=1);

// Đọc lịch phòng đọc và số chỗ trống thực tế từ CSDL
$roomRepo = new \App\Repositories\RoomRepository();
$schedule = $roomRepo->getWeeklySchedule();

$days = $schedule['days'];
$shifts = $schedule['shifts'];
$capacity = $schedule['capacity'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phòng đọc · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/reading-room.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header & điều hướng -->
    <header class="header">
        <a class="brand" href="/">
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
            <a href="/books">Tra cứu sách</a>
            <a href="/reading-room" class="active" aria-current="page">Phòng đọc</a>
            <?php if (\App\Core\Session::isAuthenticated()): ?>
                <?php $navRole = \App\Core\Session::getRoleCode(); ?>
                <?php if ($navRole === 'ADMIN'): ?>
                    <a href="/profile">Cá nhân</a>
                    <a href="/librarian">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực người dùng / Đăng nhập -->
        <?php if (\App\Core\Session::isAuthenticated()): ?>
            <?php
                $currentUser = \App\Core\Session::getAuthUser();
                $currentRole = \App\Core\Session::getRoleCode();
                $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8');
                $dashboardUrl = match ($currentRole) {
                    'ADMIN' => '/admin/settings',
                    'LIBRARIAN' => '/librarian',
                    default => '/profile',
                };
            ?>
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;margin-left:auto;">
                <a href="<?= $dashboardUrl ?>" class="login-button" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;padding:8px 16px;text-decoration:none;" title="Tài khoản của bạn">
                    <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                    <span><?= $userName ?></span>
                </a>
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

    <main class="page">
        <!-- Giới thiệu & hướng dẫn đặt chỗ -->
        <section class="room-intro">
            <span class="eyebrow">KHÔNG GIAN DÀNH CHO SỰ TẬP TRUNG</span>
            <h1>Đặt chỗ phòng đọc</h1>
            <p>Chọn ngày và ca phù hợp với lịch học của bạn.</p>
        </section>

        <p class="room-notice">
            Lịch mẫu cho tuần kế tiếp · Mỗi ca có 40 chỗ.
            Bản thử chưa yêu cầu đăng nhập hoặc lưu đặt chỗ thật.
        </p>

        <!-- Lịch phòng đọc theo ngày và ca -->
        <section aria-labelledby="schedule-title">
            <div class="room-heading">
                <h2 id="schedule-title">Lịch phòng đọc</h2>
                <span>Còn chỗ · Hết chỗ · Đã chọn đặt</span>
            </div>

            <p class="room-help" id="schedule-help">
                Trên điện thoại, vuốt ngang bảng để xem đủ các ngày.
            </p>

            <div
                class="room-table-wrap"
                tabindex="0"
                role="region"
                aria-label="Bảng lịch phòng đọc"
                aria-describedby="schedule-help"
            >
                <table class="room-table">
                    <caption class="room-caption">
                        Tuần từ ngày <?= htmlspecialchars($days[0]['display'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        đến ngày <?= htmlspecialchars(($days[array_key_last($days)]['display'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </caption>

                    <!-- Tiêu đề các ngày trong tuần -->
                    <thead>
                        <tr>
                            <th scope="col">Ca đọc</th>

                            <?php foreach ($days as $day): ?>
                                <th scope="col">
                                    <?= htmlspecialchars($day['label'], ENT_QUOTES, 'UTF-8') ?>
                                    <small>
                                        <?= htmlspecialchars($day['display'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($shifts as $shiftIndex => $shift): ?>
                            <!-- Một ca đọc trong tuần -->
                            <tr>
                                <th scope="row">
                                    <?= htmlspecialchars($shift['label'], ENT_QUOTES, 'UTF-8') ?>
                                    <small>
                                        <?= htmlspecialchars($shift['time'], ENT_QUOTES, 'UTF-8') ?>
                                    </small>
                                </th>

                                <?php foreach ($days as $dayIndex => $day): ?>
                                    <?php
                                    $free = $shift['free'][$dayIndex];
                                    $slotLabel = $day['label'] . ' ' . $day['display']
                                        . ' · ' . $shift['label'] . ' · ' . $shift['time'];
                                    ?>

                                    <td>
                                        <button
                                            class="room-slot"
                                            type="button"
                                            data-slot="<?= htmlspecialchars(
                                                $day['date'] . '-' . $shiftIndex,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            data-label="<?= htmlspecialchars($slotLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            data-free="<?= htmlspecialchars((string) $free, ENT_QUOTES, 'UTF-8') ?>"
                                            aria-label="<?= htmlspecialchars(
                                                $slotLabel . ' · Còn ' . $free . ' chỗ',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            <?= $free === 0 ? 'disabled' : '' ?>
                                            data-start="<?= htmlspecialchars(
                                                $day['date'] . 'T' . $shift['start'] . ':00+07:00',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <?php if ($free > 0): ?>
                                                <strong>
                                                    <?= htmlspecialchars((string) $free, ENT_QUOTES, 'UTF-8') ?>
                                                    / <?= htmlspecialchars((string) $capacity, ENT_QUOTES, 'UTF-8') ?>
                                                    chỗ
                                                </strong>
                                                <span>Đặt chỗ +</span>
                                            <?php else: ?>
                                                <strong>Hết chỗ</strong>
                                                <span>Chọn ca khác</span>
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Kết quả thao tác đặt chỗ mẫu -->
        <p class="room-feedback" id="room-feedback" role="status"></p>
    </main>

    <!-- Hộp xác nhận ca đọc -->
    <dialog class="room-dialog" id="booking-dialog" aria-labelledby="booking-title">
        <form method="dialog">
            <h2 id="booking-title">Xác nhận đặt chỗ</h2>
            <p id="booking-description"></p>

            <p class="room-notice">
                Vui lòng đến đúng giờ và giữ yên lặng trong phòng đọc.
                Thao tác này chỉ mô phỏng đặt chỗ.
            </p>

            <!-- Điều khiển đóng hoặc xác nhận -->
            <div class="room-dialog-actions">
                <button class="room-cancel" value="cancel" autofocus>
                    Quay lại
                </button>
                <button class="room-confirm" value="confirm">
                    Xác nhận đặt chỗ
                </button>
            </div>
        </form>
    </dialog>

    <!-- Tương tác đặt chỗ mẫu -->
    <script src="/assets/js/reading-room.js" defer></script>
</body>
</html>