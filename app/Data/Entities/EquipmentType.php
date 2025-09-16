<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class EquipmentType
{
    use Creatable;
    public int $typeId;
    public string $name;

    public function __construct(int $typeId, string $name)
    {
        $this->typeId = $typeId;
        $this->name = $name;
    }
}