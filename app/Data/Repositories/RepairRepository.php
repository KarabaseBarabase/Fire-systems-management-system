<?php
namespace App\Data\Repositories;

use App\Data\Entities\Repair;
use App\Core\Repository;

class RepairRepository extends Repository
{
    protected $table = 'repairs';
    protected $entityClass = Repair::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByPlannedYear(int $year): array
    {
        return $this->findBy(['planned_year' => $year]);
    }

    public function findByExecutionMethod(string $method): array
    {
        return $this->findBy(['execution_method' => $method]);
    }

    protected function hydrate(array $data): Repair
    {
        $repair = Repair::createEmpty();
        $repair->repairId = (int) $data['repair_id'];
        $repair->recordUuid = $data['record_uuid'];
        $repair->systemId = (int) $data['system_id'];
        $repair->workType = $data['work_type'];
        $repair->executionMethod = $data['execution_method'];
        $repair->plannedYear = (int) $data['planned_year'];
        $repair->status = $data['status'];
        $repair->cost = $data['cost'] ? (float) $data['cost'] : null;
        $repair->installationOrgId = $data['installation_org_id'] ? (int) $data['installation_org_id'] : null;
        $repair->completionDate = $data['completion_date'] ?? null;
        $repair->actFileLink = $data['act_file_link'] ?? null;
        $repair->equipmentListFileLink = $data['equipment_list_file_link'] ?? null;
        $repair->updatedAt = $data['updated_at'] ?? null;
        $repair->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;

        return $repair;
    }

    protected function toArray(object $entity): array
    {
        return [
            'record_uuid' => $entity->recordUuid,
            'system_id' => $entity->systemId,
            'work_type' => $entity->workType,
            'execution_method' => $entity->executionMethod,
            'planned_year' => $entity->plannedYear,
            'status' => $entity->status,
            'cost' => $entity->cost,
            'installation_org_id' => $entity->installationOrgId,
            'completion_date' => $entity->completionDate,
            'act_file_link' => $entity->actFileLink,
            'equipment_list_file_link' => $entity->equipmentListFileLink,
            'updated_at' => $entity->updatedAt,
            'updated_by' => $entity->updatedBy
        ];
    }

    // public function save(object $entity): object
    // {
    //     if (!$entity instanceof Repair) {
    //         throw new \InvalidArgumentException('Entity must be an instance of Repair');
    //     }

    //     $data = $this->toArray($entity);

    //     if ($entity->repairId) {
    //         // Update existing repair
    //         $this->update($entity->repairId, $data);
    //         return $this->find($entity->repairId);
    //     } else {
    //         // Insert new repair
    //         $id = $this->insert($data);
    //         return $this->find($id);
    //     }
    // }
}