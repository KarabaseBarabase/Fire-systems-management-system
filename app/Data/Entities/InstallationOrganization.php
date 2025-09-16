<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class InstallationOrganization
{
    use Creatable;
    public int $orgId;
    public string $name;
    public ?string $shortName = null;

    public function __construct(int $orgId, string $name, ?string $shortName = null)
    {
        $this->orgId = $orgId;
        $this->name = $name;
        $this->shortName = $shortName;
    }
}