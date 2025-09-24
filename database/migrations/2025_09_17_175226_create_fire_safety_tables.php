<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Справочники
        Schema::create('branches', function (Blueprint $table) {
            $table->smallInteger('branch_id')->primary();
            $table->string('name', 200);
            $table->string('short_name', 50)->nullable();
        });

        Schema::create('object_groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->string('name', 200);
            $table->text('description')->nullable();
        });

        Schema::create('curators', function (Blueprint $table) {
            $table->id('curator_id');
            $table->string('name', 50);
        });

        Schema::create('system_types', function (Blueprint $table) {
            $table->id('type_id');
            $table->string('name', 20);
            $table->string('description', 200)->nullable();
        });

        Schema::create('system_subtypes', function (Blueprint $table) {
            $table->id('subtype_id');
            $table->foreignId('type_id')->constrained('system_types', 'type_id')->onDelete('cascade');
            $table->string('name', 50);
            $table->string('description', 200)->nullable();
        });

        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id('type_id');
            $table->string('name', 100);
        });

        Schema::create('design_organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->string('name', 200);
            $table->string('short_name', 20)->nullable();
        });

        Schema::create('installation_organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->string('name', 200);
            $table->string('short_name', 20)->nullable();
        });

        Schema::create('regulations', function (Blueprint $table) {
            $table->id('regulation_id');
            $table->string('code', 100);
            $table->string('name', 300);
        });

        // Пользователи/роли
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('name', 100)->unique();
            $table->text('description')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->integer('user_id')->autoIncrement();
            $table->string('username', 100)->unique();
            $table->string('password_hash', 200);
            $table->string('full_name', 200);
            $table->string('email', 200)->nullable();
            $table->smallInteger('branch_id')->nullable()->constrained('branches', 'branch_id');
            $table->foreignId('curator_id')->nullable()->constrained('curators', 'curator_id');
            $table->enum('position', [
                'главный инженер',
                'начальник САиМО',
                'инженер по КИПиА',
                'начальник цеха',
                'руководитель группы',
                'инженер',
                'начальник СПКР',
                'заместитель начальника СПКР',
                'заместитель начальника',
                'ведущий инженер'
            ])->charset('utf8mb4');
            ;
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles', 'role_id')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // Основные сущности
        Schema::create('protection_objects', function (Blueprint $table) {
            $table->id('object_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->smallInteger('branch_id')->constrained('branches', 'branch_id');
            $table->string('name', 200);
            $table->string('short_name', 100)->nullable();
            $table->foreignId('object_group_id')->constrained('object_groups', 'group_id');
            $table->foreignId('curator_id')->constrained('curators', 'curator_id');
            $table->string('inventory_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        Schema::create('fire_systems', function (Blueprint $table) {
            $table->id('system_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('object_id')->nullable()->constrained('protection_objects', 'object_id')->onDelete('set null');
            $table->foreignId('subtype_id')->nullable()->constrained('system_subtypes', 'subtype_id')->onDelete('set null');
            $table->boolean('is_part_of_object')->default(false);
            $table->string('system_inventory_number', 50)->nullable();
            $table->string('name', 200)->nullable();
            $table->text('manual_file_link')->nullable();
            $table->text('maintenance_schedule_file_link')->nullable();
            $table->text('test_program_file_link')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы для fire_systems
        Schema::table('fire_systems', function (Blueprint $table) {
            $table->index('object_id');
            $table->index('system_inventory_number');
            $table->index('record_uuid');
        });

        Schema::create('implemented_projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->string('project_code', 100);
            $table->integer('development_year');
            $table->foreignId('design_org_id')->constrained('design_organizations', 'org_id');
            $table->foreignId('regulation_id')->constrained('regulations', 'regulation_id');
            $table->text('project_file_link')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        Schema::create('repairs', function (Blueprint $table) {
            $table->id('repair_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->enum('work_type', ['КР', 'ТР', 'РС']);
            $table->enum('execution_method', ['ХС', 'ПС']);
            $table->integer('planned_year');
            $table->enum('status', ['заявлен', 'в плане', 'выполнен', 'ожидает проверки', 'проверка пройдена', 'отклонено']);
            $table->decimal('cost', 14, 2)->nullable();
            $table->foreignId('installation_org_id')->nullable()->constrained('installation_organizations', 'org_id');
            $table->date('completion_date')->nullable();
            $table->text('act_file_link')->nullable();
            $table->text('equipment_list_file_link')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы для repairs
        Schema::table('repairs', function (Blueprint $table) {
            $table->index('system_id');
            $table->index('status');
            $table->index('planned_year');
        });

        Schema::create('mounts', function (Blueprint $table) {
            $table->id('mount_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->foreignId('installation_org_id')->constrained('installation_organizations', 'org_id');
            $table->date('commission_date');
            $table->text('act_file_link');
            $table->text('equipment_list_file_link')->nullable();
            $table->enum('status', ['ожидает проверки', 'проверка пройдена', 'отклонено'])->default('ожидает проверки');
            $table->foreignId('repair_id')->nullable()->constrained('repairs', 'repair_id')->onDelete('set null');
            $table->enum('repair_work_type', ['КР', 'ТР', 'РС'])->nullable();
            $table->enum('repair_execution_method', ['ХС', 'ПС'])->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        Schema::create('system_activations', function (Blueprint $table) {
            $table->id('system_activation_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->text('location')->nullable();
            $table->timestamp('activation_date');
            $table->string('reported_by', 200)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы для system_activations
        Schema::table('system_activations', function (Blueprint $table) {
            $table->index(['system_id', 'activation_date']);
        });

        Schema::create('system_maintenance', function (Blueprint $table) {
            $table->id('maintenance_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->enum('maintenance_type', ['техническое обслуживание', 'комплексное испытание', 'другое']);
            $table->date('maintenance_date');
            $table->string('maintenance_by', 200)->nullable();
            $table->text('test_act_file_link')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['ожидает проверки', 'проверка пройдена', 'отклонено'])->default('ожидает проверки');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы для system_maintenance
        Schema::table('system_maintenance', function (Blueprint $table) {
            $table->index(['system_id', 'maintenance_date']);
        });

        Schema::create('equipments', function (Blueprint $table) {
            $table->id('equipment_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->foreignId('type_id')->constrained('equipment_types', 'type_id');
            $table->string('model', 200);
            $table->string('serial_number', 200)->nullable();
            $table->string('location', 200)->nullable();
            $table->integer('quantity')->default(1);
            $table->smallInteger('production_year');
            $table->smallInteger('production_quarter')->nullable();
            $table->integer('service_life_years');
            $table->string('control_period', 100)->nullable();
            $table->date('last_control_date')->nullable();
            $table->string('control_result', 200)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы и уникальные ограничения для equipments
        Schema::table('equipments', function (Blueprint $table) {
            $table->index('system_id');
            $table->index('type_id');
            $table->index([DB::raw('(production_year + service_life_years)')], 'equipments_expiration_index');
            $table->unique(['system_id', 'type_id', 'model', 'serial_number']);
        });

        Schema::create('new_projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->uuid('record_uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('system_id')->constrained('fire_systems', 'system_id')->onDelete('cascade');
            $table->enum('development_method', ['хозяйственный', 'подрядный']);
            $table->foreignId('regulation_id')->constrained('regulations', 'regulation_id');
            $table->integer('planned_year');
            $table->enum('status', ['заявлен', 'в плане', 'разработан', 'ожидает проверки', 'проверка пройдена', 'отклонено']);
            $table->foreignId('design_org_id')->nullable()->constrained('design_organizations', 'org_id');
            $table->string('project_code', 100)->nullable();
            $table->text('project_file_link')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'user_id');
            $table->timestamps();
        });

        // Индексы для new_projects
        Schema::table('new_projects', function (Blueprint $table) {
            $table->index('system_id');
            $table->index('status');
        });

        // Логирование и подтверждения
        Schema::create('change_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->string('table_name', 100);
            $table->uuid('record_uuid');
            $table->enum('action', ['INSERT', 'UPDATE', 'DELETE']);
            $table->jsonb('changed_fields')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users', 'user_id');
            $table->timestamp('changed_at')->useCurrent();
        });

        // Индексы для change_log
        Schema::table('change_log', function (Blueprint $table) {
            $table->index(['table_name', 'record_uuid']);
            $table->index('changed_at');
        });

        Schema::create('approval_history', function (Blueprint $table) {
            $table->id('approval_id');
            $table->string('table_name', 50);
            $table->integer('record_id');
            $table->enum('curator_type', ['ПОА', 'УЭЗС', 'ИТЦ']);
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Обратный порядок удаления таблиц
        Schema::dropIfExists('approval_history');
        Schema::dropIfExists('change_log');
        Schema::dropIfExists('new_projects');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('system_maintenance');
        Schema::dropIfExists('system_activations');
        Schema::dropIfExists('mounts');
        Schema::dropIfExists('repairs');
        Schema::dropIfExists('implemented_projects');
        Schema::dropIfExists('fire_systems');
        Schema::dropIfExists('protection_objects');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('regulations');
        Schema::dropIfExists('installation_organizations');
        Schema::dropIfExists('design_organizations');
        Schema::dropIfExists('equipment_types');
        Schema::dropIfExists('system_subtypes');
        Schema::dropIfExists('system_types');
        Schema::dropIfExists('curators');
        Schema::dropIfExists('object_groups');
        Schema::dropIfExists('branches');
    }
};