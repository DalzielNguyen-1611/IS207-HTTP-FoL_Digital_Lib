# NHẬT KÝ THAY ĐỔI & VÁ LỖI HỆ THỐNG (FIX CHANGELOG)
## Dự án: Hệ thống Thư viện Khoa Văn học (FoL Digital Library)
**Phiên bản:** v1.2.0 Hardened Release  
**Ngày cập nhật:** 02/11/2026  

---

## 1. Tệp tin được tạo mới (Created Files)

| Đường dẫn tệp | Mục đích & Trách nhiệm |
|---|---|
| `database/migrations/2026_11_02_000008_system_hardening_and_integrity.sql` | Migration an toàn cấp CSDL: Thêm Virtual Generated Columns (`active_seat_booking`, `active_user_shift`), Unique Constraints chống overbooking/xung đột lịch và 4 Composite Indexes tối ưu truy vấn. |
| `app/Core/RateLimiter.php` | Thư viện kiểm soát tần suất yêu cầu (Rate Limiting) theo thuật toán Sliding-Window dựa trên IP và định danh người dùng. |
| `app/Services/BookingExpirationService.php` | Dịch vụ nghiệp vụ tự động quét, hết hạn các lượt đặt phòng/ghế không đến nhận (`QuaHanKhongDen`) và giải phóng ghế cho sinh viên khác. |
| `bin/expire_bookings.php` | CLI Worker độc lập được thiết kế để kích hoạt định kỳ qua Cronjob (Linux) hoặc Task Scheduler (Windows). |
| `storage/.htaccess` | Cấu hình bảo mật Apache: Chặn truy cập trực tiếp toàn bộ thư mục lưu trữ nội bộ (`Require all denied`). |
| `storage/uploads/.htaccess` | Cấu hình bảo mật Apache: Vô hiệu hóa thực thi mã PHP/CGI (`php_flag engine off`, `SetHandler default-handler`). |
| `tests/Unit/ConcurrencyBookingTest.php` | Bộ kiểm thử tự động xác minh chống Overbooking cùng ghế, chống xung đột lịch cùng người dùng và cơ chế Deadlock Retry. |
| `tests/Unit/SecurityHardeningTest.php` | Bộ kiểm thử tự động xác minh CSRF Token, Rate Limiter, Session Strict Mode, SQL Injection resistance và Upload security. |
| `tests/Unit/LibraryBusinessLogicTest.php` | Bộ kiểm thử tự động xác minh hạn mức mượn 3 cuốn, chống mượn trùng cuốn, hạn 14 ngày, gia hạn 1 lần +7 ngày, trả sách và Ghost Booking worker. |
| `docs/SYSTEM_AUDIT_REPORT.md` | Báo cáo kiểm toán kiến trúc, phân loại chi tiết và giải pháp cho 35 vấn đề. |
| `docs/FIX_CHANGELOG.md` | Nhật ký chi tiết các thay đổi mã nguồn, CSDL và API. |
| `docs/SECURITY_AND_INTEGRITY_CHECKLIST.md` | Bảng đối chiếu trạng thái (FIXED / ALREADY_SAFE) của toàn bộ 35 vấn đề kèm bằng chứng xác thực. |
| `docs/TEST_REPORT.md` | Báo cáo kết quả kiểm thử tự động 13 bộ test suites đạt chuẩn 100%. |

---

## 2. Tệp tin được chỉnh sửa (Modified Files)

### 2.1. Cấu hình & Tầng Hạ tầng Cốt lõi (Configuration & Core)
- **`config/database.php`:**
  - Bổ sung tùy chọn `time_zone = '+07:00'` trong lệnh khởi tạo kết nối PDO MySQL nhằm đồng bộ tuyệt đối múi giờ với PHP (`Asia/Ho_Chi_Minh`).
- **`app/Core/Database.php`:**
  - Triển khai phương thức `Database::transactionWithRetry(callable $callback, int $maxRetries = 3)`: Tự động phát hiện lỗi Deadlock MariaDB (Mã lỗi 1213, 1205, SQLSTATE 40001) và thực hiện thử lại giao dịch với thuật toán Exponential Backoff và Jitter ngẫu nhiên.
- **`app/Core/Session.php`:**
  - Kích hoạt `ini_set('session.use_strict_mode', '1')` chống Session Fixation.
  - Thiết lập cờ cookie phiên: `HttpOnly = true`, `SameSite = 'Lax'`, tự động bật `Secure` khi có HTTPS.
  - Bổ sung các phương thức: `getCsrfToken()`, `validateCsrfToken(?string $token)`, `regenerateCsrfToken()` sử dụng hàm so sánh an toàn `hash_equals()`.

### 2.2. Tầng Điều khiển & Định tuyến (Controllers & Routing)
- **`public/index.php`:**
  - Tích hợp kiểm tra giới hạn tần suất đăng nhập `RateLimiter::tooManyAttempts('login:ip:...')` (5 lần/phút).
  - Tích hợp kiểm tra CSRF Token trên tuyến `/login` POST.
- **`app/Controllers/OnboardingController.php`:**
  - Tích hợp kiểm tra CSRF Token và giới hạn tần suất gửi duyệt hồ sơ (5 lần/phút).
- **`app/Controllers/RoomBookingController.php`:**
  - Bổ sung xác thực `Session::validateCsrfToken()` qua cả Header `X-CSRF-TOKEN` và form payload `csrf_token`.
  - Tích hợp hỗ trợ `Idempotency-Key` (Header và Payload) với bộ đệm phản hồi chống gửi trùng lặp yêu cầu đặt chỗ.
  - Tích hợp giới hạn tần suất đặt chỗ (10 lượt/phút/user).
- **`app/Controllers/LibrarianController.php`:**
  - Bổ sung kiểm tra CSRF Token cho toàn bộ các hành động POST / AJAX: `createBook`, `updateBook`, `deleteBook`, `approveStudent`, `rejectStudent`.

### 2.3. Tầng Truy vấn Dữ liệu (Repositories)
- **`app/Repositories/RoomRepository.php`:**
  - Thêm vòng lặp tự động retry trong `createBooking()` để xử lý xung đột đồng thời.
  - Bắt lỗi trùng lặp MariaDB Duplicate Key 1062 đối với `uq_active_seat_booking` và `uq_active_user_shift`, chuyển đổi thành ngoại lệ nghiệp vụ thân thiện cho người dùng.
  - Triển khai phương thức `checkinBooking(int $bookingId)`: Xác thực trạng thái và cho phép sinh viên nhận chỗ khi đến thư viện.
  - Triển khai phương thức `expireNoShowBookings(?DateTimeImmutable $now)`: Tự động chuyển đổi các lượt đặt đã qua giờ ca đọc sang trạng thái `QuaHanKhongDen` và giải phóng ghế.
- **`app/Repositories/BookRepository.php`:**
  - Bổ sung khóa bi quan (`FOR UPDATE`) khi tính toán chỉ số bản sao sách trong `update()`.
  - Nâng cấp phương thức `delete()`: Bổ sung kiểm tra toàn vẹn lịch sử mượn trả trong `chi_tiet_muon`. Không cho phép xóa đầu sách đã phát sinh giao dịch trong quá khứ nhằm bảo tồn dữ liệu kiểm toán.
- **`app/Repositories/UserRepository.php`:**
  - Bọc toàn bộ các thao tác ghi dữ liệu nhiều bảng (`createFromGoogle`, `submitStudentCardProfile`, `approveStudent`) vào trong Database Transaction.
- **`app/Repositories/LoanRepository.php`:**
  - **Khắc phục triệt để lỗi N+1 Query (Issue 29):** Viết lại `getReportsSummary()` từ vòng lặp 15 ngày phát sinh 30 truy vấn thành 2 câu lệnh truy vấn gộp nhóm (`WHERE dt IN (...) GROUP BY dt`).
  - Triển khai `getActiveLoansCount(int $userId)` và `hasOverdueLoans(int $userId)`.
  - Triển khai `createLoan(int $userId, string $copyId, int $durationDays = 14, ?int $librarianId = null)`: Khóa bản sao bằng `SELECT ... FOR UPDATE`, kiểm tra giới hạn tối đa 3 cuốn, thời hạn 14 ngày, chống mượn trùng cuốn sách chưa hoàn tất giao dịch.
  - Triển khai `returnBook(int $detailId)`: Xác thực trạng thái `DangMuon`, tính tiền phạt trễ hạn tự động (1.000 VNĐ/ngày), chuyển sách về `SanSang` và tự động cập nhật phiếu mượn sang `HoanTat`.
  - Triển khai `renewBook(int $detailId)`: Kiểm tra sách quá hạn và giới hạn mỗi lượt mượn chỉ được gia hạn tối đa 1 lần (+7 ngày).

### 2.4. Giao diện (Views) & JavaScript
- **`resources/views/login/index.php`:** Bổ sung trường ẩn `csrf_token`.
- **`resources/views/onboarding/index.php`:** Bổ sung trường ẩn `csrf_token`.
- **`resources/views/librarian-students/index.php`:** Bổ sung trường ẩn `csrf_token` trong các modal duyệt/từ chối thẻ.
- **`resources/views/librarian-books/index.php`:** Bổ sung meta tag `csrf-token` và truyền token vào các modal thêm/sửa/xóa sách.
- **`public/assets/js/librarian-books.js`:** Tự động đính kèm header `X-CSRF-TOKEN` vào mọi AJAX request gửi lên backend.

### 2.5. Kiểm thử & Tích hợp (Tests)
- **`tests/Integration/HttpEndToEndTest.php`:** Bổ sung cơ chế tự động khởi động máy chủ PHP built-in tạm thời và dọn dẹp tiến trình an toàn khi chạy trên Windows.
- **`tests/run_all_tests.php`:** Đăng ký 3 bộ kiểm thử mới (`ConcurrencyBookingTest`, `SecurityHardeningTest`, `LibraryBusinessLogicTest`), nâng tổng số bộ kiểm thử từ 10 lên 13 suites.

---

## 3. Thay đổi Cơ sở dữ liệu (Database Migrations)
1. **Virtual Generated Column `active_seat_booking` trên bảng `dat_cho`:**
   ```sql
   ALTER TABLE `dat_cho`
   ADD COLUMN `active_seat_booking` VARCHAR(100) GENERATED ALWAYS AS (
       IF(`trang_thai` IN ('DaDat', 'DaNhanCho'), CONCAT(`id_cho_ngoi`, '_', `id_ca_doc`, '_', `ngay_dat`), NULL)
   ) VIRTUAL;
   ALTER TABLE `dat_cho` ADD CONSTRAINT `uq_active_seat_booking` UNIQUE (`active_seat_booking`);
   ```
2. **Virtual Generated Column `active_user_shift` trên bảng `dat_cho`:**
   ```sql
   ALTER TABLE `dat_cho`
   ADD COLUMN `active_user_shift` VARCHAR(100) GENERATED ALWAYS AS (
       IF(`trang_thai` IN ('DaDat', 'DaNhanCho'), CONCAT(`id_nguoi_dung`, '_', `id_ca_doc`, '_', `ngay_dat`), NULL)
   ) VIRTUAL;
   ALTER TABLE `dat_cho` ADD CONSTRAINT `uq_active_user_shift` UNIQUE (`active_user_shift`);
   ```
3. **Chỉ mục phức hợp (Composite Indexes):**
   - `idx_cuon_sach_dau_trang_thai` ON `cuon_sach` (`id_dau_sach`, `trang_thai`)
   - `idx_ctm_cuon_trang_thai` ON `chi_tiet_muon` (`id_cuon_sach`, `trang_thai`)
   - `idx_ctm_trang_thai_han` ON `chi_tiet_muon` (`trang_thai`, `han_tra`)
   - `idx_audit_time` ON `nhat_ky_he_thong` (`thoi_gian`)
