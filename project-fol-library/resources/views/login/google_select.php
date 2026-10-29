<?php

declare(strict_types=1);

// Lấy danh sách các tài khoản sinh viên đã liên kết trong hệ thống
$pdo = \App\Core\Database::getInstance();
$stmt = $pdo->query("
    SELECT tk.id_tai_khoan, tk.email, hs.ho_ten, hs.mssv 
    FROM tai_khoan tk 
    LEFT JOIN ho_so_nguoi_dung hs ON tk.id_tai_khoan = hs.id_tai_khoan 
    WHERE tk.id_vai_tro = 3 AND tk.trang_thai = 'HoatDong' 
    ORDER BY tk.id_tai_khoan ASC
");
$studentAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Màu avatar phong cách Google
$avatarColors = ['#1a73e8', '#e37400', '#188038', '#d93025', '#9334e6'];

\App\Core\Session::writeClose();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập bằng Google · Chọn tài khoản</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        body {
            background-color: #f0f4f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            color: #1f1f1f;
        }
        .google-card {
            background: #ffffff;
            border-radius: 28px;
            width: 100%;
            max-width: 440px;
            padding: 40px 36px 36px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            border: 1px solid #e3e3e3;
            animation: fadeIn 0.25s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }
        .google-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .google-logo {
            margin-bottom: 16px;
        }
        .google-title {
            font-size: 24px;
            font-weight: 500;
            color: #1f1f1f;
            margin-bottom: 8px;
        }
        .google-subtitle {
            font-size: 14px;
            color: #444746;
            line-height: 1.5;
        }
        .account-list {
            list-style: none;
            margin: 16px 0;
            border-top: 1px solid #e3e3e3;
        }
        .account-item {
            border-bottom: 1px solid #e3e3e3;
        }
        .account-link {
            display: flex;
            align-items: center;
            padding: 14px 12px;
            text-decoration: none;
            color: inherit;
            border-radius: 8px;
            transition: background 0.15s ease;
        }
        .account-link:hover {
            background-color: #f0f4f9;
        }
        .account-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 18px;
            font-weight: 500;
            margin-right: 14px;
            flex-shrink: 0;
        }
        .account-info {
            flex: 1;
            overflow: hidden;
        }
        .account-name {
            font-size: 14px;
            font-weight: 500;
            color: #1f1f1f;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .account-email {
            font-size: 12px;
            color: #444746;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .use-other-account {
            display: flex;
            align-items: center;
            padding: 14px 12px;
            text-decoration: none;
            color: #1f1f1f;
            cursor: pointer;
            border-bottom: 1px solid #e3e3e3;
            transition: background 0.15s ease;
        }
        .use-other-account:hover {
            background-color: #f0f4f9;
        }
        .use-other-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f4f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            color: #444746;
            font-size: 20px;
        }
        .other-input-form {
            display: none;
            padding: 16px 12px;
            background: #fafafa;
            border-radius: 8px;
            margin: 12px 0;
            border: 1px solid #dadce0;
        }
        .other-input-form input {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #dadce0;
            border-radius: 6px;
            margin-bottom: 10px;
            outline: none;
        }
        .other-input-form input:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 2px rgba(26,115,232,0.2);
        }
        .other-input-form button {
            background: #1a73e8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            float: right;
        }
        .google-footer-text {
            font-size: 12px;
            color: #444746;
            line-height: 1.5;
            margin-top: 24px;
            text-align: left;
        }
        .google-footer-text a {
            color: #0b57d0;
            text-decoration: none;
        }
        .google-footer-text a:hover {
            text-decoration: underline;
        }
        .back-to-site {
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
            color: #0b57d0;
            text-decoration: none;
        }
        .back-to-site:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="google-card">
        <div class="google-header">
            <!-- Logo Google chuẩn -->
            <div class="google-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.66 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.66 48 24 48z"/>
                </svg>
            </div>
            <h1 class="google-title">Chọn một tài khoản</h1>
            <p class="google-subtitle">để tiếp tục đến <strong>Thư viện số Khoa Văn học</strong></p>
        </div>

        <ul class="account-list">
            <?php foreach ($studentAccounts as $index => $acc): 
                $color = $avatarColors[$index % count($avatarColors)];
                $name = $acc['ho_ten'] ?? 'Sinh viên';
                $initial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
            ?>
                <li class="account-item">
                    <a href="/auth/google?email=<?= urlencode($acc['email']) ?>" class="account-link">
                        <div class="account-avatar" style="background-color: <?= $color ?>;">
                            <?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="account-info">
                            <div class="account-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="account-email"><?= htmlspecialchars($acc['email'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>

            <!-- Tùy chọn sử dụng tài khoản khác -->
            <li>
                <div class="use-other-account" id="btn-toggle-other">
                    <div class="use-other-icon">
                        <span>👤+</span>
                    </div>
                    <div class="account-info">
                        <div class="account-name">Sử dụng một tài khoản khác</div>
                    </div>
                </div>

                <form class="other-input-form" id="other-form" method="GET" action="/auth/google">
                    <label for="custom-google-email" style="font-size: 12px; color: #444746; display: block; margin-bottom: 6px;">
                        Nhập email Google (@gm.uit.edu.vn hoặc @gmail.com):
                    </label>
                    <input 
                        type="email" 
                        id="custom-google-email" 
                        name="email" 
                        placeholder="ten.sinhvien@gm.uit.edu.vn" 
                        required
                    >
                    <div style="overflow: hidden;">
                        <button type="submit">Tiếp tục →</button>
                    </div>
                </form>
            </li>
        </ul>

        <p class="google-footer-text">
            Để tiếp tục, Google sẽ chia sẻ tên, địa chỉ email, tùy chọn ngôn ngữ và ảnh hồ sơ của bạn với Thư viện số Khoa Văn học. Hãy xem <a href="#">Chính sách quyền riêng tư</a> và <a href="#">Điều khoản dịch vụ</a> của thư viện.
        </p>

        <a href="/login" class="back-to-site">← Hủy và quay lại trang đăng nhập</a>
    </div>

    <script>
        const btnToggleOther = document.querySelector('#btn-toggle-other')
        const otherForm = document.querySelector('#other-form')
        const customEmailInput = document.querySelector('#custom-google-email')

        if (btnToggleOther && otherForm) {
            btnToggleOther.addEventListener('click', () => {
                const isHidden = otherForm.style.display === 'none' || otherForm.style.display === ''
                otherForm.style.display = isHidden ? 'block' : 'none'
                if (isHidden && customEmailInput) {
                    customEmailInput.focus()
                }
            })
        }
    </script>
</body>
</html>
