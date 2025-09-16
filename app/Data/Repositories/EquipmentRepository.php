<?php
namespace App\Data\Repositories;

use App\Data\Entities\Equipment;
use App\Core\Repository;
use PDO;
use PDOException;

class EquipmentRepository extends Repository
{
    protected $table = 'equipments';
    protected $entityClass = Equipment::class;

    public function findBySystem(int $systemId): array
    {
        return $this->findBy(['system_id' => $systemId]);
    }

    public function findByType(int $typeId): array
    {
        return $this->findBy(['type_id' => $typeId]);
    }

    public function findExpired(): array
    {
        try {
            $sql = "SELECT * FROM equipments WHERE (production_year + service_life_years) <= EXTRACT(YEAR FROM CURRENT_DATE)";
            $stmt = $this->getPdo()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'hydrate'], $results);
        } catch (PDOException $e) {
            error_log("Error finding expired equipment: " . $e->getMessage());
            return [];
        }
    }

    protected function hydrate(array $data): Equipment
    {
        return new Equipment(
            (int) $data['equipment_id'],
            $data['record_uuid'],
            (int) $data['system_id'],
            (int) $data['type_id'],
            $data['model'],
            (int) $data['production_year'],
            (int) $data['service_life_years'],
            $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null,
            $data['serial_number'] ?? null,
            $data['location'] ?? null,
            (int) $data['quantity'],
            $data['production_quarter'] ? (int) $data['production_quarter'] : null,
            $data['control_period'] ?? null,
            $data['last_control_date'] ? new \DateTimeImmutable($data['last_control_date']) : null,
            $data['control_result'] ?? null,
            $data['notes'] ?? null,
            $data['updated_by'] ? (int) $data['updated_by'] : null
        );
    }

    protected function toArray(object $entity): array
    {
        return [
            'record_uuid' => $entity->recordUuid,
            'system_id' => $entity->systemId,
            'type_id' => $entity->typeId,
            'model' => $entity->model,
            'serial_number' => $entity->serialNumber,
            'location' => $entity->location,
            'quantity' => $entity->quantity,
            'production_year' => $entity->productionYear,
            'production_quarter' => $entity->productionQuarter,
            'service_life_years' => $entity->serviceLifeYears,
            'control_period' => $entity->controlPeriod,
            'last_control_date' => $entity->lastControlDate ? $entity->lastControlDate->format('Y-m-d') : null,
            'updated_at' => $entity->updatedAt ? $entity->updatedAt->format('Y-m-d H:i:s') : null,
            'control_result' => $entity->controlResult,
            'notes' => $entity->notes,
            'updated_by' => $entity->updatedBy
        ];
    }
}