# TÀI LIỆU KIẾN TRÚC VÀ KIỂM THỬ HỆ THỐNG PHÂN QUYỀN RBAC (FO_LIBRARY)

**Dự án:** Website Thư viện số FoL (Faculty of Law / Digital Library)  
**Phiên bản:** 1.0  
**Ngày hoàn thiện:** 29/10/2026  
**Công nghệ:** PHP 8.x MVC (OOP), MySQL/MariaDB 10.4, PDO, HTML/CSS/JavaScript  
**Mô hình:** Role-Based Access Control (RBAC) kết hợp Resource-level Authorization (Policy)

---

## 1. Mục tiêu và Nguyên tắc Thiết kế

### 1.1. Mục tiêu
- Phân định rõ ràng quyền hạn giữa 3 vai trò: **Admin (`ADMIN`)**, **Thủ thư (`LIBRARIAN`)**, và **Sinh viên (`STUDENT`)**.
- Cho phép mở rộng và thêm quyền mới trực tiếp qua dữ liệu mà không cần chỉnh sửa cấu trúc bảng CSDL.
- Toàn bộ việc kiểm soát truy cập và xác thực thẩm quyền được thực thi tập trung tại Backend, độc lập với Frontend.
- Ngăn chặn triệt để lỗ hổng truy cập ngang (IDOR - Insecure Direct Object References) và truy cập trái phép bằng cách kết hợp RBAC và Resource-level Policy.
- Kiểm toán và truy vết các thao tác quan trọng thông qua bảng nhật ký hoạt động.

### 1.2. 6 Nguyên tắc cốt lõi
1. **Least Privilege (Đặc quyền tối thiểu):** Mỗi vai trò chỉ được cấp đúng những quyền cần thiết cho nghiệp vụ của mình.
2. **Deny by Default (Mặc định từ chối):** Nếu một tài nguyên hoặc hành động không được khai báo quyền hoặc người dùng không có quyền tương ứng, Backend mặc định trả về từ chối (`403 Forbidden`).
3. **Server-side Enforcement:** Không bao giờ tin tưởng dữ liệu người dùng gửi lên (`id_tai_khoan`, `id_vai_tro`, `role`). Toàn bộ quyền được xác định từ phiên đăng nhập hợp lệ trên Server.
4. **Centralized Authorization:** Kiểm tra quyền tập trung qua `AuthorizationService` và `AuthorizationMiddleware`, tránh rải rác điều kiện kiểm tra vai trò cứng (`if ($role === 'ADMIN')`) trong Controllers.
5. **Resource-level Authorization:** Kiểm tra quyền trên từng đối tượng dữ liệu cụ thể (ví dụ: Sinh viên chỉ được hủy lượt đặt chỗ của chính mình khi chưa check-in).
6. **Auditability (Khả năng kiểm toán):** Ghi nhận các sự kiện bảo mật, đăng nhập, phân quyền vào `nhat_ky_hoat_dong`, tự động ẩn mật khẩu và token nhạy cảm.

---

## 2. Thiết kế Cơ sở dữ liệu (Database Schema & Seeding)

### 2.1. Cấu trúc 4 bảng phân quyền chính
Hệ thống kết nối 4 bảng thực thể theo chuẩn RBAC:

```
+---------------+        N:1        +-----------+
|   tai_khoan   | ----------------> |  vai_tro  |
+---------------+                   +-----------+
                                          |
                                          | 1:N
                                          v
+---------------+        N:1        +---------------+
|     quyen     | <---------------- | vai_tro_quyen |
+---------------+                   +---------------+
```

1. **Bảng `vai_tro` (Roles):**
   - `id_vai_tro` (INT, PK, AUTO_INCREMENT)
   - `ma_vai_tro` (VARCHAR(50), UNIQUE, NOT NULL): `ADMIN`, `LIBRARIAN`, `STUDENT`.
   - `ten_vai_tro` (VARCHAR(100), NOT NULL): Quản trị viên, Thủ thư, Sinh viên.
   - `mo_ta` (VARCHAR(255), NULL)
   - `created_at`, `updated_at` (DATETIME, DEFAULT CURRENT_TIMESTAMP)

2. **Bảng `quyen` (Permissions):**
   - `id_quyen` (INT, PK, AUTO_INCREMENT)
   - `ma_quyen` (VARCHAR(100), UNIQUE, NOT NULL): Định dạng chuẩn `<resource>.<action>`.
   - `ten_quyen` (VARCHAR(150), NOT NULL)
   - `mo_ta` (VARCHAR(255), NULL)
   - `created_at` (DATETIME, DEFAULT CURRENT_TIMESTAMP)

3. **Bảng `vai_tro_quyen` (Role_Permissions):**
   - `id_vai_tro` (INT, FK -> `vai_tro.id_vai_tro`)
   - `id_quyen` (INT, FK -> `quyen.id_quyen`)
   - Khóa chính kép: `PRIMARY KEY (id_vai_tro, id_quyen)`.
   - Khóa ngoại có ràng buộc `ON DELETE CASCADE`.

4. **Bảng `tai_khoan` (Accounts / Users):**
   - `id_tai_khoan` (INT, PK, AUTO_INCREMENT)
   - `id_vai_tro` (INT, FK -> `vai_tro.id_vai_tro`, NOT NULL)
   - `email` (VARCHAR(100), UNIQUE, NOT NULL)
   - `mat_khau` (VARCHAR(255), NOT NULL)
   - `trang_thai` (VARCHAR(50), NOT NULL): `HoatDong`, `Khoa`.

5. **Bảng `nhat_ky_hoat_dong` (Audit Logs):**
   - `id_nhat_ky` (INT, PK, AUTO_INCREMENT)
   - `id_tai_khoan` (INT, NULL, FK)
   - `loai_hoat_dong` (VARCHAR(100), NOT NULL)
   - `noi_dung` (TEXT, NOT NULL)
   - `ip_address` (VARCHAR(45), NULL)
   - `thoi_gian` (DATETIME, DEFAULT CURRENT_TIMESTAMP)

---

### 2.2. Ma trận phân quyền thực tế (Permission Matrix)

Hệ thống đã chuẩn hóa danh mục 20 quyền nghiệp vụ:

| Mã quyền (`ma_quyen`) | Tên quyền | Admin | Thủ thư | Sinh viên |
| :--- | :--- | :---: | :---: | :---: |
| `book.view` | Xem danh mục sách | Có | Có | Có |
| `book.create` | Thêm đầu sách mới | Có | Có | Không |
| `book.update` | Cập nhật thông tin sách | Có | Có | Không |
| `book.delete` | Xóa sách khỏi hệ thống | Có | Không | Không |
| `book.inventory` | Quản lý bản sao cuốn sách & vị trí | Có | Có | Không |
| `loan.view` | Xem danh sách mượn trả | Có | Có | Có (Cá nhân) |
| `loan.create` | Tạo yêu cầu mượn sách | Có | Có | Có |
| `loan.confirm` | Xác nhận xử lý mượn/trả sách | Có | Có | Không |
| `fine.view` | Xem danh sách phiếu phạt | Có | Có | Có (Cá nhân) |
| `fine.manage` | Quản lý và xử lý phiếu phạt | Có | Có | Không |
| `room.view` | Xem danh sách phòng và vị trí đọc | Có | Có | Có |
| `room.book` | Đặt chỗ phòng đọc/chỗ ngồi | Có | Có | Có |
| `room.cancel` | Hủy đặt chỗ | Có | Có (Bất kỳ) | Có (Cá nhân) |
| `room.manage` | Quản lý phòng đọc và lịch đặt | Có | Có | Không |
| `user.view` | Xem danh sách người dùng | Có | Không | Không |
| `user.manage` | Quản lý tài khoản (tạo, khóa) | Có | Không | Không |
| `role.manage` | Quản lý vai trò và phân quyền | Có | Không | Không |
| `setting.manage` | Quản lý cấu hình hệ thống | Có | Không | Không |
| `audit.view` | Xem nhật ký kiểm toán hệ thống | Có | Không | Không |
| `favorite.manage` | Quản lý danh mục sách yêu thích | Có | Có | Có |

---

### 2.3. Seeder dữ liệu phân quyền (`RbacSeeder.php`)
Được triển khai bằng PHP OOP thuần với Prepared Statements tại:  
`project-fol-library/database/seeders/RbacSeeder.php`

**Tài khoản kiểm thử đã khởi tạo:**
- **Quản trị viên (Admin):** `admin@fol.uit.edu.vn` | Mật khẩu: `Password@123`
- **Thủ thư (Librarian):** `librarian@fol.uit.edu.vn` | Mật khẩu: `Password@123`
- **Sinh viên (Student):** `student@gm.uit.edu.vn` | Mật khẩu: `Password@123`

---

## 3. Kiến trúc Backend MVC và Dòng chảy Dữ liệu (Rule 10 AGENTS.md)

### 3.1. Sơ đồ chu trình xử lý Request và Phân quyền
```
Client gửi HTTP Request
        │
        ▼
Front Controller (public/index.php)
        │
        ▼
AuthenticationMiddleware::handle()
 ├── Kiểm tra Session ID và Session Data
 └── Kiểm tra trạng thái tài khoản trong DB (trang_thai === 'HoatDong')
        │
        ├─► Chưa đăng nhập hoặc tài khoản bị khóa ──► 401 Unauthorized / Chuyển hướng /login
        ▼
AuthorizationMiddleware::handle($requiredPermission)
 └── AuthorizationService::hasPermission($userId, $requiredPermission)
        │
        ├─► Không sở hữu quyền ────────────────────► 403 Forbidden (Giao diện chuẩn hóa)
        ▼
Controller Action (e.g. BookingController::cancel())
        │
        ▼
Resource Policy (e.g. BookingPolicy::canCancel($user, $booking))
 ├── Kiểm tra quyền sở hữu (id_tai_khoan === booking.id_tai_khoan)
 └── Kiểm tra điều kiện nghiệp vụ (trang_thai === 'DaDat' && thoi_gian_bat_dau > NOW)
        │
        ├─► Không thỏa mãn điều kiện nghiệp vụ ────► 403 / 400 Bad Request
        ▼
Repository (PDO Prepared Statements)
        │
        ▼
AuditService::log() ──► Ghi nhận thao tác vào nhat_ky_hoat_dong
        │
        ▼
Phản hồi JSON hoặc HTML View hoàn tất
```

---

### 3.2. Chi tiết các thành phần Source Code đã xây dựng

#### 1. Core & Session (`app/Core/`)
- `Autoloader.php`: Tự động tải lớp theo chuẩn PSR-4 cho namespace `App\`.
- `Database.php`: Quản lý kết nối PDO Singleton, kích hoạt `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- `Session.php`: Wrapper bảo mật session, cấu hình cờ `HttpOnly`, `SameSite=Lax`, tái tạo session ID (`session_regenerate_id(true)`) chống tấn công Session Fixation, và hỗ trợ phương thức `Session::writeClose()` để giải phóng file lock session, ngăn chặn hiện tượng treo server đơn luồng.

#### 2. Repositories (`app/Repositories/`)
- `UserRepository.php`: Tìm người dùng theo ID, email, cập nhật trạng thái hoạt động và vai trò.
- `RoleRepository.php`: Quản lý truy vấn danh sách vai trò, gán và thu hồi quyền hạn.
- `PermissionRepository.php`: Truy vấn danh sách quyền trực tiếp từ bảng `quyen` và `vai_tro_quyen` thông qua User ID hoặc Role ID.
- `AuditLogRepository.php`: Ghi nhật ký vào bảng `nhat_ky_hoat_dong`.

#### 3. Models (`app/Models/`)
- `User.php`: Đóng gói dữ liệu tài khoản và các helper:
  - `isAdmin(): bool`
  - `isLibrarian(): bool`
  - `isStudent(): bool`
  - `isActive(): bool`
- `Role.php` & `Permission.php`: Đối tượng đại diện cho vai trò và quyền hạn.

#### 4. Services & Policies (`app/Services/`)
- `AuthorizationService.php`:
  - `hasPermission(int $userId, string $permissionCode): bool`: Kiểm tra người dùng có quyền cụ thể hay không. Có cơ chế cache danh sách quyền theo vòng đời request.
  - `hasRole(int $userId, string $roleCode): bool`: Kiểm tra vai trò của người dùng.
  - `getUserPermissions(int $userId): array`: Lấy toàn bộ mã quyền của người dùng.
- `AuditService.php`: Tự động ghi nhật ký đăng nhập, đăng xuất, truy cập trái phép, tự động lọc sạch các trường dữ liệu nhạy cảm (`password`, `token`, `mat_khau`).
- `Policies/BookingPolicy.php`:
  - `canCancel(User $user, array $booking): bool`: Kiểm tra sinh viên chỉ được hủy lượt đặt chỗ của mình khi lượt đặt còn ở trạng thái `DaDat` và chưa đến giờ nhận chỗ; Thủ thư và Admin có quyền hủy điều phối bất kỳ lúc nào.
- `Policies/LoanPolicy.php`:
  - `canView(User $user, array $loan): bool`: Sinh viên chỉ xem được phiếu mượn của bản thân; Thủ thư/Admin xem được tất cả.

#### 5. Middleware (`app/Middleware/`)
- `AuthenticationMiddleware.php`: Kiểm tra session đăng nhập và trạng thái tài khoản.
- `AuthorizationMiddleware.php`: Kiểm tra quyền bắt buộc của route. Khi bị từ chối, thiết lập HTTP Status Code 403 và hiển thị trang cảnh báo Forbidden trang nhã.

#### 6. Controllers & Router (`app/Controllers/`, `public/index.php`)
- `AuthController.php`: Quản lý đăng nhập, kiểm tra mật khẩu bằng `password_verify()`, ghi log kiểm toán khi đăng nhập thành công hoặc thất bại.
- `public/index.php`: Đăng ký quy tắc định tuyến bảo vệ:
  - `/admin/*` $\rightarrow$ Yêu cầu các quyền `setting.manage`, `user.view`, `audit.view`.
  - `/librarian/*` $\rightarrow$ Yêu cầu các quyền `loan.confirm`, `book.inventory`, `room.manage`.
  - `/profile` $\rightarrow$ Yêu cầu đăng nhập (`AuthenticationMiddleware`).

---

## 4. Báo cáo Kết quả Kiểm thử (Verification & Test Results)

### 4.1. Kiểm thử Đơn vị Tự động (Unit Tests)
Tệp kiểm thử: `project-fol-library/tests/Unit/AuthorizationServiceTest.php`  
Phương thức chạy: PHP CLI (`php tests/Unit/AuthorizationServiceTest.php`)

```
========================================================
KIỂM THỬ ĐƠN VỊ HỆ THỐNG PHÂN QUYỀN RBAC (FO_LIBRARY)
========================================================
[PASS] admin_has_setting_manage: Admin phải có quyền setting.manage
[PASS] admin_has_user_manage: Admin phải có quyền user.manage
[PASS] admin_has_role_ADMIN: Admin phải có vai trò ADMIN
[PASS] librarian_has_loan_confirm: Thủ thư phải có quyền xác nhận mượn trả loan.confirm
[PASS] librarian_denied_setting_manage: Thủ thư KHÔNG ĐƯỢC có quyền setting.manage (Least privilege)
[PASS] librarian_denied_user_manage: Thủ thư KHÔNG ĐƯỢC có quyền user.manage
[PASS] student_has_room_book: Sinh viên phải có quyền đặt chỗ room.book
[PASS] student_denied_book_create: Sinh viên KHÔNG ĐƯỢC có quyền tạo sách book.create
[PASS] student_denied_audit_view: Sinh viên KHÔNG ĐƯỢC có quyền xem audit log audit.view
[PASS] deny_unknown_permission: Mã quyền không xác định phải bị từ chối mặc định
[PASS] deny_invalid_user_id: User ID không hợp lệ phải bị từ chối
[PASS] student_can_cancel_own_booking: Sinh viên được phép hủy lượt đặt chỗ còn hạn của chính mình
[PASS] student_cannot_cancel_other_booking: Người dùng khác không được hủy lượt đặt chỗ của sinh viên khác
[PASS] librarian_can_cancel_any_booking: Thủ thư có quyền điều phối quản lý và hủy lượt đặt của sinh viên
[PASS] cannot_cancel_already_checkedin: Không được hủy lượt đặt chỗ đã check-in nhận chỗ
========================================================
TỔNG KẾT: 15/15 TEST CASES (100%) ĐẠT CHUẨN PASS!
```

---

### 4.2. Kiểm thử Tích hợp HTTP Endpoint Trực tiếp (Live HTTP Tests)
Kiểm thử trực tiếp trên Web Server `localhost:8000` với từng trường hợp định tuyến:

| Tình huống kiểm thử | Endpoint | Tài khoản thực hiện | Mã phản hồi HTTP | Kết quả thực tế | Đánh giá |
| :--- | :--- | :--- | :---: | :--- | :---: |
| **1. Khách chưa đăng nhập** | `/admin/settings` | Vãng lai (Không session) | `302 Found` | Chuyển hướng về `/login` | **PASS** |
| **2. Sinh viên truy cập trang cá nhân** | `/profile` | `student@gm.uit.edu.vn` | `200 OK` | Cho phép truy cập thông tin | **PASS** |
| **3. Sinh viên truy cập nghiệp vụ thủ thư** | `/librarian` | `student@gm.uit.edu.vn` | `403 Forbidden` | Chặn truy cập, hiển thị trang 403 | **PASS** |
| **4. Sinh viên truy cập trang quản trị** | `/admin/settings` | `student@gm.uit.edu.vn` | `403 Forbidden` | Chặn truy cập, hiển thị trang 403 | **PASS** |
| **5. Thủ thư truy cập nghiệp vụ thủ thư** | `/librarian` | `librarian@fol.uit.edu.vn` | `200 OK` | Cho phép truy cập dashboard | **PASS** |
| **6. Thủ thư truy cập trang quản trị** | `/admin/settings` | `librarian@fol.uit.edu.vn` | `403 Forbidden` | Chặn truy cập, tuân thủ Least Privilege | **PASS** |
| **7. Quản trị viên truy cập trang quản trị** | `/admin/settings` | `admin@fol.uit.edu.vn` | `200 OK` | Cho phép truy cập cấu hình hệ thống | **PASS** |

---

## 5. Quy trình Thêm Quyền Mới (Operational Guide)

Để thêm một quyền mới vào hệ thống mà không làm ảnh hưởng đến dữ liệu đang chạy:

1. **Bước 1 - Định nghĩa quyền trong DB:**  
   Thêm bản ghi vào bảng `quyen` với mã dạng `<resource>.<action>`.  
   *Ví dụ:* `INSERT INTO quyen (ma_quyen, ten_quyen, mo_ta) VALUES ('report.export', 'Xuất báo cáo thống kê', 'Cho phép xuất báo cáo PDF/Excel');`
2. **Bước 2 - Gán quyền cho vai trò:**  
   Thêm liên kết vào bảng `vai_tro_quyen` cho các vai trò được phép (Admin hoặc Thủ thư).
3. **Bước 3 - Khai báo quyền tại Route Middleware:**  
   Trong `public/index.php`, áp dụng middleware cho route cần bảo vệ:
   ```php
   AuthorizationMiddleware::handle('report.export');
   ```
4. **Bước 4 - Kiểm tra quyền trong Service / Policy (nếu có):**  
   Sử dụng `AuthorizationService` nếu cần điều kiện phức tạp:
   ```php
   if (!$authService->hasPermission($currentUser->id_tai_khoan, 'report.export')) {
       // Xử lý từ chối
   }
   ```
5. **Bước 5 - Viết Unit Test bổ sung:**  
   Thêm trường hợp test vào `tests/Unit/AuthorizationServiceTest.php` để đảm bảo không bị hồi quy (regression).

---

## 6. Lịch sử Phiên bản & Git Tracking

- **Nhánh Git:** `feature/rbac-authorization`
- **Commit ID:** `e3b106a`
- **Thông điệp Commit:** `feat(auth): thiet ke va trien khai he thong phan quyen RBAC tap trung theo chuan tai lieu v1.0`
- **Tuân thủ AGENTS.md:** Không commit trực tiếp vào `main`, toàn bộ thay đổi đã được đẩy lên nhánh feature và sẵn sàng tạo Pull Request để Leader phê duyệt.
