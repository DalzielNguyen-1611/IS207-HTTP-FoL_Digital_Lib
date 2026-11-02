<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use PDO;
use Throwable;

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
        return (int)$stmt->fetchColumn();
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
        $datePlaceholders = [];
        $dateParams = [];
        foreach ($dates as $index => $date) {
            $placeholder = ':date' . $index;
            $datePlaceholders[] = $placeholder;
            $dateParams[$placeholder] = $date;
        }

        $bookingCountsSql = "SELECT `id_ca_doc`, `ngay_dat`, COUNT(*) AS `booked`
                             FROM `dat_cho`
                             WHERE `ngay_dat` IN (" . implode(', ', $datePlaceholders) . ")
                               AND `trang_thai` IN ('DaDat', 'DaNhanCho')
                             GROUP BY `id_ca_doc`, `ngay_dat`";
        $bookingStmt = $this->db->prepare($bookingCountsSql);
        $bookingStmt->execute($dateParams);
        $bookedMap = [];
        while ($row = $bookingStmt->fetch(PDO::FETCH_ASSOC)) {
            $bookedMap[$row['id_ca_doc'] . '_' . $row['ngay_dat']] = (int)$row['booked'];
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
     * Tạo lượt đặt và tự động gán ghế còn trống trong một giao dịch.
     */
    public function createBooking(
        int $userId,
        string $date,
        int $shiftId,
        DateTimeImmutable $now
    ): array {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;
            $ownsTransaction = !$this->db->inTransaction();
            if ($ownsTransaction) {
                $this->db->beginTransaction();
            }

            try {
                $lockSuffix = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
                    ? ' FOR UPDATE'
                    : '';

                $shiftStmt = $this->db->prepare(
                    "SELECT `id_ca_doc`, `ten_ca`, `gio_bat_dau`, `gio_ket_thuc`
                     FROM `ca_doc`
                     WHERE `id_ca_doc` = :shift_id AND `trang_thai` = 'HoatDong'"
                     . $lockSuffix
                );
                $shiftStmt->execute([':shift_id' => $shiftId]);
                $shift = $shiftStmt->fetch(PDO::FETCH_ASSOC);

                if ($shift === false) {
                    throw new DomainException('Ca đọc không tồn tại hoặc đã ngừng hoạt động.');
                }

                $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
                $startAt = new DateTimeImmutable($date . ' ' . $shift['gio_bat_dau'], $timeZone);
                if ($startAt <= $now) {
                    throw new DomainException('Ca đọc này đã bắt đầu hoặc đã qua.');
                }

                $existingStmt = $this->db->prepare(
                    "SELECT 1
                     FROM `dat_cho`
                     WHERE `id_nguoi_dung` = :user_id
                       AND `ngay_dat` = :booking_date
                       AND `id_ca_doc` = :shift_id
                       AND `trang_thai` IN ('DaDat', 'DaNhanCho')
                     LIMIT 1"
                );
                $existingStmt->execute([
                    ':user_id' => $userId,
                    ':booking_date' => $date,
                    ':shift_id' => $shiftId,
                ]);
                if ($existingStmt->fetchColumn() !== false) {
                    throw new DomainException('Bạn đã có lượt đặt cho ca đọc này.');
                }

                $seatStmt = $this->db->prepare(
                    "SELECT cn.`id_cho_ngoi`, cn.`ma_cho_ngoi`
                     FROM `cho_ngoi` cn
                     WHERE cn.`trang_thai` = 'HoatDong'
                       AND NOT EXISTS (
                           SELECT 1
                           FROM `dat_cho` dc
                           WHERE dc.`id_cho_ngoi` = cn.`id_cho_ngoi`
                             AND dc.`id_ca_doc` = :shift_id
                             AND dc.`ngay_dat` = :booking_date
                             AND dc.`trang_thai` IN ('DaDat', 'DaNhanCho')
                       )
                     ORDER BY cn.`id_cho_ngoi` ASC
                     LIMIT 1"
                     . $lockSuffix
                );
                $seatStmt->execute([
                    ':shift_id' => $shiftId,
                    ':booking_date' => $date,
                ]);
                $seat = $seatStmt->fetch(PDO::FETCH_ASSOC);

                if ($seat === false) {
                    throw new DomainException('Ca đọc đã hết chỗ. Vui lòng chọn ca khác.');
                }

                $bookingCode = 'DC-' . strtoupper(bin2hex(random_bytes(8)));
                $createdAt = $now->setTimezone($timeZone)->format('Y-m-d H:i:s');
                $insertStmt = $this->db->prepare(
                    "INSERT INTO `dat_cho`
                        (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`,
                         `ngay_dat`, `thoi_gian_tao`, `trang_thai`)
                     VALUES
                        (:booking_code, :seat_id, :shift_id, :user_id,
                         :booking_date, :created_at, 'DaDat')"
                );
                $insertStmt->execute([
                    ':booking_code' => $bookingCode,
                    ':seat_id' => (int)$seat['id_cho_ngoi'],
                    ':shift_id' => $shiftId,
                    ':user_id' => $userId,
                    ':booking_date' => $date,
                    ':created_at' => $createdAt,
                ]);

                $booking = [
                    'id' => (int)$this->db->lastInsertId(),
                    'code' => $bookingCode,
                    'date' => $date,
                    'shift_id' => $shiftId,
                    'shift' => $shift['ten_ca'],
                    'start' => $shift['gio_bat_dau'],
                    'end' => $shift['gio_ket_thuc'],
                    'seat' => $seat['ma_cho_ngoi'],
                    'status' => 'DaDat',
                    'created_at' => $createdAt,
                ];

                if ($ownsTransaction) {
                    $this->db->commit();
                }

                return $booking;
            } catch (\PDOException $exception) {
                if ($ownsTransaction && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                $errorCode = (int)($exception->errorInfo[1] ?? 0);
                $sqlState = (string)($exception->errorInfo[0] ?? $exception->getCode());
                $isDeadlock = ($errorCode === 1213 || $errorCode === 1205 || $sqlState === '40001');

                if ($isDeadlock && $attempt < $maxRetries) {
                    usleep((int)((50000 * (2 ** ($attempt - 1))) + random_int(1000, 20000)));
                    continue;
                }

                $msg = $exception->getMessage();
                if ($errorCode === 1062 || str_contains($msg, 'uq_active_user_shift') || str_contains($msg, 'active_user_shift')) {
                    throw new DomainException('Bạn đã có lượt đặt cho ca đọc này.');
                }
                if ($errorCode === 1062 || str_contains($msg, 'uq_active_seat_booking') || str_contains($msg, 'active_seat_booking')) {
                    throw new DomainException('Chỗ ngồi này vừa được người khác đặt trước. Vui lòng chọn ca hoặc chỗ khác.');
                }

                throw $exception;
            } catch (Throwable $exception) {
                if ($ownsTransaction && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                throw $exception;
            }
        }

        throw new DomainException('Không thể hoàn tất đặt chỗ do xung đột truy cập đồng thời. Vui lòng thử lại.');
    }

    /**
     * Lấy lịch đặt chỗ của một tài khoản, mới nhất trước.
     */
    public function getBookingsByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT dc.`id_dat_cho` AS `id`,
                    dc.`ma_dat_cho` AS `code`,
                    dc.`ngay_dat` AS `date`,
                    dc.`trang_thai` AS `status`,
                    dc.`thoi_gian_tao` AS `created_at`,
                    dc.`thoi_gian_huy` AS `cancelled_at`,
                    cn.`ma_cho_ngoi` AS `seat`,
                    cd.`id_ca_doc` AS `shift_id`,
                    cd.`ten_ca` AS `shift`,
                    cd.`gio_bat_dau` AS `start`,
                    cd.`gio_ket_thuc` AS `end`
             FROM `dat_cho` dc
             INNER JOIN `ca_doc` cd ON cd.`id_ca_doc` = dc.`id_ca_doc`
             INNER JOIN `cho_ngoi` cn ON cn.`id_cho_ngoi` = dc.`id_cho_ngoi`
             WHERE dc.`id_nguoi_dung` = :user_id
             ORDER BY dc.`ngay_dat` DESC, cd.`gio_bat_dau` DESC, dc.`id_dat_cho` DESC"
        );
        $stmt->execute([':user_id' => $userId]);

        $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
        $now = new DateTimeImmutable('now', $timeZone);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($bookings as &$booking) {
            $startAt = new DateTimeImmutable(
                $booking['date'] . ' ' . $booking['start'],
                $timeZone
            );
            $booking['can_cancel'] = $booking['status'] === 'DaDat'
                && $startAt->getTimestamp() - $now->getTimestamp() >= 24 * 60 * 60;
            $booking['starts_at'] = $startAt->format(DateTimeImmutable::ATOM);
        }
        unset($booking);

        return $bookings;
    }

    /**
     * Hủy lượt đặt của chính tài khoản khi còn tối thiểu 24 giờ trước ca.
     */
    public function cancelBooking(int $userId, int $bookingId, DateTimeImmutable $now): void
    {
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) {
            $this->db->beginTransaction();
        }

        try {
            $lockSuffix = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
                ? ' FOR UPDATE'
                : '';
            $stmt = $this->db->prepare(
                "SELECT dc.`id_dat_cho`, dc.`trang_thai`, dc.`ngay_dat`,
                        cd.`gio_bat_dau`
                 FROM `dat_cho` dc
                 INNER JOIN `ca_doc` cd ON cd.`id_ca_doc` = dc.`id_ca_doc`
                 WHERE dc.`id_dat_cho` = :booking_id
                   AND dc.`id_nguoi_dung` = :user_id"
                 . $lockSuffix
            );
            $stmt->execute([
                ':booking_id' => $bookingId,
                ':user_id' => $userId,
            ]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking === false) {
                throw new DomainException('Không tìm thấy lượt đặt chỗ của bạn.');
            }
            if ($booking['trang_thai'] !== 'DaDat') {
                throw new DomainException('Lượt đặt này không còn ở trạng thái có thể hủy.');
            }

            $timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
            $startAt = new DateTimeImmutable(
                $booking['ngay_dat'] . ' ' . $booking['gio_bat_dau'],
                $timeZone
            );
            if ($startAt->getTimestamp() - $now->getTimestamp() < 24 * 60 * 60) {
                throw new DomainException('Chỉ có thể hủy trước ca đọc ít nhất 24 giờ.');
            }

            $updateStmt = $this->db->prepare(
                "UPDATE `dat_cho`
                 SET `trang_thai` = 'Huy', `thoi_gian_huy` = :cancelled_at
                 WHERE `id_dat_cho` = :booking_id
                   AND `id_nguoi_dung` = :user_id
                   AND `trang_thai` = 'DaDat'"
            );
            $updateStmt->execute([
                ':cancelled_at' => $now->setTimezone($timeZone)->format('Y-m-d H:i:s'),
                ':booking_id' => $bookingId,
                ':user_id' => $userId,
            ]);
            if ($updateStmt->rowCount() !== 1) {
                throw new DomainException('Lượt đặt đã được cập nhật bởi thao tác khác.');
            }

            if ($ownsTransaction) {
                $this->db->commit();
            }
        } catch (Throwable $exception) {
            if ($ownsTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
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
                LEFT JOIN `tai_khoan` tk ON dc.`id_nguoi_dung` = tk.`id_tai_khoan`
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

    /**
     * Xác nhận Check-in cho bạn đọc khi đến nhận chỗ ngồi (Issue 24: No-show Booking check-in)
     */
    public function checkinBooking(int $bookingId, ?DateTimeImmutable $now = null): void
    {
        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        $now = $now ?? new DateTimeImmutable('now', $tz);

        $stmt = $this->db->prepare(
            "SELECT dc.`id_dat_cho`, dc.`trang_thai`, dc.`ngay_dat`, cd.`gio_bat_dau`, cd.`gio_ket_thuc`
             FROM `dat_cho` dc
             INNER JOIN `ca_doc` cd ON cd.`id_ca_doc` = dc.`id_ca_doc`
             WHERE dc.`id_dat_cho` = :id"
        );
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new DomainException('Không tìm thấy lượt đặt chỗ.');
        }

        if ($booking['trang_thai'] === 'DaNhanCho') {
            throw new DomainException('Lượt đặt này đã được check-in trước đó.');
        }

        if ($booking['trang_thai'] !== 'DaDat') {
            throw new DomainException("Không thể check-in lượt đặt ở trạng thái {$booking['trang_thai']}.");
        }

        $update = $this->db->prepare(
            "UPDATE `dat_cho`
             SET `trang_thai` = 'DaNhanCho', `thoi_gian_checkin` = :checkin_time
             WHERE `id_dat_cho` = :id AND `trang_thai` = 'DaDat'"
        );
        $update->execute([
            ':checkin_time' => $now->format('Y-m-d H:i:s'),
            ':id' => $bookingId,
        ]);

        if ($update->rowCount() !== 1) {
            throw new DomainException('Lượt đặt đã được cập nhật bởi tiến trình khác.');
        }
    }

    /**
     * Tự động quét và giải phóng các lượt đặt chỗ bị bỏ rơi / quá giờ không đến (Issue 3 & 24)
     * Đổi trạng thái 'DaDat' -> 'QuaHanKhongDen'
     *
     * @return int Số lượt đặt bị hết hạn và giải phóng
     */
    public function expireNoShowBookings(?DateTimeImmutable $now = null): int
    {
        $tz = new DateTimeZone('Asia/Ho_Chi_Minh');
        $now = $now ?? new DateTimeImmutable('now', $tz);
        $currentDate = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        // Hết hạn nếu:
        // 1. Ngày đặt là quá khứ (< currentDate)
        // 2. Ngày đặt là hôm nay và giờ kết thúc ca đọc đã qua (< currentTime)
        $sql = "UPDATE `dat_cho` dc
                INNER JOIN `ca_doc` cd ON cd.`id_ca_doc` = dc.`id_ca_doc`
                SET dc.`trang_thai` = 'QuaHanKhongDen',
                    dc.`ghi_chu` = CONCAT(COALESCE(dc.`ghi_chu`, ''), ' [Tự động giải phóng do quá giờ không checkin]')
                WHERE dc.`trang_thai` = 'DaDat'
                  AND (
                      dc.`ngay_dat` < :cur_date
                      OR (dc.`ngay_dat` = :cur_date_2 AND cd.`gio_ket_thuc` <= :cur_time)
                  )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cur_date' => $currentDate,
            ':cur_date_2' => $currentDate,
            ':cur_time' => $currentTime,
        ]);

        return $stmt->rowCount();
    }
}
