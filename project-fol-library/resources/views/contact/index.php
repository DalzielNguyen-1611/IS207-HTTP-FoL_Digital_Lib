<?php

declare(strict_types=1);

$contactCards = [
    [
        'icon' => '📍',
        'title' => 'Địa chỉ phòng đọc',
        'detail' => 'Tầng 2, Nhà B, Trường Đại học Khoa học Xã hội và Nhân văn',
        'sub' => '336 Nguyễn Trãi, Quận Thanh Xuân, Hà Nội'
    ],
    [
        'icon' => '⏰',
        'title' => 'Thời gian mở cửa',
        'detail' => 'Thứ 2 đến Thứ 6: 07:30 - 21:00',
        'sub' => 'Thứ 7: 08:00 - 17:00 (Nghỉ Chủ nhật & ngày lễ)'
    ],
    [
        'icon' => '📞',
        'title' => 'Điện thoại liên hệ',
        'detail' => '(024) 3858 3799',
        'sub' => 'Hotline hỗ trợ: (024) 3858 5282'
    ],
    [
        'icon' => '✉️',
        'title' => 'Hộp thư điện tử',
        'detail' => 'khoavan@ussh.edu.vn',
        'sub' => 'thuvien.khoavan@ussh.edu.vn'
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liên hệ · Thư viện Khoa Văn học</title>

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
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

            <?php 
                $isAuthenticated = \App\Core\Session::isAuthenticated();
                $navRole = $isAuthenticated ? \App\Core\Session::getRoleCode() : null;
            ?>

            <?php if ($navRole !== 'ADMIN' && $navRole !== 'LIBRARIAN'): ?>
                <a href="/about">Giới thiệu</a>
                <a href="/rules">Nội quy</a>
                <a href="/feedback">Góp ý</a>
                <a href="/contact" class="active" aria-current="page">Liên hệ</a>
            <?php endif; ?>

            <?php if ($isAuthenticated): ?>
                <?php if ($navRole === 'ADMIN'): ?>
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
            <a class="login-button" href="/login">Đăng nhập</a>
        <?php endif; ?>
    </header>

    <!-- Nội dung chính: Thông tin liên hệ -->
    <main class="page">
        <!-- Banner tiêu đề -->
        <section class="hero" style="margin-bottom: 36px; background: #eaf3f9;">
            <div class="hero-content">
                <span class="eyebrow">KẾT NỐI & HỖ TRỢ BẠN ĐỌC</span>
                <h1 style="margin: 16px 0;">Thông tin <span>Liên hệ Thư viện</span></h1>
                <p style="font-size: 15px; color: #486581; line-height: 1.8;">
                    Chúng tôi luôn sẵn lòng chào đón, hỗ trợ và đồng hành cùng quý thầy cô, các nhà nghiên cứu và toàn thể các bạn sinh viên trong mọi hoạt động học thuật và tìm kiếm nguồn học liệu.
                </p>
            </div>
            <div style="text-align: right; font-size: 80px; opacity: 0.85;">
                📞
            </div>
        </section>

        <!-- Lưới thẻ thông tin liên hệ -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; margin-bottom: 48px;">
            <?php foreach ($contactCards as $card): ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04); display: flex; flex-direction: column; gap: 8px;">
                    <div style="font-size: 32px; margin-bottom: 4px;"><?= $card['icon'] ?></div>
                    <h3 style="margin: 0; font-size: 16px; color: #24445c;"><?= htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <div style="font-size: 14.5px; font-weight: 600; color: #1e5c8c; line-height: 1.5;"><?= htmlspecialchars($card['detail'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size: 13px; color: #718695; line-height: 1.5;"><?= htmlspecialchars($card['sub'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Khung gửi tin nhắn nhanh -->
        <section style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 36px; box-shadow: 0 4px 16px rgba(36, 68, 92, 0.04);">
            <div style="max-width: 680px; margin: 0 auto; text-align: center;">
                <h2 style="font-family: 'Literata', serif; font-size: 24px; color: #24445c; margin: 0 0 10px 0;">Gửi thư hỏi đáp hoặc yêu cầu hỗ trợ</h2>
                <p style="font-size: 14px; color: #718695; margin: 0 0 24px 0;">
                    Bạn cần liên hệ mượn tài liệu lưu trữ đặc biệt, đăng ký hướng dẫn sử dụng thư viện hoặc đề xuất mượn liên thư viện?
                </p>
                <div style="display: inline-flex; gap: 14px; justify-content: center;">
                    <a href="mailto:khoavan@ussh.edu.vn" class="login-button" style="background: #1e5c8c; color: white; border-color: #1e5c8c; font-weight: 600; padding: 12px 24px;">Gửi email trực tiếp</a>
                    <a href="/feedback" class="login-button" style="font-weight: 600; padding: 12px 24px;">Gửi biểu mẫu góp ý & đề xuất</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
