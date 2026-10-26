<?php

declare(strict_types=1);

//Đọc dữ liệu dùng chung
$books = require dirname (__DIR__, 2) . '/mock/books.php';

//Chỉ nhận mã sách là số nguyên dương; thiếu mã thì mở mẫu số 2
$rawId = $_GET['id'] ?? '2';

$bookId = is_string($rawId)
    ? filter_var(
        $rawId,
        FILTER_VALIDATE_INT,
        ['option' => ['min_range' => 1]]
    )
    : false;

$book = $bookId !== false ? ($books[$bookId] ?? null) : null;

//Trạng thái không tìm thấy sách
if ($book === null) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <!-- Metadata trang không tìm thấy tài liệu -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Không tìm thấy sách</title>
        <link rel="stylesheet" href="/assets/css/base.css">
        <link rel="stylesheet" href="/assets/css/home.css">
        <link rel="stylesheet" href="/assets/css/70years.css">
    </head>
    <body>
        <!-- Thông báo & Điều hướng về danh sách --> 
        <main class="page">
            <h1>Không tìm thấy sách</h1>
            <p>Tài liệu này chưa có trong danh sách mẫu.</p>
            <a href="/books">← Trở về tra cứu sách</a> 
        </main>
    </body>
    </html>
    <?php
    return;
}

//Các kệ và ô minh họa trong bản thử
$shelves = range(1, 6);
$cells = range(1, 4);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & Tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?> · Thư viện</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/book-detail.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/">
            <img
                class="brand-logo"
                src="assets/images/logo.jpg"
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
        </nav>
    </header>

    <main class="page">
        <!-- Điều hướng trở về danh sách -->
        <a class="detail-back" href="/books">← Trở về tra cứu sách</a>

        <!-- Bìa minh họa & Thông tin sách -->
        <section class="detail-layout" aria-labelledby="book-title">
            <div class="detail-cover-area">
                <div class="detail-cover" aria-hidden="true">
                    <span>TỦ SÁCH VĂN HỌC</span>
                    <strong>
                        <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                    <span class="detail-decoration">❧</span>
                </div>
                <p>Bìa minh họa</p>
            </div>

            <div class="detail-information">
                <!-- Phân loại & Tên tài liệu -->
                <p class="eyebrow">
                    <?= htmlspecialchars($book['category'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <h1 id="book-title">
                    <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <p class="detail-author">
                    <?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Tình trạng có thể mượn -->
                <?php if ($book['available'] > 0): ?>
                    <span class="book'status">
                        Còn <?= htmlspecialchars(
                            (string) $book['available'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?> bản 
                    </span>
                <?php else: ?>
                    <span class="book-status unavailable">
                        Đang được mượn hết
                    </span>
                <?php endif; ?>

                <!-- Mô tả & Thuộc tính tài liệu -->
                <p class="detail-description">
                    <?= htmlspecialchars($book['description'], ENT_QUOTES, 'UTF-8') ?> 
                </p>

                <dl class="detail-metadata">
                    <div>
                        <dt>Thể loại</dt>
                        <dd><?= htmlspecialchars($book['genre'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Vị trí</dt>
                        <dd>
                            Kệ <?= htmlspecialchars(
                                (string) $book['shelf'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                            · Ô <?= htmlspecialchars(

                                (string) $book['cell'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?> 
                        </dd> 
                    </div>
                </dl>

                <!-- Các thao tác thử nghiệm -->
                <div class="detail-actions">
                    <button
                        class="detail-primary"
                        id="borrow-demo"
                        type="button"
                        <?= $book['available'] > 0 ? '' : 'disabled' ?>
                    >
                        Đăng ký mượn 
                    </button>

                    <button
                        class="detail-secondary"
                        id="favorite-demo"
                        type="button"
                        aria-presses="false"
                        data-book-id="<?= htmlspecialchars(
                            (string) $book['id'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >
                        Thêm vào yêu thích 
                    </button>
                </div>

                <p class="preview-note" id="detail-feedback" role="status">
                    Bản thử giao diện · Chưa tạo phiếu mượn hoặc lưu yêu thích. 
                </p>
            </div>
        </section>

        <!-- Sơ đồ minh họa vị trí kệ -->
        <section class="detail-location" aria-labelledby="location-title">
            <h2 id="location-title">Tìm sách trong thư viện</h2>
            <p>Kệ được đánh dấu bên dưới là vị trí của tài liệu.</p>

            <div class="shelf-map">
                <?php foreach ($shelves as $shelf): ?>
                    <div class="shelf-position <?= $shelf === $book['shelf'] ? 'is-selected' : '' ?>">
                        Kệ <?= htmlspecialchars((string) $shelf, ENT_QUOTES, 'UTF-8') ?> 
                    </div>
                <?php endforeach; ?> 
            </div>

            <!-- Vị trí ô trong kệ đang chọn -->
            <h3>Các ô trong kệ của sách</h3>

            <div class="cell-map">
                <?php foreach($cells as $cell): ?>
                    <div class="cell-position <?= $cell === $book['cell'] ? 'is-selected' : '' ?>">
                        Ô <?= htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endforeach; ?> 
            </div>

            <p class="preview-note">
                Sơ đồ minh họa, chưa phải mặt bằng thực tế của thư viện. 
            </p>
        </selection>
    </main>

    <!-- Tương tác mẫu của trang chi tiết -->
    <script src="/assets/js/book-detail.js" defer></script>
</body>
</html>