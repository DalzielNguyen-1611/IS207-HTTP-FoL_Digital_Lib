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
        .sample-role-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 6px;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid #d1d5db;
            background: #ffffff;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease;
        }
        .sample-role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .sample-role-btn.student:hover { border-color: #005baa; background: #eef6fb; color: #005baa; }
        .sample-role-btn.librarian:hover { border-color: #16a34a; background: #f0fdf4; color: #16a34a; }
        .sample-role-btn.admin:hover { border-color: #d97706; background: #fffbeb; color: #d97706; }
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

        <!-- Biểu mẫu đăng nhập xác thực thật -->
        <section class="auth-card" aria-labelledby="login-title">
            <p class="auth-eyebrow">CỔNG XÁC THỰC TẬP TRUNG</p>
            <h2 id="login-title">Đăng nhập</h2>

            <p class="auth-description">
                Đăng nhập bằng tài khoản nội bộ để truy cập hệ thống theo đúng quyền hạn.
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

            <!-- Bảng tài khoản mẫu hỗ trợ điền nhanh để kiểm thử -->
            <div style="margin-bottom: 20px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; justify-content: space-between;">
                    <span>Tài khoản kiểm thử trong CSDL:</span>
                    <span style="font-weight: 400; color: #94a3b8;">Bấm để tự điền form</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    <button type="button" class="sample-role-btn student" data-email="student@gm.uit.edu.vn" data-password="Password@123">
                        <span style="font-size: 18px; margin-bottom: 2px;">🎓</span>
                        <span>Sinh viên</span>
                    </button>
                    <button type="button" class="sample-role-btn librarian" data-email="librarian@fol.uit.edu.vn" data-password="Password@123">
                        <span style="font-size: 18px; margin-bottom: 2px;">📚</span>
                        <span>Thủ thư</span>
                    </button>
                    <button type="button" class="sample-role-btn admin" data-email="admin@fol.uit.edu.vn" data-password="Password@123">
                        <span style="font-size: 18px; margin-bottom: 2px;">⚙️</span>
                        <span>Quản trị viên</span>
                    </button>
                </div>
            </div>

            <!-- Form POST đăng nhập trực tiếp vào Backend -->
            <form id="login-form" method="POST" action="/login">
                <!-- Email đăng nhập -->
                <div class="auth-field">
                    <label for="login-email">Email tài khoản</label>

                    <input
                        id="login-email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        placeholder="Nhập email của bạn (ví dụ: student@gm.uit.edu.vn)"
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
                    Đăng nhập hệ thống →
                </button>

                <!-- Kết quả thao tác -->
                <p
                    class="auth-feedback"
                    id="login-feedback"
                    role="status"
                    style="margin-top: 12px; font-size: 13px; color: #0284c7; text-align: center;"
                ></p>
            </form>

            <!-- Liên kết đăng ký dành riêng cho sinh viên -->
            <p class="auth-switch">
                Sinh viên chưa có tài khoản? <a href="/register">Đăng ký tài khoản mới</a>
            </p>
        </section>
    </main>

    <!-- Tương tác điền form và hiện/ẩn mật khẩu -->
    <script src="/assets/js/auth.js" defer></script>
</body>
</html>