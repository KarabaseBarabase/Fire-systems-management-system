<?php
namespace App\Data\Repositories;

use App\Data\Entities\Mount;
use App\Core\Repository;

class MountRepository extends Repository
{
    protected $table = 'mounts';
    protected $entityClass = Mount::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByRepair(int $repairId): array
    {
        return $this->findBy(['repair_id' => $repairId]);
    }

    protected function hydrate(array $data): Mount
    {
        $mount = Mount::createEmpty();
        $mount->mountId = (int) $data['mount_id'];
        $mount->recordUuid = $data['record_uuid'];
        $mount->systemId = (int) $data['system_id'];
        $mount->installationOrgId = (int) $data['installation_org_id'];
        $mount->commissionDate = $data['commission_date'];
        $mount->actFileLink = $data['act_file_link'];
        $mount->equipmentListFileLink = $data['equipment_list_file_link'] ?? null;
        $mount->status = $data['status'];
        $mount->updatedAt = $data['updated_at'] ?? null;
        $mount->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;
        $mount->repairId = $data['repair_id'] ? (int) $data['repair_id'] : null;
        $mount->repairWorkType = $data['repair_work_type'] ?? null;
        $mount->repairExecutionMethod = $data['repair_execution_method'] ?? null;

        return $mount;
    }

    // protected function toArray(object $entity): array
    // {
    //     return [
    //         'record_uuid' => $entity->recordUuid,
    //         'system_id' => $entity->systemId,
    //         'installation_org_id' => $entity->installationOrgId,
    //         'commission_date' => $entity->commissionDate,
    //         'act_file_link' => $entity->actFileLink,
    //         'equipment_list_file_link' => $entity->equipmentListFileLink,
    //         'status' => $entity->status,
    //         'repair_id' => $entity->repairId,
    //         'repair_work_type' => $entity->repairWorkType,
    //         'repair_execution_method' => $entity->repairExecutionMethod,
    //         'updated_at' => $entity->updatedAt,
    //         'updated_by' => $entity->updatedBy
    //     ];
    // }
    protected function toArray(object $entity): array
    {
        if (!$entity instanceof Mount) {
            throw new \InvalidArgumentException('Entity must be an instance of Mount');
        }

        $data = [
            'system_id' => $entity->systemId,
            'installation_org_id' => $entity->installationOrgId,
            'commission_date' => $entity->commissionDate->format('Y-m-d'),
            'act_file_link' => $entity->actFileLink,
            'equipment_list_file_link' => $entity->equipmentListFileLink,
            'status' => $entity->status,
            'repair_id' => $entity->repairId,
            'repair_work_type' => $entity->repairWorkType,
            'repair_execution_method' => $entity->repairExecutionMethod
        ];

        // Добавляем record_uuid только если он не пустой
        if (!empty($entity->recordUuid)) {
            $data['record_uuid'] = $entity->recordUuid;
        }

        // Добавляем updated_at только если он установлен
        if ($entity->updatedAt !== null) {
            $data['updated_at'] = $entity->updatedAt->format('Y-m-d H:i:s');
        }

        // Добавляем updated_by только если он установлен
        if ($entity->updatedBy !== null) {
            $data['updated_by'] = $entity->updatedBy;
        }

        return $data;
    }
}