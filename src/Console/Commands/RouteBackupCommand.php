<?php

namespace Marufsharia\Hyro\Console\Commands;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\SmartCrudRouteManager;

class RouteBackupCommand extends Command
{
    protected $signature = 'hyro:route-backup 
                            {action : Action to perform (list, backup, restore, clean)}
                            {--file= : Backup file to restore (for restore action)}
                            {--keep=10 : Number of backups to keep (for clean action)}';

    protected $description = 'Manage CRUD route backups';

    public function handle()
    {
        $action = $this->argument('action');
        $routeManager = app(SmartCrudRouteManager::class);

        switch ($action) {
            case 'list':
                $this->listBackups($routeManager);
                break;

            case 'backup':
                $this->createBackup($routeManager);
                break;

            case 'restore':
                $this->restoreBackup($routeManager);
                break;

            case 'clean':
                $this->cleanBackups($routeManager);
                break;

            default:
                $this->error("Invalid action: {$action}");
                $this->info("Available actions: list, backup, restore, clean");
                return 1;
        }

        return 0;
    }

    protected function listBackups(SmartCrudRouteManager $routeManager)
    {
        $backups = $routeManager->listBackups();

        if (empty($backups)) {
            $this->info('No backups found.');
            return;
        }

        $this->info('Available Route Backups:');
        $this->newLine();

        $this->table(
            ['#', 'Filename', 'Size', 'Date'],
            collect($backups)->map(function ($backup, $index) {
                return [
                    $index + 1,
                    $backup['filename'],
                    $this->formatBytes($backup['size']),
                    $backup['modified_human'],
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('Total backups: ' . count($backups));
        $this->line('Location: storage/app/private/routes');
    }

    protected function createBackup(SmartCrudRouteManager $routeManager)
    {
        $this->info('Creating route backup...');

        $backupPath = $routeManager->backup();

        if ($backupPath) {
            $this->info('✓ Backup created successfully!');
            $this->line('   Path: ' . $backupPath);
        } else {
            $this->error('✗ Failed to create backup. Route file may not exist.');
        }
    }

    protected function restoreBackup(SmartCrudRouteManager $routeManager)
    {
        $file = $this->option('file');

        if (!$file) {
            $backups = $routeManager->listBackups();

            if (empty($backups)) {
                $this->error('No backups available to restore.');
                return;
            }

            $this->listBackups($routeManager);
            $this->newLine();

            $choice = $this->ask('Enter backup number to restore (or filename)');

            if (is_numeric($choice)) {
                $index = (int)$choice - 1;
                if (isset($backups[$index])) {
                    $file = $backups[$index]['path'];
                } else {
                    $this->error('Invalid backup number.');
                    return;
                }
            } else {
                $file = storage_path('app/private/routes/' . $choice);
            }
        } else {
            // If relative path provided, prepend storage path
            if (!str_starts_with($file, '/') && !str_starts_with($file, storage_path())) {
                $file = storage_path('app/private/routes/' . $file);
            }
        }

        if (!$this->confirm("Are you sure you want to restore from this backup?\nThis will overwrite the current route file.", false)) {
            $this->info('Restore cancelled.');
            return;
        }

        $this->info('Restoring route backup...');

        if ($routeManager->restore($file)) {
            $this->info('✓ Routes restored successfully!');
            $this->warn('⚠ Remember to run: php artisan route:clear');
        } else {
            $this->error('✗ Failed to restore backup. File may not exist.');
        }
    }

    protected function cleanBackups(SmartCrudRouteManager $routeManager)
    {
        $keep = (int)$this->option('keep');

        $backups = $routeManager->listBackups();
        $total = count($backups);

        if ($total <= $keep) {
            $this->info("No cleanup needed. Current backups ({$total}) <= keep limit ({$keep})");
            return;
        }

        $toDelete = $total - $keep;

        if (!$this->confirm("This will delete {$toDelete} old backup(s), keeping the {$keep} most recent. Continue?", true)) {
            $this->info('Cleanup cancelled.');
            return;
        }

        $this->info('Cleaning old backups...');

        $deleted = $routeManager->cleanOldBackups($keep);

        $this->info("✓ Deleted {$deleted} old backup(s)");
        $this->info("✓ Kept {$keep} most recent backup(s)");
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
