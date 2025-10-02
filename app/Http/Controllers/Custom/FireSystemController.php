<?php
namespace App\Http\Controllers\Custom;

use App\Services\FireSystemService;
use App\Core\AuthInterface;
use App\Core\Database;
class FireSystemController
{
    private $fireSystemService;
    private $fireSystemRepository;
    private $auth;
    private $database;

    public function __construct(FireSystemService $fireSystemService, AuthInterface $auth, Database $database, )
    {
        $this->fireSystemService = $fireSystemService;
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
    public function update($id)
    {
        if (!$this->auth->check()) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        $user = $this->auth->user();
        $userId = $user->user_id;

        error_log("Authenticated user ID: " . $userId);
        error_log("User object: " . json_encode($user));

        // ПРОВЕРКА: Устанавливаем ли мы правильного пользователя?
        $this->database->setCurrentUserId($userId);

        // Дополнительная проверка: что БД видит как current_user_id
        try {
            $currentUser = $this->database->fetch("SELECT current_setting('app.current_user_id', true) as current_user");
            error_log("DB current_user_id setting: " . ($currentUser['current_user'] ?? 'NOT SET'));
        } catch (\Exception $e) {
            error_log("Failed to get current_user_id: " . $e->getMessage());
        }

        $requestData = request()->all();
        $system = $this->fireSystemService->updateSystem($id, $requestData);

        return response()->json([
            'success' => true,
            'message' => 'Система успешно обновлена',
            'data' => $system,
            'redirect' => route('system.show', $system->systemId)
        ]);
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

    public function edit($id)
    {
        // Проверка авторизации
        if (!$this->auth->check()) {
            return redirect('/login');
        }

        try {
            // Получение данных через сервис
            $formData = $this->fireSystemService->getFormData($id);

            // Рендеринг Blade шаблона
            return view('systems.edit', array_merge($formData, [
                'userFullName' => $this->auth->user()->full_name ?? 'Пользователь',
                'userRole' => $this->auth->user()->position ?? 'Пользователь'
            ]));

        } catch (\Exception $e) {
            abort(500, 'Ошибка при загрузке формы редактирования');
        }
    }
}