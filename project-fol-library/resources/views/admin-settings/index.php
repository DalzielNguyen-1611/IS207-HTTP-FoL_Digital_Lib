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
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-settings.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header quản trị -->
    <header class="staff-header">
        <a class="staff-brand" href="/admin/users">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Khu vực quản trị</small>
            </span>
        </a>

        <a class="staff-public-link" href="/">Về trang chủ</a>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng quản trị -->
        <nav class="staff-sidebar" aria-label="Điều hướng quản trị">
            <span class="staff-menu-label">QUẢN TRỊ HỆ THỐNG</span>
            <a href="/admin/users">Người dùng</a>
            <a href="/admin/audit">Nhật ký</a>
            <a href="/admin/settings" aria-current="page">Cấu hình</a>
            <a href="/librarian">Giao diện cán bộ</a>
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