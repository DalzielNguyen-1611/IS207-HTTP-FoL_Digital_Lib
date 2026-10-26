<?php

declare(strict_types=1);

// Chuyên ngành mẫu phục vụ giao diện đăng ký.
$majors = [
    'Văn học',
    'Ngôn ngữ học',
    'Văn hóa học',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký sinh viên · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Điều hướng về trang chủ -->
    <header class="auth-header">
        <a href="/">← Về trang chủ</a>
        <span>Thư viện Khoa Văn học</span>
    </header>

    <main class="auth-layout">
        <!-- Giới thiệu đăng ký sinh viên -->
        <section class="auth-intro" aria-labelledby="intro-title">
            <img
                class="auth-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >

            <p class="auth-eyebrow">USSH · VNU-HANOI</p>

            <h1 id="intro-title">
                Mở cánh cửa<br>
                đến không gian tri thức.
            </h1>

            <p>
                Tạo hồ sơ sinh viên để bắt đầu khám phá tài liệu
                và các tiện ích của thư viện.
            </p>

            <span class="auth-decoration" aria-hidden="true">❧</span>
        </section>

        <!-- Biểu mẫu đăng ký dành riêng cho sinh viên -->
        <section class="auth-card" aria-labelledby="register-title">
            <p class="auth-eyebrow">DÀNH CHO SINH VIÊN</p>
            <h2 id="register-title">Đăng ký tài khoản</h2>

            <p class="auth-description">
                Điền thông tin sinh viên của bạn.
            </p>

            <form id="register-form">
                <!-- Thông tin cá nhân -->
                <div class="auth-field">
                    <label for="register-name">Họ và tên</label>
                    <input
                        id="register-name"
                        type="text"
                        autocomplete="name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="auth-field">
                    <label for="register-code">Mã sinh viên</label>
                    <input
                        id="register-code"
                        type="text"
                        maxlength="30"
                        required
                    >
                </div>

                <div class="auth-field">
                    <label for="register-major">Chuyên ngành</label>
                    <select id="register-major" required>
                        <option value="">Chọn chuyên ngành</option>

                        <?php foreach ($majors as $major): ?>
                            <option value="<?= htmlspecialchars(
                                $major,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                <?= htmlspecialchars(
                                    $major,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Email sinh viên -->
                <div class="auth-field">
                    <label for="register-email">Email sinh viên</label>
                    <input
                        id="register-email"
                        type="email"
                        autocomplete="email"
                        aria-describedby="register-note"
                        required
                    >
                </div>

                <!-- Mật khẩu và xác nhận -->
                <div class="auth-field">
                    <label for="register-password">Mật khẩu thử nghiệm</label>

                    <div class="auth-password">
                        <input
                            id="register-password"
                            type="password"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button
                            id="register-toggle"
                            type="button"
                            aria-controls="register-password"
                            aria-pressed="false"
                        >
                            Hiện
                        </button>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="register-confirm">Nhập lại mật khẩu</label>
                    <input
                        id="register-confirm"
                        type="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>

                <!-- Phạm vi bản thử và quy tắc chưa cấu hình -->
                <p class="auth-notice" id="register-note">
                    Hãy dùng thông tin giả. Bản thử chưa tạo tài khoản
                    hoặc lưu dữ liệu. Tên miền email sinh viên và quy tắc
                    mã sinh viên sẽ được cấu hình theo yêu cầu của trường.
                    Mật khẩu tối thiểu 8 ký tự là quy tắc thử nghiệm.
                </p>

                <button class="auth-submit" type="submit">
                    Thử đăng ký
                </button>

                <!-- Kết quả đăng ký mẫu -->
                <p
                    class="auth-feedback"
                    id="register-feedback"
                    role="status"
                ></p>
            </form>

            <!-- Liên kết đăng nhập -->
            <p class="auth-switch">
                Đã có tài khoản? <a href="/login">Đăng nhập</a>
            </p>
        </section>
    </main>

    <!-- Tương tác đăng ký mẫu -->
    <script src="/assets/js/register.js" defer></script>
</body>
</html>