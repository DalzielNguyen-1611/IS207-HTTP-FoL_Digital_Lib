<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;

/**
 * Dịch vụ Ghi nhận Nhật ký Hoạt động & Kiểm toán (Audit Service)
 * Loại bỏ toàn bộ thông tin nhạy cảm (mật khẩu, token) trước khi ghi log
 */
class AuditService
{
    private AuditLogRepository $auditRepo;

    public function __construct(?AuditLogRepository $auditRepo = null)
    {
        $this->auditRepo = $auditRepo ?? new AuditLogRepository();
    }

    public function log(
        ?int $userId,
        string $action,
        string $targetTable,
        ?string $targetId = null,
        ?string $description = null
    ): bool {
        // Loại bỏ mật khẩu hoặc thông tin nhạy cảm nếu có trong mô tả
        $cleanDesc = $this->sanitizeDescription($description);

        return $this->auditRepo->log(
            userId: $userId,
            action: $action,
            targetTable: $targetTable,
            targetId: $targetId,
            description: $cleanDesc
        );
    }

    private function sanitizeDescription(?string $desc): ?string
    {
        if ($desc === null) {
            return null;
        }

        // Ẩn mật khẩu và token khỏi log
        $sanitized = preg_replace('/(password|mat_khau|token|secret)[\'"]?\s*[:=]\s*[\'"]?[^\'",\s]+/i', '$1: [REDACTED]', $desc);

        return $sanitized ?: $desc;
    }
}
