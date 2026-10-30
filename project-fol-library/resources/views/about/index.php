<?php

declare(strict_types=1);

// Dữ liệu giới thiệu về Thư viện Khoa Văn học
$stats = [
    ['num' => '1.200+', 'label' => 'Đầu sách & Tư liệu quý'],
    ['num' => '70 Năm', 'label' => 'Bề dày học thuật truyền thống'],
    ['num' => '40+', 'label' => 'Chỗ ngồi tự học tiện nghi'],
    ['num' => '100%', 'label' => 'Số hóa tra cứu thời gian thực'],
];

$milestones = [
    [
        'year' => '1956',
        'title' => 'Khởi dựng không gian học thuật',
        'desc' => 'Gắn liền với ngày thành lập Khoa Ngữ văn - Đại học Tổng hợp Hà Nội (nay là Trường Đại học Khoa học Xã hội và Nhân văn, ĐHQGHN).'
    ],
    [
        'year' => '1995',
        'title' => 'Hình thành Thư viện Bộ môn & Khoa',
        'desc' => 'Quy tụ hàng ngàn bản sách chữ Hán, Nôm, tư liệu văn học dân gian, văn học hiện đại và văn học thế giới do các thế hệ giáo sư, học giả đầu ngành trao tặng.'
    ],
    [
        'year' => '2024 - 2026',
        'title' => 'Chuyển đổi số & Hiện đại hóa thư viện',
        'desc' => 'Triển khai hệ thống Thư viện số FoL Digital Library, hỗ trợ đặt chỗ tự học trực tuyến, tra cứu phân loại đa chiều và quản lý mượn trả tự động.'
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giới thiệu · Thư viện Khoa Văn học (USSH · VNU-HANOI)</title>

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
                <a href="/about" class="active" aria-current="page">Giới thiệu</a>
                <a href="/rules">Nội quy</a>
                <a href="/feedback">Góp ý</a>
                <a href="/contact">Liên hệ</a>
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

    <!-- Nội dung chính: Trang Giới thiệu -->
    <main class="page">
        <!-- Hero: Giới thiệu tổng quan -->
        <section class="hero" style="margin-bottom: 40px; background: linear-gradient(135deg, #e8f2f8 0%, #f4f8fb 100%);">
            <div class="hero-content">
                <span class="eyebrow">KHÔNG GIAN HỌC THUẬT & TRI THỨC</span>
                <h1 style="margin: 16px 0;">Thư viện <span>Khoa Văn học</span></h1>
                <p style="font-size: 15px; color: #486581; line-height: 1.8;">
                    Nơi lưu giữ hồn cốt văn hóa, tinh hoa học thuật và những trang sách vàng của các thế hệ học giả, giảng viên và sinh viên Khoa Văn học suốt 70 năm xây dựng và phát triển.
                </p>
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <a href="/books" class="login-button" style="background:#1e5c8c; color:white; border-color:#1e5c8c; font-weight:600;">Khám phá kho sách</a>
                    <a href="/rules" class="login-button" style="font-weight:600;">Xem nội quy thư viện</a>
                </div>
            </div>
            <div style="text-align: center;">
                <img src="/assets/images/logo.jpg" alt="Thư viện Khoa Văn học" style="width: 220px; height: 220px; object-fit: contain; border-radius: 24px; box-shadow: 0 16px 32px rgba(30, 92, 140, 0.12); background: white; padding: 16px;">
            </div>
        </section>

        <!-- Thống kê nổi bật -->
        <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 48px;">
            <?php foreach ($stats as $st): ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                    <div style="font-size: 32px; font-weight: 700; color: #1e5c8c; font-family: 'Literata', serif;"><?= htmlspecialchars($st['num'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size: 13px; color: #718695; margin-top: 6px; font-weight: 500;"><?= htmlspecialchars($st['label'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Sứ mệnh và Giá trị cốt lõi -->
        <section style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 48px;">
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 32px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: #eaf3f9; color: #1e5c8c; font-size: 20px; margin-bottom: 16px;">🎯</div>
                <h2 style="font-family: 'Literata', serif; font-size: 22px; color: #24445c; margin-top: 0;">Sứ Mệnh Học Thuật</h2>
                <p style="font-size: 14.5px; color: #486581; line-height: 1.8;">
                    Thư viện Khoa Văn học là trung tâm thông tin tư liệu chuyên ngành trọng điểm, có nhiệm vụ lưu trữ, bảo tồn và phổ biến các tài liệu nghiên cứu văn học, ngôn ngữ, lý luận phê bình, văn học dân gian và văn học các dân tộc. Chúng tôi đồng hành cùng cán bộ nghiên cứu, giảng viên và người học trong sự nghiệp khám phá chân trời tri thức nhân văn.
                </p>
            </div>

            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 32px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: #eaf3f9; color: #1e5c8c; font-size: 20px; margin-bottom: 16px;">💡</div>
                <h2 style="font-family: 'Literata', serif; font-size: 22px; color: #24445c; margin-top: 0;">Tầm Nhìn & Hiện Đại Hóa</h2>
                <p style="font-size: 14.5px; color: #486581; line-height: 1.8;">
                    Xây dựng mô hình thư viện số thông minh kết hợp không gian đọc truyền thống ấm cúng. Hệ thống số hóa từng bước toàn bộ mục lục, tích hợp quản lý đặt chỗ theo thời gian thực và tra cứu trực tuyến 24/7, trở thành điểm tựa tri thức tin cậy hàng đầu trong khối các trường đại học nhân văn.
                </p>
            </div>
        </section>

        <!-- Dấu ấn lịch sử (Timeline) -->
        <section style="background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 36px; margin-bottom: 48px;">
            <h2 style="font-family: 'Literata', serif; font-size: 24px; color: #24445c; margin-top: 0; margin-bottom: 24px; text-align: center;">Chặng Đường Hình Thành & Phát Triển</h2>
            <div style="display: flex; flex-direction: column; gap: 24px; max-width: 800px; margin: 0 auto;">
                <?php foreach ($milestones as $m): ?>
                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <div style="min-width: 90px; padding: 8px 14px; background: #eaf3f9; color: #1e5c8c; font-weight: 700; border-radius: 10px; text-align: center; font-size: 15px;">
                            <?= htmlspecialchars($m['year'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div style="border-left: 2px solid #e2e8f0; padding-left: 20px; padding-bottom: 8px;">
                            <h3 style="margin: 0 0 6px 0; font-size: 16px; color: #24445c;"><?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p style="margin: 0; font-size: 14px; color: #6a8090; line-height: 1.7;"><?= htmlspecialchars($m['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Khối kêu gọi liên hệ & hỗ trợ -->
        <section style="background: #1e5c8c; color: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 12px 32px rgba(30, 92, 140, 0.2);">
            <h2 style="font-family: 'Literata', serif; font-size: 26px; margin: 0 0 12px 0;">Bạn cần hỗ trợ tra cứu hoặc mượn tài liệu?</h2>
            <p style="font-size: 15px; color: #dce7ef; max-width: 600px; margin: 0 auto 24px auto; line-height: 1.7;">
                Đội ngũ cán bộ thư viện Khoa Văn học luôn sẵn sàng đồng hành và giải đáp mọi thắc mắc của bạn đọc trong suốt các ngày làm việc trong tuần.
            </p>
            <div style="display: inline-flex; gap: 14px;">
                <a href="/contact" class="login-button" style="background: white; color: #1e5c8c; border: none; font-weight: 600; padding: 12px 24px;">Liên hệ với chúng tôi</a>
                <a href="/feedback" class="login-button" style="background: transparent; color: white; border-color: rgba(255,255,255,0.4); font-weight: 600; padding: 12px 24px;">Gửi góp ý & đề xuất sách</a>
            </div>
        </section>
    </main>
</body>
</html>
