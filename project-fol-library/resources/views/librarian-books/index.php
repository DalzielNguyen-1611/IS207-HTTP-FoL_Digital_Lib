<?php

declare(strict_types=1);

// Dữ liệu sách thời gian thực từ CSDL
// Tự động khởi tạo qua BookService nếu được require độc lập
if (!isset($books) || !isset($pagination)) {
    $bookService = new \App\Services\BookService();
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? max(5, min(100, (int)$_GET['per_page'])) : 15;
    $keyword = isset($_GET['keyword']) && trim((string)$_GET['keyword']) !== '' ? trim((string)$_GET['keyword']) : null;
    $prefix = isset($_GET['prefix']) && trim((string)$_GET['prefix']) !== '' ? strtoupper(trim((string)$_GET['prefix'])) : 'ALL';

    $bookData = $bookService->getPaginatedBooks(
        page: $page,
        perPage: $perPage,
        keyword: $keyword,
        prefix: $prefix === 'ALL' ? null : $prefix
    );
    $books = $bookData['items'];
    $pagination = [
        'total' => $bookData['total'],
        'page' => $bookData['page'],
        'perPage' => $bookData['perPage'],
        'totalPages' => $bookData['totalPages'],
        'keyword' => $keyword ?? '',
        'prefix' => $prefix,
    ];
}

$currentPrefix = $pagination['prefix'] ?? 'ALL';
$currentKeyword = $pagination['keyword'] ?? '';
$currentPage = $pagination['page'] ?? 1;
$totalPages = $pagination['totalPages'] ?? 1;
$totalBooks = $pagination['total'] ?? 0;
$perPage = $pagination['perPage'] ?? 15;

$startItem = $totalBooks > 0 ? ($currentPage - 1) * $perPage + 1 : 0;
$endItem = min($totalBooks, $currentPage * $perPage);
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
        <a class="staff-brand" href="/" title="Về trang chủ Thư viện">
            <img
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Cán bộ thư viện</small>
            </span>
        </a>

        <div class="staff-header-actions">
            <nav class="staff-nav" aria-label="Điều hướng">
                <a href="/">Trang chủ</a>
                <?php $currentRole = \App\Core\Session::getRoleCode(); ?>
                <?php if ($currentRole === 'ADMIN'): ?>
                    <a href="/profile">Cá nhân</a>
                    <a href="/librarian" class="active">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($currentRole === 'LIBRARIAN'): ?>
                    <a href="/librarian" class="active">Cán bộ</a>
                <?php endif; ?>
            </nav>

            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
                <?php
                    $currentUser = \App\Core\Session::getAuthUser();
                    $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Cán bộ', ENT_QUOTES, 'UTF-8');
                    $dashUrl = ($currentRole === 'ADMIN') ? '/admin/settings' : '/librarian';
                ?>
                <a href="<?= $dashUrl ?>" class="staff-btn staff-btn-user" title="Tài khoản của bạn">
                    <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                    <span><?= $userName ?></span>
                </a>
                <a href="/logout" class="staff-btn staff-btn-logout" title="Đăng xuất khỏi hệ thống">
                    Đăng xuất
                </a>
            </div>
        </div>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng nghiệp vụ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>

            <a href="/librarian">Tổng quan</a>
            <a href="/librarian/books" aria-current="page">Quản lý sách</a>
            <a href="/librarian/counter">Mượn / trả</a>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings">Về trang quản trị</a>
                <a href="/profile">Về trang cá nhân (SV)</a>
            <?php endif; ?>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề trang quản lý sách -->
            <section class="staff-intro">
                <span class="staff-eyebrow">QUẢN LÝ TÀI LIỆU</span>
                <h1>Quản lý sách</h1>
                <p>Tra cứu thông tin, quản lý danh mục và số lượng bản sách thời gian thực từ CSDL Thư viện FoL.</p>
            </section>

            <!-- Danh sách và tìm kiếm tài liệu -->
            <section class="staff-panel" aria-labelledby="book-list-title">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                    <h2 id="book-list-title" style="margin:0;">Danh mục đầu sách</h2>

                    <!-- Thêm tài liệu vào CSDL -->
                    <button class="staff-action-button" id="add-book-button" type="button" style="margin:0;font-weight:600;">
                        + Thêm đầu sách mới
                    </button>
                </div>

                <!-- Bộ lọc theo tiền tố chuyên ngành (Prefix Chips) -->
                <div class="filter-chips" role="group" aria-label="Lọc theo chuyên mục">
                    <span class="filter-chips-label">Chuyên mục:</span>
                    <a href="/librarian/books?prefix=ALL<?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" 
                       class="filter-chip <?= $currentPrefix === 'ALL' ? 'active' : '' ?>" data-prefix="ALL">
                        Tất cả
                    </a>
                    <a href="/librarian/books?prefix=KV-ANT<?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" 
                       class="filter-chip <?= $currentPrefix === 'KV-ANT' ? 'active' : '' ?>" data-prefix="KV-ANT">
                        ANT · Nhân học
                    </a>
                    <a href="/librarian/books?prefix=KV-ESS<?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" 
                       class="filter-chip <?= $currentPrefix === 'KV-ESS' ? 'active' : '' ?>" data-prefix="KV-ESS">
                        ESS · Tiểu luận
                    </a>
                    <a href="/librarian/books?prefix=KV-POE<?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" 
                       class="filter-chip <?= $currentPrefix === 'KV-POE' ? 'active' : '' ?>" data-prefix="KV-POE">
                        POE · Thơ ca
                    </a>
                    <a href="/librarian/books?prefix=KV-REC<?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" 
                       class="filter-chip <?= $currentPrefix === 'KV-REC' ? 'active' : '' ?>" data-prefix="KV-REC">
                        REC · Tuyển tập
                    </a>
                </div>

                <!-- Biểu mẫu tìm kiếm -->
                <form class="staff-search" id="staff-book-search" method="GET" action="/librarian/books">
                    <input type="hidden" name="prefix" id="search-prefix-input" value="<?= htmlspecialchars($currentPrefix, ENT_QUOTES, 'UTF-8') ?>">
                    <input
                        id="staff-book-keyword"
                        name="keyword"
                        type="search"
                        value="<?= htmlspecialchars($currentKeyword, ENT_QUOTES, 'UTF-8') ?>"
                        aria-label="Tìm mã sách, tên sách hoặc tác giả"
                        placeholder="Tìm theo mã sách (VD: KV-ANT-0001), tên sách hoặc tác giả…"
                    >
                    <button type="submit">Tìm kiếm</button>
                    <?php if ($currentKeyword !== '' || $currentPrefix !== 'ALL'): ?>
                        <a href="/librarian/books" class="staff-action-button" style="text-decoration:none;">Xóa bộ lọc</a>
                    <?php endif; ?>
                </form>

                <!-- Số lượng kết quả -->
                <p class="staff-note" id="staff-book-count" role="status" style="margin:12px 0;">
                    <?php if ($totalBooks > 0): ?>
                        Hiển thị <strong><?= $startItem ?></strong> – <strong><?= $endItem ?></strong> trong tổng số <strong><?= number_format($totalBooks) ?></strong> đầu sách
                    <?php else: ?>
                        Không tìm thấy đầu sách nào phù hợp.
                    <?php endif; ?>
                </p>

                <!-- Trạng thái kết quả rỗng -->
                <p class="staff-note" id="staff-book-empty" <?= $totalBooks > 0 ? 'hidden' : '' ?> style="padding:16px;background:#f8fafc;border-radius:8px;text-align:center;">
                    Không tìm thấy cuốn sách nào khớp với từ khóa hoặc bộ lọc đã chọn.
                </p>

                <!-- Bảng sách cuộn ngang trên màn hình nhỏ -->
                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách thông tin sách"
                >
                    <table class="staff-table" id="staff-book-table">
                        <thead>
                            <tr>
                                <th scope="col" style="width:140px;">Mã sách</th>
                                <th scope="col">Tên sách</th>
                                <th scope="col" style="width:200px;">Tác giả</th>
                                <th scope="col" style="width:90px;text-align:center;">Năm XB</th>
                                <th scope="col" style="width:120px;text-align:center;">Khả dụng</th>
                                <th scope="col" style="width:130px;">Vị trí</th>
                                <th scope="col" style="width:200px;text-align:center;">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody id="staff-book-tbody">
                            <?php foreach ($books as $book): ?>
                                <?php
                                    $bookId = (string)($book['id'] ?? $book['id_dau_sach'] ?? '');
                                    $bookTitle = (string)($book['title'] ?? $book['tieu_de'] ?? '');
                                    $bookAuthor = (string)($book['authorNames'] ?? $book['tac_gia'] ?? 'Chưa rõ');
                                    $publishYear = $book['publishYear'] ?? $book['nam_xuat_ban'] ?? '—';
                                    $totalQty = (int)($book['totalQuantity'] ?? $book['tong_cuon'] ?? $book['so_luong'] ?? 0);
                                    $availQty = (int)($book['availableQuantity'] ?? $book['co_the_muon'] ?? $totalQty);
                                    $location = (string)($book['location'] ?? $book['vi_tri'] ?? 'Kho lưu trữ');
                                ?>
                                <tr
                                    data-book-row
                                    data-id="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= htmlspecialchars(
                                        $bookId . ' ' . $bookTitle . ' ' . $bookAuthor,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <th scope="row">
                                        <code style="font-size:12px;color:#1e3a8a;font-weight:600;"><?= htmlspecialchars(
                                            $bookId,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?></code>
                                    </th>

                                    <td data-field="title" style="font-weight:500;">
                                        <?= htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-field="author" style="color:#475569;">
                                        <?= htmlspecialchars($bookAuthor, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-field="year" style="text-align:center;color:#64748b;">
                                        <?= htmlspecialchars((string)$publishYear, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-field="available" style="text-align:center;">
                                        <span class="badge-count <?= $availQty > 0 ? 'badge-available' : 'badge-empty' ?>">
                                            <?= $availQty ?> / <?= $totalQty ?> bản
                                        </span>
                                    </td>

                                    <td data-field="location" style="color:#64748b;font-size:11px;">
                                        <?= htmlspecialchars($location, ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <!-- Thao tác: Xem chi tiết bản sách, sửa và xóa -->
                                    <td>
                                        <div class="staff-book-actions" style="justify-content:center;">
                                            <button
                                                class="staff-action-button"
                                                type="button"
                                                data-view-copies="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Xem danh sách mã cuốn sách vật lý"
                                            >
                                                Bản sách
                                            </button>

                                            <button
                                                class="staff-action-button"
                                                type="button"
                                                data-edit-book="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Chỉnh sửa thông tin đầu sách"
                                            >
                                                Sửa
                                            </button>

                                            <button
                                                class="staff-action-button danger"
                                                type="button"
                                                data-delete-book="<?= htmlspecialchars($bookId, ENT_QUOTES, 'UTF-8') ?>"
                                                data-title="<?= htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                title="Xóa đầu sách"
                                            >
                                                Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thanh phân trang (Pagination) -->
                <?php if ($totalPages > 1): ?>
                    <nav class="staff-pagination" aria-label="Phân trang sách">
                        <div>
                            Trang <strong><?= $currentPage ?></strong> / <strong><?= $totalPages ?></strong>
                        </div>

                        <div class="staff-pagination-pages">
                            <!-- Nút trang trước -->
                            <?php
                                $prevPage = max(1, $currentPage - 1);
                                $prevUrl = "/librarian/books?page={$prevPage}&prefix=" . urlencode($currentPrefix) . ($currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '');
                            ?>
                            <a href="<?= $prevUrl ?>" 
                               class="staff-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>" 
                               <?= $currentPage <= 1 ? 'aria-disabled="true" style="pointer-events:none;opacity:0.4;"' : '' ?>>
                                ← Trước
                            </a>

                            <!-- Các số trang tiêu biểu -->
                            <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);

                                if ($startPage > 1): ?>
                                    <a href="/librarian/books?page=1&prefix=<?= urlencode($currentPrefix) ?><?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" class="staff-page-btn">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span style="padding:0 4px;color:#94a3b8;">…</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                                <?php
                                    $pageUrl = "/librarian/books?page={$p}&prefix=" . urlencode($currentPrefix) . ($currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '');
                                ?>
                                <a href="<?= $pageUrl ?>" class="staff-page-btn <?= $p === $currentPage ? 'active' : '' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span style="padding:0 4px;color:#94a3b8;">…</span>
                                <?php endif; ?>
                                <a href="/librarian/books?page=<?= $totalPages ?>&prefix=<?= urlencode($currentPrefix) ?><?= $currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '' ?>" class="staff-page-btn"><?= $totalPages ?></a>
                            <?php endif; ?>

                            <!-- Nút trang sau -->
                            <?php
                                $nextPage = min($totalPages, $currentPage + 1);
                                $nextUrl = "/librarian/books?page={$nextPage}&prefix=" . urlencode($currentPrefix) . ($currentKeyword !== '' ? '&keyword=' . urlencode($currentKeyword) : '');
                            ?>
                            <a href="<?= $nextUrl ?>" 
                               class="staff-page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>" 
                               <?= $currentPage >= $totalPages ? 'aria-disabled="true" style="pointer-events:none;opacity:0.4;"' : '' ?>>
                                Sau →
                            </a>
                        </div>
                    </nav>
                <?php endif; ?>
            </section>

            <!-- Ghi chú quản lý dữ liệu thật -->
            <p class="staff-note">
                Hệ thống đồng bộ dữ liệu thời gian thực giữa danh mục đầu sách, số bản sách vật lý và vị trí lưu trữ tại Thư viện FoL.
            </p>

            <!-- Thông báo phản hồi kết quả thao tác -->
            <div id="staff-edit-feedback" class="staff-note" role="status" style="margin-top:12px;font-weight:500;"></div>
        </div>
    </main>

    <!-- Hộp thoại thêm / chỉnh sửa sách -->
    <dialog
        class="staff-book-dialog"
        id="edit-book-dialog"
        aria-labelledby="edit-book-title"
    >
        <form id="edit-book-form">
            <h2 id="edit-book-title">Chỉnh sửa sách</h2>

            <!-- Thông báo lỗi nội bộ form -->
            <div id="dialog-form-error" style="display:none;padding:10px 14px;background:#fee2e2;color:#b91c1c;border-radius:8px;font-size:12px;margin-bottom:12px;"></div>

            <!-- Mã đầu sách -->
            <div class="staff-edit-field" id="field-book-id-group">
                <label for="edit-id">Mã đầu sách <span style="color:#dc2626;">*</span></label>
                <div style="display:flex;gap:8px;">
                    <select id="edit-prefix-select" style="padding:12px;border:1px solid #d4e2ec;border-radius:8px;background:white;font-weight:600;">
                        <option value="KV-ANT">KV-ANT</option>
                        <option value="KV-ESS">KV-ESS</option>
                        <option value="KV-POE">KV-POE</option>
                        <option value="KV-REC">KV-REC</option>
                        <option value="KV-GEN">KV-GEN</option>
                    </select>
                    <input id="edit-id" type="text" maxlength="50" required style="flex:1;">
                    <button type="button" id="btn-generate-id" class="staff-action-button" title="Sinh mã tự động">Tự sinh</button>
                </div>
                <small style="color:#64748b;font-size:11px;">Mã sách duy nhất đại diện cho đầu sách (VD: KV-ANT-0015).</small>
            </div>

            <!-- Thông tin tài liệu -->
            <div class="staff-edit-field">
                <label for="edit-title">Tên sách <span style="color:#dc2626;">*</span></label>
                <input id="edit-title" type="text" maxlength="300" required placeholder="Nhập tên đầu sách…">
            </div>

            <div class="staff-edit-field">
                <label for="edit-author">Tác giả <span style="color:#dc2626;">*</span></label>
                <input id="edit-author" type="text" maxlength="250" required placeholder="Nhập tên tác giả (phân cách bằng dấu phẩy nếu nhiều tác giả)…">
            </div>

            <div class="staff-edit-position">
                <div class="staff-edit-field">
                    <label for="edit-year">Năm xuất bản</label>
                    <input
                        id="edit-year"
                        type="number"
                        min="1000"
                        max="<?= (int)date('Y') + 1 ?>"
                        step="1"
                        placeholder="VD: <?= date('Y') ?>"
                    >
                </div>

                <!-- Số lượng cuốn ban đầu hoặc bổ sung -->
                <div class="staff-edit-field" id="field-copies-group">
                    <label id="label-copies" for="edit-copies">Số bản sách</label>
                    <input
                        id="edit-copies"
                        type="number"
                        min="1"
                        max="500"
                        step="1"
                        value="1"
                        required
                    >
                    <small id="note-copies" style="color:#64748b;font-size:11px;">Số cuốn sách vật lý sẽ được tạo tự động.</small>
                </div>
            </div>

            <!-- Mô tả tóm tắt -->
            <div class="staff-edit-field">
                <label for="edit-description">Mô tả tóm tắt</label>
                <textarea id="edit-description" rows="3" style="width:100%;padding:10px;border:1px solid #d4e2ec;border-radius:8px;font-family:inherit;font-size:12px;" placeholder="Tóm tắt nội dung sách hoặc ghi chú lưu trữ…"></textarea>
            </div>

            <!-- Kết thúc thao tác chỉnh sửa -->
            <div class="staff-edit-actions">
                <button id="cancel-edit-book" type="button">Hủy</button>
                <button
                    class="staff-edit-save"
                    id="save-book-button"
                    type="submit"
                >
                    Lưu vào CSDL
                </button>
            </div>
        </form>
    </dialog>

    <!-- Hộp thoại xem danh sách cuốn sách vật lý (Copies modal) -->
    <dialog
        class="staff-book-dialog"
        id="view-copies-dialog"
        aria-labelledby="copies-dialog-title"
        style="width:min(640px, calc(100% - 32px));"
    >
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h2 id="copies-dialog-title" style="margin:0;font-size:18px;">Danh sách bản sách vật lý</h2>
            <button type="button" id="close-copies-dialog" class="staff-action-button" style="padding:4px 10px;">✕ Đóng</button>
        </div>

        <p id="copies-book-info" style="margin:0 0 16px;font-size:13px;color:#475569;"></p>

        <div class="staff-table-wrap" style="max-height:360px;overflow-y:auto;">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th scope="col">Mã cuốn sách</th>
                        <th scope="col">Mã vạch</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col">Ngày nhập kho</th>
                    </tr>
                </thead>
                <tbody id="copies-table-body">
                    <tr><td colspan="4" style="text-align:center;">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
    </dialog>

    <!-- Script tương tác thời gian thực với CSDL qua AJAX -->
    <script src="/assets/js/librarian-books.js" defer></script>
</body>
</html>