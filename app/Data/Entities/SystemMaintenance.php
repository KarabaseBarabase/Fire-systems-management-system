<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class SystemMaintenance
{
    use Creatable;
    public int $maintenanceId;
    public string $recordUuid;
    public int $systemId;
    public string $maintenanceType;
    public ?\DateTimeImmutable $maintenanceDate = null;
    public ?string $maintenanceBy = null;
    public ?string $testActFileLink = null;
    public ?string $notes = null;
    public string $status = 'ожидает проверки';
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;

    public function __construct(
        int $maintenanceId,
        string $recordUuid,
        int $systemId,
        string $maintenanceType,
        ?\DateTimeImmutable $maintenanceDate = null,
        ?\DateTimeImmutable $updatedAt = null,
        ?string $maintenanceBy = null,
        ?string $testActFileLink = null,
        ?string $notes = null,
        string $status = 'ожидает проверки',
        ?int $updatedBy = null
    ) {
        $this->maintenanceId = $maintenanceId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->maintenanceType = $maintenanceType;
        $this->maintenanceDate = $maintenanceDate;
        $this->maintenanceBy = $maintenanceBy;
        $this->testActFileLink = $testActFileLink;
        $this->notes = $notes;
        $this->status = $status;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}