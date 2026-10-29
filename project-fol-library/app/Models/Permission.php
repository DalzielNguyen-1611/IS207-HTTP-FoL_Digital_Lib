<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Thực thể Quyền Hạn (Permission Entity Model)
 * Mã quyền theo định dạng: <resource>.<action>
 */
class Permission
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
            id: (int)($data['id_quyen'] ?? $data['id'] ?? 0),
            code: (string)($data['ma_quyen'] ?? ''),
            name: (string)($data['ten_quyen'] ?? ''),
            description: $data['mo_ta'] ?? null
        );
    }
}
