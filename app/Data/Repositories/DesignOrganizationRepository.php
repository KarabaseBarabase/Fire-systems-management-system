<?php
namespace App\Data\Repositories;

use App\Data\Entities\DesignOrganization;
use App\Core\Repository;

class DesignOrganizationRepository extends Repository
{
    protected $table = 'design_organizations';
    protected $entityClass = DesignOrganization::class;

    protected function hydrate(array $data): DesignOrganization
    {
        $org = DesignOrganization::createEmpty();
        $org->orgId = (int) $data['org_id'];
        $org->name = $data['name'];
        $org->shortName = $data['short_name'] ?? null;

        return $org;
    }

    protected function toArray(object $entity): array
    {
        return [
            'name' => $entity->name,
            'short_name' => $entity->shortName
        ];
    }
}