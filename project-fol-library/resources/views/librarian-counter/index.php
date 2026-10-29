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

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/counter.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header khu vực cán bộ -->
    <header class="staff-header">
        <a class="staff-brand" href="/" title="Về trang chủ Thư viện">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Cán bộ</small>
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
                    $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Tài khoản', ENT_QUOTES, 'UTF-8');
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
            <a href="/librarian/books">Quản lý sách</a>
            <a href="/librarian/counter" aria-current="page">Mượn / trả</a>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings">Về trang quản trị</a>
                <a href="/profile">Về trang cá nhân (SV)</a>
            <?php endif; ?>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề màn hình -->
            <section class="staff-intro">
                <span class="staff-eyebrow">NGHIỆP VỤ TẠI QUẦY</span>
                <h1>Mượn / trả sách</h1>
                <p>Tiếp nhận sinh viên và tạo phiếu mượn mẫu.</p>
            </section>

            <!-- Biểu mẫu tạo phiếu -->
            <section class="staff-panel" aria-labelledby="borrow-title">
                <h2 id="borrow-title">Tạo phiếu mượn</h2>

                <p class="staff-note">
                    Mã sinh viên mẫu: SV2026001 hoặc SV2026002.
                    Hạn trả do người trải nghiệm chọn.
                </p>

                <form id="counter-form">
                    <div class="counter-fields">
                        <!-- Nhập mã hoặc nhận dữ liệu từ máy quét dạng bàn phím -->
                        <div class="counter-field">
                            <label for="counter-student">Mã sinh viên</label>
                            <input
                                id="counter-student"
                                type="text"
                                list="counter-students"
                                placeholder="Nhập mã sinh viên"
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
                    Chưa có phiếu mượn.
                </p>

                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách phiếu mượn mẫu"
                >
                    <table class="staff-table">
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

            <!-- Giới hạn dữ liệu mẫu -->
            <p class="staff-note">
                Phiếu và số bản còn lại chỉ cập nhật trong lần mở trang này.
                Tải lại sẽ khôi phục dữ liệu. Chưa liên kết profile sinh viên,
                Dashboard hoặc CSDL.
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
    <script src="/assets/js/counter.js" defer></script>
</body>
</html>