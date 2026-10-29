<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Thực thể Vai Trò (Role Entity Model)
 */
class Role
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int)($data['id_vai_tro'] ?? $data['id'] ?? 0),
            code: (string)($data['ma_vai_tro'] ?? ''),
            name: (string)($data['ten_vai_tro'] ?? ''),
            description: $data['mo_ta'] ?? null
        );
    }
}
