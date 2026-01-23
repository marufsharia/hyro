<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only run optimizations if not in unit tests
        if (app()->runningUnitTests()) {
            return;
        }

        $this->createMaterializedViews();
        $this->createHelperFunctions();
        $this->createDatabaseTriggers();
    }

    private function createMaterializedViews(): void
    {
        // For PostgreSQL: Create materialized view for fast user privilege resolution
        if (Config::get('database.default') === 'pgsql') {
            $rolesTable = Config::get('hyro.database.tables.roles', 'hyro_roles');
            $privilegesTable = Config::get('hyro.database.tables.privileges', 'hyro_privileges');
            $roleUserTable = Config::get('hyro.database.tables.role_user', 'hyro_role_user');
            $privilegeRoleTable = Config::get('hyro.database.tables.privilege_role', 'hyro_privilege_role');

            DB::statement("
                CREATE MATERIALIZED VIEW IF NOT EXISTS hyro_user_privileges_mv AS
                SELECT
                    ru.user_id,
                    p.slug as privilege_slug,
                    p.is_wildcard,
                    p.wildcard_pattern,
                    MAX(p.priority) as priority,
                    BOOL_OR(pr.expires_at IS NULL OR pr.expires_at > NOW()) as is_active,
                    JSON_AGG(DISTINCT r.slug) as role_slugs,
                    MIN(ru.assigned_at) as first_granted_at,
                    MAX(COALESCE(pr.expires_at, 'infinity'::timestamp)) as last_expires_at
                FROM {$roleUserTable} ru
                JOIN {$rolesTable} r ON r.id = ru.role_id AND r.deleted_at IS NULL
                JOIN {$privilegeRoleTable} pr ON pr.role_id = r.id
                JOIN {$privilegesTable} p ON p.id = pr.privilege_id AND p.deleted_at IS NULL
                WHERE (ru.expires_at IS NULL OR ru.expires_at > NOW())
                AND (pr.expires_at IS NULL OR pr.expires_at > NOW())
                GROUP BY ru.user_id, p.slug, p.is_wildcard, p.wildcard_pattern
            ");

            DB::statement("CREATE UNIQUE INDEX ON hyro_user_privileges_mv (user_id, privilege_slug)");
            DB::statement("CREATE INDEX ON hyro_user_privileges_mv (user_id, is_active)");
            DB::statement("CREATE INDEX ON hyro_user_privileges_mv (privilege_slug, is_active)");
        }
    }

    private function createHelperFunctions(): void
    {
        // Create a PostgreSQL function for recursive role hierarchy
        if (Config::get('database.default') === 'pgsql') {
            DB::statement("
                CREATE OR REPLACE FUNCTION hyro_get_user_roles(user_id_input BIGINT)
                RETURNS TABLE(role_id BIGINT, role_slug TEXT, depth INTEGER) AS $$
                WITH RECURSIVE role_tree AS (
                    -- Direct roles
                    SELECT
                        r.id,
                        r.slug,
                        1 as depth
                    FROM hyro_role_user ru
                    JOIN hyro_roles r ON r.id = ru.role_id
                    WHERE ru.user_id = user_id_input
                    AND (ru.expires_at IS NULL OR ru.expires_at > NOW())
                    AND r.deleted_at IS NULL

                    UNION ALL

                    -- Inherited roles (if you implement role hierarchy later)
                    SELECT
                        r2.id,
                        r2.slug,
                        rt.depth + 1
                    FROM role_tree rt
                    -- JOIN for role inheritance would go here
                    -- This is a placeholder for future role hierarchy feature
                    JOIN hyro_roles r2 ON false -- Remove when implementing inheritance
                )
                SELECT * FROM role_tree;
                $$ LANGUAGE SQL STABLE;
            ");
        }
    }

    private function createDatabaseTriggers(): void
    {
        // Create trigger for automatic audit log cleanup based on retention policy
        if (Config::get('database.default') === 'pgsql') {
            $auditLogsTable = Config::get('hyro.database.tables.audit_logs', 'hyro_audit_logs');
            $retentionDays = Config::get('hyro.auditing.retention_days', 365);

            DB::statement("
                CREATE OR REPLACE FUNCTION hyro_cleanup_old_audit_logs()
                RETURNS trigger AS $$
                BEGIN
                    DELETE FROM {$auditLogsTable}
                    WHERE created_at < NOW() - INTERVAL '{$retentionDays} days';
                    RETURN NULL;
                END;
                $$ LANGUAGE plpgsql;
            ");

            DB::statement("
                CREATE TRIGGER trigger_cleanup_audit_logs
                AFTER INSERT ON {$auditLogsTable}
                FOR EACH STATEMENT
                EXECUTE FUNCTION hyro_cleanup_old_audit_logs();
            ");
        }
    }

    public function down(): void
    {
        if (Config::get('database.default') === 'pgsql') {
            DB::statement('DROP MATERIALIZED VIEW IF EXISTS hyro_user_privileges_mv CASCADE');
            DB::statement('DROP FUNCTION IF EXISTS hyro_get_user_roles(BIGINT) CASCADE');
            DB::statement('DROP FUNCTION IF EXISTS hyro_cleanup_old_audit_logs() CASCADE');
        }
    }
};
