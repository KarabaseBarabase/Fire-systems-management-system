<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Branch
{
    use Creatable;
    public int $branchId;
    public string $name;
    public ?string $shortName = null;

    public function __construct(int $branchId, string $name, ?string $shortName = null)
    {
        $this->branchId = $branchId;
        $this->name = $name;
        $this->shortName = $shortName;
    }
}