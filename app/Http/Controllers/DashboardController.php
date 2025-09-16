<?php

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\AuthInterface;
use App\Data\Repositories\FireSystemRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $database;
    protected $auth;

    public function __construct(Database $database, AuthInterface $auth)
    {
        $this->database = $database;
        $this->auth = $auth;
    }

    public function index()
    {
        try {
            // Проверяем аутентификацию
            if (!$this->auth->check()) {
                // Если пользователь не аутентифицирован, то перенаправляем на логин
                return redirect('/login');
            }

            // Получаем филиалы из БД
            $branches = $this->database->fetchAll("
            SELECT branch_id as id, name 
            FROM branches 
            ORDER BY name
            ");

            // Получаем данные о пожарных системах
            $fireSystems = $this->database->fetchAll("
                SELECT 
                    fs.system_id as id,
                    fs.name,
                    st.name as type,
                    b.name as branch_name,
                    u.full_name as responsible_person,
                    fs.system_inventory_number as inventory_number,
                    MAX(sm.maintenance_date) as last_check_date
                FROM fire_systems fs
                LEFT JOIN system_subtypes ss ON fs.subtype_id = ss.subtype_id
                LEFT JOIN system_types st ON ss.type_id = st.type_id
                LEFT JOIN protection_objects po ON fs.object_id = po.object_id
                LEFT JOIN branches b ON po.branch_id = b.branch_id
                LEFT JOIN users u ON po.updated_by = u.user_id
                LEFT JOIN system_maintenance sm ON fs.system_id = sm.system_id
                GROUP BY fs.system_id, fs.name, st.name, b.name, u.full_name, fs.system_inventory_number
                ORDER BY fs.system_id
            ");

            // Получаем данные текущего пользователя из сессии
            $user = $this->auth->user();
            $userFullName = $user->full_name ?? 'Неизвестный пользователь';
            $userRole = $user->position ?? 'Пользователь';

            // Обновляем время последней активности
            $this->auth->updateLastActivity();

        } catch (\Exception $e) {
            \Log::error("Database error: " . $e->getMessage());
            $fireSystems = [];
            $userFullName = 'Ошибка базы данных';
            $userRole = 'Системная ошибка';
        }

        //return view('dashboard.index', compact('fireSystems', 'userFullName', 'userRole'));
        return view('dashboard.index', compact('branches', 'fireSystems', 'userFullName', 'userRole'));
    }

    public function modal($id)
    {
        // Проверяем аутентификацию
        if (!$this->auth->check()) {
            return redirect('/login');
        }

        // Обновляем время последней активности
        $this->auth->updateLastActivity();

        return view('dashboard.modal', ['systemId' => $id]);
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect('/login')->with('message', 'Вы успешно вышли из системы');
    }
}
