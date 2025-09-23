<?php
namespace App\Services;

use App\Data\Repositories\FireSystemRepository;
use App\Data\Repositories\ProtectionObjectRepository;
use App\Data\Repositories\EquipmentRepository;
use App\Data\Repositories\RepairRepository;
use App\Data\Repositories\SystemMaintenanceRepository;
use App\Data\Repositories\SystemActivationRepository;
use App\Data\Repositories\MountRepository;
use App\Data\Repositories\NewProjectRepository;
use App\Data\Repositories\BranchRepository;
use App\Data\Repositories\SystemSubtypeRepository;
use App\Data\Repositories\SystemTypeRepository;
use App\Data\Repositories\RegulationRepository;
use App\Data\Repositories\ChangeLogRepository;
use App\Data\Repositories\ApprovalHistoryRepository;
use App\Data\Entities\FireSystem;
use Illuminate\Support\Facades\Log;
use DB;

class FireSystemService
{
    private $fireSystemRepo;
    private $protectionObjectRepo;
    private $equipmentRepo;
    private $repairRepo;
    private $maintenanceRepo;
    private $activationRepo;
    private $mountRepo;
    private $projectRepo;
    private $branchRepo;
    private $subtypeRepo;
    private $systemTypeRepo;
    private $regulationRepo;
    private $changeLogRepo;
    private $newProjectRepo;
    private $approvalHistoryRepo;

    public function __construct(
        FireSystemRepository $fireSystemRepo,
        ProtectionObjectRepository $protectionObjectRepo,
        EquipmentRepository $equipmentRepo,
        RepairRepository $repairRepo,
        SystemMaintenanceRepository $maintenanceRepo,
        SystemActivationRepository $activationRepo,
        MountRepository $mountRepo,
        NewProjectRepository $projectRepo,
        BranchRepository $branchRepo,
        SystemSubtypeRepository $subtypeRepo,
        SystemTypeRepository $systemTypeRepo,
        RegulationRepository $regulationRepo,
        ChangeLogRepository $changeLogRepo,
        NewProjectRepository $newProjectRepo
    ) {
        $this->fireSystemRepo = $fireSystemRepo;
        $this->protectionObjectRepo = $protectionObjectRepo;
        $this->equipmentRepo = $equipmentRepo;
        $this->repairRepo = $repairRepo;
        $this->maintenanceRepo = $maintenanceRepo;
        $this->activationRepo = $activationRepo;
        $this->mountRepo = $mountRepo;
        $this->projectRepo = $projectRepo;
        $this->branchRepo = $branchRepo;
        $this->subtypeRepo = $subtypeRepo;
        $this->systemTypeRepo = $systemTypeRepo;
        $this->regulationRepo = $regulationRepo;
        $this->changeLogRepo = $changeLogRepo;
        $this->newProjectRepo = $newProjectRepo;
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

    public function getSystemWithDetails($identifier): array
    {
        if (is_numeric($identifier)) {
            $system = $this->fireSystemRepo->find($identifier);
        } else {
            $system = $this->fireSystemRepo->findByUuid($identifier);
        }

        if (!$system) {
            throw new \Exception('Система не найдена');
        }

        // Отладочная информация
        error_log("=== SYSTEM DEBUG ===");
        error_log("System ID: " . $system->systemId);
        error_log("Object ID: " . $system->objectId);
        error_log("Subtype ID: " . $system->subtypeId);

        $object = $system->objectId ? $this->protectionObjectRepo->find($system->objectId) : null;
        error_log("Object found: " . ($object ? 'YES' : 'NO'));
        if ($object) {
            error_log("Object branchId: " . $object->branchId);
        }

        $subtype = $system->subtypeId ? $this->subtypeRepo->find($system->subtypeId) : null;
        error_log("Subtype found: " . ($subtype ? 'YES' : 'NO'));
        if ($subtype) {
            error_log("Subtype typeId: " . $subtype->typeId);
        }

        $branch = $object ? $this->getBranch($object->branchId) : null;
        error_log("Branch found: " . ($branch ? 'YES' : 'NO'));

        $systemType = $subtype ? $this->getSystemType($subtype->typeId) : null;
        error_log("System Type found: " . ($systemType ? 'YES' : 'NO'));

        return [
            'system' => $system,
            'object' => $object,
            'equipment' => $this->getEquipment($system->systemId),
            'repairs' => $this->getRepairs($system->systemId),
            'maintenance' => $this->getMaintenance($system->systemId),
            'activations' => $this->getActivations($system->systemId),
            'mounts' => $this->getMounts($system->systemId),
            'projects' => $this->getProjects($system->systemId),
            'branch' => $branch,
            'subtype' => $subtype,
            'system_type' => $systemType,
            'documents' => $this->getRegulations(),
            'history' => $this->getSystemHistory($system),
            'plans' => $this->getNewProjects($system->systemId)
        ];
    }

    protected function getRegulations(): array
    {
        try {
            return $this->regulationRepo->findAll();
        } catch (\Exception $e) {
            error_log("Error getting regulations: " . $e->getMessage());
            return [];
        }
    }

    protected function getNewProjects(int $systemId): array
    {
        try {
            // Сначала проверим все проекты в БД
            $this->newProjectRepo->debugAllProjects();

            error_log("=== Getting projects for system: {$systemId} ===");
            $projects = $this->newProjectRepo->findBySystemWithDetails($systemId);

            error_log("Projects returned: " . count($projects));
            if (count($projects) > 0) {
                error_log("First project: " . json_encode($projects[0]));
            }

            return $projects;
        } catch (\Exception $e) {
            error_log("Error getting new projects: " . $e->getMessage());
            return [];
        }
    }

    protected function getSystemHistory(object $system): array
    {
        try {
            $changeLogs = $this->changeLogRepo->findByTableAndRecordWithUser('fire_systems', $system->recordUuid);
            // Если есть ApprovalHistoryRepository, получаем подтверждения
            $approvalHistory = [];
            if (isset($this->approvalHistoryRepo)) {
                $approvalHistory = $this->approvalHistoryRepo->findByTableAndRecord('fire_systems', $system->systemId);
            }
            // Объединяем историю
            return array_merge($changeLogs, $approvalHistory);
        } catch (\Exception $e) {
            error_log("Error getting system history: " . $e->getMessage());
            return [];
        }
    }

    private function getEquipment(int $systemId): array
    {
        try {
            return $this->equipmentRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении оборудования: " . $e->getMessage());
            return [];
        }
    }

    private function getRepairs(int $systemId): array
    {
        try {
            return $this->repairRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении ремонтов: " . $e->getMessage());
            return [];
        }
    }

    private function getMaintenance(int $systemId): array
    {
        try {
            return $this->maintenanceRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении ТО: " . $e->getMessage());
            return [];
        }
    }

    private function getActivations(int $systemId): array
    {
        try {
            return $this->activationRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении активаций: " . $e->getMessage());
            return [];
        }
    }

    private function getMounts(int $systemId): array
    {
        try {
            return $this->mountRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении монтажей: " . $e->getMessage());
            return [];
        }
    }

    private function getProjects(int $systemId): array
    {
        try {
            return $this->projectRepo->findBySystem($systemId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении проектов: " . $e->getMessage());
            return [];
        }
    }

    private function getBranch(int $branchId)
    {
        try {
            error_log("Getting branch with ID: " . $branchId);
            $branch = $this->branchRepo->find($branchId);
            error_log("Branch retrieved: " . ($branch ? 'YES' : 'NO'));
            return $branch;
        } catch (\Exception $e) {
            error_log("Error getting branch: " . $e->getMessage());
            return null;
        }
    }

    private function getSubtype(int $subtypeId)
    {
        try {
            return $this->subtypeRepo->find($subtypeId);
        } catch (\Exception $e) {
            error_log("Ошибка при получении подтипа: " . $e->getMessage());
            return null;
        }
    }

    private function getSystemType(int $typeId)
    {
        try {
            error_log("Getting system type with ID: " . $typeId);
            $systemType = $this->systemTypeRepo->find($typeId);
            error_log("System type retrieved: " . ($systemType ? 'YES' : 'NO'));
            return $systemType;
        } catch (\Exception $e) {
            error_log("Error getting system type: " . $e->getMessage());
            return null;
        }
    }
    private function canEditSystem(?int $objectId, string $recordUuid): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // Если objectId не указан, проверяем глобальные права
        if ($objectId === null) {
            return $user->hasRole('edit_all');
        }

        try {
            $result = DB::selectOne("
            SELECT check_approval_permission(
                :user_id, 
                :record_uuid, 
                'fire_systems', 
                'edit'
            ) as can_edit
        ", [
                'user_id' => $user->id,
                'record_uuid' => $recordUuid
            ]);

            return (bool) $result->can_edit;

        } catch (\Exception $e) {
            Log::error('Error checking approval permission: ' . $e->getMessage());
            return $user->hasRole('edit_all') ||
                $user->hasRole('edit_branch') ||
                $user->hasRole('engineer') ||
                $user->hasRole('chief');
        }
    }

    public function deleteSystem(string $uuid): array
    {
        try {
            \Log::info("Начало удаления системы", ['uuid' => $uuid]);

            DB::beginTransaction();

            $systems = $this->fireSystemRepo->findBy(['record_uuid' => $uuid]);

            if (empty($systems)) {
                DB::rollBack();
                \Log::warning("Система не найдена", ['uuid' => $uuid]);
                return ['success' => false, 'error' => 'Система не найдена'];
            }

            $system = $systems[0];
            $systemId = $system->systemId;

            // дочерние записи
            $tables = [
                'equipments',
                'system_activations',
                'system_maintenance',
                'mounts',
                'repairs',
                'new_projects',
                'implemented_projects',
            ];

            foreach ($tables as $table) {
                $result = DB::delete("DELETE FROM {$table} WHERE system_id = ?", [$systemId]);
                \Log::debug("Удаление из таблицы", ['table' => $table, 'deleted_rows' => $result]);
            }

            $deleteResult = $this->fireSystemRepo->delete($systemId);
            if (!$deleteResult) {
                DB::rollBack();
                \Log::error("Не удалось удалить систему", ['system_id' => $systemId]);
                return ['success' => false, 'error' => 'Не удалось удалить систему'];
            }

            DB::commit();
            \Log::info("Система успешно удалена", ['system_id' => $systemId, 'uuid' => $uuid]);

            return ['success' => true, 'message' => 'Система удалена'];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Ошибка при удалении системы", [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

