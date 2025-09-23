<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class ProtectionObject
{
    use Creatable;
    public int $objectId;
    public string $recordUuid;
    public int $branchId;
    public string $name;
    public ?string $shortName = null;
    public int $objectGroupId;
    public int $curatorId;
    public ?string $inventoryNumber = null;
    public ?string $notes = null;
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;

    public function __construct(
        int $objectId,
        string $recordUuid,
        int $branchId,
        string $name,
        int $objectGroupId,
        int $curatorId,
        ?string $shortName = null,
        ?string $inventoryNumber = null,
        ?string $notes = null,
        ?\DateTimeImmutable $updatedAt = null,
        ?int $updatedBy = null
    ) {
        $this->objectId = $objectId;
        $this->recordUuid = $recordUuid;
        $this->branchId = $branchId;
        $this->name = $name;
        $this->shortName = $shortName;
        $this->objectGroupId = $objectGroupId;
        $this->curatorId = $curatorId;
        $this->inventoryNumber = $inventoryNumber;
        $this->notes = $notes;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}