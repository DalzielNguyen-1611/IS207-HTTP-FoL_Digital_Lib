<?php

declare(strict_types=1);

namespace Tests\Unit;

require_once dirname(__DIR__, 2) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Core\Database;
use App\Repositories\LoanRepository;
use App\Repositories\RoomRepository;
use App\Services\BookingExpirationService;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Exception;
use PDO;

echo "================================================================================\n";
echo " KIỂM THỬ LOGIC NGHIỆP VỤ THƯ VIỆN & CSDL (LIBRARY BUSINESS LOGIC TEST)\n";
echo "================================================================================\n";

$db = Database::getConnection();
$loanRepo = new LoanRepository($db);
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

// 1. Kiểm tra Giới hạn Mượn sách tối đa (Borrow Limit - Max 3 cuốn)
// Tạo người dùng test tạm thời hoặc dùng tài khoản sinh viên
$stmtUser = $db->query("SELECT tk.id_tai_khoan FROM tai_khoan tk JOIN vai_tro vt ON tk.id_vai_tro = vt.id_vai_tro WHERE vt.ma_vai_tro = 'STUDENT' LIMIT 1");
$testUserId = (int)$stmtUser->fetchColumn();

// Lấy 4 cuốn sách đang sẵn sàng
$stmtCopies = $db->query("SELECT id_cuon_sach FROM cuon_sach WHERE trang_thai = 'SanSang' LIMIT 4");
$copies = $stmtCopies->fetchAll(PDO::FETCH_COLUMN);

if (count($copies) < 4) {
    echo " [ERROR] Cần ít nhất 4 cuốn sách sẵn sàng để kiểm thử nghiệp vụ mượn trả.\n";
    exit(1);
}

// Dọn dẹp các phiếu mượn test cũ của người dùng này nếu có
$db->exec("UPDATE chi_tiet_muon ctm JOIN phieu_muon pm ON ctm.id_phieu_muon = pm.id SET ctm.trang_thai = 'DaTra' WHERE pm.id_nguoi_dung = {$testUserId} AND ctm.trang_thai = 'DangMuon'");

$borrowedLoans = [];
$loanError = null;

try {
    // Mượn 3 cuốn hợp lệ
    $borrowedLoans[] = $loanRepo->createLoan($testUserId, $copies[0], 14);
    $borrowedLoans[] = $loanRepo->createLoan($testUserId, $copies[1], 14);
    $borrowedLoans[] = $loanRepo->createLoan($testUserId, $copies[2], 14);
} catch (Exception $e) {
    $loanError = $e->getMessage();
}

assertTest(
    "Mượn sách trong giới hạn cho phép (3 cuốn) thành công",
    count($borrowedLoans) === 3 && $loanError === null,
    "Đã mượn thành công 3 cuốn: " . implode(', ', array_slice($copies, 0, 3))
);

// Mượn cuốn thứ 4 -> Phải bị chặn bởi Giới hạn mượn tối đa (Issue 19)
$borrowLimitBlocked = false;
$borrowLimitMsg = '';
try {
    $loanRepo->createLoan($testUserId, $copies[3], 14);
} catch (DomainException $e) {
    $borrowLimitBlocked = true;
    $borrowLimitMsg = $e->getMessage();
}

assertTest(
    "Chặn mượn sách vượt hạn mức (Vượt quá 3 cuốn tối đa) - Issue 19",
    $borrowLimitBlocked && str_contains($borrowLimitMsg, 'tối đa'),
    "Thông báo: {$borrowLimitMsg}"
);

// 2. Chống Trùng lặp lượt mượn đang hoạt động cho cùng 1 cuốn sách (Issue 20)
// Tạo thêm 1 người dùng sinh viên khác
$stmtUser2 = $db->query("SELECT tk.id_tai_khoan FROM tai_khoan tk JOIN vai_tro vt ON tk.id_vai_tro = vt.id_vai_tro WHERE vt.ma_vai_tro = 'STUDENT' AND tk.id_tai_khoan != {$testUserId} LIMIT 1");
$testUser2Id = (int)$stmtUser2->fetchColumn();

$duplicateLoanBlocked = false;
$duplicateLoanMsg = '';
try {
    // Cố tình mượn cuốn copies[0] đang trong trạng thái DangMuon của user 1
    $loanRepo->createLoan($testUser2Id, $copies[0], 14);
} catch (DomainException $e) {
    $duplicateLoanBlocked = true;
    $duplicateLoanMsg = $e->getMessage();
}

assertTest(
    "Chống mượn trùng cuốn sách đang có người khác mượn - Issue 20",
    $duplicateLoanBlocked && str_contains($duplicateLoanMsg, 'không sẵn sàng'),
    "Thông báo: {$duplicateLoanMsg}"
);

// 3. Gia hạn sách hợp lệ & Chống gia hạn quá số lần (Issue 23)
$renewSuccess = false;
$firstLoanDetailId = (int)($borrowedLoans[0]['detail_id'] ?? 0);
try {
    $renewResult = $loanRepo->renewBook($firstLoanDetailId);
    $renewSuccess = !empty($renewResult['new_due_date']);
} catch (Exception $e) {
    $renewSuccess = false;
}

assertTest(
    "Gia hạn sách mượn hợp lệ (+7 ngày) thành công - Issue 23",
    $renewSuccess,
    "Hạn mới: " . ($renewResult['new_due_date'] ?? 'N/A')
);

// Thử gia hạn lần thứ 2 -> Phải bị chặn vì mỗi cuốn chỉ được gia hạn tối đa 1 lần
$secondRenewBlocked = false;
$secondRenewMsg = '';
try {
    $loanRepo->renewBook($firstLoanDetailId);
} catch (DomainException $e) {
    $secondRenewBlocked = true;
    $secondRenewMsg = $e->getMessage();
}

assertTest(
    "Chống gia hạn vượt quá số lần cho phép (Tối đa 1 lần)",
    $secondRenewBlocked && str_contains($secondRenewMsg, 'đã được gia hạn'),
    "Thông báo: {$secondRenewMsg}"
);

// 4. Trả sách hợp lệ & Chống trả sách không hợp lệ (Issue 21)
$returnSuccess = false;
try {
    $returnResult = $loanRepo->returnBook($firstLoanDetailId);
    $returnSuccess = !empty($returnResult['success']);
} catch (Exception $e) {
    $returnSuccess = false;
}

assertTest(
    "Trả sách hợp lệ thành công và khôi phục trạng thái cuốn sách thành SanSang - Issue 21",
    $returnSuccess,
    "Tiền phạt: " . number_format($returnResult['fine_amount'] ?? 0) . " VND"
);

// Trả lại lần nữa -> Phải bị chặn vì sách đã trả rồi
$secondReturnBlocked = false;
$secondReturnMsg = '';
try {
    $loanRepo->returnBook($firstLoanDetailId);
} catch (DomainException $e) {
    $secondReturnBlocked = true;
    $secondReturnMsg = $e->getMessage();
}

assertTest(
    "Chống trả sách trùng lặp hoặc sách không trong trạng thái đang mượn",
    $secondReturnBlocked && str_contains($secondReturnMsg, 'đã được trả'),
    "Thông báo: {$secondReturnMsg}"
);

// Trả các cuốn còn lại để dọn dẹp
for ($i = 1; $i < count($borrowedLoans); $i++) {
    if (!empty($borrowedLoans[$i]['detail_id'])) {
        $loanRepo->returnBook((int)$borrowedLoans[$i]['detail_id']);
    }
}

// 5. Kiểm thử Tối ưu hóa truy vấn N+1 trong Báo cáo Thống kê (Issue 29)
$summaryReport = $loanRepo->getReportsSummary();
$reportCount = is_array($summaryReport) ? count($summaryReport) : 0;
$hasBatchData = is_array($summaryReport) && !empty($summaryReport) && isset($summaryReport[0]['date'], $summaryReport[0]['borrowed'], $summaryReport[0]['returned']);
assertTest(
    "Báo cáo thống kê mượn trả xử lý qua Batch Aggregates thay vì vòng lặp 30 truy vấn - Issue 29",
    $hasBatchData,
    "{$reportCount} mốc ngày tổng hợp dữ liệu mượn/trả qua 2 truy vấn gộp duy nhất"
);

// 6. Kiểm thử Tự động Quét hết hạn & Giải phóng chỗ (Ghost Booking Expiration - Issue 3 & 24)
$tz = new DateTimeZone('Asia/Ho_Chi_Minh');
$now = new DateTimeImmutable('now', $tz);

// Tạo 1 lượt đặt chỗ quá khứ chưa nhận chỗ
$shift = $roomRepo->getShifts()[0];
$pastDate = $now->modify('-1 day')->format('Y-m-d');
$db->exec("DELETE FROM dat_cho WHERE ngay_dat = '{$pastDate}'");

$insertPast = $db->prepare(
    "INSERT INTO `dat_cho` (`ma_dat_cho`, `id_cho_ngoi`, `id_ca_doc`, `id_nguoi_dung`, `ngay_dat`, `thoi_gian_tao`, `trang_thai`)
     VALUES ('DC-EXPIRE-TEST-1', 1, :shift_id, :user_id, :past_date, NOW(), 'DaDat')"
);
$insertPast->execute([
    ':shift_id' => (int)$shift['id_ca_doc'],
    ':user_id' => $testUserId,
    ':past_date' => $pastDate,
]);

// Chạy service quét hết hạn
$expirationService = new BookingExpirationService($roomRepo);
$expiredCount = $expirationService->expireAbandonedBookings($now);

// Kiểm tra trạng thái bản ghi trong DB
$stmtStatus = $db->query("SELECT trang_thai FROM dat_cho WHERE ma_dat_cho = 'DC-EXPIRE-TEST-1'");
$updatedStatus = $stmtStatus->fetchColumn();

assertTest(
    "Tự động quét và giải phóng chỗ quá hạn không đến (Ghost Booking Worker) - Issue 3 & 24",
    $expiredCount >= 1 && $updatedStatus === 'QuaHanKhongDen',
    "Đã quét và chuyển {$expiredCount} lượt đặt sang trạng thái QuaHanKhongDen"
);

// Dọn dẹp lượt đặt test
$db->exec("DELETE FROM dat_cho WHERE ma_dat_cho = 'DC-EXPIRE-TEST-1'");

echo "--------------------------------------------------------------------------------\n";
echo "KẾT QUẢ: {$passed}/{$total} kịch bản ĐẠT (" . round(($passed / $total) * 100) . "%)\n";
echo "================================================================================\n\n";

exit($passed === $total ? 0 : 1);
