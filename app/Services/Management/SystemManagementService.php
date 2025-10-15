<?php

namespace App\Services\Management;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\System\SystemCoreService;
use App\Services\System\EquipmentService;
use App\Services\System\ProjectService;
use App\Services\System\AuditHistoryService;
use App\Services\System\ProtectionobjectService;

class SystemManagementService
{
    private $coreService;
    private $equipmentService;
    private $projectService;
    private $historyService;
    private $protectionObjectService;
    public function __construct(
        SystemCoreService $coreService,
        EquipmentService $equipmentService,
        ProjectService $projectService,
        AuditHistoryService $historyService,
        ProtectionObjectService $protectionObjectService
    ) {
        $this->coreService = $coreService;
        $this->equipmentService = $equipmentService;
        $this->projectService = $projectService;
        $this->historyService = $historyService;
        $this->protectionObjectService = $protectionObjectService;
    }

    public function getSystemWithDetails($identifier): array
    {
        try {
            // 1. Получаем основную информацию системы
            $system = is_numeric($identifier)
                ? $this->coreService->getSystemById($identifier)
                : $this->coreService->getSystemByUuid($identifier);

            if (!$system) {
                Log::channel('errors')->warning('System not found', ['identifier' => $identifier]);
                throw new \Exception('Система не найдена');
            }

            // 2. Параллельно получаем все данные через соответствующие сервисы
            $coreData = $this->coreService->getSystemWithDetails($system->systemId);
            $equipmentData = $this->equipmentService->getSystemEquipmentWithHistory($system->systemId);
            $projectData = $this->projectService->getProjectsBySystem($system->systemId);
            $historyData = $this->historyService->getSystemHistory($system);
            $protectionObjectData = $system->objectId ? $this->protectionObjectService->getObjectWithDetails($system->objectId) : null;

            // 3. Объединяем все данные в одну структуру
            $details = array_merge(
                $coreData,
                [
                    'system' => $coreData['system'] ?? [],
                    'subtype' => $coreData['subtype'] ?? [],
                    'type' => $coreData['type'] ?? [],
                    'branch' => $coreData['branch'] ?? [],
                    'regulations' => $coreData['regulations'] ?? [],
                    'documents' => $coreData['documents'] ?? []
                ],
                $equipmentData,
                [
                    'equipment' => $equipmentData['equipment'] ?? [],
                    'repairs' => $equipmentData['repairs'] ?? [],
                    'maintenance' => $equipmentData['maintenance'] ?? [],
                    'activations' => $equipmentData['activations'] ?? [],
                    'mounts' => $equipmentData['mounts'] ?? [],
                ],
                $projectData,
                [
                    'plans' => $projectData['new_projects'] ?? null,
                    'projects' => $projectData['implemented_projects'] ?? []
                ],
                $historyData,
                [
                    'history' => $historyData
                ],
                $protectionObjectData,
                [
                    'object' => $protectionObjectData['object'] ?? [],
                    'group' => $protectionObjectData['group'] ?? [],
                    'curator' => $protectionObjectData['curator'] ?? [],
                ]
            );

            // Логируем успешное завершение
            Log::channel('business')->info('System details collected successfully', [
                'system_id' => $system->systemId,
                'details_count' => count($details)
            ]);

            return $details;

        } catch (\Exception $e) {
            Log::channel('errors')->error('Error getting system details', [
                'identifier' => $identifier,
                'error_message' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Комплексное обновление системы со всеми связанными данными
     */
    public function updateCompleteSystem($systemId, array $data)
    {
        Log::channel('business')->info('Starting complete system update', [
            'system_id' => $systemId,
            'user_id' => auth()->id()
        ]);

        return DB::transaction(function () use ($systemId, $data) {
            // 1. Обновление основной системы
            $system = $this->updateFireSystem($systemId, $data);

            // 2. Обновление объекта защиты
            $this->updateProtectionObject($data);

            // 3. Обновление оборудования
            $this->updateEquipment($systemId, $data);

            // 4. Обновление документации (теперь через coreService)
            $this->updateRegulations($systemId, $data);

            Log::channel('business')->info('Complete system update finished', [
                'system_id' => $systemId
            ]);

            return $system;
        });
    }

    public function getFormData(int $systemId = null): array
    {
        try {
            Log::channel('debug')->info("getFormData вызван с systemId: " . ($systemId ?? 'null'));

            // Все справочники для формы
            $formData = [
                'branches' => $this->coreService->getAllBranches(),
                'systemTypes' => $this->coreService->getAllSystemTypes(),
                'systemSubtypes' => $this->coreService->getAllSystemSubtypes(),
                'protectionObjects' => $this->protectionObjectService->getAllProtectionObjects(),
                'equipmentTypes' => $this->equipmentService->getAllEquipmentTypes(),
                'curators' => $this->protectionObjectService->getAllCurators(),
                'designOrganizations' => $this->projectService->getAllDesignOrganization(),
                'installationOrganizations' => $this->projectService->getAllInstallationOrganization(),
                'regulations' => $this->coreService->getAllRegulations(),
                'objectGroups' => $this->protectionObjectService->getAllObjectGroups(),
            ];

            Log::channel('debug')->info("Базовые справочники загружены");

            if ($systemId) {
                Log::channel('debug')->info("Загрузка деталей системы ID: " . $systemId);
                $systemDetails = $this->getSystemWithDetails($systemId);
                $formData = array_merge($formData, $systemDetails);
                Log::channel('debug')->info("Детали системы загружены");
            }

            Log::channel('debug')->info("getFormData завершен успешно");
            return $formData;

        } catch (\Exception $e) {
            Log::error('Ошибка в getFormData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function updateFireSystem($systemId, $data)
    {
        Log::channel('database')->info('Updating fire system', ['system_id' => $systemId]);

        $systemData = [
            'name' => $data['name'] ?? null,
            'subtypeId' => $data['subtypeId'] ?? null,
            'systemInventoryNumber' => $data['system_inventory_number'] ?? null,
            'isPartOfObject' => filter_var($data['isPartOfObject'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'objectId' => $data['objectId'] ?? null,
            'manualFileLink' => $data['manualFileLink'] ?? null,
            'maintenanceScheduleFileLink' => $data['maintenanceScheduleFileLink'] ?? null,
            'testProgramFileLink' => $data['testProgramFileLink'] ?? null,
        ];

        $system = $this->coreService->updateSystem($systemId, $systemData);

        Log::channel('debug')->info('Fire system updated', ['system_id' => $systemId]);

        return $system;
    }

    private function updateProtectionObject($data)
    {
        $objectId = $data['objectId'] ?? null;
        if (!$objectId) {
            return;
        }

        Log::channel('database')->info('Updating protection object', ['object_id' => $objectId]);

        $objectData = [
            'name' => $data['objectName'] ?? null,
            'shortName' => $data['objectShortName'] ?? null,
            'inventoryNumber' => $data['objectInventoryNumber'] ?? null,
            'objectGroupId' => $data['objectGroupId'] ?? null,
            'curatorId' => $data['curatorId'] ?? null,
            'notes' => $data['objectNotes'] ?? null,
        ];

        $objectData = array_filter($objectData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($objectData)) {
            // Используем objectService вместо protectionobjectService
            $this->objectService->updateObject($objectId, $objectData);
            Log::channel('business')->info('Protection object updated', ['object_id' => $objectId]);
        }
    }

    private function updateEquipment($systemId, $data)
    {
        $equipmentData = $data['equipment'] ?? [];
        $deletedEquipment = $data['deleted_equipment'] ?? '[]';

        Log::channel('database')->info('Processing equipment', [
            'system_id' => $systemId,
            'equipment_count' => count($equipmentData),
            'deleted_count' => count(json_decode($deletedEquipment, true) ?? [])
        ]);

        // Удаление оборудования
        $this->processEquipmentDeletion($deletedEquipment);

        // Обновление/создание оборудования
        $this->processEquipmentUpsert($systemId, $equipmentData);
    }

    private function processEquipmentDeletion($deletedEquipmentJson)
    {
        $deletedIds = json_decode($deletedEquipmentJson, true) ?? [];

        foreach ($deletedIds as $equipmentId) {
            try {
                // Используем equipmentService вместо старого названия
                $this->equipmentService->deleteEquipment($equipmentId);
                Log::channel('business')->info('Equipment deleted', ['equipment_id' => $equipmentId]);
            } catch (\Exception $e) {
                Log::channel('errors')->error('Equipment deletion failed', [
                    'equipment_id' => $equipmentId,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Откатываем транзакцию при ошибке
            }
        }
    }

    private function processEquipmentUpsert($systemId, $equipmentData)
    {
        foreach ($equipmentData as $index => $eqData) {
            try {
                $equipmentId = $eqData['equipmentId'] ?? null;

                $equipmentItemData = [
                    'systemId' => $systemId,
                    'typeId' => $eqData['typeId'] ?? null,
                    'model' => $eqData['model'] ?? null,
                    'serialNumber' => $eqData['serialNumber'] ?? null,
                    'location' => $eqData['location'] ?? null,
                    'quantity' => $eqData['quantity'] ?? 1,
                    'productionYear' => $eqData['productionYear'] ?? null,
                    'productionQuarter' => $eqData['productionQuarter'] ?? null,
                    'serviceLifeYears' => $eqData['serviceLifeYears'] ?? null,
                    'controlPeriod' => $eqData['controlPeriod'] ?? null,
                    'lastControlDate' => $eqData['lastControlDate'] ?? null,
                    'controlResult' => $eqData['controlResult'] ?? null,
                    'notes' => $eqData['notes'] ?? null,
                ];

                $equipmentItemData = array_filter($equipmentItemData, function ($value) {
                    return $value !== null && $value !== '';
                });

                if ($equipmentId) {
                    // Используем equipmentService
                    $this->equipmentService->updateEquipment($equipmentId, $equipmentItemData);
                    Log::channel('debug')->info('Equipment updated', ['equipment_id' => $equipmentId]);
                } else {
                    // Используем equipmentService
                    $newEquipment = $this->equipmentService->createEquipment($equipmentItemData);
                    Log::channel('business')->info('New equipment created', [
                        'equipment_id' => $newEquipment->equipmentId
                    ]);
                }

            } catch (\Exception $e) {
                Log::channel('errors')->error('Equipment processing failed', [
                    'system_id' => $systemId,
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);
                throw $e; // Откатываем транзакцию
            }
        }
    }

    private function updateRegulations($systemId, $data)
    {
        Log::channel('database')->info('Updating regulations', ['system_id' => $systemId]);

        $regulationData = [
            'manualFileLink' => $data['manualFileLink'] ?? null,
            'maintenanceScheduleFileLink' => $data['maintenanceScheduleFileLink'] ?? null,
            'testProgramFileLink' => $data['testProgramFileLink'] ?? null,
        ];

        $regulationData = array_filter($regulationData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (!empty($regulationData)) {
            // Теперь документация обрабатывается через coreService
            $this->coreService->updateRegulations($systemId, $regulationData);
            Log::channel('business')->info('Regulations updated', ['system_id' => $systemId]);
        }
    }

    /**
     * Получение полных данных системы для редактирования
     */
    public function getSystemForEdit($systemId)
    {
        // Используем coreService вместо fireSystemService
        $system = $this->coreService->getSystemWithDetails($systemId);

        Log::channel('debug')->info('System data fetched for edit', ['system_id' => $systemId]);

        return $system;
    }
}