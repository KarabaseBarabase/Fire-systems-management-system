<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Regulation
{
    use Creatable;
    public int $regulationId;
    public string $code;
    public string $name;

    public function __construct(int $regulationId, string $code, string $name)
    {
        $this->regulationId = $regulationId;
        $this->code = $code;
        $this->name = $name;
    }
}