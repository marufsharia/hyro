<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\DatabaseBackupService;
use Marufsharia\Hyro\Services\DatabaseRestoreService;

class RestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:db:restore
                            {backup? : Path to backup file}
                            {--connection= : Database connection to restore}
                            {--disk= : Storage disk to use}
                            {--list : List available backups}
                            {--force : Force restore without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Restore database from backup';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseRestoreService $restoreService, DatabaseBackupService $backupService): int
    {
        // List backups if requested
        if ($this->option('list')) {
            return $this->listBackups($backupService);
        }
        
        $backupPath = $this->argument('backup');
        
        // If no backup specified, show list and prompt
        if (!$backupPath) {
            $backups = $backupService->list($this->option('disk'));
            
            if (empty($backups)) {
                $this->error('No backups found.');
                return self::FAILURE;
            }
            
            $this->table(
                ['#', 'Name', 'Size', 'Modified'],
                collect($backups)->map(function ($backup, $index) {
                    return [
                        $index + 1,
                        $backup['name'],
                        $this->formatBytes($backup['size']),
                        $backup['modified_human'],
                    ];
                })->toArray()
            );
            
            $choice = $this->ask('Enter backup number to restore (or 0 to cancel)');
            
            if ($choice == 0 || !isset($backups[$choice - 1])) {
                $this->info('Restore cancelled.');
                return self::SUCCESS;
            }
            
            $backupPath = $backups[$choice - 1]['path'];
        }
        
        // Verify backup
        $verification = $restoreService->verify($backupPath, $this->option('disk'));
        
        if (!$verification['valid']) {
            $this->error('✗ Invalid backup: ' . $verification['error']);
            return self::FAILURE;
        }
        
        // Confirm restore
        if (!$this->option('force')) {
            $this->warn('⚠ WARNING: This will replace your current database!');
            $this->warn('⚠ Make sure you have a recent backup before proceeding.');
            $this->newLine();
            
            if (!$this->confirm('Are you sure you want to restore from this backup?')) {
                $this->info('Restore cancelled.');
                return self::SUCCESS;
            }
        }
        
        $this->info('Restoring database...');
        
        $result = $restoreService->restore($backupPath, [
            'connection' => $this->option('connection'),
            'disk' => $this->option('disk'),
        ]);
        
        if ($result['success']) {
            $this->info('✓ Database restored successfully!');
            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['Connection', $result['connection']],
                    ['Driver', $result['driver']],
                    ['Restored', $result['restored_at']],
                ]
            );
            
            return self::SUCCESS;
        }
        
        $this->error('✗ Restore failed: ' . $result['error']);
        return self::FAILURE;
    }
    
    /**
     * List available backups.
     */
    protected function listBackups(DatabaseBackupService $service): int
    {
        $backups = $service->list($this->option('disk'));
        
        if (empty($backups)) {
            $this->info('No backups found.');
            return self::SUCCESS;
        }
        
        $this->table(
            ['Name', 'Size', 'Modified'],
            collect($backups)->map(function ($backup) {
                return [
                    $backup['name'],
                    $this->formatBytes($backup['size']),
                    $backup['modified_human'],
                ];
            })->toArray()
        );
        
        $this->info('Total backups: ' . count($backups));
        
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
