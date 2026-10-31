# TÀI LIỆU THIẾT KẾ & VẬN HÀNH: ONBOARDING SINH VIÊN & NÉN ẢNH THẺ TỰ ĐỘNG

> **Dự án:** Hệ thống Thư viện số Khoa Văn học – USSH, VNU-HCM (FoL Digital Library)  
> **Phiên bản:** 1.0.0  
> **Ngày hoàn thành:** 31/10/2026  
> **Phạm vi:** Đăng nhập lần đầu qua Google OAuth, Hoàn thiện hồ sơ sinh viên, Tự động thu nhỏ & nén ảnh WebP trên Server (PHP GD), Bảng điều khiển duyệt thẻ của Cán bộ thư viện.

---

## 1. TỔNG QUAN YÊU CẦU & BỐI CẢNH

Khi sinh viên đăng nhập lần đầu vào Thư viện số qua tài khoản Google trường (`@gm.uit.edu.vn`, `@sv.ussh.edu.vn`):
1. Hệ thống cần cho phép sinh viên kiểm tra thông tin, bổ sung Mã số sinh viên (MSSV), Lớp - Chuyên ngành đào tạo, Số điện thoại và Giới tính.
2. Bắt buộc sinh viên phải tải lên **ảnh chụp thẻ sinh viên** rõ nét để cán bộ thư viện đối chiếu danh tính trước khi cấp quyền mượn tài liệu và đặt chỗ phòng đọc tự học.
3. Tài khoản mới sau khi đăng nhập được tự động đặt ở trạng thái chờ xét duyệt (`ChoDuyet`). Sinh viên chỉ được truy cập trang `/onboarding` và `/logout`.
4. Cán bộ thư viện có trang quản lý chuyên dụng `/librarian/students` để xem danh sách hồ sơ chờ duyệt, mở modal phóng to ảnh thẻ và lựa chọn **Phê duyệt kích hoạt** (`HoatDong`) hoặc **Yêu cầu chụp lại thẻ** kèm ghi chú phản hồi.

---

## 2. BẢN CHẤT CƠ CHẾ NÉN & TỐI ƯU ẢNH TỰ ĐỘNG TRÊN SERVER (PHP GD)

### 2.1. Thách thức với file ảnh gốc từ điện thoại
- Ảnh chụp từ smartphone hiện nay có độ phân giải rất lớn ($3000 \times 4000\text{ px}$ đến $4000 \times 6000\text{ px}$), dung lượng từ **$3\text{ MB} - 8\text{ MB}$**.
- Nếu lưu trực tiếp:
  - Ổ cứng lưu trữ của gói hosting sinh viên sẽ cạn kiệt chỉ sau vài trăm tấm ảnh.
  - Tải trang danh sách chờ duyệt của thủ thư hoặc trang hồ sơ sinh viên sẽ bị nghẽn mạng do phải tải tệp quá lớn.
  - Ảnh chụp dọc từ điện thoại thường bị xoay ngang/ngược do cờ EXIF `Orientation` không được trình duyệt chuẩn hóa.

### 2.2. Quy trình xử lý tại `ImageOptimizerService`
Dịch vụ [`app/Services/ImageOptimizerService.php`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Services/ImageOptimizerService.php) thực hiện 5 bước tự động:

```
[File ảnh tải lên từ form (3-8 MB, JPEG/PNG/WebP)]
       │
       ▼
 1. Xác thực an toàn (MIME type thực tế, getimagesize, giới hạn dung lượng)
       │
       ▼
 2. Nạp ảnh vào bộ nhớ GD (imagecreatefromjpeg / imagecreatefrompng / imagecreatefromwebp)
       │
       ▼
 3. Tự động sửa góc xoay EXIF Orientation (chống xoay ngang ảnh điện thoại)
       │
       ▼
 4. Thu nhỏ tỷ lệ bảo toàn kích thước (Scale proportional <= 1000px, imagecopyresampled)
       │
       ▼
 5. Xuất và lưu định dạng WebP hiện đại (Quality 80%) -> Giảm >90% dung lượng (30-60 KB)
```

### 2.3. Hiệu quả thực nghiệm
Kết quả đo đạc thực tế từ suite kiểm thử đơn vị [`ImageOptimizerServiceTest`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/tests/Unit/ImageOptimizerServiceTest.php):
- Ảnh kiểm thử độ phân giải lớn: $2400 \times 1800\text{ px}$, dung lượng gốc **89.4 KB**.
- Sau khi qua dịch vụ: thu nhỏ xuống đúng $1000 \times 750\text{ px}$, định dạng `.webp`.
- Dung lượng lưu trữ thực tế: **3.1 KB** (giảm **96.5%** dung lượng lưu trữ so với tệp mẫu).

---

## 3. THIẾT KẾ CƠ SỞ DỮ LIỆU & KIẾN TRÚC ROUTING

### 3.1. Cập nhật lược đồ CSDL (Migration 000005)
```sql
-- 1. Mở rộng trạng thái tài khoản
ALTER TABLE `tai_khoan` 
MODIFY COLUMN `trang_thai` ENUM('ChoDuyet', 'HoatDong', 'Khoa') NOT NULL DEFAULT 'ChoDuyet';

-- 2. Bổ sung các trường quản lý duyệt thẻ vào bảng hồ sơ
ALTER TABLE `ho_so_nguoi_dung`
ADD COLUMN `anh_the_sv` VARCHAR(255) NULL COMMENT 'Đường dẫn file ảnh thẻ sinh viên đã nén WebP',
ADD COLUMN `ngay_gui_duyet` DATETIME NULL COMMENT 'Thời điểm sinh viên gửi hồ sơ chờ duyệt',
ADD COLUMN `ghi_chu_duyet` VARCHAR(255) NULL COMMENT 'Lý do từ chối hoặc ghi chú yêu cầu chụp lại thẻ';
```

### 3.2. Ma trận phân quyền & Điều hướng bảo mật
- **Tài khoản `ChoDuyet`**:
  - Khi cố gắng truy cập `/profile`, `/counter`, `/books/borrow` hoặc các trang nội bộ: [`AuthenticationMiddleware`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Middleware/AuthenticationMiddleware.php) tự động chuyển hướng về `/onboarding`.
  - Các đường dẫn được phép: `/onboarding`, `/onboarding/submit`, `/logout`.
- **Tài khoản `HoatDong`**:
  - Truy cập đầy đủ các chức năng theo vai trò RBAC (`STUDENT`, `LIBRARIAN`, `ADMIN`).
- **Phục vụ tệp tĩnh tải lên**:
  - Thiết lập Windows Directory Junction: `public/uploads <<===>> storage/uploads`.
  - Hỗ trợ định tuyến an toàn qua `public/index.php` với kiểm tra path traversal và header `Content-Type: image/webp`, `Cache-Control: public, max-age=86400`.

---

## 4. CHI TIẾT GIAO DIỆN NGƯỜI DÙNG

### 4.1. Màn hình Onboarding Sinh viên (`/onboarding`)
- **Ngôn ngữ thiết kế:** Chuẩn Academic Library (Màu giấy ấm `#fffdf6`, màu xanh hải quân `#24445c`, viền chỉ vàng `#d4af37`, phông chữ Literata).
- **Trạng thái thông minh:**
  - *Mới đăng nhập:* Banner hướng dẫn chào mừng nộp thông tin.
  - *Đã gửi hồ sơ:* Card trạng thái "⏳ Hồ sơ đang chờ cán bộ xét duyệt" kèm thời gian nộp và thumbnail ảnh thẻ WebP hiện tại.
  - *Bị yêu cầu chụp lại:* Card cảnh báo màu hổ phách "⚠️ Cán bộ yêu cầu chụp lại ảnh thẻ: [Lý do]".
  - *Đã được duyệt:* Card chúc mừng màu xanh ngọc kèm nút chuyển về Trang cá nhân.
- **Khu vực tải ảnh thẻ kéo thả (Drag & Drop zone):**
  - Kéo thả trực tiếp từ máy tính/điện thoại hoặc click chọn file.
  - Instant Preview hiển thị ảnh ngay lập tức bằng JavaScript FileReader, hiển thị tên file, dung lượng gốc và nhãn: `Sẵn sàng nén WebP (Resize <= 1000px, Quality 80%)`.
  - Nút gửi hồ sơ có hiệu ứng loading spinner chống double-submit.

### 4.2. Bảng điều khiển Duyệt thẻ Cán bộ (`/librarian/students`)
- **Thống kê:** Huy hiệu đỏ hiển thị số lượng hồ sơ đang chờ xử lý ngay trên Sidebar cán bộ.
- **Bảng danh sách:** Cung cấp thông tin Sinh viên, MSSV, Email, SĐT, Lớp, Chuyên ngành và thời gian gửi hồ sơ.
- **Thumbnail ảnh thẻ:** Hover phóng to nhẹ; khi click sẽ mở **Modal Preview độ phân giải cao** trên nền mờ tối (backdrop blur).
- **Thao tác nhanh trong Modal & Bảng:**
  - Nút **Duyệt (Approve)** màu xanh lá: Kích hoạt ngay lập tức tài khoản sang `HoatDong`, xóa ghi chú từ chối và ghi nhật ký kiểm toán `DuyetTheSinhVien`.
  - Nút **Yêu cầu chụp lại (Reject)** màu cam đỏ: Mở hộp thoại nhập lý do (VD: *Ảnh mờ, lóa đèn flash, thiếu góc thẻ*), cập nhật `ghi_chu_duyet` và ghi nhật ký kiểm toán `TuChoiTheSinhVien`.

---

## 5. BẰNG CHỨNG KIỂM THỬ HỆ THỐNG

Toàn bộ 9 Suites kiểm thử trong Master Test Runner [`tests/run_all_tests.php`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/tests/run_all_tests.php) đã được thực thi và đạt **100% PASS**:

| Tên Suite kiểm thử | Loại kiểm thử | Số test cases | Kết quả | Thời gian |
| :--- | :--- | :---: | :---: | :---: |
| `tests/Unit/AuthorizationServiceTest.php` | Unit (RBAC) | 12 | **PASS** | 0.144 s |
| `tests/Unit/BookServiceTest.php` | Unit (Book logic) | 15 | **PASS** | 0.168 s |
| `tests/Unit/GoogleAuthServiceTest.php` | Unit (OAuth 2.0) | 10 | **PASS** | 0.076 s |
| `tests/Unit/ImageOptimizerServiceTest.php` | Unit (WebP & GD) | 5 | **PASS** | 0.368 s |
| `tests/Unit/StudentCardApprovalTest.php` | Unit (Onboarding & Approval) | 7 | **PASS** | 0.107 s |
| `tests/Integration/ViewsDbIntegrationTest.php` | Integration (15 Views DB) | 15 | **PASS** | 0.244 s |
| `tests/Integration/BookManagementRegressionTest.php` | Integration (Book Pagination) | 6 | **PASS** | 0.106 s |
| `tests/Integration/AuthenticationAuthorizationRegressionTest.php` | Integration (Security RBAC) | 16 | **PASS** | 0.123 s |
| `tests/Integration/HttpEndToEndTest.php` | Live HTTP E2E (Server:8000) | 21 | **PASS** | 0.821 s |
| **TỔNG CỘNG** | **Toàn bộ hệ thống** | **107 Cases** | **100% PASS** | **2.157 s** |
