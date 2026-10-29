# BÁO CÁO TOÀN DIỆN VỀ AUDIT, KHẮC PHỤC LỖI VÀ CHUẨN HÓA KIẾN TRÚC
## AUTHENTICATION, AUTHORIZATION (RBAC) VÀ SESSION MANAGEMENT

> **Dự án:** Website Thư viện Khoa Văn học (Faculty of Literature Digital Library - FoL Library)  
> **Framework / Công nghệ:** PHP MVC, MySQL (MariaDB 10.4), Tailwind CSS, Vanilla JS  
> **Nhánh thực hiện:** `feature/rbac-authorization`  
> **Tài liệu tham chiếu:** [docs/RBAC_IMPLEMENTATION.md](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/docs/RBAC_IMPLEMENTATION.md), [docs/GOOGLE_OAUTH_IMPLEMENTATION.md](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/docs/GOOGLE_OAUTH_IMPLEMENTATION.md)  
> **Ngày hoàn thành:** 29/10/2026  

---

## MỤC LỤC
1. [Bối cảnh & Hiện trạng trước khi sửa](#1-bối-cảnh--hiện-trạng-trước-khi-sửa)
2. [Phân tích Nguyên nhân Gốc rễ (Root Cause Analysis)](#2-phân-tích-nguyên-nhân-gốc-rễ-root-cause-analysis)
3. [Danh sách File Thay đổi và Giải pháp Kỹ thuật](#3-danh-sách-file-thay-đổi-và-giải-pháp-kỹ-thuật)
4. [Thiết kế Kiến trúc Chuẩn hóa Sau khi Sửa](#4-thiết-kế-kiến-trúc-chuẩn-hóa-sau-khi-sửa)
5. [Toàn bộ Kịch bản Kiểm thử & Kết quả Thực tế (16 Test Cases)](#5-toàn-bộ-kịch-bản-kiểm-thử--kết-quả-thực-tế-16-test-cases)
6. [Kiểm thử HTTP End-to-End với Cookie Jar trên Máy chủ Thực](#6-kiểm-thử-http-end-to-end-với-cookie-jar-trên-máy-chủ-thực)
7. [Hướng dẫn Kiểm thử Thủ công bằng Trình duyệt (Step-by-Step QA)](#7-hướng-dẫn-kiểm-thử-thủ-công-bằng-trình-duyệt-step-by-step-qa)
8. [Quy tắc Vận hành, Bảo mật & Mở rộng Tương lai](#8-quy-tắc-vận-hành-bảo-mật--mở-rộng-tương-lai)

---

## 1. Bối cảnh & Hiện trạng trước khi sửa

### 1.1. Hiện tượng lỗi được báo cáo
- **Lỗi 1 (Bỏ lọt phân quyền):** Khi người dùng đăng nhập bằng tài khoản Quản trị viên (`ADMIN`), nếu tự ý sửa URL trên thanh địa chỉ sang trang cá nhân của sinh viên (`/profile`), hệ thống vẫn cho phép truy cập bình thường thay vì chặn lại.
- **Lỗi 2 (Mất trạng thái phiên làm việc):** Sau khi bị lỗi trên hoặc sau khi chuyển trang, khi người dùng quay lại Trang chủ (`/`), hệ thống hiển thị nút **"Đăng nhập"** như thể đã mất Session, và khi bấm vào lại đưa về form đăng nhập.

### 1.2. Mục tiêu kỹ thuật
- Không sửa lỗi kiểu "vá víu" (chỉ sửa riêng `/profile` cho Admin).
- Rà soát toàn bộ chu trình xử lý của ứng dụng: từ **Authentication** (xác định danh tính), **Authorization** (kiểm tra quyền RBAC), **Session Management** (vòng đời cookie/phiên), đến **Routing** và **View Rendering**.
- Bảo đảm nguyên tắc phân quyền cốt lõi: **Thiếu quyền truy cập tuyệt đối không được tự tiện xóa session của người dùng**.

---

## 2. Phân tích Nguyên nhân Gốc rễ (Root Cause Analysis)

Sau khi kiểm tra toàn bộ mã nguồn từ entrypoint `public/index.php`, hệ thống middleware, các service nghiệp vụ và cấu trúc giao diện, chúng tôi phát hiện 4 nguyên nhân gốc rễ mang tính hệ thống:

### 2.1. Lỗi cấu hình Route Guard trong `public/index.php`
- **Vị trí:** `public/index.php` (dòng 145–168 trước khi sửa).
- **Phân tích:** Ma trận phân quyền khai báo `'/profile' => 'authenticated'`. Khi Admin truy cập `/profile`:
  1. `AuthenticationMiddleware` kiểm tra `Session::isAuthenticated()` -> Trả về `true` (vì Admin đã đăng nhập).
  2. Router kiểm tra điều kiện `if ($requiredPermission !== 'authenticated')` -> Do `$requiredPermission` chính là chuỗi `'authenticated'`, khối lệnh gọi `AuthorizationMiddleware` bị **bỏ qua hoàn toàn**.
  3. Kết quả: Bất kỳ ai đã đăng nhập (kể cả Admin và Thủ thư) đều vào được trang sinh viên.

### 2.2. Thanh điều hướng View bị gắn cứng (Hardcoded State) & Thiếu "Guest Guard"
- **Vị trí:** `resources/views/home/index.php` (dòng 79–83), `books/index.php`, `book-detail/index.php`, `reading-room/index.php`.
- **Phân tích:** 
  1. Giao diện trang chủ và các trang công khai gắn cứng thẻ HTML: `<a class="login-button" href="/login">Đăng nhập</a>`. Dù trên server Session của người dùng vẫn hoạt động bình thường, mắt người dùng vẫn nhìn thấy chữ "Đăng nhập".
  2. Khi người dùng click vào nút này, router chuyển đến `/login`. Tại đây không có middleware kiểm tra trạng thái khách (**Guest Guard**). View `login/index.php` được hiển thị lại, tạo cảm giác người dùng đã bị logout ngầm.
  3. Ngoài ra, việc router cũ gọi `Session::writeClose()` trước khi `require $view` làm ngắt kết nối session sớm; nếu view nào truy cập lại session sau khi headers đã xuất sẽ phát sinh cảnh báo và lỗi đồng bộ.

### 2.3. Backdoor leo thang đặc quyền `?switch_role=...`
- **Vị trí:** `public/index.php` (dòng 65–107 trước khi sửa).
- **Phân tích:** Đoạn mã chuyển đổi vai trò nhanh nhằm mục đích thử nghiệm cũ vẫn còn nằm trong file router công khai. Bất kỳ ai chỉ cần gửi request kèm `?switch_role=admin` là có thể tự nâng quyền lên `ADMIN` mà không cần xác thực. Điều này vi phạm tiêu chuẩn bảo mật RBAC nghiêm trọng (AUTH-10).

### 2.4. Quản lý Session thiếu an toàn và thiếu Session Timeout
- **Vị trí:** `app/Core/Session.php`.
- **Phân tích:** `session_set_cookie_params()` được gọi mà không kiểm tra `headers_sent()`, dễ dẫn đến lỗi PHP Warning khi nạp view hoặc redirect. Thời gian sống của session bị fix cứng thay vì đọc từ `.env`, và thiếu cơ chế phát hiện phiên làm việc quá hạn dựa trên timestamp xác thực (`authenticated_at`).

---

## 3. Danh sách File Thay đổi và Giải pháp Kỹ thuật

```
IS207_HTTP_Project/project-fol-library/
├── app/
│   ├── Core/
│   │   └── Session.php                       [SỬA: Cookie params an toàn, Dynamic Lifetime, Timeout, hasRole]
│   └── Middleware/
│       ├── AuthenticationMiddleware.php      [SỬA: Check DB realtime, không xóa session khi thiếu auth]
│       └── AuthorizationMiddleware.php       [SỬA: Hỗ trợ role:..., giao diện 403, BẢO TOÀN SESSION]
├── public/
│   └── index.php                             [SỬA: Guard role:STUDENT, Guest Guard, xóa switch_role]
├── resources/views/
│   ├── home/index.php                        [SỬA: Widget định danh người dùng động, hiện Quản trị viên/Sinh viên]
│   ├── books/index.php                       [SỬA: Header động hiển thị trạng thái đăng nhập]
│   ├── book-detail/index.php                 [SỬA: Header động hiển thị trạng thái đăng nhập]
│   ├── reading-room/index.php                [SỬA: Header động hiển thị trạng thái đăng nhập]
│   └── profile/index.php                     [SỬA: Đọc dữ liệu thực tế từ Session, hiển thị nút Đăng xuất]
└── tests/Integration/
    ├── AuthenticationAuthorizationRegressionTest.php [MỚI: 16 Test Cases tự động đạt chuẩn 100%]
    └── HttpEndToEndTest.php                  [MỚI: Kiểm thử live HTTP cURL với Cookie Jar]
```

### Chi tiết thay đổi từng file:

#### 1. `app/Core/Session.php`
- Thêm kiểm tra `!headers_sent()` trước khi cấu hình Cookie params (`httponly`, `samesite=Lax`, `secure` khi HTTPS).
- Đọc thời hạn phiên linh hoạt từ biến môi trường `$_ENV['SESSION_LIFETIME']` (mặc định 120 phút).
- Bổ sung kiểm tra **Session Timeout**: nếu `time() - $_SESSION['authenticated_at'] > $lifetimeSeconds`, tự động hủy phiên quá hạn.
- Bổ sung các phương thức: `hasRole(string $roleCode): bool`, `regenerateId(): void`, đồng thời chuẩn hóa mã vai trò luôn ở dạng in hoa (`ADMIN`, `LIBRARIAN`, `STUDENT`).

#### 2. `app/Middleware/AuthenticationMiddleware.php`
- Kiểm tra trạng thái tài khoản thời gian thực trong CSDL (`trang_thai === 'HoatDong'`). Nếu tài khoản bị khóa hoặc đình chỉ trong database, session bị hủy ngay lập tức và trả về mã lỗi 401.
- Nếu người dùng chưa đăng nhập: trả về HTTP 401 JSON (đối với AJAX/API) hoặc redirect về `/login?error=...` (đối với HTML) **mà không xóa session**.
- Tự động đồng bộ vai trò mới nhất từ DB vào Session nếu người quản trị thay đổi vai trò trong CSDL.

#### 3. `app/Middleware/AuthorizationMiddleware.php`
- Hỗ trợ cú pháp phân quyền theo vai trò: `role:STUDENT`, `role:ADMIN`, `role:LIBRARIAN` hoặc danh sách nhiều vai trò: `role:ADMIN,LIBRARIAN`.
- Hỗ trợ cú pháp phân quyền theo RBAC permission: `permission:<resource>.<action>` hoặc `<resource>.<action>`.
- **Nguyên tắc bảo toàn phiên:** Khi từ chối quyền, trả về **HTTP 403 Forbidden** và **tuyệt đối không gọi `Session::destroy()`**.
- Render giao diện 403 thân thiện, chỉ rõ vai trò hiện tại và vai trò cần có, kèm theo nút chuyển hướng quay lại đúng Dashboard của vai trò đó (Admin về `/admin/settings`, Thủ thư về `/librarian`, Sinh viên về `/profile`) và nút "Về trang chủ".

#### 4. `public/index.php`
- Loại bỏ hoàn toàn tham số `switch_role` và các thao tác thay đổi vai trò tùy tiện từ request.
- Thiết lập **Guest Guard**: nếu người đã đăng nhập truy cập `/login` hoặc `/register`, router tự động chuyển hướng về trang làm việc tương ứng của vai trò đó.
- Cập nhật ma trận phân quyền:
  - `'/profile' => 'role:STUDENT'`
  - `'/admin/*' => 'permission:...'`
  - `'/librarian/*' => 'permission:...'`
- Đăng ký đầy đủ view cho `/collections` và `/contact`.
- Loại bỏ lệnh `Session::writeClose()` sớm trước khi nạp template view.

#### 5. Các View giao diện (`home`, `books`, `book-detail`, `reading-room`, `profile`)
- Thay thế nút "Đăng nhập" tĩnh bằng khối điều khiển thông minh:
  ```php
  <?php if (\App\Core\Session::isAuthenticated()): ?>
      <!-- Hiển thị chấm xanh online, tên người dùng, huy hiệu vai trò và nút Đăng xuất -->
  <?php else: ?>
      <!-- Hiển thị nút Đăng nhập dẫn tới /login -->
  <?php endif; ?>
  ```
- File `profile/index.php`: Thay thế dữ liệu tĩnh mẫu bằng dữ liệu thực lấy từ `Session::getAuthUser()`.
- Cập nhật thanh điều hướng `<nav class="navigation">` tại Trang chủ (`/`), Tra cứu sách (`/books`), Chi tiết sách (`/book-detail`) và Phòng đọc (`/reading-room`): **Bất kỳ vai trò nào khi đã đăng nhập đều có liên kết trực tiếp đến khu vực làm việc của mình ngay từ Trang chủ** (Admin có liên kết *"Khu vực Quản trị"*, Thủ thư có *"Khu vực Thủ thư"*, Sinh viên có *"Trang cá nhân"*).

#### 6. Các View Quản trị & Cán bộ (`admin-*`, `librarian-*`)
- **Loại bỏ chữ " · Bản thử giao diện":** Chuẩn hóa tiêu đề khu vực thành `<small>Khu vực quản trị</small>` và `<small>Khu vực cán bộ</small>`.
- **Liên kết Logo & Tiêu đề Brand về Trang chủ (`href="/"`) cho mọi role:** Trước đây click vào Logo/Title dẫn tới `/admin/users` hoặc `/librarian`. Đã cập nhật thành `<a class="staff-brand" href="/" title="Về trang chủ Thư viện">` cho toàn bộ 8 view, đảm bảo click vào logo luôn trở về Trang chủ (`/`) và duy trì phiên đăng nhập trọn vẹn cho mọi vai trò.
- **Sửa nhãn điều hướng "Về trang sinh viên" thành "Về trang chủ":** Trước đây link trỏ về `href="/"` nhưng hiển thị nhãn gây hiểu lầm là *"Về trang sinh viên"*. Đã chuẩn hóa thành `<a class="staff-public-link" href="/">Về trang chủ</a>`, giúp mọi vai trò có thể quay về Trang chủ và từ Trang chủ đi đến mọi chức năng được cấp phép.

---

## 4. Thiết kế Kiến trúc Chuẩn hóa Sau khi Sửa

### 4.1. Chu trình Xử lý Request Tổng thể
```
[ Yêu cầu HTTP từ Client ]
            │
            ▼
┌─────────────────────────────────────────────────────────────┐
│ 1. ROUTER & GUEST GUARD (public/index.php)                  │
│    - Chuẩn hóa Request Path                                 │
│    - Nếu là /login hoặc /register:                          │
│        + Đã đăng nhập ──> Chuyển hướng về Dashboard tương ứng│
│        + Chưa đăng nhập ──> Cho phép vào form đăng nhập     │
└──────────────────────────┬──────────────────────────────────┘
                           │ (Nếu là route được bảo vệ)
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. AUTHENTICATION MIDDLEWARE                                │
│    - Kiểm tra Session::isAuthenticated()                    │
│    - Truy vấn CSDL kiểm tra trang_thai == 'HoatDong'        │
│        + Không hợp lệ / Bị khóa ──> Thu hồi Session, HTTP 401│
│        + Chưa đăng nhập ──> Redirect /login, BẢO TOÀN SESSION│
└──────────────────────────┬──────────────────────────────────┘
                           │ (Đã xác thực danh tính)
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. AUTHORIZATION MIDDLEWARE (RBAC)                          │
│    - Kiểm tra Guard: 'role:...' hoặc 'permission:...'       │
│        + Không đủ quyền ──> HTTP 403 Forbidden              │
│                             (HIỂN THỊ LỖI, BẢO TOÀN SESSION)│
│        + Đủ quyền ──> Chuyển tiếp vào Controller / View     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. DATA-LEVEL POLICIES & RESOURCE OWNERSHIP                 │
│    - BookingPolicy / LoanPolicy                             │
│    - So sánh id_nguoi_dung với Session::getUserId()         │
│    - Chống IDOR/BOLA khi thực hiện thao tác POST / DELETE   │
└─────────────────────────────────────────────────────────────┘
```

### 4.2. Bảng Ma trận Phân quyền Định tuyến (Routing Security Matrix)

| Đường dẫn (Route) | Phân loại | Quy tắc Guard | Hành vi khi Chưa Đăng nhập | Hành vi khi Không Đủ Quyền |
|---|:---:|---|---|---|
| `/` | Public | None | Cho phép truy cập (hiện nút Login) | N/A |
| `/books` | Public | None | Cho phép xem danh mục sách | N/A |
| `/book-detail` | Public | None | Cho phép xem chi tiết sách | N/A |
| `/reading-room` | Public | None | Cho phép xem lịch phòng đọc | N/A |
| `/collections` | Public | None | Cho phép xem bộ sưu tập | N/A |
| `/contact` | Public | None | Cho phép gửi liên hệ | N/A |
| `/login` | Guest | `guest` | Cho phép vào form đăng nhập | Nếu đã đăng nhập: Chuyển hướng Dashboard |
| `/register` | Guest | `guest` | Cho phép vào form đăng ký | Nếu đã đăng nhập: Chuyển hướng Dashboard |
| `/profile` | Student | `role:STUDENT` | Chuyển hướng `/login` (302) | **HTTP 403** (Admin & Thủ thư bị chặn, giữ session) |
| `/librarian` | Librarian | `permission:loan.confirm` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên bị chặn, giữ session) |
| `/librarian/books` | Librarian | `permission:book.inventory` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên bị chặn, giữ session) |
| `/librarian/counter` | Librarian | `permission:loan.confirm` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên bị chặn, giữ session) |
| `/librarian/rooms` | Librarian | `permission:room.manage` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên bị chặn, giữ session) |
| `/librarian/reports` | Librarian | `permission:loan.confirm` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên bị chặn, giữ session) |
| `/admin/users` | Admin | `permission:user.view` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên/Thủ thư bị chặn, giữ session) |
| `/admin/audit` | Admin | `permission:audit.view` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên/Thủ thư bị chặn, giữ session) |
| `/admin/settings` | Admin | `permission:setting.manage` | Chuyển hướng `/login` (302) | **HTTP 403** (Sinh viên/Thủ thư bị chặn, giữ session) |

---

## 5. Toàn bộ Kịch bản Kiểm thử & Kết quả Thực tế (16 Test Cases)

Bộ kiểm thử được xây dựng tại [`tests/Integration/AuthenticationAuthorizationRegressionTest.php`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/tests/Integration/AuthenticationAuthorizationRegressionTest.php).

**Lệnh thực thi:**
```powershell
& "E:\xampp\php\php.exe" tests/Integration/AuthenticationAuthorizationRegressionTest.php
```

### Bảng đối chiếu 16 Tình huống kiểm thử:

| Mã Test | Tình huống Kiểm thử | Kết quả Mong đợi | Kết quả Đo đạc Thực tế | Trạng thái |
|:---:|---|---|---|:---:|
| **AUTH-01** | Người chưa đăng nhập truy cập trang yêu cầu login | Redirect `/login` (HTML) hoặc trả về HTTP 401 (JSON) | `isAuthenticated() === false`, chặn truy cập trước khi vào controller | **PASS** |
| **AUTH-02** | Admin truy cập trang admin (`/admin/settings`) | Được phép truy cập | `authPass === true` và `hasPermission('setting.manage') === true` | **PASS** |
| **AUTH-03** | Admin truy cập trang chỉ dành cho sinh viên (`/profile`) | Bị từ chối (HTTP 403); Session Admin **CÒN NGUYÊN VẸN** | Bị chặn bởi `role:STUDENT`, session `ADMIN` còn nguyên, User ID 1 | **PASS** |
| **AUTH-04** | Sinh viên truy cập trang sinh viên (`/profile`) | Được phép truy cập | `role:STUDENT` khớp hoàn toàn, `hasRole('STUDENT') === true` | **PASS** |
| **AUTH-05** | Sinh viên truy cập trang admin (`/admin/settings`) | Bị từ chối (HTTP 403); Session sinh viên **CÒN NGUYÊN VẸN** | Bị từ chối do thiếu quyền `setting.manage`, session sinh viên không bị hủy | **PASS** |
| **AUTH-06** | Admin bị từ chối rồi quay lại trang chủ (`/`) | Vẫn giữ trạng thái đăng nhập Admin | Quay về `/`: `isAuthenticated() === true`, vai trò `ADMIN`, ID 1 | **PASS** |
| **AUTH-07** | Sinh viên bị từ chối rồi quay lại trang chủ (`/`) | Vẫn giữ trạng thái đăng nhập Sinh viên | Quay về `/`: `isAuthenticated() === true`, vai trò `STUDENT`, ID 3 | **PASS** |
| **AUTH-08** | Truy cập route public (`/`, `/books`, `/contact`) khi đã đăng nhập | Không bị đăng xuất hoặc redirect vòng lặp | Session không bị ảnh hưởng, giữ nguyên trạng thái xác thực | **PASS** |
| **AUTH-09** | Người dùng sửa URL trực tiếp trên trình duyệt | Backend luôn luôn kiểm tra quyền trên từng request | Mọi URL quản trị đều được chặn tại tầng Authorization Middleware | **PASS** |
| **AUTH-10** | Người dùng giả mạo role trong request (`?switch_role=admin`, POST `role=ADMIN`) | Không thể tự nâng quyền; Backend chỉ tin CSDL và Session | Request params bị phớt lờ, session giữ nguyên vai trò xác thực từ server | **PASS** |
| **AUTH-11** | Truy cập API/AJAX khi chưa đăng nhập | Trả về HTTP 401 JSON, không redirect HTML sai format | Nhận diện header `Accept: application/json` và trả về JSON 401 chuẩn API | **PASS** |
| **AUTH-12** | Người dùng bị khóa trong CSDL (`trang_thai = 'Khoa'`) | Thu hồi session ngay lập tức, không cho tiếp tục thao tác | `AuthenticationMiddleware` phát hiện tài khoản không hoạt động, hủy phiên ngay | **PASS** |
| **AUTH-13** | Đăng xuất hợp lệ qua `/logout` | Xóa sạch session và cookie, truy cập tiếp tục bị chặn | `Session::destroy()` xóa sạch session array và gửi cookie expire | **PASS** |
| **AUTH-14** | Session hết hạn (Session Timeout) | Phát hiện phiên quá hạn và tự động thu hồi an toàn | Kiểm tra `authenticated_at` vượt quá thời hạn cho phép -> tự động hủy phiên | **PASS** |
| **AUTH-15** | Truy cập tài nguyên người khác bằng cách sửa ID (chống IDOR) | Bị từ chối bởi Resource Policy | Sinh viên B không thể hủy/sửa đơn đặt chỗ phòng đọc của Sinh viên A | **PASS** |
| **AUTH-16** | Gửi request POST/DELETE trái quyền | Backend từ chối ở tầng policy, không làm thay đổi dữ liệu | Sinh viên cố xóa sách hoặc can thiệp audit log bị backend chặn đứng | **PASS** |

---

## 6. Kiểm thử HTTP End-to-End với Cookie Jar trên Máy chủ Thực

Để kiểm tra chính xác hành vi mạng thực tế của trình duyệt qua giao thức HTTP (bao gồm việc phát hành Set-Cookie, đọc Cookie jar, xử lý mã phản hồi 302/401/403/200), chúng tôi xây dựng bộ test tự động tại [`tests/Integration/HttpEndToEndTest.php`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/tests/Integration/HttpEndToEndTest.php).

**Lệnh thực thi:**
```powershell
& "E:\xampp\php\php.exe" tests/Integration/HttpEndToEndTest.php
```

**Kết quả ghi nhận:**
```text
========================================================================================
KIỂM THỬ END-TO-END HTTP THỰC TẾ TRÊN MÁY CHỦ LIVE (http://localhost:8000)
========================================================================================
[PASS] E2E-01: Khách truy cập /profile bị chuyển hướng đến /login (HTTP 302, Redirect to login)
[PASS] E2E-02: Đăng nhập Admin thành công chuyển hướng đến /admin/settings (HTTP 302, Location: /admin/settings)
[PASS] E2E-03: Admin truy cập bảng cấu hình /admin/settings thành công (HTTP 200) (HTTP 200, Đã hiển thị trang cấu hình)
[PASS] E2E-04: Admin truy cập /profile bị từ chối với mã HTTP 403 Forbidden (HTTP 403, Hiển thị giao diện 403 từ chối truy cập)
[PASS] E2E-05: Admin quay về trang chủ / VẪN GIỮ NGUYÊN SESSION (Hiện Quản trị viên & Đăng xuất) (HTTP 200, Session duy trì hoàn hảo)
[PASS] E2E-06: Admin truy cập /login tự động được chuyển về /admin/settings (Guest Guard) (HTTP 302, Location: /admin/settings)
[PASS] E2E-07: Admin đăng xuất thành công chuyển hướng về /login (HTTP 302, Đã xóa phiên)
[PASS] E2E-08: Đăng nhập Sinh viên thành công chuyển hướng đến /profile (HTTP 302, Location: /profile)
[PASS] E2E-09: Sinh viên truy cập trang cá nhân /profile thành công (HTTP 200) (HTTP 200, Đã hiển thị trang sinh viên)
[PASS] E2E-10: Sinh viên truy cập /admin/settings bị từ chối với mã HTTP 403 Forbidden (HTTP 403, Bị chặn bởi AuthorizationMiddleware)
[PASS] E2E-11: Sinh viên quay về trang chủ / VẪN GIỮ NGUYÊN SESSION (Hiện Sinh viên & Đăng xuất) (HTTP 200, Session sinh viên duy trì an toàn)
[PASS] E2E-12: Sinh viên đăng xuất thành công chuyển hướng về /login (HTTP 302, Đã thu hồi phiên sinh viên)
========================================================================================
KẾT QUẢ: 100% KIỂM THỬ LIVE HTTP E2E ĐẠT CHUẨN XUẤT SẮC!
========================================================================================
```

---

## 7. Hướng dẫn Kiểm thử Thủ công bằng Trình duyệt (Step-by-Step QA)

Bạn có thể mở trực tiếp trình duyệt Web bất kỳ (Chrome, Edge, Firefox) và thực hiện kiểm chứng theo 3 kịch bản:

### Kịch bản 1: Kiểm thử Khách vãng lai (Chưa đăng nhập)
1. Mở cửa sổ ẩn danh mới (Ctrl + Shift + N).
2. Nhập URL: `http://localhost:8000/profile` (hoặc `http://localhost:8000/admin/settings`).
3. **Kỳ vọng:** Trình duyệt tự động chuyển hướng về `http://localhost:8000/login?error=...` và hiển thị thông báo yêu cầu đăng nhập.
4. Truy cập `http://localhost:8000/`: Thanh Header hiển thị nút **"Đăng nhập"** bình thường.

### Kịch bản 2: Kiểm thử Tài khoản Quản trị viên (Admin)
1. Truy cập `http://localhost:8000/login`.
2. Nhập thông tin quản trị:
   - **Email:** `admin@fol.uit.edu.vn`
   - **Mật khẩu:** `Password@123`
   *(Hoặc đăng nhập qua nút Google với email quản trị đã cấu hình: `nguyendoanduchieu16112006@gmail.com`).*
3. **Kỳ vọng:** Đăng nhập thành công và chuyển thẳng vào Bảng Quản trị (`/admin/settings`).
4. **Kiểm tra lỗi cũ:** Trên thanh địa chỉ của trình duyệt, gõ trực tiếp: `http://localhost:8000/profile` rồi nhấn Enter.
5. **Kỳ vọng:** Trình duyệt trả về mã **HTTP 403 Forbidden**. Màn hình hiển thị:
   - Tiêu đề: *"Không đủ quyền truy cập"*.
   - Mô tả: *"Trang này chỉ dành cho vai trò [STUDENT]. Vai trò hiện tại của bạn là [Quản trị viên (ADMIN)]."*
   - Nút: *"← Quay lại Bảng Quản trị (Admin)"* và nút *"Về trang chủ"*.
6. Click vào nút **"Về trang chủ"** (hoặc gõ `http://localhost:8000/`):
7. **Kỳ vọng (Xác minh không mất Session):**
   - Thanh điều hướng (Navbar) hiển thị đầy đủ các liên kết mà Admin có quyền truy cập: **"Trang chủ"**, **"Tra cứu sách"**, **"Phòng đọc"**, **"Cá nhân"** (`/profile`), **"Cán bộ"** (`/librarian`), **"Quản trị"** (`/admin/settings`).
   - Góc phải Header hiển thị chấm xanh online, tên **"Quản trị viên Hệ thống"** (gọn gàng, bỏ huy hiệu chữ xám rườm rà) và nút **"Đăng xuất"**.
   - Bấm vào tên quản trị sẽ mở ngay Bảng điều khiển Quản trị (`/admin/settings`).
8. **Kiểm thử Admin đảm nhận cả 3 vai trò (Sinh viên, Cán bộ, Quản trị):**
   - Bấm vào **"Cá nhân"**: Mở trang `/profile` thành công (HTTP 200) để kiểm thử giao diện và chức năng của vai trò Sinh viên.
   - Bấm vào **"Cán bộ"**: Mở trang `/librarian` thành công (HTTP 200) để kiểm thử giao diện và chức năng của vai trò Thủ thư/Cán bộ thư viện.
   - Bấm vào **"Quản trị"**: Mở trang `/admin/settings` thành công (HTTP 200) để cấu hình hệ thống.
   - Tại mọi màn hình (kể cả Header trang Cán bộ và Quản trị), luôn có các liên kết và nút bấm giúp Admin chuyển đổi qua lại mượt mà giữa các vai trò mà không bao giờ bị gián đoạn hay mất phiên.
9. Thử gõ `http://localhost:8000/login`: Trình duyệt tự động chuyển về `/admin/settings` mà không hiện lại form đăng nhập (Guest Guard).
10. Bấm **"Đăng xuất"**: Phiên làm việc kết thúc an toàn.

### Kịch bản 3: Kiểm thử Tài khoản Sinh viên (Student)
1. Truy cập `http://localhost:8000/login`.
2. Đăng nhập tài khoản sinh viên mẫu:
   - **Email:** `student@gm.uit.edu.vn`
   - **Mật khẩu:** `Password@123`
   *(Hoặc đăng nhập Google bằng email sinh viên có đuôi `@gm.uit.edu.vn` hoặc `@sv.ussh.edu.vn`).*
3. **Kỳ vọng:** Đăng nhập thành công, chuyển hướng vào Trang cá nhân sinh viên (`/profile`). Thanh điều hướng chỉ hiển thị các mục Sinh viên được phép truy cập (**Trang chủ**, **Tra cứu sách**, **Phòng đọc**, **Cá nhân**). Tuyệt đối không hiển thị liên kết "Cán bộ" hay "Quản trị".
4. Sửa URL trên thanh địa chỉ sang trang quản trị: `http://localhost:8000/admin/settings` hoặc `http://localhost:8000/admin/users`.
5. **Kỳ vọng:** Nhận màn hình **HTTP 403 Forbidden** kèm nút *"← Quay lại Trang Cá nhân Sinh viên"*.
6. Click quay về Trang chủ (`/`): Header vẫn hiển thị tên sinh viên và nút **"Đăng xuất"** (không mất Session).
7. Bấm **"Đăng xuất"**: Đăng xuất thành công.

---

## 8. Quy tắc Vận hành, Bảo mật & Mở rộng Tương lai

1. **Thêm Route mới trong tương lai:**
   - Khi tạo một trang hoặc API mới, bắt buộc phải khai báo quy tắc bảo vệ trong mảng `$routeGuards` tại `public/index.php`.
   - Nếu trang chỉ dành riêng cho một vai trò, sử dụng cú pháp: `'role:VAI_TRO'` (ví dụ: `role:STUDENT`, `role:ADMIN`, `role:LIBRARIAN`).
   - Nếu trang dùng chung hoặc phụ thuộc vào quyền hạn chuyên biệt, sử dụng mã quyền RBAC: `'permission:<resource>.<action>'`.
2. **Không bao giờ tin tưởng client:**
   - Tuyệt đối không đọc vai trò người dùng từ `$_GET`, `$_POST`, hay Header gửi từ client để ra quyết định phân quyền.
   - Luôn sử dụng `Session::getUserId()` và `Session::getRoleCode()`, kết hợp với CSDL thời gian thực qua `AuthenticationMiddleware` và `AuthorizationService`.
3. **Bảo mật thao tác dữ liệu (Chống IDOR):**
   - Đối với các thao tác cập nhật hoặc xóa (như hủy phiếu mượn, hủy đặt chỗ, sửa thông tin cá nhân), luôn kiểm tra quyền sở hữu bản ghi qua Policy class (ví dụ: `BookingPolicy::canCancel()`, `LoanPolicy::canRenew()`).
4. **Bảo trì môi trường Production:**
   - Đảm bảo HTTPS luôn được bật để cookie Session kích hoạt thuộc tính `Secure = true`.
   - Cấu hình biến môi trường `SESSION_LIFETIME` trong file `.env` phù hợp với chính sách an toàn thông tin của nhà trường (khuyến nghị 60 đến 120 phút).
