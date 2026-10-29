<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PermissionRepository;
use App\Repositories\UserRepository;

/**
 * Dịch vụ Phân quyền tập trung (Centralized Authorization Service)
 * Thực thi nguyên tắc Deny by Default, Least Privilege và kiểm soát phân quyền RBAC
 */
class AuthorizationService
{
    private PermissionRepository $permissionRepo;
    private UserRepository $userRepo;

    /**
     * Cache quyền theo request để tránh lặp truy vấn cơ sở dữ liệu
     * @var array<int, array<string, bool>>
     */
    private array $userPermissionsCache = [];

    /**
     * Cache thông tin người dùng theo request
     * @var array<int, array|null>
     */
    private array $userCache = [];

    public function __construct(
        ?PermissionRepository $permissionRepo = null,
        ?UserRepository $userRepo = null
    ) {
        $this->permissionRepo = $permissionRepo ?? new PermissionRepository();
        $this->userRepo = $userRepo ?? new UserRepository();
    }

    /**
     * Kiểm tra người dùng có quyền cụ thể hay không (<resource>.<action>)
     * Nguyên tắc: Mặc định từ chối (Deny by default)
     */
    public function hasPermission(int $userId, string $permissionCode): bool
    {
        if ($userId <= 0 || empty($permissionCode)) {
            return false;
        }

        // Kiểm tra trạng thái tài khoản, nếu bị Khóa thì từ chối ngay lập tức
        $user = $this->getUser($userId);
        if ($user === null || ($user['trang_thai'] ?? '') !== 'HoatDong') {
            return false;
        }

        $permissions = $this->getUserPermissions($userId);
        return isset($permissions[$permissionCode]);
    }

    /**
     * Kiểm tra người dùng có vai trò cụ thể hay không (ADMIN, LIBRARIAN, STUDENT)
     */
    public function hasRole(int $userId, string $roleCode): bool
    {
        if ($userId <= 0 || empty($roleCode)) {
            return false;
        }

        $user = $this->getUser($userId);
        if ($user === null || ($user['trang_thai'] ?? '') !== 'HoatDong') {
            return false;
        }

        return ($user['ma_vai_tro'] ?? '') === strtoupper($roleCode);
    }

    /**
     * Lấy danh sách mã quyền của người dùng (dưới dạng mảng key => true để tra cứu O(1))
     * @return array<string, bool>
     */
    public function getUserPermissions(int $userId): array
    {
        if (isset($this->userPermissionsCache[$userId])) {
            return $this->userPermissionsCache[$userId];
        }

        $codes = $this->permissionRepo->getPermissionCodesByUserId($userId);
        $mapped = [];
        foreach ($codes as $code) {
            $mapped[$code] = true;
        }

        $this->userPermissionsCache[$userId] = $mapped;
        return $mapped;
    }

    /**
     * Xóa cache quyền khi vai trò hoặc quyền của tài khoản bị thay đổi
     */
    public function clearCache(?int $userId = null): void
    {
        if ($userId !== null) {
            unset($this->userPermissionsCache[$userId], $this->userCache[$userId]);
        } else {
            $this->userPermissionsCache = [];
            $this->userCache = [];
        }
    }

    private function getUser(int $userId): ?array
    {
        if (!array_key_exists($userId, $this->userCache)) {
            $this->userCache[$userId] = $this->userRepo->findById($userId);
        }

        return $this->userCache[$userId];
    }
}
