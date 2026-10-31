<?php

declare(strict_types=1);

if (!isset($pendingStudents)) {
    $userRepo = new \App\Repositories\UserRepository();
    $pendingStudents = $userRepo->getPendingStudents();
    $message = isset($_GET['msg']) ? trim((string)$_GET['msg']) : null;
    $error = isset($_GET['error']) ? trim((string)$_GET['error']) : null;
}

$pendingCount = count($pendingStudents);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duyệt thẻ sinh viên · Thư viện Khoa Văn học</title>

    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/70years.css">

    <style>
        .student-table-wrapper {
            background: #ffffff;
            border: 1px solid #e5dfd3;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(36, 68, 92, 0.05);
            margin-top: 20px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        .student-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 14px 16px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
        }

        .student-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .student-table tr:hover {
            background: #fbfdfe;
        }

        .student-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #cbd5e1;
            flex-shrink: 0;
        }

        .student-name {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .student-mssv {
            font-family: monospace;
            font-size: 12px;
            font-weight: 600;
            color: #24445c;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .card-thumb-container {
            width: 90px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            position: relative;
            background: #000000;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .card-thumb-container:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .card-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-thumb-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: rgba(0, 0, 0, 0.65);
            color: #ffffff;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 3px;
        }

        .action-btns {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-approve {
            background: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease;
        }

        .btn-approve:hover {
            background: #047857;
        }

        .btn-reject {
            background: #ffffff;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .btn-reject:hover {
            background: #fef2f2;
            border-color: #f87171;
        }

        /* Modal Phóng to ảnh thẻ */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-container {
            background: #ffffff;
            border-radius: 12px;
            max-width: 760px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            animation: modalFadeIn 0.2s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            padding: 18px 24px;
            background: #24445c;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
        }

        .modal-close {
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            opacity: 0.8;
        }

        .modal-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 24px;
            text-align: center;
            background: #f8fafc;
        }

        .modal-card-img {
            max-width: 100%;
            max-height: 480px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            object-fit: contain;
            background: #ffffff;
        }

        .modal-meta {
            margin-top: 16px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-around;
            text-align: left;
            font-size: 13px;
        }

        .modal-footer {
            padding: 16px 24px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #e5dfd3;
            border-radius: 8px;
            margin-top: 20px;
        }

        .empty-icon {
            font-size: 56px;
            margin-bottom: 12px;
        }

        .empty-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 6px;
        }

        .empty-desc {
            font-size: 14px;
            color: #64748b;
            margin: 0;
        }

        .alert-toast {
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-toast.success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-toast.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
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
            <a href="/librarian" class="active" aria-current="page">Cán bộ</a>
            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <a href="/admin/settings">Quản trị</a>
            <?php endif; ?>
        </nav>

        <?php
            $currentUser = \App\Core\Session::getAuthUser();
            $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Cán bộ', ENT_QUOTES, 'UTF-8');
        ?>
        <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
            <a href="/librarian" class="login-button" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;padding:8px 16px;text-decoration:none;">
                <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                <span><?= $userName ?></span>
            </a>
            <a href="/logout" class="login-button" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:8px 14px;color:#dc2626;border-color:#fecaca;background:#fff5f5;text-decoration:none;" title="Đăng xuất">
                Đăng xuất
            </a>
        </div>
    </header>

    <main class="staff-layout">
        <!-- Sidebar cán bộ -->
        <nav class="staff-sidebar" aria-label="Điều hướng cán bộ">
            <span class="staff-menu-label">NGHIỆP VỤ THƯ VIỆN</span>
            <a href="/librarian">Tổng quan</a>
            <a href="/librarian/students" aria-current="page" style="display:flex;align-items:center;justify-content:space-between;">
                <span>Duyệt thẻ sinh viên</span>
                <?php if ($pendingCount > 0): ?>
                    <span style="background:#dc2626;color:#ffffff;font-size:11px;font-weight:700;padding:2px 7px;border-radius:10px;"><?= $pendingCount ?></span>
                <?php endif; ?>
            </a>
            <a href="/librarian/books">Quản lý sách</a>
            <a href="/librarian/counter">Mượn / trả</a>
            <a href="/librarian/rooms">Phòng đọc</a>
            <a href="/librarian/reports">Báo cáo</a>

            <?php if (\App\Core\Session::getRoleCode() === 'ADMIN'): ?>
                <span class="staff-menu-label" style="margin-top:16px;">CHUYỂN VAI TRÒ TEST</span>
                <a href="/admin/settings">Về trang quản trị</a>
                <a href="/profile">Về trang cá nhân (SV)</a>
            <?php endif; ?>
        </nav>

        <!-- Nội dung chính -->
        <div class="staff-content">
            <section class="staff-intro">
                <span class="staff-eyebrow">XÉT DUYỆT BẠN ĐỌC</span>
                <h1>Duyệt thẻ sinh viên</h1>
                <p>
                    Kiểm tra và đối chiếu ảnh chụp thẻ sinh viên do người dùng tải lên để kích hoạt quyền mượn tài liệu và đặt phòng đọc.
                </p>
            </section>

            <!-- Thông báo flash -->
            <?php if (!empty($message)): ?>
                <div class="alert-toast success">
                    <span>✅</span>
                    <span><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert-toast error">
                    <span>❌</span>
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if ($pendingCount === 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">🎉</div>
                    <h3 class="empty-title">Không có thẻ sinh viên nào đang chờ duyệt</h3>
                    <p class="empty-desc">Toàn bộ hồ sơ sinh viên đã được cán bộ xử lý hoặc chưa có lượt đăng ký mới nào.</p>
                </div>
            <?php else: ?>
                <div class="student-table-wrapper">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>Sinh viên</th>
                                <th>Thông tin liên hệ</th>
                                <th>Lớp & Chuyên ngành</th>
                                <th>Thời gian nộp</th>
                                <th>Ảnh thẻ SV</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingStudents as $s): ?>
                                <?php
                                    $sId = (int)$s['id_tai_khoan'];
                                    $sName = htmlspecialchars($s['ho_ten'] ?? 'Chưa rõ', ENT_QUOTES, 'UTF-8');
                                    $sMssv = htmlspecialchars($s['mssv'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sEmail = htmlspecialchars($s['email'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sPhone = htmlspecialchars($s['so_dien_thoai'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sLop = htmlspecialchars($s['ten_lop'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sNganh = htmlspecialchars($s['ten_nganh'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sTime = htmlspecialchars($s['ngay_gui_duyet'] ?? $s['created_at'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $sAvatar = !empty($s['anh_dai_dien']) ? htmlspecialchars($s['anh_dai_dien'], ENT_QUOTES, 'UTF-8') : '/assets/images/logo.png';
                                    $sCard = htmlspecialchars($s['anh_the_sv'] ?? '', ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td>
                                        <div class="student-info-cell">
                                            <img src="<?= $sAvatar ?>" alt="Avatar" class="student-avatar" onerror="this.src='/assets/images/logo.png'">
                                            <div>
                                                <div class="student-name"><?= $sName ?></div>
                                                <span class="student-mssv"><?= $sMssv ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color:#0f172a;font-weight:500;"><?= $sEmail ?></div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?= $sPhone ?: 'Chưa cập nhật SĐT' ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#1e293b;"><?= $sLop ?></div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?= $sNganh ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size:13px;color:#475569;"><?= $sTime ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($sCard)): ?>
                                            <div class="card-thumb-container" onclick="openCardModal('<?= $sCard ?>', '<?= $sName ?>', '<?= $sMssv ?>', '<?= $sLop ?>', <?= $sId ?>)" title="Nhấp để phóng to ảnh thẻ">
                                                <img src="<?= $sCard ?>" alt="Ảnh thẻ" class="card-thumb-img">
                                                <span class="card-thumb-badge">Phóng to</span>
                                            </div>
                                        <?php else: ?>
                                            <span style="font-size:12px;color:#94a3b8;font-style:italic;">Chưa có ảnh</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <form action="/librarian/students/approve" method="POST" style="margin:0;">
                                                <input type="hidden" name="user_id" value="<?= $sId ?>">
                                                <button type="submit" class="btn-approve" onclick="return confirm('Xác nhận phê duyệt thẻ cho sinh viên <?= $sName ?> (MSSV: <?= $sMssv ?>)?')">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    Duyệt
                                                </button>
                                            </form>

                                            <button type="button" class="btn-reject" onclick="promptReject(<?= $sId ?>, '<?= $sName ?>')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                Yêu cầu chụp lại
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal Phóng to ảnh thẻ SV -->
    <div class="modal-overlay" id="cardModal" onclick="closeCardModal(event)">
        <div class="modal-container" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Chi tiết thẻ sinh viên</h3>
                <button type="button" class="modal-close" onclick="closeCardModal()">&times;</button>
            </div>
            <div class="modal-body">
                <img id="modalImg" src="" alt="Ảnh thẻ sinh viên phóng to" class="modal-card-img">
                <div class="modal-meta">
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;">Họ và tên</div>
                        <strong id="modalName" style="color:#0f172a;font-size:14px;">-</strong>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;">Mã sinh viên</div>
                        <strong id="modalMssv" style="color:#24445c;font-size:14px;font-family:monospace;">-</strong>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#64748b;text-transform:uppercase;">Lớp sinh hoạt</div>
                        <strong id="modalLop" style="color:#0f172a;font-size:14px;">-</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-reject" id="modalRejectBtn" onclick="modalPromptReject()">
                    Yêu cầu chụp lại
                </button>
                <form id="modalApproveForm" action="/librarian/students/approve" method="POST" style="margin:0;">
                    <input type="hidden" name="user_id" id="modalUserId" value="0">
                    <button type="submit" class="btn-approve">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Phê duyệt thẻ này
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Form ẩn để gửi yêu cầu chụp lại -->
    <form id="rejectForm" action="/librarian/students/reject" method="POST" style="display:none;">
        <input type="hidden" name="user_id" id="rejectUserId" value="0">
        <input type="hidden" name="reason" id="rejectReason" value="">
    </form>

    <script>
        let currentModalUserId = 0;
        let currentModalUserName = '';

        function openCardModal(cardUrl, name, mssv, lop, userId) {
            currentModalUserId = userId;
            currentModalUserName = name;

            document.getElementById('modalImg').src = cardUrl;
            document.getElementById('modalTitle').textContent = `Thẻ SV · ${name} (${mssv})`;
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalMssv').textContent = mssv;
            document.getElementById('modalLop').textContent = lop;
            document.getElementById('modalUserId').value = userId;

            document.getElementById('cardModal').classList.add('active');
        }

        function closeCardModal(e) {
            if (!e || e.target.id === 'cardModal' || e.target.classList.contains('modal-close')) {
                document.getElementById('cardModal').classList.remove('active');
            }
        }

        function promptReject(userId, name) {
            const reason = prompt(`Nhập lý do yêu cầu sinh viên ${name} chụp lại thẻ:\n(Ví dụ: Ảnh bị mờ, lóa sáng, thiếu góc thẻ)`, 'Ảnh thẻ bị mờ hoặc lóa sáng, vui lòng chụp lại rõ thông tin.');
            if (reason !== null && reason.trim() !== '') {
                document.getElementById('rejectUserId').value = userId;
                document.getElementById('rejectReason').value = reason.trim();
                document.getElementById('rejectForm').submit();
            }
        }

        function modalPromptReject() {
            promptReject(currentModalUserId, currentModalUserName);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCardModal();
            }
        });
    </script>
</body>
</html>
