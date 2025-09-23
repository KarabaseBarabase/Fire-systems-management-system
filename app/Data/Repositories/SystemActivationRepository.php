<?php
namespace App\Data\Repositories;

use App\Data\Entities\SystemActivation;
use App\Core\Repository;
use PDO;
use PDOException;

class SystemActivationRepository extends Repository
{
    protected $table = 'system_activations';
    protected $entityClass = SystemActivation::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByDateRange(\DateTime $start, \DateTime $end): array
    {
        try {
            $stmt = $this->getPdo()->prepare(
                "SELECT * FROM system_activations 
                 WHERE activation_date BETWEEN :start AND :end 
                 ORDER BY activation_date DESC"
            );
            $stmt->execute([
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $end->format('Y-m-d H:i:s')
            ]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'hydrate'], $results);
        } catch (PDOException $e) {
            error_log("Error finding activations by date range: " . $e->getMessage());
            return [];
        }
    }

    protected function hydrate(array $data): SystemActivation
    {
        $activation = SystemActivation::createEmpty();
        $activation->systemActivationId = (int) $data['system_activation_id'];
        $activation->recordUuid = $data['record_uuid'];
        $activation->systemId = (int) $data['system_id'];
        $activation->location = $data['location'] ?? null;

        // Преобразуем строку в DateTimeImmutable
        $activation->activationDate = $data['activation_date'] ?
            new \DateTimeImmutable($data['activation_date']) : null;

        $activation->reportedBy = $data['reported_by'] ?? null;
        $activation->notes = $data['notes'] ?? null;

        // Также преобразуем updatedAt
        $activation->updatedAt = $data['updated_at'] ?
            new \DateTimeImmutable($data['updated_at']) : null;

        $activation->updatedBy = $data['updated_by'] ? (int) $data['updated_by'] : null;

        return $activation;
    }

    protected function toArray(object $entity): array
    {
        return [
            'record_uuid' => $entity->recordUuid,
            'system_id' => $entity->systemId,
            'location' => $entity->location,
            'activation_date' => $entity->activationDate ?
                $entity->activationDate->format('Y-m-d H:i:s') : null,
            'reported_by' => $entity->reportedBy,
            'notes' => $entity->notes,
            'updated_at' => $entity->updatedAt ?
                $entity->updatedAt->format('Y-m-d H:i:s') : null,
            'updated_by' => $entity->updatedBy
        ];
    }
}