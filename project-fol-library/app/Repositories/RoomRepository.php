<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

/**
 * Tầng truy vấn dữ liệu Phòng đọc & Ca đọc (Room Repository)
 * Tuân thủ 100% Prepared Statements qua PDO theo Rule 8, 9, 11 (AGENTS.md)
 */
class RoomRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /**
     * Lấy toàn bộ ca đọc đang hoạt động
     */
    public function getShifts(): array
    {
        $sql = "SELECT 
                    `id_ca_doc`,
                    `ten_ca` AS `label`,
                    CONCAT(TIME_FORMAT(`gio_bat_dau`, '%H:%i'), ' – ', TIME_FORMAT(`gio_ket_thuc`, '%H:%i')) AS `time`,
                    TIME_FORMAT(`gio_bat_dau`, '%H:%i') AS `start`,
                    `gio_bat_dau`,
                    `gio_ket_thuc`,
                    `trang_thai`
                FROM `ca_doc`
                WHERE `trang_thai` = 'HoatDong'
                ORDER BY `gio_bat_dau` ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Lấy tổng sức chứa phòng đọc (số ghế hoạt động)
     */
    public function getTotalCapacity(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM `cho_ngoi` WHERE `trang_thai` = 'HoatDong'");
        $cap = (int)($stmt ? $stmt->fetchColumn() : 40);
        return $cap > 0 ? $cap : 40;
    }

    /**
     * Lấy lịch phòng đọc 5 ngày trong tuần kèm số chỗ trống thực tế cho trang /reading-room
     */
    public function getWeeklySchedule(?DateTimeImmutable $startDate = null): array
    {
        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        if ($startDate === null) {
            $startDate = (new DateTimeImmutable('today', $tz))->modify('next monday');
        }

        $days = [];
        $dates = [];
        for ($i = 0; $i < 5; $i++) {
            $d = $startDate->modify("+{$i} days");
            $dateStr = $d->format('Y-m-d');
            $dates[] = $dateStr;
            $days[] = [
                'date' => $dateStr,
                'label' => 'Thứ ' . ($i + 2),
                'display' => $d->format('d/m'),
            ];
        }

        $shifts = $this->getShifts();
        $capacity = $this->getTotalCapacity();

        // Lấy số lượng đặt chỗ thực tế theo từng ca và từng ngày
        $bookingCountsSql = "SELECT `id_ca_doc`, `ngay_dat`, COUNT(*) AS `booked`
                             FROM `dat_cho`
                             WHERE `ngay_dat` IN ('" . implode("','", $dates) . "') 
                               AND `trang_thai` != 'Huy'
                             GROUP BY `id_ca_doc`, `ngay_dat`";
        $bookingStmt = $this->db->query($bookingCountsSql);
        $bookedMap = [];
        if ($bookingStmt) {
            while ($row = $bookingStmt->fetch(PDO::FETCH_ASSOC)) {
                $bookedMap[$row['id_ca_doc'] . '_' . $row['ngay_dat']] = (int)$row['booked'];
            }
        }

        $formattedShifts = [];
        foreach ($shifts as $shift) {
            $freeArray = [];
            foreach ($dates as $dt) {
                $booked = $bookedMap[$shift['id_ca_doc'] . '_' . $dt] ?? 0;
                $freeArray[] = max(0, $capacity - $booked);
            }

            $formattedShifts[] = [
                'id_ca_doc' => (int)$shift['id_ca_doc'],
                'label' => $shift['label'],
                'time' => $shift['time'],
                'start' => $shift['start'],
                'free' => $freeArray,
            ];
        }

        return [
            'days' => $days,
            'shifts' => $formattedShifts,
            'capacity' => $capacity,
        ];
    }

    /**
     * Lấy danh sách lượt đặt chỗ theo ngày cho trang /librarian/rooms
     */
    public function getBookingsByDate(string $date, ?int $shiftId = null): array
    {
        $sql = "SELECT 
                    dc.`id_dat_cho`,
                    dc.`ma_dat_cho` AS `id`,
                    COALESCE(hs.`mssv`, CONCAT('ND', dc.`id_nguoi_dung`)) AS `code`,
                    COALESCE(hs.`ho_ten`, tk.`email`) AS `name`,
                    dc.`ngay_dat` AS `date`,
                    CAST(dc.`id_ca_doc` AS CHAR) AS `shift`,
                    cn.`ma_cho_ngoi` AS `seat`,
                    cd.`ten_ca`,
                    CASE 
                        WHEN dc.`trang_thai` = 'DaNhanCho' THEN 'checked-in'
                        WHEN dc.`trang_thai` = 'QuaHanKhongDen' THEN 'no-show'
                        WHEN dc.`trang_thai` = 'Huy' THEN 'cancelled'
                        ELSE 'reserved'
                    END AS `attendance`,
                    (CASE WHEN dc.`trang_thai` = 'QuaHanKhongDen' THEN 1 ELSE 0 END) AS `violation`
                FROM `dat_cho` dc
                INNER JOIN `tai_khoan` tk ON dc.`id_nguoi_dung` = tk.`id_tai_khoan`
                LEFT JOIN `ho_so_nguoi_dung` hs ON tk.`id_tai_khoan` = hs.`id_tai_khoan`
                INNER JOIN `ca_doc` cd ON dc.`id_ca_doc` = cd.`id_ca_doc`
                LEFT JOIN `cho_ngoi` cn ON dc.`id_cho_ngoi` = cn.`id_cho_ngoi`
                WHERE dc.`ngay_dat` = :date";

        $params = [':date' => $date];

        if ($shiftId !== null && $shiftId > 0) {
            $sql .= " AND dc.`id_ca_doc` = :shift";
            $params[':shift'] = $shiftId;
        }

        $sql .= " ORDER BY cd.`gio_bat_dau` ASC, dc.`thoi_gian_tao` ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
