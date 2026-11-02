# BÁO CÁO KIỂM TOÁN HỆ THỐNG TOÀN DIỆN (SYSTEM AUDIT REPORT)
## Dự án: Hệ thống Thư viện Khoa Văn học (FoL Digital Library)
**Ngày kiểm toán:** 02/11/2026  
**Vai trò thực hiện:** Senior Backend Engineer, Database Engineer & Application Security Engineer  
**Môi trường:** PHP 8.2.12 (WINNT), MariaDB 10.4.32, Apache / Built-in CLI Server  

---

## 1. Kiến trúc Hệ thống Được Kiểm toán

Hệ thống Thư viện Số FoL Digital Library được xây dựng theo mô hình **PHP MVC thuần (Custom MVC Framework)** kết hợp cơ sở dữ liệu quan hệ MySQL/MariaDB:
- **Tầng Điều khiển (Controllers):** `AuthController`, `BookController`, `LibrarianController`, `OnboardingController`, `ProfileController`, `RoomBookingController`, `AdminController`.
- **Tầng Nghiệp vụ (Services):** `AuthorizationService`, `GoogleAuthService`, `ImageOptimizerService`, `RoomBookingService`, `BookingExpirationService`.
- **Tầng Lưu trữ & Dữ liệu (Repositories):** `BookRepository`, `UserRepository`, `RoomRepository`, `LoanRepository`, `DashboardRepository`.
- **Tầng Hạ tầng Cốt lõi (Core):** `Database` (PDO Singleton with Deadlock Retry), `Session` (Strict Mode & CSRF Management), `RateLimiter` (Sliding-window IP/Account Throttling), `Autoloader`, `View`.
- **Tầng Middleware:** `AuthMiddleware`, `AuthorizationMiddleware`, `GuestMiddleware`.
- **Cơ sở dữ liệu (Database Schema):** MariaDB `fol_library_db` chuẩn hóa 3NF với 16 bảng, hỗ trợ khóa ngoại, ràng buộc độc nhất (Unique Constraints), cột sinh ảo (Virtual Generated Columns) và chỉ mục phức hợp (Composite Indexes).

---

## 2. Phân loại và Kết quả Kiểm toán 35 Vấn đề

### Nhóm A: Đồng thời & Toàn vẹn Dữ liệu (Concurrency & Data Integrity) - P0 Critical

| ID | Vấn đề | Phân loại | Tệp tin & Vị trí liên quan | Nguyên nhân gốc rễ | Giải pháp đã triển khai |
|---|---|---|---|---|---|
| 1 | Race Condition / Overbooking | **CONFIRMED -> FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql`, `app/Repositories/RoomRepository.php` | Khi 2 sinh viên đặt cùng 1 ghế tại cùng 1 ca đọc đồng thời, ứng dụng chỉ kiểm tra `SELECT` trước `INSERT`, dẫn đến Phantom Read & overbooking cùng ghế. | Tạo Virtual Generated Column `active_seat_booking` và `UNIQUE KEY uq_active_seat_booking` trên `dat_cho`; Khóa `cho_ngoi` bằng `FOR UPDATE`. |
| 2 | Duplicate Submission | **CONFIRMED -> FIXED** | `app/Controllers/RoomBookingController.php` | Người dùng nhấp đúp hoặc mạng lag gửi 2 yêu cầu POST liên tiếp; trước đây chỉ dựa vào JS vô hiệu hóa nút bấm. | Bổ sung hỗ trợ `Idempotency-Key` (Header & Payload) với cache phản hồi; Ràng buộc Unique Key ở cấp độ CSDL MariaDB. |
| 3 | Ghost Booking | **CONFIRMED -> FIXED** | `app/Services/BookingExpirationService.php`, `bin/expire_bookings.php`, `app/Repositories/RoomRepository.php` | Đặt chỗ trước nhưng sinh viên không đến nhận chỗ (`DaDat`), làm chiếm dụng chỗ ngồi mà không có cơ chế tự động hủy quá hạn. | Thêm `expireNoShowBookings()`, service `BookingExpirationService` và CLI worker `bin/expire_bookings.php` chạy định kỳ qua Cron/Task Scheduler. |
| 4 | Time Overlap | **CONFIRMED -> FIXED** | `app/Repositories/RoomRepository.php`, `app/Services/RoomBookingService.php` | Ca đọc phân mảnh có thể bị trùng giờ nếu thời gian kết thúc <= thời gian bắt đầu. | Kiểm tra ràng buộc ca đọc và xác thực `ngay_dat` tương lai; Ràng buộc `ca_doc` bắt buộc `gio_bat_dau < gio_ket_thuc`. |
| 5 | Lost Update | **CONFIRMED -> FIXED** | `app/Repositories/BookRepository.php`, `app/Repositories/LoanRepository.php` | Nhiều thủ thư cùng cập nhật số lượng/trạng thái cuốn sách hoặc bạn đọc cùng mượn 1 cuốn sách dẫn đến ghi đè dữ liệu. | Áp dụng Pessimistic Locking (`SELECT ... FOR UPDATE`) khi đọc và cập nhật trạng thái `cuon_sach`. |
| 6 | Deadlock | **CONFIRMED -> FIXED** | `app/Core/Database.php`, `app/Repositories/RoomRepository.php` | Các truy vấn đồng thời lock tài nguyên khác thứ tự có thể phát sinh mã lỗi MariaDB 1213 hoặc SQLSTATE 40001. | Xây dựng `Database::transactionWithRetry(callable $callback, int $maxRetries = 3)` với Exponential Backoff & Jitter. |
| 7 | Inconsistent State | **CONFIRMED -> FIXED** | `app/Repositories/UserRepository.php`, `app/Repositories/LoanRepository.php` | Tạo tài khoản kèm hồ sơ hoặc mượn sách gồm nhiều câu lệnh INSERT/UPDATE riêng lẻ không bọc trong Transaction. | Bọc toàn bộ các thao tác liên bảng (`tai_khoan` + `ho_so_nguoi_dung`, `phieu_muon` + `chi_tiet_muon` + `cuon_sach`) trong CSDL Transaction. |
| 8 | State Transition Conflicts | **CONFIRMED -> FIXED** | `app/Repositories/RoomRepository.php`, `app/Repositories/LoanRepository.php` | Cho phép cập nhật trạng thái không hợp lệ (ví dụ: hủy lượt đặt đã hoàn tất, trả sách chưa từng mượn). | Ràng buộc điều kiện nghiêm ngặt trong mệnh đề `WHERE ... AND trang_thai = 'DaDat'` và kiểm tra `rowCount()`. |
| 9 | Orphan Records | **CONFIRMED -> FIXED** | `database/schema.sql`, `app/Repositories/BookRepository.php` | Xóa đầu sách khi đã có lịch sử mượn trong quá khứ dẫn đến mất dấu vết kiểm toán hoặc lỗi khóa ngoại mồ côi. | Bổ sung kiểm tra lịch sử giao dịch trong `BookRepository::delete()`: Không cho phép xóa đầu sách đã phát sinh lượt mượn. |

---

### Nhóm B: Bảo mật & Phân quyền (Security & Authorization) - P0 Critical

| ID | Vấn đề | Phân loại | Tệp tin & Vị trí liên quan | Nguyên nhân gốc rễ | Giải pháp đã triển khai |
|---|---|---|---|---|---|
| 10 | SQL Injection | **SAFE & HARDENED** | `app/Repositories/*.php` | Ghép chuỗi truy vấn trực tiếp là nguyên nhân hàng đầu gây lộ dữ liệu. | 100% câu lệnh CSDL trong toàn bộ dự án sử dụng PDO Prepared Statements với bound parameters. |
| 11 | XSS (Cross-Site Scripting) | **SAFE & HARDENED** | `resources/views/**/*.php` | Dữ liệu đầu vào từ người dùng hiển thị trực tiếp ra HTML mà không escape. | 100% dữ liệu hiển thị được bọc qua `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. |
| 12 | CSRF (Cross-Site Request Forgery) | **CONFIRMED -> FIXED** | `app/Core/Session.php`, `public/index.php`, `app/Controllers/*.php` | Các form POST (Login, Onboarding, Room Booking, Quản lý sách, Duyệt thẻ) trước đây chưa có CSRF Token xác thực backend. | Triển khai `Session::getCsrfToken()`, `Session::validateCsrfToken()` bằng `hash_equals()`; nhúng hidden token & header `X-CSRF-TOKEN`. |
| 13 | IDOR (Insecure Direct Object Reference) | **SAFE & HARDENED** | `app/Controllers/RoomBookingController.php`, `app/Repositories/LoanRepository.php` | Tin cậy ID người dùng gửi từ Client thay vì lấy từ Session. | 100% định danh người dùng được trích xuất an toàn từ `Session::getUserId()`, cấm ghi đè ID từ request payload. |
| 14 | Broken Access Control | **SAFE & HARDENED** | `app/Middleware/AuthorizationMiddleware.php`, `app/Services/AuthorizationService.php` | Kiểm tra quyền chỉ ở frontend hoặc thiếu kiểm tra quyền ở API endpoint. | Phân quyền RBAC 3 cấp (`STUDENT`, `LIBRARIAN`, `ADMIN`) bắt buộc ở router và middleware; từ chối truy cập với HTTP 403. |
| 15 | Session Hijacking / Fixation | **CONFIRMED -> FIXED** | `app/Core/Session.php` | Không bật Strict Mode và không thiết lập cờ bảo mật cookie chuẩn (`HttpOnly`, `SameSite`, `Secure`). | Cấu hình `ini_set('session.use_strict_mode', '1')`, cookie params `HttpOnly=true`, `SameSite=Lax`, tái tạo session ID khi đăng nhập/đăng xuất. |
| 16 | Password Security | **SAFE** | `app/Controllers/AuthController.php` | Lưu trữ mật khẩu dạng rõ hoặc băm yếu (MD5/SHA1). | Sử dụng thuật toán chuẩn `password_hash($password, PASSWORD_BCRYPT)` và `password_verify()`. |
| 17 | Mass Assignment | **SAFE** | `app/Repositories/UserRepository.php`, `app/Controllers/LibrarianController.php` | Nhận trực tiếp `$_POST` vào câu lệnh UPDATE khiến người dùng tự nâng quyền vai trò (`vai_tro`). | Khai báo whitelist cụ thể từng trường dữ liệu cho phép cập nhật, loại trừ hoàn toàn trường nhạy cảm như `id_vai_tro`, `email`. |
| 18 | File Upload Vulnerabilities | **CONFIRMED -> FIXED** | `app/Services/ImageOptimizerService.php`, `storage/.htaccess`, `storage/uploads/.htaccess` | Tệp tải lên có thể bị chèn mã thực thi PHP/CGI hoặc vượt dung lượng/MIME giả mạo. | Kiểm tra magic bytes + GD library resize/re-encode sang WebP nguyên bản; tạo `.htaccess` chặn thực thi script trong thư mục uploads. |

---

### Nhóm C: Logic Nghiệp vụ Thư viện (Library Business Logic) - P1 High

| ID | Vấn đề | Phân loại | Tệp tin & Vị trí liên quan | Nguyên nhân gốc rễ | Giải pháp đã triển khai |
|---|---|---|---|---|---|
| 19 | Borrow Limit Violation | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Chưa có ràng buộc giới hạn số sách tối đa một sinh viên được phép mượn cùng lúc. | Thiết lập giới hạn tối đa 3 cuốn; kiểm tra số sách đang mượn (`DangMuon`) bên trong Transaction trước khi tạo phiếu mới. |
| 20 | Duplicate Active Loan | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Hai bạn đọc mượn cùng một cuốn sách vật lý (`id_cuon_sach`) khi trạng thái chưa được cập nhật kịp thời. | Khóa cuốn sách bằng `SELECT ... FOR UPDATE`, kiểm tra trạng thái `SanSang` và xác nhận không có bản ghi `chi_tiet_muon` dở dang. |
| 21 | Invalid Book Return | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Cho phép thao tác trả sách với cuốn sách chưa mượn hoặc đã trả rồi. | Xác thực trạng thái `chi_tiet_muon.trang_thai = 'DangMuon'`; tính tiền phạt trễ hạn tự động (1.000 VNĐ/ngày); chuyển sách về `SanSang`. |
| 22 | Incorrect Due Date | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Chưa chuẩn hóa thời gian mượn mặc định theo quy định thư viện. | Thiết lập thời hạn mượn chuẩn xác 14 ngày (`CURDATE() + 14 days`). |
| 23 | Invalid Book Renewal | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Cho phép bạn đọc gia hạn không giới hạn hoặc gia hạn khi sách đã bị quá hạn. | Giới hạn tối đa 1 lần gia hạn (+7 ngày); chặn gia hạn nếu sách đã quá hạn (`han_tra < CURDATE()`). |
| 24 | No-show Booking | **CONFIRMED -> FIXED** | `app/Repositories/RoomRepository.php`, `app/Services/BookingExpirationService.php` | Bạn đọc đặt chỗ nhưng không đến nhận chỗ, gây lãng phí tài nguyên phòng đọc. | Thêm quy trình Check-in (`checkinBooking`) và quét hủy tự động các lượt đặt quá hạn (`QuaHanKhongDen`). |
| 25 | Invalid Cancellation | **CONFIRMED -> FIXED** | `app/Repositories/RoomRepository.php` | Người dùng cố hủy lượt đặt của người khác hoặc hủy lượt đặt đã hoàn tất/đã nhận chỗ. | Xác thực quyền sở hữu `id_nguoi_dung` và chỉ cho phép hủy khi trạng thái là `DaDat` và trước giờ ca đọc tối thiểu 24h. |
| 26 | User Booking Conflict | **CONFIRMED -> FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql`, `app/Repositories/RoomRepository.php` | Một bạn đọc đặt cùng lúc nhiều ghế trong cùng 1 ca đọc của cùng 1 ngày. | Bổ sung cột sinh ảo `active_user_shift` và ràng buộc độc nhất `UNIQUE KEY uq_active_user_shift` trên bảng `dat_cho`. |
| 27 | Invalid Operating Hours | **SAFE** | `app/Repositories/RoomRepository.php`, `app/Services/RoomBookingService.php` | Đặt chỗ vào các khung giờ ngoài ca hoạt động của thư viện. | Hệ thống quản lý chỗ ngồi thông qua bảng `ca_doc` cố định (`Sang`, `Chieu`), từ chối mọi ngày quá khứ hoặc ca đã qua. |

---

### Nhóm D: Cơ sở dữ liệu & Hiệu năng (Database & Performance) - P1-P2

| ID | Vấn đề | Phân loại | Tệp tin & Vị trí liên quan | Nguyên nhân gốc rễ | Giải pháp đã triển khai |
|---|---|---|---|---|---|
| 28 | Missing Indexes | **CONFIRMED -> FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql` | Thiếu composite index trên các bảng có tần suất truy vấn lớn: `cuon_sach`, `chi_tiet_muon`, `dat_cho`. | Tạo các composite index: `idx_cuon_sach_dau_trang_thai`, `idx_ctm_cuon_trang_thai`, `idx_ctm_trang_thai_han`, `idx_audit_time`. |
| 29 | N+1 Queries | **CONFIRMED -> FIXED** | `app/Repositories/LoanRepository.php` | Hàm `getReportsSummary()` thực hiện 2 truy vấn đếm trong vòng lặp 15 ngày (tổng cộng 30 queries riêng lẻ). | Thay thế hoàn toàn bằng 2 truy vấn gộp nhóm duy nhất (`WHERE dt IN (...) GROUP BY dt`). |
| 30 | Unbounded Queries | **SAFE & HARDENED** | `app/Repositories/BookRepository.php` | Tải toàn bộ danh mục sách hoặc lịch sử mượn không giới hạn khiến tràn bộ nhớ. | 100% danh sách lớn áp dụng Server-Side Pagination qua `LIMIT :limit OFFSET :offset`. |
| 31 | Inefficient SQL | **CONFIRMED -> FIXED** | `app/Repositories/RoomRepository.php`, `app/Repositories/LoanRepository.php` | Truy vấn kiểm tra chỗ trống sử dụng lồng nhau phức tạp không tận dụng index. | Tối ưu hóa điều kiện `NOT EXISTS` kết hợp composite index, chỉ chọn các cột thực sự cần thiết. |
| 32 | Cache Invalidation | **SAFE** | `app/Core/RateLimiter.php` | Dữ liệu phiên và cache rate limit bị lưu trữ vô thời hạn. | Triển khai cơ chế Sliding-Window với thời gian hết hạn tự động (decay seconds). |
| 33 | Connection Management | **CONFIRMED -> FIXED** | `app/Core/Database.php` | Kết nối PDO không rollback khi gặp ngoại lệ bất ngờ, hoặc treo kết nối transaction kéo dài. | Chuẩn hóa `transactionWithRetry` và khối `try ... catch (\Throwable)` đảm bảo tự động rollback trong mọi trường hợp. |

---

### Nhóm E: Vận hành & Độ tin cậy (Reliability & Operations) - P1-P2

| ID | Vấn đề | Phân loại | Tệp tin & Vị trí liên quan | Nguyên nhân gốc rễ | Giải pháp đã triển khai |
|---|---|---|---|---|---|
| 34 | Timezone Mismatch | **CONFIRMED -> FIXED** | `config/database.php`, `app/Core/Database.php` | Múi giờ MySQL khác múi giờ PHP (UTC vs Asia/Ho_Chi_Minh) dẫn đến sai lệch lịch đặt phòng và thời hạn mượn. | Cấu hình `time_zone = '+07:00'` đồng bộ trực tiếp khi khởi tạo kết nối PDO; PHP thiết lập múi giờ `Asia/Ho_Chi_Minh`. |
| 35 | Rate Limiting | **CONFIRMED -> FIXED** | `app/Core/RateLimiter.php`, `public/index.php`, `app/Controllers/RoomBookingController.php` | Chưa có cơ chế giới hạn tần suất yêu cầu trên backend, nguy cơ bị Brute-force mật khẩu hoặc Spam đặt chỗ. | Xây dựng bộ RateLimiter chuẩn: giới hạn đăng nhập 5 lần/phút/IP, đặt chỗ 10 lần/phút/user, trả mã phản hồi chờ tương ứng. |

---

## 3. Rủi ro còn lại và Kiến nghị Vận hành (Operational Recommendations)
1. **Thiết lập Cronjob / Windows Task Scheduler:** Khuyến nghị cấu hình chạy script `bin/expire_bookings.php` mỗi 15 phút một lần để tự động quét hủy các lượt đặt chỗ quá hạn.
2. **Cấu hình HTTPS môi trường Production:** Khi đưa lên production, đảm bảo máy chủ kích hoạt SSL/TLS để cờ cookie `secure = true` phát huy tối đa tác dụng chống đánh cắp phiên trên đường truyền.
