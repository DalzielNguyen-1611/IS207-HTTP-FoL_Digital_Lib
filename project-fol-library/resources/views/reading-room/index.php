<?php

declare(strict_types=1);

// Đọc lịch của tuần được chọn; chỉ tuần kế tiếp được phép đặt chỗ.
$bookingService = new \App\Services\RoomBookingService();
try {
    $schedule = $bookingService->getSchedule(
        isset($_GET['week']) ? (string)$_GET['week'] : null
    );
} catch (\DomainException $exception) {
    http_response_code(400);
    echo htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

$days = $schedule['days'];
$shifts = $schedule['shifts'];
$capacity = $schedule['capacity'];
$selectedWeek = new \DateTimeImmutable($days[0]['date'], new \DateTimeZone('Asia/Ho_Chi_Minh'));
$nextBookableWeek = (new \DateTimeImmutable('today', new \DateTimeZone('Asia/Ho_Chi_Minh')))
    ->modify('next monday');
$isBookableWeek = $selectedWeek->format('Y-m-d') === $nextBookableWeek->format('Y-m-d');
$previousWeek = $selectedWeek->modify('-7 days')->format('Y-m-d');
$nextWeek = $selectedWeek->modify('+7 days')->format('Y-m-d');
$csrfToken = \App\Core\Session::get('csrf_token');
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    \App\Core\Session::set('csrf_token', $csrfToken);
}
$canBook = \App\Core\Session::isAuthenticated()
    && in_array(\App\Core\Session::getRoleCode(), ['STUDENT', 'ADMIN'], true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Phòng đọc · Thư viện Khoa Văn học</title>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

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

            <!-- Menu thả xuống: Dịch vụ thư viện (Active) -->
            <div class="nav-dropdown is-active">
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
                    <a href="/reading-room" class="nav-dropdown-item active" role="menuitem">
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

    <main
        class="page"
        data-can-book="<?= $canBook ? 'true' : 'false' ?>"
        data-bookable-week="<?= $isBookableWeek ? 'true' : 'false' ?>"
        data-week-start="<?= htmlspecialchars($selectedWeek->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
    >
        <!-- Giới thiệu & hướng dẫn đặt chỗ -->
        <section class="room-intro">
            <span class="eyebrow">KHÔNG GIAN DÀNH CHO SỰ TẬP TRUNG</span>
            <h1>Đặt chỗ phòng đọc</h1>
            <p>Chọn ngày và ca phù hợp với lịch học của bạn.</p>
        </section>

        <p class="room-notice">
            Ghế được hệ thống tự động phân bổ. Chỉ có thể đặt chỗ cho tuần kế tiếp.
            Bạn cần đăng nhập bằng tài khoản sinh viên để đặt.
        </p>

        <!-- Lịch phòng đọc theo ngày và ca -->
        <section aria-labelledby="schedule-title">
            <div class="room-heading">
                <h2 id="schedule-title">Lịch phòng đọc</h2>
                <span><?= $isBookableWeek ? 'Tuần đang mở đặt chỗ' : 'Chỉ xem lịch · Chưa mở đặt chỗ' ?></span>
            </div>

            <nav class="room-week-navigation" aria-label="Chuyển tuần lịch phòng đọc">
                <a href="/reading-room?week=<?= htmlspecialchars($previousWeek, ENT_QUOTES, 'UTF-8') ?>">
                    ← Tuần trước
                </a>
                <strong>
                    <?= htmlspecialchars($days[0]['display'], ENT_QUOTES, 'UTF-8') ?>
                    –
                    <?= htmlspecialchars($days[array_key_last($days)]['display'], ENT_QUOTES, 'UTF-8') ?>
                    <?= htmlspecialchars($selectedWeek->format('Y'), ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <a href="/reading-room?week=<?= htmlspecialchars($nextWeek, ENT_QUOTES, 'UTF-8') ?>">
                    Tuần sau →
                </a>
            </nav>

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
                        <?php foreach ($shifts as $shift): ?>
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
                                            data-date="<?= htmlspecialchars($day['date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-shift-id="<?= htmlspecialchars((string)$shift['id_ca_doc'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-label="<?= htmlspecialchars($slotLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            data-free="<?= htmlspecialchars((string) $free, ENT_QUOTES, 'UTF-8') ?>"
                                            data-capacity="<?= htmlspecialchars((string) $capacity, ENT_QUOTES, 'UTF-8') ?>"
                                            aria-label="<?= htmlspecialchars(
                                                $slotLabel . ' · Đã đặt ' . ($capacity - $free) . ' trên ' . $capacity . ' chỗ',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                            <?= ($free === 0 || !$isBookableWeek) ? 'disabled' : '' ?>
                                            data-start="<?= htmlspecialchars(
                                                $day['date'] . 'T' . $shift['start'] . ':00+07:00',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            <strong>
                                                <?= htmlspecialchars((string) ($capacity - $free), ENT_QUOTES, 'UTF-8') ?>
                                                / <?= htmlspecialchars((string) $capacity, ENT_QUOTES, 'UTF-8') ?>
                                                chỗ đã đặt
                                            </strong>
                                            <?php if (!$isBookableWeek): ?>
                                                <span>Chỉ đặt được tuần kế tiếp</span>
                                            <?php elseif ($free > 0): ?>
                                                <span>Đặt chỗ +</span>
                                            <?php else: ?>
                                                <span>Chọn ca khác</span>
                                            <?php endif; ?>
                                        </button>
                                        <button
                                            class="room-booking-cancel"
                                            type="button"
                                            data-date="<?= htmlspecialchars($day['date'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-shift-id="<?= htmlspecialchars((string)$shift['id_ca_doc'], ENT_QUOTES, 'UTF-8') ?>"
                                            aria-label="<?= htmlspecialchars('Hủy đặt chỗ ' . $slotLabel, ENT_QUOTES, 'UTF-8') ?>"
                                            hidden
                                        >
                                            Hủy đặt chỗ
                                        </button>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Kết quả thao tác đặt chỗ -->
        <p class="room-feedback" id="room-feedback" role="status"></p>
    </main>

    <!-- Hộp xác nhận ca đọc -->
    <dialog class="room-dialog" id="booking-dialog" aria-labelledby="booking-title">
        <form method="dialog">
            <h2 id="booking-title">Xác nhận đặt chỗ</h2>
            <p id="booking-description"></p>

            <p class="room-notice">
                Vui lòng đến đúng giờ và giữ yên lặng trong phòng đọc.
                Ghế sẽ được hệ thống tự động chọn nếu còn chỗ.
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

    <!-- Tương tác đặt chỗ -->
    <script src="/assets/js/reading-room.js" defer></script>
</body>
</html>