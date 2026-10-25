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
            <button type="button" disabled>Mượn / trả · Sắp có</button>
            <button type="button" disabled>Phòng đọc · Sắp có</button>
            <button type="button" disabled>Báo cáo · Sắp có</button>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề quản lý sách -->
            <section class="staff-intro">
                <span class="staff-eyebrow">QUẢN LÝ TÀI LIỆU</span>
                <h1>Quản lý sách</h1>
                <p>Tra cứu thông tin, số lượng và vị trí tài liệu mẫu.</p>
            </section>

            <section class="staff-panel" aria-labelledby="book-list-title">
                <!-- Thanh tìm kiếm tài liệu -->
                <h2 id="book-list-title">Danh sách sách</h2>

                <form class="staff-search" id="staff-book-search">
                    <input
                        id="staff-book-keyword"
                        type="search"
                        aria-label="Tìm theo mã sách, tên sách hoặc tác giả"
                        placeholder="Tìm mã sách, tên sách hoặc tác giả…"
                    >
                    <button type="submit">Tìm kiếm</button>
                </form>

                <!-- Số lượng kết quả -->
                <p class="staff-note" id="staff-book-count" role="status">
                    <?= htmlspecialchars(
                        (string) count($books),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> tài liệu mẫu
                </p>

                <!-- Trạng thái không có kết quả -->
                <p class="staff-note" id="staff-book-empty" hidden>
                    Không tìm thấy sách phù hợp.
                </p>

                <!-- Bảng thông tin sách -->
                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách thông tin sách"
                >
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th scope="col">Mã sách</th>
                                <th scope="col">Tên sách</th>
                                <th scope="col">Tác giả</th>
                                <th scope="col">Có thể mượn</th>
                                <th scope="col">Vị trí</th>
                                <th scope="col">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <!-- Một tài liệu mẫu -->
                                <tr
                                    data-book-row
                                    data-search="<?= htmlspecialchars(
                                        'BK' . $book['id'] . ' ' .
                                        $book['title'] . ' ' . $book['author'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <th scope="row">
                                        BK<?= htmlspecialchars(
                                            (string) $book['id'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </th>

                                    <td><?= htmlspecialchars(
                                        $book['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $book['author'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        (string) $book['available'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?> bản</td>

                                    <td><?= htmlspecialchars(
                                        $book['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td>
                                        <a
                                            class="staff-book-link"
                                            href="/book-detail?id=<?= htmlspecialchars(
                                                (string) $book['id'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"
                                        >
                                            Xem sách ↗
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Giới hạn bản thử -->
            <p class="staff-note">
                Số lượng hiển thị là số bản có thể mượn, không phải tổng
                số bản thư viện sở hữu. Chưa kết nối dữ liệu quản lý thật.
            </p>
        </div>
    </main>
    <!-- Tìm kiếm trong danh sách sách mẫu -->
    <script src="/assets/js/librarian-books.js" defer></script>
</body>
</html>