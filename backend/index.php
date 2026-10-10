<?php
/**
 * TechCare Backend Entry Point
 * Giới thiệu API và kiểm tra trạng thái môi trường PHP
 */

require_once __DIR__ . '/config/database.php';
$isDbConnected = Database::isConnected();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>TechCare Platform - PHP Backend API Server</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 font-['Inter'] min-h-screen p-6 sm:p-12">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between border-b border-slate-800 pb-6 mb-8">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-semibold mb-2">
          🐘 Pure PHP 8.x OOP &amp; PDO Backend
        </div>
        <h1 class="text-3xl font-extrabold text-white font-['Plus_Jakarta_Sans']">TechCare Backend API</h1>
        <p class="text-slate-400 text-sm mt-1">Hệ sinh thái máy chủ RESTful API nền tảng dịch vụ kỹ thuật TechCare</p>
      </div>
      <div class="flex flex-col items-end gap-2">
        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          PHP Engine Active
        </span>
        <a href="../frontend/index.html" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition shadow-lg shadow-blue-500/20">
          Mở Frontend Portal &rarr;
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60">
        <div class="text-xs text-slate-400 font-medium">Kiến trúc hệ thống</div>
        <div class="text-xl font-bold text-white mt-1">Tiered Architecture</div>
        <div class="text-xs text-blue-400 mt-2 font-mono">Presentation &bull; Logic &bull; Data</div>
      </div>
      <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60">
        <div class="text-xs text-slate-400 font-medium">Phiên bản PHP</div>
        <div class="text-xl font-bold text-white mt-1"><?php echo PHP_VERSION; ?></div>
        <div class="text-xs text-emerald-400 mt-2 font-mono">PHP OOP / PDO / JSON</div>
      </div>
      <div class="p-5 rounded-2xl bg-slate-800/60 border border-slate-700/60">
        <div class="text-xs text-slate-400 font-medium">Cơ sở dữ liệu</div>
        <div class="text-xl font-bold text-white mt-1"><?php echo $isDbConnected ? 'MySQL Connected' : 'Database Offline'; ?></div>
        <div class="text-xs <?php echo $isDbConnected ? 'text-emerald-400' : 'text-rose-400'; ?> mt-2 font-mono">
          <?php echo $isDbConnected ? 'DB: techcare_db (Active)' : 'Vui lòng kiểm tra kết nối MySQL'; ?>
        </div>
      </div>
    </div>

    <div class="bg-slate-800/40 border border-slate-800 rounded-2xl p-6">
      <h2 class="text-lg font-bold text-white mb-4 flex items-center justify-between">
        <span>Danh sách RESTful API Endpoints</span>
        <a href="api/index.php" target="_blank" class="text-xs text-blue-400 hover:underline font-normal">Xem JSON Response &rarr;</a>
      </h2>

      <div class="space-y-3 font-['JetBrains_Mono'] text-xs">
        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">GET</span>
            <span class="text-slate-200">api/bookings.php</span>
          </div>
          <span class="text-slate-400">Danh sách toàn bộ phiếu yêu cầu / tickets</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">POST</span>
            <span class="text-slate-200">api/bookings.php</span>
          </div>
          <span class="text-slate-400">Tạo đơn đặt lịch sửa chữa / cứu hộ IT</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">GET</span>
            <span class="text-slate-200">api/dispatch.php?action=dashboard</span>
          </div>
          <span class="text-slate-400">Thống kê ca trực và hàng đợi điều phối</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">POST</span>
            <span class="text-slate-200">api/dispatch.php?action=assign</span>
          </div>
          <span class="text-slate-400">Phân công kỹ thuật viên cho ca trực</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">GET</span>
            <span class="text-slate-200">api/technicians.php?action=schedule</span>
          </div>
          <span class="text-slate-400">Lịch làm việc của kỹ thuật viên (#NV-204)</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold">GET</span>
            <span class="text-slate-200">api/technicians.php?action=wallet</span>
          </div>
          <span class="text-slate-400">Xem số dư ví 3.450.000đ &amp; lịch sử nhận tiền</span>
        </div>

        <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 font-bold">POST</span>
            <span class="text-slate-200">api/technicians.php?action=withdraw</span>
          </div>
          <span class="text-slate-400">Tạo lệnh rút tiền về tài khoản ngân hàng</span>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
