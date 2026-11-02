<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Repositories\RoomRepository;
use App\Services\RoomBookingService;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use PDO;
use RuntimeException;

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec(
    "CREATE TABLE ca_doc (
        id_ca_doc INTEGER PRIMARY KEY,
        ten_ca TEXT NOT NULL,
        gio_bat_dau TEXT NOT NULL,
        gio_ket_thuc TEXT NOT NULL,
        trang_thai TEXT NOT NULL
    );
    CREATE TABLE cho_ngoi (
        id_cho_ngoi INTEGER PRIMARY KEY AUTOINCREMENT,
        ma_cho_ngoi TEXT NOT NULL,
        trang_thai TEXT NOT NULL
    );
    CREATE TABLE dat_cho (
        id_dat_cho INTEGER PRIMARY KEY AUTOINCREMENT,
        ma_dat_cho TEXT NOT NULL UNIQUE,
        id_cho_ngoi INTEGER NOT NULL,
        id_ca_doc INTEGER NOT NULL,
        id_nguoi_dung INTEGER NOT NULL,
        ngay_dat TEXT NOT NULL,
        thoi_gian_tao TEXT NOT NULL,
        thoi_gian_huy TEXT NULL,
        trang_thai TEXT NOT NULL
    );"
);
$db->exec("INSERT INTO ca_doc VALUES (1, 'Ca sáng', '07:30:00', '11:30:00', 'HoatDong')");
$db->exec("INSERT INTO cho_ngoi (ma_cho_ngoi, trang_thai) VALUES ('G01', 'HoatDong'), ('G02', 'HoatDong')");

$service = new RoomBookingService(new RoomRepository($db));
$repository = new RoomRepository($db);
$capacity = $repository->getTotalCapacity();
if ($capacity !== 2) {
    throw new RuntimeException('Sức chứa phải khớp số ghế đang hoạt động.');
}

$timeZone = new DateTimeZone('Asia/Ho_Chi_Minh');
$invalidWeekRejected = false;
try {
    $service->getSchedule('2026-11-03');
} catch (DomainException) {
    $invalidWeekRejected = true;
}
if (!$invalidWeekRejected) {
    throw new RuntimeException('Lịch tuần phải bắt đầu từ thứ Hai.');
}

$scheduleRepository = new class extends RoomRepository {
    public ?DateTimeImmutable $requestedWeek = null;

    public function __construct()
    {
    }

    public function getWeeklySchedule(?DateTimeImmutable $startDate = null): array
    {
        $this->requestedWeek = $startDate;
        return [];
    }
};
$scheduleService = new RoomBookingService($scheduleRepository);
$scheduleService->getSchedule('2026-11-02');
if ($scheduleRepository->requestedWeek?->format('Y-m-d') !== '2026-11-02') {
    throw new RuntimeException('Lịch phải tải đúng tuần được chọn.');
}

$bookingDate = (new DateTimeImmutable('today', $timeZone))
    ->modify('next monday')
    ->modify('+4 days')
    ->format('Y-m-d');
$first = $service->createBooking(101, $bookingDate, 1);
if ($first['seat'] !== 'G01') {
    throw new RuntimeException('Đặt chỗ đầu tiên phải được gán ghế trống đầu tiên.');
}

$duplicateRejected = false;
try {
    $service->createBooking(101, $bookingDate, 1);
} catch (DomainException) {
    $duplicateRejected = true;
}
if (!$duplicateRejected) {
    throw new RuntimeException('Không được tạo hai lượt đặt cùng tài khoản, ngày và ca.');
}

$second = $service->createBooking(102, $bookingDate, 1);
if ($second['seat'] !== 'G02') {
    throw new RuntimeException('Hai lượt đặt cùng ca phải nhận hai ghế khác nhau.');
}

$capacityRejected = false;
try {
    $service->createBooking(103, $bookingDate, 1);
} catch (DomainException) {
    $capacityRejected = true;
}
if (!$capacityRejected) {
    throw new RuntimeException('Không được nhận đặt chỗ khi đã hết ghế.');
}

$futureWeekRejected = false;
try {
    $service->createBooking(
        105,
        (new DateTimeImmutable($bookingDate, $timeZone))->modify('+7 days')->format('Y-m-d'),
        1
    );
} catch (DomainException) {
    $futureWeekRejected = true;
}
if (!$futureWeekRejected) {
    throw new RuntimeException('Không được đặt trước tuần kế tiếp.');
}

$db->exec("INSERT INTO cho_ngoi (ma_cho_ngoi, trang_thai) VALUES ('G03', 'HoatDong')");
$bookings = $service->getBookings(101);
if (count($bookings) !== 1 || $bookings[0]['seat'] !== 'G01' || !$bookings[0]['can_cancel']) {
    throw new RuntimeException('Danh sách lượt đặt phải thuộc đúng người dùng và có thông tin ghế.');
}

$service->cancelBooking(101, $first['id']);
$cancelled = $service->getBookings(101);
if (count($cancelled) !== 1 || $cancelled[0]['status'] !== 'Huy') {
    throw new RuntimeException('Hủy chỗ phải được lưu lại trong lịch sử đặt.');
}

$replacement = $service->createBooking(103, $bookingDate, 1);
if ($replacement['seat'] !== 'G01') {
    throw new RuntimeException('Ghế đã hủy phải được giải phóng cho lượt đặt mới.');
}

$cutoffBooking = $service->createBooking(104, $bookingDate, 1);
$startAt = new DateTimeImmutable($bookingDate . ' 07:30:00', $timeZone);
$insideCancellationWindow = false;
try {
    (new RoomRepository($db))->cancelBooking(
        104,
        $cutoffBooking['id'],
        $startAt->modify('-23 hours')
    );
} catch (DomainException) {
    $insideCancellationWindow = true;
}
if (!$insideCancellationWindow) {
    throw new RuntimeException('Không được hủy lượt đặt khi còn dưới 24 giờ.');
}

$db->exec("INSERT INTO cho_ngoi (ma_cho_ngoi, trang_thai) VALUES ('G04', 'HoatDong')");
$exactCutoffBooking = $service->createBooking(106, $bookingDate, 1);
$repository->cancelBooking(
    106,
    $exactCutoffBooking['id'],
    $startAt->modify('-24 hours')
);
if ($service->getBookings(106)[0]['status'] !== 'Huy') {
    throw new RuntimeException('Được phép hủy khi còn đúng 24 giờ trước ca.');
}

$foreignBookingRejected = false;
try {
    $service->cancelBooking(102, $replacement['id']);
} catch (DomainException) {
    $foreignBookingRejected = true;
}
if (!$foreignBookingRejected) {
    throw new RuntimeException('Không được hủy lượt đặt thuộc tài khoản khác.');
}

$db->exec("UPDATE cho_ngoi SET trang_thai = 'BaoTri'");
if ($repository->getTotalCapacity() !== 0) {
    throw new RuntimeException('Không được báo sức chứa giả khi toàn bộ ghế ngừng hoạt động.');
}

echo "PASS: Room booking create, duplicate, capacity, list, cancel and ownership checks." . PHP_EOL;
