<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Tầng truy vấn dữ liệu Vai Trò (Role Repository)
 * 100% Prepared Statements qua PDO
 */
class RoleRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id_vai_tro`, `ma_vai_tro`, `ten_vai_tro`, `mo_ta`, `created_at`, `updated_at`
             FROM `vai_tro`
             WHERE `id_vai_tro` = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT `id_vai_tro`, `ma_vai_tro`, `ten_vai_tro`, `mo_ta`, `created_at`, `updated_at`
             FROM `vai_tro`
             WHERE `ma_vai_tro` = :code
             LIMIT 1'
        );
        $stmt->execute([':code' => $code]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT `id_vai_tro`, `ma_vai_tro`, `ten_vai_tro`, `mo_ta`, `created_at`, `updated_at`
             FROM `vai_tro`
             ORDER BY `id_vai_tro` ASC'
        );

        return $stmt->fetchAll();
    }

    public function getPermissions(int $roleId): array
    {
        $stmt = $this->db->prepare(
            'SELECT q.`id_quyen`, q.`ma_quyen`, q.`ten_quyen`, q.`mo_ta`
             FROM `quyen` q
             INNER JOIN `vai_tro_quyen` vtq ON q.`id_quyen` = vtq.`id_quyen`
             WHERE vtq.`id_vai_tro` = :role_id
             ORDER BY q.`ma_quyen` ASC'
        );
        $stmt->execute([':role_id' => $roleId]);

        return $stmt->fetchAll();
    }

    public function assignPermission(int $roleId, int $permissionId): bool
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO `vai_tro_quyen` (`id_vai_tro`, `id_quyen`)
             VALUES (:role_id, :permission_id)'
        );

        return $stmt->execute([
            ':role_id' => $roleId,
            ':permission_id' => $permissionId
        ]);
    }

    public function revokePermission(int $roleId, int $permissionId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM `vai_tro_quyen`
             WHERE `id_vai_tro` = :role_id AND `id_quyen` = :permission_id'
        );

        return $stmt->execute([
            ':role_id' => $roleId,
            ':permission_id' => $permissionId
        ]);
    }
}
