<?php

declare(strict_types=1);

$error = $_GET['error'] ?? null;
$message = $_GET['message'] ?? null;
$savedEmail = $_GET['email'] ?? '';
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
    <style>
        .auth-alert-error {
            background: #fdf2f2;
            border: 1px solid #f8b4b4;
            color: #9b1c1c;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .auth-alert-success {
            background: #f3faf7;
            border: 1px solid #bcf0da;
            color: #03543f;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-google-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 8px;
            color: #3c4043;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(60,64,67,0.08);
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .btn-google-login:hover {
            background: #f8fafc;
            border-color: #4285f4;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.2);
            transform: translateY(-1px);
        }
        .auth-divider {
            display: flex;
            align-items: center;
            margin: 20px 0 16px 0;
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        .auth-divider span {
            padding: 0 12px;
        }
    </style>
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

            <p class="auth-eyebrow">USSH · VNU-HANOI</p>

            <h1 id="intro-title">
                Tiếp nối hành trình<br>
                khám phá tri thức.
            </h1>

            <p>
                Tra cứu tài liệu, theo dõi sách mượn và tìm
                một không gian đọc phù hợp dành riêng cho bạn.
            </p>

            <span class="auth-decoration" aria-hidden="true">❧</span>
        </section>

        <!-- Biểu mẫu đăng nhập xác thực -->
        <section class="auth-card" aria-labelledby="login-title">
            <p class="auth-eyebrow">CHÀO MỪNG BẠN TRỞ LẠI</p>
            <h2 id="login-title">Đăng nhập</h2>

            <p class="auth-description">
                Sử dụng tài khoản thư viện của bạn.
            </p>

            <?php if (!empty($error)): ?>
                <div class="auth-alert-error" role="alert">
                    <span style="font-size: 18px;">⚠️</span>
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="auth-alert-success" role="status">
                    <span style="font-size: 18px;">✅</span>
                    <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <!-- Form POST đăng nhập trực tiếp vào Backend -->
            <form id="login-form" method="POST" action="/login">
                <!-- Email đăng nhập -->
                <div class="auth-field">
                    <label for="login-email">Email</label>

                    <input
                        id="login-email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        placeholder="librarian@fol.uit.edu.vn"
                        required
                        value="<?= htmlspecialchars($savedEmail, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <!-- Mật khẩu & nút hiện/ẩn -->
                <div class="auth-field">
                    <label for="login-password">Mật khẩu</label>

                    <div class="auth-password">
                        <input
                            id="login-password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Nhập mật khẩu..."
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

                <div style="display: flex; justify-content: space-between; align-items: center; margin: 12px 0 18px 0; font-size: 13px;">
                    <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; color: #4b5563;">
                        <input type="checkbox" name="remember" value="1"> Ghi nhớ phiên đăng nhập
                    </label>
                    <a href="/forgot-password" style="color: #005baa; text-decoration: none;">Quên mật khẩu?</a>
                </div>

                <button class="auth-submit" type="submit" id="submit-login-btn">
                    Đăng nhập
                </button>

                <!-- Kết quả thao tác -->
                <p
                    class="auth-feedback"
                    id="login-feedback"
                    role="status"
                    style="margin-top: 12px; font-size: 13px; color: #0284c7; text-align: center;"
                ></p>
            </form>

            <div class="auth-divider">
                <span>hoặc</span>
            </div>

            <!-- Đăng nhập Google chuyển xuống phía dưới -->
            <a href="/auth/google" class="btn-google-login" id="btn-google-login" title="Đăng nhập với Google">
                <svg width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.66 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.66 48 24 48z"/>
                </svg>
                <span>Đăng nhập với Google</span>
            </a>
        </section>
    </main>

    <!-- Tương tác điền form và hiện/ẩn mật khẩu -->
    <script src="/assets/js/auth.js" defer></script>
</body>
</html>