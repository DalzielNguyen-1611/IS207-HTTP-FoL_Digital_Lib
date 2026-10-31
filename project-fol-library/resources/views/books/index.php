<?php

declare(strict_types=1);

// Nếu View được nạp trực tiếp mà chưa qua BookController (ví dụ trong kịch bản test tích hợp),
// tự động kích hoạt BookController để đảm bảo đầy đủ dữ liệu
if (!isset($books)) {
    $bookController = new \App\Controllers\BookController();
    $bookController->index();
    return;
}

// Hàm hỗ trợ sinh URL phân trang lưu giữ toàn bộ từ khóa, bộ lọc và sắp xếp
$buildPageUrl = function(int $targetPage) use ($filters, $perPage): string {
    $params = array_filter([
        'page' => $targetPage,
        'limit' => $perPage !== 10 ? $perPage : null,
        'q' => $filters['q'] ?? null,
        'major' => $filters['major'] ?? null,
        'author' => $filters['author'] ?? null,
        'year' => $filters['year'] ?? null,
        'status' => $filters['status'] ?? null,
        'sort' => ($filters['sort'] ?? 'id_asc') !== 'id_asc' ? $filters['sort'] : null,
        'prefix' => $filters['prefix'] ?? null,
    ], fn($v) => $v !== null && $v !== '');
    return '/books?' . http_build_query($params);
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tra cứu sách · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/books.css?v=<?= file_exists(__DIR__ . '/../../../public/assets/css/books.css') ? filemtime(__DIR__ . '/../../../public/assets/css/books.css') : time() ?>">
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

    <main class="page">
        <!-- Tiêu đề trang tra cứu -->
        <section class="catalog-intro">
            <h1>Tra cứu tài liệu</h1>
        </section>

        <!-- Thanh tìm kiếm tài liệu -->
        <form class="search-box" id="catalog-search" method="GET" action="/books">
            <input
                type="search"
                name="q"
                value="<?= htmlspecialchars($filters['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                aria-label="Tìm tên sách, tác giả hoặc chuyên ngành"
                placeholder="Tìm tên sách, tác giả, chuyên ngành..."
            >
            <?php if (!empty($filters['major'])): ?>
                <input type="hidden" name="major" value="<?= htmlspecialchars($filters['major'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <?php if (!empty($filters['author'])): ?>
                <input type="hidden" name="author" value="<?= htmlspecialchars($filters['author'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <?php if (!empty($filters['year'])): ?>
                <input type="hidden" name="year" value="<?= htmlspecialchars((string)$filters['year'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <?php if (!empty($filters['status'])): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($filters['status'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <?php if (!empty($filters['sort']) && $filters['sort'] !== 'id_asc'): ?>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($filters['sort'], ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
            <button type="submit">Tìm kiếm</button>
        </form>

        <!-- Bố cục 2 phần tỉ lệ 19 : 81 từ phần bộ lọc & danh sách kết quả trở xuống -->
        <div class="catalog-layout">
            <!-- Cột trái: Bộ lọc phía Server (19%) -->
            <aside class="catalog-sidebar" aria-label="Bộ lọc tìm kiếm">
                <form id="catalog-filters-form" method="GET" action="/books">
                    <?php if (!empty($filters['q'])): ?>
                        <input type="hidden" name="q" value="<?= htmlspecialchars($filters['q'], ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>

                    <div class="filter-panel">
                        <div class="filter-panel-header">
                            <span class="filter-panel-title">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                Bộ lọc
                            </span>
                        </div>

                        <!-- Lọc theo Chuyên ngành -->
                        <div class="filter-group">
                            <label class="filter-label" for="filter-major">Chuyên ngành</label>
                            <select id="filter-major" name="major" data-filter="major" class="filter-select">
                                <option value="">Tất cả chuyên ngành</option>
                                <?php foreach ($filterOptions['majors'] ?? [] as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['major'] ?? '') === $opt ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lọc theo Tác giả -->
                        <div class="filter-group">
                            <label class="filter-label" for="filter-author">Tác giả</label>
                            <select id="filter-author" name="author" data-filter="author" class="filter-select">
                                <option value="">Tất cả tác giả</option>
                                <?php foreach ($filterOptions['authors'] ?? [] as $opt): ?>
                                    <option value="<?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>" <?= ($filters['author'] ?? '') === $opt ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($opt, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lọc theo Thời gian xuất bản -->
                        <div class="filter-group">
                            <label class="filter-label" for="filter-year">Thời gian xuất bản</label>
                            <select id="filter-year" name="year" data-filter="year" class="filter-select">
                                <option value="">Tất cả các năm</option>
                                <?php foreach ($filterOptions['years'] ?? [] as $opt): ?>
                                    <option value="<?= htmlspecialchars((string)$opt, ENT_QUOTES, 'UTF-8') ?>" <?= (string)($filters['year'] ?? '') === (string)$opt ? 'selected' : '' ?>>
                                        Năm <?= htmlspecialchars((string)$opt, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Lọc theo Tình trạng sách -->
                        <div class="filter-group">
                            <label class="filter-label" for="filter-status">Tình trạng sách</label>
                            <select id="filter-status" name="status" data-filter="status" class="filter-select">
                                <option value="">Tất cả tình trạng</option>
                                <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Còn sách có thể mượn</option>
                            </select>
                        </div>

                        <!-- Sắp xếp kết quả -->
                        <div class="filter-group">
                            <label class="filter-label" for="filter-sort">Sắp xếp</label>
                            <select id="filter-sort" name="sort" data-filter="sort" class="filter-select">
                                <option value="id_asc" <?= ($filters['sort'] ?? '') === 'id_asc' ? 'selected' : '' ?>>Mã sách tăng dần (Mặc định)</option>
                                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Năm xuất bản: Mới nhất</option>
                                <option value="oldest" <?= ($filters['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Năm xuất bản: Cũ nhất</option>
                                <option value="title_asc" <?= ($filters['sort'] ?? '') === 'title_asc' ? 'selected' : '' ?>>Tên sách: A - Z</option>
                                <option value="title_desc" <?= ($filters['sort'] ?? '') === 'title_desc' ? 'selected' : '' ?>>Tên sách: Z - A</option>
                            </select>
                        </div>

                        <!-- Nút làm mới bộ lọc -->
                        <a class="catalog-reset" id="catalog-reset" href="/books" style="display:inline-flex;align-items:center;text-decoration:none;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            Làm mới bộ lọc
                        </a>
                    </div>
                </form>
            </aside>

            <!-- Cột phải: Danh sách kết quả tìm kiếm (81%) -->
            <section class="catalog-content" id="catalog-content" aria-label="Kết quả tìm kiếm sách">
                <!-- Thông báo Lỗi truy vấn nếu xảy ra sự cố cơ sở dữ liệu -->
                <?php if (!empty($errorMessage)): ?>
                    <div class="catalog-error-state" role="alert" style="padding: 24px; border: 1px solid #fecaca; background: #fff5f5; border-radius: 8px; text-align: center; color: #b91c1c; margin-bottom: 24px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 10px; display: block; color: #dc2626;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <h3 style="margin: 0 0 6px; font-size: 16px; font-weight: 600;">Lỗi kết nối cơ sở dữ liệu</h3>
                        <p style="margin: 0 0 14px; font-size: 13.5px; color: #7f1d1d;"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                        <a href="/books" class="pagination-btn" style="display: inline-flex;">Tải lại trang</a>
                    </div>
                <?php endif; ?>

                <!-- Số lượng kết quả -->
                <p class="catalog-count" id="catalog-count" role="status">
                    <?php if ($totalBooks > 0): ?>
                        Hiển thị <?= count($books) ?> trong tổng số <strong><?= number_format($totalBooks) ?></strong> đầu sách (Trang <?= $currentPage ?> / <?= $totalPages ?>)
                    <?php else: ?>
                        Không tìm thấy tài liệu phù hợp
                    <?php endif; ?>
                </p>

                <!-- Trạng thái không có kết quả -->
                <?php if (empty($errorMessage) && count($books) === 0): ?>
                    <div class="books-empty" id="catalog-empty" role="status" style="padding: 44px 24px; border: 1px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #64748b; background: rgba(255,255,255,0.4); margin: 16px 0;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="margin: 0 auto 12px; display: block; color: #94a3b8;">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <p style="font-size: 15px; font-weight: 600; color: #334155; margin: 0 0 6px;">Không tìm thấy tài liệu phù hợp</p>
                        <p style="font-size: 13.5px; color: #64748b; margin: 0 0 16px;">Không có tài liệu nào khớp với từ khóa và các tiêu chí lọc bạn đã chọn.</p>
                        <a href="/books" class="pagination-btn" style="display: inline-flex;">Xóa bộ lọc để xem tất cả</a>
                    </div>
                <?php endif; ?>

                <!-- Danh sách tài liệu (Chuẩn 10 cuốn mỗi trang theo Server-Side LIMIT) -->
                <div class="catalog-list" id="catalog-list" aria-label="Danh sách sách">
                    <?php foreach ($books as $book): ?>
                        <?php
                            $bookAuthor = $book['author'] ?? $book['authorNames'] ?? 'Chưa rõ tác giả';
                            $bookMajor = $book['major'] ?? 'Khoa Văn học';
                            $bookCategory = $book['category'] ?? 'Văn học & Ngôn ngữ';
                            $bookCover = $book['cover_url'] ?? $book['anh_bia'] ?? '/assets/images/default-book-cover.jpg';
                        ?>
                        <article 
                            class="catalog-item"
                            data-search="<?= htmlspecialchars(
                                $book['title'] . ' ' . $bookAuthor . ' ' . $bookCategory . ' ' . $bookMajor . ' ' . ($book['publishYear'] ?? '') . ' ' . $book['id'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            data-major="<?= htmlspecialchars($bookMajor, ENT_QUOTES, 'UTF-8') ?>"
                            data-author="<?= htmlspecialchars($bookAuthor, ENT_QUOTES, 'UTF-8') ?>"
                            data-year="<?= htmlspecialchars((string)($book['publishYear'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <!-- Khung ảnh sách cố định: hình ảnh co giãn tự động theo khung kèm lazy loading -->
                            <div class="catalog-cover-box">
                                <img
                                    src="<?= htmlspecialchars($bookCover, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Bìa sách <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>"
                                    loading="lazy"
                                >
                            </div>

                            <!-- Bố cục 3 dòng siêu gọn (chuẩn danh mục sách in) -->
                            <div class="catalog-main-info">
                                <!-- Dòng 1 (Ngang mép trên ảnh bìa): Tiêu đề sách -->
                                <h2 class="book-title">
                                    <a href="/book-detail?id=<?= htmlspecialchars((string)$book['id'], ENT_QUOTES, 'UTF-8') ?>" class="catalog-title-link">
                                        <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </h2>

                                <!-- Dòng 2 (Ở giữa): Tác giả / Đơn vị biên soạn -->
                                <p class="book-author">
                                    <?= htmlspecialchars($bookAuthor, ENT_QUOTES, 'UTF-8') ?>
                                </p>

                                <!-- Dòng 3 (Dưới cùng – Gom toàn bộ thông tin tra cứu): Mã định danh · NXB · Năm · Ngôn ngữ · Số bản -->
                                <div class="catalog-card-footer">
                                    <div class="book-meta">
                                        <span><?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="meta-dot">·</span>
                                        <span><?= htmlspecialchars($book['publisher'] ?? 'NXB Đại học Quốc gia', ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (!empty($book['publishYear'])): ?>
                                            <span class="meta-dot">·</span>
                                            <span><?= htmlspecialchars((string)$book['publishYear'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <span class="meta-dot">·</span>
                                        <span><?= htmlspecialchars($book['language'] ?? 'Tiếng Việt', ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (!empty($book['location'])): ?>
                                            <span class="meta-dot">·</span>
                                            <span><?= htmlspecialchars($book['location'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <span class="meta-dot">·</span>
                                        <span><?= ($book['availableQuantity'] ?? $book['available'] ?? 0) > 0 ? 'Còn ' . (int)($book['availableQuantity'] ?? $book['available']) . '/' . (int)($book['totalQuantity'] ?? $book['total']) . ' bản' : 'Đang mượn hết' ?></span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang danh mục sách lưu giữ đầy đủ tham số lọc -->
                <?php if ($totalPages > 1): ?>
                    <div class="catalog-pagination" id="catalog-pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentPage - 1), ENT_QUOTES, 'UTF-8') ?>" class="pagination-btn pagination-prev">
                                ← Trang trước
                            </a>
                        <?php endif; ?>

                        <span class="pagination-info" id="pagination-info">
                            Trang <strong><?= $currentPage ?></strong> / <span id="pagination-total-pages"><?= $totalPages ?></span>
                        </span>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= htmlspecialchars($buildPageUrl($currentPage + 1), ENT_QUOTES, 'UTF-8') ?>" class="pagination-btn pagination-next">
                                Trang sau →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Thông báo đồng bộ dữ liệu thời gian thực -->
        <p class="preview-note">
            Dữ liệu đồng bộ trực tiếp từ CSDL Thư viện Khoa Văn học (USSH · VNU-HANOI) qua cơ chế phân trang phía máy chủ (Server-Side Pagination). 
        </p> 
    </main>

    <!-- Tương tác tìm kiếm và chuyển trang phía máy chủ -->
    <script src="/assets/js/books.js" defer></script>
</body>
</html>
