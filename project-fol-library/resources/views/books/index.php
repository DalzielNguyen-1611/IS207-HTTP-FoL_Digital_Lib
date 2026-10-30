<?php

declare(strict_types=1);

// Đọc dữ liệu sách từ CSDL thực tế qua BookService
$bookService = new \App\Services\BookService();

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$keyword = isset($_GET['q']) ? trim((string)$_GET['q']) : (isset($_GET['keyword']) ? trim((string)$_GET['keyword']) : null);
$prefix = isset($_GET['prefix']) && trim((string)$_GET['prefix']) !== '' && strtoupper(trim((string)$_GET['prefix'])) !== 'ALL'
    ? strtoupper(trim((string)$_GET['prefix']))
    : null;

$bookData = $bookService->getPaginatedBooks(
    page: $page,
    perPage: $perPage,
    keyword: $keyword,
    prefix: $prefix
);

$books = [];
foreach ($bookData['items'] as $item) {
    $category = match (substr($item['id'], 0, 6)) {
        'KV-ANT' => 'Nhân học / Dân tộc',
        'KV-ESS' => 'Tiểu luận / Nghiên cứu',
        'KV-POE' => 'Thơ ca / Văn vần',
        'KV-REC' => 'Tuyển tập / Tổng hợp',
        default => 'Văn học & Ngôn ngữ'
    };
    $major = match (substr($item['id'], 0, 6)) {
        'KV-ANT' => 'Nhân học',
        'KV-ESS' => 'Tiểu luận',
        'KV-POE' => 'Thơ ca',
        'KV-REC' => 'Tuyển tập',
        default => 'Khoa Văn học'
    };

    $books[] = [
        'id' => $item['id'],
        'title' => $item['title'],
        'author' => $item['authorNames'] !== '' ? $item['authorNames'] : 'Chưa rõ tác giả',
        'category' => $category,
        'major' => $major,
        'genre' => 'Sách chuyên khảo',
        'available' => $item['availableQuantity'],
        'location' => $item['location'] ?? 'Kho lưu trữ Thư viện FoL',
    ];
}

$filterOptions = [
    'major' => ['Nhân học', 'Tiểu luận', 'Thơ ca', 'Tuyển tập'],
    'genre' => ['Sách chuyên khảo'],
    'author' => array_values(array_unique(array_filter(array_column($books, 'author')))),
];

$filterLabels = [
    'major' => 'Chuyên ngành',
    'genre' => 'Thể loại',
    'author' => 'Tác giả',
];

$totalBooks = $bookData['total'];
$totalPages = $bookData['totalPages'];
$currentPage = $bookData['page'];
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

            <!-- Menu thả xuống: Dịch vụ thư viện (Active) -->
            <div class="nav-dropdown is-active">
                <button type="button" class="nav-dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                    <span>Dịch vụ</span>
                    <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="nav-dropdown-menu" role="menu">
                    <a href="/books" class="nav-dropdown-item active" role="menuitem">
                        <span class="nav-dropdown-icon">📚</span>
                        <div class="nav-dropdown-text">
                            <span class="nav-dropdown-title">Tra cứu sách</span>
                            <span class="nav-dropdown-desc">Kho 1.200+ giáo trình, chuyên khảo & luận văn</span>
                        </div>
                    </a>
                    <a href="/reading-room" class="nav-dropdown-item" role="menuitem">
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
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;margin-left:auto;">
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
            Hiển thị <?= count($books) ?> trong tổng số <strong><?= number_format($totalBooks) ?></strong> đầu sách từ CSDL Thư viện FoL (Trang <?= $currentPage ?> / <?= $totalPages ?>)
        </p>

        <!-- Trạng thái tìm kiếm không có kết quả -->
         <p class="books-empty" id="catalog-empty" <?= count($books) > 0 ? 'hidden' : '' ?>>
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

        <!-- Phân trang danh mục sách -->
        <?php if ($totalPages > 1): ?>
            <div style="display:flex;justify-content:center;align-items:center;gap:8px;margin:28px 0;flex-wrap:wrap;">
                <?php if ($currentPage > 1): ?>
                    <a href="/books?page=<?= $currentPage - 1 ?><?= $keyword ? '&q=' . urlencode($keyword) : '' ?><?= $prefix ? '&prefix=' . urlencode($prefix) : '' ?>" class="catalog-reset" style="text-decoration:none;display:inline-flex;align-items:center;">
                        ← Trang trước
                    </a>
                <?php endif; ?>

                <span style="font-size:13px;color:#475569;margin:0 10px;">
                    Trang <strong><?= $currentPage ?></strong> / <?= $totalPages ?>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="/books?page=<?= $currentPage + 1 ?><?= $keyword ? '&q=' . urlencode($keyword) : '' ?><?= $prefix ? '&prefix=' . urlencode($prefix) : '' ?>" class="catalog-reset" style="text-decoration:none;display:inline-flex;align-items:center;">
                        Trang sau →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Thông báo đồng bộ dữ liệu thời gian thực -->
        <p class="preview-note">
            Dữ liệu đồng bộ trực tiếp từ CSDL Thư viện Khoa Văn học (USSH · VNU-HANOI). 
        </p> 
    </main>

    <!-- Tương tác tìm kiếm trên trang tra cứu -->
     <script src="/assets/js/books.js" defer></script>
</body>
</html>
