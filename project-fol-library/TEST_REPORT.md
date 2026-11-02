# BÁO CÁO KẾT QUẢ KIỂM THỬ HỆ THỐNG (TEST REPORT)
## Dự án: Hệ thống Thư viện Khoa Văn học (FoL Digital Library)
**Ngày thực hiện kiểm thử:** 02/11/2026  
**Công cụ thực thi:** Master Test Runner (`tests/run_all_tests.php`)  
**Môi trường thử nghiệm:** PHP 8.2.12 (WINNT), MariaDB 10.4.32, Máy chủ HTTP Live `http://localhost:8000`  

---

## 1. Tổng quan Kết quả Thực thi

| Chỉ số tổng hợp | Số liệu thực tế | Tỷ lệ đạt |
|---|---|---|
| **Tổng số Bộ kiểm thử (Test Suites)** | **13 suites** | **100%** |
| **Số Test Suites ĐẠT (PASS)** | **13 suites** | **100%** |
| **Số Test Suites THẤT BẠI (FAIL)** | **0 suites** | **0%** |
| **Tổng thời gian thực thi toàn bộ** | **~4.5 giây** | **Tối ưu cao** |

---

## 2. Bảng Tổng kết Chi tiết 13 Test Suites

| STT | Bộ kiểm thử (Test Suite File) | Nhóm | Mô tả chi tiết | Kết quả | Thời gian |
|---|---|---|---|---|---|
| 1 | `tests/Unit/AuthorizationServiceTest.php` | Unit | Phân quyền RBAC & Quyền truy cập các vai trò | **PASS** | 0.211s |
| 2 | `tests/Unit/BookServiceTest.php` | Unit | Xử lý logic Nghiệp vụ & Dữ liệu Đầu sách | **PASS** | 0.293s |
| 3 | `tests/Unit/GoogleAuthServiceTest.php` | Unit | Xác thực Google OAuth 2.0 & Giới hạn Domain trường | **PASS** | 0.091s |
| 4 | `tests/Unit/ImageOptimizerServiceTest.php` | Unit | Tự động Thu nhỏ & Nén ảnh WebP (GD Library) | **PASS** | 0.568s |
| 5 | `tests/Unit/StudentCardApprovalTest.php` | Unit | Quy trình Xét duyệt Thẻ Sinh viên & Luồng Chờ duyệt | **PASS** | 0.138s |
| 6 | `tests/Unit/RoomBookingServiceTest.php` | Unit | Nghiệp vụ đặt chỗ phòng đọc và phân bổ ghế | **PASS** | 0.077s |
| 7 | `tests/Unit/ConcurrencyBookingTest.php` | Unit | Đồng thời, Chống Overbooking & Deadlock Retry | **PASS** | 0.321s |
| 8 | `tests/Unit/SecurityHardeningTest.php` | Unit | Bảo mật CSRF, Rate Limiting, Session Fixation & SQLi | **PASS** | 0.170s |
| 9 | `tests/Unit/LibraryBusinessLogicTest.php` | Unit | Nghiệp vụ Mượn/Trả, Hạn mức & Ghost Booking Worker | **PASS** | 0.183s |
| 10 | `tests/Integration/ViewsDbIntegrationTest.php` | Integration | Tích hợp 15 Giao diện Views kết nối CSDL thực | **PASS** | 0.275s |
| 11 | `tests/Integration/BookManagementRegressionTest.php` | Integration | Hồi quy Quản lý sách & Phân trang thủ thư | **PASS** | 0.248s |
| 12 | `tests/Integration/AuthenticationAuthorizationRegressionTest.php` | Integration | Hồi quy Toàn diện Phiên đăng nhập & Bảo mật RBAC (16 Kịch bản) | **PASS** | 0.123s |
| 13 | `tests/Integration/HttpEndToEndTest.php` | Integration / Live HTTP | Kiểm thử Live HTTP End-to-End trên Server thực (21 Kịch bản) | **PASS** | 1.972s |

---

## 3. Chi tiết Kịch bản Kiểm thử Chuyên sâu

### A. Kiểm thử Đồng thời & Toàn vẹn (Concurrency & Integrity Test)
- **Kịch bản 1: Đặt chỗ hợp lệ lần đầu** -> Thành công, sinh mã `DC-...`, trạng thái `DaDat` (PASS).
- **Kịch bản 2: Chống xung đột lịch cùng người dùng** -> Sinh viên A cố tình đặt thêm ghế khác trong cùng ca bị chặn ngay lập tức với thông báo thân thiện (PASS).
- **Kịch bản 3: Bảo vệ mức CSDL (uq_active_user_shift)** -> Khi cố tình gửi câu lệnh SQL INSERT trực tiếp vào CSDL để bypass ứng dụng, MariaDB chặn đứng với lỗi 1062 Duplicate entry (PASS).
- **Kịch bản 4: Bảo vệ mức CSDL (uq_active_seat_booking)** -> Khi 2 sinh viên cố tình tranh chấp cùng 1 ghế trong cùng 1 ca đọc, MariaDB chặn đứng với lỗi 1062 Duplicate entry (PASS).
- **Kịch bản 5: Đặt ca khác nhau trong cùng ngày** -> Sinh viên được phép đặt ca sáng và ca chiều trong cùng 1 ngày bình thường (PASS).
- **Kịch bản 6: Giải phóng ghế khi hủy chỗ** -> Khi sinh viên hủy chỗ (`DaHuy`), cột sinh ảo trả về `NULL`, giải phóng ghế cho sinh viên khác đặt lại ngay lập tức (PASS).
- **Kịch bản 7: Cơ chế tự động Retry khi gặp Deadlock** -> Giả lập lỗi deadlock 1213 trong closure, `Database::transactionWithRetry` tự động lùi bước với exponential backoff và thực thi thành công ở lần thử tiếp theo (PASS).

### B. Kiểm thử Bảo mật & Làm cứng Hệ thống (Security & Hardening Test)
- **Kịch bản 1: Sinh và xác thực CSRF Token** -> Token 64 ký tự hex (256-bit entropy); hàm so sánh `hash_equals` từ chối token giả mạo hoặc rỗng (PASS).
- **Kịch bản 2: Đổi mới CSRF Token** -> Token cũ bị vô hiệu hóa sau khi tái tạo (PASS).
- **Kịch bản 3: Rate Limiter Sliding Window** -> Cho phép dưới ngưỡng và chặn đứng ngay khi vượt quá giới hạn (ví dụ 3 lần/phút); trả về thời gian chờ tính bằng giây (PASS).
- **Kịch bản 4: Session Strict Mode** -> `ini_get('session.use_strict_mode')` được bật giá trị `1`, ngăn chặn triệt để tấn công Session Fixation (PASS).
- **Kịch bản 5: Chống SQL Injection** -> Payload dạng `' OR '1'='1' UNION SELECT ...` được xử lý dạng literal string an toàn, không thể can thiệp vào câu lệnh SQL (PASS).
- **Kịch bản 6: Chống thực thi tệp tải lên** -> Các tệp `.htaccess` trong `storage/` và `storage/uploads/` ngăn chặn trực tiếp truy cập và vô hiệu hóa module PHP engine (PASS).

### C. Kiểm thử Nghiệp vụ Thư viện (Library Business Logic Test)
- **Kịch bản 1: Giới hạn mượn tối đa 3 cuốn** -> Mượn 3 cuốn thành công; mượn cuốn thứ 4 bị chặn với thông báo quy chế thư viện (PASS).
- **Kịch bản 2: Chống mượn trùng cuốn sách** -> Cuốn sách đang trong trạng thái `DangMuon` bị khóa và từ chối cho người khác mượn (PASS).
- **Kịch bản 3: Gia hạn sách** -> Gia hạn lần đầu thành công (+7 ngày); gia hạn lần 2 bị chặn vì vượt quá số lần cho phép (PASS).
- **Kịch bản 4: Trả sách** -> Trả sách thành công, tự động tính tiền phạt trễ hạn nếu quá hạn; cuốn sách được khôi phục về trạng thái `SanSang`; từ chối trả lại cuốn sách đã trả (PASS).
- **Kịch bản 5: Tối ưu hóa truy vấn N+1** -> Báo cáo tổng hợp mượn trả 15 ngày thu thập dữ liệu bằng 2 truy vấn gộp nhóm thay vì vòng lặp 30 queries (PASS).
- **Kịch bản 6: Ghost Booking Expiration Worker** -> Dịch vụ `BookingExpirationService` tự động quét các lượt đặt quá hạn và chuyển sang `QuaHanKhongDen`, giải phóng chỗ ngồi (PASS).

### D. Kiểm thử Live HTTP End-to-End (21 Kịch bản Thực tế)
- Toàn bộ 21 kịch bản HTTP mô phỏng trình duyệt thực tế (đăng nhập Admin, đổi mật khẩu, phân quyền 403 Forbidden cho Sinh viên, duy trì Cookie Session giữa các trang, kiểm tra Guest Guard, chuyển hướng Onboarding, phục vụ ảnh tĩnh nén WebP an toàn) đều đạt chuẩn 100% với mã HTTP tương ứng (200, 302, 403).

---

## 4. Tình huống Chưa Kiểm thử & Khuyến nghị Vận hành
- **Kiểm thử Tải đồng thời quy mô cực lớn (Stress Testing > 1,000 req/s):** Hiện tại hệ thống đã được kiểm thử với các kịch bản Race Condition & Deadlock Retry ở mức CSDL và ứng dụng. Khuyến nghị khi triển khai thực tế trên cụm máy chủ nhiều nodes nên sử dụng Apache JMeter hoặc k6 để đo lường ngưỡng tài nguyên phần cứng.
- **Tự động hóa Cronjob trên Máy chủ:** Cần thiết lập cấu hình chạy `php bin/expire_bookings.php` mỗi 15 phút một lần trên hệ thống Linux (crontab) hoặc Windows (Task Scheduler).
