<?php
namespace App\Http\Controllers\Custom;

use App\Services\FireSystemService;
use App\Core\AuthInterface;

class FireSystemController
{
    private $fireSystemService;
    private $auth;

    public function __construct(FireSystemService $fireSystemService, AuthInterface $auth)
    {
        $this->fireSystemService = $fireSystemService;
        $this->auth = $auth;
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

    public function show($id)
    {
        if (!$this->auth->check()) {
            return redirect('/login');
        }

        try {
            // Используем сервис для получения данных
            $fireSystemService = app(FireSystemService::class);
            $systemDetails = $fireSystemService->getSystemWithDetails($id);

            return view('systems.show', [
                'system' => $systemDetails['system'], // Объект FireSystem
                'object' => $systemDetails['object'], // Объект ProtectionObject
                'equipment' => $systemDetails['equipment'], // Коллекция Equipment
                'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
                'userRole' => $this->auth->user()->position ?? 'Пользователь'
            ]);

        } catch (\Exception $e) {
            abort(500, 'Ошибка при загрузке системы: ' . $e->getMessage());
        }
    }
    // public function show($id)
    // {
    //     if (!$this->auth->check()) {
    //         return redirect('/login');
    //     }

    //     try {
    //         // Простые данные для теста (замените на реальные из БД)
    //         $system = [
    //             'id' => $id,
    //             'name' => 'Тестовая система ' . $id,
    //             'type' => 'АУПТ',
    //             'status' => 'Исправна',
    //             'inventory_number' => 'INV-' . $id,
    //             'branch_name' => 'ЛПУМГ',
    //             'responsible_person' => 'Иванов И.И.',
    //             'last_check_date' => '2024-01-15'
    //         ];

    //         return view('systems.show', [
    //             'system' => $system,
    //             'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
    //             'userRole' => $this->auth->user()->position ?? 'Пользователь'
    //         ]);

    //     } catch (\Exception $e) {
    //         abort(500, 'Ошибка при загрузке системы');
    //     }
    // }


    // public function show($params)
    // {
    //     if (!$this->auth->check()) {
    //         return ['error' => 'Не авторизован'];
    //     }

    //     try {
    //         $uuid = $params['uuid'] ?? null;
    //         if (!$uuid) {
    //             return [
    //                 'success' => false,
    //                 'error' => 'UUID системы не указан'
    //             ];
    //         }

    //         $systemDetails = $this->fireSystemService->getSystemWithDetails($uuid);

    //         return [
    //             'success' => true,
    //             'data' => $systemDetails
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ];
    //     }
    // }



    // public function showPage($uuid)
    // {
    //     \Log::info('showPage called with UUID: ' . $uuid);

    //     if (!$this->auth->check()) {
    //         \Log::warning('User not authenticated');
    //         return redirect('/login');
    //     }

    //     try {
    //         $result = $this->show(['uuid' => $uuid]);
    //         \Log::info('API result: ', $result);

    //         if (!$result['success']) {
    //             \Log::error('API error: ' . ($result['error'] ?? 'Unknown'));
    //             abort(404, $result['error']);
    //         }

    //         return view('systems.show', [
    //             'system' => $result['data'],
    //             'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
    //             'userRole' => $this->auth->user()->position ?? 'Пользователь'
    //         ]);

    //     } catch (\Exception $e) {
    //         \Log::error('showPage exception: ' . $e->getMessage());
    //         \Log::error('Stack trace: ' . $e->getTraceAsString());
    //         abort(500, 'Ошибка при загрузке системы');
    //     }
    // }

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

    /* Обновить систему */
    public function update($params)
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        try {
            $uuid = $params['uuid'] ?? null;
            if (!$uuid) {
                return [
                    'success' => false,
                    'error' => 'UUID системы не указан'
                ];
            }

            $request = $_POST; // или json_decode(file_get_contents('php://input'), true)
            $system = $this->fireSystemService->updateSystem($uuid, $request);

            return [
                'success' => true,
                'message' => 'Система успешно обновлена',
                'data' => $system
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /* Удалить систему */
    public function destroy($params)
    {
        if (!$this->auth->check()) {
            return ['error' => 'Не авторизован'];
        }

        $uuid = $params['uuid'] ?? null;
        if (!$uuid) {
            return [
                'success' => false,
                'error' => 'UUID системы не указан'
            ];
        }

        // Здесь будет логика удаления системы
        // Пока заглушка - в реальности нужно вызвать соответствующий метод сервиса

        return [
            'success' => true,
            'message' => 'Система удалена'
        ];
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


}