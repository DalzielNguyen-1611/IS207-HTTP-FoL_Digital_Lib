<?php

declare(strict_types=1);

// Ngày hiển thị theo múi giờ Việt Nam.
$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

// Số liệu minh họa, chưa tổng hợp từ CSDL.
$statistics = [
    ['label' => 'Sách đang mượn', 'value' => '128', 'note' => 'Tổng phiếu đang mở'],
    ['label' => 'Sách quá hạn', 'value' => '9', 'note' => 'Cần theo dõi xử lý'],
    ['label' => 'Lượt đặt phòng hôm nay', 'value' => '36', 'note' => 'Trong 4 ca đọc'],
];

// Các ca đọc mẫu trong ngày.
$readingShifts = [
    ['name' => 'Ca 1', 'time' => '07:30 – 09:30', 'booked' => 12],
    ['name' => 'Ca 2', 'time' => '09:45 – 11:45', 'booked' => 8],
    ['name' => 'Ca 3', 'time' => '13:00 – 15:00', 'booked' => 10],
    ['name' => 'Ca 4', 'time' => '15:15 – 17:15', 'booked' => 6],
];

// Phiếu cần chú ý, hoàn toàn là dữ liệu giả.
$attentionLoans = [
    [
        'code' => 'PM001',
        'student' => 'SV2026001',
        'book' => 'Dẫn luận ngôn ngữ học',
        'status' => 'Quá hạn',
    ],
    [
        'code' => 'PM002',
        'student' => 'SV2026002',
        'book' => 'Truyện Kiều',
        'status' => 'Chờ nhận sách',
    ],
];
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
        <a class="staff-brand" href="/librarian">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Khu vực cán bộ · Bản thử giao diện</small>
            </span>
        </a>

        <a class="staff-public-link" href="/">Về trang sinh viên</a>
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
                    ) ?> · Số liệu minh họa
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
                                    (string) $shift['booked'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> / 40 chỗ đã đặt
                            </strong>
                        </article>
                    <?php endforeach; ?>
                </div>

                <p class="staff-note">
                    Lịch minh họa, không xác nhận thư viện mở cửa
                    vào ngày đang hiển thị.
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
                                            <span class="staff-badge">
                                                <?= htmlspecialchars(
                                                    $loan['status'],
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

            <!-- Phạm vi bản thử -->
            <p class="staff-note">
                Chưa xác thực vai trò cán bộ.
                Số liệu minh họa độc lập với các trang nghiệp vụ.
            </p>
        </div>
    </main>
</body>
</html>