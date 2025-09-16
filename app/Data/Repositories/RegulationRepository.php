<?php
namespace App\Data\Repositories;

use App\Data\Entities\Regulation;
use App\Core\Repository;

class RegulationRepository extends Repository
{
    protected $table = 'regulations';
    protected $entityClass = Regulation::class;

    public function findByCode(string $code): ?Regulation
    {
        $results = $this->findBy(['code' => $code]);
        return !empty($results) ? $results[0] : null;
    }

    protected function hydrate(array $data): Regulation
    {
        $regulation = Regulation::createEmpty();
        $regulation->regulationId = (int) $data['regulation_id'];
        $regulation->code = $data['code'];
        $regulation->name = $data['name'];

        return $regulation;
    }

    protected function toArray(object $entity): array
    {
        return [
            'code' => $entity->code,
            'name' => $entity->name
        ];
    }
}