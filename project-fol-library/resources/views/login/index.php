<?php

declare(strict_types=1);

// Vai trò dùng để xem thử giao diện, không cấp quyền thật.
$roles = [
    'student' => 'Sinh viên',
    'librarian' => 'Cán bộ thư viện',
    'admin' => 'Quản trị viên',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập · Thư viện Khoa Văn học</title>

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
        <!-- Giới thiệu thư viện -->
        <section class="auth-intro" aria-labelledby="intro-title">
            <img
                class="auth-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >

            <p class="auth-eyebrow">USSH · VNU-HCM</p>

            <h1 id="intro-title">
                Tiếp nối hành trình<br>
                khám phá tri thức.
            </h1>

            <p>
                Tra cứu tài liệu, theo dõi sách mượn và tìm
                một góc đọc dành riêng cho bạn.
            </p>

            <span class="auth-decoration" aria-hidden="true">❧</span>
        </section>

        <!-- Biểu mẫu đăng nhập thử nghiệm -->
        <section class="auth-card" aria-labelledby="login-title">
            <p class="auth-eyebrow">CHÀO MỪNG BẠN TRỞ LẠI</p>
            <h2 id="login-title">Đăng nhập</h2>

            <p class="auth-description">
                Sử dụng tài khoản thư viện của bạn.
            </p>

            <form id="login-form">
                <!-- Vai trò trải nghiệm -->
                <div class="auth-field">
                    <label for="login-role">Vai trò trải nghiệm</label>

                    <select id="login-role" required>
                        <?php foreach ($roles as $value => $label): ?>
                            <option value="<?= htmlspecialchars(
                                $value,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>">
                                <?= htmlspecialchars(
                                    $label,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Email đăng nhập -->
                <div class="auth-field">
                    <label for="login-email">Email</label>

                    <input
                        id="login-email"
                        type="email"
                        autocomplete="username"
                        placeholder="Nhập email của bạn"
                        required
                    >
                </div>

                <!-- Mật khẩu & nút hiện/ẩn -->
                <div class="auth-field">
                    <label for="login-password">Mật khẩu</label>

                    <div class="auth-password">
                        <input
                            id="login-password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Nhập mật khẩu thử nghiệm"
                            aria-describedby="login-note"
                            required
                        >

                        <button
                            id="toggle-password"
                            type="button"
                            aria-controls="login-password"
                            aria-pressed="false"
                        >
                            Hiện
                        </button>
                    </div>
                </div>

                <!-- Thông báo phạm vi bản thử -->
                <p class="auth-notice" id="login-note">
                    Bản thử giao diện: hãy dùng thông tin giả.
                    Mật khẩu không được gửi đi hoặc lưu lại.
                    Chọn vai trò không cấp quyền truy cập thật.
                </p>

                <button class="auth-submit" type="submit">
                    Thử đăng nhập
                </button>

                <!-- Kết quả thao tác mẫu -->
                <p
                    class="auth-feedback"
                    id="login-feedback"
                    role="status"
                ></p>
            </form>

            <!-- Liên kết đăng ký dành riêng cho sinh viên -->
            <p class="auth-switch">
                Sinh viên mới? <a href="/register">Đăng ký tài khoản</a>
            </p>
        </section>
    </main>

    <!-- Tương tác giao diện đăng nhập -->
    <script src="/assets/js/auth.js" defer></script>
</body>
</html>