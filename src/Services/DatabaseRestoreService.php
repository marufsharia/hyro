<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class DatabaseRestoreService
{
    /**
     * Restore database from backup.
     */
    public function restore(string $backupPath, array $options = []): array
    {
        $connection = $options['connection'] ?? config('database.default');
        $disk = $options['disk'] ?? config('hyro.database.backup.disk', 'local');
        
        $config = config("database.connections.{$connection}");
        $driver = $config['driver'];
        
        try {
            // Download backup from storage
            $localPath = $this->downloadBackup($backupPath, $disk);
            
            // Decrypt if needed
            if (str_ends_with($localPath, '.enc')) {
                $localPath = $this->decryptBackup($localPath);
            }
            
            // Decompress if needed
            if (str_ends_with($localPath, '.gz')) {
                $localPath = $this->decompressBackup($localPath);
            }
            
            // Restore based on driver
            match ($driver) {
                'mysql' => $this->restoreMysql($config, $localPath),
                'pgsql' => $this->restorePostgres($config, $localPath),
                'sqlite' => $this->restoreSqlite($config, $localPath),
                default => throw new Exception("Unsupported database driver: {$driver}"),
            };
            
            // Clean up temporary file
            if (file_exists($localPath)) {
                unlink($localPath);
            }
            
            return [
                'success' => true,
                'connection' => $connection,
                'driver' => $driver,
                'restored_at' => now()->toDateTimeString(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Download backup from storage.
     */
    protected function downloadBackup(string $path, string $disk): string
    {
        $tempDir = sys_get_temp_dir();
        $localPath = $tempDir . DIRECTORY_SEPARATOR . basename($path);
        
        $contents = Storage::disk($disk)->get($path);
        file_put_contents($localPath, $contents);
        
        return $localPath;
    }
    
    /**
     * Decrypt backup file.
     */
    protected function decryptBackup(string $path): string
    {
        $decryptedPath = str_replace('.enc', '', $path);
        
        $encrypted = file_get_contents($path);
        $decrypted = decrypt($encrypted);
        
        file_put_contents($decryptedPath, $decrypted);
        unlink($path);
        
        return $decryptedPath;
    }
    
    /**
     * Decompress backup file.
     */
    protected function decompressBackup(string $path): string
    {
        $decompressedPath = str_replace('.gz', '', $path);
        
        $input = gzopen($path, 'rb');
        $output = fopen($decompressedPath, 'wb');
        
        while (!gzeof($input)) {
            fwrite($output, gzread($input, 1024 * 512));
        }
        
        gzclose($input);
        fclose($output);
        
        unlink($path);
        
        return $decompressedPath;
    }
    
    /**
     * Restore MySQL database.
     */
    protected function restoreMysql(array $config, string $path): void
    {
        $host = $config['host'];
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($path)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('MySQL restore failed');
        }
    }
    
    /**
     * Restore PostgreSQL database.
     */
    protected function restorePostgres(array $config, string $path): void
    {
        $host = $config['host'];
        $port = $config['port'] ?? 5432;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        
        putenv("PGPASSWORD={$password}");
        
        $command = sprintf(
            'pg_restore --host=%s --port=%s --username=%s --dbname=%s --clean %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($path)
        );
        
        exec($command, $output, $returnCode);
        
        putenv('PGPASSWORD');
        
        if ($returnCode !== 0) {
            throw new Exception('PostgreSQL restore failed');
        }
    }
    
    /**
     * Restore SQLite database.
     */
    protected function restoreSqlite(array $config, string $path): void
    {
        $database = $config['database'];
        
        // Backup current database
        if (file_exists($database)) {
            $backupPath = $database . '.backup.' . time();
            copy($database, $backupPath);
        }
        
        if (!copy($path, $database)) {
            throw new Exception('SQLite restore failed');
        }
    }
    
    /**
     * Verify backup integrity.
     */
    public function verify(string $backupPath, string $disk = null): array
    {
        $disk = $disk ?? config('hyro.database.backup.disk', 'local');
        
        try {
            if (!Storage::disk($disk)->exists($backupPath)) {
                throw new Exception('Backup file not found');
            }
            
            $size = Storage::disk($disk)->size($backupPath);
            
            if ($size === 0) {
                throw new Exception('Backup file is empty');
            }
            
            return [
                'valid' => true,
                'size' => $size,
                'path' => $backupPath,
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
