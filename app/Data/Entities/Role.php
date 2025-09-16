<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Role
{
    use Creatable;
    public int $roleId;
    public string $name;
    public ?string $description = null;

    public function __construct(int $roleId, string $name, ?string $description = null)
    {
        $this->roleId = $roleId;
        $this->name = $name;
        $this->description = $description;
    }
}