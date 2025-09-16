<?php
namespace App\Services;

use App\Data\Repositories\NewProjectRepository;
use App\Data\Repositories\ImplementedProjectRepository;
use App\Data\Repositories\FireSystemRepository;
use App\Data\Entities\NewProject;
use App\Data\Entities\ImplementedProject;

class ProjectService
{
    private $newProjectRepo;
    private $implementedProjectRepo;
    private $fireSystemRepo;

    public function __construct(
        NewProjectRepository $newProjectRepo,
        ImplementedProjectRepository $implementedProjectRepo,
        FireSystemRepository $fireSystemRepo
    ) {
        $this->newProjectRepo = $newProjectRepo;
        $this->implementedProjectRepo = $implementedProjectRepo;
        $this->fireSystemRepo = $fireSystemRepo;
    }

    /* Создание нового проекта */
    public function createNewProject(array $data): NewProject
    {
        $this->validateNewProjectData($data);

        $project = new NewProject(
            0,
            '',
            $data['system_id'],
            $data['development_method'],
            $data['regulation_id'],
            $data['planned_year'],
            $data['status'] ?? 'заявлен',
            $data['design_org_id'] ?? null,
            $data['project_code'] ?? null,
            $data['project_file_link'] ?? null,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            null
        );

        return $this->newProjectRepo->save($project);
    }

    /* Создание реализованного проекта */
    public function createImplementedProject(array $data): ImplementedProject
    {
        $this->validateImplementedProjectData($data);

        $project = new ImplementedProject(
            0,
            '',
            $data['system_id'],
            $data['project_code'],
            $data['development_year'],
            $data['design_org_id'],
            $data['regulation_id'],
            $data['project_file_link'] ?? null,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            null
        );

        return $this->implementedProjectRepo->save($project);
    }

    /* Обновление статуса проекта */
    public function updateProjectStatus(string $uuid, string $status, string $projectType = 'new'): object
    {
        if ($projectType === 'new') {
            $project = $this->newProjectRepo->findByUuid($uuid);
            $repo = $this->newProjectRepo;
        } else {
            $project = $this->implementedProjectRepo->findByUuid($uuid);
            $repo = $this->implementedProjectRepo;
        }

        if (!$project) {
            throw new \Exception('Проект не найден');
        }

        $project->status = $status;
        return $repo->save($project);
    }

    /* Получение проектов по системе */
    public function getProjectsBySystem(int $systemId): array
    {
        return [
            'new_projects' => $this->newProjectRepo->findBySystem($systemId),
            'implemented_projects' => $this->implementedProjectRepo->findBySystem($systemId)
        ];
    }

    private function validateNewProjectData(array $data): void
    {
        $required = ['system_id', 'development_method', 'regulation_id', 'planned_year'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
            }
        }
    }

    private function validateImplementedProjectData(array $data): void
    {
        $required = ['system_id', 'project_code', 'development_year', 'design_org_id', 'regulation_id'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
            }
        }
    }
}