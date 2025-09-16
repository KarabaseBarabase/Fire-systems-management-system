<?php

namespace App\Http\Controllers\Custom;

use App\Core\AuthInterface;
use App\Core\Database;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct(AuthInterface $auth, Database $database)
    {
        $this->auth = $auth;
        $this->database = $database;
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $sql = "SELECT * FROM users WHERE username = :username AND is_active = true";
            $params = ['username' => $credentials['username']];

            $user = $this->database->fetch($sql, $params);

            // if ($user) {
            //     \Log::info('DEBUG Password check:', [
            //         'username' => $credentials['username'],
            //         'password_provided' => $credentials['password'],
            //         'password_hash_in_db' => $user['password_hash'],
            //         'password_match' => password_verify($credentials['password'], $user['password_hash']),
            //         'hash_algorithm' => password_get_info($user['password_hash'])['algo'] ?? 'unknown'
            //     ]);
            // }

            if ($user && $credentials['password'] === $user['password_hash']) {
                $this->auth->login((object) $user);
                return redirect()->route('dashboard');
            }

            return back()->withErrors(['login' => 'Неверные учетные данные']);

        } catch (\Exception $e) {
            \Log::error("Login error: " . $e->getMessage());
            return back()->withErrors(['login' => 'Ошибка при входе в систему']);
        }
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect('/login')->with('success', 'Вы успешно вышли из системы');
    }
}


