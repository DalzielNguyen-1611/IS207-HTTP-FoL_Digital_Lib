<?php

declare(strict_types=1);

// Ngày minh họa đình chỉ tài khoản.
$today = new DateTimeImmutable(
    'today',
    new DateTimeZone('Asia/Ho_Chi_Minh')
);

// Tài khoản mẫu, không đại diện người dùng thật.
$users = [
    [
        'code' => 'SV2026001',
        'name' => 'Nguyễn Minh Anh',
        'email' => 'minhanh@example.com',
        'role' => 'student',
        'status' => 'active',
        'until' => '',
    ],
    [
        'code' => 'SV2026002',
        'name' => 'Trần Hoàng Nam',
        'email' => 'hoangnam@example.com',
        'role' => 'student',
        'status' => 'suspended',
        'until' => $today->modify('+30 days')->format('d/m/Y'),
    ],
    [
        'code' => 'CB001',
        'name' => 'Trần Thu Hà',
        'email' => 'thuha@example.com',
        'role' => 'librarian',
        'status' => 'active',
        'until' => '',
    ],
];

$roles = [
    'student' => 'Sinh viên',
    'librarian' => 'Cán bộ',
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

    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/librarian.css">
    <link rel="stylesheet" href="/assets/css/admin-users.css">
    <link rel="stylesheet" href="/assets/css/70years.css">
</head>
<body>
    <!-- Header khu vực quản trị -->
    <header class="staff-header">
        <a class="staff-brand" href="/admin/users">
            <img src="/assets/images/logo.jpg" alt="Logo Khoa Văn học">

            <span>
                <strong>Thư viện Khoa Văn học</strong>
                <small>Khu vực quản trị · Bản thử giao diện</small>
            </span>
        </a>

        <a class="staff-public-link" href="/">Về trang sinh viên</a>
    </header>

    <main class="staff-layout">
        <!-- Điều hướng quản trị -->
        <nav class="staff-sidebar" aria-label="Điều hướng quản trị">
            <span class="staff-menu-label">QUẢN TRỊ HỆ THỐNG</span>

            <a href="/admin/users" aria-current="page">Người dùng</a>
            <a href="/admin/audit">Nhật ký</a>
            <button type="button" disabled>Cấu hình · Sắp có</button>

            <a href="/librarian">Giao diện cán bộ ↗</a>
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

                <!-- Số lượng kết quả và trạng thái rỗng -->
                <p class="staff-note" id="user-count" role="status">
                    <?= htmlspecialchars(
                        (string) count($users),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?> tài khoản mẫu
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

            <!-- Vai trò mẫu -->
            <div class="admin-edit-field">
                <label for="edit-user-role">Vai trò</label>
                <select id="edit-user-role">
                    <option value="student">Sinh viên</option>
                    <option value="librarian">Cán bộ</option>
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