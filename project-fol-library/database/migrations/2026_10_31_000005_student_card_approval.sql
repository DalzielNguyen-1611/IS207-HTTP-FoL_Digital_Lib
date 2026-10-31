-- =============================================================================
-- Migration: Bổ sung quy trình Duyệt thẻ Sinh viên & Lưu trữ ảnh thẻ WebP
-- Main Project: Hệ Thống Thư Viện Số Khoa Văn Học (IS207.R11)
-- =============================================================================

-- 1. Mở rộng ENUM trạng thái tài khoản bao gồm 'ChoDuyet' (Chờ duyệt thẻ SV)
ALTER TABLE `tai_khoan`
    MODIFY COLUMN `trang_thai` ENUM('ChoDuyet', 'HoatDong', 'Khoa') NOT NULL DEFAULT 'ChoDuyet';

-- 2. Bổ sung các cột lưu thông tin ảnh thẻ sinh viên và nhật ký xét duyệt vào ho_so_nguoi_dung
ALTER TABLE `ho_so_nguoi_dung`
    ADD COLUMN `anh_the_sv` VARCHAR(255) NULL COMMENT 'Đường dẫn file ảnh thẻ sinh viên đã nén WebP' AFTER `anh_dai_dien`,
    ADD COLUMN `ngay_gui_duyet` DATETIME NULL COMMENT 'Thời điểm sinh viên gửi hồ sơ chờ duyệt' AFTER `anh_the_sv`,
    ADD COLUMN `ghi_chu_duyet` VARCHAR(255) NULL COMMENT 'Lý do từ chối hoặc ghi chú yêu cầu chụp lại thẻ' AFTER `ngay_gui_duyet`;
