<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WorkSeeder extends Seeder
{
    public function run()
    {
        $this->truncateTables();

        // Примеры пользователей
        $users = [
            [
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'full_name' => 'Администратор Системы',
                'email' => 'admin@company.ru',
                'branch_id' => 1,
                'curator_id' => 1,
                'position' => 'главный инженер',
                'is_active' => true
            ]
        ];

        foreach ($users as $user) {
            $userId = DB::table('users')->insertGetId($user);

            DB::table('user_roles')->insert([
                ['user_id' => $userId, 'role_id' => 1],
                ['user_id' => $userId, 'role_id' => 4],
            ]);
        }

        // Пример объекта защиты
        $objectId = DB::table('protection_objects')->insertGetId([
            'branch_id' => 1,
            'name' => 'Тестовый объект',
            'short_name' => 'Тест',
            'object_group_id' => 1,
            'curator_id' => 1,
            'inventory_number' => 'OBJ-001',
            'record_uuid' => Str::uuid()
        ]);

        // Пример системы
        $systemId = DB::table('fire_systems')->insertGetId([
            'object_id' => $objectId,
            'subtype_id' => 1,
            'is_part_of_object' => false,
            'system_inventory_number' => 'SYS-001',
            'name' => 'Тестовая система',
            'record_uuid' => Str::uuid()
        ]);
    }

    private function truncateTables()
    {
        $tables = [
            'user_roles',
            'users',
            'protection_objects',
            'fire_systems',
            'implemented_projects',
            'repairs',
            'mounts',
            'system_activations',
            'system_maintenance',
            'equipments',
            'new_projects',
        ];

        foreach ($tables as $table) {
            if (\Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
    }
}