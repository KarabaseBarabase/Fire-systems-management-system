<?php
namespace App\Data\Repositories;

use App\Data\Entities\UserRole;
use App\Core\Repository;
use PDO;
use PDOException;

class UserRoleRepository extends Repository
{
    protected $table = 'user_roles';
    protected $entityClass = UserRole::class;

    public function findByUser(int $userId): array
    {
        return $this->findBy(['user_id' => $userId]);
    }

    public function findByRole(int $roleId): array
    {
        return $this->findBy(['role_id' => $roleId]);
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        try {
            $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)
                    ON CONFLICT (user_id, role_id) DO NOTHING";
            $stmt = $this->getPdo()->prepare($sql);
            return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
        } catch (PDOException $e) {
            error_log("Error assigning role: " . $e->getMessage());
            return false;
        }
    }

    public function removeRole(int $userId, int $roleId): bool
    {
        try {
            $stmt = $this->getPdo()->prepare(
                "DELETE FROM user_roles WHERE user_id = :user_id AND role_id = :role_id"
            );
            return $stmt->execute(['user_id' => $userId, 'role_id' => $roleId]);
        } catch (PDOException $e) {
            error_log("Error removing role: " . $e->getMessage());
            return false;
        }
    }

    protected function hydrate(array $data): UserRole
    {
        $userRole = UserRole::createEmpty();
        $userRole->userId = (int) $data['user_id'];
        $userRole->roleId = (int) $data['role_id'];

        return $userRole;
    }

    protected function toArray(object $entity): array
    {
        return [
            'user_id' => $entity->userId,
            'role_id' => $entity->roleId
        ];
    }
}