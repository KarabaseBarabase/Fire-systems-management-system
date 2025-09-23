<?php
namespace App\Data\Repositories;

use App\Data\Entities\ProtectionObject;
use App\Core\Repository;
use DateTimeImmutable;
class ProtectionObjectRepository extends Repository
{
    protected $table = 'protection_objects';
    protected $entityClass = ProtectionObject::class;

    public function findByBranch(int $branchId): array
    {
        return $this->findBy(['branch_id' => $branchId]);
    }

    public function findByCurator(int $curatorId): array
    {
        return $this->findBy(['curator_id' => $curatorId]);
    }

    protected function hydrate(array $data): ProtectionObject
    {
        $object = ProtectionObject::createEmpty();
        $object->objectId = (int) $data['object_id'];
        $object->recordUuid = $data['record_uuid'];
        $object->branchId = (int) $data['branch_id'];
        $object->name = $data['name'];
        $object->shortName = $data['short_name'] ?? null;
        $object->objectGroupId = (int) $data['object_group_id'];
        $object->curatorId = (int) $data['curator_id'];
        $object->inventoryNumber = $data['inventory_number'] ?? null;
        $object->notes = $data['notes'] ?? null;

        // Преобразуем строку в DateTimeImmutable
        $object->updatedAt = $data['updated_at'] ?
            new DateTimeImmutable($data['updated_at']) : null;

        $object->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;

        return $object;
    }

    protected function toArray(object $entity): array
    {
        return [
            'object_id' => $entity->objectId,
            'record_uuid' => $entity->recordUuid,
            'branch_id' => $entity->branchId,
            'name' => $entity->name,
            'short_name' => $entity->shortName,
            'object_group_id' => $entity->objectGroupId,
            'curator_id' => $entity->curatorId,
            'inventory_number' => $entity->inventoryNumber,
            'notes' => $entity->notes,
            'updated_at' => $entity->updatedAt?->format('Y-m-d H:i:s'),
            'updated_by' => $entity->updatedBy
        ];
    }
}