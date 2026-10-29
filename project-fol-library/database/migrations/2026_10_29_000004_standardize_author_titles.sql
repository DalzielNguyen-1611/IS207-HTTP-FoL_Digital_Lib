-- Migration: Standardize Author Academic Titles According to Official Guidelines
-- Date: 2026-10-29
-- Database: fol_library_db
-- Description:
--   Chuẩn hóa các danh xưng học hàm, học vị theo quy chuẩn:
--   - Giáo sư: GS.
--   - Phó Giáo sư: PGS.
--   - Tiến sĩ: TS.
--   - Thạc sĩ: ThS. (chữ h viết thường)
--   - Viện sĩ: VS.
--   - Gộp bản ghi trùng sau khi chuẩn hóa ('Ts. Nguyễn Phương Liên' -> 'TS. Nguyễn Phương Liên')
--   - Đánh số lại ID liên tục từ 1 đến 969.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Chuẩn hóa danh xưng học hàm, học vị
UPDATE tac_gia SET ten_tac_gia = 'TS. Hoàng Cẩm Giang' WHERE ten_tac_gia = 'Ts. Hoàng Cẩm Giang';
UPDATE tac_gia SET ten_tac_gia = 'ThS. Nguyễn Thị Bích' WHERE ten_tac_gia = 'Ths. Nguyễn Thị Bích';
UPDATE tac_gia SET ten_tac_gia = 'GS. Ngô Đức Thịnh' WHERE ten_tac_gia = 'Gs. Ngô Đức Thịnh';
UPDATE tac_gia SET ten_tac_gia = 'TS. Frank Proschan' WHERE ten_tac_gia = 'Ts. Frank Proschan';
UPDATE tac_gia SET ten_tac_gia = 'GS.VS. Hoàng Trinh' WHERE ten_tac_gia = 'Gs.vs. Hoàng Trinh';
UPDATE tac_gia SET ten_tac_gia = 'TS. Tô Nhật' WHERE ten_tac_gia = 'Tiến Sĩ Tô Nhật';
UPDATE tac_gia SET ten_tac_gia = 'TS. Nguyễn Phương Liên' WHERE ten_tac_gia = 'Ts Nguyễn Phương Liên';
UPDATE tac_gia SET ten_tac_gia = 'PGS. Vương Đình Quyền' WHERE ten_tac_gia = 'Pgs. Vương Đình Quyền';
UPDATE tac_gia SET ten_tac_gia = 'GS. Hà Minh Đức' WHERE ten_tac_gia = 'Gs Hà Minh Đức';
UPDATE tac_gia SET ten_tac_gia = 'GS. Phong Lê' WHERE ten_tac_gia = 'Gs Phong Lê';
UPDATE tac_gia SET ten_tac_gia = 'GS. Nguyễn Đăng Mạnh' WHERE ten_tac_gia = 'Gs Nguyễn Đăng Mạnh';
UPDATE tac_gia SET ten_tac_gia = 'ThS. Tạ Hồng Hạnh' WHERE ten_tac_gia = 'Ths. Tạ Hồng Hạnh';
UPDATE tac_gia SET ten_tac_gia = 'Dr. Mortimer J. Adler' WHERE ten_tac_gia = 'Dr.mortimer J.adler';
UPDATE tac_gia SET ten_tac_gia = 'GS. Nhan Bảo' WHERE ten_tac_gia = 'Gs. Nhan Bảo';
UPDATE tac_gia SET ten_tac_gia = 'TS. Thích Đồng Văn' WHERE ten_tac_gia = 'Ts. Thích Đồng Văn';
UPDATE tac_gia SET ten_tac_gia = 'PGS.TS. Trần Lê Bảo' WHERE ten_tac_gia = 'PGS.TS Trần Lê Bảo';

-- 2. Gộp bản ghi trùng 'Ts. Nguyễn Phương Liên' (ID 566) vào 'TS. Nguyễn Phương Liên' (ID 393)
UPDATE tac_gia_sach SET id_tac_gia = 393 WHERE id_tac_gia = 566;
DELETE FROM tac_gia WHERE id = 566;

-- 3. Đánh số lại ID liên tục từ 1 đến 969
DROP TEMPORARY TABLE IF EXISTS _author_map;
CREATE TEMPORARY TABLE _author_map (
    old_id INT NOT NULL PRIMARY KEY,
    new_id INT NOT NULL,
    UNIQUE KEY (new_id)
) ENGINE=InnoDB;

INSERT INTO _author_map (old_id, new_id)
SELECT id, ROW_NUMBER() OVER (ORDER BY id ASC) as new_id
FROM tac_gia
ORDER BY id ASC;

UPDATE tac_gia_sach tgs
JOIN _author_map m ON tgs.id_tac_gia = m.old_id
SET tgs.id_tac_gia = m.new_id;

UPDATE tac_gia tg
JOIN _author_map m ON tg.id = m.old_id
SET tg.id = -m.new_id;

UPDATE tac_gia
SET id = -id
WHERE id < 0;

ALTER TABLE tac_gia AUTO_INCREMENT = 970;

DROP TEMPORARY TABLE IF EXISTS _author_map;

SET FOREIGN_KEY_CHECKS = 1;
