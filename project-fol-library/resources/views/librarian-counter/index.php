<?php

declare(strict_types=1);

// Đọc dữ liệu sách và bạn đọc từ CSDL thực tế
$bookRepo = new \App\Repositories\BookRepository();
$userRepo = new \App\Repositories\UserRepository();
$loanRepo = new \App\Repositories\LoanRepository();

$bookData = $bookRepo->getPaginated(1, 100);
$books = [];
foreach ($bookData['items'] as $b) {
    $books[] = [
        'id' => $b['id_dau_sach'],
        'title' => $b['tieu_de'],
        'available' => (int)($b['co_the_muon'] ?? 0),
    ];
}

$rawStudents = $userRepo->getStudents();
$students = [];
foreach ($rawStudents as $st) {
    $students[$st['mssv']] = $st['name'];
}

$pendingCount = $userRepo->countPendingStudents();
$counterLoans = $loanRepo->getCounterLoans();

$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mượn / trả tại quầy · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo70.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo70.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/counter.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/" title="Về trang chủ Thư viện">
            <img
                class="brand-logo"
                src="/assets/images/logo70.png"
                alt="Logo Khoa Văn học"
            >

            <span>
                <span class="brand-label">USSH · VNU-HANOI</span>
                <strong>Thư viện Khoa Văn học</strong>
            </span>
        </a>

        <nav id="primary-navigation" class="navigation" aria-label="Điều hướng chính">
            <a href="/">Trang chủ</a>

            <!-- Menu thả xuống: Dịch vụ thư viện -->
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-trigger" aria-expanded="false" aria-haspopup="true">
                    <span>Dịch vụ</span>
                    <svg class="nav-dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div class="nav-dropdown-menu" role="menu">
                    <a href="/books" class="nav-dropdown-item" role="menuitem">
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
                    <a href="/librarian" class="active" aria-current="page">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian" class="active" aria-current="page">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <button class="mobile-nav-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="Mở menu" onclick="const nav=this.closest('header').querySelector('.navigation'); const expanded=this.getAttribute('aria-expanded')==='true'; this.setAttribute('aria-expanded',String(!expanded)); this.setAttribute('aria-label',expanded?'Mở menu':'Đóng menu'); nav.classList.toggle('is-open',!expanded);">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- Trạng thái xác thực người dùng / Đăng xuất -->
        <?php if ($isAuthenticated): ?>
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
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

    <main class="staff-layout">
        <!-- Điều hướng nghiệp vụ cán bộ thư viện -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <a href="/librarian"><span>Tổng quan</span></a>
            <a href="/librarian/students">
                <span>Duyệt thẻ sinh viên</span>
                <?php if (($pendingCount ?? 0) > 0): ?>
                    <span class="staff-nav-badge"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="/librarian/books"><span>Quản lý sách</span></a>
            <a href="/librarian/counter" aria-current="page"><span>Mượn / trả</span></a>
            <a href="/librarian/rooms"><span>Phòng đọc</span></a>
            <a href="/librarian/reports"><span>Báo cáo</span></a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings"><span>Về trang quản trị</span></a>
                <a href="/profile"><span>Về trang cá nhân (SV)</span></a>
            <?php endif; ?>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề màn hình -->
            <section class="staff-intro">
                <h1>Mượn / trả sách</h1>
                <p>Tiếp nhận sinh viên và xử lý phiếu mượn trả thời gian thực.</p>
            </section>

            <!-- Biểu mẫu tạo phiếu -->
            <section class="staff-panel" aria-labelledby="borrow-title">
                <h2 id="borrow-title">Tạo phiếu mượn</h2>

                <p class="staff-note counter-hint">
                    <?php if (!empty($students)): ?>
                        Gợi ý mã bạn đọc: <strong><?= htmlspecialchars(implode(', ', array_slice(array_keys($students), 0, 4)), ENT_QUOTES, 'UTF-8') ?></strong>.
                    <?php else: ?>
                        Nhập mã số sinh viên đã kích hoạt tài khoản.
                    <?php endif; ?>
                    Hạn trả do cán bộ chỉ định (mặc định 14 ngày).
                </p>

                <form id="counter-form">
                    <div class="counter-fields">
                        <!-- Nhập mã hoặc nhận dữ liệu từ máy quét dạng bàn phím -->
                        <div class="counter-field">
                            <label for="counter-student">Mã số sinh viên</label>
                            <input
                                id="counter-student"
                                type="text"
                                list="counter-students"
                                placeholder="Nhập mã số sinh viên"
                                autocomplete="off"
                                required
                            >

                            <datalist id="counter-students">
                                <?php foreach ($students as $code => $name): ?>
                                    <option
                                        value="<?= htmlspecialchars((string)$code, ENT_QUOTES, 'UTF-8') ?>"
                                        data-student-name="<?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <?= htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <!-- Chọn sách từ danh mục mẫu -->
                        <div class="counter-field">
                            <label for="counter-book">Sách cần mượn</label>

                            <select id="counter-book" required>
                                <option value="">Chọn sách</option>

                                <?php foreach ($books as $book): ?>
                                    <option
                                        value="<?= htmlspecialchars(
                                            (string) $book['id'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-title="<?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-available="<?= htmlspecialchars(
                                            (string) $book['available'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        <?= $book['available'] === 0 ? 'disabled' : '' ?>
                                    >
                                        <?= htmlspecialchars(
                                            $book['id'] . ' · ' .
                                            $book['title'] . ' · Còn ' .
                                            $book['available'] . ' bản',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Chọn hạn trả, chưa áp dụng chính sách số ngày mượn -->
                        <div class="counter-field">
                            <label for="counter-due">Hạn trả</label>
                            <input
                                id="counter-due"
                                type="date"
                                min="<?= htmlspecialchars(
                                    $today->format('Y-m-d'),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >
                        </div>
                    </div>

                    <!-- Tạo hoặc xóa dữ liệu đang nhập -->
                    <div class="counter-actions">
                        <button class="counter-primary" type="submit">
                            Tạo phiếu mẫu
                        </button>

                        <button class="staff-action-button" type="reset">
                            Nhập lại
                        </button>
                    </div>

                    <p class="staff-note" id="counter-feedback" role="status"></p>
                </form>
            </section>

            <!-- Danh sách phiếu vừa tạo -->
            <section class="staff-panel" aria-labelledby="counter-loans-title">
                <h2 id="counter-loans-title">Phiếu mượn trong phiên thử</h2>

                <p class="staff-note" id="counter-empty">
                    Chưa có phiếu mượn
                </p>

                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách phiếu mượn mẫu"
                >
                    <table class="staff-table" id="counter-loans-table">
                        <thead>
                            <tr>
                                <th scope="col">Mã phiếu</th>
                                <th scope="col">Sinh viên</th>
                                <th scope="col">Sách</th>
                                <th scope="col">Hạn trả</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody id="counter-loans"></tbody>
                    </table>
                </div>
            </section>

            <p class="staff-note">
                Dữ liệu phiếu mượn thời gian thực được đồng bộ trực tiếp với CSDL và Dashboard Thư viện FoL.
            </p>
        </div>
    </main>

    <!-- Hộp xác nhận trả sách hoặc ghi nhận mất -->
    <dialog
        class="counter-dialog"
        id="return-dialog"
        aria-labelledby="return-title"
    >
        <form id="return-form">
            <h2 id="return-title">Xử lý phiếu mượn</h2>
            <p id="return-description"></p>

            <!-- Ngày xử lý có thể thay đổi để kiểm tra quá hạn mẫu -->
            <div class="counter-field">
                <label for="return-date">Ngày xử lý mẫu</label>
                <input id="return-date" type="date" required>
            </div>

            <!-- Phí bồi thường riêng khi sách bị mất -->
            <div class="counter-field" id="compensation-field" hidden>
                <label for="return-compensation">
                    Phí bồi thường sách mất (đ)
                </label>
                <input
                    id="return-compensation"
                    type="number"
                    min="0"
                    step="1"
                    value="0"
                >
            </div>

            <!-- Chi tiết phí được tính lại khi đổi ngày -->
            <p class="counter-fee" id="return-fee" role="status"></p>

            <p class="staff-note">
                Ngày xử lý có thể thay đổi để thử giao diện.
                Phí mẫu gồm phí quá hạn và khoản bồi thường được nhập.
                Chưa ghi nhận thanh toán thật.
            </p>

            <!-- Xác nhận hoặc hủy thao tác -->
            <div class="counter-actions">
                <button class="counter-primary" type="submit">
                    Xác nhận xử lý mẫu
                </button>
                <button
                    class="staff-action-button"
                    id="cancel-return"
                    type="button"
                >
                    Quay lại
                </button>
            </div>
        </form>
    </dialog>

    <!-- Tương tác tạo phiếu mẫu -->
    <script>window.__INITIAL_COUNTER_LOANS__ = <?= json_encode($counterLoans, JSON_UNESCAPED_UNICODE) ?>;</script>
    <script src="/assets/js/counter.js" defer></script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>