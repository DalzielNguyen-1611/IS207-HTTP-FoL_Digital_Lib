-- =============================================================================
-- Migration: Bổ sung chỉ mục hiệu năng và ràng buộc chống Race Condition / Overbooking
-- Main Project: Hệ Thống Thư Viện Số Khoa Văn Học (IS207.R11)
-- =============================================================================

-- 1. Dọn dẹp các bản ghi trùng lặp dữ liệu mẫu cũ nếu có trước khi áp dụng Unique Constraint
DELETE d1 FROM `dat_cho` d1
INNER JOIN `dat_cho` d2 
WHERE d1.`id_dat_cho` > d2.`id_dat_cho` 
  AND d1.`id_nguoi_dung` = d2.`id_nguoi_dung` 
  AND d1.`id_ca_doc` = d2.`id_ca_doc` 
  AND d1.`ngay_dat` = d2.`ngay_dat` 
  AND d1.`trang_thai` IN ('DaDat', 'DaNhanCho')
  AND d2.`trang_thai` IN ('DaDat', 'DaNhanCho');

-- 2. Khóa chống Overbooking ghế (Một chỗ ngồi chỉ có thể có tối đa 1 lượt đặt hoạt động trong 1 ca đọc của 1 ngày)
SET @dbname = DATABASE();
SET @tablename = "dat_cho";

-- Thêm cột sinh active_seat_booking nếu chưa tồn tại
SET @columnname = "active_seat_booking";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE `dat_cho` ADD COLUMN `active_seat_booking` VARCHAR(100) GENERATED ALWAYS AS (IF(`trang_thai` IN ('DaDat', 'DaNhanCho'), CONCAT(`id_cho_ngoi`, '_', `id_ca_doc`, '_', `ngay_dat`), NULL)) STORED;"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Thêm UNIQUE KEY uq_active_seat_booking nếu chưa tồn tại
SET @indexname = "uq_active_seat_booking";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `dat_cho` ADD UNIQUE KEY `uq_active_seat_booking` (`active_seat_booking`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Khóa chống trùng lặp ca đọc của sinh viên (Một sinh viên chỉ có tối đa 1 lượt đặt hoạt động trong 1 ca đọc của 1 ngày)
SET @columnname = "active_user_shift";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE `dat_cho` ADD COLUMN `active_user_shift` VARCHAR(100) GENERATED ALWAYS AS (IF(`trang_thai` IN ('DaDat', 'DaNhanCho'), CONCAT(`id_nguoi_dung`, '_', `id_ca_doc`, '_', `ngay_dat`), NULL)) STORED;"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @indexname = "uq_active_user_shift";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `dat_cho` ADD UNIQUE KEY `uq_active_user_shift` (`active_user_shift`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Bổ sung Composite Index cho bảng cuốn sách (tối ưu đếm số lượng khả dụng & truy vấn nhanh)
SET @tablename = "cuon_sach";
SET @indexname = "idx_cuon_sach_dau_trang_thai";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `cuon_sach` ADD INDEX `idx_cuon_sach_dau_trang_thai` (`id_dau_sach`, `trang_thai`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Bổ sung Composite Index cho chi tiết mượn (truy vấn hạn trả và kiểm tra sách đang mượn)
SET @tablename = "chi_tiet_muon";
SET @indexname = "idx_ctm_cuon_trang_thai";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `chi_tiet_muon` ADD INDEX `idx_ctm_cuon_trang_thai` (`id_cuon_sach`, `trang_thai`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @indexname = "idx_ctm_trang_thai_han";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `chi_tiet_muon` ADD INDEX `idx_ctm_trang_thai_han` (`trang_thai`, `han_tra`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Bổ sung Index sắp xếp thời gian cho Nhật ký kiểm toán
SET @tablename = "nhat_ky_hoat_dong";
SET @indexname = "idx_audit_time";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND INDEX_NAME = @indexname) > 0,
  "SELECT 1",
  "ALTER TABLE `nhat_ky_hoat_dong` ADD INDEX `idx_audit_time` (`thoi_gian`);"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
