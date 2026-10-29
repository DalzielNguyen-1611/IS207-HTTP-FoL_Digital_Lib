-- Migration: Split Composite Authors into Discrete Entities
-- Date: 2026-10-29
-- Database: fol_library_db
-- Description:
--   1. Chuẩn hóa tên đơn vị ID 79 thành 'Hội Đồng Lý Luận - Phê Bình Văn Học Nghệ Thuật Trung Ương' (đơn vị độc lập, không tách).
--   2. Tách 16 bản ghi tác giả bị gộp có chứa dấu phẩy (ID 40, 62, 178, 186, 297, 310, 329, 356, 370, 402, 437, 446, 543, 667, 965, 975) thành từng tác giả riêng biệt.
--   3. Cập nhật lại liên kết trong bảng trung gian `tac_gia_sach` để các đầu sách liên kết trực tiếp với các tác giả độc lập tương ứng.
--   4. Xóa các bản ghi tác giả gộp cũ sau khi đã chuyển toàn bộ liên kết.

START TRANSACTION;

-- 1. Chuẩn hóa ID 79
UPDATE `tac_gia` 
SET `ten_tac_gia` = 'Hội Đồng Lý Luận - Phê Bình Văn Học Nghệ Thuật Trung Ương' 
WHERE `id` = 79;

-- 2. Đảm bảo các tác giả độc lập mới tồn tại (INSERT IGNORE)
INSERT IGNORE INTO `tac_gia` (`ten_tac_gia`) VALUES
('Hội Văn Nghệ Dân Gian Nghệ An'),
('Hội Đồng Gia Tộc Họ Phan'),
('Viện Nghiên Cứu Chủ Nghĩa Mác - Lê Nin Và Tư tưởng Hồ Chí Minh'),
('Đào Tuấn Ảnh'),
('Trần Hồng Vân'),
('Lại Nguyên Ân'),
('Nhà Xuất Bản Sự Thật'),
('Nhà Xuất Bản Pháp Lý'),
('Nguyễn Thị Bình'),
('John A. Matthew'),
('David T. Helbert'),
('Sở Văn Hoá Thông Tin Thể Thao Đồng Nai'),
('Trường Đại Học Bansomejchaopraya'),
('Sở Văn Hóa Thông Tin'),
('Hội Văn Nghệ Dân Gian Tỉnh Nghệ An'),
('GS. Nguyễn Khắc Phi (Chủ Biên)'),
('PGS. Lưu Đức Trung'),
('PGS.TS Trần Lê Bảo'),
('Samuel Kernell'),
('Gary C.jacobson'),
('Nguyễn Từ Chi'),
('Nguyễn Cao Huy'),
('Kevin Bowen'),
('Nguyen Ba Chung'),
('Hà Trúc Dương');

COMMIT;
