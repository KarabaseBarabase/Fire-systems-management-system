<?php
namespace App\Core;

use App\Core\AuthInterface;
use App\Core\Database;

abstract class Controller
{
    protected $db;
    protected $auth;
    protected $request;
    protected $response;

    public function __construct(Database $db, AuthInterface $auth)
    {
        $this->db = $db;
        $this->auth = $auth;
        $this->initializeRequest();
    }

    /* Инициализация данных запроса */
    protected function initializeRequest(): void
    {
        $this->request = [
            'method' => $_SERVER['REQUEST_METHOD'],
            'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
            'query' => $_GET,
            'body' => $this->getRequestBody(),
            'headers' => getallheaders()
        ];
    }

    /* Получение тела запроса в зависимости от Content-Type */
    protected function getRequestBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            return json_decode(file_get_contents('php://input'), true) ?? [];
        }

        return $_POST;
    }

    /* Отправка JSON-ответа */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /* Проверка аутентификации */
    protected function requireAuth(): void
    {
        if (!$this->auth->check()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
    }

    /* Проверка прав доступа */
    protected function checkPermission(string $permission, $resource = null): bool
    {
        // Здесь можно интегрировать с вашей системой прав
        return true; // Заглушка
    }

    /* Перенаправление */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }

    /* Рендеринг представления (если используется шаблонизатор) */
    protected function render(string $view, array $data = []): void
    {
        // Заглушка - реализация зависит от вашего шаблонизатора
        extract($data);
        require __DIR__ . "/../Views/$view.php";
    }

    /* Установка пользователя в контекст БД */
    protected function setDbUserContext(int $userId): void
    {
        $this->db->setCurrentUserId($userId);
    }
}