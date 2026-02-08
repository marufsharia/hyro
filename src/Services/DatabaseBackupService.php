<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class DatabaseBackupService
{
    /**
     * Create a database backup.
     */
    public function backup(array $options = []): array
    {
        $connection = $options['connection'] ?? config('database.default');
        $disk = $options['disk'] ?? config('hyro.database.backup.disk', 'local');
        $compress = $options['compress'] ?? config('hyro.database.backup.compress', true);
        $encrypt = $options['encrypt'] ?? config('hyro.database.backup.encrypt', false);
        
        $config = config("database.connections.{$connection}");
        $driver = $config['driver'];
        
        $filename = $this->generateFilename($connection);
        $path = $this->getBackupPath($filename);
        
        try {
            // Create backup based on driver
            $backupPath = match ($driver) {
                'mysql' => $this->backupMysql($config, $path),
                'pgsql' => $this->backupPostgres($config, $path),
                'sqlite' => $this->backupSqlite($config, $path),
                default => throw new Exception("Unsupported database driver: {$driver}"),
            };
            
            // Compress if enabled
            if ($compress) {
                $backupPath = $this->compressBackup($backupPath);
            }
            
            // Encrypt if enabled
            if ($encrypt) {
                $backupPath = $this->encryptBackup($backupPath);
            }
            
            // Store to disk
            $storedPath = $this->storeBackup($backupPath, $disk);
            
            // Clean up temporary file
            if (file_exists($backupPath)) {
                unlink($backupPath);
            }
            
            return [
                'success' => true,
                'path' => $storedPath,
                'size' => Storage::disk($disk)->size($storedPath),
                'connection' => $connection,
                'driver' => $driver,
                'compressed' => $compress,
                'encrypted' => $encrypt,
                'created_at' => now()->toDateTimeString(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Backup MySQL database.
     */
    protected function backupMysql(array $config, string $path): string
    {
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('MySQL backup failed');
        }
        
        return $path;
    }
    
    /**
     * Backup PostgreSQL database.
     */
    protected function backupPostgres(array $config, string $path): string
    {
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        putenv("PGPASSWORD={$password}");
        
        $command = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --format=c --file=%s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($path),
            escapeshellarg($database)
        );
        
        exec($command, $output, $returnCode);
        
        putenv('PGPASSWORD');
        
        if ($returnCode !== 0) {
            throw new Exception('PostgreSQL backup failed');
        }
        
        return $path;
    }
    
    /**
     * Backup SQLite database.
     */
    protected function backupSqlite(array $config, string $path): string
    {
        $database = $config['database'];
        
        if (!file_exists($database)) {
            throw new Exception("SQLite database not found: {$database}");
        }
        
        if (!copy($database, $path)) {
            throw new Exception('SQLite backup failed');
        }
        
        return $path;
    }
    
    /**
     * Compress backup file.
     */
    protected function compressBackup(string $path): string
    {
        $compressedPath = $path . '.gz';
        
        $input = fopen($path, 'rb');
        $output = gzopen($compressedPath, 'wb9');
        
        while (!feof($input)) {
            gzwrite($output, fread($input, 1024 * 512));
        }
        
        fclose($input);
        gzclose($output);
        
        unlink($path);
        
        return $compressedPath;
    }
    
    /**
     * Encrypt backup file.
     */
    protected function encryptBackup(string $path): string
    {
        $key = config('hyro.database.backup.encryption_key') ?? config('app.key');
        $encryptedPath = $path . '.enc';
        
        $data = file_get_contents($path);
        $encrypted = encrypt($data);
        
        file_put_contents($encryptedPath, $encrypted);
        unlink($path);
        
        return $encryptedPath;
    }
    
    /**
     * Store backup to disk.
     */
    protected function storeBackup(string $path, string $disk): string
    {
        $filename = basename($path);
        $storagePath = 'backups/' . $filename;
        
        Storage::disk($disk)->put($storagePath, file_get_contents($path));
        
        return $storagePath;
    }
    
    /**
     * Generate backup filename.
     */
    protected function generateFilename(string $connection): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $hash = Str::random(8);
        
        return "backup_{$connection}_{$timestamp}_{$hash}.sql";
    }
    
    /**
     * Get backup path.
     */
    protected function getBackupPath(string $filename): string
    {
        $tempDir = sys_get_temp_dir();
        return $tempDir . DIRECTORY_SEPARATOR . $filename;
    }
    
    /**
     * List all backups.
     */
    public function list(string $disk = null): array
    {
        $disk = $disk ?? config('hyro.database.backup.disk', 'local');
        
        $files = Storage::disk($disk)->files('backups');
        
        return collect($files)->map(function ($file) use ($disk) {
            return [
                'path' => $file,
                'name' => basename($file),
                'size' => Storage::disk($disk)->size($file),
                'modified' => Storage::disk($disk)->lastModified($file),
                'modified_human' => now()->createFromTimestamp(
                    Storage::disk($disk)->lastModified($file)
                )->diffForHumans(),
            ];
        })->sortByDesc('modified')->values()->toArray();
    }
    
    /**
     * Delete a backup.
     */
    public function delete(string $path, string $disk = null): bool
    {
        $disk = $disk ?? config('hyro.database.backup.disk', 'local');
        
        return Storage::disk($disk)->delete($path);
    }
    
    /**
     * Clean old backups.
     */
    public function cleanup(int $keepDays = null): int
    {
        $keepDays = $keepDays ?? config('hyro.database.backup.retention_days', 30);
        $disk = config('hyro.database.backup.disk', 'local');
        
        $cutoff = now()->subDays($keepDays)->timestamp;
        $deleted = 0;
        
        foreach ($this->list($disk) as $backup) {
            if ($backup['modified'] < $cutoff) {
                if ($this->delete($backup['path'], $disk)) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
}
