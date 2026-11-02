# BẢNG KIỂM TRA BẢO MẬT & TOÀN VẸN HỆ THỐNG (SECURITY & INTEGRITY CHECKLIST)
## Dự án: Hệ thống Thư viện Khoa Văn học (FoL Digital Library)
**Trạng thái kiểm tra:** Hoàn tất 100% (35/35 vấn đề)  
**Tỷ lệ đạt chuẩn:** 35/35 (0 UNRESOLVED)  

---

## Bảng Theo dõi Chi tiết 35 Vấn đề

### Nhóm A: Đồng thời & Toàn vẹn Dữ liệu (Concurrency & Data Integrity)

| STT | Vấn đề kiểm tra | Phân loại | Tệp tin & Dòng mã minh chứng | Bằng chứng xác thực (Concrete Evidence) |
|---|---|---|---|---|
| 1 | Race Condition / Overbooking | **FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql:10-18`, `app/Repositories/RoomRepository.php:191-204` | Virtual column `active_seat_booking` sinh mã duy nhất `seat_shift_date` khi ở trạng thái `DaDat`/`DaNhanCho`. Ràng buộc `uq_active_seat_booking` đảm bảo ở mức MariaDB Engine cấm tuyệt đối 2 người cùng đặt 1 ghế trong cùng 1 ca. Đã kiểm thử tự động tại `tests/Unit/ConcurrencyBookingTest.php:111` (Bắt lỗi 1062). |
| 2 | Duplicate Submission | **FIXED** | `app/Controllers/RoomBookingController.php:38-51` | Hỗ trợ `Idempotency-Key` (Header hoặc JSON payload). Nếu client gửi lại cùng mã idempotency trong vòng 120s, hệ thống trả lại kết quả đã cache từ trước mà không ghi thêm vào CSDL. |
| 3 | Ghost Booking | **FIXED** | `app/Services/BookingExpirationService.php:18-35`, `bin/expire_bookings.php:15-32` | CLI worker quét tự động `expireNoShowBookings()`: Chuyển các lượt đặt trước có giờ bắt đầu ca đọc đã qua (`startAt <= $now`) sang trạng thái `QuaHanKhongDen`, tự động giải phóng ghế cho sinh viên khác. |
| 4 | Time Overlap | **FIXED** | `app/Repositories/RoomRepository.php:166-170`, `database/schema.sql:350-370` | Ca đọc `ca_doc` có giờ bắt đầu và kết thúc cố định (`gio_bat_dau < gio_ket_thuc`). Backend kiểm tra `startAt <= $now` từ chối đặt ca đã qua giờ. Cột sinh `active_seat_booking` chặn tuyệt đối chồng lấn thời gian. |
| 5 | Lost Update | **FIXED** | `app/Repositories/BookRepository.php:264`, `app/Repositories/LoanRepository.php:306-315` | Cập nhật sách sử dụng Pessimistic Locking: `SELECT id_cuon_sach FROM cuon_sach WHERE id_cuon_sach = :copy_id FOR UPDATE` ngăn ngừa 2 tiến trình đồng thời đọc và ghi đè trạng thái. |
| 6 | Deadlock | **FIXED** | `app/Core/Database.php:85-127` | Triển khai `Database::transactionWithRetry(callable $callback, int $maxRetries = 3)`. Bắt các mã lỗi MariaDB 1213, 1205 và SQLSTATE 40001, tự động lùi bước với Exponential Backoff & Jitter ngẫu nhiên. Đã kiểm thử tại `ConcurrencyBookingTest.php:180`. |
| 7 | Inconsistent State | **FIXED** | `app/Repositories/UserRepository.php:97-150`, `app/Repositories/LoanRepository.php:276-382` | Các thao tác ghi đồng thời nhiều bảng được bọc 100% trong `beginTransaction()` và `commit()`, tự động `rollBack()` khi gặp bất kỳ `Throwable` nào. |
| 8 | State Transition Conflicts | **FIXED** | `app/Repositories/RoomRepository.php:340-380`, `app/Repositories/LoanRepository.php:408-415` | Kiểm tra điều kiện trạng thái hiện tại trước khi chuyển trạng thái: `cancelBooking` chỉ cho phép khi trạng thái là `DaDat`, `returnBook` chỉ cho phép khi trạng thái là `DangMuon` (ném `DomainException` nếu đã trả). |
| 9 | Orphan Records | **FIXED** | `app/Repositories/BookRepository.php:336-348`, `database/schema.sql` | `BookRepository::delete()` kiểm tra `chi_tiet_muon` trước khi xóa: Nếu đầu sách đã có lịch sử mượn, từ chối xóa và yêu cầu chỉ chuyển trạng thái sang ngưng phục vụ để bảo vệ toàn vẹn lịch sử. |

---

### Nhóm B: Bảo mật & Phân quyền (Security & Authorization)

| STT | Vấn đề kiểm tra | Phân loại | Tệp tin & Dòng mã minh chứng | Bằng chứng xác thực (Concrete Evidence) |
|---|---|---|---|---|
| 10 | SQL Injection | **ALREADY_SAFE** | `app/Repositories/*.php` | 100% câu lệnh SQL sử dụng PDO Prepared Statements với tham số ràng buộc (`:id`, `:email`, `:date`). Không có bất kỳ vị trí nối chuỗi biến người dùng trực tiếp vào câu lệnh SQL. Đã kiểm thử payload UNION/OR 1=1 tại `SecurityHardeningTest.php:98`. |
| 11 | XSS | **ALREADY_SAFE** | `resources/views/**/*.php` | 100% biến đầu ra trong views được escape qua `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`. |
| 12 | CSRF | **FIXED** | `app/Core/Session.php:171-201`, `public/index.php:149-153` | Sinh token CSRF 64 ký tự hex ngẫu nhiên (`bin2hex(random_bytes(32))`), so sánh an toàn bằng `hash_equals()`. Đã nhúng vào form Login, Onboarding, Duyệt thẻ và AJAX header `X-CSRF-TOKEN`. |
| 13 | IDOR | **ALREADY_SAFE** | `app/Controllers/RoomBookingController.php:73`, `app/Repositories/LoanRepository.php:26` | Định danh người dùng luôn được lấy từ `Session::getUserId()`. API không nhận `user_id` từ phía client để xác định quyền sở hữu tài nguyên. |
| 14 | Broken Access Control | **ALREADY_SAFE** | `app/Middleware/AuthorizationMiddleware.php:20-45` | Kiểm tra quyền truy cập ở cấp độ Router/Middleware backend dựa trên danh sách trắng quyền (`roles`). Người dùng không đủ quyền bị chặn ngay tại tầng mạng với HTTP 403 Forbidden. |
| 15 | Session Hijacking / Fixation | **FIXED** | `app/Core/Session.php:29-45` | Kích hoạt `session.use_strict_mode = 1`, cookie params `HttpOnly = true`, `SameSite = Lax`, gọi `session_regenerate_id(true)` khi đăng nhập thành công. |
| 16 | Password Security | **ALREADY_SAFE** | `app/Controllers/AuthController.php:40-60` | Mật khẩu được băm bằng `password_hash($password, PASSWORD_BCRYPT)` và xác thực bằng `password_verify()`. Không lưu plaintext mật khẩu. |
| 17 | Mass Assignment | **ALREADY_SAFE** | `app/Repositories/UserRepository.php:180-220` | Whitelist rõ ràng các trường cho phép cập nhật (`ho_ten`, `ngay_sinh`, `gioi_tinh`, `so_dien_thoai`). Không cho phép can thiệp vào `id_vai_tro`, `email`, `trang_thai`. |
| 18 | File Upload Vulnerabilities | **FIXED** | `app/Services/ImageOptimizerService.php:45-90`, `storage/uploads/.htaccess:1-12` | Xác thực MIME type, quét magic bytes, re-encode toàn bộ ảnh sang định dạng WebP qua thư viện GD; cấu hình `.htaccess` chặn thực thi script PHP trong thư mục lưu trữ. |

---

### Nhóm C: Logic Nghiệp vụ Thư viện (Library Business Logic)

| STT | Vấn đề kiểm tra | Phân loại | Tệp tin & Dòng mã minh chứng | Bằng chứng xác thực (Concrete Evidence) |
|---|---|---|---|---|
| 19 | Borrow Limit Violation | **FIXED** | `app/Repositories/LoanRepository.php:297-299` | Kiểm tra `$this->getActiveLoansCount($userId) >= 3`. Ném `DomainException('Độc giả đã mượn tối đa 3 cuốn sách...')`. Đã kiểm thử tự động tại `LibraryBusinessLogicTest.php:76-90`. |
| 20 | Duplicate Active Loan | **FIXED** | `app/Repositories/LoanRepository.php:319-331` | Khóa cuốn sách bằng `FOR UPDATE`, kiểm tra trạng thái `SanSang` và xác minh không có bản ghi `chi_tiet_muon` dở dang (`DangMuon`, `ChoNhan`). Đã kiểm thử tại `LibraryBusinessLogicTest.php:95-115`. |
| 21 | Invalid Book Return | **FIXED** | `app/Repositories/LoanRepository.php:407-430` | Chỉ cho phép trả sách khi trạng thái là `DangMuon`. Tính tiền phạt trễ hạn tự động (1.000 VNĐ/ngày). Trả lần 2 ném ngoại lệ cuốn sách đã được trả. Đã kiểm thử tại `LibraryBusinessLogicTest.php:145-170`. |
| 22 | Incorrect Due Date | **FIXED** | `app/Repositories/LoanRepository.php:350` | Tính toán thời hạn chuẩn xác: `date('Y-m-d', strtotime('+14 days'))` theo đúng quy định thư viện. |
| 23 | Invalid Book Renewal | **FIXED** | `app/Repositories/LoanRepository.php:485-520` | Giới hạn tối đa 1 lần gia hạn (`so_lan_gia_han < 1`), tăng thêm 7 ngày hạn trả (`han_tra + 7 days`). Chặn gia hạn nếu sách đã quá hạn. Đã kiểm thử tại `LibraryBusinessLogicTest.php:120-140`. |
| 24 | No-show Booking | **FIXED** | `app/Repositories/RoomRepository.php:445-520`, `bin/expire_bookings.php` | Thêm phương thức check-in tại quầy/phòng đọc và cơ chế tự động quét chuyển sang `QuaHanKhongDen` nếu sinh viên không đến nhận chỗ. |
| 25 | Invalid Cancellation | **FIXED** | `app/Repositories/RoomRepository.php:345-360` | Chỉ cho phép tài khoản chủ sở hữu hủy lượt đặt khi còn tối thiểu 24 giờ trước giờ bắt đầu ca đọc và trạng thái là `DaDat`. |
| 26 | User Booking Conflict | **FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql:20-28` | Cột sinh ảo `active_user_shift` kết hợp `UNIQUE KEY uq_active_user_shift` trên bảng `dat_cho` ngăn chặn cùng 1 sinh viên đặt nhiều chỗ trong cùng 1 ca đọc. Đã kiểm thử tại `ConcurrencyBookingTest.php:75-105`. |
| 27 | Invalid Operating Hours | **ALREADY_SAFE** | `app/Repositories/RoomRepository.php:152-170` | Đặt chỗ được gắn cố định với bảng `ca_doc` có giờ bắt đầu và kết thúc cụ thể; từ chối mọi yêu cầu đặt chỗ vào ca đã trôi qua. |

---

### Nhóm D: Cơ sở dữ liệu & Hiệu năng (Database & Performance)

| STT | Vấn đề kiểm tra | Phân loại | Tệp tin & Dòng mã minh chứng | Bằng chứng xác thực (Concrete Evidence) |
|---|---|---|---|---|
| 28 | Missing Indexes | **FIXED** | `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql:30-40` | Bổ sung 4 chỉ mục phức hợp: `idx_cuon_sach_dau_trang_thai`, `idx_ctm_cuon_trang_thai`, `idx_ctm_trang_thai_han`, `idx_audit_time`. |
| 29 | N+1 Queries | **FIXED** | `app/Repositories/LoanRepository.php:175-235` | Thay thế vòng lặp 30 truy vấn riêng lẻ trong `getReportsSummary()` bằng 2 truy vấn gộp nhóm qua `IN (...) GROUP BY dt`. Đã kiểm thử tại `LibraryBusinessLogicTest.php:180-190`. |
| 30 | Unbounded Queries | **ALREADY_SAFE** | `app/Repositories/BookRepository.php:75-130` | Toàn bộ danh mục đầu sách áp dụng phân trang máy chủ (Server-Side Pagination) với `LIMIT :limit OFFSET :offset`. |
| 31 | Inefficient SQL | **FIXED** | `app/Repositories/RoomRepository.php:189-204` | Sử dụng `NOT EXISTS` có chỉ mục thay thế cho phép quét bảng toàn bộ (Table Scan), chỉ lấy các cột cần thiết (`id_cho_ngoi`, `ma_cho_ngoi`). |
| 32 | Cache Invalidation | **ALREADY_SAFE** | `app/Core/RateLimiter.php:40-75` | Bộ đệm RateLimiter tự động hết hạn và xóa các mục cũ theo cửa sổ trượt (Sliding-Window Decay). |
| 33 | Connection Management | **FIXED** | `app/Core/Database.php:85-127` | Đảm bảo 100% Transaction được Commit hoặc Rollback an toàn; tự động đóng kết nối khi kết thúc phiên làm việc. |

---

### Nhóm E: Vận hành & Độ tin cậy (Reliability & Operations)

| STT | Vấn đề kiểm tra | Phân loại | Tệp tin & Dòng mã minh chứng | Bằng chứng xác thực (Concrete Evidence) |
|---|---|---|---|---|
| 34 | Timezone Mismatch | **FIXED** | `config/database.php:24`, `app/Core/Database.php:55` | Đồng bộ hóa `time_zone = '+07:00'` trực tiếp trong tùy chọn kết nối PDO MySQL; khớp hoàn toàn với cấu hình múi giờ PHP `Asia/Ho_Chi_Minh`. |
| 35 | Rate Limiting | **FIXED** | `app/Core/RateLimiter.php:15-85`, `public/index.php:140-147` | Giới hạn đăng nhập tối đa 5 lần/phút/IP; giới hạn gửi duyệt thẻ và đặt phòng tối đa 10 lần/phút. Trả về thông báo thời gian chờ cụ thể. Đã kiểm thử tại `SecurityHardeningTest.php:55-80`. |
