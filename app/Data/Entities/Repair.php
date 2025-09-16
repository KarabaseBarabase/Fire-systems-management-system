<?php
namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;

class Repair
{
    use Creatable;
    public int $repairId;
    public string $recordUuid;
    public int $systemId;
    public string $workType;
    public string $executionMethod;
    public int $plannedYear;
    public string $status;
    public ?float $cost;
    public ?int $installationOrgId;
    public ?string $completionDate;
    public ?string $actFileLink;
    public ?string $equipmentListFileLink;
    public ?string $updatedAt;
    public ?int $updatedBy;

    public function __construct(
        int $repairId,
        string $recordUuid,
        int $systemId,
        string $workType,
        string $executionMethod,
        int $plannedYear,
        string $status,
        ?float $cost = null,
        ?int $installationOrgId = null,
        ?string $completionDate = null,
        ?string $actFileLink = null,
        ?string $equipmentListFileLink = null,
        ?string $updatedAt = null,
        ?int $updatedBy = null
    ) {
        $this->repairId = $repairId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->workType = $workType;
        $this->executionMethod = $executionMethod;
        $this->plannedYear = $plannedYear;
        $this->status = $status;
        $this->cost = $cost;
        $this->installationOrgId = $installationOrgId;
        $this->completionDate = $completionDate;
        $this->actFileLink = $actFileLink;
        $this->equipmentListFileLink = $equipmentListFileLink;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}