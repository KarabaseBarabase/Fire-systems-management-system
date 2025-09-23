<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class NewProject
{
    use Creatable;
    public int $projectId;
    public string $recordUuid;
    public int $systemId;
    public string $developmentMethod;
    public int $regulationId;
    public int $plannedYear;
    public string $status;
    public ?int $designOrgId = null;
    public ?string $projectCode = null;
    public ?string $projectFileLink = null;
    public ?\DateTimeImmutable $updatedAt = null;
    public ?int $updatedBy = null;

    public function __construct(
        int $projectId,
        string $recordUuid,
        int $systemId,
        string $developmentMethod,
        int $regulationId,
        int $plannedYear,
        string $status,
        ?\DateTimeImmutable $updatedAt = null,
        ?int $designOrgId = null,
        ?string $projectCode = null,
        ?string $projectFileLink = null,
        ?int $updatedBy = null
    ) {
        $this->projectId = $projectId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->developmentMethod = $developmentMethod;
        $this->regulationId = $regulationId;
        $this->plannedYear = $plannedYear;
        $this->status = $status;
        $this->designOrgId = $designOrgId;
        $this->projectCode = $projectCode;
        $this->projectFileLink = $projectFileLink;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}