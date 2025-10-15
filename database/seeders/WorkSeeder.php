<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WorkSeeder extends Seeder
{
    public function run()
    {
        $this->truncateTables();
        // Проверяем существование функции
        $functionExists = DB::selectOne("
        SELECT 1 FROM pg_proc 
        WHERE proname = 'assign_roles_to_user' 
        AND pronargs = 1
    ");

        if (!$functionExists) {
            Log::warning('Функция assign_roles_to_user не найдена!');
            return;
        }
        // Примеры групп объектов
        $objectGroups = [
            [1, 'Производственные здания', 'Производственные и технологические объекты'],
            [2, 'Административные здания', 'Офисные и административные помещения'],
            [3, 'Склады', 'Складские помещения и терминалы'],
            [4, 'Насосные станции', 'Насосные и компрессорные станции'],
            [5, 'КИП', 'Объекты контрольно-измерительных приборов'],
        ];

        foreach ($objectGroups as $group) {
            DB::table('object_groups')->updateOrInsert(
                ['group_id' => $group[0]],
                ['name' => $group[1], 'description' => $group[2]]
            );
        }

        // installation_organizations
        $installationOrgs = [
            [1, 'ООО "МонтажСервис"', 'МС'],
            [2, 'ЗАО "ТехноМонтаж"', 'ТМ'],
            [3, 'АО "СпецМонтаж"', 'СМ'],
            [4, 'Хозяйственный способ', 'ХС'],
        ];

        foreach ($installationOrgs as $org) {
            DB::table('installation_organizations')->updateOrInsert(
                ['org_id' => $org[0]],
                ['name' => $org[1], 'short_name' => $org[2]]
            );
        }

        // design_organizations (если тоже используются)
        $designOrgs = [
            [1, 'ООО "ПроектСервис"', 'ПС'],
            [2, 'АО "ИнжПроект"', 'ИП'],
            [3, 'ИТЦ СПКР', 'ИТЦ'],
        ];

        foreach ($designOrgs as $org) {
            DB::table('design_organizations')->updateOrInsert(
                ['org_id' => $org[0]],
                ['name' => $org[1], 'short_name' => $org[2]]
            );
        }

        // regulations (если используются)
        $regulations = [
            [1, 'НПБ 88-2001', 'Установки пожаротушения и сигнализации. Нормы и правила проектирования'],
            [2, 'СП 5.13130.2009', 'Системы противопожарной защиты'],
            [3, 'ФЗ-123', 'Технический регламент о требованиях пожарной безопасности'],
        ];

        foreach ($regulations as $regulation) {
            DB::table('regulations')->updateOrInsert(
                ['regulation_id' => $regulation[0]],
                ['code' => $regulation[1], 'name' => $regulation[2]]
            );
        }

        // Примеры пользователей
        $users = [
            [
                'user_id' => 1,
                'username' => 'admin',
                'password_hash' => Hash::make('admin123'),
                'full_name' => 'Администратор Системы',
                'email' => 'admin@company.ru',
                'branch_id' => 1,
                'curator_id' => 1,
                'position' => 'главный инженер',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 2,
                'username' => 'engineer1',
                'password_hash' => Hash::make('engineer123'),
                'full_name' => 'Иванов Иван Иванович',
                'email' => 'engineer1@company.ru',
                'branch_id' => 2,
                'curator_id' => 1,
                'position' => 'инженер по КИПиА',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'user_id' => 3,
                'username' => 'engineer2',
                'password_hash' => Hash::make('engineer123'),
                'full_name' => 'Петров Петр Петрович',
                'email' => 'engineer2@company.ru',
                'branch_id' => 3,
                'curator_id' => 2,
                'position' => 'инженер',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('users')->insert($users);

        // Примеры объектов защиты
        $protectionObjects = [
            [
                'object_id' => 1,
                'branch_id' => 2,
                'name' => 'Основное производственное здание',
                'short_name' => 'Главный корпус',
                'object_group_id' => 1,
                'curator_id' => 1,
                'inventory_number' => 'OBJ-001',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'object_id' => 2,
                'branch_id' => 2,
                'name' => 'Склад готовой продукции',
                'short_name' => 'Склад №1',
                'object_group_id' => 3,
                'curator_id' => 1,
                'inventory_number' => 'OBJ-002',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'object_id' => 3,
                'branch_id' => 3,
                'name' => 'Административное здание',
                'short_name' => 'Офис',
                'object_group_id' => 2,
                'curator_id' => 2,
                'inventory_number' => 'OBJ-003',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('protection_objects')->insert($protectionObjects);

        // Примеры систем пожаротушения
        $fireSystems = [
            [
                'system_id' => 1,
                'object_id' => 1,
                'subtype_id' => 1,
                'is_part_of_object' => false,
                'system_inventory_number' => 'SYS-001',
                'name' => 'Адресная АПС производственного цеха',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'system_id' => 2,
                'object_id' => 1,
                'subtype_id' => 3,
                'is_part_of_object' => false,
                'system_inventory_number' => 'SYS-002',
                'name' => 'СОУЭ тип 3 производственного цеха',
                'record_uuid' => Str::uuid(),
                'createdated_at' => now(),
                'updated_at' => now()
            ],
            [
                'system_id' => 3,
                'object_id' => 2,
                'subtype_id' => 9,
                'is_part_of_object' => false,
                'system_inventory_number' => 'SYS-003',
                'name' => 'АУПТ пенного типа склада',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('fire_systems')->insert($fireSystems);

        // Примеры оборудования
        $equipments = [
            [
                'equipment_id' => 1,
                'system_id' => 1,
                'type_id' => 1,
                'model' => 'ППКУП-М',
                'serial_number' => 'PPK-001',
                'location' => 'Щитовая №1',
                'quantity' => 1,
                'production_year' => 2020,
                'production_quarter' => 2,
                'service_life_years' => 10,
                'control_period' => 'ежегодно',
                'last_control_date' => '2023-06-15',
                'control_result' => 'исправен',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'equipment_id' => 2,
                'system_id' => 1,
                'type_id' => 4,
                'model' => 'ДИП-34А',
                'serial_number' => 'DIP-001',
                'location' => 'Цех, секция А',
                'quantity' => 12,
                'production_year' => 2020,
                'production_quarter' => 2,
                'service_life_years' => 8,
                'control_period' => 'ежеквартально',
                'last_control_date' => '2023-09-10',
                'control_result' => 'исправен',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('equipments')->insert($equipments);

        // Примеры ремонтов
        $repairs = [
            [
                'repair_id' => 1,
                'system_id' => 1,
                'work_type' => 'ТР',
                'execution_method' => 'ХС',
                'planned_year' => 2024,
                'status' => 'в плане',
                'cost' => 150000.00,
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'repair_id' => 2,
                'system_id' => 3,
                'work_type' => 'КР',
                'execution_method' => 'ПС',
                'planned_year' => 2024,
                'status' => 'заявлен',
                'cost' => 450000.00,
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('repairs')->insert($repairs);

        // Примеры монтажных работ
        $mounts = [
            [
                'mount_id' => 1,
                'system_id' => 1,
                'installation_org_id' => 1,
                'commission_date' => '2020-06-15',
                'act_file_link' => '/docs/acts/act_001.pdf',
                'equipment_list_file_link' => '/docs/lists/list_001.pdf',
                'status' => 'проверка пройдена',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('mounts')->insert($mounts);

        // Примеры технического обслуживания
        $maintenance = [
            [
                'maintenance_id' => 1,
                'system_id' => 1,
                'maintenance_type' => 'техническое обслуживание',
                'maintenance_date' => '2023-12-01',
                'maintenance_by' => 'Сервисная компании "Охрана"',
                'test_act_file_link' => '/docs/maintenance/act_001.pdf',
                'status' => 'проверка пройдена',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('system_maintenance')->insert($maintenance);

        // Примеры активаций систем
        $activations = [
            [
                'system_activation_id' => 1,
                'system_id' => 1,
                'location' => 'Цех, участок покраски',
                'activation_date' => now()->subDays(30),
                'reported_by' => 'Мастер участка Сидоров А.И.',
                'notes' => 'Ложное срабатывание из-за пыли',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('system_activations')->insert($activations);

        // Примеры новых проектов
        $newProjects = [
            [
                'project_id' => 1,
                'system_id' => 2,
                'development_method' => 'подрядный',
                'regulation_id' => 1,
                'planned_year' => 2024,
                'status' => 'заявлен',
                'record_uuid' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('new_projects')->insert($newProjects);

        Log::info('Проверка создания функций...');
        try {
            $functionExists = DB::selectOne("
            SELECT 1 FROM pg_proc 
            WHERE proname = 'assign_roles_to_user' 
            AND pronargs = 1
        ");

            if ($functionExists) {
                Log::info('Функция assign_roles_to_user успешно создана в миграции');
            } else {
                Log::error('Функция assign_roles_to_user НЕ создана в миграции!');
            }

        } catch (\Exception $e) {
            Log::error('Ошибка проверки функции: ' . $e->getMessage());
        }
    }

    private function truncateTables()
    {
        $tables = [
            'user_roles', // Очищаем таблицу ролей пользователей
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

        DB::statement('SET session_replication_role = replica;');
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        DB::statement('SET session_replication_role = origin;');
    }
}