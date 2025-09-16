<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class SystemSubtype
{
    use Creatable;
    public int $subtypeId;
    public int $typeId;
    public string $name;
    public ?string $description = null;

    public function __construct(int $subtypeId, int $typeId, string $name, ?string $description = null)
    {
        $this->subtypeId = $subtypeId;
        $this->typeId = $typeId;
        $this->name = $name;
        $this->description = $description;
    }
}