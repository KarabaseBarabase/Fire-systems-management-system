<?php
namespace App\Data\Repositories;

use App\Data\Entities\Curator;
use App\Core\Repository;

class CuratorRepository extends Repository
{
    protected $table = 'curators';
    protected $entityClass = Curator::class;

    protected function hydrate(array $data): Curator
    {
        $curator = Curator::createEmpty();
        $curator->curatorId = (int) $data['curator_id'];
        $curator->name = $data['name'];

        return $curator;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name
        ];
    }
}