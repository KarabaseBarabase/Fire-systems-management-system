<?php
namespace App\Data\Repositories;

use App\Data\Entities\Role;
use App\Core\Repository;
use PDO;
use PDOException;

class RoleRepository extends Repository
{
    protected $table = 'roles';
    protected $entityClass = Role::class;

    public function findByUser(int $userId): array
    {
        try {
            $sql = "SELECT r.* FROM roles r 
                    JOIN user_roles ur ON r.role_id = ur.role_id 
                    WHERE ur.user_id = :user_id";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'hydrate'], $results);
        } catch (PDOException $e) {
            error_log("Error finding roles by user: " . $e->getMessage());
            return [];
        }
    }

    public function findByName(string $name): ?Role
    {
        $results = $this->findBy(['name' => $name]);
        return !empty($results) ? $results[0] : null;
    }

    protected function hydrate(array $data): Role
    {
        $role = Role::createEmpty();
        $role->roleId = (int) $data['role_id'];
        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;

        return $role;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name,
            'description' => $entity->description
        ];
    }
}