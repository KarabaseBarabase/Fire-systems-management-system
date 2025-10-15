<?php
namespace App\Services;

use App\Data\Repositories\{
    FireSystemRepository,
    ProtectionObjectRepository,
    EquipmentRepository,
    EquipmentTypeRepository,
    RepairRepository,
    SystemMaintenanceRepository,
    SystemActivationRepository,
    MountRepository,
    NewProjectRepository,
    BranchRepository,
    SystemSubtypeRepository,
    SystemTypeRepository,
    RegulationRepository,
    ChangeLogRepository,
    ApprovalHistoryRepository,
    CuratorRepository,
    DesignOrganizationRepository,
    InstallationOrganizationRepository,
    ObjectGroupRepository
};

use App\Data\Entities\FireSystem;
use Illuminate\Support\Facades\Log;
use DB;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;

class FireSystemService
{
    private $fireSystemRepo;
    private $protectionObjectRepo;
    private $equipmentRepo;
    private $equipmentTypeRepo;
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
    private $curatorRepo;
    private $designOrganizationRepo;
    private $installationOrganizationRepo;
    private $objectGroupRepo;

    private $auth;

    public function __construct(
        FireSystemRepository $fireSystemRepo,
        ProtectionObjectRepository $protectionObjectRepo,
        EquipmentRepository $equipmentRepo,
        EquipmentTypeRepository $equipmentTypeRepo,
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
        NewProjectRepository $newProjectRepo,
        ApprovalHistoryRepository $approvalHistoryRepo = null,
        CuratorRepository $curatorRepo = null,
        DesignOrganizationRepository $designOrganizationRepo = null,
        InstallationOrganizationRepository $installationOrganizationRepo = null,
        ObjectGroupRepository $objectGroupRepo = null,

        Guard $auth
    ) {
        $this->fireSystemRepo = $fireSystemRepo;
        $this->protectionObjectRepo = $protectionObjectRepo;
        $this->equipmentRepo = $equipmentRepo;
        $this->equipmentTypeRepo = $equipmentTypeRepo;
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
        $this->approvalHistoryRepo = $approvalHistoryRepo;
        $this->curatorRepo = $curatorRepo;
        $this->designOrganizationRepo = $designOrganizationRepo;
        $this->installationOrganizationRepo = $installationOrganizationRepo;
        $this->objectGroupRepo = $objectGroupRepo;

        $this->auth = $auth;
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

    public function updateSystem(string $id, array $data): FireSystem
    {
        $system = $this->fireSystemRepo->find($id);
        if (!$system) {
            throw new \Exception('Система не найдена');
        }

        error_log("Updating system ID: " . $id . ", Record UUID: " . ($system->recordUuid ?? 'no UUID'));

        // Обновляем свойства системы
        if (isset($data['object_id']))
            $system->objectId = $data['object_id'];
        if (isset($data['subtype_id']))
            $system->subtypeId = $data['subtype_id'];

        // Исправляем обработку boolean поля
        if (isset($data['is_part_of_object'])) {
            $system->isPartOfObject = (bool) $data['is_part_of_object'];
        } else {
            $system->isPartOfObject = false; // если чекбокс не отмечен
        }

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

    // public function getSystemWithDetails($identifier): array
    // {
    //     try {
    //         Log::channel('business')->info('Getting system with details', [
    //             'identifier' => $identifier,
    //             'identifier_type' => is_numeric($identifier) ? 'numeric_id' : 'uuid'
    //         ]);

    //         // Поиск системы
    //         if (is_numeric($identifier)) {
    //             $system = $this->fireSystemRepo->find($identifier);
    //             Log::channel('debug')->debug('Searching system by ID', ['id' => $identifier]);
    //         } else {
    //             $system = $this->fireSystemRepo->findByUuid($identifier);
    //             Log::channel('debug')->debug('Searching system by UUID', ['uuid' => $identifier]);
    //         }

    //         if (!$system) {
    //             Log::channel('errors')->warning('System not found', [
    //                 'identifier' => $identifier,
    //                 'search_type' => is_numeric($identifier) ? 'id' : 'uuid'
    //             ]);
    //             throw new \Exception('Система не найдена');
    //         }

    //         Log::channel('business')->info('System found', [
    //             'system_id' => $system->systemId,
    //             'object_id' => $system->objectId,
    //             'subtype_id' => $system->subtypeId
    //         ]);

    //         // Получение связанных данных
    //         $object = $system->objectId ? $this->protectionObjectRepo->find($system->objectId) : null;
    //         Log::channel('debug')->debug('Object lookup result', [
    //             'object_id' => $system->objectId,
    //             'object_found' => !is_null($object),
    //             'object_branch_id' => $object ? $object->branchId : null
    //         ]);

    //         $subtype = $system->subtypeId ? $this->subtypeRepo->find($system->subtypeId) : null;
    //         Log::channel('debug')->debug('Subtype lookup result', [
    //             'subtype_id' => $system->subtypeId,
    //             'subtype_found' => !is_null($subtype),
    //             'subtype_type_id' => $subtype ? $subtype->typeId : null
    //         ]);

    //         // Получение дополнительных данных
    //         $branch = $object ? $this->getBranch($object->branchId) : null;
    //         $systemType = $subtype ? $this->getSystemType($subtype->typeId) : null;

    //         Log::channel('debug')->debug('Additional data lookup', [
    //             'branch_found' => !is_null($branch),
    //             'system_type_found' => !is_null($systemType)
    //         ]);

    //         // Сбор всех данных системы
    //         Log::channel('business')->info('Collecting system details', ['system_id' => $system->systemId]);

    //         $details = [
    //             'system' => $system,
    //             'object' => $object,
    //             'equipment' => $this->getEquipment($system->systemId),
    //             'repairs' => $this->getRepairs($system->systemId),
    //             'maintenance' => $this->getMaintenance($system->systemId),
    //             'activations' => $this->getActivations($system->systemId),
    //             'mounts' => $this->getMounts($system->systemId),
    //             'projects' => $this->getProjects($system->systemId),
    //             'branch' => $branch,
    //             'subtype' => $subtype,
    //             'system_type' => $systemType,
    //             'documents' => $this->getRegulations(),
    //             'history' => $this->getSystemHistory($system),
    //             'plans' => $this->getNewProjects($system->systemId)
    //         ];

    //         // Логируем успешное завершение
    //         Log::channel('business')->info('System details collected successfully', [
    //             'system_id' => $system->systemId,
    //             'details_count' => count($details),
    //             'has_equipment' => !empty($details['equipment']),
    //             'has_repairs' => !empty($details['repairs']),
    //             'has_maintenance' => !empty($details['maintenance'])
    //         ]);

    //         return $details;

    //     } catch (\Exception $e) {
    //         Log::channel('errors')->error('Error getting system details', [
    //             'identifier' => $identifier,
    //             'error_message' => $e->getMessage(),
    //             'error_file' => $e->getFile(),
    //             'error_line' => $e->getLine()
    //         ]);

    //         throw $e;
    //     }
    // }

    // protected function getRegulations(): array
    // {
    //     try {
    //         return $this->regulationRepo->findAll();
    //     } catch (\Exception $e) {
    //         error_log("Error getting regulations: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // protected function getNewProjects(int $systemId): array
    // {
    //     try {
    //         // Сначала проверим все проекты в БД
    //         $this->newProjectRepo->debugAllProjects();

    //         error_log("=== Getting projects for system: {$systemId} ===");
    //         $projects = $this->newProjectRepo->findBySystemWithDetails($systemId);

    //         error_log("Projects returned: " . count($projects));
    //         if (count($projects) > 0) {
    //             error_log("First project: " . json_encode($projects[0]));
    //         }

    //         return $projects;
    //     } catch (\Exception $e) {
    //         error_log("Error getting new projects: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // protected function getSystemHistory(object $system): array
    // {
    //     try {
    //         $changeLogs = $this->changeLogRepo->findByTableAndRecordWithUser('fire_systems', $system->recordUuid);
    //         // Если есть ApprovalHistoryRepository, получаем подтверждения
    //         $approvalHistory = [];
    //         if (isset($this->approvalHistoryRepo)) {
    //             $approvalHistory = $this->approvalHistoryRepo->findByTableAndRecord('fire_systems', $system->systemId);
    //         }
    //         // Объединяем историю
    //         return array_merge($changeLogs, $approvalHistory);
    //     } catch (\Exception $e) {
    //         error_log("Error getting system history: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getEquipment(int $systemId): array
    // {
    //     try {
    //         $equipment = $this->equipmentRepo->findBySystem($systemId);

    //         // Логируем что возвращается из репозитория
    //         Log::channel('debug')->debug('Equipment data from repository in service', [
    //             'system_id' => $systemId,
    //             'count' => count($equipment),
    //             'data_type' => gettype($equipment),
    //             'is_array' => is_array($equipment),
    //             'first_item_exists' => isset($equipment[0]),
    //             'first_item_type' => isset($equipment[0]) ? gettype($equipment[0]) : 'no items',
    //             'first_item_class' => isset($equipment[0]) ? get_class($equipment[0]) : 'no items'
    //         ]);

    //         if (isset($equipment[0]) && is_object($equipment[0])) {
    //             Log::channel('debug')->debug('First equipment item properties', [
    //                 'equipment_type_name' => $equipment[0]->equipment_type_name ?? 'NOT SET',
    //                 'model' => $equipment[0]->model ?? 'NOT SET',
    //                 'serialNumber' => $equipment[0]->serialNumber ?? 'NOT SET',
    //                 'productionYear' => $equipment[0]->productionYear ?? 'NOT SET',
    //                 'all_properties' => get_object_vars($equipment[0])
    //             ]);
    //         }

    //         return $equipment;
    //     } catch (\Exception $e) {
    //         Log::channel('errors')->error("Ошибка при получении оборудования", [
    //             'system_id' => $systemId,
    //             'error' => $e->getMessage()
    //         ]);
    //         return [];
    //     }
    // }

    // private function getRepairs(int $systemId): array
    // {
    //     try {
    //         return $this->repairRepo->findBySystem($systemId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении ремонтов: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getMaintenance(int $systemId): array
    // {
    //     try {
    //         return $this->maintenanceRepo->findBySystem($systemId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении ТО: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getActivations(int $systemId): array
    // {
    //     try {
    //         return $this->activationRepo->findBySystem($systemId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении активаций: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getMounts(int $systemId): array
    // {
    //     try {
    //         return $this->mountRepo->findBySystem($systemId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении монтажей: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getProjects(int $systemId): array
    // {
    //     try {
    //         return $this->projectRepo->findBySystem($systemId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении проектов: " . $e->getMessage());
    //         return [];
    //     }
    // }

    // private function getBranch(int $branchId)
    // {
    //     try {
    //         error_log("Getting branch with ID: " . $branchId);
    //         $branch = $this->branchRepo->find($branchId);
    //         error_log("Branch retrieved: " . ($branch ? 'YES' : 'NO'));
    //         return $branch;
    //     } catch (\Exception $e) {
    //         error_log("Error getting branch: " . $e->getMessage());
    //         return null;
    //     }
    // }

    // private function getSubtype(int $subtypeId)
    // {
    //     try {
    //         return $this->subtypeRepo->find($subtypeId);
    //     } catch (\Exception $e) {
    //         error_log("Ошибка при получении подтипа: " . $e->getMessage());
    //         return null;
    //     }
    // }

    // private function getSystemType(int $typeId)
    // {
    //     try {
    //         error_log("Getting system type with ID: " . $typeId);
    //         $systemType = $this->systemTypeRepo->find($typeId);
    //         error_log("System type retrieved: " . ($systemType ? 'YES' : 'NO'));
    //         return $systemType;
    //     } catch (\Exception $e) {
    //         error_log("Error getting system type: " . $e->getMessage());
    //         return null;
    //     }
    // }
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

    /**
     * Получить все данные для формы редактирования/создания системы
     */
    public function getFormData($identifier = null): array
    {
        try {
            $formData = [
                'branches' => $this->branchRepo->findAll(),
                'systemTypes' => $this->systemTypeRepo->findAll(),
                'systemSubtypes' => $this->subtypeRepo->findAll(),
                'protectionObjects' => $this->protectionObjectRepo->findAll(),
                'equipmentTypes' => $this->equipmentTypeRepo->findAll(),
                'curators' => $this->curatorRepo ? $this->curatorRepo->findAll() : [],
                'designOrganizations' => $this->designOrganizationRepo ? $this->designOrganizationRepo->findAll() : [],
                'installationOrganizations' => $this->installationOrganizationRepo ? $this->installationOrganizationRepo->findAll() : [],
                'regulations' => $this->regulationRepo->findAll(),
                'objectGroups' => $this->objectGroupRepo ? $this->objectGroupRepo->findAll() : [],
            ];

            // Если передан идентификатор - получаем данные конкретной системы
            if ($identifier) {
                $systemDetails = $this->getSystemWithDetails($identifier);
                $formData = array_merge($formData, $systemDetails);
            }

            return $formData;

        } catch (\Exception $e) {
            Log::error('Ошибка при получении данных формы: ' . $e->getMessage());
            throw $e;
        }
    }
}

