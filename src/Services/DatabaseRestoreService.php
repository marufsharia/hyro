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
        
        // Try to find mysql client
        $mysql = $this->findMysqlClient();
        
        if (!$mysql) {
            // Fallback to PHP-based restore
            $this->restoreMysqlPhp($config, $path);
            return;
        }
        
        // Build command based on OS
        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s %s < "%s" 2>&1',
                $mysql,
                $host,
                $port,
                $username,
                $password,
                $database,
                $path
            );
        } else {
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s %s < %s',
                escapeshellarg($mysql),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );
        }
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            // Fallback to PHP-based restore
            $this->restoreMysqlPhp($config, $path);
        }
    }
    
    /**
     * Find mysql client executable.
     */
    protected function findMysqlClient(): ?string
    {
        // Check if mysql is in PATH
        if (PHP_OS_FAMILY === 'Windows') {
            exec('where mysql 2>nul', $output, $returnCode);
        } else {
            exec('which mysql 2>/dev/null', $output, $returnCode);
        }
        
        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }
        
        // Check common installation paths
        $commonPaths = [
            // Windows paths
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
            'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe',
            'C:\xampp\mysql\bin\mysql.exe',
            'C:\wamp64\bin\mysql\mysql8.0.27\bin\mysql.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe',
            // Linux/Mac paths
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/usr/local/mysql/bin/mysql',
        ];
        
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Restore MySQL database using PHP (fallback method).
     */
    protected function restoreMysqlPhp(array $config, string $path): void
    {
        $sql = file_get_contents($path);
        
        if ($sql === false) {
            throw new Exception('Could not read backup file');
        }
        
        // Split into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function ($statement) {
                return !empty($statement) && !str_starts_with($statement, '--');
            }
        );
        
        $connection = $config['connection'] ?? 'mysql';
        
        DB::connection($connection)->unprepared('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    DB::connection($connection)->unprepared($statement);
                } catch (\Exception $e) {
                    // Continue on error (some statements might fail on restore)
                    continue;
                }
            }
        }
        
        DB::connection($connection)->unprepared('SET FOREIGN_KEY_CHECKS=1');
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
