# PHÁT TRIỂN ỨNG DỤNG WEB — WEB APPLICATION DEVELOPMENT

## TechCare Platform — Hệ Thống Đặt Lịch & Điều Phối Kỹ Thuật Viên Sửa Chữa Thiết Bị IT (3-Tier Architecture)

> Kho lưu trữ toàn bộ mã nguồn, cơ sở dữ liệu, tài liệu thiết kế và kiến trúc 3-Tier phục vụ cho đồ án môn học Phát triển ứng dụng Web (IS207).

---

## 📌 1. THÔNG TIN HỌC PHẦN & ĐỒ ÁN

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Tên môn học** | Phát triển ứng dụng Web — Web Application Development |
| **Mã môn học** | IS207 |
| **Lớp học phần** | IS207 (Khoa HTTT) |
| **Đề tài đồ án** | TechCare Platform — Hệ thống đặt lịch, phân công và điều phối kỹ thuật viên sửa chữa thiết bị IT |
| **Mô hình kiến trúc** | 3-Tier Architecture (Presentation – Application – Data Tier) |
| **Học kỳ / Năm học** | Học kì 1 — Năm học 2026 - 2027 |
| **Khoa / Trường** | Khoa Hệ thống Thông tin — Trường ĐH Công nghệ Thông tin (UIT - ĐHQG-HCM) |

---

## 👨‍🏫 2. GIẢNG VIÊN HƯỚNG DẪN

* **Giảng viên lý thuyết:** ThS. Dương Phi Long

---

## 👨‍🎓 3. THÀNH VIÊN NHÓM

| STT | Họ và Tên | MSSV | Lớp | Vai trò | GitHub | Email |
| :-: | :--- | :-: | :-: | :-: | :--- | :--- |
| 1 | **Nguyễn Đoàn Đức Hiếu** | 24520500 | IS402.R12 | **Leader** | [@DalzielNguyen-1611](https://github.com/DalzielNguyen-1611) | [24520500@gm.uit.edu.vn](mailto:24520500@gm.uit.edu.vn) |
| 2 | **Nguyễn Thị Phương Hoa** | 23520506 | IS402.R12 | Member | — | [23520506@gm.uit.edu.vn](mailto:23520506@gm.uit.edu.vn) |
| 3 | **Nguyễn Văn Phát** | 24521314 | IS402.R12 | Member | — | [24521314@gm.uit.edu.vn](mailto:24521314@gm.uit.edu.vn) |
| 4 | **Trần Anh Thư** | 24521732 | IS402.R12 | Member | — | [24521732@gm.uit.edu.vn](mailto:24521732@gm.uit.edu.vn) |
| 5 | **Trần Ngọc Thảo** | 24521649 | IS402.R12 | Member | — | [24521649@gm.uit.edu.vn](mailto:24521649@gm.uit.edu.vn) |

---

## 🎯 4. MỤC TIÊU CỦA ĐỒ ÁN

Repository này được xây dựng nhằm giải quyết bài toán số hóa quy trình cứu hộ, sửa chữa thiết bị IT doanh nghiệp và tận nhà với các mục tiêu chính:

1. **Quản lý & Tổ chức mã nguồn chuẩn 3-Tier**: Tách bạch hoàn toàn giữa Giao diện (Presentation Tier), Xử lý nghiệp vụ & RESTful API (Application Tier với PHP thuần), và Lưu trữ dữ liệu (Data Tier với MySQL 3NF).
2. **Xây dựng quy trình nghiệp vụ IT Support thực tế**:
   * **Khách hàng**: Đặt lịch sửa chữa PC/Laptop/Mạng nhanh chóng, chọn khung giờ và theo dõi tiến độ ca trực.
   * **Điều phối viên (Dispatch)**: Giám sát toàn bộ ticket chờ xử lý, trạng thái trực ca của kỹ thuật viên và phân công tức thời.
   * **Kỹ thuật viên (Technician)**: Tiếp nhận ca làm việc, nghiệm thu hoàn tất dịch vụ, xuất hóa đơn linh kiện và quản lý ví thu nhập.
3. **Phát triển Backend RESTful API độc lập**: 100% PHP thuần hướng đối tượng (OOP), PDO Prepared Statements phòng chống SQL Injection, chuẩn hóa JSON response và CORS.
4. **Tối ưu trải nghiệm giao diện người dùng**: Thiết kế giao diện Dashboard và Portal hiện đại, responsive, trực quan, hỗ trợ mô phỏng hoạt động kể cả khi chưa kết nối Database.

---

## 📂 5. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
IS207-HTTP-mini-project/
├── frontend/                     # Tier 1: Presentation Tier (Giao diện Web Client)
│   ├── index.html                # Cổng điều hướng toàn bộ 17 màn hình chức năng
│   ├── dat_lich.html             # Đặt lịch sửa chữa & cứu hộ IT (TechCare Desk & Home)
│   ├── khach_hang.html           # Theo dõi tiến độ & đánh giá ca sửa chữa phía khách hàng
│   ├── ky_thuat_vien.html        # Portal Kỹ thuật viên: Lịch trực, nghiệm thu & ví thu nhập
│   ├── quan_ly.html              # Dashboard Điều phối viên: Phân công đơn & giám sát thợ
│   ├── dang_nhap.html            # Đăng nhập / Chuyển đổi vai trò người dùng
│   ├── danh_gia.html             # Khảo sát & xếp hạng chất lượng dịch vụ
│   ├── assets/                   # Vector logo SVG, hình ảnh thiết bị & icons
│   │   ├── js/                   # Modules JavaScript phía Client
│   │   │   ├── api.js            # Client HTTP Adapter gọi Backend RESTful API
│   │   │   └── auth.js           # Quản lý phiên làm việc & phân quyền vai trò
│   │   └── *.jpg / *.svg         # Banner, hình minh họa sự cố máy tính
│   └── archive_old_pages/        # Các màn hình thành phần chi tiết độc lập
│
├── backend/                      # Tier 2: Application / Logic Tier (100% PHP THUẦN OOP)
│   ├── config/
│   │   └── database.php          # Quản lý kết nối PDO Singleton (hỗ trợ fallback mock data)
│   ├── helpers/
│   │   ├── Response.php          # Chuẩn hóa JSON Response, HTTP status code & CORS
│   │   └── AuthHelper.php        # Hỗ trợ xác thực người dùng và kiểm tra quyền
│   ├── models/                   # Tầng mô hình dữ liệu (Data Access Objects)
│   │   ├── User.php              # Tài khoản, phân quyền (Khách hàng, KTV, Điều phối, Admin)
│   │   ├── Booking.php           # Phiếu yêu cầu sửa chữa, trạng thái ca trực, nghiệm thu
│   │   ├── Technician.php        # Hồ sơ kỹ thuật viên, trạng thái ca, đánh giá sao
│   │   ├── Review.php            # Đánh giá & nhận xét của khách hàng
│   │   └── Wallet.php            # Ví thu nhập KTV, lịch sử cộng tiền, lệnh rút tiền
│   ├── controllers/              # Tầng điều khiển nghiệp vụ (Business Controllers)
│   │   ├── AuthController.php    # Nghiệp vụ đăng nhập, đăng ký, phiên làm việc
│   │   ├── BookingController.php # Nghiệp vụ tạo đơn, tra cứu và cập nhật trạng thái
│   │   ├── DispatchController.php# Nghiệp vụ thống kê dashboard và phân bổ kỹ thuật viên
│   │   ├── TechnicianController.php # Nghiệp vụ lịch làm việc, nghiệm thu ca, ví thợ
│   │   └── ReviewController.php  # Nghiệp vụ gửi và tổng hợp đánh giá
│   ├── api/                      # Tầng Endpoint RESTful API
│   │   ├── auth.php              # Router xử lý xác thực
│   │   ├── bookings.php          # Router xử lý đơn đặt lịch
│   │   ├── dispatch.php          # Router điều phối & thống kê
│   │   ├── technicians.php       # Router thông tin & nghiệp vụ KTV
│   │   ├── reviews.php           # Router đánh giá chất lượng
│   │   └── index.php             # Danh mục tài liệu API trực tiếp trên web
│   ├── .htaccess                 # Cấu hình URL Rewrite sạch cho Web Server Apache
│   └── index.php                 # Trang kiểm tra môi trường PHP & trạng thái hệ thống
│
├── database/                     # Tier 3: Data Tier (Cơ sở dữ liệu quan hệ RDBMS)
│   ├── schema.sql                # DDL khởi tạo 8 bảng quan hệ chuẩn hóa 3NF
│   └── README.md                 # Hướng dẫn chi tiết nạp CSDL vào MySQL qua phpMyAdmin / CLI
│
├── docs/                         # Tài liệu kỹ thuật & kiến trúc đồ án
│   ├── architecture_3tier.md     # Thuyết minh chi tiết kiến trúc 3-Tier phục vụ báo cáo/slide
│   ├── techcare_desk_DESIGN.md   # Design tokens & UX Flow phân hệ IT doanh nghiệp
│   └── techcare_home_DESIGN.md   # Design tokens & UX Flow phân hệ sửa chữa gia đình
│
├── .gitignore                    # Loại trừ file rác, dữ liệu nhạy cảm và file thử nghiệm
└── README.md                     # Tài liệu giới thiệu tổng quan đồ án môn học
```

---

## 🚀 6. HƯỚNG DẪN CÀI ĐẶT & KHỞI CHẠY

### Cách 1: Sử dụng XAMPP (Khuyên dùng)
1. Bật **Apache** và **MySQL** trong bảng điều khiển **XAMPP Control Panel**.
2. Sao chép thư mục dự án vào thư mục web root của XAMPP:
   ```text
   C:\xampp\htdocs\mini_project\
   ```
3. Mở trình duyệt và truy cập các cổng:
   * **Cổng điều hướng Frontend:** [http://localhost/mini_project/frontend/index.html](http://localhost/mini_project/frontend/index.html)
   * **Cổng kiểm tra Backend PHP:** [http://localhost/mini_project/backend/index.php](http://localhost/mini_project/backend/index.php)
   * **API Lấy danh sách phiếu:** [http://localhost/mini_project/backend/api/bookings.php](http://localhost/mini_project/backend/api/bookings.php)
4. *(Tùy chọn)* Nạp CSDL: Mở [phpMyAdmin](http://localhost/phpmyadmin), tạo CSDL tên `techcare_db` và Import file [database/schema.sql](database/schema.sql). *(Lưu ý: Nếu chưa bật MySQL, hệ thống vẫn có cơ chế Mock Data tự động để demo mượt mà không bị gián đoạn).*

### Cách 2: Sử dụng PHP Built-in Server
Nếu máy tính đã cài đặt môi trường PHP:
1. Mở Terminal / PowerShell tại thư mục gốc của dự án:
   ```bash
   php -S localhost:8000
   ```
2. Truy cập qua trình duyệt:
   * **Frontend:** [http://localhost:8000/frontend/index.html](http://localhost:8000/frontend/index.html)
   * **Backend:** [http://localhost:8000/backend/index.php](http://localhost:8000/backend/index.php)

### Cách 3: Trình chiếu giao diện tĩnh (Static Presentation)
Có thể mở trực tiếp file `frontend/index.html` trên bất kỳ trình duyệt nào để trải nghiệm và thuyết trình giao diện mà không cần thiết lập Web Server.

---

## 📡 7. DANH MỤC RESTFUL API PHP (APPLICATION TIER)

| Endpoint PHP | Phương thức | Mô tả chức năng |
| :--- | :---: | :--- |
| `api/auth.php?action=login` | `POST` | Đăng nhập tài khoản (Khách hàng, KTV, Điều phối) |
| `api/auth.php?action=register` | `POST` | Đăng ký tài khoản người dùng mới |
| `api/auth.php?action=profile` | `GET` | Lấy thông tin tài khoản đang đăng nhập |
| `api/bookings.php` | `GET` | Lấy danh sách toàn bộ phiếu yêu cầu / tickets |
| `api/bookings.php?id={id}` | `GET` | Xem chi tiết ca trực (ví dụ: `BK-3895` hoặc `TC-HOME-3895`) |
| `api/bookings.php` | `POST` | Tạo phiếu đặt lịch sửa chữa / cứu hộ IT mới |
| `api/bookings.php?id={id}&action=status` | `POST` / `PUT` | Cập nhật tiến độ ca sửa chữa (`assigned`, `in_progress`, `completed`) |
| `api/dispatch.php?action=dashboard` | `GET` | Thống kê số liệu, hàng đợi pending, KTV trực ca cho điều phối |
| `api/dispatch.php?action=assign` | `POST` | Phân công KTV cho ca cứu hộ |
| `api/dispatch.php?action=technicians-live` | `GET` | Danh sách trạng thái realtime của tất cả kỹ thuật viên |
| `api/technicians.php?action=schedule` | `GET` | Xem lịch trực ca & phân công nhiệm vụ của KTV (`#NV-204`) |
| `api/technicians.php?action=complete&booking_id={id}` | `POST` | Nghiệm thu hoàn tất ca & xuất hóa đơn linh kiện |
| `api/technicians.php?action=wallet` | `GET` | Xem số dư ví thu nhập & lịch sử nhận tiền |
| `api/technicians.php?action=withdraw` | `POST` | Tạo yêu cầu rút tiền về tài khoản ngân hàng |
| `api/reviews.php` | `POST` | Khách hàng gửi đánh giá sao & nhận xét sau sửa chữa |

---

## 📑 8. TÀI LIỆU & BÁO CÁO MÔN HỌC

* Thuyết minh chi tiết kiến trúc 3-Tier: [docs/architecture_3tier.md](docs/architecture_3tier.md)
* Thiết kế phân hệ TechCare Desk (Doanh nghiệp): [docs/techcare_desk_DESIGN.md](docs/techcare_desk_DESIGN.md)
* Thiết kế phân hệ TechCare Home (Gia đình): [docs/techcare_home_DESIGN.md](docs/techcare_home_DESIGN.md)
* Hướng dẫn thiết lập Cơ sở dữ liệu: [database/README.md](database/README.md)
