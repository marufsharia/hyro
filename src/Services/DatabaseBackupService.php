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
        
        // Try to find mysqldump in common locations
        $mysqldump = $this->findMysqldump();
        
        if (!$mysqldump) {
            // Fallback to PHP-based backup
            return $this->backupMysqlPhp($config, $path);
        }
        
        // Build command based on OS
        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s %s > "%s" 2>&1',
                $mysqldump,
                $host,
                $port,
                $username,
                $password,
                $database,
                $path
            );
        } else {
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s %s > %s',
                escapeshellarg($mysqldump),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($path)
            );
        }
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($path) || filesize($path) === 0) {
            // Fallback to PHP-based backup
            return $this->backupMysqlPhp($config, $path);
        }
        
        return $path;
    }
    
    /**
     * Find mysqldump executable.
     */
    protected function findMysqldump(): ?string
    {
        // Check if mysqldump is in PATH
        if (PHP_OS_FAMILY === 'Windows') {
            exec('where mysqldump 2>nul', $output, $returnCode);
        } else {
            exec('which mysqldump 2>/dev/null', $output, $returnCode);
        }
        
        if ($returnCode === 0 && !empty($output)) {
            return trim($output[0]);
        }
        
        // Check common installation paths
        $commonPaths = [
            // Windows paths
            'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe',
            'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysqldump.exe',
            'C:\xampp\mysql\bin\mysqldump.exe',
            'C:\wamp64\bin\mysql\mysql8.0.27\bin\mysqldump.exe',
            'C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe',
            // Linux/Mac paths
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/usr/local/mysql/bin/mysqldump',
        ];
        
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Backup MySQL database using PHP (fallback method).
     */
    protected function backupMysqlPhp(array $config, string $path): string
    {
        $database = $config['database'];
        
        $handle = fopen($path, 'w');
        
        if (!$handle) {
            throw new Exception('Could not create backup file');
        }
        
        // Write header
        fwrite($handle, "-- MySQL Backup\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: {$database}\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        
        // Get all tables
        $tables = DB::connection($config['connection'] ?? 'mysql')
            ->select('SHOW TABLES');
        
        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            
            // Get table structure
            $createTable = DB::connection($config['connection'] ?? 'mysql')
                ->select("SHOW CREATE TABLE `{$tableName}`");
            
            fwrite($handle, "-- Table: {$tableName}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createTable[0]->{'Create Table'} . ";\n\n");
            
            // Get table data
            $rows = DB::connection($config['connection'] ?? 'mysql')
                ->table($tableName)
                ->get();
            
            if ($rows->isNotEmpty()) {
                fwrite($handle, "-- Data for table: {$tableName}\n");
                
                foreach ($rows as $row) {
                    $values = array_map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, (array) $row);
                    
                    $columns = array_keys((array) $row);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    $valueList = implode(', ', $values);
                    
                    fwrite($handle, "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n");
                }
                
                fwrite($handle, "\n");
            }
        }
        
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
        
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
