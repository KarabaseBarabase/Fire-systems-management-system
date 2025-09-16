<?php
namespace App\Data\Repositories;

use App\Data\Entities\EquipmentType;
use App\Core\Repository;

class EquipmentTypeRepository extends Repository
{
    protected $table = 'equipment_types';
    protected $entityClass = EquipmentType::class;

    protected function hydrate(array $data): EquipmentType
    {
        $type = EquipmentType::createEmpty();
        $type->typeId = (int) $data['type_id'];
        $type->name = $data['name'];

        return $type;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name
        ];
    }
}