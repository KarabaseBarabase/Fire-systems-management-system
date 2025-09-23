<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class FireSystem
{
    use Creatable;
    public int $systemId;
    public string $recordUuid;
    public ?int $objectId = null;
    public ?int $subtypeId = null;
    public bool $isPartOfObject = false;
    public ?string $systemInventoryNumber = null;
    public ?string $name = null;
    public ?string $manualFileLink = null;
    public ?string $maintenanceScheduleFileLink = null;
    public ?string $testProgramFileLink = null;
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;

    public function __construct(
        int $systemId,
        string $recordUuid,
        bool $isPartOfObject = false,
        ?\DateTimeImmutable $updatedAt = null,
        ?int $objectId = null,
        ?int $subtypeId = null,
        ?string $systemInventoryNumber = null,
        ?string $name = null,
        ?string $manualFileLink = null,
        ?string $maintenanceScheduleFileLink = null,
        ?string $testProgramFileLink = null,
        ?int $updatedBy = null
    ) {
        $this->systemId = $systemId;
        $this->recordUuid = $recordUuid;
        $this->objectId = $objectId;
        $this->subtypeId = $subtypeId;
        $this->isPartOfObject = $isPartOfObject;
        $this->systemInventoryNumber = $systemInventoryNumber;
        $this->name = $name;
        $this->manualFileLink = $manualFileLink;
        $this->maintenanceScheduleFileLink = $maintenanceScheduleFileLink;
        $this->testProgramFileLink = $testProgramFileLink;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}