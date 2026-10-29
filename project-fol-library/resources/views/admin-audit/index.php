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

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-audit.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header quản trị -->
    <header class="staff-header">
        <a class="staff-brand" href="/" title="Về trang chủ Thư viện">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">
            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Quản trị</small>
            </span>
        </a>

        <div class="staff-header-actions">
            <nav class="staff-nav" aria-label="Điều hướng">
                <a href="/">Trang chủ</a>
                <?php $currentRole = \App\Core\Session::getRoleCode(); ?>
                <?php if ($currentRole === 'ADMIN'): ?>
                    <a href="/profile">Cá nhân</a>
                    <a href="/librarian">Cán bộ</a>
                    <a href="/admin/settings" class="active">Quản trị</a>
                <?php elseif ($currentRole === 'LIBRARIAN'): ?>
                    <a href="/librarian">Cán bộ</a>
                <?php endif; ?>
            </nav>

            <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
                <?php
                    $currentUser = \App\Core\Session::getAuthUser();
                    $userName = htmlspecialchars($currentUser['ho_ten'] ?? $currentUser['email'] ?? 'Tài khoản', ENT_QUOTES, 'UTF-8');
                ?>
                <a href="/admin/settings" class="staff-btn staff-btn-user" title="Tài khoản của bạn">
                    <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                    <span><?= $userName ?></span>
                </a>
                <a href="/logout" class="staff-btn staff-btn-logout" title="Đăng xuất khỏi hệ thống">
                    Đăng xuất
                </a>
            </div>
        </div>
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