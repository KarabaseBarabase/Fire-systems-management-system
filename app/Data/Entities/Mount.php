<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class Mount
{
    use Creatable;
    public int $mountId;
    public string $recordUuid;
    public int $systemId;
    public int $installationOrgId;
    public ?\DateTimeImmutable $commissionDate = null;
    public string $actFileLink;
    public ?string $equipmentListFileLink = null;
    public string $status = 'ожидает проверки';
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;
    public ?int $repairId = null;
    public ?string $repairWorkType = null;
    public ?string $repairExecutionMethod = null;

    public function __construct(
        int $mountId,
        string $recordUuid,
        int $systemId,
        int $installationOrgId,
        ?\DateTimeImmutable $commissionDate = null,
        string $actFileLink,
        ?\DateTimeImmutable $updatedAt = null,
        ?string $equipmentListFileLink = null,
        string $status = 'ожидает проверки',
        ?int $updatedBy = null,
        ?int $repairId = null,
        ?string $repairWorkType = null,
        ?string $repairExecutionMethod = null
    ) {
        $this->mountId = $mountId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->installationOrgId = $installationOrgId;
        $this->commissionDate = $commissionDate;
        $this->actFileLink = $actFileLink;
        $this->equipmentListFileLink = $equipmentListFileLink;
        $this->status = $status;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
        $this->repairId = $repairId;
        $this->repairWorkType = $repairWorkType;
        $this->repairExecutionMethod = $repairExecutionMethod;
    }
}