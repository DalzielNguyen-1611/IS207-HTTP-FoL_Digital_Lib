<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Thực thể Tài khoản Người dùng (User Entity Model)
 */
class User
{
    public function __construct(
        public readonly int $id,
        public readonly int $roleId,
        public readonly string $roleCode,
        public readonly string $email,
        public readonly string $status,
        public readonly ?string $fullName = null,
        public readonly ?string $studentId = null,
        public readonly ?string $phone = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)($data['id_tai_khoan'] ?? $data['id'] ?? 0),
            roleId: (int)($data['id_vai_tro'] ?? 0),
            roleCode: (string)($data['ma_vai_tro'] ?? ''),
            email: (string)($data['email'] ?? ''),
            status: (string)($data['trang_thai'] ?? 'HoatDong'),
            fullName: $data['ho_ten'] ?? null,
            studentId: $data['mssv'] ?? null,
            phone: $data['so_dien_thoai'] ?? null
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'HoatDong';
    }

    public function isAdmin(): bool
    {
        return $this->roleCode === 'ADMIN';
    }

    public function isLibrarian(): bool
    {
        return $this->roleCode === 'LIBRARIAN';
    }

    public function isStudent(): bool
    {
        return $this->roleCode === 'STUDENT';
    }
}
