<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Repositories\RoomRepository;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use PDO;

echo "================================================================================\n";
echo " KIỂM THỬ ĐỒNG THỜI & TOÀN VẸN ĐẶT CHỖ (CONCURRENCY & INTEGRITY TEST)\n";
echo "================================================================================\n";

$db = Database::getConnection();
$roomRepo = new RoomRepository($db);
$passed = 0;
$total = 0;

function assertTest(string $description, bool $condition, ?string $detail = null): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo " [PASS] {$description}" . ($detail ? " ({$detail})" : "") . "\n";
    } else {
        echo " [FAIL] {$description}" . ($detail ? " ({$detail})" : "") . "\n";
    }
}

// Lấy danh sách ca đọc đang hoạt động
$shifts = $roomRepo->getShifts();
if (count($shifts) < 2) {
    echo " [ERROR] Cần ít nhất 2 ca đọc hoạt động để kiểm thử.\n";
    exit(1);
}

$shift1 = (int)$shifts[0]['id_ca_doc'];
$shift2 = (int)$shifts[1]['id_ca_doc'];

// Lấy người dùng sinh viên để thử nghiệm
$stmt = $db->query("SELECT tk.id_tai_khoan FROM tai_khoan tk JOIN vai_tro vt ON tk.id_vai_tro = vt.id_vai_tro WHERE vt.ma_vai_tro = 'STUDENT' LIMIT 2");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$userA = (int)($users[0]['id_tai_khoan'] ?? 1);
$userB = (int)($users[1]['id_tai_khoan'] ?? 2);

$tz = new DateTimeZone('Asia/Ho_Chi_Minh');
$now = new DateTimeImmutable('now', $tz);
$testDate = $now->modify('+7 days')->format('Y-m-d');

// Dọn dẹp dữ liệu kiểm thử cũ của ngày này nếu có
$delStmt = $db->prepare("DELETE FROM dat_cho WHERE ngay_dat = :ngay");
$delStmt->execute([':ngay' => $testDate]);

// 1. Đặt chỗ hợp lệ lần đầu thành công
$bookingA = null;
try {
    $bookingA = $roomRepo->createBooking($userA, $testDate, $shift1, $now);
    assertTest(
        "Đặt chỗ hợp lệ lần đầu thành công",
        !empty($bookingA['code']) && $bookingA['status'] === 'DaDat',
        "Mã: " . ($bookingA['code'] ?? 'N/A') . " - Ghế: " . ($bookingA['seat'] ?? 'N/A')
    );
} catch (Exception $e) {
    assertTest("Đặt chỗ hợp lệ lần đầu thành công", false, $e->getMessage());
}

// 2. Chống Xung đột cùng Người dùng: User A cố tình đặt THÊM chỗ trong CÙNG CA, CÙNG NGÀY -> Phải bị chặn
$userConflictBlocked = false;
$userConflictMsg = '';
try {
    $roomRepo->createBooking($userA, $testDate, $shift1, $now);
} catch (Exception $e) {
    $userConflictBlocked = true;
    $userConflictMsg = $e->getMessage();
}

assertTest(
    "Chống Xung đột lịch: Ngăn chặn 1 người dùng đặt nhiều chỗ trong cùng 1 ca",
    $userConflictBlocked && (str_contains($userConflictMsg, 'đã có lượt đặt') || str_contains($userConflictMsg, 'đã có lịch đặt chỗ')),
    "Đã chặn với lỗi: {$userConflictMsg}"
);

// 3. Kiểm tra Ràng buộc Độc nhất CSDL (uq_active_user_shift) ở mức MariaDB Engine
$dbConstraintTriggered = false;
try {
    // Cố tình bypass ứng dụng và chèn trực tiếp 1 bản ghi thứ 2 của User A cùng ca
    $rawInsert = $db->prepare(
        "INSERT INTO `dat_cho` (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`, `ngay_dat`, `thoi_gian_tao`, `trang_thai`)
         VALUES (:code, 2, :shift_id, :user_id, :ngay_dat, NOW(), 'DaDat')"
    );
    $rawInsert->execute([
        ':code' => 'DC-BYPASS-TEST-1',
        ':shift_id' => $shift1,
        ':user_id' => $userA,
        ':ngay_dat' => $testDate,
    ]);
} catch (\PDOException $e) {
    $dbConstraintTriggered = (str_contains($e->getMessage(), 'uq_active_user_shift') || $e->errorInfo[1] === 1062);
}

assertTest(
    "Bảo vệ mức CSDL: Ràng buộc uq_active_user_shift chặn chèn trùng lịch cùng ca",
    $dbConstraintTriggered,
    "MariaDB ném mã lỗi 1062 Duplicate entry cho active_user_shift"
);

// 4. Kiểm tra Ràng buộc Độc nhất CSDL (uq_active_seat_booking) chống Overbooking trùng ghế
$seatOverbookingTriggered = false;
try {
    // Lấy id_cho_ngoi của booking A vừa tạo
    $seatIdOfA = (int)$db->query("SELECT id_cho_ngoi FROM dat_cho WHERE ma_dat_cho = " . $db->quote($bookingA['code']))->fetchColumn();
    // User B cố tình chèn trực tiếp vào cùng ghế id đó
    $rawSeatInsert = $db->prepare(
        "INSERT INTO `dat_cho` (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`, `ngay_dat`, `thoi_gian_tao`, `trang_thai`)
         VALUES (:code, :seat_id, :shift_id, :user_id, :ngay_dat, NOW(), 'DaDat')"
    );
    $rawSeatInsert->execute([
        ':code' => 'DC-BYPASS-SEAT-2',
        ':seat_id' => $seatIdOfA,
        ':shift_id' => $shift1,
        ':user_id' => $userB,
        ':ngay_dat' => $testDate,
    ]);
} catch (\PDOException $e) {
    $seatOverbookingTriggered = (str_contains($e->getMessage(), 'uq_active_seat_booking') || $e->errorInfo[1] === 1062);
}

assertTest(
    "Bảo vệ mức CSDL: Ràng buộc uq_active_seat_booking chặn tuyệt đối overbooking cùng ghế",
    $seatOverbookingTriggered,
    "MariaDB ném mã lỗi 1062 Duplicate entry cho active_seat_booking"
);

// 5. Cho phép User A đặt ở CA KHÁC trong cùng ngày (ca 2)
$caKhacSuccess = false;
try {
    $bookingA2 = $roomRepo->createBooking($userA, $testDate, $shift2, $now);
    $caKhacSuccess = !empty($bookingA2['code']);
} catch (Exception $e) {
    $caKhacSuccess = false;
}

assertTest(
    "Cho phép người dùng đặt ca khác nhau trong cùng ngày",
    $caKhacSuccess,
    "Ca 2 tạo thành công: " . ($bookingA2['code'] ?? 'N/A')
);

// 6. Hủy đặt chỗ và kiểm tra giải phóng ràng buộc độc nhất (Cho phép đặt lại sau khi hủy)
$rebookAfterCancelSuccess = false;
try {
    if (!empty($bookingA['id'])) {
        $roomRepo->cancelBooking($userA, (int)$bookingA['id'], $now);
        // Đặt lại ca 1 thành công vì lượt đặt cũ đã chuyển sang 'DaHuy' (active column = NULL)
        $bookingA_Rebook = $roomRepo->createBooking($userA, $testDate, $shift1, $now);
        $rebookAfterCancelSuccess = !empty($bookingA_Rebook['code']);
    }
} catch (Exception $e) {
    $rebookAfterCancelSuccess = false;
}

assertTest(
    "Giải phóng ràng buộc khi hủy đặt chỗ: Cho phép đặt lại sau khi trạng thái là DaHuy",
    $rebookAfterCancelSuccess,
    "Cột sinh ảo active_user_shift trở về NULL khi hủy, giải phóng chỉ mục độc nhất"
);

// 7. Kiểm tra cơ chế tự động xử lý Deadlock (Lỗi 1213) qua Database::transactionWithRetry
$retryExecuted = false;
$attemptsCount = 0;
try {
    $result = Database::transactionWithRetry(function($conn) use (&$attemptsCount) {
        $attemptsCount++;
        if ($attemptsCount < 3) {
            // Giả lập lỗi deadlock MariaDB 1213
            throw new \PDOException("Deadlock found when trying to get lock; try restarting transaction", 1213);
        }
        return 'SUCCESS_AFTER_RETRY';
    }, 3);
    $retryExecuted = ($result === 'SUCCESS_AFTER_RETRY' && $attemptsCount === 3);
} catch (Exception $e) {
    $retryExecuted = false;
}

assertTest(
    "Cơ chế tự động Retry khi gặp Deadlock (Lỗi 1213) với Exponential Backoff",
    $retryExecuted,
    "Thực thi thành công sau {$attemptsCount} lần thử lại"
);

// Dọn dẹp dữ liệu kiểm thử
$delStmt->execute([':ngay' => $testDate]);

echo "--------------------------------------------------------------------------------\n";
echo "KẾT QUẢ: {$passed}/{$total} kịch bản ĐẠT (" . round(($passed / $total) * 100) . "%)\n";
echo "================================================================================\n\n";

exit($passed === $total ? 0 : 1);
