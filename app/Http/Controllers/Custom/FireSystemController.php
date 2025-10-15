<?php
namespace App\Http\Controllers\Custom;

use App\Services\FireSystemService;
use App\Core\AuthInterface;
use App\Core\Database;
use Illuminate\Support\Facades\Log;
use SystemManager;

class FireSystemController
{
    private $fireSystemService;
    private $fireSystemRepository;
    private $systemManager;
    private $auth;
    private $database;

    public function __construct(SystemManager $systemManager, AuthInterface $auth, Database $database, )
    {
        $this->systemManager = $systemManager;
        $this->auth = $auth;
        $this->database = $database;
    }

    /* Получить все системы пожаротушения */
    public function index()
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        try {
            // Здесь будет логика получения списка систем
            // Пока заглушка - в реальности нужно вызвать соответствующий метод сервиса
            return [
                'success' => true,
                'data' => []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    // public function show($id)
    // {
    //     if (!$this->auth->check()) {
    //         return redirect('/login');
    //     }
    //     \Log::channel('debug')->info('Show method called', ['id' => $id, 'view' => 'systems.show']);
    //     try {
    //         $fireSystemService = app(FireSystemService::class);
    //         $systemDetails = $fireSystemService->getSystemWithDetails($id);

    //         return view('systems.show', [
    //             'system' => $systemDetails['system'],
    //             'object' => $systemDetails['object'],
    //             'equipment' => $systemDetails['equipment'],
    //             'repairs' => $systemDetails['repairs'],
    //             'maintenance' => $systemDetails['maintenance'],
    //             'activations' => $systemDetails['activations'],
    //             'mounts' => $systemDetails['mounts'],
    //             'projects' => $systemDetails['projects'], // Реализованные проекты
    //             'branch' => $systemDetails['branch'],
    //             'subtype' => $systemDetails['subtype'],
    //             'system_type' => $systemDetails['system_type'],
    //             'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
    //             'userRole' => $this->auth->user()->position ?? 'Пользователь',
    //             'documents' => $systemDetails['documents'],
    //             'history' => $systemDetails['history'],
    //             'plans' => $systemDetails['plans'] // Новые проекты
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('Error in show method', ['error' => $e->getMessage()]);
    //         abort(500, 'Ошибка при загрузке системы: ' . $e->getMessage());
    //     }
    // }
    public function show($id)
    {
        $systemDetails = SystemManager::getSystemWithDetails($id);

        return view('systems.show', [
            'system' => $systemDetails['system'],
            'object' => $systemDetails['object'],
            'equipment' => $systemDetails['equipment'],
            'repairs' => $systemDetails['repairs'],
            'maintenance' => $systemDetails['maintenance'],
            'activations' => $systemDetails['activations'],
            'mounts' => $systemDetails['mounts'],
            'projects' => $systemDetails['implemented_projects'], // из projectData
            'branch' => $systemDetails['branch'],
            'subtype' => $systemDetails['subtype'],
            'system_type' => $systemDetails['type'], // обратите внимание - 'type' а не 'system_type'
            'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
            'userRole' => $this->auth->user()->position ?? 'Пользователь',
            'documents' => $systemDetails['documents'], // документация системы из coreData
            'regulations' => $systemDetails['regulations'], // нормативные документы из coreData
            'history' => $systemDetails['history'],
            'plans' => $systemDetails['new_projects'] // из projectData
        ]);
    }

    /* Создать новую систему */
    public function store()
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        try {
            $request = $_POST; // или json_decode(file_get_contents('php://input'), true)
            $system = $this->fireSystemService->createSystem($request);

            return [
                'success' => true,
                'message' => 'Система успешно создана',
                'data' => $system
            ];
        } catch (\InvalidArgumentException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Ошибка при создании системы'
            ];
        }
    }
    public function create()
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        try {
            return [
                'success' => true,
                'view_data' => [
                    'systemSubtypes' => $this->systemCoreService->getAllSystemSubtypes(),
                    'protectionObjects' => $this->protectionObjectService->getAllProtectionObjects(),
                    'objectGroups' => $this->objectGroupRepo->getAllObjectGroups(),
                    'curators' => $this->curatorRepo->getAllCurators(),
                    'branches' => $this->branchRepo->getAllBranches(), // ← ОБЯЗАТЕЛЬНО!
                    'equipmentTypes' => $this->equipmentRepo->getAllEquipmentTypes(),
                    'equipmentCount' => 0,
                    'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
                    'userRole' => $this->auth->user()->position ?? 'Пользователь'
                ]
            ];

            // Или если используете шаблонизатор:
            // return $this->view->render('system/edit.html', [
            //     'systemSubtypes' => $this->systemCoreService->getAllSystemSubtypes(),
            //     'protectionObjects' => $this->protectionObjectService->getAllProtectionObjects(),
            //     'objectGroups' => $this->objectGroupRepo->getAllObjectGroups(),
            //     'curators' => $this->curatorRepo->getAllCurators(),
            //     'branches' => $this->branchRepo->getAllBranches(),
            //     'equipmentTypes' => $this->equipmentRepo->getAllEquipmentTypes(),
            //     'equipmentCount' => 0,
            //     'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
            //     'userRole' => $this->auth->user()->position ?? 'Пользователь'
            // ]);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Ошибка при загрузке формы: ' . $e->getMessage()
            ];
        }
    }


    /* Обновить систему */
    public function update($id)
    {
        if (!$this->auth->check()) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        try {
            $system = $this->systemManager->updateCompleteSystem($id, request()->all());

            \Log::channel('business')->info('System updated successfully', [
                'system_id' => $system->systemId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Система успешно обновлена',
                'redirect' => route('system.show', $system->systemId)
            ]);

        } catch (\Exception $e) {
            \Log::channel('errors')->error('System update failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка при обновлении системы'
            ], 500);
        }
    }

    private function updateSystemWithRelations($systemId, $data)
    {
        \Log::channel('debug')->infoinfo('Starting system relations update', ['system_id' => $systemId]);

        // 1. Обновление основной системы
        $system = $this->updateFireSystem($systemId, $data);

        // 2. Обновление объекта защиты (если привязан)
        if (isset($data['objectId']) && $data['objectId']) {
            $this->updateProtectionObject($data['objectId'], $data);
        }

        // 3. Обновление оборудования
        if (isset($data['equipment']) && is_array($data['equipment'])) {
            $this->updateEquipment($systemId, $data['equipment']);
        }

        // 4. Обработка удаленного оборудования
        if (isset($data['deleted_equipment'])) {
            $this->deleteEquipment($data['deleted_equipment']);
        }

        // 5. Обновление документации (regulation)
        $this->updateRegulations($systemId, $data);

        return $system;
    }

    private function updateFireSystem($systemId, $data)
    {
        \Log::channel('database')->info('Updating fire_system', [
            'system_id' => $systemId,
            'data' => [
                'name' => $data['name'] ?? null,
                'subtypeId' => $data['subtypeId'] ?? null,
                'systemInventoryNumber' => $data['system_inventory_number'] ?? null,
                'isPartOfObject' => $data['isPartOfObject'] ?? false,
                'objectId' => $data['objectId'] ?? null
            ]
        ]);

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

        \Log::channel('business')->info('Fire system updated', ['system_id' => $systemId]);

        return $system;
    }

    private function updateProtectionObject($objectId, $data)
    {
        \Log::channel('database')->info('Updating protection_object', [
            'object_id' => $objectId,
            'data' => [
                'name' => $data['objectName'] ?? null,
                'shortName' => $data['objectShortName'] ?? null,
                'inventoryNumber' => $data['objectInventoryNumber'] ?? null,
                'objectGroupId' => $data['objectGroupId'] ?? null,
                'curatorId' => $data['curatorId'] ?? null,
                'notes' => $data['objectNotes'] ?? null
            ]
        ]);

        $objectData = [
            'name' => $data['objectName'] ?? null,
            'shortName' => $data['objectShortName'] ?? null,
            'inventoryNumber' => $data['objectInventoryNumber'] ?? null,
            'objectGroupId' => $data['objectGroupId'] ?? null,
            'curatorId' => $data['curatorId'] ?? null,
            'notes' => $data['objectNotes'] ?? null,
        ];

        // Удаляем null значения
        $objectData = array_filter($objectData, function ($value) {
            return $value !== null;
        });

        if (!empty($objectData)) {
            // Предполагая, что у вас есть сервис для объектов
            $this->protectionObjectService->updateObject($objectId, $objectData);
            \Log::channel('business')->info('Protection object updated', ['object_id' => $objectId]);
        }
    }

    private function updateEquipment($systemId, $equipmentData)
    {
        \Log::channel('database')->info('Updating equipment', [
            'system_id' => $systemId,
            'equipment_count' => count($equipmentData),
            'equipment_data' => $equipmentData
        ]);

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

                // Удаляем null значения
                $equipmentItemData = array_filter($equipmentItemData, function ($value) {
                    return $value !== null;
                });

                if ($equipmentId) {
                    // Обновление существующего оборудования
                    $this->equipmentService->updateEquipment($equipmentId, $equipmentItemData);
                    \Log::channel('debug')->infoinfo('Equipment updated', [
                        'equipment_id' => $equipmentId,
                        'index' => $index
                    ]);
                } else {
                    // Создание нового оборудования
                    $newEquipment = $this->equipmentService->createEquipment($equipmentItemData);
                    \Log::channel('business')->info('New equipment created', [
                        'equipment_id' => $newEquipment->equipmentId,
                        'system_id' => $systemId
                    ]);
                }

            } catch (\Exception $e) {
                \Log::channel('errors')->error('Equipment update failed', [
                    'system_id' => $systemId,
                    'index' => $index,
                    'error' => $e->getMessage()
                ]);
                // Продолжаем обработку остального оборудования
                continue;
            }
        }
    }

    private function deleteEquipment($deletedEquipmentJson)
    {
        $deletedIds = json_decode($deletedEquipmentJson, true) ?? [];

        if (!empty($deletedIds)) {
            \Log::channel('database')->info('Deleting equipment', ['equipment_ids' => $deletedIds]);

            foreach ($deletedIds as $equipmentId) {
                try {
                    $this->equipmentService->deleteEquipment($equipmentId);
                    \Log::channel('business')->info('Equipment deleted', ['equipment_id' => $equipmentId]);
                } catch (\Exception $e) {
                    \Log::channel('errors')->error('Equipment deletion failed', [
                        'equipment_id' => $equipmentId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    private function updateRegulations($systemId, $data)
    {
        \Log::channel('database')->info('Updating regulations', ['system_id' => $systemId]);

        $regulationData = [
            'manualFileLink' => $data['manualFileLink'] ?? null,
            'maintenanceScheduleFileLink' => $data['maintenanceScheduleFileLink'] ?? null,
            'testProgramFileLink' => $data['testProgramFileLink'] ?? null,
        ];

        // Удаляем null значения
        $regulationData = array_filter($regulationData, function ($value) {
            return $value !== null;
        });

        if (!empty($regulationData)) {
            // Предполагая, что у вас есть сервис для документации
            $this->regulationService->updateOrCreateRegulation($systemId, $regulationData);
            \Log::channel('business')->info('Regulations updated', ['system_id' => $systemId]);
        }
    }

    /* Удалить систему */
    public function destroy($uuid)
    {
        try {
            \Log::channel('debug')->info("Начало удаления системы", ['uuid' => $uuid]);

            if (!$this->auth->check()) {
                \Log::warning("Пользователь не авторизован");
                return response()->json(['error' => 'Не авторизован'], 401);
            }

            if (!$uuid) {
                \Log::warning("UUID не указан");
                return response()->json([
                    'success' => false,
                    'error' => 'UUID системы не указан'
                ], 400);
            }

            $result = $this->fireSystemService->deleteSystem($uuid);

            $statusCode = $result['success'] ? 200 : 500;
            \Log::channel('debug')->info("Удаление завершено", [
                'success' => $result['success'],
                'status_code' => $statusCode
            ]);

            return response()->json($result, $statusCode);

        } catch (\Exception $e) {
            \Log::error("Ошибка при удалении системы", [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Внутренняя ошибка сервера'
            ], 500);
        }
    }

    /* Получить системы по объекту */
    public function getByObject($params)
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        $objectId = $params['id'] ?? null;
        if (!$objectId) {
            return [
                'success' => false,
                'error' => 'ID объекта не указан'
            ];
        }

        // Здесь будет логика получения систем по объекту
        // Пока заглушка

        return [
            'success' => true,
            'data' => []
        ];
    }

    public function edit($id)
    {
        // Проверка авторизации
        if (!$this->auth->check()) {
            return redirect('/login');
        }

        try {
            Log::channel('debug')->info("Начало загрузки формы редактирования системы ID: " . $id);

            // Получение данных через сервис
            $formData = SystemManager::getFormData($id);
            Log::channel('debug')->info("Данные формы успешно получены", array_keys($formData));

            // Рендеринг Blade шаблона
            return view('systems.edit', array_merge($formData, [
                'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
                'userRole' => $this->auth->user()->position ?? 'Пользователь'
            ]));

        } catch (\Exception $e) {
            Log::error("Ошибка в методе edit: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            abort(500, 'Ошибка при загрузке формы редактирования: ' . $e->getMessage());
        }
    }
}