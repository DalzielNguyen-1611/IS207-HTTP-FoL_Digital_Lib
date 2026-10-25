<?php

declare(strict_types=1);

// Ngày tham chiếu theo múi giờ Việt Nam.
$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

// Số liệu hoạt động mẫu theo ngày, chưa tổng hợp từ CSDL.
$reports = [
    ['offset' => 0, 'borrowed' => 12, 'returned' => 8],
    ['offset' => -1, 'borrowed' => 15, 'returned' => 10],
    ['offset' => -7, 'borrowed' => 9, 'returned' => 6],
    ['offset' => -35, 'borrowed' => 20, 'returned' => 18],
    ['offset' => -100, 'borrowed' => 14, 'returned' => 12],
    ['offset' => -370, 'borrowed' => 7, 'returned' => 5],
];

$periods = [
    'day' => 'Ngày',
    'week' => 'Tuần',
    'month' => 'Tháng',
    'quarter' => 'Quý',
    'year' => 'Năm',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo thống kê · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/reports.css">
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
        <!-- Điều hướng nghiệp vụ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>
            <a href="/librarian">Tổng quan</a>
            <a href="/librarian/books">Quản lý sách</a>
            <a href="/librarian/counter">Mượn / trả</a>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports" aria-current="page">Báo cáo</a>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề báo cáo -->
            <section class="staff-intro">
                <span class="staff-eyebrow">HOẠT ĐỘNG THƯ VIỆN</span>
                <h1>Báo cáo thống kê</h1>
                <p>Theo dõi lượt mượn và trả theo kỳ báo cáo mẫu.</p>
            </section>

            <!-- Bộ lọc báo cáo -->
            <section class="staff-panel" aria-labelledby="report-filter-title">
                <h2 id="report-filter-title">Chọn kỳ báo cáo</h2>

                <form class="report-filters" id="report-form">
                    <div>
                        <label for="report-period">Kỳ thống kê</label>
                        <select id="report-period">
                            <?php foreach ($periods as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="report-date">Ngày thuộc kỳ cần xem</label>
                        <input
                            id="report-date"
                            type="date"
                            value="<?= htmlspecialchars(
                                $today->format('Y-m-d'),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >
                    </div>

                    <button class="report-primary" type="submit">
                        Xem báo cáo
                    </button>
                </form>

                <p class="staff-note">
                    Tuần tính từ Thứ 2 đến Chủ nhật.
                    Tháng, quý và năm được tính theo lịch.
                </p>
            </section>

            <!-- Tổng số hoạt động trong kỳ đang hiển thị -->
            <p class="staff-note" id="report-range" role="status"></p>

            <section class="staff-stats" aria-label="Tổng hợp báo cáo">
                <article class="staff-stat">
                    <h2>Lượt mượn</h2>
                    <strong id="report-borrowed">0</strong>
                    <p>Tổng lượt trong dữ liệu mẫu</p>
                </article>

                <article class="staff-stat">
                    <h2>Lượt trả</h2>
                    <strong id="report-returned">0</strong>
                    <p>Tổng lượt trong dữ liệu mẫu</p>
                </article>

                <article class="staff-stat">
                    <h2>Ngày có dữ liệu mẫu</h2>
                    <strong id="report-days">0</strong>
                    <p>Không phải tổng số ngày của kỳ</p>
                </article>
            </section>

            <!-- Bảng chi tiết & xuất báo cáo -->
            <section class="staff-panel" aria-labelledby="report-table-title">
                <div class="report-heading">
                    <h2 id="report-table-title">Hoạt động theo ngày</h2>

                    <button
                        class="staff-action-button"
                        id="export-report"
                        type="button"
                    >
                        Xuất CSV
                    </button>
                </div>

                <p class="staff-note" id="report-empty" hidden>
                    Không có dữ liệu mẫu trong kỳ đã chọn.
                </p>

                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Báo cáo hoạt động theo ngày"
                >
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th scope="col">Ngày</th>
                                <th scope="col">Lượt mượn</th>
                                <th scope="col">Lượt trả</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($reports as $report): ?>
                                <?php
                                $date = $today
                                    ->modify($report['offset'] . ' days')
                                    ->format('Y-m-d');
                                ?>

                                <!-- Một ngày có dữ liệu mẫu -->
                                <tr
                                    data-report-row
                                    data-date="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8') ?>"
                                    data-borrowed="<?= htmlspecialchars(
                                        (string) $report['borrowed'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-returned="<?= htmlspecialchars(
                                        (string) $report['returned'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <th scope="row"><?= htmlspecialchars(
                                        implode('/', array_reverse(explode('-', $date))),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></th>

                                    <td><?= htmlspecialchars(
                                        (string) $report['borrowed'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        (string) $report['returned'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="staff-note" id="report-feedback" role="status"></p>

                <!-- Phạm vi dữ liệu và xuất tệp -->
                <p class="staff-note">
                    Số liệu minh họa độc lập với Dashboard và phiếu tại quầy.
                    CSV chỉ xuất các dòng thuộc kỳ đang hiển thị.
                </p>
            </section>
        </div>
    </main>

    <!-- Lọc và xuất báo cáo mẫu -->
    <script src="/assets/js/reports.js" defer></script>
</body>
</html>