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

            <p class="auth-eyebrow">USSH · VNU-HANOI</p>

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
                    <label for="login-role">Chọn vai trò trải nghiệm</label>

                    <select id="login-role">
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

                <!-- Phím tắt vào nhanh trực tiếp theo vai trò -->
                <div class="auth-quick-actions" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 14px; margin-bottom: 20px;">
                    <a href="/profile" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 6px; background: #eef6fb; border: 1px solid #c2ddf0; border-radius: 10px; text-decoration: none; color: #005baa; font-size: 13px; font-weight: 500; text-align: center; transition: all 0.2s;">
                        <span style="font-size: 22px; margin-bottom: 4px;">🎓</span>
                        <span>Sinh viên</span>
                    </a>
                    <a href="/librarian" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 6px; background: #eef9f2; border: 1px solid #bce6cc; border-radius: 10px; text-decoration: none; color: #1b8846; font-size: 13px; font-weight: 500; text-align: center; transition: all 0.2s;">
                        <span style="font-size: 22px; margin-bottom: 4px;">📚</span>
                        <span>Cán bộ</span>
                    </a>
                    <a href="/admin/settings" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 6px; background: #fff8e6; border: 1px solid #f6dd9e; border-radius: 10px; text-decoration: none; color: #b78103; font-size: 13px; font-weight: 500; text-align: center; transition: all 0.2s;">
                        <span style="font-size: 22px; margin-bottom: 4px;">⚙️</span>
                        <span>Quản trị viên</span>
                    </a>
                </div>

                <!-- Email đăng nhập (Không bắt buộc) -->
                <div class="auth-field">
                    <label for="login-email">Email <span style="font-size: 12px; font-weight: 400; color: #718695;">(Không bắt buộc)</span></label>

                    <input
                        id="login-email"
                        type="email"
                        autocomplete="username"
                        placeholder="Có thể bỏ trống để vào xem ngay"
                    >
                </div>

                <!-- Mật khẩu & nút hiện/ẩn (Không bắt buộc) -->
                <div class="auth-field">
                    <label for="login-password">Mật khẩu <span style="font-size: 12px; font-weight: 400; color: #718695;">(Không bắt buộc)</span></label>

                    <div class="auth-password">
                        <input
                            id="login-password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Có thể bỏ trống để vào xem ngay"
                            aria-describedby="login-note"
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
                    Chế độ trải nghiệm giao diện nhanh: Không cần nhập Email và Mật khẩu. Bạn có thể chọn vai trò ở trên hoặc bấm nút dưới đây để vào xem trực tiếp.
                </p>

                <button class="auth-submit" type="submit">
                    Vào xem giao diện ngay →
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