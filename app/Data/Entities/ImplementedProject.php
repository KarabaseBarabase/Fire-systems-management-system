<?php

namespace App\Data\Entities;

use App\Data\Entities\Traits\Creatable;
class ImplementedProject
{
    use Creatable;
    public int $projectId;
    public string $recordUuid;
    public int $systemId;
    public string $projectCode;
    public int $developmentYear;
    public int $designOrgId;
    public int $regulationId;
    public ?string $projectFileLink = null;
    public \DateTimeImmutable $updatedAt;
    public ?int $updatedBy = null;

    public function __construct(
        int $projectId,
        string $recordUuid,
        int $systemId,
        string $projectCode,
        int $developmentYear,
        int $designOrgId,
        int $regulationId,
        \DateTimeImmutable $updatedAt,
        ?string $projectFileLink = null,
        ?int $updatedBy = null
    ) {
        $this->projectId = $projectId;
        $this->recordUuid = $recordUuid;
        $this->systemId = $systemId;
        $this->projectCode = $projectCode;
        $this->developmentYear = $developmentYear;
        $this->designOrgId = $designOrgId;
        $this->regulationId = $regulationId;
        $this->projectFileLink = $projectFileLink;
        $this->updatedAt = $updatedAt;
        $this->updatedBy = $updatedBy;
    }
}