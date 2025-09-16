<?php
namespace App\Data\Repositories;

use App\Data\Entities\InstallationOrganization;
use App\Core\Repository;

class InstallationOrganizationRepository extends Repository
{
    protected $table = 'installation_organizations';
    protected $entityClass = InstallationOrganization::class;

    protected function hydrate(array $data): InstallationOrganization
    {
        $org = InstallationOrganization::createEmpty();
        ;
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