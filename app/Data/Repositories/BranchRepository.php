<?php
namespace App\Data\Repositories;

use App\Data\Entities\Branch;
use App\Core\Repository;

class BranchRepository extends Repository
{
    protected $table = 'branches';
    protected $entityClass = Branch::class;

    protected function hydrate(array $data): Branch
    {
        $branch = Branch::createEmpty();
        $branch->branchId = (int) $data['branch_id'];
        $branch->name = $data['name'];
        $branch->shortName = $data['short_name'] ?? null;

        return $branch;
    }

    protected function toArray(object $entity): array
    {
        return [
            'branch_id' => $entity->branchId,
            'name' => $entity->name,
            'short_name' => $entity->shortName
        ];
    }
}