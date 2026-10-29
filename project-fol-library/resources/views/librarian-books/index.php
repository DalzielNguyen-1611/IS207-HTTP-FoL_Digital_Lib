<?php

declare(strict_types=1);

// Dữ liệu sách mẫu dùng chung, chưa kết nối CSDL.
$books = require dirname(__DIR__, 2) . '/mock/books.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý sách · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header khu vực cán bộ -->
    <header class="staff-header">
        <a class="staff-brand" href="/librarian">
            <img
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Khu vực cán bộ</small>
            </span>
        </a>

        <a class="staff-public-link" href="/">Về trang chủ</a>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng nghiệp vụ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>

            <a href="/librarian">Tổng quan</a>
            <a href="/librarian/books" aria-current="page">Quản lý sách</a>

            <button type="button" disabled>Mượn / trả · Sắp có</button>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề trang quản lý sách -->
            <section class="staff-intro">
                <span class="staff-eyebrow">QUẢN LÝ TÀI LIỆU</span>
                <h1>Quản lý sách</h1>
                <p>Tra cứu thông tin, số lượng và vị trí tài liệu mẫu.</p>
            </section>

            <!-- Danh sách và tìm kiếm tài liệu -->
            <section class="staff-panel" aria-labelledby="book-list-title">
                <h2 id="book-list-title">Danh sách sách</h2>

                <!-- Thêm tài liệu vào bảng mẫu -->
                <button class="staff-action-button" id="add-book-button" type="button">
                    + Thêm sách mới
                </button>

                <!-- Biểu mẫu tìm kiếm -->
                <form class="staff-search" id="staff-book-search">
                    <input
                        id="staff-book-keyword"
                        type="search"
                        aria-label="Tìm mã sách, tên sách hoặc tác giả"
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

                <!-- Trạng thái kết quả rỗng -->
                <p class="staff-note" id="staff-book-empty" hidden>
                    Không tìm thấy sách phù hợp.
                </p>

                <!-- Bảng sách cuộn ngang trên màn hình nhỏ -->
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
                                <th scope="col">Thao tác</th>
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

                                    <td data-field="title"><?= htmlspecialchars(
                                        $book['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td data-field="author"><?= htmlspecialchars(
                                        $book['author'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td data-field="available">
                                        <?= htmlspecialchars(
                                            (string) $book['available'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?> bản
                                    </td>

                                    <td data-field="location"><?= htmlspecialchars(
                                        $book['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <!-- Hai thao tác độc lập: xem chi tiết và chỉnh sửa -->
                                    <td>
                                        <div class="staff-book-actions">
                                            <a
                                                class="staff-action-button"
                                                href="/book-detail?id=<?= htmlspecialchars(
                                                    (string) $book['id'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >
                                                Xem sách
                                            </a>

                                            <button
                                                class="staff-action-button"
                                                type="button"
                                                data-edit-book
                                            >
                                                Sửa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Phạm vi dữ liệu mẫu -->
            <p class="staff-note">
                Số lượng hiển thị là số bản có thể mượn,
                không phải tổng số bản thư viện sở hữu.
                Chưa kết nối dữ liệu quản lý thật.
            </p>

            <!-- Thông báo kết quả chỉnh sửa -->
            <p id="staff-edit-feedback" class="staff-note" role="status"></p>
        </div>
    </main>

    <!-- Hộp chỉnh sửa sách mẫu -->
    <dialog
        class="staff-book-dialog"
        id="edit-book-dialog"
        aria-labelledby="edit-book-title"
    >
        <form id="edit-book-form">
            <h2 id="edit-book-title">Chỉnh sửa sách</h2>

            <!-- Thông tin tài liệu -->
            <div class="staff-edit-field">
                <label for="edit-title">Tên sách</label>
                <input id="edit-title" type="text" maxlength="200" required>
            </div>

            <div class="staff-edit-field">
                <label for="edit-author">Tác giả</label>
                <input id="edit-author" type="text" maxlength="150" required>
            </div>

            <!-- Số lượng có thể mượn -->
            <div class="staff-edit-field">
                <label for="edit-available">Số bản có thể mượn</label>
                <input
                    id="edit-available"
                    type="number"
                    min="0"
                    max="10000"
                    step="1"
                    required
                >
            </div>

            <!-- Vị trí tài liệu -->
            <div class="staff-edit-position">
                <div class="staff-edit-field">
                    <label for="edit-shelf">Kệ</label>
                    <input
                        id="edit-shelf"
                        type="number"
                        min="1"
                        max="6"
                        step="1"
                        required
                    >
                </div>

                <div class="staff-edit-field">
                    <label for="edit-cell">Ô</label>
                    <input
                        id="edit-cell"
                        type="number"
                        min="1"
                        max="4"
                        step="1"
                        required
                    >
                </div>
            </div>

            <!-- Giới hạn bản thử -->
            <p class="staff-note">
                Sơ đồ mẫu có 6 kệ, mỗi kệ 4 ô.
                Thay đổi chỉ áp dụng cho bảng đang mở, chưa lưu vào hệ thống.
            </p>

            <!-- Kết thúc thao tác chỉnh sửa -->
            <div class="staff-edit-actions">
                <button id="cancel-edit-book" type="button">Hủy</button>
                <button
                    class="staff-edit-save"
                    id="save-book-button"
                    type="submit"
                >
                    Lưu thay đổi mẫu
                </button>
            </div>
        </form>
    </dialog>

    <!-- Tìm kiếm trong danh sách sách mẫu -->
    <script src="/assets/js/librarian-books.js" defer></script>
</body>
</html>