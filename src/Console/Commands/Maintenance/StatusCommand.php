<?php

namespace Marufsharia\Hyro\Console\Commands\Maintenance;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\User;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;

class StatusCommand extends BaseCommand
{
    protected $signature = 'hyro:status
                            {--check-database : Verify database connection}
                            {--check-services : Check external services}
                            {--detailed : Show detailed information}
                            {--format=table : Output format (table, json, yaml)}';

    protected $description = 'Check Hyro system status';

    protected function executeCommand(): void
    {
        $this->info('Hyro System Status');
        $this->line('===================');

        $status = [
            'system' => $this->getSystemInfo(),
            'database' => $this->getDatabaseInfo(),
            'users' => $this->getUserInfo(),
            'roles' => $this->getRoleInfo(),
            'privileges' => $this->getPrivilegeInfo(),
            'security' => $this->getSecurityInfo(),
            'performance' => $this->getPerformanceInfo(),
        ];

        if ($this->option('check-database')) {
            $status['database_connection'] = $this->checkDatabaseConnection();
        }

        if ($this->option('check-services')) {
            $status['external_services'] = $this->checkExternalServices();
        }

        $format = $this->option('format');

        switch ($format) {
            case 'json':
                $this->line(json_encode($status, JSON_PRETTY_PRINT));
                break;
            case 'yaml':
                $this->outputYaml($status);
                break;
            default:
                $this->outputStatusTable($status);
        }

        // Overall status
        $this->newLine();
        $this->outputOverallStatus($status);

        $this->stats['processed'] = 1;
        $this->stats['succeeded'] = 1;
    }

    protected function getSystemInfo(): array
    {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug') ? 'ON' : 'OFF',
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'maintenance_mode' => app()->isDownForMaintenance() ? 'Yes' : 'No',
        ];
    }

    protected function getDatabaseInfo(): array
    {
        try {
            \DB::connection()->getPdo();
            $connection = 'Connected';
            $driver = config('database.default');
        } catch (\Exception $e) {
            $connection = 'Disconnected: ' . $e->getMessage();
            $driver = 'Unknown';
        }

        return [
            'status' => $connection,
            'driver' => $driver,
            'tables_count' => count(\DB::select('SHOW TABLES')),
            'migrations' => $this->getMigrationsStatus(),
        ];
    }

    protected function getUserInfo(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $suspendedUsers = User::where('is_suspended', true)->count();
        $superAdmins = User::where('is_super_admin', true)->count();

        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'suspended' => $suspendedUsers,
            'super_admins' => $superAdmins,
            'inactive_percentage' => $totalUsers > 0 ? round((($totalUsers - $activeUsers) / $totalUsers) * 100, 2) . '%' : '0%',
        ];
    }

    protected function getRoleInfo(): array
    {
        $totalRoles = Role::count();
        $rolesWithUsers = Role::has('users')->count();
        $rolesWithPrivileges = Role::has('privileges')->count();

        return [
            'total' => $totalRoles,
            'with_users' => $rolesWithUsers,
            'with_privileges' => $rolesWithPrivileges,
            'average_users_per_role' => $totalRoles > 0 ? round(User::count() / $totalRoles, 2) : 0,
        ];
    }

    protected function getPrivilegeInfo(): array
    {
        $totalPrivileges = Privilege::count();
        $byScope = Privilege::groupBy('scope')->selectRaw('scope, count(*) as count')->get();

        return [
            'total' => $totalPrivileges,
            'by_scope' => $byScope->pluck('count', 'scope')->toArray(),
            'unused_privileges' => Privilege::doesntHave('roles')->count(),
        ];
    }

    protected function getSecurityInfo(): array
    {
        $lockedUsers = User::where('is_active', false)->whereNotNull('locked_at')->count();
        $expiredTokens = \DB::table('personal_access_tokens')
            ->where('expires_at', '<', now())
            ->where('revoked', false)
            ->count();

        return [
            'locked_users' => $lockedUsers,
            'expired_tokens' => $expiredTokens,
            'recent_failed_logins' => $this->getRecentFailedLogins(),
            'audit_log_entries' => \DB::table('audit_logs')->where('created_at', '>', now()->subDay())->count(),
        ];
    }

    protected function getPerformanceInfo(): array
    {
        return [
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'execution_time' => round(microtime(true) - LARAVEL_START, 4) . ' seconds',
            'database_queries' => count(\DB::getQueryLog()),
        ];
    }

    protected function checkDatabaseConnection(): array
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'OK', 'ping' => round(microtime(true) - LARAVEL_START, 4) . 's'];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    protected function getMigrationsStatus(): string
    {
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles($migrator->paths());
            $ran = $migrator->getRepository()->getRan();
            $pending = count($files) - count($ran);

            return "{$pending} pending, " . count($ran) . " ran";
        } catch (\Exception $e) {
            return 'Unknown: ' . $e->getMessage();
        }
    }

    protected function getRecentFailedLogins(): int
    {
        return \DB::table('audit_logs')
            ->where('action', 'like', '%failed%')
            ->where('created_at', '>', now()->subHour())
            ->count();
    }

    protected function checkExternalServices(): array
    {
        $services = [];

        // Check cache
        try {
            \Cache::put('hyro_status_check', 'ok', 10);
            $services['cache'] = \Cache::get('hyro_status_check') === 'ok' ? 'OK' : 'ERROR';
        } catch (\Exception $e) {
            $services['cache'] = 'ERROR: ' . $e->getMessage();
        }

        // Check queue
        try {
            $services['queue'] = config('queue.default') . ' (configured)';
        } catch (\Exception $e) {
            $services['queue'] = 'ERROR: ' . $e->getMessage();
        }

        return $services;
    }

    protected function outputStatusTable(array $status): void
    {
        foreach ($status as $category => $data) {
            $this->info("\n" . strtoupper(str_replace('_', ' ', $category)) . ":");

            $tableData = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_SLASHES);
                }
                $tableData[] = [str_replace('_', ' ', $key), $value];
            }

            $this->table(['Metric', 'Value'], $tableData);
        }
    }

    protected function outputYaml(array $data): void
    {
        $yaml = '';

        foreach ($data as $category => $values) {
            $yaml .= "$category:\n";
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $yaml .= "  $key:\n";
                    foreach ($value as $subKey => $subValue) {
                        $yaml .= "    $subKey: $subValue\n";
                    }
                } else {
                    $yaml .= "  $key: $value\n";
                }
            }
        }

        $this->line($yaml);
    }

    protected function outputOverallStatus(array $status): void
    {
        $issues = [];

        // Check for critical issues
        if (str_contains($status['database']['status'], 'Disconnected')) {
            $issues[] = 'Database disconnected';
        }

        if ($status['users']['total'] === 0) {
            $issues[] = 'No users in system';
        }

        if ($status['roles']['total'] === 0) {
            $issues[] = 'No roles defined';
        }

        if (app()->isDownForMaintenance()) {
            $issues[] = 'System in maintenance mode';
        }

        if (empty($issues)) {
            $this->success('✅ System status: HEALTHY');
            $this->infoMessage('All systems operational');
        } else {
            $this->error('⚠️  System status: ISSUES DETECTED');
            foreach ($issues as $issue) {
                $this->line("  • {$issue}");
            }
        }
    }
}
