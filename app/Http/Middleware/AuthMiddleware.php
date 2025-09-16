<?php

namespace App\Http\Middleware;

use App\Core\AuthInterface;
use App\Core\Database;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    private $auth;
    private $database;

    public function __construct(AuthInterface $auth, Database $database)
    {
        $this->auth = $auth;
        $this->database = $database;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Пропускаем публичные маршруты без проверки
        $publicRoutes = ['login', 'logout'];
        if (in_array($request->route()->getName(), $publicRoutes)) {
            return $next($request);
        }

        if (!$this->auth->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized - please login'], 401);
            }

            return redirect()->route('login');
        }

        // Устанавливаем контекст пользователя в БД
        if ($userId = $this->auth->getUserId()) {
            // Установка app.current_user_id в PostgreSQL
            $this->database->setCurrentUserId($userId);
        }

        return $next($request);
    }
}