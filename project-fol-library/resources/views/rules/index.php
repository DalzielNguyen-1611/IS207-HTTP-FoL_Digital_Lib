<?php

declare(strict_types=1);

// Dữ liệu nội quy thư viện
$ruleCategories = [
    [
        'icon' => '🪪',
        'title' => 'I. Đối tượng & Điều kiện sử dụng',
        'items' => [
            'Cán bộ, giảng viên, sinh viên và học viên cao học thuộc Trường ĐHKHXH&NV và ĐHQG có nhu cầu học tập, nghiên cứu.',
            'Bạn đọc xuất trình Thẻ sinh viên / Thẻ cán bộ hoặc đăng nhập tài khoản trực tuyến (Google VNU) để sử dụng các tiện ích của thư viện.',
            'Trang phục lịch sự, tác phong nghiêm túc, tuân thủ hướng dẫn của cán bộ quản lý thư viện.'
        ]
    ],
    [
        'icon' => '📖',
        'title' => 'II. Quy chế Mượn - Trả tài liệu',
        'items' => [
            'Số lượng mượn về: Mỗi bạn đọc được mượn tối đa 03 đầu sách cùng một thời điểm.',
            'Thời hạn mượn: 14 ngày kể từ ngày mượn. Được gia hạn 01 lần (thêm 07 ngày) qua cổng thông tin nếu sách chưa có người khác đặt trước.',
            'Tài liệu tra cứu đặc biệt: Luận văn, luận án, từ điển tra cứu chuyên ngành và bản thảo sách quý hiếm chỉ được phục vụ đọc tại chỗ trong phòng đọc.',
            'Kiểm tra tình trạng sách: Bạn đọc có trách nhiệm kiểm tra kỹ tình trạng bản sách trước khi mang ra khỏi quầy mượn.'
        ]
    ],
    [
        'icon' => '🪑',
        'title' => 'III. Quy định Phòng đọc & Đặt chỗ ngồi',
        'items' => [
            'Thời gian hoạt động phòng đọc: Phục vụ 3 ca mỗi ngày (Ca sáng: 07:30 - 11:30 | Ca chiều: 13:00 - 17:00 | Ca tối: 17:30 - 21:00).',
            'Quy định Check-in: Bạn đọc đặt chỗ trực tuyến qua hệ thống phải có mặt và làm thủ tục check-in trong vòng 15 phút đầu của ca. Sau thời gian này, hệ thống sẽ tự động hủy lượt đặt để nhường chỗ cho bạn đọc khác.',
            'Giữ gìn không gian chung: Giữ im lặng tuyệt đối, chuyển điện thoại sang chế độ rung, không mang đồ ăn có mùi hoặc nước uống không có nắp đậy vào phòng đọc.'
        ]
    ],
    [
        'icon' => '⚖️',
        'title' => 'IV. Xử lý vi phạm & Bồi thường tài liệu',
        'items' => [
            'Trả sách trễ hạn: Phạt phí quá hạn 1.000 VNĐ / cuốn / ngày trễ hạn theo quy chế quản lý thư viện.',
            'Làm mất tài liệu: Bồi thường 01 bản sách mới cùng tên, cùng nhà xuất bản và nộp phí xử lý nghiệp vụ; hoặc đền bù giá trị theo định giá của hội đồng thư viện đối với tư liệu quý.',
            'Làm rách nát, viết vẽ bẩn lên sách: Xử phạt theo mức độ hư hại từ 50% đến 100% giá trị tài liệu.',
            'Đình chỉ quyền sử dụng: Bạn đọc có quá 3 lần vi phạm hoặc có hành vi gian lận mượn trả sẽ bị tạm đình chỉ tài khoản từ 1 đến 3 tháng.'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nội quy thư viện · Thư viện Khoa Văn học</title>

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

            <a href="/about">Giới thiệu</a>
            <a href="/rules" class="active" aria-current="page">Nội quy</a>
            <a href="/feedback">Góp ý</a>
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

    <!-- Nội dung chính: Nội quy thư viện -->
    <main class="page">
        <!-- Banner tiêu đề -->
        <section class="hero" style="margin-bottom: 36px; background: #eaf3f9;">
            <div class="hero-content">
                <span class="eyebrow">QUY CHẾ HOẠT ĐỘNG VÀ KHÔNG GIAN HỌC TẬP</span>
                <h1 style="margin: 16px 0;">Nội quy & <span>Quy định Thư viện</span></h1>
                <p style="font-size: 15px; color: #486581; line-height: 1.8;">
                    Để xây dựng môi trường học thuật văn minh, bình đẳng và bảo tồn tốt nhất nguồn tài liệu tri thức quý giá, kính đề nghị toàn thể bạn đọc nắm vững và nghiêm túc thực hiện các nội quy dưới đây.
                </p>
            </div>
            <div style="text-align: right; font-size: 80px; opacity: 0.85;">
                📜
            </div>
        </section>

        <!-- Danh sách các điều khoản nội quy -->
        <div style="display: flex; flex-direction: column; gap: 28px; margin-bottom: 48px;">
            <?php foreach ($ruleCategories as $cat): ?>
                <section style="background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px 32px; box-shadow: 0 4px 12px rgba(36, 68, 92, 0.04);">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
                        <span style="font-size: 26px;"><?= $cat['icon'] ?></span>
                        <h2 style="font-family: 'Literata', serif; font-size: 20px; color: #24445c; margin: 0;"><?= htmlspecialchars($cat['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    </div>
                    <ul style="margin: 0; padding-left: 24px; display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($cat['items'] as $item): ?>
                            <li style="font-size: 14.5px; color: #486581; line-height: 1.75;">
                                <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </div>

        <!-- Khung hỗ trợ & giải đáp thắc mắc -->
        <section style="background: white; border: 1px dashed #3476aa; border-radius: 18px; padding: 28px; text-align: center;">
            <h3 style="font-size: 17px; color: #24445c; margin: 0 0 8px 0;">Bạn có thắc mắc hoặc cần khiếu nại về các quy định trên?</h3>
            <p style="font-size: 14px; color: #718695; margin: 0 0 16px 0;">Cán bộ thư viện luôn tôn trọng và lắng nghe mọi ý kiến đóng góp của bạn đọc để hoàn thiện dịch vụ.</p>
            <div style="display: inline-flex; gap: 12px;">
                <a href="/feedback" class="login-button" style="background: #1e5c8c; color: white; border-color: #1e5c8c; font-weight: 600;">Gửi ý kiến đóng góp</a>
                <a href="/reading-room" class="login-button" style="font-weight: 600;">Đặt chỗ phòng đọc ngay</a>
            </div>
        </section>
    </main>
</body>
</html>
