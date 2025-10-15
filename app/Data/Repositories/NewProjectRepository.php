<?php
namespace App\Data\Repositories;

use App\Data\Entities\NewProject;
use App\Core\Repository;
use PDO;
use PDOException;
use Illuminate\Support\Facades\Log;
class NewProjectRepository extends Repository
{
    protected $table = 'new_projects';
    protected $entityClass = NewProject::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    // public function findBySystemWithDetails(int $systemId): array
    // {
    //     try {
    //         $sql = "
    //         SELECT 
    //             np.*,
    //             r.code as regulation_code,
    //             r.name as regulation_name,
    //             do.name as design_org_name,
    //             do.short_name as design_org_short_name,
    //             u.full_name as updated_by_name
    //         FROM new_projects np
    //         LEFT JOIN regulations r ON np.regulation_id = r.regulation_id
    //         LEFT JOIN design_organizations do ON np.design_org_id = do.org_id
    //         LEFT JOIN users u ON np.updated_by = u.user_id
    //         WHERE np.system_id = :system_id
    //         ORDER BY np.planned_year DESC, np.created_at DESC
    //     ";

    //         $stmt = $this->getPdo()->prepare($sql);
    //         $stmt->bindValue(':system_id', (int) $systemId, PDO::PARAM_INT);
    //         $stmt->execute();
    //         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         return array_map([$this, 'hydrateWithDetails'], $results);
    //     } catch (PDOException $e) {
    //         Log::channel('debug')->debug("Error finding projects with details: " . $e->getMessage());
    //         return [];
    //     }
    // }

    public function findBySystemWithDetails(int $systemId): array
    {
        try {
            $pdo = $this->getPdo();

            $sql = "
            SELECT 
                np.*,
                r.code AS regulation_code,
                r.name AS regulation_name,
                dorg.name AS design_org_name,
                dorg.short_name AS design_org_short_name,
                u.full_name AS updated_by_name
            FROM public.new_projects np
            LEFT JOIN public.regulations r ON np.regulation_id = r.regulation_id
            LEFT JOIN public.design_organizations dorg ON np.design_org_id = dorg.org_id
            LEFT JOIN public.users u ON np.updated_by = u.user_id
            WHERE np.system_id = :system_id
            ORDER BY np.planned_year DESC, np.created_at DESC
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->bindValue(':system_id', $systemId, PDO::PARAM_INT);

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Log::channel('debug')->debug(
                "[findBySystemWithDetails] Raw results from DB:",
                array_map(function ($row) {
                    return [
                        'project_id' => $row['project_id'],
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at']
                    ];
                }, $results)
            );

            return array_map([$this, 'hydrateWithDetails'], $results);

        } catch (PDOException $e) {
            Log::channel('debug')->error("[findBySystemWithDetails] PDO Error: " . $e->getMessage());
            return [];
        } catch (\Throwable $e) {
            Log::channel('debug')->error("[findBySystemWithDetails] General Error: " . $e->getMessage());
            return [];
        }
    }

    public function debugAllProjects(): array
    {
        try {
            $sql = "SELECT * FROM new_projects";
            $stmt = $this->getPdo()->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            Log::channel('debug')->debug("=== DEBUG: All projects in database ===");
            Log::channel('debug')->debug("Total projects: " . count($results));
            foreach ($results as $project) {
                Log::channel('debug')->debug("Project: system_id=" . $project['system_id'] . ", id=" . $project['project_id']);
            }

            return $results;
        } catch (PDOException $e) {
            Log::channel('debug')->debug("Error debugging projects: " . $e->getMessage());
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
            Log::channel('debug')->debug("Error finding all projects: " . $e->getMessage());
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

    protected function hydrateWithDetails(array $data): NewProject
    {
        $project = new NewProject(
            projectId: (int) $data['project_id'],
            recordUuid: $data['record_uuid'],
            systemId: (int) $data['system_id'],
            developmentMethod: $data['development_method'],
            regulationId: (int) $data['regulation_id'],
            plannedYear: (int) $data['planned_year'],
            status: $data['status'],
            designOrgId: $data['design_org_id'] ? (int) $data['design_org_id'] : null,
            projectCode: $data['project_code'] ?? null,
            projectFileLink: $data['project_file_link'] ?? null,
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null,
            updatedBy: $data['updated_by'] ? (int) $data['updated_by'] : null,
            createdAt: $data['created_at'] ? new \DateTimeImmutable($data['created_at']) : null
        );

        // Добавляем связанные данные как динамические свойства
        $project->regulationCode = $data['regulation_code'] ?? null;
        $project->regulationName = $data['regulation_name'] ?? null;
        $project->designOrgName = $data['design_org_name'] ?? null;
        $project->designOrgShortName = $data['design_org_short_name'] ?? null;
        $project->updatedByName = $data['updated_by_name'] ?? null;

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