<?php
namespace App\Data\Repositories;

use App\Data\Entities\ImplementedProject;
use App\Core\Repository;

class ImplementedProjectRepository extends Repository
{
    protected $table = 'implemented_projects';
    protected $entityClass = ImplementedProject::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByDesignOrg(int $orgId): array
    {
        return $this->findBy(['design_org_id' => $orgId]);
    }

    public function findByProjectCode(string $projectCode): ?ImplementedProject
    {
        $results = $this->findBy(['project_code' => $projectCode]);
        return !empty($results) ? $results[0] : null;
    }

    protected function hydrate(array $data): ImplementedProject
    {
        $project = ImplementedProject::createEmpty();
        $project->projectId = (int) $data['project_id'];
        $project->recordUuid = $data['record_uuid'];
        $project->systemId = (int) $data['system_id'];
        $project->projectCode = $data['project_code'];
        $project->developmentYear = (int) $data['development_year'];
        $project->designOrgId = (int) $data['design_org_id'];
        $project->regulationId = (int) $data['regulation_id'];
        $project->projectFileLink = $data['project_file_link'] ?? null;
        $project->updatedAt = $data['updated_at'] ?? null;
        $project->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;

        return $project;
    }

    protected function toArray(object $entity): array
    {
        return [
            'record_uuid' => $entity->recordUuid,
            'system_id' => $entity->systemId,
            'project_code' => $entity->projectCode,
            'development_year' => $entity->developmentYear,
            'design_org_id' => $entity->designOrgId,
            'regulation_id' => $entity->regulationId,
            'project_file_link' => $entity->projectFileLink,
            'updated_at' => $entity->updatedAt,
            'updated_by' => $entity->updatedBy
        ];
    }
}