<?php
namespace App\Services;

use App\Data\Repositories\UserRepository;
use App\Data\Repositories\RoleRepository;
use App\Core\AuthInterface;
use App\Data\Entities\User;

class AuthService
{
    private $userRepository;
    private $roleRepository;
    private $auth;

    public function __construct(
        UserRepository $userRepo,
        RoleRepository $roleRepo,
        AuthInterface $auth
    ) {
        $this->userRepository = $userRepo;
        $this->roleRepository = $roleRepo;
        $this->auth = $auth;
    }

    public function authenticate(string $username, string $password): ?User
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user && password_verify($password, $user->passwordHash)) {
            $this->auth->login($user);
            $this->updateLastActive($user->userId);
            return $user;
        }

        return null;
    }

    public function logout(): bool
    {
        return $this->auth->logout();
    }

    public function getCurrentUser(): ?User
    {
        $user = $this->auth->user();

        // Если возвращается модель - преобразуем в сущность через репозиторий
        if ($user instanceof \App\Models\User) {
            return $this->userRepository->modelToEntity($user);
        }

        // Если уже сущность или null - возвращаем как есть
        return $user;
    }

    public function hasPermission(string $permission, $resource = null): bool
    {
        $user = $this->getCurrentUser();
        if (!$user)
            return false;

        // Здесь будет логика проверки прав через check_approval_permission
        return true;
    }

    private function updateLastActive(int $userId): void
    {
        $this->userRepository->updateLastActive($userId);
    }
}