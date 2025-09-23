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
            if (!$this->auth->check()) {
                return redirect('/login');
            }

            $user = $this->auth->user();
            $userId = $user->user_id;
            $userFullName = $user->full_name;
            $userRole = $user->position;

            // Получаем филиалы
            $branches = $this->database->fetchAll("SELECT branch_id as id, name FROM branches ORDER BY name");

            // Получаем ВСЕ системы сначала
            $fireSystems = $this->database->fetchAll("
            SELECT 
                fs.record_uuid as uuid,
                fs.system_id as id,
                fs.name,
                st.name as type,
                b.name as branch_name,
                u.full_name as responsible_person,
                fs.system_inventory_number as inventory_number,
                MAX(sm.maintenance_date) as last_check_date,
                fs.record_uuid,
                po.object_id,
                po.branch_id as object_branch_id
            FROM fire_systems fs
            LEFT JOIN system_subtypes ss ON fs.subtype_id = ss.subtype_id
            LEFT JOIN system_types st ON ss.type_id = st.type_id
            LEFT JOIN protection_objects po ON fs.object_id = po.object_id
            LEFT JOIN branches b ON po.branch_id = b.branch_id
            LEFT JOIN users u ON po.updated_by = u.user_id
            LEFT JOIN system_maintenance sm ON fs.system_id = sm.system_id
            GROUP BY fs.system_id, fs.name, st.name, b.name, u.full_name, 
                     fs.system_inventory_number, fs.record_uuid, po.object_id, 
                     po.branch_id
            ORDER BY fs.system_id
        ");

            // Фильтруем системы по правам доступа просмотра
            $filteredSystems = [];
            foreach ($fireSystems as $system) {
                try {
                    // Проверяем права просмотра для каждой системы
                    $canView = $this->checkViewPermission($userId, $system['record_uuid']);

                    if ($canView) {
                        $filteredSystems[] = $system;
                    }

                } catch (\Exception $e) {
                    \Log::warning("Permission check failed for system {$system['id']}: " . $e->getMessage());
                    // Fallback: используем базовую проверку
                    // if ($this->basicViewCheck($userId, $userRole, $system)) {
                    //     $filteredSystems[] = $system;
                    // }
                }
            }

            $this->auth->updateLastActivity();

        } catch (\Exception $e) {
            \Log::error("Database error in index(): " . $e->getMessage());
            $filteredSystems = [];
            $branches = [];
            $userFullName = 'Ошибка системы';
            $userRole = 'Ошибка';
            session()->flash('error', 'Ошибка загрузки данных');
        }

        return view('dashboard.index', [
            'branches' => $branches,
            'fireSystems' => $filteredSystems,
            'userFullName' => $userFullName,
            'userRole' => $userRole
        ]);
    }
    private function checkViewPermission($userId, $recordUuid)
    {
        try {
            $result = $this->database->fetch("
            SELECT check_approval_permission(:user_id, :record_uuid, 'fire_systems', 'view') as can_view
        ", ['user_id' => $userId, 'record_uuid' => $recordUuid]);

            return $result ? (bool) $result['can_view'] : false;

        } catch (\Exception $e) {
            \Log::error('Error checking view permission: ' . $e->getMessage());
            return false;
        }
    }

    private function checkEditPermission($userId, $recordUuid)
    {
        try {
            $result = $this->database->fetch("
            SELECT check_approval_permission(:user_id, :record_uuid, 'fire_systems', 'edit') as can_edit
        ", ['user_id' => $userId, 'record_uuid' => $recordUuid]);

            return $result ? (bool) $result['can_edit'] : false;

        } catch (\Exception $e) {
            \Log::error('Error checking edit permission: ' . $e->getMessage());
            return $this->basicRoleCheck($this->auth->user()->position);
        }
    }

    private function basicViewCheck($userId, $userRole, $system)
    {
        // Fallback проверка прав просмотра
        $user = $this->auth->user();

        // Если пользователь имеет роль view_all
        if ($this->hasRole($userId, 'view_all')) {
            return true;
        }

        // Если пользователь имеет роль view_branch_analytics и система в его филиале
        if ($this->hasRole($userId, 'view_branch_analytics') && $user->branch_id == $system['object_branch_id']) {
            return true;
        }

        // Если пользователь куратор объекта
        if ($user->curator_id == $system['object_curator_id']) {
            return true;
        }

        return false;
    }

    private function hasRole($userId, $roleName)
    {
        try {
            $result = $this->database->fetch("
            SELECT 1 FROM user_roles ur
            JOIN roles r ON ur.role_id = r.role_id
            WHERE ur.user_id = :user_id AND r.name = :role_name
        ", ['user_id' => $userId, 'role_name' => $roleName]);

            return (bool) $result;

        } catch (\Exception $e) {
            \Log::error('Error checking role: ' . $e->getMessage());
            return false;
        }
    }

    private function basicRoleCheck($userRole)
    {
        $allowedRoles = ['инженер', 'начальник', 'администратор', 'engineer', 'chief', 'admin'];
        return in_array(strtolower($userRole), array_map('strtolower', $allowedRoles));
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
