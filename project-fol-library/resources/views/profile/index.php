<?php

declare(strict_types=1);

// Tải thông tin tài khoản sinh viên đã xác thực từ Session
$authUser = \App\Core\Session::getAuthUser();
$student = [
    'name' => $authUser['ho_ten'] ?? 'Sinh viên Văn khoa',
    'code' => $authUser['mssv'] ?? ('SV' . str_pad((string)($authUser['id_tai_khoan'] ?? 1), 6, '0', STR_PAD_LEFT)),
    'email' => $authUser['email'] ?? 'student@gm.uit.edu.vn',
    'phone' => $authUser['so_dien_thoai'] ?? '',
    'major' => $authUser['ten_nganh'] ?? 'Văn học',
];

//Các mục trong trang cá nhân
$sections = [
    'information' => 'Thông tin',
    'loans' => 'Sách mượn',
    'favorites' => 'Yêu thích',
    'bookings' => 'Phòng đọc',
];

// Đọc dữ liệu mượn sách và sách yêu thích thực tế của tài khoản từ CSDL
$userId = (int)($authUser['id_tai_khoan'] ?? \App\Core\Session::getUserId() ?? 0);
$loanRepo = new \App\Repositories\LoanRepository();
$loanGroups = $loanRepo->getUserLoans($userId);
$favoriteCatalog = $loanRepo->getUserFavorites($userId);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <!-- Metadata & Tài nguyên giao diện -->
    <meta charset='UTF-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trang cá nhân · Thư viện Khoa Văn học</title>

    <!-- Favicon biểu trưng Khoa Văn học -->
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/assets/images/logo.png">

    <link rel="stylesheet" href='/assets/css/base.css'>
    <link rel="stylesheet" href='/assets/css/home.css'>
    <link rel="stylesheet" href='/assets/css/profile.css'>
    <link rel="stylesheet" href='/assets/css/70years.css'>
</head>
<body>
    <!-- Header & Điều hướng -->
    <header class="header">
        <a class="brand" href="/">
            <img
                class="brand-logo"
                src="/assets/images/logo.jpg"
                alt="Logo Khoa Văn học"
            >
            <span>
                <span class="brand-label">THƯ VIỆN KHOA VĂN HỌC</span>
                <strong>Không gian tri thức của bạn</strong>
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
            <a href="/rules">Nội quy</a>
            <a href="/feedback">Góp ý</a>
            <a href="/contact">Liên hệ</a>
            <a href="/profile" class="active" aria-current="page">Cá nhân</a>
            <?php $navRole = \App\Core\Session::getRoleCode(); ?>
            <?php if ($navRole === 'ADMIN'): ?>
                <a href="/librarian">Cán bộ</a>
                <a href="/admin/settings">Quản trị</a>
            <?php elseif ($navRole === 'LIBRARIAN'): ?>
                <a href="/librarian">Cán bộ</a>
            <?php endif; ?>
        </nav>

        <!-- Trạng thái xác thực & Đăng xuất -->
        <div class="user-nav-group" style="display:inline-flex;align-items:center;gap:10px;">
            <a href="/profile" class="login-button" style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;padding:8px 16px;text-decoration:none;" title="Tài khoản của bạn">
                <span style="display:inline-block;width:8px;height:8px;background:#10b981;border-radius:50%;box-shadow:0 0 0 2px rgba(16,185,129,0.2);"></span>
                <span><?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
            <a href="/logout" class="login-button" style="display:inline-flex;align-items:center;font-size:13px;font-weight:600;padding:8px 14px;color:#dc2626;border-color:#fecaca;background:#fff5f5;text-decoration:none;" title="Đăng xuất khỏi hệ thống">
                Đăng xuất
            </a>
        </div>
    </header>

    <main class="page">
        <!-- Giới thiệu trang cá nhân -->
        <section class="profile-intro">
            <span class="eyebrow">GÓC THƯ VIỆN CỦA BẠN</span>
            <h1>Trang cá nhân</h1>
            <p>Quản lý hồ sơ và theo dõi hoạt động tại thư viện</p>
        </section>

        <div class="profile-layout">
            <!-- Điều hướng các mục hồ sơ -->
            <nav class="profile-menu" aria-label="Các mục trang cá nhân">
                <?php foreach ($sections as $key => $label): ?>
                    <button
                        type="button"
                        data-section="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                        aria-controls="panel-<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $key === 'information' ? 'aria-current="true"' : '' ?>
                    >
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                <?php endforeach; ?>
            </nav>

            <div>
                <!-- Thông tin cá nhân mẫu -->
                <section
                    class="profile-panel"
                    id="panel-information"
                    aria-labelledby="information-title"
                >
                    <h2 id="information-title">Thông tin cá nhân</h2>

                    <form id="profile-form">
                        <div class="profile-fields">
                            <!-- Họ tên -->
                            <div>
                                <label for="profile-name">Họ và tên</label>
                                <input
                                    id="profile-name"
                                    type="text"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <!-- Mã sinh viên không chỉnh sửa -->
                            <div>
                                <label for="profile-code">Mã sinh viên</label>
                                <input
                                    id="profile-code"
                                    type="text"
                                    value="<?= htmlspecialchars($student['code'], ENT_QUOTES, 'UTF-8') ?>"
                                    readonly
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="profile-email">Email sinh viên</label>
                                <input
                                    id="profile-email"
                                    type="email"
                                    value="<?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <!-- Số điện thoại -->
                            <div>
                                <label for="profile-phone">Số điện thoại</label>
                                <input
                                    id="profile-phone"
                                    type="tel"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($student['phone'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>
                        </div>

                        <!-- Thao tác biểu mẫu -->
                        <div class="profile-actions">
                            <button class="profile-save" type="submit">
                                Lưu thay đổi mẫu
                            </button>
                            <button class="profile-reset" type="reset">
                                Khôi phục
                            </button>
                        </div>

                        <p id="profile-feedback" role="status"></p>
                    </form>

                    <p class="preview-note">
                        Hồ sơ mẫu. Thay đổi chưa được lưu và sẽ mất khi tải lại trang. Hãy dùng thông tin giả để trải nghiệm
                    </p>
                </section>

                <!-- Sách đang mượn & lịch sử mượn mẫu -->
                <section
                    class="profile-panel"
                    id="panel-loans"
                    aria-labelledby="loans-title"
                    hidden
                >
                    <h2 id="loans-title">Sách mượn</h2>
   
                    <p class="preview-note">
                        Các phiếu dưới đây là dữ liệu minh họa, chưa liên kết với thao tác đăng ký mượn trên website.
                    </p>

                    <?php foreach ($loanGroups as $groupIndex => $group): ?>
                        <!-- Một nhóm phiếu mượn -->
                        <h3 id="loan-group-<?= htmlspecialchars(
                            (string) $groupIndex,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>">
                            <?= htmlspecialchars($group['title'], ENT_QUOTES, 'UTF-8') ?>
                        </h3>

                    <?php if ($group['items'] === []): ?>
                        <!-- Trạng thái không có phiếu -->
                        <p class="profile-empty">Chưa có phiếu mượn trong mục này.</p>
                    <?php else: ?>
                        <!-- Bảng có thể cuộn ngang trên điện thoại -->
                        <div
                            class="profile-table-wrap"
                            tabindex="0"
                            role="region"
                            aria-labelledby="loan-group-<?= htmlspecialchars(
                                (string) $groupIndex,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >
                            <table class="profile-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Tên sách</th>
                                        <th scope="col">Ngày mượn</th>
                                        <th scope="col">Hạn trả</th>
                                        <th scope="col">Ngày trả</th>
                                        <th scope="col">Trạng thái</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($group['items'] as $loan): ?>
                                        <!-- Thông tin một phiếu mượn -->
                                        <tr>
                                            <th scope="row">
                                                <?= htmlspecialchars(
                                                    $loan['title'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </th>

                                            <td><?= htmlspecialchars(
                                                $loan['borrowed'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?></td>

                                            <td><?= htmlspecialchars(
                                                $loan['due'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?></td>

                                            <td><?= htmlspecialchars(
                                                $loan['returned'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?></td>

                                            <td>
                                                <span class="loan-status <?= $loan['overdue'] ? 'is-overdue' : '' ?>">
                                                    <?= htmlspecialchars(
                                                        $loan['status'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    ) ?>
                                                </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </section>

                <!-- Danh sách yêu thích lưu trên trình duyệt -->
                <section
                    class="profile-panel"
                    id="panel-favorites"
                    aria-labelledby="favorites-title"
                    hidden
                >
                    <h2 id="favorites-title">Sách yêu thích</h2>

                    <p class="preview-note">
                        Danh sách mẫu chỉ lưu trên trình duyệt này,
                        chưa gắn với tài khoản hoặc đồng bộ sang thiết bị khác.
                    </p>

                    <!-- Thông báo kết quả cập nhật -->
                    <p id="favorites-feedback" role="status"></p>

                    <!-- Trạng thái chưa lưu sách -->
                    <p class="profile-empty" id="favorites-empty" hidden>
                        Bạn chưa lưu sách yêu thích.
                        <a href="/books">Khám phá sách →</a>
                    </p>

                    <!-- Các tài liệu sẽ được JavaScript lọc theo mã đã lưu -->
                    <div class="profile-favorites">
                        <?php foreach ($favoriteCatalog as $favoriteBook): ?>
                            <article
                                class="profile-favorite"
                                data-favorite-id="<?= htmlspecialchars(
                                    (string) $favoriteBook['id'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                hidden
                            >
                                <!-- Tên sách & tác giả -->
                                <div>
                                    <h3>
                                        <a href="/book-detail?id=<?= htmlspecialchars(
                                            (string) $favoriteBook['id'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                            <?= htmlspecialchars(
                                                $favoriteBook['title'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </a>
                                    </h3>

                                    <p>
                                        <?= htmlspecialchars(
                                            $favoriteBook['author'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </p>
                                </div>

                                <!-- Thao tác bỏ yêu thích -->
                                <button
                                    class="profile-reset"
                                    type="button"
                                    data-remove-favorite="<?= htmlspecialchars(
                                        (string) $favoriteBook['id'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    aria-label="<?= htmlspecialchars(
                                        'Bỏ yêu thích ' . $favoriteBook['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >
                                    Bỏ yêu thích
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Lịch đặt phòng đọc lưu trên trình duyệt -->
                <section
                    class="profile-panel"
                    id="panel-bookings"
                    aria-labelledby="bookings-title"
                    hidden
                >
                    <h2 id="bookings-title">Lịch phòng đọc</h2>

                    <p class="preview-note">
                        Lịch mẫu trên trình duyệt này, chưa gắn với tài khoản.
                        Có thể hủy khi còn ít nhất 72 giờ trước ca đọc.
                    </p>

                    <!-- Thông báo thao tác -->
                    <p id="bookings-feedback" role="status"></p>

                    <!-- Trạng thái chưa có lịch -->
                    <p class="profile-empty" id="bookings-empty" hidden>
                        Bạn chưa có lịch đặt phòng đọc.
                    </p>

                    <!-- Danh sách được tạo bằng JavaScript -->
                    <div class="profile-bookings" id="bookings-list"></div>

                    <!-- Điều hướng tới lịch ca đọc -->
                    <p><a href="/reading-room">Đặt chỗ phòng đọc →</a></p>
                </section>
            </div>
        </div>
    </main>

    <!-- Tương tác hồ sơ mẫu -->
    <script src="/assets/js/profile.js" defer></script>

    <!-- Quản lý phòng đọc mẫu -->
    <script src="/assets/js/profile-bookings.js" defer></script>
</body>
</html>

