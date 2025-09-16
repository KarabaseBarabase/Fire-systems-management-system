<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class SystemType
{
    use Creatable;
    public int $typeId;
    public string $name;
    public ?string $description = null;

    public function __construct(int $typeId, string $name, ?string $description = null)
    {
        $this->typeId = $typeId;
        $this->name = $name;
        $this->description = $description;
    }
}