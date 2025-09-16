<?php
namespace App\Data\Repositories;

use App\Data\Entities\SystemMaintenance;
use App\Core\Repository;

class SystemMaintenanceRepository extends Repository
{
    protected $table = 'system_maintenance';
    protected $entityClass = SystemMaintenance::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status]);
    }

    public function findByType(string $type): array
    {
        return $this->findBy(['maintenance_type' => $type]);
    }

    protected function hydrate(array $data): SystemMaintenance
    {
        $maintenance = SystemMaintenance::createEmpty();
        $maintenance->maintenanceId = (int) $data['maintenance_id'];
        $maintenance->recordUuid = $data['record_uuid'];
        $maintenance->systemId = (int) $data['system_id'];
        $maintenance->maintenanceType = $data['maintenance_type'];
        $maintenance->maintenanceDate = $data['maintenance_date'];
        $maintenance->maintenanceBy = $data['maintenance_by'] ?? null;
        $maintenance->testActFileLink = $data['test_act_file_link'] ?? null;
        $maintenance->notes = $data['notes'] ?? null;
        $maintenance->status = $data['status'];
        $maintenance->updatedAt = $data['updated_at'] ?? null;
        $maintenance->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;

        return $maintenance;
    }

    protected function toArray(object $entity): array
    {
        return [
            'record_uuid' => $entity->recordUuid,
            'system_id' => $entity->systemId,
            'maintenance_type' => $entity->maintenanceType,
            'maintenance_date' => $entity->maintenanceDate,
            'maintenance_by' => $entity->maintenanceBy,
            'test_act_file_link' => $entity->testActFileLink,
            'notes' => $entity->notes,
            'status' => $entity->status,
            'updated_at' => $entity->updatedAt,
            'updated_by' => $entity->updatedBy
        ];
    }
}