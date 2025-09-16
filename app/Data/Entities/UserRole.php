<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class UserRole
{
    use Creatable;
    public int $userId;
    public int $roleId;

    public function __construct(int $userId, int $roleId)
    {
        $this->userId = $userId;
        $this->roleId = $roleId;
    }
}