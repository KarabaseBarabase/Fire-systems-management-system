<?php
// app/Services/SystemManagementService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemManagementService
{
    public function __construct(
        private FireSystemService $fireSystemService,
        private ProtectionObjectService $protectionObjectService,
        private EquipmentService $equipmentService,
        private RegulationService $regulationService
    ) {
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

            // 4. Обновление документации
            $this->updateRegulations($systemId, $data);

            Log::channel('business')->info('Complete system update finished', [
                'system_id' => $systemId
            ]);

            return $system;
        });
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

        $system = $this->fireSystemService->updateSystem($systemId, $systemData);

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
            $this->protectionObjectService->updateObject($objectId, $objectData);
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
                    $this->equipmentService->updateEquipment($equipmentId, $equipmentItemData);
                    Log::channel('debug')->info('Equipment updated', ['equipment_id' => $equipmentId]);
                } else {
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
            $this->regulationService->updateOrCreateRegulation($systemId, $regulationData);
            Log::channel('business')->info('Regulations updated', ['system_id' => $systemId]);
        }
    }

    /**
     * Получение полных данных системы для редактирования
     */
    public function getSystemForEdit($systemId)
    {
        $system = $this->fireSystemService->getSystemWithRelations($systemId);

        Log::channel('debug')->info('System data fetched for edit', ['system_id' => $systemId]);

        return $system;
    }
}