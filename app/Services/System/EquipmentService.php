<?php
namespace App\Services\System;

use App\Data\Repositories\EquipmentRepository;
use App\Data\Repositories\EquipmentTypeRepository;
use App\Data\Repositories\FireSystemRepository;
use App\Data\Entities\Equipment;
use App\Data\Repositories\MountRepository;
use App\Data\Repositories\RepairRepository;
use App\Data\Repositories\SystemActivationRepository;
use App\Data\Repositories\SystemMaintenanceRepository;
use Dotenv\Repository\Adapter\ReplacingWriter;

class EquipmentService
{
    private $equipmentRepo; // Equipment (оборудование)
    private $equipmentTypeRepo;
    private $repairRepo; // Repair (ремонты)
    private $maintenanceRepo; // Maintenance (ТО)
    private $activationRepo; // Activation (активации) 
    private $mountRepo; // Mount (монтажи)

    public function __construct(
        EquipmentRepository $equipmentRepo,
        EquipmentTypeRepository $equipmentTypeRepo,
        RepairRepository $repairRepo,
        SystemMaintenanceRepository $maintenanceRepo,
        SystemActivationRepository $activationRepo,
        MountRepository $mountRepo

    ) {
        $this->equipmentRepo = $equipmentRepo;
        $this->equipmentTypeRepo = $equipmentTypeRepo;
        $this->repairRepo = $repairRepo;
        $this->maintenanceRepo = $maintenanceRepo;
        $this->activationRepo = $activationRepo;
        $this->mountRepo = $mountRepo;
    }

    public function getSystemEquipmentWithHistory($systemId)
    {
        return [
            'equipment' => $this->equipmentRepo->findBySystem($systemId),
            'repairs' => $this->repairRepo->findBySystem($systemId),
            'maintenance' => $this->maintenanceRepo->findBySystem($systemId),
            'activations' => $this->activationRepo->findBySystem($systemId),
            'mounts' => $this->mountRepo->findBySystem($systemId),
        ];
    }

    /* Добавление оборудования */
    public function addEquipment(array $data): Equipment
    {
        $this->validateEquipmentData($data);

        $equipment = new Equipment(
            0,
            '',
            $data['system_id'],
            $data['type_id'],
            $data['model'],
            $data['serial_number'] ?? null,
            $data['location'] ?? null,
            $data['quantity'] ?? 1,
            $data['production_year'],
            $data['production_quarter'] ?? null,
            $data['service_life_years'],
            $data['control_period'] ?? null,
            $data['last_control_date'] ?? null,
            $data['control_result'] ?? null,
            $data['notes'] ?? null,
            (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            null
        );

        return $this->equipmentRepo->save($equipment);
    }

    /* Получение оборудования по системе */
    public function getEquipmentBySystem(int $systemId): array
    {
        return $this->equipmentRepo->findBySystem($systemId);
    }

    /* Получение просроченного оборудования */
    public function getExpiredEquipment(): array
    {
        return $this->equipmentRepo->findExpired();
    }

    /* Обновление данных контроля оборудования */
    public function updateEquipmentControl(string $uuid, array $controlData): Equipment
    {
        $equipment = $this->equipmentRepo->findByUuid($uuid);
        if (!$equipment) {
            throw new \Exception('Оборудование не найдено');
        }

        if (isset($controlData['last_control_date'])) {
            $equipment->lastControlDate = $controlData['last_control_date'];
        }

        if (isset($controlData['control_result'])) {
            $equipment->controlResult = $controlData['control_result'];
        }

        return $this->equipmentRepo->save($equipment);
    }

    private function validateEquipmentData(array $data): void
    {
        $required = ['system_id', 'type_id', 'model', 'production_year', 'service_life_years'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
            }
        }
    }

    public function getAllEquipmentTypes()
    {
        return $this->equipmentTypeRepo->findAll();
    }
}

// class EquipmentService
// {
//     private $equipmentRepo;
//     private $fireSystemRepo;

//     public function __construct(
//         EquipmentRepository $equipmentRepo,
//         FireSystemRepository $fireSystemRepo
//     ) {
//         $this->equipmentRepo = $equipmentRepo;
//         $this->fireSystemRepo = $fireSystemRepo;
//     }

//     /* Добавление оборудования */
//     public function addEquipment(array $data): Equipment
//     {
//         $this->validateEquipmentData($data);

//         $equipment = new Equipment(
//             0,
//             '',
//             $data['system_id'],
//             $data['type_id'],
//             $data['model'],
//             $data['serial_number'] ?? null,
//             $data['location'] ?? null,
//             $data['quantity'] ?? 1,
//             $data['production_year'],
//             $data['production_quarter'] ?? null,
//             $data['service_life_years'],
//             $data['control_period'] ?? null,
//             $data['last_control_date'] ?? null,
//             $data['control_result'] ?? null,
//             $data['notes'] ?? null,
//             (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
//             null
//         );

//         return $this->equipmentRepo->save($equipment);
//     }

//     /* Получение оборудования по системе */
//     public function getEquipmentBySystem(int $systemId): array
//     {
//         return $this->equipmentRepo->findBySystem($systemId);
//     }

//     /* Получение просроченного оборудования */
//     public function getExpiredEquipment(): array
//     {
//         return $this->equipmentRepo->findExpired();
//     }

//     /* Обновление данных контроля оборудования */
//     public function updateEquipmentControl(string $uuid, array $controlData): Equipment
//     {
//         $equipment = $this->equipmentRepo->findByUuid($uuid);
//         if (!$equipment) {
//             throw new \Exception('Оборудование не найдено');
//         }

//         if (isset($controlData['last_control_date'])) {
//             $equipment->lastControlDate = $controlData['last_control_date'];
//         }

//         if (isset($controlData['control_result'])) {
//             $equipment->controlResult = $controlData['control_result'];
//         }

//         return $this->equipmentRepo->save($equipment);
//     }

//     private function validateEquipmentData(array $data): void
//     {
//         $required = ['system_id', 'type_id', 'model', 'production_year', 'service_life_years'];
//         foreach ($required as $field) {
//             if (!isset($data[$field])) {
//                 throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
//             }
//         }
//     }
// }