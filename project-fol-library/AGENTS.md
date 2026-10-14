# Quy Định Và Nguyên Tắc Dành Cho Agent (AGENTS.md)
# Project: FoL Digital Library (Faculty of Literature Management System - Khoa Văn học)

Tài liệu này định nghĩa hệ thống nguyên tắc bắt buộc có số thứ tự (STT) dành cho AI Coding Agent và Testing Agent khi tham gia phát triển, chỉnh sửa và kiểm thử mã nguồn dự án.

---

# PHẦN I: NGUYÊN TẮC QUẢN TRỊ DỰ ÁN & QUY TRÌNH PHÁT TRIỂN

## 📌 Rule 1: Cấu trúc thư mục trong README.md bên ngoài chỉ ghi đến cấp thư mục
- Khi cập nhật hoặc chỉnh sửa tập tin `README.md` ở cấp ngoài cùng của repository (`IS207_HTTP_Project/README.md`), phần cây cấu trúc thư mục (Directory Tree) **chỉ được phép ghi đến cấp thư mục (directories/folders)**.
- **Tuyệt đối không liệt kê các file riêng lẻ** (ví dụ: `composer.json`, `index.php`, `.env.example`, `.gitignore`, `schema.sql`, `*.php`,...).
- Chi tiết danh sách tệp tin và mô tả cụ thể của từng file sẽ được ghi đầy đủ và chi tiết tại tập tin `project-fol-library/README.md`.

## 📌 Rule 2: Quy trình làm việc với Git, Phân nhánh, Commit Message & Quyền Merge
1. **Kiểm tra nhánh trước khi viết code:**
   - Trước khi thực hiện bất kỳ thay đổi nào, bắt buộc phải kiểm tra nhánh hiện tại (`git branch --show-current` hoặc `git status`).
   - **Tuyệt đối KHÔNG code hoặc commit trực tiếp trên nhánh `main`**.
2. **Quy tắc tạo nhánh (Branching Convention):**
   - Nếu đang ở nhánh `main`, bắt buộc phải tạo nhánh mới trước khi thực hiện công việc:
     ```bash
     git checkout -b <loại-nhánh>/<tên-ngắn-gọn>
     ```
   - Định dạng tiền tố nhánh:
     - `feature/<tên-tính-năng>`: Thêm tính năng mới (ví dụ: `feature/borrow-book`, `feature/book-filter`).
     - `fix/<tên-lỗi>`: Sửa lỗi (ví dụ: `fix/login-session`, `fix/sql-injection-risk`).
     - `refactor/<tên-module>`: Cải tiến cấu trúc code (ví dụ: `refactor/loan-service`).
     - `test/<tên-module>`: Bổ sung bộ kiểm thử (ví dụ: `test/auth-validator`).
     - `docs/<tên-tài-liệu>`: Cập nhật tài liệu kỹ thuật (ví dụ: `docs/api-guide`).
3. **Quy định Commit Message (Conventional Commits):**
   - Mọi commit khi push lên Git bắt buộc phải tuân thủ đúng định dạng:
     ```text
     <type>(<scope>): <mô tả ngắn gọn hành động thực hiện>
     ```
   - Các `type` được phép:
     - `feat`: Thêm tính năng mới (ví dụ: `feat(books): thêm bộ lọc tìm kiếm theo chuyên ngành văn học`).
     - `fix`: Sửa lỗi hệ thống (ví dụ: `fix(auth): sửa lỗi không lưu phiên đăng nhập của sinh viên`).
     - `refactor`: Tái cấu trúc mã nguồn, không đổi logic (ví dụ: `refactor(loan): tối ưu hàm tính tiền phạt`).
     - `docs`: Cập nhật tài liệu, hướng dẫn (ví dụ: `docs(agents): cập nhật quy định phân nhánh git`).
     - `style`: Định dạng code theo chuẩn PSR-12, không ảnh hưởng logic.
     - `test`: Thêm hoặc cập nhật unit/integration test (ví dụ: `test(auth): bổ sung test case cho AuthValidator`).
     - `chore`: Tác vụ bảo trì, cấu hình, gitignore (ví dụ: `chore(env): cập nhật file cấu hình mẫu`).
   - *Cấm tuyệt đối các commit message mơ hồ như: "update", "fix", "done", "edit code".*
4. **Quy định Merge & Phân quyền xét duyệt:**
   - Sau khi hoàn thành và vượt qua kiểm thử trên nhánh riêng, đẩy nhánh lên kho lưu trữ:
     ```bash
     git push origin <tên-nhánh>
     ```
   - **Chỉ được phép tạo Pull Request (PR) / Yêu cầu Merge** mô tả rõ nội dung thay đổi và kết quả test.
   - **TUYỆT ĐỐI KHÔNG TỰ Ý MERGE VÀO NHÁNH `main`**.
   - **CHỈ TRƯỞNG NHÓM (Leader: Nguyễn Đoàn Đức Hiếu — @DalzielNguyen-1611) MỚI CÓ QUYỀN DUYỆT VÀ THỰC HIỆN MERGE VÀO NHÁNH `main`**.

## 📌 Rule 3: Các công nghệ chính & Giới hạn nền tảng
- **Backend**: **PHP** (100% PHP thuần hướng đối tượng OOP, phiên bản PHP 8.x).
- **Database**: **MySQL / MariaDB** hoặc hệ quản trị cơ sở dữ liệu đã được nhóm/giảng viên phê duyệt.
- **Frontend**: **HTML, CSS, JavaScript**.
- **CSS Framework**: Sử dụng **Bootstrap**, **Tailwind CSS** hoặc công nghệ đã được nhóm lựa chọn.
- **Kiến trúc**: **MVC**, có thể kết hợp **Service Layer** và **Repository/DAO** theo thiết kế đã duyệt.
- **Giới hạn nền tảng & cấm kỵ**:
  - Tuyệt đối không sử dụng Node.js, Python, Java, .NET hoặc bất kỳ nền tảng nào khác để thay thế backend PHP.
  - Node.js chỉ được sử dụng cho frontend toolchain nếu dự án được cấu hình và phê duyệt như vậy.
  - Không tự ý thêm framework, thư viện hoặc dependency mới khi chưa cần thiết; nếu cần, phải giải thích lý do và xin phê duyệt trước khi cài đặt.

## 📌 Rule 4: Chuẩn mã nguồn & Autoloading PSR-4
- Tuân thủ nghiêm ngặt chuẩn **PSR-4** với namespace gốc: `App\` ánh xạ đến thư mục `app/`, `Tests\` ánh xạ đến thư mục `tests/`.
- Mã nguồn viết bằng **PHP 8.x OOP**, khai báo kiểu dữ liệu rõ ràng (Type Hinting, Return Types).
- Duy trì tính độc lập: Luôn đảm bảo hệ thống có thể chạy được thông qua cả Composer Autoload lẫn `App\Core\Autoloader` độc lập.

## 📌 Rule 5: An toàn bảo mật & Quản lý biến môi trường
- Tuyệt đối không commit file `.env` hoặc các thông tin bảo mật (mật khẩu CSDL, secret key, token) lên Git.
- Mọi biến cấu hình mới cần được khai báo mẫu trong `.env.example` và thiết lập giá trị mặc định an toàn trong thư mục `config/`.
- Mọi dữ liệu xuất ra màn hình HTML phải được xử lý qua `htmlspecialchars()` để ngăn chặn lỗ hổng XSS.
- Thư mục `storage/` phải luôn được bảo vệ, cấm truy cập trực tiếp từ phía Web Client.

---

# PHẦN II: QUY ĐỊNH PHÁT TRIỂN GIAO DIỆN & THÊM MỚI TRANG UI (UI CODING RULES)

## 📌 Rule 6: Quy chuẩn thêm mới một trang giao diện (UI Page Implementation Standard)

Khi nhận nhiệm vụ xây dựng hoặc bổ sung một trang giao diện mới (chỉ tính UI/Frontend mock), Agent bắt buộc phải tuân thủ nghiêm ngặt 6 quy chuẩn sau:

### 1. Phạm vi công việc: Chỉ xử lý tầng giao diện (UI-Only Scope)
- **Giới hạn trách nhiệm**: Chỉ viết mã giao diện hiển thị (HTML/PHP template, CSS, JS tĩnh, mock data và điều hướng Front Controller).
- **Tuyệt đối cấm**: Không tự ý viết mã nghiệp vụ Backend (không viết Service, Repository, Model, không tự tạo bảng CSDL hoặc chạy migration) trừ khi có task Backend riêng được giao.
- **Tính độc lập của UI**: Màn hình phải tự vận hành và hiển thị đầy đủ trạng thái dữ liệu (đang tải, có dữ liệu, danh sách rỗng, phân trang) bằng Mock Data mà không phụ thuộc vào kết nối cơ sở dữ liệu thật.

### 2. Cấu trúc thư mục & Nguyên tắc Single Source of Truth
- **Vị trí lưu trữ View**: Mọi file giao diện bắt buộc phải đặt trong thư mục:
  ```text
  project-fol-library/resources/views/<tên-module>/index.php
  ```
  *(Ví dụ: `resources/views/home/index.php`, `resources/views/collections/index.php`, `resources/views/contact/index.php`)*.
- **CẤM TUYỆT ĐỐI**: Không bao giờ tạo các file `.html` tĩnh rải rác bên trong thư mục `public/` (ví dụ cấm tạo `public/index.html`, `public/contact.html`, `public/collections.html`,...). Mọi file `.html` tĩnh rải rác trong `public/` được coi là vi phạm cấu trúc dự án và phải bị xóa bỏ ngay lập tức.
- **Quản lý tài nguyên tĩnh**: Toàn bộ CSS, JavaScript, hình ảnh phải đặt tập trung trong `public/assets/`:
  - `public/assets/css/`: Bảng kiểu mẫu CSS dùng chung hoặc riêng cho module.
  - `public/assets/js/`: Script tương tác phía trình duyệt.
  - `public/assets/images/`: Hình ảnh, biểu tượng, logo, banner hệ thống.

### 3. Cơ chế định tuyến Clean URL qua Front Controller (`public/index.php`)
- Mọi trang UI mới phải được đăng ký route trong Front Controller duy nhất tại `public/index.php`.
- **Định dạng URL chuẩn**: Bắt buộc dùng Clean URL thân thiện người dùng, không để lộ phần mở rộng `.php` hoặc `.html`:
  - Trang chủ: `/` $\rightarrow$ `resources/views/home/index.php`
  - Bộ sưu tập: `/collections` $\rightarrow$ `resources/views/collections/index.php`
  - Liên hệ: `/contact` $\rightarrow$ `resources/views/contact/index.php`
- Front Controller chịu trách nhiệm:
  1. Chuẩn hóa đường dẫn (tự động loại bỏ dấu gạch chéo cuối URL).
  2. Ánh xạ an toàn tới file view tương ứng trong `resources/views/`.
  3. Trả về mã HTTP `404 Not Found` kèm trang thông báo thân thiện nếu route hoặc view không tồn tại.

### 4. Quy chuẩn Design System & Nhận diện thương hiệu Khoa Văn học
Mọi trang UI mới phải kế thừa chuẩn nhận diện thương hiệu đã thống nhất:
- **Typography**: Bắt buộc nạp Google Fonts `Roboto, sans-serif` với các trọng số `300, 400, 500, 700`.
- **Bảng mã màu chuẩn (Brand Color Palette)**:
  - Màu xanh lá chủ đạo (Brand Primary Green): `#1b8846` (hover: `#156f38`).
  - Màu xanh ĐHQG (VNU Blue): `#005baa` (navy đậm: `#003e7e`, top bar: `#0b2240`).
  - Màu vàng điểm xuyết (Accent Gold): `#c59b27`.
  - Màu chữ: Chữ chính `#212529`, chữ phụ/mô tả `#555555` hoặc `#6c757d`.
  - Màu nền & viền: Nền body `#f8f9fa`, nền thẻ card `#ffffff`, đường kẻ viền `#dee2e6`.
- **Logo hệ thống**: Bắt buộc dùng logo tròn chính thức nền trong suốt tại `public/assets/images/logo.png`. Kích thước hiển thị chuẩn trong Header là `68px x 68px`.
- **Kế thừa Layout**:
  - **Top bar**: Chiều cao chuẩn 40px (màu xanh navy đậm `#003e7e` hoặc `#0b2240`, chữ trắng, chứa chọn ngôn ngữ & liên kết tiện ích).
  - **Header**: Chiều cao chuẩn 110px, chứa logo và khối chữ 3 cấp: ĐẠI HỌC QUỐC GIA TP.HCM - TRƯỜNG ĐH KHXH&NV - KHOA VĂN HỌC.
  - **Container**: Độ rộng tối đa chuẩn `1200px` - `1280px` căn giữa màn hình (`margin: 0 auto;`).
  - **Footer**: Chứa đầy đủ thông tin bản quyền và liên hệ của Khoa Văn học.

### 5. Dữ liệu mẫu (Mock Data) & Tiêu chuẩn an toàn tầng View
- Khai báo Mock Data rõ ràng bằng mảng PHP thuần (`array`) ở đầu file view hoặc file data mẫu dùng chung để giả lập cấu trúc dữ liệu thực tế.
- **Bảo mật XSS**: Bắt buộc dùng `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')` cho mọi biến mock data khi in ra HTML.
- **Tuyệt đối cấm**: Không mở kết nối cơ sở dữ liệu (`PDO`, `mysqli`), không thực hiện truy vấn SQL (`SELECT`, `INSERT`,...) trực tiếp trong file view.

### 6. Quy trình 5 bước triển khai một trang UI mới
Khi thực hiện tạo một trang mới, thực hiện tuần tự theo 5 bước:

| Bước | Hành động cụ thể |
|---|---|
| **1. Tạo nhánh Git** | Từ nhánh cập nhật mới nhất, tạo nhánh theo Rule 2: `git checkout -b feature/<tên-trang>-ui`. |
| **2. Tạo View File** | Tạo thư mục và file view tại `resources/views/<module>/index.php`. |
| **3. Bổ sung Assets** | Đặt file CSS/JS hoặc hình ảnh riêng vào `public/assets/css/`, `public/assets/js/`, `public/assets/images/`. |
| **4. Đăng ký Route** | Mở `public/index.php` và thêm route Clean URL tương ứng trỏ tới file view vừa tạo. |
| **5. Kiểm thử & Đẩy mã** | Chạy PHP Server (`php -S localhost:8000 -t public`), kiểm tra trực quan trên trình duyệt (desktop & mobile), commit theo chuẩn Conventional Commits `feat(ui): ...` và tạo PR tới Leader. |

---

## 📌 Rule 7: Bảng tiêu chí nghiệm thu giao diện (UI Acceptance Checklist)

Trước khi gửi Pull Request hoặc báo cáo hoàn thành nhiệm vụ UI, Agent bắt buộc phải kiểm tra đạt 100% các tiêu chí sau:

- [ ] **Vị trí file đúng chuẩn**: View nằm trong `resources/views/<module>/index.php`; không có bất kỳ file `.html` nào trong `public/`.
- [ ] **Định tuyến Clean URL hoạt động**: Truy cập được trang qua URL sạch (ví dụ: `http://localhost:8000/<route>`) mà không cần gõ `.php` hay `.html`.
- [ ] **Chuẩn Typography & Font**: Đã nhúng và hiển thị đúng font chữ `Roboto, sans-serif`.
- [ ] **Chuẩn Màu sắc & Logo**: Sử dụng đúng logo `public/assets/images/logo.png` và các mã màu trong Brand Palette.
- [ ] **Đồng bộ Header/Footer**: Kích thước Top bar (40px) và Header (110px, logo 68px) khớp với toàn bộ hệ sinh thái website.
- [ ] **Mock Data an toàn**: Dữ liệu mẫu hiển thị đầy đủ các trạng thái, có thoát mã ký tự đặc biệt qua `htmlspecialchars()`, không gọi DB.
- [ ] **Responsive & Thẩm mỹ**: Giao diện không vỡ layout khi co dãn màn hình, khoảng cách (padding/margin) hài hòa, chuyên nghiệp.
- [ ] **Tuân thủ Git Workflow**: Commit đúng định dạng `feat(ui): ...`, push lên nhánh `feature/...` và tạo PR chờ Leader duyệt (Rule 2).

---

# PHẦN III: QUY ĐỊNH PHÁT TRIỂN BACKEND (BACKEND CODING RULES)

## 📌 Rule 8: Nguyên tắc chung phát triển Backend
- Chỉ triển khai Backend bằng PHP, sử dụng MySQL/MariaDB và PDO theo cấu hình dự án.
- Tuân thủ kiến trúc MVC kết hợp Service–Repository hiện có.
- Trước khi tạo file mới, kiểm tra code và thành phần tương ứng đã tồn tại chưa.
- Chỉ tạo hoặc sửa file thuộc phạm vi tính năng được giao; không tự ý thay đổi thiết kế, database schema hoặc API contract đã duyệt.
- Không gộp Controller, nghiệp vụ, truy vấn SQL và giao diện vào cùng một file.
- Không tạo file hoặc lớp không cần thiết chỉ để tuân theo cấu trúc mẫu.

## 📌 Rule 9: Quy định code theo thư mục

| Thư mục | Trách nhiệm và loại code cần viết |
|---|---|
| `app/Controllers/` | Nhận HTTP request, lấy dữ liệu đầu vào, gọi Service và trả response hoặc render View. |
| `app/Models/` | Định nghĩa thực thể và cấu trúc dữ liệu nghiệp vụ theo thiết kế (`Book.php`, `User.php`, `Loan.php`). |
| `app/Services/` | Xử lý nghiệp vụ như mượn/trả sách, kiểm tra điều kiện mượn, xử lý vi phạm và điều phối thao tác dữ liệu. |
| `app/Repositories/` | Viết truy vấn SQL và thao tác CRUD bằng PDO; luôn sử dụng prepared statements. |
| `app/Middleware/` | Xử lý xác thực đăng nhập, phân quyền và các kiểm tra request dùng chung. |
| `app/Core/` | Chứa hạ tầng dùng chung (Router, Database connection, View engine, Autoloader). Chỉ sửa khi mở rộng nền tảng. |
| `app/Validators/` | Kiểm tra dữ liệu đầu vào phía server theo quy tắc đã thống nhất. |
| `config/` | Cấu hình ứng dụng, database và môi trường; không hardcode thông tin bí mật trong mã nguồn. |
| `database/migrations/` | Tạo hoặc thay đổi schema theo migration đã được phê duyệt. Không tự ý sửa schema khi làm tính năng thường. |
| `database/seeders/` | Viết dữ liệu mẫu phục vụ khởi tạo hoặc phát triển khi được yêu cầu. |
| `routes/` | Khai báo URL, HTTP method, Controller và middleware tương ứng. |
| `resources/views/` | Chứa PHP template phục vụ giao diện; không đặt truy vấn SQL hoặc nghiệp vụ Backend tại đây. |
| `public/assets/` | Chỉ chứa tài nguyên tĩnh công khai. Không đặt logic nghiệp vụ Backend hoặc thông tin bí mật tại đây. |
| `storage/logs/` | Lưu log lỗi và hoạt động theo cấu hình; không ghi mật khẩu, token hoặc dữ liệu nhạy cảm. |
| `storage/uploads/` | Lưu file tải lên theo chính sách bảo mật; không mặc định cho phép truy cập trực tiếp từ trình duyệt. |
| `tests/Unit/` | Chứa unit test cho các thành phần Backend độc lập. |
| `tests/Integration/` | Chứa test tích hợp giữa các lớp, database và endpoint khi phù hợp. |

## 📌 Rule 10: Thứ tự triển khai một tính năng Backend
Thực hiện nghiêm ngặt theo trình tự 8 bước:
1. **Repository:** Triển khai truy vấn dữ liệu nếu tính năng cần truy cập database.
2. **Model:** Bổ sung hoặc cập nhật thực thể nếu kiến trúc yêu cầu.
3. **Service:** Triển khai quy tắc nghiệp vụ và điều phối thao tác dữ liệu.
4. **Validator:** Bổ sung kiểm tra đầu vào nếu cần.
5. **Controller:** Tiếp nhận request, gọi Service và trả response.
6. **Middleware:** Bổ sung hoặc cấu hình xác thực, phân quyền nếu cần.
7. **Routes:** Đăng ký endpoint và gắn middleware phù hợp.
8. **Integration:** Rà soát kết nối giữa các lớp và chuyển sang quy trình kiểm thử.

*Lưu ý: Không tạo file ở bước không cần thiết. Nếu thay đổi chỉ liên quan đến một lớp, chỉ sửa lớp đó và các thành phần thực sự bị ảnh hưởng.*

## 📌 Rule 11: Các quy định bắt buộc trong Backend
- Controller không chứa truy vấn SQL trực tiếp.
- Service không chịu trách nhiệm render HTML hoặc xử lý HTTP routing.
- Repository không chứa quy tắc nghiệp vụ.
- Mọi truy vấn có dữ liệu đầu vào phải sử dụng prepared statements.
- Mọi endpoint cần bảo vệ phải được kiểm tra quyền ở phía server.
- Không tự ý thay đổi database schema, route hoặc response contract ngoài phạm vi được duyệt.
- Không tự kết luận code đã vượt qua kiểm thử; phải thực hiện theo Testing Rules riêng.
- Sau mỗi nhiệm vụ, báo cáo các file đã tạo/sửa và nội dung thay đổi.

---

# PHẦN IV: QUY ĐỊNH KIỂM THỬ (TESTING RULES)

## 📌 Rule 12: Vai trò và nguyên tắc kiểm thử độc lập
- Đóng vai trò Testing Agent độc lập sau khi Coding Agent hoàn thành nhiệm vụ.
- Kiểm thử bám sát yêu cầu và tiêu chí chấp nhận đã duyệt; không tự ý thay đổi hoặc tự tạo tiêu chí nghiệp vụ mới.
- Không mặc định code chạy được đồng nghĩa với tính năng đúng; tuyệt đối không báo PASS nếu chưa thực hiện phép kiểm tra thực tế.
- Phân biệt minh bạch 4 trạng thái: **PASS**, **FAIL**, **BLOCKED**, **NOT RUN**.

## 📌 Rule 13: Quy trình và các giai đoạn kiểm thử trọng yếu

| Giai đoạn | Nhiệm vụ kiểm thử trọng tâm |
|---|---|
| **1. Phạm vi test** | Đọc yêu cầu; xác định luồng thành công, thất bại, ca biên và các lớp/endpoint/bảng CSDL bị ảnh hưởng. |
| **2. Kiểm tra tĩnh** | Chạy `php -l` cú pháp; rà soát autoload, dependency, route mapping, prepared statements và kiểm tra lộ lọt bí mật. |
| **3. Unit test** | Kiểm thử độc lập Service (nghiệp vụ), Repository (truy vấn), Controller (điều phối), Validator (dữ liệu biên). Cách ly DB nếu có thể. |
| **4. Integration test** | Kiểm tra chuỗi Route $\rightarrow$ Controller $\rightarrow$ Service $\rightarrow$ Repository $\rightarrow$ DB; kiểm tra rollback transaction và auth trên môi trường test riêng. |
| **5. Functional test** | Kiểm tra trường hợp thành công, dữ liệu không hợp lệ, không tìm thấy, không đủ quyền và các ca biên (chuỗi rỗng, ký tự đặc biệt, phân trang). |
| **6. Security test** | Kiểm tra SQL Injection, XSS, CSRF, bypass phân quyền endpoint, lộ token/lỗi nội bộ và bắt buộc validation phía server. |

## 📌 Rule 14: Quy tắc xử lý khi phát hiện lỗi & Regression test
- **Khi phát hiện lỗi**: Ghi lại bước tái hiện, input, kết quả mong đợi vs thực tế, log lỗi (không lộ bí mật); xác định thành phần gây lỗi và báo Coding Agent sửa.
- **Nguyên tắc test**: Tuyệt đối không sửa test case để biến lỗi implementation thành PASS (trừ khi nhóm xác nhận test sai yêu cầu).
- **Regression test**: Sau khi sửa lỗi, bắt buộc chạy lại test thất bại, kiểm tra các module phụ thuộc liên quan và chạy bộ test tổng thể trước khi nghiệm thu.

## 📌 Rule 15: Điều kiện kết luận & Chuẩn báo cáo kiểm thử
- **Điều kiện kết luận:**
  - **PASS**: 100% kiểm tra bắt buộc trong phạm vi đều đạt kết quả thực tế.
  - **FAIL**: Có ít nhất một kiểm tra bắt buộc thất bại.
  - **BLOCKED**: Không thể kiểm tra do thiếu môi trường, dependency, quyền hoặc dữ liệu.
  - **NOT RUN**: Chưa thực hiện kiểm tra. *Không báo cáo tính năng PASS khi còn test ở trạng thái FAIL, BLOCKED hoặc NOT RUN.*
- **Nội dung báo cáo kiểm thử tối thiểu:**
  1. Phạm vi và phiên bản code được kiểm thử.
  2. Môi trường và công cụ đã sử dụng.
  3. Danh sách test case: Input, Kết quả mong đợi vs Kết quả thực tế.
  4. Kết quả từng loại test (Static, Unit, Integration, Functional, Security, Regression).
  5. Các lỗi phát hiện, mức độ ảnh hưởng và cách tái hiện.
  6. Các test chưa chạy hoặc bị chặn kèm lý do cụ thể.
  7. Kết luận tổng thể: **PASS**, **FAIL** hoặc **BLOCKED** (dựa trên số liệu và log thực tế, không tự tạo kết quả).
