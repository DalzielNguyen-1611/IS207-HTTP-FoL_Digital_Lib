-- Tăng tốc kiểm tra sức chứa, đặt trùng ca và tìm ghế trống.
ALTER TABLE `dat_cho`
    ADD INDEX `idx_booking_slot_status` (`ngay_dat`, `id_ca_doc`, `trang_thai`),
    ADD INDEX `idx_booking_user_slot_status` (`id_nguoi_dung`, `ngay_dat`, `id_ca_doc`, `trang_thai`),
    ADD INDEX `idx_booking_seat_slot_status` (`id_cho_ngoi`, `id_ca_doc`, `ngay_dat`, `trang_thai`);
