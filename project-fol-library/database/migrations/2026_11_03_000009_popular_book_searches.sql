-- Thêm bộ đếm để xếp Sách nổi bật theo lượt tìm kiếm.
ALTER TABLE dau_sach ADD COLUMN luot_tim_kiem INT UNSIGNED NOT NULL DEFAULT 0 AFTER so_luong;
