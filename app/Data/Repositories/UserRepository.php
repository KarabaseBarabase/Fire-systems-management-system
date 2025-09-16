<?php
namespace App\Data\Repositories;

use App\Data\Entities\User;
use App\Core\Repository;
use PDO;
use PDOException;

class UserRepository extends Repository
{
    protected $table = 'users';
    protected $entityClass = User::class;

    public function findByUsername(string $username): ?User
    {
        try {
            $stmt = $this->getPdo()->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? $this->hydrate($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding user by username: " . $e->getMessage());
            return null;
        }
    }

    public function findByBranch(int $branchId): array
    {
        return $this->findBy(['branch_id' => $branchId]);
    }

    public function updateLastActive(int $userId): bool
    {
        try {
            $sql = "UPDATE users SET last_active_at = CURRENT_TIMESTAMP WHERE user_id = :user_id";
            $stmt = $this->getPdo()->prepare($sql);
            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            error_log("Error updating last active time: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserRoles(int $userId, array $roleIds): bool
    {
        try {
            $this->getPdo()->beginTransaction();

            // Удаляем старые роли
            $stmt = $this->getPdo()->prepare("DELETE FROM user_roles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            // Добавляем новые роли
            $stmt = $this->getPdo()->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");

            foreach ($roleIds as $roleId) {
                $stmt->execute([
                    'user_id' => $userId,
                    'role_id' => $roleId
                ]);
            }

            return $this->getPdo()->commit();
        } catch (PDOException $e) {
            $this->getPdo()->rollBack();
            error_log("Error updating user roles: " . $e->getMessage());
            return false;
        }
    }

    public function getUserRoles(int $userId): array
    {
        try {
            $sql = "SELECT r.* FROM roles r 
                    JOIN user_roles ur ON r.role_id = ur.role_id 
                    WHERE ur.user_id = :user_id";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user roles: " . $e->getMessage());
            return [];
        }
    }

    protected function hydrate(array $data): User
    {
        $user = User::createEmpty();
        $user->userId = (int) $data['user_id'];
        $user->username = $data['username'];
        $user->passwordHash = $data['password_hash'];
        $user->fullName = $data['full_name'];
        $user->email = $data['email'] ?? null;
        $user->branchId = $data['branch_id'] ? (int) $data['branch_id'] : null;
        $user->position = $data['position'];
        $user->isActive = (bool) $data['is_active'];
        $user->lastActiveAt = $data['last_active_at'] ?? null;

        return $user;
    }

    protected function toArray(object $entity): array
    {
        return [
            'user_id' => $entity->userId,
            'username' => $entity->username,
            'password_hash' => $entity->passwordHash,
            'full_name' => $entity->fullName,
            'email' => $entity->email,
            'branch_id' => $entity->branchId,
            'position' => $entity->position,
            'is_active' => $entity->isActive,
            'last_active_at' => $entity->lastActiveAt
        ];
    }

    public function modelToEntity(\App\Models\User $model): User
    {
        $entity = User::createEmpty();

        $entity->userId = $model->user_id;
        $entity->username = $model->username;
        $entity->passwordHash = $model->password_hash;
        $entity->fullName = $model->full_name;
        $entity->email = $model->email;
        $entity->branchId = $model->branch_id;
        $entity->position = $model->position;
        $entity->isActive = (bool) $model->is_active;

        if ($model->last_active_at) {
            $entity->lastActiveAt = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $model->last_active_at
            );
        }

        return $entity;
    }
}