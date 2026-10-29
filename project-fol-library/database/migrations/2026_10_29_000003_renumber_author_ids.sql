-- Migration: Renumber Author IDs sequentially from 1 to 970
-- Date: 2026-10-29
-- Database: fol_library_db
-- Description:
--   Đánh số lại liên tục toàn bộ ID trong bảng `tac_gia` từ 1 đến 970 (không bị khuyết số).
--   Đồng thời cập nhật đồng bộ toàn bộ trường `id_tac_gia` trong bảng trung gian `tac_gia_sach`.
--   Đặt lại AUTO_INCREMENT cho bảng `tac_gia` là 971.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tạo bảng ánh xạ tạm old_id -> new_id
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

-- 2. Cập nhật bảng liên kết tac_gia_sach
UPDATE tac_gia_sach tgs
JOIN _author_map m ON tgs.id_tac_gia = m.old_id
SET tgs.id_tac_gia = m.new_id;

-- 3. Cập nhật bảng tac_gia tránh xung đột unique
UPDATE tac_gia tg
JOIN _author_map m ON tg.id = m.old_id
SET tg.id = -m.new_id;

UPDATE tac_gia
SET id = -id
WHERE id < 0;

-- 4. Đặt lại AUTO_INCREMENT
ALTER TABLE tac_gia AUTO_INCREMENT = 971;

DROP TEMPORARY TABLE IF EXISTS _author_map;

SET FOREIGN_KEY_CHECKS = 1;
