-- =============================================================================
-- Migration: Bổ sung bảng khoa và liên kết ngành - khoa chuẩn USSH VNU-HANOI
-- Main Project: Hệ Thống Thư Viện Số Khoa Văn Học (IS207.R11)
-- =============================================================================

-- 1. Tạo bảng khoa (Faculty/Institute)
CREATE TABLE IF NOT EXISTS `khoa` (
    `id_khoa` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_khoa` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Thêm cột id_khoa vào bảng nganh nếu chưa tồn tại
SET @dbname = DATABASE();
SET @tablename = "nganh";
SET @columnname = "id_khoa";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `nganh` ADD COLUMN `id_khoa` INT NULL AFTER `ten_nganh`, ADD CONSTRAINT `fk_nganh_khoa` FOREIGN KEY (`id_khoa`) REFERENCES `khoa` (`id_khoa`) ON DELETE SET NULL ON UPDATE CASCADE;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Nạp danh mục 18 Khoa / Viện của Trường Đại học KHXH&NV
INSERT INTO `khoa` (`id_khoa`, `ten_khoa`) VALUES
(1, 'Viện Đào tạo Báo chí và Truyền thông'),
(2, 'Khoa Du lịch học'),
(3, 'Khoa Văn học'),
(4, 'Khoa Lịch sử'),
(5, 'Khoa Xã hội học'),
(6, 'Khoa Triết học'),
(7, 'Khoa Thông tin - Thư viện'),
(8, 'Khoa Lưu trữ học và Quản trị văn phòng'),
(9, 'Khoa Đông phương học'),
(10, 'Khoa Hàn Quốc học'),
(11, 'Khoa Nhật Bản học'),
(12, 'Khoa Khoa học Chính trị'),
(13, 'Khoa Quốc tế học'),
(14, 'Khoa Khoa học Quản lý'),
(15, 'Khoa Ngôn ngữ học'),
(16, 'Khoa Nhân học'),
(17, 'Khoa Tâm lý học'),
(18, 'Khoa Việt Nam học và Tiếng Việt')
ON DUPLICATE KEY UPDATE `ten_khoa` = VALUES(`ten_khoa`);

-- 4. Cập nhật và nạp danh mục Chuyên ngành gắn liền với Khoa tương ứng
INSERT INTO `nganh` (`id_nganh`, `ten_nganh`, `id_khoa`) VALUES
(1, 'Văn học', 3),
(2, 'Ngôn ngữ học', 15),
(3, 'Hán Nôm', 3),
(4, 'Văn hóa học', 4),
(6, 'Báo chí', 1),
(7, 'Quan hệ công chúng', 1),
(8, 'Truyền thông đa phương tiện', 1),
(9, 'Quản trị dịch vụ du lịch và lữ hành', 2),
(10, 'Quản trị khách sạn', 2),
(11, 'Điện ảnh và nghệ thuật đại chúng', 3),
(12, 'Lịch sử', 4),
(13, 'Xã hội học', 5),
(14, 'Công tác xã hội', 5),
(15, 'Triết học', 6),
(16, 'Tôn giáo học', 6),
(17, 'Thông tin - Thư viện', 7),
(18, 'Quản lý thông tin', 7),
(19, 'Lưu trữ học', 8),
(20, 'Quản trị văn phòng', 8),
(21, 'Đông phương học', 9),
(22, 'Đông Nam Á học', 9),
(23, 'Hàn Quốc học', 10),
(24, 'Nhật Bản học', 11),
(25, 'Chính trị học', 12),
(26, 'Quốc tế học', 13),
(27, 'Khoa học quản lý', 14),
(28, 'Nhân học', 16),
(29, 'Tâm lý học', 17),
(30, 'Việt Nam học', 18)
ON DUPLICATE KEY UPDATE `ten_nganh` = VALUES(`ten_nganh`), `id_khoa` = VALUES(`id_khoa`);
