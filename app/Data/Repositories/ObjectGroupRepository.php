<?php
namespace App\Data\Repositories;

use App\Data\Entities\ObjectGroup;
use App\Core\Repository;

class ObjectGroupRepository extends Repository
{
    protected $table = 'object_groups';
    protected $entityClass = ObjectGroup::class;

    protected function hydrate(array $data): ObjectGroup
    {
        $group = ObjectGroup::createEmpty();
        $group->groupId = (int) $data['group_id'];
        $group->name = $data['name'];
        $group->description = $data['description'] ?? null;

        return $group;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name,
            'description' => $entity->description
        ];
    }
}