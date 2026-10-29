<?php

declare(strict_types=1);

$feedbackTypes = [
    'book_request' => '📚 Đề xuất bổ sung đầu sách mới',
    'reading_room' => '🪑 Góp ý về cơ sở vật chất & phòng đọc',
    'service' => '🤝 Đóng góp ý kiến về dịch vụ mượn - trả',
    'other' => '💬 Ý kiến đóng góp khác',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Góp ý & Đề xuất sách · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
    <style>
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 20px;
        }
        .form-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #24445c;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #dce7ef;
            border-radius: 10px;
            font-size: 14px;
            color: #24445c;
            background: #ffffff;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #1e5c8c;
            box-shadow: 0 0 0 3px rgba(30, 92, 140, 0.12);
        }
        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }
        .submit-btn {
            background: #1e5c8c;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 13px 28px;
            font-size: 14.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .submit-btn:hover {
            background: #174a70;
            transform: translateY(-1px);
        }
        .toast-success {
            display: none;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/">
            <img class="brand-logo" src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
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

            <a href="/about">Giới thiệu</a>
            <a href="/rules">Nội quy</a>
            <a href="/feedback" class="active" aria-current="page">Góp ý</a>
            <a href="/contact">Liên hệ</a>

            <?php if (\App\Core\Session::isAuthenticated()): ?>
                <?php $navRole = \App\Core\Session::getRoleCode(); ?>
                <?php if ($navRole === 'ADMIN'): ?>
                    <a href="/profile">Cá nhân</a>
                    <a href="/librarian">Cán bộ</a>
                    <a href="/admin/settings">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực người dùng / Đăng nhập -->
        <?php if (\App\Core\Session::isAuthenticated()): ?>
            <?php
                $currentUser = \App\Core\Session::getAuthUser();
                $currentRole = \App\Core\Session::getRoleCode();
                $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8');
                $dashboardUrl = match ($currentRole) {
                    'ADMIN' => '/admin/settings',
                    'LIBRARIAN' => '/librarian',
                    default => '/profile',
                };
            ?>
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;margin-left:auto;">
                <a href="<?= $dashboardUrl ?>" class="login-button" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;padding:8px 16px;text-decoration:none;" title="Tài khoản của bạn">
                    <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                    <span><?= $userName ?></span>
                </a>
                <a href="/logout" class="login-button" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:8px 14px;color:#dc2626;border-color:#fecaca;background:#fff5f5;text-decoration:none;" title="Đăng xuất khỏi hệ thống">
                    Đăng xuất
                </a>
            </div>
        <?php else: ?>
            <a class="login-button" href="/login">Đăng nhập</a>
        <?php endif; ?>
    </header>

    <!-- Nội dung chính: Góp ý & Đề xuất tài liệu -->
    <main class="page">
        <!-- Banner tiêu đề -->
        <section class="hero" style="margin-bottom: 36px; background: #eaf3f9;">
            <div class="hero-content">
                <span class="eyebrow">LẮNG NGHE & HOÀN THIỆN DỊCH VỤ</span>
                <h1 style="margin: 16px 0;">Góp ý & <span>Đề xuất Sách mới</span></h1>
                <p style="font-size: 15px; color: #486581; line-height: 1.8;">
                    Mọi ý kiến đóng góp của bạn đọc là cơ sở quý báu để Thư viện Khoa Văn học không ngừng nâng cao chất lượng phục vụ, bổ sung nguồn học liệu thiết thực và hoàn thiện cơ sở vật chất phòng đọc.
                </p>
            </div>
            <div style="text-align: right; font-size: 80px; opacity: 0.85;">
                💡
            </div>
        </section>

        <!-- Khối Form & Khối Hướng dẫn -->
        <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 32px; align-items: flex-start; margin-bottom: 48px;">
            <!-- Cột trái: Form gửi ý kiến -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 32px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                <div id="feedback-alert" class="toast-success">
                    <strong>Cảm ơn bạn đã gửi ý kiến đóng góp!</strong><br>
                    Thông tin của bạn đã được chuyển tới Ban Quản lý Thư viện Khoa Văn học. Chúng tôi sẽ phản hồi qua email trong vòng 24 - 48 giờ làm việc.
                </div>

                <form id="feedback-form" onsubmit="handleFeedbackSubmit(event)">
                    <div class="form-group">
                        <label for="fb-type" class="form-label">Chủ đề đóng góp <span style="color:#dc2626;">*</span></label>
                        <select id="fb-type" class="form-select" required>
                            <?php foreach ($feedbackTypes as $key => $title): ?>
                                <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="fb-name" class="form-label">Họ và tên <span style="color:#dc2626;">*</span></label>
                            <input type="text" id="fb-name" class="form-input" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="form-group">
                            <label for="fb-email" class="form-label">Email liên hệ <span style="color:#dc2626;">*</span></label>
                            <input type="email" id="fb-email" class="form-input" placeholder="sv@ussh.edu.vn" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="fb-title" class="form-label">Tiêu đề ý kiến / Tên sách đề xuất <span style="color:#dc2626;">*</span></label>
                        <input type="text" id="fb-title" class="form-input" placeholder="Ví dụ: Đề xuất bổ sung giáo trình Lý luận Văn học tập 2..." required>
                    </div>

                    <div class="form-group">
                        <label for="fb-content" class="form-label">Nội dung chi tiết <span style="color:#dc2626;">*</span></label>
                        <textarea id="fb-content" class="form-textarea" placeholder="Mô tả chi tiết ý kiến góp ý của bạn, hoặc thông tin tác giả/nhà xuất bản nếu bạn muốn đề xuất mua sách mới..." required></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                        <button type="submit" class="submit-btn">Gửi ý kiến đóng góp</button>
                    </div>
                </form>
            </div>

            <!-- Cột phải: Thông tin tiếp nhận & Quy trình phản hồi -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                    <h3 style="font-size: 16px; color: #24445c; margin-top: 0; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                        <span>📋</span> Quy trình tiếp nhận & xử lý
                    </h3>
                    <ul style="margin: 0; padding-left: 20px; color: #486581; font-size: 13.5px; line-height: 1.8; display: flex; flex-direction: column; gap: 8px;">
                        <li><strong>Bước 1:</strong> Bạn đọc điền thông tin và gửi phiếu góp ý/đề xuất sách trực tuyến.</li>
                        <li><strong>Bước 2:</strong> Thủ thư phụ trách tiếp nhận, phân loại và chuyển đến Bộ phận Chuyên môn/Hội đồng Thư viện.</li>
                        <li><strong>Bước 3:</strong> Phản hồi kết quả tiếp nhận hoặc kế hoạch mua sắm tài liệu qua email của bạn đọc trong 1-2 ngày làm việc.</li>
                    </ul>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px;">
                    <h3 style="font-size: 15px; color: #24445c; margin-top: 0; margin-bottom: 10px;">📞 Kênh liên hệ trực tiếp</h3>
                    <p style="font-size: 13.5px; color: #6a8090; line-height: 1.7; margin: 0 0 12px 0;">
                        Nếu cần phản ánh các sự cố khẩn cấp tại phòng đọc, bạn đọc có thể liên hệ trực tiếp:
                    </p>
                    <div style="font-size: 13.5px; color: #24445c; line-height: 1.8;">
                        • <strong>Địa chỉ:</strong> Tầng 2, Nhà B, 336 Nguyễn Trãi, Thanh Xuân, Hà Nội<br>
                        • <strong>Hotline:</strong> (024) 3858 3799<br>
                        • <strong>Email:</strong> khoavan@ussh.edu.vn
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function handleFeedbackSubmit(e) {
            e.preventDefault();
            var alertBox = document.getElementById('feedback-alert');
            var form = document.getElementById('feedback-form');
            if (alertBox) {
                alertBox.style.display = 'block';
                form.reset();
                window.scrollTo({ top: form.offsetTop - 80, behavior: 'smooth' });
            }
        }
    </script>
</body>
</html>
