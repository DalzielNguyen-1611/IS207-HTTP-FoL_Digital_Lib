<?php

declare(strict_types=1);

// Ngày minh họa đình chỉ tài khoản.
$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

require_once dirname(__DIR__, 3) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\UserRepository;

$userRepo = new UserRepository();
$dbUsers = $userRepo->getAllUsers();

// Chuyển đổi dữ liệu từ CSDL fol_library_db sang cấu trúc phục vụ hiển thị
$users = array_map(static function (array $u): array {
    return [
        'code' => (string)($u['code'] ?? 'ND' . $u['id_tai_khoan']),
        'name' => (string)($u['name'] ?? $u['email']),
        'email' => (string)$u['email'],
        'role' => (string)($u['role_slug'] ?? 'student'),
        'status' => (string)($u['status_slug'] ?? 'active'),
        'until' => '',
    ];
}, $dbUsers);

$roles = [
    'student' => 'Sinh viên',
    'librarian' => 'Cán bộ',
    'admin' => 'Quản trị viên',
];

$statuses = [
    'active' => 'Hoạt động',
    'suspended' => 'Đình chỉ',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & tài nguyên giao diện -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý người dùng · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/home.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-users.css">
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
            <a class="login-button" href="/login">
                Đăng nhập
            </a>
        <?php endif; ?>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng quản trị -->
        <nav class="staff-sidebar" aria-label="Điều hướng quản trị">
            <span class="staff-menu-label">QUẢN TRỊ HỆ THỐNG</span>

            <a href="/admin/users" aria-current="page">Người dùng</a>
            <a href="/admin/audit">Nhật ký</a>
            <a href="/admin/settings">Cấu hình</a>
            <a href="/librarian">Giao diện cán bộ</a>
            <a href="/profile">Giao diện cá nhân (SV)</a>
        </nav>

        <div class="staff-content">
            <!-- Tiêu đề quản lý người dùng -->
            <section class="staff-intro">
                <span class="staff-eyebrow">TÀI KHOẢN THƯ VIỆN</span>
                <h1>Quản lý người dùng</h1>
                <p>Tra cứu tài khoản sinh viên và cán bộ trong dữ liệu mẫu.</p>
            </section>

            <section class="staff-panel" aria-labelledby="users-title">
                <h2 id="users-title">Danh sách tài khoản</h2>

                <!-- Tìm kiếm theo mã, tên hoặc email -->
                <form class="staff-search" id="user-search-form">
                    <input
                        id="user-keyword"
                        type="search"
                        aria-label="Tìm mã tài khoản, họ tên hoặc email"
                        placeholder="Tìm mã tài khoản, họ tên hoặc email…"
                    >
                    <button type="submit">Tìm kiếm</button>
                </form>

                <!-- Bộ lọc vai trò và trạng thái -->
                <div class="admin-user-filters">
                    <div>
                        <label for="user-role-filter">Vai trò</label>
                        <select id="user-role-filter">
                            <option value="">Tất cả vai trò</option>

                            <?php foreach ($roles as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="user-status-filter">Trạng thái</label>
                        <select id="user-status-filter">
                            <option value="">Tất cả trạng thái</option>

                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button
                        class="staff-action-button"
                        id="reset-user-filters"
                        type="button"
                    >
                        Xóa bộ lọc
                    </button>
                </div>

                <p class="staff-note" id="user-count" role="status">
                    <?= htmlspecialchars(
                        (string) count($users),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> tài khoản
                </p>

                <!-- Kết quả chỉnh sửa mẫu -->
                <p class="staff-note" id="user-feedback" role="status"></p>

                <p class="staff-note" id="user-empty" hidden>
                    Không tìm thấy tài khoản phù hợp.
                </p>

                <!-- Bảng tài khoản có thể cuộn ngang -->
                <div
                    class="staff-table-wrap"
                    tabindex="0"
                    role="region"
                    aria-label="Danh sách tài khoản mẫu"
                >
                    <table class="staff-table">
                        <thead>
                            <tr>
                                <th scope="col">Mã tài khoản</th>
                                <th scope="col">Họ tên</th>
                                <th scope="col">Email</th>
                                <th scope="col">Vai trò</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col">Đình chỉ đến</th>
                                <th scope="col">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <!-- Một tài khoản mẫu -->
                                <tr
                                    data-user-row
                                    data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-status="<?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= htmlspecialchars(
                                        $user['code'] . ' ' .
                                        $user['name'] . ' ' . $user['email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    <th scope="row">
                                        <?= htmlspecialchars($user['code'], ENT_QUOTES, 'UTF-8') ?>
                                    </th>

                                    <td data-user-field="name">
                                        <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-user-field="email">
                                        <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td data-user-field="role">
                                        <?= htmlspecialchars(
                                            $roles[$user['role']],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <td data-user-field="status">
                                        <span class="admin-user-status <?= $user['status'] === 'suspended' ? 'is-suspended' : '' ?>">
                                            <?= htmlspecialchars(
                                                $statuses[$user['status']],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td data-user-field="until">
                                        <?= htmlspecialchars(
                                            $user['until'] !== '' ? $user['until'] : '—',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </td>

                                    <!-- Chỉnh sửa và đổi trạng thái tài khoản mẫu -->
                                    <td>
                                        <div class="admin-user-actions">
                                            <button
                                                class="staff-action-button"
                                                type="button"
                                                data-edit-user
                                            >
                                                Sửa
                                            </button>

                                            <button
                                                class="staff-action-button"
                                                type="button"
                                                data-toggle-suspension
                                            >
                                                Đình chỉ 30 ngày
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Phạm vi bản thử -->
                <p class="staff-note">
                    Chưa xác thực quyền Admin. Trạng thái và thời hạn đình chỉ
                    là dữ liệu minh họa, không khóa tài khoản thật.
                </p>
            </section>
        </div>
    </main>

    <!-- Hộp chỉnh sửa tài khoản mẫu -->
    <dialog
        class="admin-user-dialog"
        id="edit-user-dialog"
        aria-labelledby="edit-user-title"
    >
        <form id="edit-user-form">
            <h2 id="edit-user-title">Chỉnh sửa tài khoản</h2>
            <p class="staff-note" id="edit-user-code"></p>

            <!-- Thông tin cá nhân -->
            <div class="admin-edit-field">
                <label for="edit-user-name">Họ và tên</label>
                <input
                    id="edit-user-name"
                    type="text"
                    maxlength="100"
                    required
                >
            </div>

            <div class="admin-edit-field">
                <label for="edit-user-email">Email</label>
                <input
                    id="edit-user-email"
                    type="email"
                    maxlength="254"
                    required
                >
            </div>

            <!-- Vai trò -->
            <div class="admin-edit-field">
                <label for="edit-user-role">Vai trò</label>
                <select id="edit-user-role">
                    <option value="student">Sinh viên</option>
                    <option value="librarian">Cán bộ</option>
                    <option value="admin">Quản trị viên</option>
                </select>
            </div>

            <p class="staff-note">
                Chỉ cập nhật bảng mẫu, không cấp quyền thật.
                Tài khoản đang đình chỉ tạm giữ nguyên vai trò
                trong bước thử này.
            </p>

            <!-- Lưu hoặc hủy thao tác -->
            <div class="admin-edit-actions">
                <button
                    class="staff-action-button"
                    id="cancel-edit-user"
                    type="button"
                >
                    Hủy
                </button>

                <button class="admin-save-user" type="submit">
                    Lưu thay đổi mẫu
                </button>
            </div>
        </form>
    </dialog>

    <!-- Tìm kiếm và lọc tài khoản mẫu -->
    <script src="/assets/js/admin-users.js" defer></script>
</body>
</html>