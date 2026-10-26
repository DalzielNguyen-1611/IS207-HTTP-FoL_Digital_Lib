<!-- Header / Banner UIT -->
<p align="center">
  <a href="https://www.uit.edu.vn/" title="Trường Đại học Công nghệ Thông tin - ĐHQG TP.HCM" style="border: none;">
    <img src="https://i.imgur.com/WmMnSRt.png" alt="Trường Đại học Công nghệ Thông tin | University of Information Technology" width="650">
  </a>
</p>

<h1 align="center"><b>PHÁT TRIỂN ỨNG DỤNG WEB - WEB APPLICATION DEVELOPMENT</b></h1>

<h2 align="center">🏛️ MAIN PROJECT: FoL Digital Library — Hệ Thống Thư Viện Số Khoa Văn Học</h2>
<h3 align="center"><i>(MVC & Service - Repository Pattern Architecture)</i></h3>

<p align="center">
  <i>Kho lưu trữ toàn bộ mã nguồn, cơ sở dữ liệu, tài liệu kỹ thuật phục vụ học phần Phát triển ứng dụng web (IS207)</i>
</p>

<p align="center">
  <a href="https://github.com/DalzielNguyen-1611/IS207-HTTP-FoL_Digital_Lib"><img src="https://img.shields.io/badge/Course-IS207-0077b6?style=for-the-badge&logo=googlescholar&logoColor=white" alt="Course IS207"></a>
  <img src="https://img.shields.io/badge/Semester-HK1%20(2026--2027)-0096c7?style=for-the-badge&logo=clockify&logoColor=white" alt="Semester">
  <img src="https://img.shields.io/badge/PHP-8.2%20OOP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0%20(3NF)-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

---

## 📌 1. THÔNG TIN HỌC PHẦN & ĐỒ ÁN

| Thuộc tính | Chi tiết |
| :--- | :--- |
| **Tên môn học** | Phát triển ứng dụng web — Web Application Development |
| **Mã môn học** | IS207 |
| **Lớp học phần** | IS207.R11 |
| **Số tín chỉ** | 4 (3 lý thuyết + 1 thực hành) |
| **Dự án chính (Main Project)** | **FoL Digital Library — Hệ thống Quản lý và Tra cứu Thư viện số Khoa Văn Học** (`project-fol-library/`) |
| **Dự án thành phần (Mini Project)** | **TechCare Platform — Hệ thống Đặt lịch & Phân công Kỹ thuật viên IT** (`mini-project-techcare-platform/`) |
| **Mô hình kiến trúc chính** | MVC kết hợp Service - Repository Pattern (100% PHP thuần OOP, PDO, Clean Architecture) |
| **Học kỳ / Năm học** | Học kì 1 — Năm học 2026 - 2027 |
| **Khoa / Trường** | Khoa Hệ thống Thông tin — Trường ĐH Công nghệ Thông tin (UIT - ĐHQG-HCM) |

---

## 👨‍🏫 2. GIẢNG VIÊN HƯỚNG DẪN

- **Giảng viên phụ trách:** ThS. Tạ Việt Phương — [phuongtv@uit.edu.vn](mailto:phuongtv@uit.edu.vn)

---

## 👨‍🎓 3. THÀNH VIÊN NHÓM THỰC HIỆN

| STT | Họ và Tên | MSSV | Lớp | Vai trò | GitHub | Email |
| :---: | :--- | :---: | :---: | :---: | :---: | :--- |
| 1 | **Nguyễn Đoàn Đức Hiếu** | 24520500 | IS207.R11 | **Leader** | [@DalzielNguyen-1611](https://github.com/DalzielNguyen-1611) | [24520500@gm.uit.edu.vn](mailto:24520500@gm.uit.edu.vn) |
| 2 | **Nguyễn Văn Phát** | 24521314 | IS207.R11 | Member | — | [24521314@gm.uit.edu.vn](mailto:24521314@gm.uit.edu.vn) |
| 3 | **Trần Hoàng Phúc** | 24521398 | IS207.R11 | Member | — | [24521398@gm.uit.edu.vn](mailto:24521398@gm.uit.edu.vn) |
| 4 | **Dương Đình Thảo** | 24521645 | IS207.R11 | Member | — | [24521645@gm.uit.edu.vn](mailto:24521645@gm.uit.edu.vn) |
| 5 | **Trần Thị Thanh Thảo** | 24521651 | IS207.R11 | Member | — | [24521651@gm.uit.edu.vn](mailto:24521651@gm.uit.edu.vn) |
| 6 | **Hồ Thị Phương Thùy** | 24521751 | IS207.R11 | Member | — | [24521751@gm.uit.edu.vn](mailto:24521751@gm.uit.edu.vn) |

---

## 📂 4. TỔ CHỨC CẤU TRÚC KHO LƯU TRỮ (REPOSITORY)

Kho lưu trữ gồm 2 phần đồ án độc lập và được cấu trúc theo phân mục chuẩn mực:

```text
IS207-HTTP-FoL_Digital_Lib/
├── project-fol-library/              # [DỰ ÁN CHÍNH - MAIN PROJECT]
│   ├── app/                          # Mã nguồn backend MVC & Service-Repository
│   │   ├── Controllers/              # Xử lý request, gọi Service, trả response
│   │   ├── Models/                   # Định nghĩa thực thể dữ liệu & nghiệp vụ
│   │   ├── Services/                 # Logic nghiệp vụ mượn trả, xử lý vi phạm
│   │   ├── Repositories/             # Tương tác cơ sở dữ liệu qua PDO
│   │   ├── Middleware/               # Xác thực, phân quyền, kiểm tra request
│   │   ├── Core/                     # Router, Database Singleton, View engine, Autoloader
│   │   └── Validators/               # Kiểm tra và xác thực dữ liệu đầu vào
│   ├── config/                       # Cấu hình ứng dụng và cơ sở dữ liệu
│   ├── database/                     # Cơ sở dữ liệu và kịch bản khởi tạo
│   │   ├── migrations/               # Các kịch bản di chuyển lược đồ CSDL
│   │   └── seeders/                  # Dữ liệu mẫu (seeders)
│   ├── public/                       # Điểm truy cập Web công khai
│   │   └── assets/                   # Tài nguyên tĩnh
│   │       ├── css/
│   │       ├── js/
│   │       └── images/
│   ├── resources/                    # Giao diện HTML/PHP template chưa công khai trực tiếp
│   │   └── views/                    # Thư mục chứa giao diện views
│   ├── routes/                       # Tuyến đường web và api
│   ├── storage/                      # Lưu trữ nội bộ
│   │   ├── logs/                     # Nhật ký hoạt động
│   │   └── uploads/                  # Tệp tin tải lên
│   └── tests/                        # Kiểm thử mã nguồn
│       ├── Unit/
│       └── Integration/
│
└── mini-project-techcare-platform/   # [DỰ ÁN THÀNH PHẦN - MINI PROJECT]
    ├── frontend/                     # Presentation Tier (Giao diện người dùng)
    │   └── assets/                   # Tài nguyên tĩnh
    │       └── js/
    ├── backend/                      # Application Tier (100% PHP thuần OOP)
    │   ├── api/                      # Endpoints RESTful API
    │   ├── config/                   # Cấu hình kết nối CSDL
    │   ├── controllers/              # Bộ điều khiển nghiệp vụ
    │   ├── helpers/                  # Lớp tiện ích hỗ trợ
    │   └── models/                   # Lớp mô hình dữ liệu
    ├── database/                     # Data Tier (Cơ sở dữ liệu)
    └── docs/                         # Tài liệu kiến trúc và thiết kế
```

---

## 🏛️ 5. CHI TIẾT DỰ ÁN CHÍNH: FOL DIGITAL LIBRARY

### 5.1. Định hướng chức năng và mục tiêu hệ thống
- **Tra cứu và tìm kiếm tài liệu văn học**: Tìm kiếm nhanh theo tiêu đề, tác giả, ISBN và phân loại chuyên ngành (Lý luận & Phê bình Văn học, Văn học Việt Nam, Văn học Thế giới, Ngôn ngữ học & Việt ngữ học, Hán Nôm & Văn bản học).
- **Quản lý mượn/trả sách trực tuyến**: Hạn mức mượn tối đa 5 quyển/độc giả, thời hạn mượn 14 ngày, tự động tính tiền phạt trễ hạn.
- **Phân quyền người dùng**: Phân chia theo vai trò Admin, Thủ thư, Giảng viên, Sinh viên.
- **RESTful API độc lập**: Cung cấp API JSON cho toàn bộ nghiệp vụ phục vụ giao diện và tích hợp.

### 5.2. Khởi tạo và thiết lập Main Project (`project-fol-library`)

```bash
# 1. Chuyển vào thư mục dự án chính
cd project-fol-library

# 2. Khởi tạo biến môi trường từ mẫu
copy .env.example .env

# 3. Tạo cơ sở dữ liệu MySQL rỗng (nếu cần)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS fol_library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Khởi chạy máy chủ phát triển
php -S localhost:8000 -t public
```

Truy cập hệ thống tại: **[http://localhost:8000](http://localhost:8000)**

*Lưu ý:* Hiện tại dự án đã hoàn thành khởi tạo khung sườn kiến trúc thư mục chuẩn và hệ thống quy tắc phát triển ([AGENTS.md](project-fol-library/AGENTS.md)), sẵn sàng cho quá trình phát triển tính năng.
Chi tiết hướng dẫn toàn diện vui lòng tham khảo: [project-fol-library/README.md](project-fol-library/README.md).

---

## 🛠️ 6. THÔNG TIN MINI PROJECT: TECHCARE PLATFORM

Hệ thống đặt lịch cứu hộ và sửa chữa thiết bị IT 3-Tier (đã hoàn thành tại giai đoạn mini-project):
- **Frontend**: [mini-project-techcare-platform/frontend/index.html](mini-project-techcare-platform/frontend/index.html)
- **Backend**: [mini-project-techcare-platform/backend/index.php](mini-project-techcare-platform/backend/index.php)
- **Tài liệu kiến trúc 3-Tier**: [mini-project-techcare-platform/docs/architecture_3tier.md](mini-project-techcare-platform/docs/architecture_3tier.md)

---

## 📑 7. TIÊU CHÍ KỸ THUẬT & QUY ĐỊNH BẢO MẬT
- **Bảo mật cơ sở dữ liệu**: 100% truy vấn thực hiện qua PDO Prepared Statements, không ghép chuỗi SQL trực tiếp phòng chống SQL Injection.
- **Mã hóa mật khẩu**: Sử dụng giải thuật Bcrypt (`password_hash` và `password_verify`).
- **Phòng chống XSS**: Escape toàn bộ dữ liệu đầu ra với `htmlspecialchars()`.
- **Quản lý biến môi trường**: Tách biệt thông tin nhạy cảm vào `.env` và được `.gitignore` loại trừ, không commit lên GitHub.
