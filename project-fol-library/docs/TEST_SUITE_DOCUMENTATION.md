# TÀI LIỆU TOÀN DIỆN VỀ BỘ KIỂM THỬ HỆ THỐNG THƯ VIỆN SỐ (FoL DIGITAL LIBRARY TEST SUITE)

**Dự án:** Hệ thống Thư viện số Khoa Văn học - USSH, VNU-HANOI (`project-fol-library`)  
**Môn học / Mã đề tài:** Công nghệ Web và Dịch vụ Trực tuyến (IS207)  
**Nhóm tác giả:** Nhóm Đồ Án IS207.R11  
**Ngày cập nhật:** 31/10/2026  
**Phiên bản tài liệu:** 2.2 (Bổ sung cơ chế Phân trang & Lọc đa tiêu chí phía Server-Side MVC chuẩn 10 đầu sách/trang; duy trì 102/102 test cases 100% PASS)  

---

## 1. TỔNG QUAN HỆ THỐNG KIỂM THỬ (EXECUTIVE SUMMARY)

Nhằm đảm bảo chất lượng phần mềm đạt tiêu chuẩn cao nhất về **tính đúng đắn nghiệp vụ, hiệu năng truy vấn, an toàn bảo mật (RBAC/OWASP)** và **trải nghiệm người dùng đồng nhất**, toàn bộ dự án đã được bao phủ bởi hệ thống kiểm thử tự động nhiều tầng:

* **Tổng số bộ kiểm thử (Test Suites):** 7 bộ kiểm thử chuyên biệt.
* **Tổng số kịch bản kiểm thử (Test Cases):** **102 / 102 Test Cases**.
* **Tỷ lệ thành công:** **100% PASS (0 thất bại, 0 lỗi cảnh báo)**.
* **Thời gian thực thi toàn bộ:** ~4.7 giây (chạy trực tiếp trên môi trường PHP 8.2 + MySQL/MariaDB).
* **Công cụ chạy tổng thể (Master Runner):** [`tests/run_all_tests.php`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/tests/run_all_tests.php).

```
========================================================================================
                              PHÂN BỔ BỘ KIỂM THỬ (102 TEST CASES)
========================================================================================
Nhóm Kiểm thử Đơn vị (Unit Tests):              49 Test Cases (100% PASS)
  ├── 1. AuthorizationServiceTest.php            : 15 Test Cases
  ├── 2. BookServiceTest.php                     : 16 Test Cases
  └── 3. GoogleAuthServiceTest.php               : 18 Test Cases
----------------------------------------------------------------------------------------
Nhóm Kiểm thử Tích hợp & E2E (Integration):     53 Test Cases (100% PASS)
  ├── 4. ViewsDbIntegrationTest.php              : 13 Test Cases
  ├── 5. BookManagementRegressionTest.php        :  7 Test Cases
  ├── 6. AuthenticationAuthorizationTest.php     : 16 Test Cases (AUTH-01 -> AUTH-16)
  └── 7. HttpEndToEndTest.php                    : 17 Test Cases (E2E-01  -> E2E-17)
========================================================================================
TỔNG CỘNG: 102 / 102 TEST CASES ĐẠT CHUẨN XUẤT SẮC
========================================================================================
```

---

## 2. DANH MỤC VÀ CHI TIẾT CÁC BỘ KIỂM THỬ

### 2.1. Nhóm Kiểm thử Đơn vị (Unit Tests)

#### Suite 1: `tests/Unit/AuthorizationServiceTest.php` (15 Test Cases)
* **Đối tượng kiểm thử:** [`App\Services\AuthorizationService`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Services/AuthorizationService.php)
* **Mục tiêu:** Xác minh mô hình phân quyền RBAC 3 vai trò (`ADMIN`, `LIBRARIAN`, `STUDENT`) kết hợp Policy mức tài nguyên (Resource-level Authorization).
* **Danh sách Test Cases:**
  1. `admin_has_setting_manage`: Admin có đầy đủ quyền cấu hình hệ thống (`setting.manage`).
  2. `admin_has_user_manage`: Admin có quyền quản lý người dùng (`user.manage`).
  3. `admin_has_role_ADMIN`: Nhận diện chính xác role code `ADMIN`.
  4. `librarian_has_loan_confirm`: Thủ thư có quyền xác nhận mượn/trả sách (`loan.confirm`).
  5. `librarian_denied_setting_manage`: Thủ thư KHÔNG được phép truy cập cấu hình hệ thống (nguyên tắc *Least Privilege*).
  6. `librarian_denied_user_manage`: Thủ thư KHÔNG được phép sửa quyền người dùng khác.
  7. `student_has_room_book`: Sinh viên có quyền đặt chỗ phòng đọc (`room.book`).
  8. `student_denied_book_create`: Sinh viên KHÔNG được phép tạo hoặc chỉnh sửa đầu sách (`book.create`).
  9. `student_denied_audit_view`: Sinh viên KHÔNG được phép đọc nhật ký hệ thống (`audit.view`).
  10. `deny_unknown_permission`: Từ chối tuyệt đối mọi mã quyền không tồn tại (*Deny by Default*).
  11. `deny_invalid_user_id`: Người dùng không tồn tại hoặc ID âm/0 tự động bị từ chối.
  12. `student_can_cancel_own_booking`: Sinh viên được phép hủy đặt chỗ của chính mình khi chưa check-in.
  13. `student_cannot_cancel_other_booking`: Sinh viên KHÔNG được hủy đặt chỗ của sinh viên khác (Chống lỗ hổng IDOR).
  14. `librarian_can_cancel_any_booking`: Thủ thư có thẩm quyền điều phối và hủy đặt chỗ của bất kỳ sinh viên nào.
  15. `cannot_cancel_already_checkedin`: Hệ thống chặn việc hủy lượt đặt chỗ đối với ghế đã check-in nhận phòng.

---

#### Suite 2: `tests/Unit/BookServiceTest.php` (16 Test Cases)
* **Đối tượng kiểm thử:** [`App\Services\BookService`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Services/BookService.php), [`App\Validators\BookValidator`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Validators/BookValidator.php), [`App\Repositories\BookRepository`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Repositories/BookRepository.php).
* **Mục tiêu:** Kiểm thử toàn diện vòng đời nghiệp vụ sách (CRUD), chuẩn hóa dữ liệu đầu vào, phân trang, tìm kiếm và trigger CSDL.
* **Danh sách Test Cases:**
  1. `validator_rejects_empty_title`: Bắt lỗi khi tiêu đề sách để trống.
  2. `validator_rejects_empty_author`: Bắt lỗi khi tên tác giả để trống.
  3. `validator_rejects_invalid_year`: Bắt lỗi năm xuất bản âm hoặc lớn hơn năm hiện tại + 1.
  4. `validator_accepts_valid_payload`: Chấp thuận dữ liệu nhập sách hợp lệ.
  5. `pagination_has_expected_keys`: Cấu trúc mảng phân trang chứa đủ `items`, `total`, `page`, `perPage`, `totalPages`.
  6. `database_contains_imported_books`: Xác nhận CSDL chứa đủ trên 1.200 đầu sách thực tế (Hiện tại: 1.236 cuốn).
  7. `page1_items_count_correct`: Trang 1 trả về đúng số lượng cấu hình `perPage`.
  8. `prefix_filter_works_properly`: Lọc chuyên mục `KV-ANT` trả về đúng 145 cuốn sách thuộc nhóm này.
  9. `keyword_search_returns_results`: Tìm kiếm từ khóa 'Nobel' trả về đúng kết quả liên quan.
  10. `get_book_detail_success`: Truy vấn đầu sách `KV-ANT-0001` kèm danh sách cuốn sách vật lý (`cuon_sach`).
  11. `create_book_success`: Tạo mới một đầu sách thử nghiệm thành công kèm transaction an toàn.
  12. `trigger_auto_set_so_luong`: Kiểm chứng Trigger CSDL tự động tăng `so_luong` và sinh bản sách vật lý theo chỉ số `.1, .2, .3`.
  13. `update_book_success`: Cập nhật thông tin và in bổ sung bản sách mới.
  14. `additional_copy_reflected`: Số lượng cuốn sách vật lý tăng tương ứng trong CSDL.
  15. `delete_book_success`: Xóa đầu sách thử nghiệm an toàn kèm dọn dẹp liên kết tác giả.
  16. `book_no_longer_exists`: Xác minh đầu sách thử nghiệm không còn tồn tại trong CSDL.

---

#### Suite 3: `tests/Unit/GoogleAuthServiceTest.php` (18 Test Cases)
* **Đối tượng kiểm thử:** [`App\Services\GoogleAuthService`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Services/GoogleAuthService.php)
* **Mục tiêu:** Kiểm thử luồng xác thực Google OAuth 2.0 Single Sign-On, tạo URL đăng nhập, quản lý CSRF Token và chính sách Whitelist domain học thuật.
* **Danh sách Test Cases:**
  1. `isConfigured` trả về `false` khi thiếu Client ID hoặc Client Secret.
  2. `isConfigured` trả về `true` khi đã điền đầy đủ cấu hình Google OAuth.
  3. URL xác thực trỏ chuẩn xác đến `https://accounts.google.com/o/oauth2/v2/auth`.
  4. URL xác thực bắt buộc chứa tham số `prompt=select_account` để luôn hiển thị màn hình chọn tài khoản.
  5. URL xác thực chứa tham số `state` (CSRF protection) được lưu trong Session.
  6. URL xác thực mang đúng `client_id` cấu hình.
  7. URL xác thực yêu cầu đủ 3 scopes: `openid email profile`.
  8. Chấp thuận domain sinh viên UIT: `@gm.uit.edu.vn`.
  9. Chấp thuận domain sinh viên Nhân văn: `@sv.ussh.edu.vn`.
  10. Kiểm tra domain không phân biệt hoa thường (`@GM.UIT.EDU.VN`).
  11. Từ chối các domain lạ ngoài danh mục trường đại học.
  12. Từ chối tài khoản cá nhân `@gmail.com` đóng vai trò sinh viên.
  13. Nhận diện chính xác email Admin được cấu hình trong hệ thống.
  14. Nhận diện email Admin không phân biệt hoa thường.
  15. Từ chối email không được cấp quyền Admin trong biến môi trường.
  16. Hàm `isAllowed` chấp thuận email sinh viên hợp lệ.
  17. Hàm `isAllowed` chấp thuận email quản trị viên hợp lệ.
  18. Hàm `isAllowed` từ chối các email ngoài whitelist.

---

### 2.2. Nhóm Kiểm thử Tích hợp & Trực tiếp (Integration & E2E)

#### Suite 4: `tests/Integration/ViewsDbIntegrationTest.php` (13 Test Cases)
* **Đối tượng kiểm thử:** 13 tệp Giao diện người dùng (Views) trong thư mục [`resources/views/`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/).
* **Mục tiêu:** Xác minh 100% Mock Data đã bị gỡ bỏ; tất cả các trang đều truy vấn dữ liệu sống từ CSDL MySQL và render giao diện chuẩn nhận diện thương hiệu FoL.
* **Danh sách 13 Views kiểm thử:**
  1. `view_books_renders_real_db`: Trang Tra cứu tài liệu ([/books](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/books/index.php)).
  2. `view_book_detail_renders_real_db`: Trang Chi tiết tài liệu ([/book-detail](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/book-detail/index.php)).
  3. `view_profile_renders_real_db`: Trang Hồ sơ cá nhân & Sách mượn ([/profile](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/profile/index.php)).
  4. `view_librarian_counter_renders_real_db`: Quầy thủ thư Mượn/Trả sách ([/librarian/counter](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/librarian-counter/index.php)).
  5. `view_librarian_rooms_renders_real_db`: Quản lý đặt chỗ phòng đọc ([/librarian/rooms](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/librarian-rooms/index.php)).
  6. `view_librarian_reports_renders_real_db`: Báo cáo thống kê hoạt động ([/librarian/reports](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/librarian-reports/index.php)).
  7. `view_reading_room_renders_real_db`: Trang Đặt chỗ phòng đọc sinh viên ([/reading-room](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/reading-room/index.php)).
  8. `view_admin_users_renders_real_db`: Trang Quản trị người dùng ([/admin/users](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/admin-users/index.php)).
  9. `view_admin_audit_renders_real_db`: Trang Nhật ký hệ thống ([/admin/audit](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/admin-audit/index.php)).
  10. `view_about_renders_successfully`: Trang Giới thiệu Thư viện FoL ([/about](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/about/index.php)).
  11. `view_rules_renders_successfully`: Trang Nội quy Thư viện ([/rules](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/rules/index.php)).
  12. `view_feedback_renders_successfully`: Trang Góp ý & Đề xuất sách mới ([/feedback](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/feedback/index.php)).
  13. `view_contact_renders_successfully`: Trang Liên hệ Thư viện ([/contact](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/contact/index.php)).

---

#### Suite 5: `tests/Integration/BookManagementRegressionTest.php` (7 Test Cases)
* **Đối tượng kiểm thử:** Chức năng quản lý danh mục sách của Thủ thư ([/librarian/books](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/resources/views/librarian-books/index.php)).
* **Mục tiêu:** Kiểm thử hồi quy các thành phần giao diện, phân trang và tương tác Modal/API.
* **Danh sách Test Cases:**
  1. `view_renders_successfully`: Render thành công bảng quản lý sách.
  2. `view_contains_real_book_codes`: Dữ liệu bảng chứa mã sách CSDL thật (`KV-ANT-...`).
  3. `view_contains_pagination`: Hiển thị thanh phân trang chuẩn.
  4. `view_contains_filter_chips`: Chứa các chip lọc nhanh chuyên mục (`ANT`, `ESS`, `POE`, `REC`).
  5. `view_contains_modals`: Có đủ Modal Thêm sách, Sửa sách và Modal Xem danh sách bản sách vật lý.
  6. `api_json_search_returns_valid_data`: API tìm kiếm AJAX trả về cấu trúc JSON hợp lệ.
  7. `book_detail_renders_real_book`: Kiểm tra trang chi tiết hiển thị trùng khớp tiêu đề sách từ CSDL.

---

#### Suite 6: `tests/Integration/AuthenticationAuthorizationRegressionTest.php` (16 Test Cases)
* **Đối tượng kiểm thử:** Toàn bộ hệ thống Session, Cookie, [`App\Core\Session`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Core/Session.php), [`App\Middleware\AuthenticationMiddleware`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Middleware/AuthenticationMiddleware.php), [`App\Middleware\AuthorizationMiddleware`](file:///e:/Repo%20Project/IS207_HTTP_Project/project-fol-library/app/Middleware/AuthorizationMiddleware.php).
* **Mục tiêu:** Phòng chống 100% các lỗi bảo mật phiên đăng nhập, rò rỉ quyền hạn và leo thang đặc quyền.
* **Danh sách 16 Kịch bản Hồi quy chuyên sâu:**
  * **AUTH-01:** Khách chưa đăng nhập bị chặn trước khi truy cập các route quản trị hoặc cá nhân.
  * **AUTH-02:** Admin đăng nhập được cấp quyền vào trang quản trị (`setting.manage`).
  * **AUTH-03:** Admin có đặc quyền kiểm thử vai trò Sinh viên mà phiên đăng nhập Quản trị vẫn an toàn.
  * **AUTH-04:** Sinh viên đăng nhập hợp lệ được phép truy cập trang cá nhân `/profile`.
  * **AUTH-05:** Sinh viên bị chặn khi truy cập trang Quản trị, Session sinh viên được bảo toàn nguyên vẹn.
  * **AUTH-06:** Admin chuyển đổi giữa các vai trò kiểm thử và quay về trang chủ vẫn giữ nguyên phiên đăng nhập Quản trị.
  * **AUTH-07:** Sinh viên sau khi bị từ chối vào trang quản trị quay về trang chủ vẫn giữ nguyên phiên Sinh viên.
  * **AUTH-08:** Truy cập các route công khai (`/`, `/books`, `/reading-room`, `/contact`) không làm mất phiên hoặc lặp chuyển hướng.
  * **AUTH-09:** Người dùng gõ trực tiếp URL quản trị/thủ thư vào thanh địa chỉ đều bị Backend chặn tại Authorization check.
  * **AUTH-10:** Chống giả mạo quyền: Tham số client gửi (`role`, `id_vai_tro`, `switch_role`) trong GET/POST bị Backend vô hiệu hóa hoàn toàn.
  * **AUTH-11:** Yêu cầu AJAX/API khi chưa xác thực được nhận diện đúng định dạng và trả về mã lỗi HTTP 401 Unauthorized dạng JSON.
  * **AUTH-12:** Tài khoản bị đình chỉ hoặc vô hiệu hóa trong CSDL bị thu hồi phiên đăng nhập ngay lập tức.
  * **AUTH-13:** Thao tác đăng xuất chủ động xóa sạch dữ liệu phiên và cookie; các truy cập kế tiếp bị chặn hoàn toàn.
  * **AUTH-14:** Phiên làm việc quá hạn (Session Timeout) được phát hiện chính xác và tự động thu hồi phiên an toàn.
  * **AUTH-15:** Chống IDOR: Người dùng cố tình đổi ID tài nguyên sang bản ghi của người khác bị Policy từ chối tuyệt đối.
  * **AUTH-16:** Thao tác thay đổi dữ liệu trái quyền (POST/DELETE) bị backend từ chối, bảo vệ toàn vẹn CSDL.

---

#### Suite 7: `tests/Integration/HttpEndToEndTest.php` (17 Test Cases)
* **Đối tượng kiểm thử:** Máy chủ HTTP thực tế đang chạy tại `http://localhost:8000`.
* **Mục tiêu:** Kiểm thử trải nghiệm người dùng thực tế qua giao thức HTTP (Status Code, Cookie Jar, Redirect Header, DOM Response).
* **Danh sách 17 Kịch bản E2E:**
  * **E2E-01:** Khách truy cập `/profile` nhận phản hồi `HTTP 302 Redirect` chuyển hướng đến `/login`.
  * **E2E-02:** Đăng nhập Admin thành công nhận `HTTP 302` chuyển hướng đến `/admin/settings`.
  * **E2E-03:** Admin truy cập `/admin/settings` nhận `HTTP 200 OK` (hiển thị trang cấu hình).
  * **E2E-04:** Admin truy cập `/profile` nhận `HTTP 200 OK` (Admin đảm nhận được role Sinh viên để kiểm thử).
  * **E2E-05:** Admin quay về trang chủ `/` nhận `HTTP 200 OK` và VẪN GIỮ NGUYÊN PHIÊN ĐĂNG NHẬP (Hiện menu Cán bộ, Quản trị & Đăng xuất).
  * **E2E-06:** Admin truy cập `/login` tự động được chuyển về `/admin/settings` (Guest Guard bảo vệ).
  * **E2E-07:** Admin đăng xuất nhận `HTTP 302` chuyển về `/login` và cookie phiên bị hủy.
  * **E2E-08:** Đăng nhập Sinh viên thành công chuyển hướng đến `/profile`.
  * **E2E-09:** Sinh viên truy cập `/profile` nhận `HTTP 200 OK` với đầy đủ thông tin độc giả.
  * **E2E-10:** Sinh viên cố truy cập `/admin/settings` bị từ chối với mã lỗi `HTTP 403 Forbidden`.
  * **E2E-11:** Sinh viên quay về trang chủ `/` VẪN GIỮ NGUYÊN PHIÊN SINH VIÊN (Hiện menu Trang cá nhân & Đăng xuất).
  * **E2E-12:** Sinh viên đăng xuất thành công chuyển về `/login`.
  * **E2E-13:** Khách truy cập trang Giới thiệu `/about` nhận `HTTP 200 OK`.
  * **E2E-14:** Khách truy cập trang Nội quy `/rules` nhận `HTTP 200 OK`.
  * **E2E-15:** Khách truy cập trang Góp ý `/feedback` nhận `HTTP 200 OK`.
  * **E2E-16:** Khách truy cập trang Liên hệ `/contact` nhận `HTTP 200 OK`.
  * **E2E-17:** Menu chính điều hướng chuẩn xác với đầy đủ liên kết Dịch vụ, Giới thiệu, Nội quy, Góp ý, Liên hệ.

---

## 3. MA TRẬN TRUY XUẤT NGUỒN GỐC (TRACEABILITY MATRIX)

| Mã yêu cầu | Mô tả nghiệp vụ & Mục tiêu kỹ thuật | Bộ kiểm thử đảm nhiệm | Kết quả |
| :--- | :--- | :--- | :---: |
| **REQ-01** | Quản lý 1.236 đầu sách, tìm kiếm, lọc tiền tố chuyên mục | `BookServiceTest`, `BookManagementRegressionTest` | **PASS** |
| **REQ-02** | Xóa bỏ toàn bộ Mock Data, nạp dữ liệu sống từ MySQL | `ViewsDbIntegrationTest` (13 Views) | **PASS** |
| **REQ-03** | Chuẩn hóa kho sách vật lý, hiển thị trung thực kệ/ô | `BookRepository` / `BookServiceTest` | **PASS** |
| **REQ-04** | Giao diện thẻ sách 3 tầng siêu gọn, căn trái, gạch chân chuẩn | `ViewsDbIntegrationTest`, Browser Screenshot | **PASS** |
| **REQ-05** | Phân quyền 3 vai trò (Admin, Thủ thư, Sinh viên) | `AuthorizationServiceTest` | **PASS** |
| **REQ-06** | Đăng nhập Google SSO (@gm.uit.edu.vn, @sv.ussh.edu.vn) | `GoogleAuthServiceTest` | **PASS** |
| **SEC-01** | Chống leo thang đặc quyền, giả mạo role param | `AuthenticationAuthorizationRegressionTest` | **PASS** |
| **SEC-02** | Chống truy cập trái phép ngang hàng (Chống IDOR) | `AuthorizationServiceTest` (AUTH-15) | **PASS** |
| **SEC-03** | Bảo toàn Session khi chuyển trang, chống vòng lặp redirect | `HttpEndToEndTest` (E2E-05, E2E-11) | **PASS** |
| **SEC-04** | Chặn người dùng chưa đăng nhập, trả mã lỗi HTTP 401/403 | `HttpEndToEndTest` (E2E-01, E2E-10) | **PASS** |

---

## 4. HƯỚNG DẪN THỰC THI TOÀN BỘ BỘ KIỂM THỬ

### 4.1. Chạy tất cả 102 bài kiểm thử bằng 1 lệnh duy nhất
Tại thư mục gốc dự án `project-fol-library`, thực thi lệnh:
```bash
php tests/run_all_tests.php
```
*(Nếu sử dụng XAMPP trên Windows):*
```powershell
& "E:\xampp\php\php.exe" tests/run_all_tests.php
```

### 4.2. Chạy từng bộ kiểm thử độc lập
```powershell
# 1. Kiểm thử phân quyền RBAC
& "E:\xampp\php\php.exe" tests/Unit/AuthorizationServiceTest.php

# 2. Kiểm thử nghiệp vụ Đầu sách
& "E:\xampp\php\php.exe" tests/Unit/BookServiceTest.php

# 3. Kiểm thử Google OAuth
& "E:\xampp\php\php.exe" tests/Unit/GoogleAuthServiceTest.php

# 4. Kiểm thử 13 Giao diện kết nối CSDL
& "E:\xampp\php\php.exe" tests/Integration/ViewsDbIntegrationTest.php

# 5. Kiểm thử Quản lý sách Thủ thư
& "E:\xampp\php\php.exe" tests/Integration/BookManagementRegressionTest.php

# 6. Kiểm thử Hồi quy Phiên đăng nhập & Bảo mật
& "E:\xampp\php\php.exe" tests/Integration/AuthenticationAuthorizationRegressionTest.php

# 7. Kiểm thử Live HTTP E2E (Yêu cầu server đang chạy tại port 8000)
& "E:\xampp\php\php.exe" tests/Integration/HttpEndToEndTest.php
```

---

## 5. BẰNG CHỨNG THỰC THI THỰC TẾ (LATEST EXECUTION LOG)

```text
================================================================================
   HỆ THỐNG THƯ VIỆN KHOA VĂN HỌC (FoL DIGITAL LIBRARY) - MASTER TEST RUNNER    
================================================================================
Thời gian kiểm thử: 2026-10-31 09:53:16
Môi trường PHP:    8.2.12 (WINNT)
Thư mục gốc:       E:\Repo Project\IS207_HTTP_Project\project-fol-library
================================================================================

>>> BẮT ĐẦU NHÓM: Unit Tests
--------------------------------------------------------------------------------
 [PASS] tests/Unit/AuthorizationServiceTest.php (0.134s)
        Mô tả: Phân quyền RBAC & Quyền truy cập (15/15 test cases)
 [PASS] tests/Unit/BookServiceTest.php (0.214s)
        Mô tả: Xử lý logic Nghiệp vụ & Dữ liệu Đầu sách (16/16 test cases)
 [PASS] tests/Unit/GoogleAuthServiceTest.php (0.153s)
        Mô tả: Xác thực Google OAuth 2.0 & Giới hạn Domain trường (18/18 test cases)

>>> BẮT ĐẦU NHÓM: Integration Tests
--------------------------------------------------------------------------------
 [PASS] tests/Integration/ViewsDbIntegrationTest.php (0.197s)
        Mô tả: Tích hợp 13 Giao diện Views kết nối CSDL thực (13/13 test cases)
 [PASS] tests/Integration/BookManagementRegressionTest.php (0.168s)
        Mô tả: Hồi quy Quản lý sách & Phân trang thủ thư (7/7 test cases)
 [PASS] tests/Integration/AuthenticationAuthorizationRegressionTest.php (0.167s)
        Mô tả: Hồi quy Toàn diện Phiên đăng nhập & Bảo mật RBAC (16/16 test cases)
 [PASS] tests/Integration/HttpEndToEndTest.php (4.051s)
        Mô tả: Kiểm thử Live HTTP End-to-End trên Server (17/17 test cases)

================================================================================
                            BẢNG TỔNG KẾT KIỂM THỬ                              
================================================================================
Bộ kiểm thử                                                      | Kết quả | Thời gian
--------------------------------------------------------------------------------
tests/Unit/AuthorizationServiceTest.php                          | PASS    | 0.134 s
tests/Unit/BookServiceTest.php                                   | PASS    | 0.214 s
tests/Unit/GoogleAuthServiceTest.php                             | PASS    | 0.153 s
tests/Integration/ViewsDbIntegrationTest.php                     | PASS    | 0.197 s
tests/Integration/BookManagementRegressionTest.php               | PASS    | 0.168 s
tests/Integration/AuthenticationAuthorizationRegressionTest.php | PASS    | 0.167 s
tests/Integration/HttpEndToEndTest.php                           | PASS    | 4.051 s
--------------------------------------------------------------------------------
Tổng số Test Suites: 7 | Đạt: 7 | Thất bại: 0
Tỷ lệ thành công:    100%
Tổng số Test Cases:  102 / 102 test cases PASS 100%
================================================================================
```
