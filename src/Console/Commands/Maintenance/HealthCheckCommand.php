<?php

namespace Marufsharia\Hyro\Console\Commands\Maintenance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\AuditLog;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;

class HealthCheckCommand extends BaseCommand
{
    protected $signature = 'hyro:health-check
                            {--fix : Attempt to fix issues automatically}
                            {--detailed : Show detailed information}';

    protected $description = 'Check Hyro system health and integrity';

    protected function executeCommand(): void
    {
        $this->info('🩺 Running Hyro Health Check...');
        $this->newLine();

        $checks = [
            'Configuration' => [$this, 'checkConfiguration'],
            'Database' => [$this, 'checkDatabase'],
            'Tables' => [$this, 'checkTables'],
            'Models' => [$this, 'checkModels'],
            'Cache' => [$this, 'checkCache'],
            'Integrity' => [$this, 'checkIntegrity'],
            'Security' => [$this, 'checkSecurity'],
        ];

        $results = [];
        $hasIssues = false;

        foreach ($checks as $name => $check) {
            $this->info("Checking: {$name}...");
            $result = $check();
            $results[$name] = $result;

            if (!$result['healthy']) {
                $hasIssues = true;
                $this->error("  ❌ {$result['message']}");

                if (!empty($result['details'])) {
                    foreach ($result['details'] as $detail) {
                        $this->warn("    - {$detail}");
                    }
                }

                if ($this->option('fix') && isset($result['fix'])) {
                    $this->info("  🔧 Attempting fix...");
                    try {
                        $result['fix']();
                        $this->info("  ✅ Fix applied");
                    } catch (\Exception $e) {
                        $this->error("  ❌ Fix failed: {$e->getMessage()}");
                    }
                }
            } else {
                $this->info("  ✅ {$result['message']}");

                if ($this->option('detailed') && !empty($result['details'])) {
                    foreach ($result['details'] as $detail) {
                        $this->info("    - {$detail}");
                    }
                }
            }
        }

        $this->newLine();
        $this->showSummary($results, $hasIssues);
    }

    private function checkConfiguration(): array
    {
        $issues = [];
        $healthy = true;

        // Check if config file exists
        if (!config('hyro')) {
            $issues[] = 'Configuration file not loaded';
            $healthy = false;
        }

        // Check required config sections
        $requiredSections = ['api', 'cli', 'ui', 'database', 'security', 'auditing'];
        foreach ($requiredSections as $section) {
            if (!config("hyro.{$section}")) {
                $issues[] = "Missing config section: {$section}";
                $healthy = false;
            }
        }

        // Check security settings
        if (!config('hyro.security.fail_closed', true)) {
            $issues[] = 'Security: fail_closed is disabled (security risk)';
            $healthy = false;
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Configuration OK' : 'Configuration issues found',
            'details' => $issues,
            'fix' => $healthy ? null : function () {
                // Could regenerate config or set defaults
            },
        ];
    }

    private function checkDatabase(): array
    {
        $issues = [];
        $healthy = true;

        try {
            // Test database connection
            DB::connection()->getPdo();

            // Check migrations
            $migrations = DB::table('migrations')
                ->where('migration', 'like', '%hyro%')
                ->count();

            if ($migrations === 0) {
                $issues[] = 'No Hyro migrations found (run php artisan hyro:install)';
                $healthy = false;
            }
        } catch (\Exception $e) {
            $issues[] = "Database connection failed: {$e->getMessage()}";
            $healthy = false;
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Database connection OK' : 'Database issues found',
            'details' => $issues,
        ];
    }

    private function checkTables(): array
    {
        $issues = [];
        $healthy = true;
        $tableDetails = [];

        $requiredTables = [
            Config::get('hyro.database.tables.roles'),
            Config::get('hyro.database.tables.privileges'),
            Config::get('hyro.database.tables.role_user'),
            Config::get('hyro.database.tables.privilege_role'),
            Config::get('hyro.database.tables.user_suspensions'),
            Config::get('hyro.database.tables.audit_logs'),
        ];

        foreach ($requiredTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $tableDetails[] = "{$table}: {$count} records";
            } else {
                $issues[] = "Missing table: {$table}";
                $healthy = false;
                $tableDetails[] = "{$table}: MISSING";
            }
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'All tables present' : 'Missing tables',
            'details' => $tableDetails,
            'fix' => !$healthy ? function () {
                Artisan::call('migrate', [
                    '--path' => 'database/migrations',
                    '--force' => true,
                ]);
            } : null,
        ];
    }

    private function checkModels(): array
    {
        $issues = [];
        $healthy = true;
        $modelDetails = [];

        try {
            // Check Role model
            $roleCount = Role::count();
            $modelDetails[] = "Roles: {$roleCount}";

            if ($roleCount === 0) {
                $issues[] = 'No roles defined (run php artisan hyro:install --seed)';
                $healthy = false;
            }

            // Check Privilege model
            $privilegeCount = Privilege::count();
            $modelDetails[] = "Privileges: {$privilegeCount}";

            if ($privilegeCount === 0) {
                $issues[] = 'No privileges defined';
                $healthy = false;
            }

            // Check for system roles
            $systemRoles = Role::where('is_system', true)->count();
            $modelDetails[] = "System roles: {$systemRoles}";

            // Check for protected roles
            $protectedRoles = Role::where('is_protected', true)->count();
            $modelDetails[] = "Protected roles: {$protectedRoles}";

        } catch (\Exception $e) {
            $issues[] = "Model check failed: {$e->getMessage()}";
            $healthy = false;
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Models OK' : 'Model issues found',
            'details' => $modelDetails,
        ];
    }

    private function checkCache(): array
    {
        $issues = [];
        $healthy = true;
        $cacheDetails = [];

        try {
            // Test cache connection
            Cache::put('hyro:health:test', 'ok', 10);
            $testValue = Cache::get('hyro:health:test');

            if ($testValue !== 'ok') {
                $issues[] = 'Cache test failed';
                $healthy = false;
            } else {
                $cacheDetails[] = 'Cache connection: OK';
            }

            // Check cache configuration
            $cacheEnabled = Config::get('hyro.cache.enabled', true);
            $cacheDetails[] = "Cache enabled: " . ($cacheEnabled ? 'Yes' : 'No');

            if (!$cacheEnabled) {
                $issues[] = 'Cache is disabled (performance impact)';
                // Not necessarily unhealthy, just a warning
            }

        } catch (\Exception $e) {
            $issues[] = "Cache check failed: {$e->getMessage()}";
            $healthy = false;
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Cache OK' : 'Cache issues found',
            'details' => $cacheDetails,
        ];
    }

    private function checkIntegrity(): array
    {
        $issues = [];
        $healthy = true;
        $integrityDetails = [];

        try {
            // Check for orphaned role_user records
            $userModel = Config::get('hyro.models.users');
            $orphanedRoleUser = DB::table(Config::get('hyro.database.tables.role_user'))
                ->whereNotExists(function ($query) use ($userModel) {
                    $query->select(DB::raw(1))
                        ->from(Config::get('hyro.database.tables.users', 'users'))
                        ->whereColumn('users.id', 'role_user.user_id');
                })
                ->count();

            if ($orphanedRoleUser > 0) {
                $issues[] = "Found {$orphanedRoleUser} orphaned role_user records";
                $integrityDetails[] = "Orphaned role_user: {$orphanedRoleUser}";
                $healthy = false;
            } else {
                $integrityDetails[] = 'No orphaned role_user records';
            }

            // Check for orphaned privilege_role records
            $orphanedPrivilegeRole = DB::table(Config::get('hyro.database.tables.privilege_role'))
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from(Config::get('hyro.database.tables.privileges'))
                        ->whereColumn('privileges.id', 'privilege_role.privilege_id');
                })
                ->orWhereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from(Config::get('hyro.database.tables.roles'))
                        ->whereColumn('roles.id', 'privilege_role.role_id');
                })
                ->count();

            if ($orphanedPrivilegeRole > 0) {
                $issues[] = "Found {$orphanedPrivilegeRole} orphaned privilege_role records";
                $integrityDetails[] = "Orphaned privilege_role: {$orphanedPrivilegeRole}";
                $healthy = false;
            } else {
                $integrityDetails[] = 'No orphaned privilege_role records';
            }

            // Check for duplicate slugs
            $duplicateRoles = Role::select('slug')
                ->groupBy('slug')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            if ($duplicateRoles > 0) {
                $issues[] = "Found {$duplicateRoles} duplicate role slugs";
                $integrityDetails[] = "Duplicate role slugs: {$duplicateRoles}";
                $healthy = false;
            }

        } catch (\Exception $e) {
            $issues[] = "Integrity check failed: {$e->getMessage()}";
            $healthy = false;
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Data integrity OK' : 'Integrity issues found',
            'details' => $integrityDetails,
            'fix' => !$healthy ? function () use ($orphanedRoleUser, $orphanedPrivilegeRole) {
                if ($orphanedRoleUser > 0) {
                    DB::table(Config::get('hyro.database.tables.role_user'))
                        ->whereNotExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from(Config::get('hyro.database.tables.users', 'users'))
                                ->whereColumn('users.id', 'role_user.user_id');
                        })
                        ->delete();
                }

                if ($orphanedPrivilegeRole > 0) {
                    DB::table(Config::get('hyro.database.tables.privilege_role'))
                        ->whereNotExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from(Config::get('hyro.database.tables.privileges'))
                                ->whereColumn('privileges.id', 'privilege_role.privilege_id');
                        })
                        ->orWhereNotExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from(Config::get('hyro.database.tables.roles'))
                                ->whereColumn('roles.id', 'privilege_role.role_id');
                        })
                        ->delete();
                }
            } : null,
        ];
    }

    private function checkSecurity(): array
    {
        $issues = [];
        $healthy = true;
        $securityDetails = [];

        // Check audit log retention
        $retentionDays = Config::get('hyro.auditing.retention_days', 365);
        $oldLogs = AuditLog::where('created_at', '<', now()->subDays($retentionDays))->count();

        if ($oldLogs > 0) {
            $securityDetails[] = "Old audit logs pending cleanup: {$oldLogs}";
            // This is informational, not an issue
        } else {
            $securityDetails[] = 'Audit logs within retention period';
        }

        // Check for users with too many privileges (potential security risk)
        $userModel = Config::get('hyro.models.users');
        $users = $userModel::has('roles')->withCount('roles')->get();

        foreach ($users as $user) {
            if ($user->roles_count > 10) {
                $issues[] = "User {$user->email} has {$user->roles_count} roles (potential privilege creep)";
                $securityDetails[] = "User {$user->email}: {$user->roles_count} roles";
                // Warning, not necessarily unhealthy
            }
        }

        // Check for suspended users with active tokens
        if (Config::get('hyro.security.suspension.auto_revoke_tokens', true)) {
            $suspendedUsers = $userModel::whereHas('suspensions', function ($q) {
                $q->whereNull('unsuspended_at')
                    ->where(function ($q) {
                        $q->whereNull('suspended_until')
                            ->orWhere('suspended_until', '>', now());
                    });
            })->has('tokens')->count();

            if ($suspendedUsers > 0) {
                $issues[] = "Found {$suspendedUsers} suspended users with active tokens";
                $securityDetails[] = "Suspended users with tokens: {$suspendedUsers}";
                $healthy = false;
            }
        }

        return [
            'healthy' => $healthy,
            'message' => $healthy ? 'Security checks passed' : 'Security issues found',
            'details' => $securityDetails,
        ];
    }

    private function showSummary(array $results, bool $hasIssues): void
    {
        $healthyChecks = 0;
        $totalChecks = count($results);

        foreach ($results as $result) {
            if ($result['healthy']) {
                $healthyChecks++;
            }
        }

        $healthPercentage = round(($healthyChecks / $totalChecks) * 100);

        if ($healthPercentage === 100) {
            $this->info("✅ Overall Health: EXCELLENT ({$healthPercentage}%)");
            $this->info("All {$totalChecks} checks passed successfully");
        } elseif ($healthPercentage >= 80) {
            $this->warn("⚠️  Overall Health: GOOD ({$healthPercentage}%)");
            $this->info("{$healthyChecks}/{$totalChecks} checks passed");
        } elseif ($healthPercentage >= 60) {
            $this->warn("⚠️  Overall Health: FAIR ({$healthPercentage}%)");
            $this->info("{$healthyChecks}/{$totalChecks} checks passed");
        } else {
            $this->error("❌ Overall Health: POOR ({$healthPercentage}%)");
            $this->error("Only {$healthyChecks}/{$totalChecks} checks passed");
        }

        if ($hasIssues) {
            $this->newLine();
            $this->warn('Issues detected. Recommended actions:');

            foreach ($results as $name => $result) {
                if (!$result['healthy'] && !empty($result['details'])) {
                    $this->warn("  {$name}:");
                    foreach ($result['details'] as $detail) {
                        $this->warn("    - {$detail}");
                    }
                }
            }

            $this->newLine();
            $this->info('Try running: php artisan hyro:health-check --fix');
            $this->info('Or manually address the issues above.');
        }
    }
}
