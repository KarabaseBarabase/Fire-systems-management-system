<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class ObjectGroup
{
    use Creatable;
    public int $groupId;
    public string $name;
    public ?string $description = null;

    public function __construct(int $groupId, string $name, ?string $description = null)
    {
        $this->groupId = $groupId;
        $this->name = $name;
        $this->description = $description;
    }
}