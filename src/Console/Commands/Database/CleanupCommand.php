<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\DatabaseBackupService;

class CleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:db:cleanup
                            {--days= : Keep backups newer than this many days}
                            {--disk= : Storage disk to use}
                            {--dry-run : Show what would be deleted without deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old database backups';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $service): int
    {
        $days = $this->option('days') ?? config('hyro.database.backup.retention_days', 30);
        $disk = $this->option('disk');
        $dryRun = $this->option('dry-run');
        
        $this->info("Cleaning up backups older than {$days} days...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be deleted');
        }
        
        $backups = $service->list($disk);
        $cutoff = now()->subDays($days)->timestamp;
        $toDelete = [];
        
        foreach ($backups as $backup) {
            if ($backup['modified'] < $cutoff) {
                $toDelete[] = $backup;
            }
        }
        
        if (empty($toDelete)) {
            $this->info('No backups to clean up.');
            return self::SUCCESS;
        }
        
        $this->table(
            ['Name', 'Size', 'Age'],
            collect($toDelete)->map(function ($backup) {
                return [
                    $backup['name'],
                    $this->formatBytes($backup['size']),
                    $backup['modified_human'],
                ];
            })->toArray()
        );
        
        if ($dryRun) {
            $this->info('Would delete ' . count($toDelete) . ' backup(s)');
            return self::SUCCESS;
        }
        
        if (!$this->confirm('Delete these ' . count($toDelete) . ' backup(s)?')) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }
        
        $deleted = $service->cleanup($days);
        
        $this->info("✓ Deleted {$deleted} backup(s)");
        
        return self::SUCCESS;
    }
    
    /**
     * Format bytes to human readable.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
