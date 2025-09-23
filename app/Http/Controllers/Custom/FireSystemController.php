<?php
namespace App\Http\Controllers\Custom;

use App\Services\FireSystemService;
use App\Core\AuthInterface;
use App\Data\Repositories;
class FireSystemController
{
    private $fireSystemService;
    private $fireSystemRepository;
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
            $fireSystemService = app(FireSystemService::class);
            $systemDetails = $fireSystemService->getSystemWithDetails($id);

            return view('systems.show', [
                'system' => $systemDetails['system'],
                'object' => $systemDetails['object'],
                'equipment' => $systemDetails['equipment'],
                'repairs' => $systemDetails['repairs'],
                'maintenance' => $systemDetails['maintenance'],
                'activations' => $systemDetails['activations'],
                'mounts' => $systemDetails['mounts'],
                'projects' => $systemDetails['projects'], // Реализованные проекты
                'branch' => $systemDetails['branch'],
                'subtype' => $systemDetails['subtype'],
                'system_type' => $systemDetails['system_type'],
                'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
                'userRole' => $this->auth->user()->position ?? 'Пользователь',
                'documents' => $systemDetails['documents'],
                'history' => $systemDetails['history'],
                'plans' => $systemDetails['plans'] // Новые проекты
            ]);

        } catch (\Exception $e) {
            abort(500, 'Ошибка при загрузке системы: ' . $e->getMessage());
        }
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
    public function destroy($uuid)
    {
        try {
            \Log::info("Начало удаления системы", ['uuid' => $uuid]);

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
            \Log::info("Удаление завершено", [
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


}