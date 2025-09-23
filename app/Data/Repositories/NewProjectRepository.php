<?php
namespace App\Data\Repositories;

use App\Data\Entities\NewProject;
use App\Core\Repository;
use PDO;
use PDOException;
class NewProjectRepository extends Repository
{
    protected $table = 'new_projects';
    protected $entityClass = NewProject::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findBySystemWithDetails(int $systemId): array
    {
        try {
            error_log("=== DEBUG: Searching for projects with system_id = {$systemId} ===");

            // Сначала простой запрос без JOIN
            $simpleSql = "SELECT * FROM new_projects WHERE system_id = :system_id";
            $simpleStmt = $this->getPdo()->prepare($simpleSql);
            $simpleStmt->execute(['system_id' => $systemId]);
            $simpleResults = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Simple query found: " . count($simpleResults) . " results");
            if (count($simpleResults) > 0) {
                error_log("Simple result: " . json_encode($simpleResults[0]));
            }

            // Теперь запрос с JOIN
            $sql = "SELECT np.*, 
                       org.name as design_org_name,
                       org.short_name as design_org_short_name,
                       r.code as regulation_code,
                       r.name as regulation_name
                FROM new_projects np 
                LEFT JOIN design_organizations org ON np.design_org_id = org.org_id 
                LEFT JOIN regulations r ON np.regulation_id = r.regulation_id 
                WHERE np.system_id = :system_id 
                ORDER BY np.planned_year DESC, np.project_id DESC";

            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute(['system_id' => $systemId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("JOIN query found: " . count($results) . " results");
            if (count($results) > 0) {
                error_log("JOIN result: " . json_encode($results[0]));
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error finding new projects for system {$systemId}: " . $e->getMessage());
            error_log("SQL error info: " . print_r($stmt->errorInfo(), true));
            return [];
        }
    }
    public function debugAllProjects(): array
    {
        try {
            $sql = "SELECT * FROM new_projects";
            $stmt = $this->getPdo()->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("=== DEBUG: All projects in database ===");
            error_log("Total projects: " . count($results));
            foreach ($results as $project) {
                error_log("Project: system_id=" . $project['system_id'] . ", id=" . $project['project_id']);
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error debugging projects: " . $e->getMessage());
            return [];
        }
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByDevelopmentMethod(string $method): array
    {
        return $this->findBy(['development_method' => $method]);
    }

    public function findAll(): array
    {
        try {
            $sql = "SELECT * FROM new_projects LIMIT 5";
            $stmt = $this->getPdo()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding all projects: " . $e->getMessage());
            return [];
        }
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
        $project->updatedAt = $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null;
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
            'updated_at' => $entity->updatedAt?->format('Y-m-d H:i:s'),
            'updated_by' => $entity->updatedBy
        ];
    }
}