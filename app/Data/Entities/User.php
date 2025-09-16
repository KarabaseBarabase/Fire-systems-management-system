<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class User
{
    use Creatable;
    public int $userId;
    public string $username;
    public string $passwordHash;
    public string $fullName;
    public ?string $email = null;
    public ?int $branchId = null;
    public string $position;
    public bool $isActive = true;
    public ?\DateTimeImmutable $lastActiveAt = null;

    public function __construct(
        int $userId,
        string $username,
        string $passwordHash,
        string $fullName,
        string $position,
        ?string $email = null,
        ?int $branchId = null,
        bool $isActive = true,
        ?\DateTimeImmutable $lastActiveAt = null
    ) {
        $this->userId = $userId;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->branchId = $branchId;
        $this->position = $position;
        $this->isActive = $isActive;
        $this->lastActiveAt = $lastActiveAt;
    }
}