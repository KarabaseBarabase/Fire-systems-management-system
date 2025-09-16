<?php
namespace App\Data\Repositories;

use App\Data\Entities\SystemSubtype;
use App\Core\Repository;

class SystemSubtypeRepository extends Repository
{
    protected $table = 'system_subtypes';
    protected $entityClass = SystemSubtype::class;

    public function findByType(int $typeId): array
    {
        return $this->findBy(['type_id' => $typeId]);
    }

    protected function hydrate(array $data): SystemSubtype
    {
        $subtype = SystemSubtype::createEmpty();
        $subtype->subtypeId = (int) $data['subtype_id'];
        $subtype->typeId = (int) $data['type_id'];
        $subtype->name = $data['name'];
        $subtype->description = $data['description'] ?? null;

        return $subtype;
    }

    protected function toArray(object $entity): array
    {
        return [
            'type_id' => $entity->typeId,
            'name' => $entity->name,
            'description' => $entity->description
        ];
    }
}