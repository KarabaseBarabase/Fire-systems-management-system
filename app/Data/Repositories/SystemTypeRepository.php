<?php
namespace App\Data\Repositories;

use App\Data\Entities\SystemType;
use App\Core\Repository;

class SystemTypeRepository extends Repository
{
    protected $table = 'system_types';
    protected $entityClass = SystemType::class;

    protected function hydrate(array $data): SystemType
    {
        $type = SystemType::createEmpty();
        $type->typeId = (int) $data['type_id'];
        $type->name = $data['name'];
        $type->description = $data['description'] ?? null;

        return $type;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name,
            'description' => $entity->description
        ];
    }
}