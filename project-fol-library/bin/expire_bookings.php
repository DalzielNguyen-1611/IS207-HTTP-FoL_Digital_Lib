<?php

declare(strict_types=1);

/**
 * CLI Cronjob / Worker: Tự động quét và giải phóng các lượt đặt chỗ bị quá giờ không đến
 * Chạy định kỳ: * * * * * (mỗi phút một lần) hoặc mỗi 15 phút qua crontab / Windows Task Scheduler
 * Command: php bin/expire_bookings.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

use App\Services\BookingExpirationService;

$service = new BookingExpirationService();
$count = $service->expireAbandonedBookings();

$timestamp = date('Y-m-d H:i:s');
echo "[{$timestamp}] Booking Expiration Worker: Đã quét và giải phóng {$count} lượt đặt chỗ quá hạn.\n";
