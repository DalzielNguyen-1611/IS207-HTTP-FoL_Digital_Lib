<?php

declare(strict_types=1);

namespace App\Services\Policies;

use App\Services\AuthorizationService;

/**
 * Chính sách phân quyền tài nguyên Phiếu Mượn & Phiếu Phạt (Loan Policy)
 */
class LoanPolicy
{
    private AuthorizationService $authService;

    public function __construct(?AuthorizationService $authService = null)
    {
        $this->authService = $authService ?? new AuthorizationService();
    }

    /**
     * Sinh viên chỉ được xem phiếu mượn của chính mình; Thủ thư và Admin xem được tất cả
     */
    public function canViewLoan(int $userId, array $loanRecord): bool
    {
        if (!$this->authService->hasPermission($userId, 'loan.view')) {
            return false;
        }

        if ($this->authService->hasPermission($userId, 'loan.confirm')) {
            return true;
        }

        $ownerId = (int)($loanRecord['id_nguoi_dung'] ?? 0);
        return $ownerId === $userId;
    }

    /**
     * Sinh viên chỉ được xem phiếu phạt của chính mình; Thủ thư và Admin xem và xử lý được tất cả
     */
    public function canViewFine(int $userId, array $fineRecord): bool
    {
        if (!$this->authService->hasPermission($userId, 'fine.view')) {
            return false;
        }

        if ($this->authService->hasPermission($userId, 'fine.manage')) {
            return true;
        }

        $ownerId = (int)($fineRecord['id_nguoi_dung'] ?? 0);
        return $ownerId === $userId;
    }
}
