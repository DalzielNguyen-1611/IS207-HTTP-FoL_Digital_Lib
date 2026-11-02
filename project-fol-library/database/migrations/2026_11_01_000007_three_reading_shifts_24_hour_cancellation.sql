-- Align reading-room shifts with the published schedule and retain old shift
-- records as inactive so existing bookings keep their foreign-key references.
UPDATE `ca_doc`
SET
    `ten_ca` = CASE `id_ca_doc`
        WHEN 1 THEN 'Ca sáng'
        WHEN 2 THEN 'Ca chiều'
        WHEN 3 THEN 'Ca tối'
        ELSE `ten_ca`
    END,
    `gio_bat_dau` = CASE `id_ca_doc`
        WHEN 1 THEN '07:30:00'
        WHEN 2 THEN '13:00:00'
        WHEN 3 THEN '17:30:00'
        ELSE `gio_bat_dau`
    END,
    `gio_ket_thuc` = CASE `id_ca_doc`
        WHEN 1 THEN '11:30:00'
        WHEN 2 THEN '17:00:00'
        WHEN 3 THEN '21:00:00'
        ELSE `gio_ket_thuc`
    END
WHERE `id_ca_doc` IN (1, 2, 3);

UPDATE `ca_doc`
SET `trang_thai` = 'NgungHoatDong'
WHERE `id_ca_doc` > 3 AND `trang_thai` = 'HoatDong';
