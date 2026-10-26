<?php

declare(strict_types=1);

//Đọc dữ liệu mẫu dùng chung, không kết nối CSDL
$books = require dirname(__DIR__, 2) . '/mock/books.php';

//Danh sách lựa chọn bộ lọc lấy từ dữ liệu mẫu
$filterOptions = [
    'major' => array_values(array_unique(array_column($books, 'major'))),
    'genre' => array_values(array_unique(array_column($books, 'genre'))),
    'author' => array_values(array_unique(array_column($books, 'author'))),
];

$filterLabels = [
    'major' => 'Chuyên ngành',
    'genre' => 'Thể loại',
    'author' => 'Tác giả',
];

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Matadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tra cứu sách · Thư viên Khoa văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/books.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
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
            <a href="/books" class="active" aria-current="page">
                Tra cứu sách 
            </a>
        </nav>

        <!-- Liên kết tới trang đăng nhập -->
        <a class="login-button" href="/login">
            Đăng nhập 
        </a> 
    </header>

    <main class="page">
        <!-- Tiêu đề trang tra cứu -->
        <section class="catalog-intro">
            <span class="eyebrow">KHÔNG GIAN TRI THỨC CỦA BẠN</span>
            <h1>Tra cứu tài liệu</h1>
            <p>Khám phá sách và tài liệu tham khảo chuyên ngành.</p>
        </section>

        <!-- Thanh tìm kiếm tài liệu mẫu -->
        <form class="search-box" id="catalog-search">
            <input
                type="search"
                aria-label="Tìm tên sách, tác giả hoặc chuyên ngành"
                placeholder="Tìm tên sách, tác giả, chuyên ngành..."
            >
            <button type="submit">Tìm kiếm</button>
        </form>

        <!-- Bộ lọc tài liệu mẫu -->
        <div class="catalog-filters">
            <?php foreach ($filterLabels as $field => $label): ?>
                <div class="catalog-filter">
                    <label for="filter-<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </label>

                    <select
                        id="filter-<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"
                        data-filter="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <option value="">Tất cả</option>

                        <?php foreach ($filterOptions[$field] as $option): ?>
                            <option value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?> 
                    </select> 
                </div>
            <?php endforeach; ?>

            <!-- Xóa từ khóa và các lựa chọn lọc -->
            <button class="catalog-reset" id="catalog-reset" type="button">
                Xóa bộ lọc
            </button> 
        </div>
        
        <!-- Số lượng kết quả -->
        <p class="catalog-count" id="catalog-count" role="status">
            <?= htmlspecialchars(
                (string) count($books),
                ENT_QUOTES,
                'UTF-8'
            ) ?> tài liệu mẫu 
        </p>

        <!-- Trạng thái tìm kiếm không có kết quả -->
         <p class="books-empty" id="catalog-empty" hidden>
            Không tìm thấy tài liệu phù hợp. Bạn hãy thử từ khóa khác. 
        </p>

        <!-- Danh sách tài liệu dạng hàng ngang -->
        <section class="catalog-list" aria-label="Danh sách sách">
            <?php if ($books === []): ?>
                <!-- Trạng thái danh sách rỗng -->
                <p class="books-empty">Chưa có tài liệu để hiển thị.</p>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <article 
                        class="catalog-item"
                        data-search="<?= htmlspecialchars(
                            $book['title'] . ' ' . $book['author'] . ' ' . $book['category'] . ' ' . $book['major'] . ' ' . $book['genre'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        data-major="<?= htmlspecialchars($book['major'], ENT_QUOTES, 'UTF-8') ?>"
                        data-genre="<?= htmlspecialchars($book['genre'], ENT_QUOTES, 'UTF-8') ?>"
                        data-author="<?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                        <!-- Bìa sách minh họa -->
                        <div class="catalog-cover" aria-hidden="true">
                            <span>TỦ SÁCH VĂN HỌC</span>
                            <strong>
                                <?= htmlspecialchars(
                                    $book['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> 
                            </strong>
                            <span class="catalog-decoration">❧</span>
                        </div>

                        <!-- Thông tin tài liệu -->
                         <div class="catalog-information">
                            <p class="book-category">
                                <?= htmlspecialchars(
                                    $book['category'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> 
                            </p>

                            <!-- Liên kết tới chi tiết tài liệu -->
                            <h2>
                                <a href="/book-detail?id=<?= htmlspecialchars(
                                    (string) $book['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>">
                                    <?= htmlspecialchars(
                                        $book['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?> 
                                </a> 
                            </h2>

                            <p class="book-author">
                                <?= htmlspecialchars(
                                    $book['author'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> 
                            </p>

                            <p class="catalog-location">
                                <?= htmlspecialchars(
                                    $book['location'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?> 
                            </p>

                            <!-- Tình trạng sách -->
                            <?php if ($book['available'] > 0): ?>
                                <span class="book-status">
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
                        </div> 
                    </article>
                <?php endforeach; ?>
            <?php endif; ?> 
        </section>

        <!-- Thông báo phạm vi bản thử -->
        <p class="preview-note">
            Dữ liệu mẫu · Bộ lọc và trang chi tiết sẽ được bổ sung tiếp. 
        </p> 
    </main>

    <!-- Tương tác tìm kiếm trên trang tra cứu -->
     <script src="/assets/js/books.js" defer></script>
</body>
</html>
