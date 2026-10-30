<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\AuditLogRepository;

$auditRepo = new AuditLogRepository();
$logs = $auditRepo->getFilteredLogs(100);

// Bảng ánh xạ nhãn hành động cho thân thiện
$knownActionLabels = [
    'MuonSach' => 'Mượn sách',
    'TraSach' => 'Trả sách',
    'TaoDauSach' => 'Thêm đầu sách',
    'SuaDauSach' => 'Cập nhật sách',
    'XoaDauSach' => 'Xóa đầu sách',
    'DangNhap' => 'Đăng nhập',
    'DangXuat' => 'Đăng xuất',
    'DatCho' => 'Đặt chỗ',
    'HuyDatCho' => 'Hủy đặt chỗ',
    'DiemDanh' => 'Điểm danh',
    'create-book' => 'Thêm sách',
    'update-book' => 'Cập nhật sách',
    'borrow' => 'Xác nhận mượn',
    'return' => 'Xác nhận trả',
];

$actions = [];
foreach ($logs as $logItem) {
    $act = (string)($logItem['action'] ?? '');
    if ($act !== '' && !isset($actions[$act])) {
        $actions[$act] = $knownActionLabels[$act] ?? $act;
    }
}
if (empty($actions)) {
    $actions = [
        'borrow' => 'Xác nhận mượn',
        'return' => 'Xác nhận trả',
        'create-book' => 'Thêm sách',
        'update-book' => 'Cập nhật sách',
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nhật ký hệ thống · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-audit.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header: Nhận diện thư viện & điều hướng -->
    <header class="header">
        <a class="brand" href="/" title="Về trang chủ Thư viện">
            <img
                class="brand-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >

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
                <a href="/contact">Liên hệ</a>
            <?php endif; ?>

            <?php if ($isAuthenticated): ?>
                <?php if ($navRole === 'ADMIN'): ?>
                    <a href="/librarian">Cán bộ</a>
                    <a href="/admin/settings" class="active" aria-current="page">Quản trị</a>
                <?php elseif ($navRole === 'LIBRARIAN'): ?>
                    <a href="/librarian">Cán bộ</a>
                <?php else: ?>
                    <a href="/profile">Cá nhân</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực người dùng / Đăng xuất -->
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
            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
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
            <a class="login-button" href="/login">
                Đăng nhập
            </a>
        <?php endif; ?>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng quản trị -->
        <nav class="staff-sidebar" aria-label="Điều hướng quản trị">
            <span class="staff-menu-label">QUẢN TRỊ HỆ THỐNG</span>

            <a href="/admin/users">Người dùng</a>
            <a href="/admin/audit" aria-current="page">Nhật ký</a>
            <a href="/admin/settings">Cấu hình</a>
            <a href="/librarian">Giao diện cán bộ</a>
            <a href="/profile">Giao diện cá nhân (SV)</a>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề nhật ký -->
            <section class="staff-intro">
                <span class="staff-eyebrow">LỊCH SỬ THAO TÁC</span>
                <h1>Nhật ký hệ thống</h1>
                <p>Theo dõi lịch sử thao tác của người dùng và cán bộ thư viện trên hệ thống.</p>
            </section>

            <section class="staff-panel" aria-labelledby="audit-title">
                <h2 id="audit-title">Lịch sử hoạt động</h2>

                <!-- Tìm theo cán bộ, mã đối tượng hoặc nội dung -->
                <form class="staff-search" id="audit-search-form">
                    <input
                        id="audit-keyword"
                        type="search"
                        aria-label="Tìm cán bộ, mã sách, mã phiếu hoặc nội dung"
                        placeholder="Tìm cán bộ, mã sách, mã phiếu hoặc nội dung…"
                    >
                    <button type="submit">Tìm kiếm</button>
                </form>

                <!-- Lọc kết hợp ngày và loại thao tác -->
                <div class="audit-filters">
                    <div>
                        <label for="audit-date">Ngày thao tác</label>
                        <input id="audit-date" type="date">
                    </div>

                    <div>
                        <label for="audit-action">Loại thao tác</label>
                        <select id="audit-action">
                            <option value="">Tất cả thao tác</option>

                            <?php foreach ($actions as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button
                        class="staff-action-button"
                        id="audit-reset"
                        type="button"
                    >
                        Xóa bộ lọc
                    </button>
                </div>

                <!-- Kết quả và trạng thái rỗng -->
                <p class="staff-note" id="audit-count" role="status"></p>

                <p class="staff-note" id="audit-empty" hidden>
                    Không có nhật ký phù hợp.
                </p>

                <!-- Bảng lịch sử hoạt động -->
                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Bảng nhật ký hệ thống"
                >
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th scope="col">Thời gian</th>
                                <th scope="col">Cán bộ / Người dùng</th>
                                <th scope="col">Hành động</th>
                                <th scope="col">Đối tượng</th>
                                <th scope="col">Chi tiết</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php $actionLabel = $actions[$log['action']] ?? $log['action']; ?>
                                <!-- Bản ghi nhật ký hệ thống -->
                                <tr
                                    data-audit-row
                                    data-date="<?= htmlspecialchars($log['date'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-action="<?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= htmlspecialchars(
                                        $log['staff'] . ' ' . $log['target'] . ' ' .
                                        $log['detail'] . ' ' . $actionLabel,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <td class="audit-time">
                                        <?= htmlspecialchars(
                                            implode('/', array_reverse(explode('-', $log['date']))),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                        <br>
                                        <?= htmlspecialchars($log['time'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td><?= htmlspecialchars(
                                        $log['staff'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $actionLabel,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $log['target'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $log['detail'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Ghi chú nhật ký -->
                <p class="staff-note">
                    Thời gian hiển thị theo múi giờ Việt Nam (Asia/Ho_Chi_Minh).
                    Nhật ký ghi nhận lịch sử các thao tác nghiệp vụ trên toàn hệ thống.
                </p>
            </section>
        </div>
    </main>

    <!-- Tương tác tìm kiếm và lọc nhật ký -->
    <script src="/assets/js/admin-audit.js" defer></script>
</body>
</html>