<?php

declare(strict_types=1);

//Dữ liệu mẫu trang chủ
$libraryName = 'Thư viện Khoa Văn học';
$description = 'Khám phá tài liệu, tìm nguồn cảm hứng và kết nối với không gian tri thức của bạn.';

//Dữ liệu mẫu sách nổi bật, chưa kết nối CSDL
$featureBooks = [
    [
        'title' => 'Dẫn luận ngôn ngữ học',
        'author' => 'Nguyễn Thiện Giáp',
        'category' => 'Ngôn ngữ học',
        'available' => 12,
    ],
    [
        'title' => 'Truyện Kiều',
        'author' => 'Nguyễn Du',
        'category' => 'Văn học Việt Nam',
        'available' => 5,
    ],
    [
        'title' => 'Lý luận văn học',
        'author' => 'Trần Đình Sử',
        'category' => 'Lý luận & Phê bình',
        'available' => 8,
    ],
    [
        'title' => 'Thi nhân Việt Nam',
        'author' => 'Hoài Thanh · Hoài Chân',
        'category' => 'Văn học Việt Nam',
        'available' => 0,
    ],
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata $ CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($libraryName, ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện $ điều hướng -->
    <header class="header">
        <a class="brand" href="/">
            <img
                class="brand-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >

            <span>
                <span class="brand-label">USSH · VNU-HCM</span>
                <strong>
                    <?= htmlspecialchars($libraryName, ENT_QUOTES, 'UTF-8') ?>
                </strong>
            </span>
        </a>

        <nav class="navigation" aria-label="Điều hướng chính">
            <a href="#home" class="active">Trang chủ</a>
            <a href="/books">Tra cứu sách</a>
        </nav>

        <!-- Liên kết tới trang đăng nhập -->
        <a class="login-button" href="/login">
            Đăng nhập 
        </a>
        
    </header>

    <!-- Banner chính & tìm kiếm mẫu -->
    <main class="page" id="home">
        <section class="hero">
            <div class="hero-content">
                <span class="eyebrow">
                    KHÁM PHÁ · HỌC TẬP · KẾT NỐI
                </span>

                <h1>
                    Mỗi trang sách,<br>
                    một <span>chân trời mới.</span>
                </h1>

                <p>
                    <?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <form class="search-box" id="search">
                    <input
                        type="search"
                        aria-label="Tìm tên sách, tác giả hoặc từ khóa"
                        placeholder="Tìm tên sách, tác giả, từ khóa..."
                    >
                    <button type="submit">Tìm kiếm</button>
                </form>

                <p class="search-hint">
                    Gợi ý: Văn học Việt Nam · Ngôn ngữ học · Văn hóa học
                </p> 
            </div>

            <!-- Minh họa kệ sách bằng CSS -->
            <div class="hero-art" aria-hidden="true">
                <div class="arch">
                    <span class="sparkle">✦</span>

                    <?php for ($shelf = 0; $shelf < 3; $shelf++): ?>
                        <div class="shelf">
                            <?php for ($book = 0; $book < 6; $book++): ?>
                                <span class="book-spine"></span>
                            <?php endfor; ?> 
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="art-caption">
                    ✦ Tri thức luôn rộng mở 
                </div> 
            </div> 
        </section>

        <!-- Danh sách sách nổi bật -->
         <section class="featured" aria-labelledby="featured-title">
            <!-- Tiêu đề danh sách -->
            <div class="featured-heading">
                <div>
                    <span class="eyebrow">ĐƯỢC CHỌN CHO BẠN</span>
                    <h2 id="featured-title">Sách nổi bật</h2>
                </div>

                <span class="featured-note">Tài liệu tham khảo chuyên ngành</span>
            </div>

            <?php if ($featureBooks === []): ?>
                <!-- Trạng thái danh sách rỗng -->
                <p class="books-empty">
                    Chưa có sách nổi bật. Bạn hãy quay lại sau nhé.
                </p>
            <?php else: ?>
                <!-- Trạng thái tìm kiếm trong danh sách mẫu -->
                <p id="search-summary" role="status" hidden></p>

                <!-- Thông báo khi không có sách phù hợp -->
                <p id="search-empty" class="books-empty" hidden>
                    Không tìm thấy danh sách phù hợp. Bạn hãy thử từ khóa khác.
                </p>

                <!-- Các thẻ sách từ dữ liệu mẫu -->
                <div class="books-grid">
                    <?php foreach ($featureBooks as $book): ?>
                        <article 
                            class="book-card"
                            data-search="<?= htmlspecialchars(
                                $book['title'] . ' ' . $book['author'] . ' ' . $book['category'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                            <!-- Bìa minh họa, không phải bìa xuất bản -->
                            <div class="book-cover-area" aria-hidden="true">
                                <div class="book-cover">
                                    <span class="cover-label">TỦ SÁCH VĂN HỌC</span>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $book['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?> 
                                    </strong>

                                    <span class="cover-decoration">❧</span>
                                </div>
                            </div>

                            <!-- Thông tin & Tình trạng sách -->
                            <div class="book-information">
                                <p class="book-category">
                                    <?= htmlspecialchars(
                                        $book['category'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?> 
                                </p>

                                <h3>
                                    <?= htmlspecialchars(
                                        $book['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h3>
                                
                                <p class="book-author">
                                    <?= htmlspecialchars(
                                        $book['author'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>

                                <?php if ($book['available'] > 0): ?>
                                    <span class="book-status">
                                        Còn
                                        <?= htmlspecialchars(
                                            (string) $book['available'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        bản
                                    </span>
                                <?php else: ?>
                                    <span class="book-status unavailable">
                                        Đang được mượn hết 
                                    </span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?> 
                </div>
            <?php endif; ?>
        </section>

        <!-- Thông báo trạng thái giao diện -->
        <p class="preview-note" id="preview-note" role="status">
            Giao diện đang xây dựng · Chưa kết nối dữ liệu thật.
        </p> 
    </main>

    <!-- Tương tác tìm kiếm mẫu -->
    <script src="/assets/js/home.js" defer></script> 
</body>
</html>



