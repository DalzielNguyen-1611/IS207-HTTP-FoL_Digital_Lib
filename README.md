# TechCare Platform — Mini Project Phát Triển Ứng Dụng Web (PHP Backend)

> **Mô hình kiến trúc**: 3-Tier Architecture (Client – Server)  
> **Ngôn ngữ Backend**: **100% PHP thuần (PHP OOP / PDO / RESTful API)**  
> **Giao diện Frontend**: HTML5 + TailwindCSS  
> **Cơ sở dữ liệu**: MySQL / MariaDB (RDBMS chuẩn 3NF)

---

## 📁 Cấu Trúc Đồ Án Chuẩn 3-Tier

```
mini_project/
├── frontend/                     # Tier 1: Presentation Tier (Giao diện Web Desktop)
│   ├── index.html                # Trang cổng điều hướng sitemap 17 màn hình
│   ├── techcare_desk_*.html      # 4 màn hình phân hệ hỗ trợ IT doanh nghiệp
│   ├── techcare_home_*.html      # 4 màn hình phân hệ sửa chữa tận nhà
│   ├── dieu_phoi_*.html          # 3 màn hình phân hệ điều phối viên (Dispatch)
│   ├── ktv_*.html                # 7 màn hình phân hệ kỹ thuật viên hiện trường
│   └── assets/                   # Vector logo SVG và tài nguyên tĩnh
│
├── backend/                      # Tier 2: Application / Logic Tier (100% PHP THUẦN)
│   ├── config/
│   │   └── database.php          # Lớp kết nối PDO với MySQL (tự động fallback mock data)
│   ├── helpers/
│   │   └── Response.php          # Quản lý JSON, CORS headers và HTTP status
│   ├── models/                   # User.php, Booking.php, Technician.php, Wallet.php
│   ├── controllers/              # AuthController, BookingController, DispatchController, TechnicianController
│   ├── api/                      # Các endpoint API: bookings.php, auth.php, dispatch.php, technicians.php, index.php
│   ├── .htaccess                 # Cấu hình rewrite URL sạch cho Apache
│   └── index.php                 # Trang kiểm tra trạng thái môi trường PHP & danh sách API
│
├── database/                     # Tier 3: Data Tier (Cơ sở dữ liệu quan hệ)
│   ├── schema.sql                # DDL tạo 8 bảng quan hệ chuẩn hóa 3NF
│   ├── seed.sql                  # Dữ liệu mẫu (Users, KTV #NV-204, Ca trực #TC-HOME-3895)
│   └── README.md                 # Hướng dẫn nạp CSDL vào MySQL qua phpMyAdmin / CLI
│
├── docs/                         # Tài liệu đồ án & báo cáo
│   ├── architecture_3tier.md     # Thuyết minh chi tiết kiến trúc 3-Tier để làm báo cáo/slide
│   ├── techcare_desk_DESIGN.md   # Design tokens TechCare Desk
│   └── techcare_home_DESIGN.md   # Design tokens TechCare Home
│
├── .gitignore
└── README.md
```

---

## 🚀 Hướng Dẫn Khởi Chạy Đồ Án

### Cách 1: Sử dụng XAMPP (Phổ biến nhất tại trường đại học)
1. Bật **Apache** và **MySQL** trong bảng điều khiển **XAMPP Control Panel**.
2. Sao chép toàn bộ thư mục `mini_project` vào thư mục `C:\xampp\htdocs\`.
3. Mở trình duyệt và truy cập:
   * **Cổng điều hướng Frontend**: [http://localhost/mini_project/frontend/index.html](http://localhost/mini_project/frontend/index.html)
   * **Cổng kiểm tra Backend PHP**: [http://localhost/mini_project/backend/index.php](http://localhost/mini_project/backend/index.php)
   * **API Lấy danh sách đơn**: [http://localhost/mini_project/backend/api/bookings.php](http://localhost/mini_project/backend/api/bookings.php)
4. *(Tùy chọn)* Nạp CSDL: Mở [http://localhost/phpmyadmin](http://localhost/phpmyadmin), tạo CSDL tên `techcare_db` và lần lượt Import file [database/schema.sql](database/schema.sql) rồi đến [database/seed.sql](database/seed.sql). *(Lưu ý: Nếu chưa kịp nạp CSDL, Backend PHP vẫn tự động dùng dữ liệu mẫu có sẵn để trả về JSON không hề bị lỗi!)*

### Cách 2: Chạy nhanh bằng lệnh PHP Built-in Server (Không cần cài Apache)
Nếu máy bạn đã có PHP:
1. Mở Terminal tại thư mục `mini_project`:
```bash
php -S localhost:8000
```
2. Mở trình duyệt truy cập:
- Frontend: [http://localhost:8000/frontend/index.html](http://localhost:8000/frontend/index.html)
- Backend: [http://localhost:8000/backend/index.php](http://localhost:8000/backend/index.php)

### Cách 3: Mở trực tiếp Frontend tĩnh
Bạn có thể nhấp đúp trực tiếp vào file `frontend/index.html` để trình chiếu giao diện bất cứ lúc nào.

---

## 📡 Danh Mục RESTful API PHP (Application Tier)

| Endpoint PHP | Phương thức | Mô tả |
| :--- | :--- | :--- |
| `api/auth.php?action=login` | `POST` | Đăng nhập tài khoản |
| `api/auth.php?action=register` | `POST` | Đăng ký tài khoản mới |
| `api/auth.php?action=profile` | `GET` | Lấy thông tin user hiện tại |
| `api/bookings.php` | `GET` | Lấy toàn bộ phiếu yêu cầu / tickets |
| `api/bookings.php?id={id}` | `GET` | Xem chi tiết ca trực (vd: `BK-3895` hoặc `TC-HOME-3895`) |
| `api/bookings.php` | `POST` | Tạo đơn đặt lịch sửa chữa / cứu hộ IT mới |
| `api/bookings.php?id={id}&action=status` | `POST` / `PUT` | Cập nhật tiến độ ca sửa chữa |
| `api/dispatch.php?action=dashboard` | `GET` | Thống kê số liệu, hàng đợi pending, KTV trực ca |
| `api/dispatch.php?action=assign` | `POST` | Phân công KTV cho ca cứu hộ |
| `api/dispatch.php?action=technicians-live` | `GET` | Trạng thái realtime của tất cả thợ |
| `api/technicians.php?action=schedule` | `GET` | Xem lịch làm việc của KTV (`#NV-204`) |
| `api/technicians.php?action=complete&booking_id={id}` | `POST` | Nghiệm thu hoàn tất ca & xuất hóa đơn linh kiện |
| `api/technicians.php?action=wallet` | `GET` | Xem số dư ví 3.450.000đ & lịch sử nhận tiền |
| `api/technicians.php?action=withdraw` | `POST` | Tạo lệnh rút tiền về tài khoản ngân hàng |

---

## 📑 Báo Cáo Môn Học
Xem chi tiết phần lý thuyết và phân tích mô hình 3-Tier tại: [docs/architecture_3tier.md](docs/architecture_3tier.md).
