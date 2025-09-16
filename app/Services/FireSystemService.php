<?php
namespace App\Services;

use App\Data\Repositories\FireSystemRepository;
use App\Data\Repositories\ProtectionObjectRepository;
use App\Data\Repositories\EquipmentRepository;
use App\Data\Entities\FireSystem;

class FireSystemService
{
    private $fireSystemRepo;
    private $protectionObjectRepo;
    private $equipmentRepo;

    public function __construct(
        FireSystemRepository $fireSystemRepo,
        ProtectionObjectRepository $protectionObjectRepo,
        EquipmentRepository $equipmentRepo
    ) {
        $this->fireSystemRepo = $fireSystemRepo;
        $this->protectionObjectRepo = $protectionObjectRepo;
        $this->equipmentRepo = $equipmentRepo;
    }


    public function createSystem(array $data): FireSystem
    {
        $this->validateSystemData($data);

        $fireSystem = new FireSystem(
            0, // systemId будет установлен базой данных
            '', // сгенерирует база данных
            $data['is_part_of_object'] ?? false,
            new \DateTimeImmutable(),
            $data['object_id'] ?? null,
            $data['subtype_id'] ?? null,
            $data['system_inventory_number'] ?? null,
            $data['name'] ?? null,
            $data['manual_file_link'] ?? null,
            $data['maintenance_schedule_file_link'] ?? null,
            $data['test_program_file_link'] ?? null,
            null // updatedBy будет установлен триггером базы данных
        );

        return $this->fireSystemRepo->save($fireSystem);
    }

    public function updateSystem(string $uuid, array $data): FireSystem
    {
        $system = $this->fireSystemRepo->findByUuid($uuid);
        if (!$system) {
            throw new \Exception('Система не найдена');
        }

        // Обновляем свойства системы
        if (isset($data['object_id']))
            $system->objectId = $data['object_id'];
        if (isset($data['subtype_id']))
            $system->subtypeId = $data['subtype_id'];
        if (isset($data['is_part_of_object']))
            $system->isPartOfObject = $data['is_part_of_object'];
        if (isset($data['system_inventory_number']))
            $system->systemInventoryNumber = $data['system_inventory_number'];
        if (isset($data['name']))
            $system->name = $data['name'];
        if (isset($data['manual_file_link']))
            $system->manualFileLink = $data['manual_file_link'];
        if (isset($data['maintenance_schedule_file_link']))
            $system->maintenanceScheduleFileLink = $data['maintenance_schedule_file_link'];
        if (isset($data['test_program_file_link']))
            $system->testProgramFileLink = $data['test_program_file_link'];

        // Обновляем временную метку
        $system->updatedAt = new \DateTimeImmutable();

        return $this->fireSystemRepo->save($system);
    }

    private function validateSystemData(array $data): void
    {
        $required = ['subtype_id', 'is_part_of_object'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Отсутствует обязательное поле: $field");
            }
        }

        if ($data['is_part_of_object'] && empty($data['object_id'])) {
            throw new \InvalidArgumentException('Для системы в составе объекта обязателен object_id');
        }

        // Дополнительные проверки можно добавить здесь
    }
    // public function getSystemWithDetails(string $uuid): array
    // {
    //     $system = $this->fireSystemRepo->findByUuid($uuid);
    //     if (!$system) {
    //         throw new \Exception('Система не найдена');
    //     }

    //     $object = $system->objectId ?
    //         $this->protectionObjectRepo->find($system->objectId) : null;

    //     $equipment = $this->equipmentRepo->findBySystem($system->systemId);

    //     return [
    //         'system' => $system,
    //         'object' => $object,
    //         'equipment' => $equipment
    //     ];
    // }
    public function getSystemWithDetails($identifier): array
    {
        // Определяем, это UUID или ID
        if (is_numeric($identifier)) {
            // Ищем по system_id
            $system = $this->fireSystemRepo->find($identifier);
        } else {
            // Ищем по UUID
            $system = $this->fireSystemRepo->findByUuid($identifier);
        }

        if (!$system) {
            throw new \Exception('Система не найдена');
        }

        $object = $system->objectId ?
            $this->protectionObjectRepo->find($system->objectId) : null;

        $equipment = $this->equipmentRepo->findBySystem($system->systemId);

        return [
            'system' => $system,
            'object' => $object,
            'equipment' => $equipment
        ];
    }

    private function canEditSystem(?int $objectId): bool
    {
        // Здесь будет логика проверки прав через check_approval_permission
        return true;
    }
}

// public function createSystem(array $data): FireSystem
// {
//     // Валидация данных
//     $this->validateSystemData($data);

//     // Проверка прав доступа
//     if (!$this->canEditSystem($data['object_id'] ?? null)) {
//         throw new \Exception('Недостаточно прав для создания системы');
//     }

//     return $this->fireSystemRepo->save($data);
// }

// public function updateSystem(string $uuid, array $data): FireSystem
// {
//     $system = $this->fireSystemRepo->findByUuid($uuid);
//     if (!$system) {
//         throw new \Exception('Система не найдена');
//     }

//     // Проверка прав доступа
//     if (!$this->canEditSystem($system->objectId)) {
//         throw new \Exception('Недостаточно прав для редактирования системы');
//     }

//     return $this->fireSystemRepo->update($system->systemId, $data);
// }