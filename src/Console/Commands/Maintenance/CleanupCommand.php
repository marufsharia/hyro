<?php

namespace Marufsharia\Hyro\Console\Commands\Maintenance;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class CleanupCommand extends BaseCommand
{
    protected $signature = 'hyro:cleanup
                            {--older-than=30 : Cleanup data older than X days}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--only= : Only cleanup specific items (comma-separated: audit_logs,revoked_tokens,expired_tokens,failed_jobs)}
                            {--exclude= : Exclude specific items from cleanup}
                            {--batch-size=1000 : Process records in batches of this size}
                            {--vacuum : Optimize database tables after cleanup}';

    protected $description = 'Cleanup old system data';

    protected function executeCommand(): void
    {
        $this->info('Hyro System Cleanup');
        $this->line('===================');

        $olderThan = (int) $this->option('older-than');
        $cutoffDate = now()->subDays($olderThan);

        $operations = $this->getCleanupOperations($olderThan);

        if ($only = $this->option('only')) {
            $onlyItems = explode(',', $only);
            $operations = array_intersect_key($operations, array_flip($onlyItems));
        }

        if ($exclude = $this->option('exclude')) {
            $excludeItems = explode(',', $exclude);
            $operations = array_diff_key($operations, array_flip($excludeItems));
        }

        if (empty($operations)) {
            $this->warn('No cleanup operations selected');
            return;
        }

        $this->displayCleanupPlan($operations, $cutoffDate);

        if (!$this->confirmDestructiveAction('Proceed with cleanup?')) {
            $this->infoMessage('Cleanup cancelled.');
            return;
        }

        $results = [];
        $totalDeleted = 0;

        foreach ($operations as $name => $operation) {
            $this->info("\nCleaning up: " . str_replace('_', ' ', $name));

            try {
                $deleted = $this->performCleanup($name, $operation, $cutoffDate);
                $results[$name] = ['deleted' => $deleted, 'status' => 'success'];
                $totalDeleted += $deleted;

                $this->info("Deleted {$deleted} records");
            } catch (\Exception $e) {
                $results[$name] = ['deleted' => 0, 'status' => 'error', 'message' => $e->getMessage()];
                $this->error("Failed: " . $e->getMessage());
            }
        }

        $this->displayResults($results, $totalDeleted);

        if ($this->option('vacuum') && !$this->dryRun) {
            $this->optimizeDatabase();
        }

        $this->stats['processed'] = $totalDeleted;
        $this->stats['succeeded'] = $totalDeleted;
    }

    protected function getCleanupOperations(int $olderThan): array
    {
        return [
            'audit_logs' => [
                'table' => 'audit_logs',
                'date_column' => 'created_at',
                'description' => 'Old audit log entries',
                'retention_days' => config('hyro.audit.retention_days', 90),
                'where' => function ($query) {
                    return $query->where('action', 'not like', '%system%');
                }
            ],
            'revoked_tokens' => [
                'table' => 'personal_access_tokens',
                'date_column' => 'revoked_at',
                'description' => 'Revoked access tokens',
                'retention_days' => 7,
                'where' => function ($query) {
                    return $query->where('revoked', true);
                }
            ],
            'expired_tokens' => [
                'table' => 'personal_access_tokens',
                'date_column' => 'expires_at',
                'description' => 'Expired access tokens',
                'retention_days' => 1,
                'where' => function ($query) {
                    return $query->where('expires_at', '<', now())
                        ->where('revoked', false);
                }
            ],
            'failed_jobs' => [
                'table' => 'failed_jobs',
                'date_column' => 'failed_at',
                'description' => 'Failed job records',
                'retention_days' => 30,
                'where' => null
            ],
            'temporary_files' => [
                'callback' => fn($date) => $this->cleanupTemporaryFiles($date),
                'description' => 'Temporary system files',
                'retention_days' => 7
            ],
            'cache_tags' => [
                'callback' => fn($date) => $this->cleanupCacheTags($date),
                'description' => 'Expired cache tags',
                'retention_days' => 1
            ]
        ];
    }

    protected function displayCleanupPlan(array $operations, $cutoffDate): void
    {
        $this->info("Cleanup Plan (older than {$cutoffDate->format('Y-m-d')}):");

        $tableData = [];
        foreach ($operations as $name => $op) {
            $tableData[] = [
                'Operation' => str_replace('_', ' ', $name),
                'Description' => $op['description'],
                'Retention' => ($op['retention_days'] ?? 'N/A') . ' days',
                'Table/Callback' => $op['table'] ?? 'callback',
            ];
        }

        $this->table(['Operation', 'Description', 'Retention', 'Type'], $tableData);

        // Show estimated counts
        $this->info("\nEstimated records to cleanup:");
        foreach ($operations as $name => $op) {
            if (isset($op['table'])) {
                $count = $this->estimateRecords($op, $cutoffDate);
                $this->line("  • {$op['description']}: {$count} records");
            }
        }
    }

    protected function estimateRecords(array $operation, $cutoffDate): int
    {
        $query = \DB::table($operation['table'])
            ->where($operation['date_column'], '<', $cutoffDate);

        if (isset($operation['where']) && is_callable($operation['where'])) {
            $operation['where']($query);
        }

        return $query->count();
    }

    protected function performCleanup(string $name, array $operation, $cutoffDate): int
    {
        if (isset($operation['callback'])) {
            return $operation['callback']($cutoffDate);
        }

        $query = \DB::table($operation['table'])
            ->where($operation['date_column'], '<', $cutoffDate);

        if (isset($operation['where']) && is_callable($operation['where'])) {
            $operation['where']($query);
        }

        if ($this->dryRun) {
            $count = $query->count();
            $this->info("[DRY RUN] Would delete {$count} records from {$operation['table']}");
            return $count;
        }

        $batchSize = $this->option('batch-size');
        $totalDeleted = 0;

        do {
            $ids = $query->take($batchSize)->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted = \DB::table($operation['table'])
                ->whereIn('id', $ids)
                ->delete();

            $totalDeleted += $deleted;

            if ($this->option('verbose')) {
                $this->info("Batch deleted {$deleted} records (total: {$totalDeleted})");
            }

            // Sleep briefly to avoid overwhelming the database
            usleep(100000); // 0.1 second
        } while (true);

        return $totalDeleted;
    }

    protected function cleanupTemporaryFiles($cutoffDate): int
    {
        if ($this->dryRun) {
            $this->info("[DRY RUN] Would cleanup temporary files");
            return 0;
        }

        $tempDirs = [
            storage_path('temp'),
            storage_path('cache'),
            storage_path('logs'),
        ];

        $deleted = 0;
        foreach ($tempDirs as $dir) {
            if (file_exists($dir)) {
                $files = glob($dir . '/*');
                foreach ($files as $file) {
                    if (filemtime($file) < $cutoffDate->timestamp) {
                        unlink($file);
                        $deleted++;
                    }
                }
            }
        }

        return $deleted;
    }

    protected function cleanupCacheTags($cutoffDate): int
    {
        if ($this->dryRun) {
            $this->info("[DRY RUN] Would cleanup cache tags");
            return 0;
        }

        // This would depend on your cache implementation
        // For example, with Redis cache tags:
        // $deleted = Redis::connection()->flushdb();

        $this->info("Cache tag cleanup not implemented in this version");
        return 0;
    }

    protected function displayResults(array $results, int $totalDeleted): void
    {
        $this->newLine();
        $this->info('Cleanup Results:');

        $tableData = [];
        foreach ($results as $name => $result) {
            $tableData[] = [
                'Operation' => str_replace('_', ' ', $name),
                'Deleted' => $result['deleted'],
                'Status' => $result['status'],
                'Message' => $result['message'] ?? 'Success',
            ];
        }

        $this->table(['Operation', 'Deleted', 'Status', 'Message'], $tableData);

        $this->newLine();
        if ($this->dryRun) {
            $this->info("[DRY RUN] Would delete {$totalDeleted} records total");
        } else {
            $this->success("✓ Cleanup completed: {$totalDeleted} records deleted");
        }
    }

    protected function optimizeDatabase(): void
    {
        $this->info('Optimizing database...');

        try {
            $tables = \DB::select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                \DB::statement("OPTIMIZE TABLE {$tableName}");
                $this->info("Optimized table: {$tableName}");
            }

            $this->success('Database optimization completed');
        } catch (\Exception $e) {
            $this->error("Database optimization failed: " . $e->getMessage());
        }
    }
}
