<?php
namespace App\Data\Repositories;

use App\Data\Entities\FireSystem;
use App\Core\Repository;
use App\Core\Database;
use PDO;
use PDOException;

class FireSystemRepository extends Repository
{
    protected $table = 'fire_systems';
    protected $entityClass = FireSystem::class;
    protected $primaryKey = 'system_id';

    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->primaryKey = 'system_id';
    }

    public function findByObject(int $objectId): array
    {
        return $this->findBy(['object_id' => $objectId]);
    }

    public function findBySubtype(int $subtypeId): array
    {
        return $this->findBy(['subtype_id' => $subtypeId]);
    }

    public function findByInventoryNumber(string $inventoryNumber): ?FireSystem
    {
        $results = $this->findBy(['system_inventory_number' => $inventoryNumber]);
        return !empty($results) ? $results[0] : null;
    }



    protected function hydrate(array $data): FireSystem
    {
        return new FireSystem(
            (int) $data['system_id'],
            $data['record_uuid'],
            (bool) $data['is_part_of_object'],
            $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null,
            $data['object_id'] ? (int) $data['object_id'] : null,
            $data['subtype_id'] ? (int) $data['subtype_id'] : null,
            $data['system_inventory_number'] ?? null,
            $data['name'] ?? null,
            $data['manual_file_link'] ?? null,
            $data['maintenance_schedule_file_link'] ?? null,
            $data['test_program_file_link'] ?? null,
            $data['updated_by'] ? (int) $data['updated_by'] : null
        );
    }

    protected function toArray(object $entity): array
    {
        $data = [
            'object_id' => $entity->objectId,
            'subtype_id' => $entity->subtypeId,
            'is_part_of_object' => $entity->isPartOfObject,
            'system_inventory_number' => $entity->systemInventoryNumber,
            'name' => $entity->name,
            'manual_file_link' => $entity->manualFileLink,
            'maintenance_schedule_file_link' => $entity->maintenanceScheduleFileLink,
            'test_program_file_link' => $entity->testProgramFileLink,
            // Исправленная строка - проверяем на null
            'updated_at' => $entity->updatedAt ? $entity->updatedAt->format('Y-m-d H:i:s') : null,
            'updated_by' => $entity->updatedBy
        ];

        // Добавляем record_uuid только если он не пустой
        if (!empty($entity->recordUuid)) {
            $data['record_uuid'] = $entity->recordUuid;
        }

        // Добавляем system_id только для существующих записей
        if ($entity->systemId > 0) {
            $data['system_id'] = $entity->systemId;
        }

        return $data;
    }
}

