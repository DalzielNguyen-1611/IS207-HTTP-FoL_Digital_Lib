# FoL Digital Library — Hệ Thống Thư Viện Số Khoa Văn Học (IS207.R11)

<p align="center">
  <img src="https://img.shields.io/badge/Course-IS207-0077b6?style=for-the-badge&logo=googlescholar&logoColor=white" alt="Course IS207">
  <img src="https://img.shields.io/badge/Architecture-MVC%20%2B%20Service--Repository-0284c7?style=for-the-badge" alt="Architecture">
  <img src="https://img.shields.io/badge/PHP-8.2%20OOP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/MySQL-8.0%20(3NF)-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
</p>

---

## 📌 1. GIỚI THIỆU DỰ ÁN
**FoL Digital Library** (Faculty of Literature Digital Library) là hệ thống quản lý và tra cứu thư viện số chuyên biệt dành cho Khoa Văn học. Hệ thống hướng đến việc số hóa quy trình quản lý giáo trình, tuyển tập tác phẩm, tài liệu nghiên cứu văn học, ngôn ngữ học và luân chuyển mượn trả ấn phẩm cho sinh viên, giảng viên và thủ thư.

Dự án áp dụng kiến trúc **MVC kết hợp Service - Repository Pattern chuẩn mực**, 100% PHP thuần Hướng đối tượng (OOP), PDO Prepared Statements phòng chống SQL Injection, cấu trúc module hóa phân tầng rõ ràng (Clean Architecture).

Hiện tại, dự án đang ở giai đoạn **khởi tạo khung sườn kiến trúc (Architecture Skeleton)**, sẵn sàng để các thành viên và Agent bắt đầu triển khai các module tính năng theo đúng quy trình.

---

## 🏗️ 2. MÔ HÌNH KIẾN TRÚC HỆ THỐNG

Hệ thống được thiết kế theo mô hình phân tầng:
1. **Presentation Tier (Giao diện & Điểm vào)**: `public/index.php`, `resources/views/`
2. **Controller Tier (Điều khiển)**: Nhận HTTP Request, gọi Validator kiểm tra tính hợp lệ, điều phối gọi Service và trả response.
3. **Service Tier (Nghiệp vụ - Business Logic)**: Xử lý toàn bộ logic nghiệp vụ (quy tắc mượn/trả, thời hạn, tính phí trễ hạn, điều phối giao dịch).
4. **Repository Tier (Truy vấn dữ liệu)**: Tương tác trực tiếp với Database qua PDO Prepared Statements, cô lập hoàn toàn truy vấn SQL khỏi tầng nghiệp vụ.
5. **Data Tier**: Cơ sở dữ liệu MySQL chuẩn hóa 3NF, bảo toàn toàn vẹn dữ liệu.

```text
Request ──> public/index.php ──> Router ──> Middleware (Auth/Role/CORS)
                                                │
                                                ▼
                                         Controller ──> Validator
                                                │
                                                ▼
                                             Service (Business Rules)
                                                │
                                                ▼
                                           Repository (PDO Prepared Stmts)
                                                │
                                                ▼
                                         MySQL Database
```

---

## 📂 3. CẤU TRÚC THƯ MỤC DỰ ÁN

Cấu trúc thư mục khởi tạo ban đầu tuân thủ nghiêm ngặt thiết kế hệ thống:

```text
project-fol-library/
├── app/                              # Toàn bộ mã nguồn backend theo MVC & Service-Repository
│   ├── Controllers/                  # Xử lý request, gọi Service, trả response
│   ├── Models/                       # Định nghĩa dữ liệu và mô hình miền nghiệp vụ
│   ├── Services/                     # Xử lý logic nghiệp vụ
│   ├── Repositories/                 # Truy vấn và thao tác dữ liệu qua PDO
│   ├── Middleware/                   # Xác thực, phân quyền, kiểm tra request
│   ├── Core/                         # Router, Controller cơ sở, Database, View renderer
│   └── Validators/                   # Kiểm tra dữ liệu đầu vào
│
├── config/                           # Cấu hình môi trường và hệ thống
│   ├── app.php                       # Cấu hình ứng dụng, múi giờ, bảo mật
│   └── database.php                  # Cấu hình kết nối cơ sở dữ liệu PDO
│
├── database/                         # Cơ sở dữ liệu và kịch bản khởi tạo
│   ├── schema.sql                    # Kịch bản khởi tạo cấu trúc bảng CSDL
│   ├── migrations/                   # Các tập lệnh migration phân phiên bản
│   └── seeders/                      # Dữ liệu khởi tạo mẫu
│
├── public/                           # Điểm truy cập web và tài nguyên công khai
│   ├── index.php                     # Front Controller tiếp nhận toàn bộ request
│   ├── .htaccess                     # Cấu hình Rewrite URL sạch
│   ├── favicon.ico                   # Icon website
│   └── assets/                       # Tài nguyên tĩnh
│       ├── css/                      # Tệp tin định kiểu CSS
│       ├── js/                       # Tệp tin kịch bản JavaScript
│       └── images/                   # Hình ảnh, biểu trưng
│
├── resources/                        # Giao diện HTML/PHP template chưa công khai trực tiếp
│   └── views/                        # Giao diện các trang và thành phần
│
├── routes/                           # Khai báo tuyến đường hệ thống
│   ├── web.php                       # Tuyến đường giao diện Web (HTML)
│   └── api.php                       # Tuyến đường RESTful API (JSON)
│
├── storage/                          # Lưu trữ nội bộ (Bảo mật, cấm truy cập trực tiếp)
│   ├── logs/                         # Ghi nhận log lỗi hệ thống
│   └── uploads/                      # Lưu trữ ảnh bìa, file đính kèm
│
├── tests/                            # Kiểm thử mã nguồn
│   ├── Unit/                         # Kiểm thử đơn vị
│   └── Integration/                  # Kiểm thử tích hợp
│
├── composer.json                     # Quản lý phụ thuộc PHP & Autoload PSR-4
├── .env.example                      # Mẫu biến môi trường (Database, App Secret,...)
├── .gitignore                        # Danh sách loại trừ file nhạy cảm và thư mục rác
├── AGENTS.md                         # Toàn bộ 13 quy định bắt buộc dành cho Coding & Testing Agent
└── README.md                         # Hướng dẫn chi tiết dự án
```

---

## 🚀 4. HƯỚNG DẪN CÀI ĐẶT & KHỞI ĐỘNG KHUNG SƯỜN

### 4.1. Yêu cầu môi trường
- PHP >= 8.1 (Khuyến nghị PHP 8.2)
- Tiện ích mở rộng PHP: `pdo_mysql`, `mbstring`, `json`
- MySQL >= 8.0 (hoặc MariaDB tương đương)
- Composer (quản lý phụ thuộc và nạp lớp theo chuẩn PSR-4)

### 4.2. Các bước thiết lập ban đầu
1. **Thiết lập biến môi trường**:
   ```bash
   cp .env.example .env
   # Hoặc trên Windows PowerShell:
   Copy-Item .env.example .env
   ```

2. **Cập nhật Composer Autoload**:
   ```bash
   composer dump-autoload
   ```

3. **Khởi chạy máy chủ phát triển (Development Server)**:
   ```bash
   php -S localhost:8000 -t public
   ```
   Truy cập tại: `http://localhost:8000`

---

## 📜 5. NGUYÊN TẮC PHÁT TRIỂN & KIỂM THỬ

Mọi thành viên và AI Agent khi tham gia phát triển dự án bắt buộc phải đọc và tuân thủ toàn bộ 13 nguyên tắc trong tệp tin:
👉 **[AGENTS.md](AGENTS.md)**

*Tóm tắt quy tắc trọng yếu:*
- **Quy trình Git**: Luôn kiểm tra nhánh trước khi viết code. Cấm code trực tiếp trên `main`. Bắt buộc tạo nhánh riêng (`feature/`, `fix/`, `docs/`,...) và tạo PR. Chỉ Trưởng nhóm Nguyễn Đoàn Đức Hiếu ([@DalzielNguyen-1611](https://github.com/DalzielNguyen-1611)) mới có quyền merge vào `main`.
- **Trình tự 8 bước Backend**: Repository $\rightarrow$ Model $\rightarrow$ Service $\rightarrow$ Validator $\rightarrow$ Controller $\rightarrow$ Middleware $\rightarrow$ Routes $\rightarrow$ Integration.
- **Quy định Testing độc lập**: Không tự ý báo PASS khi chưa thực hiện phép thử thực tế. Báo cáo phân biệt rõ: PASS, FAIL, BLOCKED, NOT RUN.
