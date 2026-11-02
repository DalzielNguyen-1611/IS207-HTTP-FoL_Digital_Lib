<?php

declare(strict_types=1);

// Đảm bảo dữ liệu người dùng và danh mục lớp được cung cấp đầy đủ
if (!isset($user) || !isset($classes)) {
    $userRepo = new \App\Repositories\UserRepository();
    $userId = (int)(\App\Core\Session::getUserId() ?? 0);
    $user = $userRepo->findById($userId);
    $classes = $userRepo->getClassesList();
}

$status = $user['trang_thai'] ?? 'ChoDuyet';
$hasCard = !empty($user['anh_the_sv']);
$rejectionNote = $user['ghi_chu_duyet'] ?? null;
$submittedAt = $user['ngay_gui_duyet'] ?? null;

// Chuẩn hóa họ tên Google SSO sang định dạng tiếng Việt chuẩn: Họ + Tên đệm + Tên chính
if (!function_exists('fixVietnameseNameOrder')) {
    function fixVietnameseNameOrder(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) < 2) {
            return $name;
        }
        $vietnameseSurnames = [
            'nguyễn', 'nguyen', 'trần', 'tran', 'lê', 'le', 'phạm', 'pham',
            'hoàng', 'hoang', 'huỳnh', 'huynh', 'phan', 'vũ', 'vu', 'võ', 'vo',
            'đặng', 'dang', 'bùi', 'bui', 'đỗ', 'do', 'hồ', 'ho', 'ngô', 'ngo',
            'dương', 'duong', 'lý', 'ly', 'đào', 'dao', 'đoàn', 'doan', 'đinh', 'dinh',
            'lâm', 'lam', 'phùng', 'phung', 'mai', 'trịnh', 'trinh', 'lương', 'luong',
            'tạ', 'ta', 'khổng', 'khong', 'quách', 'quach', 'thái', 'thai', 'vương', 'vuong'
        ];
        $firstWord = mb_strtolower($parts[0], 'UTF-8');
        $secondWord = mb_strtolower($parts[1], 'UTF-8');
        if (!in_array($firstWord, $vietnameseSurnames, true) && in_array($secondWord, $vietnameseSurnames, true)) {
            $firstName = array_shift($parts);
            $parts[] = $firstName;
            return implode(' ', $parts);
        }
        return $name;
    }
}

$rawName = (string)($user['ho_ten'] ?? '');
$userName = htmlspecialchars(fixVietnameseNameOrder($rawName), ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');

// Tự động trích xuất mã số sinh viên từ các chữ số đứng trước '@' trong email trường
$emailPrefix = explode('@', (string)($user['email'] ?? ''))[0];
preg_match('/\d+/', $emailPrefix, $mssvMatches);
$extractedMssv = !empty($mssvMatches[0]) ? $mssvMatches[0] : '';
$currentMssv = (string)($user['mssv'] ?? '');
if (!empty($extractedMssv)) {
    $userMssv = htmlspecialchars($extractedMssv, ENT_QUOTES, 'UTF-8');
} else {
    $userMssv = htmlspecialchars($currentMssv, ENT_QUOTES, 'UTF-8');
}

$userPhone = htmlspecialchars($user['so_dien_thoai'] ?? '', ENT_QUOTES, 'UTF-8');
$userDob = htmlspecialchars($user['ngay_sinh'] ?? '2005-01-01', ENT_QUOTES, 'UTF-8');
$userGender = $user['gioi_tinh'] ?? 'Nam';
$userClassId = (int)($user['id_lop'] ?? 1);
$userClassTen = htmlspecialchars($user['ten_lop'] ?? 'VH2024.1', ENT_QUOTES, 'UTF-8');
$userNganhId = (int)($user['id_nganh'] ?? 1);

$userKhoaId = (int)($user['id_khoa'] ?? 3); // Mặc định Khoa Văn học (ID: 3)

if (!isset($faculties) || empty($faculties)) {
    $faculties = $userRepo->getFacultiesList();
}
if (!isset($majors) || empty($majors)) {
    $majors = $userRepo->getMajorsList();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký hồ sơ & Thẻ sinh viên · Thư viện Khoa Văn học</title>

    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/70years.css">

    <style>
        .onboarding-container {
            max-width: 1180px;
            margin: 40px auto 80px;
            padding: 0 20px;
            box-sizing: border-box;
        }

        @media (min-width: 1228px) {
            .onboarding-container {
                padding: 0;
            }
        }

        .onboarding-card {
            background: #ffffff;
            border: 1px solid #e5dfd3;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(36, 68, 92, 0.08), 0 8px 10px -6px rgba(36, 68, 92, 0.04);
            overflow: hidden;
        }

        .onboarding-header {
            background: linear-gradient(135deg, #fffdf6 0%, #fbf5e6 100%);
            color: #24445c;
            padding: 36px 36px 28px;
            position: relative;
            border-bottom: 1px solid #e9dfbf;
        }

        .onboarding-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d4af37, #efd097, #d4af37);
        }

        .onboarding-eyebrow {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #1e5c8c;
            margin-bottom: 8px;
        }

        .onboarding-title {
            font-size: 26px;
            font-family: var(--font-serif, "Literata", serif);
            font-weight: 700;
            color: #24445c;
            margin: 0 0 10px;
            line-height: 1.3;
        }

        .onboarding-desc {
            font-size: 14.5px;
            color: #617584;
            line-height: 1.6;
            margin: 0;
        }

        .onboarding-body {
            padding: 36px;
            background: #fffdf9;
        }

        /* Status Banners */
        .status-banner {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 18px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.6;
        }

        .status-banner.approved {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .status-banner.pending {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .status-banner.welcome {
            background: rgba(242, 178, 27, 0.1);
            border: 1px solid rgba(242, 178, 27, 0.3);
            color: #78350f;
        }

        .status-banner.rejected {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .status-banner.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .status-banner-icon {
            font-size: 24px;
            line-height: 1;
            flex-shrink: 0;
        }

        .status-banner-content h4 {
            margin: 0 0 4px;
            font-size: 15px;
            font-weight: 700;
        }

        .status-banner-content p {
            margin: 0;
        }

        /* Giao diện trạng thái tối giản (Inline Status) */
        .inline-status-pending {
            margin-bottom: 24px;
            padding: 0;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .inline-status-header {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 15px;
            line-height: 1.4;
        }

        .inline-status-icon {
            color: #d97706; /* Tông màu amber ấm biểu thị trạng thái chờ duyệt */
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
        }

        .inline-status-title {
            font-weight: 700;
            color: #0f172a;
        }

        .inline-status-sep {
            color: #94a3b8;
            font-weight: 400;
            user-select: none;
        }

        .inline-status-time {
            color: #64748b;
            font-size: 14px;
            font-weight: 400;
        }

        .inline-status-desc {
            margin-top: 6px;
            padding-left: 26px; /* Căn chỉnh thẳng hàng với tiêu đề sau icon 18px + 8px gap */
            font-size: 13.5px;
            line-height: 1.55;
            color: #64748b;
        }

        .inline-status-desc p {
            margin: 0;
        }

        @media (max-width: 640px) {
            .inline-status-desc {
                padding-left: 0;
            }
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-full {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-label .req {
            color: #dc2626;
            font-weight: 700;
            margin-left: 4px;
        }

        .form-control {
            height: 44px;
            padding: 8px 14px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #1e5c8c;
            box-shadow: 0 0 0 3px rgba(30, 92, 140, 0.15);
        }

        input.form-control:read-only,
        textarea.form-control:read-only {
            background-color: #fafaf9 !important;
            color: #78716c !important;
            cursor: not-allowed !important;
        }

        select.form-control {
            background-color: #ffffff !important;
            color: #0f172a !important;
            cursor: pointer !important;
        }

        select.form-control option {
            background-color: #ffffff;
            color: #0f172a;
        }

        /* Tailwind utility classes mapping */
        .bg-stone-50 {
            background-color: #fafaf9 !important;
        }

        .cursor-not-allowed {
            cursor: not-allowed !important;
        }

        .text-stone-500 {
            color: #78716c !important;
        }

        .text-stone-500:hover, .hover\:text-stone-800:hover {
            color: #292524 !important;
        }

        .hover\:underline:hover {
            text-decoration: underline !important;
        }

        .text-stone-400 {
            color: #a8a29e !important;
        }

        /* Upload Area */
        .upload-section {
            margin-top: 10px;
            padding: 24px;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            text-align: center;
            transition: all 0.2s ease;
            position: relative;
        }

        .upload-section.dragover {
            border-color: #1e5c8c;
            background: #f0f7ff;
        }

        .upload-icon {
            font-size: 40px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .upload-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .upload-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 14px;
        }

        .file-input-hidden {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-section:hover {
            border-color: #dec383;
            background: #fffdf9;
        }

        .upload-btn-fake {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #efd097;
            border: 1px solid #dec383;
            color: #24445c;
            font-weight: 600;
            font-size: 13.5px;
            padding: 10px 22px;
            border-radius: 6px;
            pointer-events: none;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(36, 68, 92, 0.08);
        }

        .upload-section:hover .upload-btn-fake {
            background: #e5c37f;
            border-color: #d1b16e;
            color: #1a3346;
        }

        /* Preview Area */
        .card-preview-box {
            margin-top: 20px;
            display: none;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            text-align: left;
        }

        .card-preview-box.active {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .preview-img-wrapper {
            width: 180px;
            height: 120px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            background: #000000;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .preview-meta {
            flex: 1;
        }

        .preview-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .btn-remove-photo {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 5px 12px;
            font-size: 12.5px;
            font-weight: 500;
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-remove-photo:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        /* Existing card */
        .existing-card-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 14px;
        }

        /* Actions */
        .onboarding-actions {
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #e5dfd3;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #efd097;
            border: 1px solid #dec383;
            color: #24445c;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 3px 6px rgba(36, 68, 92, 0.08);
        }

        .submit-btn:hover {
            background: #e5c37f;
            border-color: #d1b16e;
            color: #1a3346;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(36, 68, 92, 0.12);
        }

        .submit-btn:active {
            transform: scale(0.99);
        }

        .logout-link {
            font-size: 14px;
            color: #78716c;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s ease;
        }

        .logout-link:hover {
            color: #292524;
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .card-preview-box.active, .existing-card-box {
                flex-direction: column;
                align-items: flex-start;
            }
            .preview-img-wrapper {
                width: 100%;
                height: 180px;
            }
            .onboarding-actions {
                flex-direction: column-reverse;
                gap: 16px;
                align-items: stretch;
            }
            .submit-btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <a class="brand" href="/" title="Về trang chủ Thư viện">
            <img class="brand-logo" src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <span class="brand-label">USSH · VNU-HANOI</span>
                <strong>Thư viện Khoa Văn học</strong>
            </span>
        </a>

        <nav class="navigation" aria-label="Điều hướng chính">
            <a href="/">Trang chủ</a>
            <a href="/books">Tra cứu sách</a>
            <a href="/rules">Nội quy</a>
        </nav>

        <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
            <span style="font-size:13px;font-weight:600;color:#334155;"><?= $userEmail ?></span>
            <a href="/logout" class="login-button text-stone-500 hover:text-stone-800 hover:underline" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:6px 14px;color:#78716c;border-color:#e7e5e4;background:#fafaf9;text-decoration:none;" title="Đăng xuất">
                Đăng xuất
            </a>
        </div>
    </header>

    <main class="onboarding-container">
        <div class="onboarding-card">
            <div class="onboarding-header">
                <h1 class="onboarding-title">Chào mừng bạn đến với Thư viện!</h1>
                <p class="onboarding-desc">
                    Vui lòng hoàn thiện hồ sơ bên dưới để sử dụng dịch vụ mượn tài liệu và phòng tự học.
                </p>
            </div>

            <div class="onboarding-body">
                <!-- Thông báo trạng thái -->
                <?php if ($status === 'HoatDong'): ?>
                    <div class="status-banner approved">
                        <div class="status-banner-icon">✅</div>
                        <div class="status-banner-content">
                            <h4>Thẻ sinh viên đã được phê duyệt!</h4>
                            <p>Tài khoản của bạn đã ở trạng thái <strong>Hoạt động</strong>. Bạn có toàn quyền tra cứu, mượn sách tại quầy và đăng ký ca phòng đọc.</p>
                            <p style="margin-top:8px;">
                                <a href="/profile" style="color:#047857;font-weight:700;text-decoration:underline;">Đi đến Trang cá nhân của bạn &rarr;</a>
                            </p>
                        </div>
                    </div>
                <?php elseif ($status === 'ChoDuyet'): ?>
                    <?php if (!empty($rejectionNote)): ?>
                        <div class="status-banner rejected">
                            <div class="status-banner-icon">⚠️</div>
                            <div class="status-banner-content">
                                <h4>Yêu cầu chụp lại ảnh thẻ sinh viên</h4>
                                <p><strong>Lý do từ cán bộ thư viện:</strong> <?= htmlspecialchars($rejectionNote, ENT_QUOTES, 'UTF-8') ?></p>
                                <p style="margin-top:6px;">Vui lòng đặt thẻ trên mặt phẳng đủ sáng, chụp thẳng góc và tải lại ảnh mới bên dưới để cán bộ kiểm tra lại.</p>
                            </div>
                        </div>
                    <?php elseif ($hasCard || !empty($success)): ?>
                        <div class="inline-status-pending">
                            <div class="inline-status-header">
                                <span class="inline-status-icon" aria-hidden="true">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16.5 12"></polyline>
                                    </svg>
                                </span>
                                <strong class="inline-status-title">Hồ sơ đang chờ duyệt</strong>
                                <span class="inline-status-sep">·</span>
                                <span class="inline-status-time">Đã gửi lúc <?= htmlspecialchars(date('H:i (d/m/Y)', strtotime($submittedAt ?: 'now')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="inline-status-desc">
                                <p>Thư viện dự kiến xử lý hồ sơ trong vòng 24 giờ làm việc.</p>
                                <p style="margin-top:2px;">Bạn có thể chỉnh sửa thông tin bên dưới nếu cần.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="status-banner error">
                        <div class="status-banner-icon">❌</div>
                        <div class="status-banner-content">
                            <h4>Đã xảy ra lỗi</h4>
                            <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Biểu mẫu gửi hồ sơ -->
                <form id="onboardingForm" action="/onboarding/submit" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\Core\Session::getCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-grid">
                        <!-- Email sinh viên -->
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <span>Email tài khoản trường</span>
                            </label>
                            <input type="email" id="email" class="form-control bg-stone-50 cursor-not-allowed" value="<?= $userEmail ?>" readonly title="Email Google đã liên kết">
                        </div>

                        <!-- Họ và tên -->
                        <div class="form-group">
                            <label class="form-label" for="ho_ten">
                                <span>Họ và tên sinh viên <span class="req">*</span></span>
                            </label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control" value="<?= $userName ?>" required placeholder="Ví dụ: Nguyễn Văn A">
                        </div>

                        <!-- Mã số sinh viên -->
                        <div class="form-group">
                            <label class="form-label" for="mssv">
                                <span>Mã số sinh viên <span class="req">*</span></span>
                            </label>
                            <input type="text" id="mssv" name="mssv" class="form-control bg-stone-50 cursor-not-allowed" value="<?= $userMssv ?>" readonly required placeholder="Ví dụ: 24520500" style="text-transform:uppercase;">
                        </div>

                        <!-- Hàng 2: Tách thông tin học tập thành 3 phần: Khoa (dropdown), Ngành (dropdown), Lớp (tự điền) -->
                        <!-- Khoa / Viện đào tạo -->
                        <div class="form-group">
                            <label class="form-label" for="id_khoa">
                                <span>Khoa / Viện đào tạo <span class="req">*</span></span>
                            </label>
                            <select id="id_khoa" name="id_khoa" class="form-control" required>
                                <?php foreach ($faculties as $fac): ?>
                                    <option value="<?= (int)$fac['id_khoa'] ?>" <?= ($userKhoaId === (int)$fac['id_khoa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fac['ten_khoa'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Chuyên ngành học -->
                        <div class="form-group">
                            <label class="form-label" for="id_nganh">
                                <span>Chuyên ngành đào tạo <span class="req">*</span></span>
                            </label>
                            <select id="id_nganh" name="id_nganh" class="form-control" required>
                                <?php 
                                foreach ($majors as $m): 
                                    if ((int)($m['id_khoa'] ?? 0) === $userKhoaId):
                                ?>
                                    <option value="<?= (int)$m['id_nganh'] ?>" <?= ($userNganhId === (int)$m['id_nganh']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['ten_nganh'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </select>
                        </div>

                        <!-- Lớp sinh hoạt (Tự điền) -->
                        <div class="form-group">
                            <label class="form-label" for="ten_lop">
                                <span>Lớp sinh hoạt <span class="req">*</span></span>
                            </label>
                            <input type="text" id="ten_lop" name="ten_lop" class="form-control" value="<?= $userClassTen ?>" required placeholder="Ví dụ: VH2024.1 hoặc K18-VH">
                        </div>

                        <!-- Hàng 3: Số điện thoại liên hệ (bắt buộc), Giới tính, Ngày sinh -->
                        <!-- Số điện thoại liên hệ -->
                        <div class="form-group">
                            <label class="form-label" for="so_dien_thoai">
                                <span>Số điện thoại liên hệ <span class="req">*</span></span>
                            </label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control" value="<?= $userPhone ?>" required placeholder="Ví dụ: 0912345678" pattern="[0-9]{9,12}">
                        </div>

                        <!-- Giới tính -->
                        <div class="form-group">
                            <label class="form-label" for="gioi_tinh">
                                <span>Giới tính <span class="req">*</span></span>
                            </label>
                            <select id="gioi_tinh" name="gioi_tinh" class="form-control" required>
                                <option value="Nam" <?= $userGender === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                <option value="Nu" <?= $userGender === 'Nu' ? 'selected' : '' ?>>Nữ</option>
                                <option value="Khac" <?= $userGender === 'Khac' ? 'selected' : '' ?>>Khác</option>
                            </select>
                        </div>

                        <!-- Ngày sinh -->
                        <div class="form-group">
                            <label class="form-label" for="ngay_sinh">
                                <span>Ngày sinh <span class="req">*</span></span>
                            </label>
                            <input type="date" id="ngay_sinh" name="ngay_sinh" class="form-control" value="<?= $userDob ?>" required>
                        </div>

                        <!-- Phần Upload ảnh thẻ SV -->
                        <div class="form-group form-full">
                            <label class="form-label" for="anh_the_sv">
                                <span>Ảnh chụp mặt trước Thẻ sinh viên <span class="req">*</span></span>
                            </label>

                            <div class="upload-section" id="dropzone">
                                <input type="file" id="anh_the_sv" name="anh_the_sv" class="file-input-hidden" accept="image/jpeg,image/png,image/webp,image/jpg" <?= $hasCard ? '' : 'required' ?>>
                                <div class="upload-icon">🪪</div>
                                <div class="upload-title">Kéo thả hoặc bấm vào đây để tải lên ảnh thẻ sinh viên</div>
                                <div class="upload-subtitle">Hỗ trợ JPG, PNG, WEBP (Tối đa 10MB).</div>
                                <div class="upload-hint text-stone-400" style="font-size:12px;color:#a8a29e;margin-top:-6px;margin-bottom:14px;">
                                    (Hệ thống sẽ tự động nén và tối ưu hóa ảnh)
                                </div>
                                <span class="upload-btn-fake">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"></path></svg>
                                    Chọn ảnh từ thiết bị
                                </span>
                            </div>

                            <!-- Preview ảnh tức thì trước khi upload -->
                            <div class="card-preview-box" id="previewBox">
                                <div class="preview-img-wrapper">
                                    <img id="previewImg" src="" alt="Xem trước ảnh thẻ">
                                </div>
                                <div class="preview-meta">
                                    <div class="preview-name" id="previewFileName">the_sinh_vien.jpg</div>
                                    <button type="button" class="btn-remove-photo" id="btnRemovePhoto" title="Xóa ảnh và chọn lại">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                        <span>Xóa ảnh</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Ảnh thẻ đã tải lên trước đó nếu có -->
                            <?php if ($hasCard): ?>
                                <?php
                                    $savedCardUrl = !empty($user['anh_the_sv']) 
                                        ? (str_starts_with($user['anh_the_sv'], '/') ? $user['anh_the_sv'] : '/uploads/student_cards/' . $user['anh_the_sv']) 
                                        : '';
                                ?>
                                <div class="existing-card-box">
                                    <div class="preview-img-wrapper">
                                        <img src="<?= htmlspecialchars($savedCardUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh thẻ đã nộp">
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px;">
                                            Ảnh thẻ hiện đang lưu trên hệ thống
                                        </div>
                                        <div style="font-size:12px;color:#64748b;margin-bottom:8px;">
                                            Đã nộp lúc <?= htmlspecialchars($submittedAt ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <span style="font-size:11px;background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-weight:600;">
                                            Nếu muốn thay đổi, bạn chỉ cần chọn ảnh mới bên trên
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Nút thao tác -->
                    <div class="onboarding-actions">
                        <a href="/logout" class="logout-link text-stone-500 hover:text-stone-800 hover:underline">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                            Đăng xuất khỏi tài khoản này
                        </a>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><?= $hasCard ? 'Cập nhật hồ sơ & Gửi lại thẻ' : 'Gửi hồ sơ duyệt thẻ sinh viên' ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer" style="margin-top:60px;background:#ffffff;border-top:1px solid #e5dfd3;padding:30px 20px;text-align:center;font-size:13px;color:#64748b;">
        <p style="margin:0 0 6px;"><strong>Thư viện Khoa Văn học · Trường Đại học Khoa học Xã hội và Nhân văn</strong></p>
        <p style="margin:0;">Địa chỉ: Nhà B, 336 Nguyễn Trãi, Thanh Xuân, Hà Nội · Hỗ trợ kỹ thuật: 024 3858 3960</p>
    </footer>

    <script>
        // Xử lý kéo thả & xem trước ảnh tức thì
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('anh_the_sv');
        const previewBox = document.getElementById('previewBox');
        const previewImg = document.getElementById('previewImg');
        const previewFileName = document.getElementById('previewFileName');
        const btnRemovePhoto = document.getElementById('btnRemovePhoto');
        const form = document.getElementById('onboardingForm');
        const submitBtn = document.getElementById('submitBtn');

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                handleFileSelect(this.files[0]);
            }
        });

        function handleFileSelect(file) {
            if (!file) return;

            if (file.size === 0) {
                alert('Tệp bạn vừa chọn không có dữ liệu (0 bytes). Vui lòng chọn một file ảnh thực tế.');
                resetFileInput();
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Dung lượng ảnh vượt quá 10MB. Vui lòng chọn một file ảnh có dung lượng nhỏ hơn.');
                resetFileInput();
                return;
            }

            if (!file.type.match('image.*')) {
                alert('Vui lòng chỉ chọn tệp hình ảnh (JPG, PNG, WEBP).');
                resetFileInput();
                return;
            }

            previewFileName.textContent = file.name;

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.onload = function() {
                    previewBox.classList.add('active');
                };
                previewImg.onerror = function() {
                    alert('Không thể hiển thị tệp này dưới dạng hình ảnh. Vui lòng chọn một file ảnh JPG, PNG hoặc WEBP hợp lệ.');
                    resetFileInput();
                };
                previewImg.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function resetFileInput() {
            fileInput.value = '';
            previewImg.src = '';
            previewFileName.textContent = '';
            previewBox.classList.remove('active');
        }

        if (btnRemovePhoto) {
            btnRemovePhoto.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                resetFileInput();
            });
        }

        // Xử lý liên kết động Khoa/Viện -> Chuyên ngành đào tạo
        const allMajors = <?= json_encode($majors, JSON_UNESCAPED_UNICODE) ?>;
        const khoaSelect = document.getElementById('id_khoa');
        const nganhSelect = document.getElementById('id_nganh');
        let initialNganhId = <?= (int)$userNganhId ?>;

        function updateMajorsByFaculty(facultyId, preserveSaved = false) {
            const fid = parseInt(facultyId, 10);
            const filteredMajors = allMajors.filter(m => parseInt(m.id_khoa, 10) === fid);

            nganhSelect.innerHTML = '';

            if (filteredMajors.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Chưa có ngành --';
                nganhSelect.appendChild(opt);
                return;
            }

            let isSelected = false;
            filteredMajors.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id_nganh;
                opt.textContent = m.ten_nganh;
                if (preserveSaved && parseInt(m.id_nganh, 10) === initialNganhId) {
                    opt.selected = true;
                    isSelected = true;
                }
                nganhSelect.appendChild(opt);
            });

            // Nếu ngành đang chọn không thuộc Khoa mới, tự động chọn ngành đầu tiên của Khoa đó
            if (!isSelected && filteredMajors.length > 0) {
                nganhSelect.options[0].selected = true;
            }
        }

        if (khoaSelect && nganhSelect) {
            khoaSelect.addEventListener('change', function() {
                updateMajorsByFaculty(this.value, false);
            });
            // Đồng bộ danh sách ngành ban đầu theo Khoa đang chọn
            updateMajorsByFaculty(khoaSelect.value, true);
        }

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg style="animation:spin 1s linear infinite;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="0.8"></path>
                </svg>
                <span>Đang nén ảnh & Gửi hồ sơ...</span>
            `;
        });
    </script>
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</body>
</html>
