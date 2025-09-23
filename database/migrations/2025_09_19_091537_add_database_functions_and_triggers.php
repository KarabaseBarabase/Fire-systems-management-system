<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        try {
            DB::unprepared("
            CREATE OR REPLACE FUNCTION trigger_set_updated_at()
            RETURNS TRIGGER AS $$
            DECLARE v_user_id INTEGER;
            BEGIN
                IF TG_OP = 'UPDATE' THEN
                    NEW.updated_at = CURRENT_TIMESTAMP;
                    BEGIN
                        v_user_id := current_setting('app.current_user_id', true)::INTEGER;
                        NEW.updated_by = v_user_id;
                    EXCEPTION WHEN others THEN NULL;
                    END;
                END IF;
                RETURN NEW;
            END; $$ LANGUAGE plpgsql;

            CREATE OR REPLACE FUNCTION fn_log_changes()
            RETURNS TRIGGER AS $$
            DECLARE
                changes  JSONB := '{}'::JSONB;
                rec_uuid UUID;
                user_id  INTEGER;
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    rec_uuid := NEW.record_uuid;
                    changes  := to_jsonb(NEW);

                ELSIF TG_OP = 'UPDATE' THEN
                    rec_uuid := NEW.record_uuid;
                    SELECT COALESCE(jsonb_object_agg(key, value), '{}'::jsonb) INTO changes
                    FROM (
                        SELECT key, value
                        FROM jsonb_each(to_jsonb(NEW))
                        WHERE (to_jsonb(OLD)->key) IS DISTINCT FROM value
                    ) s;

                ELSIF TG_OP = 'DELETE' THEN
                    rec_uuid := OLD.record_uuid;
                    changes  := to_jsonb(OLD);
                END IF;

                BEGIN
                    user_id := current_setting('app.current_user_id', true)::INTEGER;
                EXCEPTION WHEN others THEN
                    user_id := NULL;
                END;

                IF TG_OP = 'DELETE' OR changes <> '{}'::jsonb THEN
                    INSERT INTO change_log(table_name, record_uuid, action, changed_fields, changed_by)
                    VALUES (TG_TABLE_NAME, rec_uuid, TG_OP, changes, user_id);
                END IF;

                RETURN COALESCE(NEW, OLD);
            END; 
            $$ LANGUAGE plpgsql;

            CREATE OR REPLACE FUNCTION check_approval_permission(
                p_user_id INTEGER,
                p_record_uuid UUID,
                p_table_name VARCHAR(50),
                p_action_type VARCHAR(20) DEFAULT 'view' -- view, edit, confirm
            ) RETURNS BOOLEAN AS $$
            DECLARE 
                v_curator_type VARCHAR(50);
                v_branch_id SMALLINT;
                v_user_branch_id SMALLINT;
                v_user_position VARCHAR(200);
                v_user_curator_id INTEGER;
                v_object_curator_id INTEGER;
                v_system_type VARCHAR(20);
                v_execution_method VARCHAR(10);
                v_development_method VARCHAR(20);
            BEGIN
                SELECT u.branch_id, u.position, u.curator_id 
                INTO v_user_branch_id, v_user_position, v_user_curator_id
                FROM users u 
                WHERE u.user_id = p_user_id;

                IF p_action_type != 'view' AND NOT (
                    SELECT is_active FROM users WHERE user_id = p_user_id
                ) THEN RETURN FALSE; END IF;

                IF p_table_name IN ('mounts', 'system_maintenance', 'repairs', 'new_projects', 'fire_systems') THEN
                    SELECT 
                        c.name, 
                        po.branch_id,
                        po.curator_id,
                        st.name,
                        r.execution_method,
                        np.development_method
                    INTO 
                        v_curator_type, 
                        v_branch_id,
                        v_object_curator_id,
                        v_system_type,
                        v_execution_method,
                        v_development_method
                    FROM fire_systems fs
                    JOIN protection_objects po ON fs.object_id = po.object_id
                    JOIN curators c ON po.curator_id = c.curator_id
                    LEFT JOIN system_subtypes ss ON fs.subtype_id = ss.subtype_id
                    LEFT JOIN system_types st ON ss.type_id = st.type_id
                    LEFT JOIN repairs r ON r.system_id = fs.system_id AND r.record_uuid = p_record_uuid
                    LEFT JOIN new_projects np ON np.system_id = fs.system_id AND np.record_uuid = p_record_uuid
                    WHERE fs.system_id = (
                        CASE p_table_name
                            WHEN 'mounts' THEN (SELECT system_id FROM mounts WHERE record_uuid = p_record_uuid)
                            WHEN 'system_maintenance' THEN (SELECT system_id FROM system_maintenance WHERE record_uuid = p_record_uuid)
                            WHEN 'repairs' THEN (SELECT system_id FROM repairs WHERE record_uuid = p_record_uuid)
                            WHEN 'new_projects' THEN (SELECT system_id FROM new_projects WHERE record_uuid = p_record_uuid)
                            WHEN 'fire_systems' THEN (SELECT system_id FROM fire_systems WHERE record_uuid = p_record_uuid)
                        END
                    );
                END IF;


                IF p_action_type = 'view' THEN
                    IF EXISTS (
                        SELECT 1 FROM user_roles ur
                        JOIN roles r ON ur.role_id = r.role_id
                        WHERE ur.user_id = p_user_id AND r.name = 'view_all'
                    ) THEN RETURN TRUE; END IF;

                    IF EXISTS (
                        SELECT 1 FROM user_roles ur
                        JOIN roles r ON ur.role_id = r.role_id
                        WHERE ur.user_id = p_user_id AND r.name = 'view_branch_analytics'
                    ) AND v_user_branch_id = v_branch_id THEN
                        RETURN TRUE;
                    END IF;

                    IF v_user_curator_id = v_object_curator_id THEN
                        RETURN TRUE;
                    END IF;

                    RETURN FALSE;
                END IF;


                IF p_action_type = 'edit' THEN
                    IF EXISTS (
                        SELECT 1 FROM user_roles ur
                        JOIN roles r ON ur.role_id = r.role_id
                        WHERE ur.user_id = p_user_id AND r.name = 'edit_branch'
                    ) AND v_user_branch_id = v_branch_id THEN
                        RETURN TRUE;
                    END IF;

                    IF EXISTS (
                        SELECT 1 FROM user_roles ur
                        JOIN roles r ON ur.role_id = r.role_id
                        WHERE ur.user_id = p_user_id AND r.name = 'edit_all'
                    ) THEN
                        RETURN TRUE;
                    END IF;

                    IF v_user_curator_id = v_object_curator_id THEN
                        RETURN TRUE;
                    END IF;

                    RETURN FALSE;
                END IF;


                IF p_action_type = 'confirm' THEN
                    IF p_table_name = 'repairs' THEN
                        IF EXISTS (
                            SELECT 1 FROM user_roles ur
                            JOIN roles r ON ur.role_id = r.role_id
                            WHERE ur.user_id = p_user_id AND r.name = 'confirm_repair_branch'
                        ) AND v_user_branch_id = v_branch_id THEN
                            RETURN TRUE;
                        END IF;

                        IF EXISTS (
                            SELECT 1 FROM user_roles ur
                            JOIN roles r ON ur.role_id = r.role_id
                            WHERE ur.user_id = p_user_id AND r.name = 'confirm_repair_all'
                        ) THEN
                            RETURN TRUE;
                        END IF;

                    ELSIF p_table_name = 'new_projects' THEN
                        IF EXISTS (
                            SELECT 1 FROM user_roles ur
                            JOIN roles r ON ur.role_id = r.role_id
                            WHERE ur.user_id = p_user_id AND r.name = 'confirm_design_itc'
                        ) AND v_user_position = 'начальник СПКР' THEN
                            RETURN TRUE;
                        END IF;

                        IF EXISTS (
                            SELECT 1 FROM user_roles ur
                            JOIN roles r ON ur.role_id = r.role_id
                            WHERE ur.user_id = p_user_id AND r.name = 'confirm_design_all'
                        ) THEN
                            RETURN TRUE;
                        END IF;

                    ELSE
                        IF v_user_curator_id = v_object_curator_id THEN
                            RETURN TRUE;
                        END IF;
                    END IF;

                    RETURN FALSE;
                END IF;

                RETURN FALSE;
            END;
            $$ LANGUAGE plpgsql SECURITY DEFINER;
            CREATE INDEX idx_protection_objects_curator ON protection_objects(curator_id);
            CREATE INDEX idx_fire_systems_object ON fire_systems(object_id);
            CREATE INDEX idx_repairs_system_uuid ON repairs(system_id, record_uuid);
            CREATE INDEX idx_new_projects_system_uuid ON new_projects(system_id, record_uuid);

            CREATE OR REPLACE FUNCTION process_status_change()
            RETURNS TRIGGER AS $$
            DECLARE
                v_user_id      INTEGER;
                v_curator_type VARCHAR(50);
                v_record_id    INTEGER;
            BEGIN
                IF TG_TABLE_NAME NOT IN ('repairs', 'new_projects', 'mounts', 'system_maintenance') THEN
                    RETURN NEW;
                END IF;

                IF OLD.status = NEW.status THEN
                    RETURN NEW;
                END IF;

                BEGIN
                    v_user_id := current_setting('app.current_user_id', true)::INTEGER;
                EXCEPTION WHEN OTHERS THEN
                    RAISE EXCEPTION 'Не удалось определить пользователя (app.current_user_id)';
                END;

                IF TG_TABLE_NAME = 'new_projects' THEN
                    SELECT c.name INTO v_curator_type
                    FROM protection_objects po
                    JOIN fire_systems fs ON fs.object_id = po.object_id
                    JOIN curators c ON c.curator_id = po.curator_id
                    WHERE fs.system_id = NEW.system_id;

                    v_record_id := NEW.project_id;

                ELSIF TG_TABLE_NAME = 'repairs' THEN
                    IF NEW.execution_method = 'ХС' THEN 
                        v_curator_type := 'ПОА';
                    ELSE 
                        v_curator_type := 'УЭЗС';
                    END IF;
                    v_record_id := NEW.repair_id;

                ELSIF TG_TABLE_NAME = 'mounts' THEN
                    SELECT c.name INTO v_curator_type
                    FROM protection_objects po
                    JOIN fire_systems fs ON fs.object_id = po.object_id
                    JOIN curators c ON c.curator_id = po.curator_id
                    WHERE fs.system_id = NEW.system_id;

                    v_record_id := NEW.mount_id;

                ELSIF TG_TABLE_NAME = 'system_maintenance' THEN
                    SELECT c.name INTO v_curator_type
                    FROM protection_objects po
                    JOIN fire_systems fs ON fs.object_id = po.object_id
                    JOIN curators c ON c.curator_id = po.curator_id
                    WHERE fs.system_id = NEW.system_id;

                    v_record_id := NEW.maintenance_id;
                END IF;

                IF TG_TABLE_NAME = 'mounts' AND NEW.status = 'проверка пройдена' THEN
                    IF NOT EXISTS (SELECT 1 FROM fire_systems s WHERE s.system_id = NEW.system_id AND NULLIF(trim(s.name),'') IS NOT NULL) THEN
                        RAISE EXCEPTION 'Нет наименования системы';
                    END IF;
                    IF NEW.equipment_list_file_link IS NULL AND NOT EXISTS (SELECT 1 FROM equipments e WHERE e.system_id = NEW.system_id) THEN
                        RAISE EXCEPTION 'Нет сведений о технических устройствах';
                    END IF;
                    IF NOT EXISTS (SELECT 1 FROM implemented_projects ip WHERE ip.system_id = NEW.system_id) THEN
                        RAISE EXCEPTION 'Нет записи о реализованном проекте';
                    END IF;
                END IF;

                IF NOT check_approval_permission(v_user_id, NEW.record_uuid, TG_TABLE_NAME, 'confirm') THEN
                    RAISE EXCEPTION 'Нет прав подтверждения для %', TG_TABLE_NAME;
                END IF;

                INSERT INTO approval_history(
                    table_name, record_id, curator_type, old_status, new_status, approved_by, comment
                ) VALUES (
                    TG_TABLE_NAME, v_record_id, v_curator_type, OLD.status, NEW.status, v_user_id, current_setting('app.approval_comment', true)
                );

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE OR REPLACE FUNCTION ensure_mount_after_repair()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.status = 'выполнен' THEN
                    IF NOT EXISTS (SELECT 1 FROM mounts m WHERE m.repair_id = NEW.repair_id) THEN
                        IF NEW.installation_org_id IS NULL OR NEW.completion_date IS NULL OR NEW.act_file_link IS NULL THEN
                            RAISE EXCEPTION 'Не заполнены обязательные поля для ввода в эксплуатацию';
                        END IF;
                        INSERT INTO mounts(system_id, installation_org_id, commission_date, act_file_link, equipment_list_file_link, status, repair_id, repair_work_type, repair_execution_method)
                        VALUES (NEW.system_id, NEW.installation_org_id, NEW.completion_date, NEW.act_file_link, NEW.equipment_list_file_link, 'ожидает проверки', NEW.repair_id, NEW.work_type, NEW.execution_method);
                    END IF;
                END IF;
                RETURN NEW;
            END; $$ LANGUAGE plpgsql;

            CREATE OR REPLACE FUNCTION prevent_record_uuid_update()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.record_uuid <> OLD.record_uuid THEN
                    RAISE EXCEPTION 'Идентификатор записи (record_uuid) не может быть изменен в таблице %', TG_TABLE_NAME;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE OR REPLACE FUNCTION assign_roles_to_user(p_user_id INTEGER)
            RETURNS VOID AS $$
            DECLARE
                v_position VARCHAR;
                v_curator_type VARCHAR;
            BEGIN
                SELECT position, curator_id
                INTO v_position, v_curator_type
                FROM users
                WHERE user_id = p_user_id;

                DELETE FROM user_roles WHERE user_id = p_user_id;

                -- Все пользователи получают возможность просматривать все системы
                INSERT INTO user_roles(user_id, role_id)
                SELECT p_user_id, role_id
                FROM roles
                WHERE name = 'view_all';

                IF v_position IN ('главный инженер', 'начальник САиМО', 'инженер по КИПиА') THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'view_branch_analytics';
                END IF;

                IF v_position IN ('начальник подразделения автоматизации', 'руководитель группы', 'инженер по КИПиА') THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'edit_branch';
                END IF;

                IF v_position IN ('УЭЗС', 'ПОА') THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'edit_all';
                END IF;

                IF v_position = 'начальник подразделения автоматизации' THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'confirm_repair_branch';
                ELSIF v_position IN ('ПОА', 'УЭЗС') THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'confirm_repair_all';
                END IF;

                IF v_position = 'начальник СПКР ИТЦ' THEN
                    INSERT INTO user_roles(user_id, role_id)
                    SELECT p_user_id, role_id
                    FROM roles
                    WHERE name = 'confirm_design_itc';
                END IF;

            END;
            $$ LANGUAGE plpgsql SECURITY DEFINER;

            CREATE OR REPLACE FUNCTION trigger_assign_roles()
            RETURNS TRIGGER AS $$
            BEGIN
                PERFORM assign_roles_to_user(NEW.user_id);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER assign_roles_on_user_change
            AFTER INSERT OR UPDATE OF position ON users
            FOR EACH ROW
            EXECUTE FUNCTION trigger_assign_roles();

            DO $$
            DECLARE tbl TEXT;
                base_tables TEXT[] := ARRAY['protection_objects', 'fire_systems', 'implemented_projects', 'mounts', 'system_activations', 'system_maintenance', 'equipments', 'new_projects', 'repairs'];
                approval_tables TEXT[] := ARRAY['repairs', 'new_projects', 'mounts', 'system_maintenance'];
            BEGIN
                FOREACH tbl IN ARRAY base_tables LOOP
                    EXECUTE format('
                        DROP TRIGGER IF EXISTS %I_changes ON %I;
                        CREATE TRIGGER %I_changes 
                        AFTER INSERT OR UPDATE OR DELETE ON %I 
                        FOR EACH ROW EXECUTE FUNCTION fn_log_changes()', 
                        tbl, tbl, tbl, tbl);
                        
                    EXECUTE format('
                        DROP TRIGGER IF EXISTS %I_set_updated_at ON %I;
                        CREATE TRIGGER %I_set_updated_at 
                        BEFORE UPDATE ON %I 
                        FOR EACH ROW EXECUTE FUNCTION trigger_set_updated_at()', 
                        tbl, tbl, tbl, tbl);
                        
                    EXECUTE format('
                        DROP TRIGGER IF EXISTS %I_prevent_uuid_update ON %I;
                        CREATE TRIGGER %I_prevent_uuid_update 
                        BEFORE UPDATE ON %I 
                        FOR EACH ROW EXECUTE FUNCTION prevent_record_uuid_update()', 
                        tbl, tbl, tbl, tbl);
                END LOOP;
                
                FOREACH tbl IN ARRAY approval_tables LOOP
                    EXECUTE format('
                        DROP TRIGGER IF EXISTS %I_status_trigger ON %I;
                        CREATE TRIGGER %I_status_trigger 
                        BEFORE UPDATE OF status ON %I 
                        FOR EACH ROW EXECUTE FUNCTION process_status_change()', 
                        tbl, tbl, tbl, tbl);
                END LOOP;
                
                EXECUTE '
                    DROP TRIGGER IF EXISTS repairs_mount_ensurer ON repairs;
                    CREATE TRIGGER repairs_mount_ensurer 
                    AFTER UPDATE OF status ON repairs 
                    FOR EACH ROW 
                    WHEN (NEW.status = ''выполнен'')
                    EXECUTE FUNCTION ensure_mount_after_repair()';
            END $$;

            CREATE OR REPLACE FUNCTION analytics_check_permission()
            RETURNS TRIGGER AS $$
            DECLARE
                v_user_id INTEGER;
            BEGIN
                BEGIN
                    v_user_id := current_setting('app.current_user_id', true)::INTEGER;
                EXCEPTION WHEN others THEN
                    RAISE EXCEPTION 'Не удалось определить пользователя (app.current_user_id)';
                END;

                IF NOT check_approval_permission(v_user_id, NEW.record_uuid, TG_TABLE_NAME::VARCHAR(50), 'edit') THEN
                    RAISE EXCEPTION 'Нет прав редактирования записи аналитики';
                END IF;

                RETURN NEW;
            END; $$ LANGUAGE plpgsql;

            DO $$
            DECLARE
                tbl TEXT;
                base_tables TEXT[] := ARRAY[
                    'protection_objects', 'fire_systems', 'implemented_projects', 
                    'mounts', 'system_activations', 'system_maintenance', 
                    'equipments', 'new_projects', 'repairs'
                ];
            BEGIN
                FOREACH tbl IN ARRAY base_tables LOOP
                    EXECUTE format('DROP TRIGGER IF EXISTS trg_%I_check_permission ON %I;', tbl, tbl);
                    EXECUTE format('
                        CREATE TRIGGER trg_%I_check_permission
                        BEFORE UPDATE ON %I
                        FOR EACH ROW
                        EXECUTE FUNCTION analytics_check_permission();', tbl, tbl);
                END LOOP;
            END $$;
        ");
        } catch (\Exception $e) {
            \Log::error('Ошибка создания функции: ' . $e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        DB::unprepared("
            DO $$
            DECLARE
                tbl TEXT;
                tables_to_clean TEXT[] := ARRAY[
                    'protection_objects', 'fire_systems', 'implemented_projects', 
                    'mounts', 'system_activations', 'system_maintenance', 
                    'equipments', 'new_projects', 'repairs'
                ];
            BEGIN
                FOREACH tbl IN ARRAY tables_to_clean LOOP
                    EXECUTE format('DROP TRIGGER IF EXISTS %I_changes ON %I', tbl, tbl);
                    EXECUTE format('DROP TRIGGER IF EXISTS %I_set_updated_at ON %I', tbl, tbl);
                    EXECUTE format('DROP TRIGGER IF EXISTS %I_prevent_uuid_update ON %I', tbl, tbl);
                    EXECUTE format('DROP TRIGGER IF EXISTS %I_status_trigger ON %I', tbl, tbl);
                    EXECUTE format('DROP TRIGGER IF EXISTS trg_%I_check_permission ON %I', tbl, tbl);
                END LOOP;
                
                DROP TRIGGER IF EXISTS repairs_mount_ensurer ON repairs;
                DROP TRIGGER IF EXISTS assign_roles_on_user_change ON users;
            END $$;

            DROP FUNCTION IF EXISTS trigger_set_updated_at();
            DROP FUNCTION IF EXISTS fn_log_changes();
            DROP FUNCTION IF EXISTS check_approval_permission(INTEGER, UUID, VARCHAR, VARCHAR);
            DROP FUNCTION IF EXISTS process_status_change();
            DROP FUNCTION IF EXISTS ensure_mount_after_repair();
            DROP FUNCTION IF EXISTS prevent_record_uuid_update();
            DROP FUNCTION IF EXISTS assign_roles_to_user(INTEGER);
            DROP FUNCTION IF EXISTS trigger_assign_roles();
            DROP FUNCTION IF EXISTS analytics_check_permission();
        ");
    }
};
