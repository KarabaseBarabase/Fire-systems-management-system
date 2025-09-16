<?php
namespace App\Data\Repositories;

use App\Data\Entities\NewProject;
use App\Core\Repository;

class NewProjectRepository extends Repository
{
    protected $table = 'new_projects';
    protected $entityClass = NewProject::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByDevelopmentMethod(string $method): array
    {
        return $this->findBy(['development_method' => $method]);
    }

    protected function hydrate(array $data): NewProject
    {
        $project = NewProject::createEmpty();
        $project->projectId = (int) $data['project_id'];
        $project->recordUuid = $data['record_uuid'];
        $project->systemId = (int) $data['system_id'];
        $project->developmentMethod = $data['development_method'];
        $project->regulationId = (int) $data['regulation_id'];
        $project->plannedYear = (int) $data['planned_year'];
        $project->status = $data['status'];
        $project->designOrgId = $data['design_org_id'] ? (int) $data['design_org_id'] : null;
        $project->projectCode = $data['project_code'] ?? null;
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
            'development_method' => $entity->developmentMethod,
            'regulation_id' => $entity->regulationId,
            'planned_year' => $entity->plannedYear,
            'status' => $entity->status,
            'design_org_id' => $entity->designOrgId,
            'project_code' => $entity->projectCode,
            'project_file_link' => $entity->projectFileLink,
            'updated_at' => $entity->updatedAt,
            'updated_by' => $entity->updatedBy
        ];
    }
}