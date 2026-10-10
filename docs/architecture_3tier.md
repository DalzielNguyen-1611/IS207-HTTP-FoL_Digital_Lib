# BÁO CÁO THUYẾT MINH KIẾN TRÚC 3-TIER CHO HỆ THỐNG TECHCARE PLATFORM

**Môn học**: Phát Triển Ứng Dụng Web  
**Đề tài**: Nền tảng điều phối dịch vụ kỹ thuật & cứu hộ IT (TechCare Platform)  
**Mô hình áp dụng**: Kiến trúc 3 tầng (3-Tier Architecture) trên nền tảng Client – Server  
**Công nghệ sử dụng**: Frontend (HTML5/TailwindCSS) – Backend (PHP OOP / PDO RESTful API) – Database (MySQL/RDBMS)

---

## I. TỔNG QUAN VỀ KIẾN TRÚC 3-TIER

Kiến trúc 3-Tier (Three-Tier Architecture) là một mẫu kiến trúc phần mềm client-server phân tách ứng dụng thành ba tầng logic và vật lý độc lập:
1. **Presentation Tier (Tầng Trình Diễn / Frontend)**
2. **Application / Business Logic Tier (Tầng Nghiệp Vụ / Backend PHP)**
3. **Data Tier (Tầng Dữ Liệu / Database)**

Mỗi tầng đảm nhiệm một trách nhiệm chuyên biệt, giao tiếp với tầng kế cận thông qua các giao thức và định dạng dữ liệu chuẩn hóa (HTTP/HTTPS, REST API, JSON, SQL).

```
+-------------------------------------------------------------+
|               TIER 1: PRESENTATION TIER                     |
|    HTML5 + TailwindCSS + JavaScript (Client-Side)           |
|    - Cổng người dùng TechCare Desk (IT Support)             |
|    - Cổng khách hàng TechCare Home (Sửa thiết bị tận nhà)   |
|    - Cổng điều phối viên Dispatch Center                    |
|    - Cổng kỹ thuật viên hiện trường (Field Tech Portal)     |
+-------------------------------------------------------------+
                              |
               RESTful API (HTTP / JSON Format)
                              |
                              v
+-------------------------------------------------------------+
|            TIER 2: APPLICATION / BUSINESS LOGIC TIER        |
|    Ngôn ngữ PHP (Lập trình Hướng Đối Tượng - OOP)           |
|    - Config: Database Connection Manager (PDO)              |
|    - Controllers: AuthController, BookingController,        |
|                   DispatchController, TechnicianController  |
|    - Models: User, Booking, Technician, Wallet              |
|    - Helpers: Response Manager (CORS, JSON, HTTP Status)    |
|    - Logic: Thuật toán điều phối KTV, tính SLA, quản lý ví  |
+-------------------------------------------------------------+
                              |
               PDO (PHP Data Objects) / SQL Queries
                              |
                              v
+-------------------------------------------------------------+
|                    TIER 3: DATA TIER                        |
|    Relational Database Management System (MySQL / MariaDB)  |
|    - Tables: Users, Technicians, Services, Bookings,        |
|              Booking_Parts, Wallets, Transactions, Reviews  |
|    - Ràng buộc toàn vẹn dữ liệu (Primary & Foreign Keys)   |
+-------------------------------------------------------------+
```

---

## II. CHI TIẾT CÁC TẦNG TRONG HỆ THỐNG

### 1. Presentation Tier (Tầng Trình Diễn - Frontend)
* **Thư mục**: `frontend/`
* **Công nghệ**: HTML5 ngữ nghĩa, TailwindCSS, Google Material Symbols, Google Fonts.
* **Nhiệm vụ**:
  * Hiển thị giao diện người dùng trực quan, responsive theo chuẩn Web Desktop.
  * Tiếp nhận tương tác người dùng (đặt lịch cứu hộ, xem timeline KTV, bấm nút nghiệm thu, tạo lệnh rút tiền).
  * Gửi yêu cầu dữ liệu không đồng bộ (AJAX / Fetch API) đến Tầng 2 thông qua các endpoint RESTful API PHP và hiển thị kết quả.

### 2. Application Logic Tier (Tầng Xử Lý Nghiệp Vụ - Backend PHP)
* **Thư mục**: `backend/`
* **Công nghệ**: **PHP 7.4+ / PHP 8.x**, lập trình Hướng Đối Tượng (OOP), PDO.
* **Cấu trúc module hóa**:
  * `config/database.php`: Lớp Database Singleton khởi tạo kết nối PDO an toàn với MySQL, tự động kiểm soát ngoại lệ.
  * `helpers/Response.php`: Lớp xử lý dữ liệu đầu ra JSON, cấu hình đầy đủ HTTP Headers và CORS cho phép gọi chéo domain.
  * `models/`: Định nghĩa thực thể dữ liệu, cung cấp các hàm nghiệp vụ:
    * `User.php`: Tìm kiếm, xác thực tài khoản, đăng ký người dùng mới.
    * `Booking.php`: Quản lý danh sách ticket, thêm mới đơn, cập nhật tiến độ, tính tiền linh kiện.
    * `Technician.php`: Hồ sơ thợ, đánh giá SLA, chuyển đổi trạng thái trực (`ready`/`busy`).
    * `Wallet.php`: Số dư khả dụng, tạo giao dịch trừ tiền khi rút về ngân hàng.
  * `controllers/`: Tiếp nhận HTTP Request, gọi Model tương ứng và trả về JSON:
    * `AuthController.php`: Đăng nhập, đăng ký, lấy thông tin cá nhân.
    * `BookingController.php`: Đặt lịch sửa chữa & cứu hộ IT.
    * `DispatchController.php`: Bảng điều phối, gán KTV theo khoảng cách.
    * `TechnicianController.php`: Lịch làm việc, nghiệm thu ca trực, rút tiền ví.
  * `api/`: Các endpoint API RESTful (`bookings.php`, `auth.php`, `dispatch.php`, `technicians.php`).

### 3. Data Tier (Tầng Dữ Liệu - Database)
* **Thư mục**: `database/`
* **Công nghệ**: MySQL / MariaDB (tương thích XAMPP, WAMP, Laragon).
* **Cấu trúc thiết kế**:
  * `schema.sql`: Định nghĩa cấu trúc lược đồ dữ liệu đạt chuẩn hóa 3NF gồm 8 bảng quan hệ liên kết chặt chẽ bằng khóa ngoại `FOREIGN KEY`.
  * `seed.sql`: Bộ dữ liệu mẫu phong phú mô phỏng các ca trực thực tế, hồ sơ kỹ thuật viên bậc 3 `#NV-204`, giúp hệ thống có thể trình diễn ngay lập tức trong buổi vấn đáp đồ án.

---

## III. ƯU ĐIỂM CỦA KIẾN TRÚC KHI SỬ DỤNG PHP

1. **Chuẩn mực theo chương trình đào tạo**: PHP là ngôn ngữ kinh điển, phổ biến và bắt buộc trong hầu hết các môn học Phát triển ứng dụng Web tại các trường đại học.
2. **Triển khai siêu nhanh trên môi trường XAMPP / Laragon**: Chỉ cần copy vào `htdocs` hoặc `www` là toàn bộ Frontend, Backend PHP và MySQL Database lập tức chạy được ngay.
3. **Cơ chế Fallback thông minh**: Backend PHP được thiết kế với cơ chế tự động trả dữ liệu mẫu nếu máy của giảng viên chưa bật MySQL, đảm bảo bài thuyết trình không bao giờ bị dừng lại do lỗi kết nối CSDL.
