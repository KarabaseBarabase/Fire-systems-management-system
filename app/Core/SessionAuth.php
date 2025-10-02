<?php
namespace App\Core;

class SessionAuth implements AuthInterface
{
    private $sessionName;
    private $sessionLifetime;
    private $sessionStarted = false;

    public function __construct(?string $sessionName = null, ?int $sessionLifetime = null)
    {
        $this->sessionName = $sessionName ?? $_ENV['SESSION_NAME'] ?? 'app_session';
        $this->sessionLifetime = $sessionLifetime ?? $_ENV['SESSION_LIFETIME'] ?? 3600;
    }

    /* Запуск сессии с настройками безопасности */
    private function startSession(): void
    {
        if (!$this->sessionStarted && session_status() === PHP_SESSION_NONE) {
            session_start([
                'name' => $this->sessionName,
                'cookie_lifetime' => $this->sessionLifetime,
                'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'cookie_httponly' => true,
                'cookie_samesite' => 'Strict',
                'use_strict_mode' => true,
                'use_only_cookies' => true,
                'gc_maxlifetime' => $this->sessionLifetime
            ]);

            $this->sessionStarted = true;

            // Обновляем время жизни cookie при каждом запросе
            setcookie(
                $this->sessionName,
                session_id(),
                [
                    'expires' => time() + $this->sessionLifetime,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );
        }
    }

    /* Аутентификация пользователя */
    public function login($user): bool
    {
        try {
            $this->startSession(); // Добавляем здесь

            // Регенерируем ID сессии для защиты
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['user_data'] = [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'branch_id' => $user->branch_id,
                'position' => $user->position
            ];

            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            return true;
        } catch (\Exception $e) {
            error_log("SessionAuth login error: " . $e->getMessage());
            return false;
        }
    }

    /* Выход пользователя */
    public function logout(): bool
    {
        try {
            $this->startSession(); // Добавляем здесь

            // Очищаем данные сессии
            $_SESSION = [];

            // Удаляем cookie сессии
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    $this->sessionName,
                    '',
                    [
                        'expires' => time() - 42000,
                        'path' => $params['path'],
                        'domain' => $params['domain'],
                        'secure' => $params['secure'],
                        'httponly' => $params['httponly'],
                        'samesite' => $params['samesite']
                    ]
                );
            }

            // Уничтожаем сессию
            session_destroy();
            $this->sessionStarted = false;

            return true;
        } catch (\Exception $e) {
            error_log("SessionAuth logout error: " . $e->getMessage());
            return false;
        }
    }

    /* Проверка аутентификации пользователя */
    public function check(): bool
    {
        $this->startSession(); // Добавляем здесь

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Проверка времени бездействия (30 минут)
        $inactivityTimeout = 1800;
        if (
            isset($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity']) > $inactivityTimeout
        ) {
            $this->logout();
            return false;
        }

        // Обновляем время последней активности
        $_SESSION['last_activity'] = time();

        return true;
    }

    /* Получение данных пользователя */
    public function user(): ?object
    {
        $this->startSession(); // Добавляем здесь

        if (!$this->check()) {
            return null;
        }

        return (object) ($_SESSION['user_data'] ?? null);
    }

    /* Получение ID пользователя */
    public function getUserId(): ?int
    {
        $this->startSession(); // Добавляем здесь
        return $_SESSION['user_id'] ?? null;
    }

    /* Обновление данных пользователя в сессии */
    public function updateUserData(array $data): bool
    {
        $this->startSession(); // Добавляем здесь

        if (!$this->check()) {
            return false;
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $_SESSION['user_data'])) {
                $_SESSION['user_data'][$key] = $value;
            }
        }

        return true;
    }

    /* Получение времени входа */
    public function getLoginTime(): ?int
    {
        $this->startSession(); // Добавляем здесь
        return $_SESSION['login_time'] ?? null;
    }

    /* Получение времени последней активности */
    public function getLastActivityTime(): ?int
    {
        $this->startSession(); // Добавляем здесь
        return $_SESSION['last_activity'] ?? null;
    }

    /* Проверка роли пользователя */
    public function hasRole(string $role): bool
    {
        $user = $this->user();
        return $user && isset($user->role) && $user->role === $role;
    }

    /* Проверка нескольких ролей */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();
        return $user && isset($user->role) && in_array($user->role, $roles);
    }

    /* Установка flash-сообщения */
    public function flash(string $key, $value): void
    {
        $this->startSession(); // Добавляем здесь
        $_SESSION['flash'][$key] = $value;
    }

    /* Получение flash-сообщения */
    public function getFlash(string $key)
    {
        $this->startSession(); // Добавляем здесь
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    /* Получение ID сессии */
    public function getSessionId(): string
    {
        $this->startSession(); // Добавляем здесь
        return session_id();
    }

    /* Очистка всех данных сессии */
    public function clear(): bool
    {
        try {
            $this->startSession(); // Добавляем здесь
            session_unset();
            return true;
        } catch (\Exception $e) {
            error_log("SessionAuth clear error: " . $e->getMessage());
            return false;
        }
    }

    /* Проверка истечения сессии */
    public function isExpired(): bool
    {
        $this->startSession(); // Добавляем здесь

        if (!isset($_SESSION['last_activity'])) {
            return true;
        }

        return (time() - $_SESSION['last_activity']) > $this->sessionLifetime;
    }

    public function updateLastActivity(): void
    {
        $this->startSession(); // Добавляем здесь

        if ($this->check()) {
            $_SESSION['last_activity'] = time();
        }
    }
}