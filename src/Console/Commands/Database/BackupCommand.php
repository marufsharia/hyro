<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\DatabaseBackupService;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:db:backup
                            {--connection= : Database connection to backup}
                            {--disk= : Storage disk to use}
                            {--compress : Compress the backup}
                            {--encrypt : Encrypt the backup}
                            {--no-compress : Do not compress the backup}
                            {--no-encrypt : Do not encrypt the backup}';

    /**
     * The console command description.
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $service): int
    {
        $this->info('Creating database backup...');
        
        $options = [
            'connection' => $this->option('connection'),
            'disk' => $this->option('disk'),
            'compress' => $this->option('no-compress') ? false : ($this->option('compress') ?? true),
            'encrypt' => $this->option('no-encrypt') ? false : ($this->option('encrypt') ?? false),
        ];
        
        $result = $service->backup($options);
        
        if ($result['success']) {
            $this->info('✓ Backup created successfully!');
            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['Path', $result['path']],
                    ['Size', $this->formatBytes($result['size'])],
                    ['Connection', $result['connection']],
                    ['Driver', $result['driver']],
                    ['Compressed', $result['compressed'] ? 'Yes' : 'No'],
                    ['Encrypted', $result['encrypted'] ? 'Yes' : 'No'],
                    ['Created', $result['created_at']],
                ]
            );
            
            return self::SUCCESS;
        }
        
        $this->error('✗ Backup failed: ' . $result['error']);
        return self::FAILURE;
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
