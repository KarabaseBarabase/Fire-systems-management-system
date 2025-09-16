<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Curator
{
    use Creatable;
    public int $curatorId;
    public string $name;

    public function __construct(int $curatorId, string $name)
    {
        $this->curatorId = $curatorId;
        $this->name = $name;
    }
}