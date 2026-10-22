-- =============================================================================
-- FoL Digital Library — Database Schema Definition
-- Main Project: Hệ Thống Thư Viện Số Khoa Văn Học (IS207.R11)
-- Hệ quản trị CSDL: MySQL 8.0+ / MariaDB 10.4+
-- Bảng mã: utf8mb4 / Collation: utf8mb4_unicode_ci
-- Được chuyển đổi từ thiết kế DBML
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. BẢNG VAI TRÒ VÀ QUYỀN HẠN (RBAC)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `vai_tro` (
    `id_vai_tro` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_vai_tro` VARCHAR(50) NOT NULL UNIQUE,
    `ten_vai_tro` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Admin, CanBo, SinhVien',
    `mo_ta` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'CURRENT_TIMESTAMP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quyen` (
    `id_quyen` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_quyen` VARCHAR(100) NOT NULL UNIQUE,
    `ten_quyen` VARCHAR(150) NOT NULL,
    `mo_ta` VARCHAR(255) NULL,
    `created_at` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vai_tro_quyen` (
    `id_vai_tro` INT NOT NULL,
    `id_quyen` INT NOT NULL,
    PRIMARY KEY (`id_vai_tro`, `id_quyen`),
    CONSTRAINT `fk_54` FOREIGN KEY (`id_vai_tro`) REFERENCES `vai_tro` (`id_vai_tro`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_53` FOREIGN KEY (`id_quyen`) REFERENCES `quyen` (`id_quyen`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. TÀI KHOẢN, HỒ SƠ NGƯỜI DÙNG & LỚP / NGÀNH
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tai_khoan` (
    `id_tai_khoan` INT AUTO_INCREMENT PRIMARY KEY,
    `id_vai_tro` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `google_sub` VARCHAR(255) NULL UNIQUE COMMENT 'Dinh danh Google neu dang nhap Google',
    `mat_khau` VARCHAR(255) NULL COMMENT 'Password hash; NULL neu chi dung Google',
    `trang_thai` ENUM('HoatDong', 'Khoa') NOT NULL DEFAULT 'HoatDong',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_1` FOREIGN KEY (`id_vai_tro`) REFERENCES `vai_tro` (`id_vai_tro`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `nganh` (
    `id_nganh` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_nganh` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `id_lop` (
    `id_lop` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_lop` VARCHAR(50) NOT NULL,
    `id_nganh` INT NOT NULL,
    CONSTRAINT `fk_42` FOREIGN KEY (`id_nganh`) REFERENCES `nganh` (`id_nganh`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ho_so_nguoi_dung` (
    `id_ho_so` INT AUTO_INCREMENT PRIMARY KEY,
    `id_tai_khoan` INT NOT NULL UNIQUE,
    `mssv` VARCHAR(255) NOT NULL UNIQUE,
    `ho_ten` VARCHAR(100) NOT NULL,
    `id_lop` INT NOT NULL,
    `gioi_tinh` VARCHAR(255) NOT NULL,
    `ngay_sinh` DATE NOT NULL,
    `so_dien_thoai` VARCHAR(20) NOT NULL,
    `anh_dai_dien` VARCHAR(255) NULL COMMENT 'mặc định sẽ là 1 ảnh nào đấy',
    CONSTRAINT `fk_52` FOREIGN KEY (`id_tai_khoan`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_44` FOREIGN KEY (`id_lop`) REFERENCES `id_lop` (`id_lop`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. CƠ SỞ VẬT CHẤT: PHÒNG, KỆ SÁCH, Ô SÁCH, CHỖ NGỒI, CA ĐỌC
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `phong` (
    `id_phong` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_phong` VARCHAR(20) NOT NULL UNIQUE,
    `ten_phong` VARCHAR(50) NOT NULL,
    `mo_ta` VARCHAR(255) NULL,
    `trang_thai` VARCHAR(50) NOT NULL COMMENT 'Đang hoạt động, tạm đóng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ke_sach` (
    `id_ke_sach` INT AUTO_INCREMENT PRIMARY KEY,
    `id_phong` INT NOT NULL,
    `ma_ke` VARCHAR(30) NOT NULL UNIQUE,
    `ten_ke` VARCHAR(100) NULL,
    `mo_ta` VARCHAR(255) NULL,
    CONSTRAINT `fk_35` FOREIGN KEY (`id_phong`) REFERENCES `phong` (`id_phong`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `o_sach` (
    `id_o_sach` INT AUTO_INCREMENT PRIMARY KEY,
    `id_ke_sach` INT NULL,
    `ma_o` VARCHAR(255) NOT NULL,
    `suc_chua` INT NULL,
    `mo_ta` VARCHAR(255) NULL,
    CONSTRAINT `fk_34` FOREIGN KEY (`id_ke_sach`) REFERENCES `ke_sach` (`id_ke_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ca_doc` (
    `id_ca_doc` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_ca` VARCHAR(50) NOT NULL UNIQUE,
    `gio_bat_dau` TIME NOT NULL,
    `gio_ket_thuc` TIME NOT NULL,
    `trang_thai` VARCHAR(20) NOT NULL DEFAULT 'HoatDong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cho_ngoi` (
    `id_cho_ngoi` INT AUTO_INCREMENT PRIMARY KEY,
    `id_phong` INT NOT NULL,
    `ma_cho_ngoi` VARCHAR(20) NOT NULL UNIQUE,
    `mo_ta` VARCHAR(255) NULL,
    `trang_thai` VARCHAR(20) NOT NULL DEFAULT 'HoatDong' COMMENT 'HoatDong, BaoTri, NgungSuDung',
    CONSTRAINT `fk_38` FOREIGN KEY (`id_phong`) REFERENCES `phong` (`id_phong`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. ĐẶT CHỖ PHÒNG ĐỌC
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `dat_cho` (
    `id_dat_cho` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_dat_cho` VARCHAR(50) NOT NULL UNIQUE,
    `id_cho_ngoi` INT NOT NULL,
    `id_ca_doc` INT NOT NULL,
    `id_nguoi_dung` INT NOT NULL,
    `ngay_dat` DATE NOT NULL,
    `thoi_gian_tao` DATETIME NOT NULL,
    `thoi_gian_xac_nhan` DATETIME NULL,
    `thoi_gian_checkin` DATETIME NULL,
    `thoi_gian_huy` DATETIME NULL,
    `trang_thai` VARCHAR(30) NOT NULL DEFAULT 'DaDat' COMMENT 'DaDat, DaNhanCho, Huy, QuaHanKhongDen',
    `ghi_chu` VARCHAR(500) NULL,
    CONSTRAINT `fk_19` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_20` FOREIGN KEY (`id_cho_ngoi`) REFERENCES `cho_ngoi` (`id_cho_ngoi`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_21` FOREIGN KEY (`id_ca_doc`) REFERENCES `ca_doc` (`id_ca_doc`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. QUY TẮC ĐÌNH CHỈ, VI PHẠM & LỊCH SỬ ĐÌNH CHỈ
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `quy_tac_dinh_chi` (
    `id_quy_tac` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_quy_tac` VARCHAR(255) NOT NULL,
    `kieu_dieu_kien` VARCHAR(50) NOT NULL COMMENT 'Theo số lần, theo mức độ',
    `nguong_so_lan` INT NULL,
    `thoi_han_dinh_chi` INT NOT NULL,
    `trang_thai` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `loai_vi_pham` (
    `id_loai_vi_pham` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_loai` VARCHAR(30) NOT NULL UNIQUE,
    `ten_loai` VARCHAR(100) NOT NULL,
    `mo_ta` VARCHAR(255) NULL,
    `muc_do` VARCHAR(50) NOT NULL COMMENT 'Nhẹ, trung bình, nặng',
    `trang_thai` VARCHAR(50) NULL COMMENT 'Đang áp dụng, ngừng áp dụng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vi_pham_phong_doc` (
    `id_vi_pham` INT AUTO_INCREMENT PRIMARY KEY,
    `id_dat_cho` INT NULL,
    `id_tai_khoan` INT NOT NULL COMMENT 'Đây là tài khoản của người vi phạm',
    `id_can_bo` INT NULL,
    `id_loai_vi_pham` INT NOT NULL,
    `ngay_vi_pham` DATETIME NOT NULL,
    `ly_do` VARCHAR(50) NOT NULL COMMENT 'HuySatGio, KhongDen, DangKyHo, Khac',
    `mo_ta` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL,
    `trang_thai` VARCHAR(255) NULL COMMENT 'Đã xác nhận, đã hủy',
    CONSTRAINT `fk_45` FOREIGN KEY (`id_loai_vi_pham`) REFERENCES `loai_vi_pham` (`id_loai_vi_pham`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_46` FOREIGN KEY (`id_tai_khoan`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_47` FOREIGN KEY (`id_can_bo`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_50` FOREIGN KEY (`id_dat_cho`) REFERENCES `dat_cho` (`id_dat_cho`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lich_su_dinh_chi` (
    `id_dinh_chi` INT AUTO_INCREMENT PRIMARY KEY,
    `id_tai_khoan` INT NOT NULL,
    `id_can_bo` INT NULL,
    `id_quy_tac` INT NULL,
    `ngay_bat_dau` DATETIME NOT NULL,
    `ngay_ket_thuc` DATETIME NOT NULL,
    `ly_do` VARCHAR(500) NOT NULL,
    `trang_thai` VARCHAR(20) NOT NULL DEFAULT 'HieuLuc' COMMENT 'HieuLuc, HetHan, Huy',
    `ngay_tao` DATETIME NOT NULL,
    `loai_dinh_chi` VARCHAR(50) NOT NULL COMMENT 'Tự động, thủ công',
    CONSTRAINT `fk_48` FOREIGN KEY (`id_tai_khoan`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_49` FOREIGN KEY (`id_can_bo`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_51` FOREIGN KEY (`id_quy_tac`) REFERENCES `quy_tac_dinh_chi` (`id_quy_tac`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. TÀI NGUYÊN SÁCH: NHÀ XUẤT BẢN, TÁC GIẢ, THỂ LOẠI, ĐẦU SÁCH & CUỐN SÁCH
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `nha_xuat_ban` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_nha_xuat_ban` VARCHAR(200) NOT NULL,
    `dia_chi` VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tac_gia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_tac_gia` VARCHAR(200) NOT NULL,
    `tieu_su` TEXT NULL,
    `ngay_sinh` DATE NULL,
    `ngay_mat` DATE NULL,
    `quoc_tich` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `the_loai` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_the_loai` VARCHAR(150) NOT NULL,
    `loai` ENUM('TheLoai', 'ChuyenNganh') NOT NULL,
    `mo_ta` VARCHAR(500) NULL,
    UNIQUE KEY `idx_2` (`ten_the_loai`, `loai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dau_sach` (
    `id_dau_sach` INT AUTO_INCREMENT PRIMARY KEY,
    `tieu_de` VARCHAR(300) NOT NULL,
    `isbn` VARCHAR(20) NULL UNIQUE,
    `mo_ta` TEXT NULL,
    `nam_xuat_ban` INT NULL,
    `lan_xuat_ban` INT NULL,
    `ngon_ngu` VARCHAR(50) NULL,
    `id_nha_xuat_ban` INT NULL,
    `ngay_tao` DATETIME NULL,
    `ngay_cap_nhat` DATETIME NULL,
    `so_luong` INT NULL,
    CONSTRAINT `fk_2` FOREIGN KEY (`id_nha_xuat_ban`) REFERENCES `nha_xuat_ban` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tac_gia_sach` (
    `id_dau_sach` INT NOT NULL,
    `id_tac_gia` INT NOT NULL,
    PRIMARY KEY (`id_dau_sach`, `id_tac_gia`),
    CONSTRAINT `fk_3` FOREIGN KEY (`id_dau_sach`) REFERENCES `dau_sach` (`id_dau_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_4` FOREIGN KEY (`id_tac_gia`) REFERENCES `tac_gia` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dau_sach_the_loai` (
    `id_dau_sach` INT NOT NULL,
    `id_the_loai` INT NOT NULL,
    PRIMARY KEY (`id_dau_sach`, `id_the_loai`),
    CONSTRAINT `fk_5` FOREIGN KEY (`id_dau_sach`) REFERENCES `dau_sach` (`id_dau_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_6` FOREIGN KEY (`id_the_loai`) REFERENCES `the_loai` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cuon_sach` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_vach` VARCHAR(50) NOT NULL UNIQUE,
    `id_dau_sach` INT NOT NULL,
    `id_o_sach` INT NULL,
    `trang_thai` VARCHAR(30) NOT NULL DEFAULT 'SanSang' COMMENT 'SanSang, DangMuon, Mat, Hong, ThanhLy',
    `ngay_nhap_kho` DATE NULL,
    `ghi_chu` VARCHAR(500) NULL,
    CONSTRAINT `fk_7` FOREIGN KEY (`id_dau_sach`) REFERENCES `dau_sach` (`id_dau_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_33` FOREIGN KEY (`id_o_sach`) REFERENCES `o_sach` (`id_o_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. SÁCH YÊU THÍCH VÀ LỊCH SỬ TƯƠNG TÁC
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sach_yeu_thich` (
    `id_nguoi_dung` INT NOT NULL,
    `id_dau_sach` INT NOT NULL,
    `ngay_them` DATETIME NOT NULL,
    PRIMARY KEY (`id_nguoi_dung`, `id_dau_sach`),
    CONSTRAINT `fk_9` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_10` FOREIGN KEY (`id_dau_sach`) REFERENCES `dau_sach` (`id_dau_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lich_su_sach_yeu_thich` (
    `id_lich_su` INT AUTO_INCREMENT PRIMARY KEY,
    `id_nguoi_dung` INT NOT NULL,
    `id_dau_sach` INT NOT NULL,
    `hanh_dong` VARCHAR(255) NOT NULL COMMENT 'Them, BoYeuThich',
    `thoi_diem` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_36` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_37` FOREIGN KEY (`id_dau_sach`) REFERENCES `dau_sach` (`id_dau_sach`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. QUẢN LÝ MƯỢN TRẢ & XỬ LÝ PHẠT
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `phieu_muon` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_phieu` VARCHAR(50) NOT NULL UNIQUE,
    `id_nguoi_dung` INT NOT NULL,
    `id_can_bo` INT NULL COMMENT 'Can bo xu ly phieu',
    `ngay_dang_ky` DATETIME NOT NULL COMMENT 'Ngày sinh viên đăng ký mượn sách trên web',
    `ngay_duyet` DATETIME NULL COMMENT 'Ngày sinh viên đến nhận sách và được xác nhận bởi cán bộ',
    `ghi_chu` VARCHAR(500) NULL,
    `trang_thai` VARCHAR(30) NOT NULL DEFAULT 'ChoDuyet' COMMENT 'ChoDuyet, DaDuyet, TuChoi, HoanTat, DaHuy',
    CONSTRAINT `fk_11` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_12` FOREIGN KEY (`id_can_bo`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chi_tiet_muon` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_phieu_muon` INT NOT NULL,
    `id_cuon_sach` INT NOT NULL,
    `ngay_muon` DATE NULL COMMENT 'Ngay thuc te nhan sach',
    `han_tra` DATE NULL COMMENT 'Han tra sach',
    `ngay_tra_thuc_te` DATE NULL,
    `trang_thai` VARCHAR(30) NOT NULL DEFAULT 'DangMuon' COMMENT 'DangMuon, DaTra, Mat, Hong',
    `ghi_chu` VARCHAR(500) NULL,
    CONSTRAINT `fk_13` FOREIGN KEY (`id_phieu_muon`) REFERENCES `phieu_muon` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_14` FOREIGN KEY (`id_cuon_sach`) REFERENCES `cuon_sach` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `phieu_phat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_phat` VARCHAR(50) NOT NULL UNIQUE,
    `id_chi_tiet_muon` INT NOT NULL,
    `so_tien` DECIMAL(12,2) NOT NULL COMMENT 'So tien phai nop',
    `ly_do` VARCHAR(30) NOT NULL COMMENT 'TreHan, MatSach, HongSach, Khac',
    `mo_ta` VARCHAR(500) NULL,
    `trang_thai` VARCHAR(30) NOT NULL DEFAULT 'ChuaDong' COMMENT 'ChuaDong, DongMotPhan, DaDong, Huy',
    `ngay_tao` DATETIME NOT NULL,
    CONSTRAINT `fk_15` FOREIGN KEY (`id_chi_tiet_muon`) REFERENCES `chi_tiet_muon` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `thanh_toan_phat` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_phieu_phat` INT NOT NULL,
    `so_tien` DECIMAL(12,2) NOT NULL,
    `phuong_thuc` VARCHAR(30) NOT NULL COMMENT 'TienMat, ChuyenKhoan, Khac',
    `ma_giao_dich` VARCHAR(100) NULL,
    `ngay_thanh_toan` DATETIME NOT NULL,
    `ghi_chu` VARCHAR(500) NULL,
    CONSTRAINT `fk_17` FOREIGN KEY (`id_phieu_phat`) REFERENCES `phieu_phat` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. NHẬT KÝ HOẠT ĐỘNG VÀ KIỂM TOÁN HỆ THỐNG (AUDIT LOGS)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `nhat_ky_hoat_dong` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `id_nguoi_dung` INT NULL,
    `hanh_dong` VARCHAR(100) NOT NULL COMMENT 'Tao, Sua, Xoa, Duyet, TuChoi, TraSach, ThuTien...',
    `doi_tuong` VARCHAR(100) NOT NULL COMMENT 'Ten bang hoac loai doi tuong',
    `id_doi_tuong` VARCHAR(100) NULL COMMENT 'ID doi tuong bi tac dong',
    `mo_ta` TEXT NULL,
    `dia_chi_ip` VARCHAR(45) NULL,
    `thoi_gian` DATETIME NOT NULL,
    CONSTRAINT `fk_32` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `tai_khoan` (`id_tai_khoan`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
