<?php

declare(strict_types=1);

// Đọc dữ liệu sách từ cơ sở dữ liệu thật hoặc dữ liệu mẫu dự phòng
$rawId = $_GET['id'] ?? 'KV-ANT-0001';
$book = null;

if (is_string($rawId) && trim($rawId) !== '') {
    try {
        $bookService = new \App\Services\BookService();
        $found = $bookService->getBookDetail(trim($rawId));
        if ($found) {
            $prefix = strtoupper(substr($found->id, 0, 6));
            $category = match ($prefix) {
                'KV-ANT' => 'Nhân học / Dân tộc',
                'KV-ESS' => 'Tiểu luận / Nghiên cứu',
                'KV-POE' => 'Thơ ca / Văn vần',
                'KV-REC' => 'Tuyển tập / Tổng hợp',
                default => 'Văn học & Ngôn ngữ'
            };
            $major = match ($prefix) {
                'KV-ANT' => 'Nhân học',
                'KV-ESS' => 'Tiểu luận',
                'KV-POE' => 'Thơ ca',
                'KV-REC' => 'Tuyển tập',
                default => 'Khoa Văn học'
            };

            $shelf = null;
            $cell = null;
            if (!empty($found->copies)) {
                foreach ($found->copies as $copy) {
                    if (!empty($copy['ma_ke'])) {
                        $shelf = (int)$copy['ma_ke'];
                    }
                    if (!empty($copy['ma_o'])) {
                        $cell = (int)$copy['ma_o'];
                    }
                    if ($shelf !== null || $cell !== null) {
                        break;
                    }
                }
            }

            $book = [
                'id' => $found->id,
                'title' => $found->title,
                'author' => $found->authorNames !== '' ? $found->authorNames : 'Chưa rõ tác giả',
                'category' => $category,
                'major' => $major,
                'genre' => 'Sách chuyên khảo',
                'publishYear' => $found->publishYear,
                'language' => $found->language ?? 'Tiếng Việt',
                'publisher' => 'NXB Đại học Quốc gia',
                'available' => $found->availableQuantity,
                'total' => $found->totalQuantity,
                'shelf' => $shelf,
                'cell' => $cell,
                'location' => $found->location ?? null,
                'description' => $found->description ?: 'Tài liệu lưu trữ và phục vụ bạn đọc tại Thư viện Khoa Văn học.',
                'copies' => $found->copies,
                'cover_url' => $found->coverUrl ?? $found->anh_bia ?? '/assets/images/default-book-cover.jpg',
            ];
        }
    } catch (\Throwable $e) {
        $book = null;
    }
}

// Trạng thái không tìm thấy sách trong CSDL
if ($book === null) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Không tìm thấy sách · Thư viện Khoa Văn học</title>
        <link rel="stylesheet" href="/assets/css/base.css">
        <link rel="stylesheet" href="/assets/css/home.css">
        <link rel="stylesheet" href="/assets/css/70years.css">
    </head>
    <body>
        <main class="page" style="padding-top: 60px; text-align: center;">
            <h1 style="font-family: 'Literata', serif; font-size: 32px; color: #24445c;">Không tìm thấy tài liệu</h1>
            <p style="color: #64748b; margin: 16px 0 24px;">Tài liệu bạn yêu cầu không tồn tại hoặc đã được chuyển khỏi danh mục phục vụ.</p>
            <a href="/books" style="color: #1a5699; font-weight: 600; text-decoration: underline;">← Trở về tra cứu sách</a> 
        </main>
    </body>
    </html>
    <?php
    return;
}

// Các kệ và ô minh họa trong bản thử
$shelves = range(1, 6);
$cells = range(1, 4);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?> · Chi tiết tài liệu</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/books.css?v=<?= file_exists(__DIR__ . '/../../../public/assets/css/books.css') ? filemtime(__DIR__ . '/../../../public/assets/css/books.css') : time() ?>">
    <link rel="stylesheet" href="/assets/css/book-detail.css?v=<?= file_exists(__DIR__ . '/../../../public/assets/css/book-detail.css') ? filemtime(__DIR__ . '/../../../public/assets/css/book-detail.css') : time() ?>">
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
        <!-- Đường dẫn Breadcrumbs / Quay lại -->
        <nav class="detail-breadcrumb" aria-label="Đường dẫn điều hướng">
            <a class="detail-back-link" href="/books">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Trở về tra cứu sách
            </a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?></span>
        </nav>

        <!-- Khung chi tiết: Bìa sách & Thông tin -->
        <section class="detail-layout" aria-labelledby="book-title">
            <!-- Cột trái: Khung bìa sách học thuật cố định -->
            <div class="detail-cover-box">
                <img
                    src="<?= htmlspecialchars($book['cover_url'], ENT_QUOTES, 'UTF-8') ?>"
                    alt="Bìa sách <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <!-- Cột phải: Thông tin chi tiết -->
            <div class="detail-information">
                <!-- Dòng 1: Mã ký hiệu căn trái -->
                <div class="detail-header-row">
                    <span class="location-code">Ký hiệu: <?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <h1 class="detail-title" id="book-title">
                    <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>
                </h1>

                <p class="detail-author-line">
                    <span>Tác giả: <strong><?= htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                    <span class="meta-pipe" style="margin: 0 8px;">|</span>
                    <span class="book-status-classic <?= $book['available'] > 0 ? 'is-available' : 'is-unavailable' ?>">
                        [<?= $book['available'] > 0 ? 'Còn ' . (int)$book['available'] . '/' . (int)$book['total'] . ' bản sẵn sàng phục vụ' : 'Đang mượn hết' ?>]
                    </span>
                </p>

                <!-- Tóm tắt tài liệu -->
                <p class="detail-description">
                    <?= htmlspecialchars($book['description'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Bảng siêu dữ liệu unboxed với đường kẻ ngang mảnh -->
                <dl class="detail-metadata">
                    <div class="metadata-row">
                        <dt>Ký hiệu:</dt>
                        <dd><span class="meta-code"><?= htmlspecialchars($book['id'], ENT_QUOTES, 'UTF-8') ?></span></dd>
                    </div>
                    <div class="metadata-row">
                        <dt>Xuất bản:</dt>
                        <dd>
                            <?= htmlspecialchars($book['publisher'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if (!empty($book['publishYear'])): ?>
                                · Năm <?= htmlspecialchars((string)$book['publishYear'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div class="metadata-row">
                        <dt>Ngôn ngữ:</dt>
                        <dd><?= htmlspecialchars($book['language'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div class="metadata-row">
                        <dt>Vị trí:</dt>
                        <dd><?= !empty($book['location']) ? htmlspecialchars($book['location'], ENT_QUOTES, 'UTF-8') : '—' ?></dd>
                    </div>
                </dl>

                <!-- Nút thao tác mẫu -->
                <div class="detail-actions">
                    <button
                        class="detail-primary"
                        id="borrow-demo"
                        type="button"
                        <?= $book['available'] > 0 ? '' : 'disabled' ?>
                    >
                        Đăng ký mượn tài liệu
                    </button>

                    <button
                        class="detail-secondary"
                        id="favorite-demo"
                        type="button"
                        aria-pressed="false"
                        data-book-id="<?= htmlspecialchars((string)$book['id'], ENT_QUOTES, 'UTF-8') ?>"
                    >
                        Thêm vào yêu thích
                    </button>
                </div>

                <p class="preview-note" id="detail-feedback" role="status">
                    Bản thử giao diện · Chưa tạo phiếu mượn hoặc lưu yêu thích.
                </p>
            </div>
        </section>

        <!-- Sơ đồ vị trí kệ trong thư viện (Mở phẳng trên nền giấy kem) -->
        <section class="detail-location" aria-labelledby="location-title">
            <div class="location-header">
                <h2 id="location-title">Vị trí lưu trữ trong thư viện</h2>
                <?php if (!empty($book['location'])): ?>
                    <p>Kệ và ô được tô màu nổi bật là vị trí lưu giữ bản sách vật lý của tài liệu này.</p>
                <?php else: ?>
                    <p>Tài liệu này hiện chưa được xếp vào kệ hoặc ô cụ thể trong kho.</p>
                <?php endif; ?>
            </div>

            <!-- Dãy kệ -->
            <div class="location-block">
                <h3 class="location-subhead">Dãy Kệ sách</h3>
                <div class="shelf-map">
                    <?php foreach ($shelves as $shelf): ?>
                        <?php $isSelected = ($book['shelf'] !== null && $shelf === $book['shelf']); ?>
                        <div class="shelf-position <?= $isSelected ? 'is-selected' : '' ?>">
                            <span>Kệ <?= htmlspecialchars((string) $shelf, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($isSelected): ?>
                                <span class="selected-badge">Vị trí sách</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Ngăn / Ô -->
            <div class="location-block">
                <h3 class="location-subhead">Ngăn / Ô lưu trữ</h3>
                <div class="cell-map">
                    <?php foreach ($cells as $cell): ?>
                        <?php $isSelected = ($book['cell'] !== null && $cell === $book['cell']); ?>
                        <div class="cell-position <?= $isSelected ? 'is-selected' : '' ?>">
                            <span>Ô <?= htmlspecialchars((string) $cell, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($isSelected): ?>
                                <span class="selected-badge">Vị trí sách</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="preview-note">
                Sơ đồ bố cục phân bổ kho sách tầng 2 Thư viện Khoa Văn học.
            </p>
        </section>
    </main>

    <!-- Tương tác mẫu của trang chi tiết -->
    <script src="/assets/js/book-detail.js" defer></script>
</body>
</html>