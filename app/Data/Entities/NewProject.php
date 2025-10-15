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

    public ?\DateTimeImmutable $createdAt;

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
        ?int $updatedBy = null,
        ?\DateTimeImmutable $createdAt = null
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
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'project_id' => $this->projectId,
            'record_uuid' => $this->recordUuid,
            'system_id' => $this->systemId,
            'development_method' => $this->developmentMethod,
            'regulation_id' => $this->regulationId,
            'planned_year' => $this->plannedYear,
            'status' => $this->status,
            'design_org_id' => $this->designOrgId,
            'project_code' => $this->projectCode,
            'project_file_link' => $this->projectFileLink,
            'updated_by' => $this->updatedBy,
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),

            // Дополнительные данные из JOIN
            'regulation_code' => $this->regulationCode ?? null,
            'regulation_name' => $this->regulationName ?? null,
            'design_org_name' => $this->designOrgName ?? null,
            'design_org_short_name' => $this->designOrgShortName ?? null,
            'updated_by_name' => $this->updatedByName ?? null,
        ];
    }
}