-- Migration: Deduplicate Authors and Re-link Books to Minimum Author ID
-- Date: 2026-10-29
-- Database: fol_library_db
-- Description:
--   Tìm kiếm các tác giả trùng tên (19 nhóm, 27 bản ghi trùng).
--   Chuyển toàn bộ liên kết sách trong `tac_gia_sach` sang ID tác giả nhỏ nhất của mỗi nhóm (61 liên kết chuyển đổi).
--   Xóa bỏ các bản ghi tác giả trùng có ID lớn hơn (27 bản ghi xóa).

START TRANSACTION;

-- Chuyển liên kết sách cho các ID trùng và xóa bản ghi trùng:
-- 1. 'Trường Đại Học Khoa Học Xã Hội Và Nhân Văn - Đại học Quốc gia Hà Nội' (Keep 1, Delete 5, 242, 368, 369, 401)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 1 WHERE `id_tac_gia` IN (5, 242, 368, 369, 401);
DELETE FROM `tac_gia` WHERE `id` IN (5, 242, 368, 369, 401);

-- 2. 'Nhà Xuất Bản Đại Học Quốc Gia Hà Nội' (Keep 22, Delete 23, 38, 64)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 22 WHERE `id_tac_gia` IN (23, 38, 64);
DELETE FROM `tac_gia` WHERE `id` IN (23, 38, 64);

-- 3. 'Nhà Xuất Bản Văn Hóa - Thông Tin' (Keep 25, Delete 362)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 25 WHERE `id_tac_gia` = 362;
DELETE FROM `tac_gia` WHERE `id` = 362;

-- 4. 'Nhà Xuất Bản Văn Học' (Keep 32, Delete 372)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 32 WHERE `id_tac_gia` = 372;
DELETE FROM `tac_gia` WHERE `id` = 372;

-- 5. 'Nhà Xuất Bản Hội Nhà Văn' (Keep 43, Delete 63)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 43 WHERE `id_tac_gia` = 63;
DELETE FROM `tac_gia` WHERE `id` = 63;

-- 6. 'Phan Cư Đệ' (Keep 57, Delete 173)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 57 WHERE `id_tac_gia` = 173;
DELETE FROM `tac_gia` WHERE `id` = 173;

-- 7. 'Viện Văn Học - Viện Hàn Lâm Khoa Học Xã Hội Việt Nam' (Keep 87, Delete 89)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 87 WHERE `id_tac_gia` = 89;
DELETE FROM `tac_gia` WHERE `id` = 89;

-- 8. 'Viện Văn Học - Viện Khoa Học Xã Hội Việt Nam' (Keep 88, Delete 455, 539)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 88 WHERE `id_tac_gia` IN (455, 539);
DELETE FROM `tac_gia` WHERE `id` IN (455, 539);

-- 9. 'Khoa Văn Học - Trường Đại Học Khoa Học Xã Hội Và Nhân Văn - Đại học Quốc gia Hà Nội' (Keep 124, Delete 403, 563)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 124 WHERE `id_tac_gia` IN (403, 563);
DELETE FROM `tac_gia` WHERE `id` IN (403, 563);

-- 10. 'Nhà Xuất Bản Chính Trị Quốc Gia Sự Thật' (Keep 125, Delete 214)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 125 WHERE `id_tac_gia` = 214;
DELETE FROM `tac_gia` WHERE `id` = 214;

-- 11. 'Nguyện Thiện Giáp' (Keep 132, Delete 484)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 132 WHERE `id_tac_gia` = 484;
DELETE FROM `tac_gia` WHERE `id` = 484;

-- 12. 'Nhà Xuất Bản Thanh Niên' (Keep 174, Delete 551)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 174 WHERE `id_tac_gia` = 551;
DELETE FROM `tac_gia` WHERE `id` = 551;

-- 13. 'Nguyễn Viết Chúc' (Keep 181, Delete 496)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 181 WHERE `id_tac_gia` = 496;
DELETE FROM `tac_gia` WHERE `id` = 496;

-- 14. 'Trần Ngọc Vương' (Keep 327, Delete 443)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 327 WHERE `id_tac_gia` = 443;
DELETE FROM `tac_gia` WHERE `id` = 443;

-- 15. 'Triều Nguyên' (Keep 330, Delete 510)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 330 WHERE `id_tac_gia` = 510;
DELETE FROM `tac_gia` WHERE `id` = 510;

-- 16. 'Nhà Xuất Bản Thế Giới' (Keep 504, Delete 980)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 504 WHERE `id_tac_gia` = 980;
DELETE FROM `tac_gia` WHERE `id` = 980;

-- 17. 'Nguyễn Văn Hòa' (Keep 665, Delete 746)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 665 WHERE `id_tac_gia` = 746;
DELETE FROM `tac_gia` WHERE `id` = 746;

-- 18. 'Nhà Xuất Bản Khoa Học Xã Hội' (Keep 727, Delete 796)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 727 WHERE `id_tac_gia` = 796;
DELETE FROM `tac_gia` WHERE `id` = 796;

-- 19. 'Phạm Cao Củng' (Keep 780, Delete 823)
UPDATE `tac_gia_sach` SET `id_tac_gia` = 780 WHERE `id_tac_gia` = 823;
DELETE FROM `tac_gia` WHERE `id` = 823;

COMMIT;
