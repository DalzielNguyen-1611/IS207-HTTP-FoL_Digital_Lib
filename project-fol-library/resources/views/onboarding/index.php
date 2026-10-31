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
$userName = htmlspecialchars($user['ho_ten'] ?? '', ENT_QUOTES, 'UTF-8');
$userEmail = htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8');
$userMssv = htmlspecialchars($user['mssv'] ?? '', ENT_QUOTES, 'UTF-8');
$userPhone = htmlspecialchars($user['so_dien_thoai'] ?? '', ENT_QUOTES, 'UTF-8');
$userDob = htmlspecialchars($user['ngay_sinh'] ?? '2005-01-01', ENT_QUOTES, 'UTF-8');
$userGender = $user['gioi_tinh'] ?? 'Nam';
$userClassId = (int)($user['id_lop'] ?? 1);
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
            max-width: 920px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }

        .onboarding-card {
            background: #ffffff;
            border: 1px solid #e5dfd3;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(36, 68, 92, 0.08), 0 8px 10px -6px rgba(36, 68, 92, 0.04);
            overflow: hidden;
        }

        .onboarding-header {
            background: linear-gradient(135deg, #24445c 0%, #172c3c 100%);
            color: #ffffff;
            padding: 36px 36px 28px;
            position: relative;
        }

        .onboarding-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d4af37, #f3e5ab, #d4af37);
        }

        .onboarding-eyebrow {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #f3e5ab;
            margin-bottom: 8px;
        }

        .onboarding-title {
            font-size: 26px;
            font-family: var(--font-serif, "Literata", serif);
            font-weight: 700;
            margin: 0 0 10px;
            line-height: 1.3;
        }

        .onboarding-desc {
            font-size: 15px;
            color: #d1dbe3;
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

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            border-color: #24445c;
            box-shadow: 0 0 0 3px rgba(36, 68, 92, 0.15);
        }

        .form-control:read-only {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
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
            border-color: #24445c;
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

        .upload-btn-fake {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #24445c;
            color: #ffffff;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 20px;
            border-radius: 6px;
            pointer-events: none;
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

        .preview-size {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .preview-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #047857;
            background: #d1fae5;
            padding: 3px 8px;
            border-radius: 4px;
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
            background: #24445c;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 32px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(36, 68, 92, 0.2);
        }

        .submit-btn:hover {
            background: #1a3244;
            transform: translateY(-1px);
            box-shadow: 0 6px 10px -1px rgba(36, 68, 92, 0.25);
        }

        .logout-link {
            font-size: 14px;
            color: #dc2626;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-link:hover {
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
            <a href="/logout" class="login-button" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:6px 14px;color:#dc2626;border-color:#fecaca;background:#fff5f5;text-decoration:none;" title="Đăng xuất">
                Đăng xuất
            </a>
        </div>
    </header>

    <main class="onboarding-container">
        <div class="onboarding-card">
            <div class="onboarding-header">
                <span class="onboarding-eyebrow">HỒ SƠ BẠN ĐỌC SINH VIÊN</span>
                <h1 class="onboarding-title">Kích hoạt Thẻ Thư viện Khoa Văn học</h1>
                <p class="onboarding-desc">
                    Hoàn tất thông tin cá nhân và tải lên ảnh chụp thẻ sinh viên rõ nét để cán bộ thư viện kích hoạt tài khoản mượn giáo trình và đặt chỗ phòng đọc tự học.
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
                    <?php elseif ($hasCard): ?>
                        <div class="status-banner pending">
                            <div class="status-banner-icon">⏳</div>
                            <div class="status-banner-content">
                                <h4>Hồ sơ của bạn đang chờ cán bộ xét duyệt</h4>
                                <p>Thư viện đã tiếp nhận thông tin và ảnh thẻ sinh viên của bạn (Gửi lúc: <?= htmlspecialchars($submittedAt ?? 'Vừa xong', ENT_QUOTES, 'UTF-8') ?>). Cán bộ sẽ đối chiếu và kích hoạt thẻ trong vòng 24 giờ làm việc.</p>
                                <p style="margin-top:4px;font-size:13px;color:#3b82f6;">Bạn vẫn có thể cập nhật lại thông tin hoặc chụp lại ảnh thẻ mới bên dưới nếu phát hiện sai sót.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="status-banner pending">
                            <div class="status-banner-icon">👋</div>
                            <div class="status-banner-content">
                                <h4>Chào mừng bạn đăng nhập lần đầu!</h4>
                                <p>Vui lòng kiểm tra lại thông tin, chọn Lớp sinh hoạt và tải lên ảnh thẻ sinh viên để thư viện cấp quyền sử dụng.</p>
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

                <?php if (!empty($success)): ?>
                    <div class="status-banner approved">
                        <div class="status-banner-icon">🎉</div>
                        <div class="status-banner-content">
                            <h4>Đã gửi hồ sơ thành công!</h4>
                            <p>Ảnh thẻ sinh viên đã được hệ thống nén tối ưu WebP tự động và chuyển đến hàng đợi xét duyệt của cán bộ thư viện.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Biểu mẫu gửi hồ sơ -->
                <form id="onboardingForm" action="/onboarding/submit" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- Email sinh viên -->
                        <div class="form-group">
                            <label class="form-label">
                                <span>Email tài khoản trường</span>
                                <span style="font-size:11px;color:#64748b;">(Tự động liên kết Google)</span>
                            </label>
                            <input type="email" class="form-control" value="<?= $userEmail ?>" readonly title="Email Google đã liên kết">
                        </div>

                        <!-- Họ và tên -->
                        <div class="form-group">
                            <label class="form-label" for="ho_ten">
                                <span>Họ và tên sinh viên <span class="req">*</span></span>
                            </label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control" value="<?= $userName ?>" required placeholder="Ví dụ: Nguyễn Văn A">
                        </div>

                        <!-- Mã số sinh viên (MSSV) -->
                        <div class="form-group">
                            <label class="form-label" for="mssv">
                                <span>Mã số sinh viên (MSSV) <span class="req">*</span></span>
                            </label>
                            <input type="text" id="mssv" name="mssv" class="form-control" value="<?= $userMssv ?>" required placeholder="Ví dụ: 22010123" style="text-transform:uppercase;">
                        </div>

                        <!-- Lớp và Ngành học -->
                        <div class="form-group">
                            <label class="form-label" for="id_lop">
                                <span>Lớp - Ngành sinh hoạt <span class="req">*</span></span>
                            </label>
                            <select id="id_lop" name="id_lop" class="form-control" required>
                                <option value="">-- Chọn Lớp / Ngành đào tạo --</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= (int)$c['id_lop'] ?>" <?= ($userClassId === (int)$c['id_lop']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['ten_lop'] . ' - ' . $c['ten_nganh'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Số điện thoại -->
                        <div class="form-group">
                            <label class="form-label" for="so_dien_thoai">
                                <span>Số điện thoại liên hệ</span>
                            </label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control" value="<?= $userPhone ?>" placeholder="Ví dụ: 0912345678">
                        </div>

                        <!-- Giới tính -->
                        <div class="form-group">
                            <label class="form-label" for="gioi_tinh">
                                <span>Giới tính</span>
                            </label>
                            <select id="gioi_tinh" name="gioi_tinh" class="form-control">
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
                            <label class="form-label">
                                <span>Ảnh chụp mặt trước Thẻ sinh viên <span class="req">*</span></span>
                                <span style="font-size:11px;color:#059669;font-weight:600;">Tự động nén WebP & tối ưu bảo mật</span>
                            </label>

                            <div class="upload-section" id="dropzone">
                                <input type="file" id="anh_the_sv" name="anh_the_sv" class="file-input-hidden" accept="image/jpeg,image/png,image/webp,image/jpg" <?= $hasCard ? '' : 'required' ?>>
                                <div class="upload-icon">🪪</div>
                                <div class="upload-title">Kéo & thả ảnh thẻ sinh viên vào đây, hoặc nhấp để chọn tệp</div>
                                <div class="upload-subtitle">Hỗ trợ JPG, PNG, WEBP chụp từ điện thoại hoặc máy ảnh (Dung lượng ảnh gốc đến 10MB)</div>
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
                                    <div class="preview-size" id="previewFileSize">Dung lượng: 3.8 MB</div>
                                    <div class="preview-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Sẵn sàng nén WebP (Resize $\le 1000\text{px}$, Quality 80%)
                                    </div>
                                </div>
                            </div>

                            <!-- Ảnh thẻ đã tải lên trước đó nếu có -->
                            <?php if ($hasCard): ?>
                                <div class="existing-card-box">
                                    <div class="preview-img-wrapper">
                                        <img src="<?= htmlspecialchars($user['anh_the_sv'], ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh thẻ đã nộp">
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:4px;">
                                            Ảnh thẻ hiện đang lưu trên hệ thống
                                        </div>
                                        <div style="font-size:12px;color:#64748b;margin-bottom:8px;">
                                            Định dạng WebP tối ưu · Đã nộp lúc <?= htmlspecialchars($submittedAt ?? '', ENT_QUOTES, 'UTF-8') ?>
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
                        <a href="/logout" class="logout-link">
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
        const previewFileSize = document.getElementById('previewFileSize');
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
            if (!file.type.match('image.*')) {
                alert('Vui lòng chỉ chọn tệp hình ảnh (JPG, PNG, WEBP).');
                return;
            }

            previewFileName.textContent = file.name;
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
            previewFileSize.textContent = `Dung lượng gốc: ${sizeInMb} MB · Sẽ được nén WebP tự động`;

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewBox.classList.add('active');
            };
            reader.readAsDataURL(file);
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
