<?php

declare(strict_types=1);

// Tham số mẫu cho giao diện cấu hình.
$numberFields = [
    [
        'id' => 'settings-shelves',
        'label' => 'Số lượng kệ',
        'value' => 6,
        'min' => 1,
        'max' => 50,
    ],
    [
        'id' => 'settings-cells',
        'label' => 'Số ô trên mỗi kệ',
        'value' => 4,
        'min' => 1,
        'max' => 50,
    ],
];

$defaultFine = 5000;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cấu hình hệ thống · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-settings.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/" title="Về trang chủ Thư viện">
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
                    <a href="/librarian">Cán bộ</a>
                    <a href="/admin/settings" class="active" aria-current="page">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực người dùng / Đăng xuất -->
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

    <main class="staff-layout">
        <!-- Điều hướng quản trị -->
        <nav class="staff-sidebar" aria-label="Điều hướng quản trị">
            <span class="staff-menu-label">QUẢN TRỊ HỆ THỐNG</span>
            <a href="/admin/users">Người dùng</a>
            <a href="/admin/audit">Nhật ký</a>
            <a href="/admin/settings" aria-current="page">Cấu hình</a>
            <a href="/librarian">Giao diện cán bộ</a>
            <a href="/profile">Giao diện cá nhân (SV)</a>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề cấu hình -->
            <section class="staff-intro">
                <span class="staff-eyebrow">THAM SỐ VẬN HÀNH</span>
                <h1>Cấu hình hệ thống</h1>
                <p>Thiết lập thử sơ đồ, mức phạt và tùy chọn thông báo.</p>
            </section>

            <form id="settings-form">
                <!-- Cấu hình sơ đồ thư viện -->
                <section class="staff-panel" aria-labelledby="layout-title">
                    <h2 id="layout-title">Sơ đồ thư viện</h2>

                    <div class="settings-grid">
                        <?php foreach ($numberFields as $field): ?>
                            <div class="settings-field">
                                <label for="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8') ?>
                                </label>

                                <input
                                    id="<?= htmlspecialchars($field['id'], ENT_QUOTES, 'UTF-8') ?>"
                                    type="number"
                                    value="<?= htmlspecialchars((string) $field['value'], ENT_QUOTES, 'UTF-8') ?>"
                                    min="<?= htmlspecialchars((string) $field['min'], ENT_QUOTES, 'UTF-8') ?>"
                                    max="<?= htmlspecialchars((string) $field['max'], ENT_QUOTES, 'UTF-8') ?>"
                                    step="1"
                                    required
                                >
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <p class="staff-note" id="settings-capacity" role="status"></p>
                </section>

                <!-- Cấu hình phí quá hạn -->
                <section class="staff-panel" aria-labelledby="fine-title">
                    <h2 id="fine-title">Mức phạt quá hạn</h2>

                    <div class="settings-field">
                        <label for="settings-fine">Phí mỗi ngày (đ)</label>
                        <input
                            id="settings-fine"
                            type="number"
                            min="0"
                            max="1000000"
                            step="1"
                            value="<?= htmlspecialchars(
                                (string) $defaultFine,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >
                    </div>

                    <p class="staff-note">
                        Mặc định mẫu: 5.000đ/ngày.
                        Bồi thường sách mất là khoản xử lý riêng,
                        chưa được cấu hình tại đây.
                    </p>
                </section>

                <!-- Tùy chọn email tự động mẫu -->
                <section class="staff-panel" aria-labelledby="email-title">
                    <h2 id="email-title">Thông báo email</h2>

                    <label class="settings-checkbox">
                        <input
                            id="settings-email-enabled"
                            type="checkbox"
                            aria-controls="settings-email-fields"
                        >
                        Bật thông báo email tự động trong bản thử
                    </label>

                    <fieldset id="settings-email-fields" disabled>
                        <legend>Thông tin hiển thị người gửi</legend>

                        <div class="settings-grid">
                            <div class="settings-field">
                                <label for="settings-sender-name">Tên người gửi</label>
                                <input
                                    id="settings-sender-name"
                                    type="text"
                                    maxlength="100"
                                    value="Thư viện Khoa Văn học"
                                    required
                                >
                            </div>

                            <div class="settings-field">
                                <label for="settings-sender-email">Email người gửi mẫu</label>
                                <input
                                    id="settings-sender-email"
                                    type="email"
                                    maxlength="254"
                                    placeholder="thuvien@example.com"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Loại thông báo dự kiến -->
                        <div class="settings-email-types">
                            <label class="settings-checkbox">
                                <input id="settings-email-loan" type="checkbox" checked>
                                Xác nhận mượn / trả sách
                            </label>

                            <label class="settings-checkbox">
                                <input id="settings-email-due" type="checkbox" checked>
                                Nhắc hạn trả sách
                            </label>

                            <label class="settings-checkbox">
                                <input id="settings-email-room" type="checkbox" checked>
                                Xác nhận đặt / hủy phòng đọc
                            </label>
                        </div>
                    </fieldset>

                    <p class="staff-note">
                        Không nhập mật khẩu email hoặc thông tin SMTP.
                        Bản thử không gửi thư, không xác minh địa chỉ
                        và chưa cấu hình lịch gửi.
                    </p>
                </section>

                <!-- Thao tác lưu hoặc khôi phục mẫu -->
                <div class="settings-actions">
                    <button class="settings-save" type="submit">
                        Lưu cấu hình mẫu
                    </button>

                    <button class="staff-action-button" type="reset">
                        Khôi phục mặc định mẫu
                    </button>
                </div>

                <p class="staff-note" id="settings-feedback" role="status"></p>
            </form>

            <!-- Giới hạn phạm vi cấu hình -->
            <p class="staff-note">
                Các thay đổi chỉ phục vụ thử biểu mẫu trên trang này.
                Chưa áp dụng sang các trang khác và sẽ mất khi tải lại.
            </p>
        </div>
    </main>

    <!-- Tương tác cấu hình mẫu -->
    <script src="/assets/js/admin-settings.js" defer></script>
</body>
</html>