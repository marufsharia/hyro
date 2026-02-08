<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class DatabaseOptimizationService
{
    /**
     * Optimize database tables.
     */
    public function optimize(array $options = []): array
    {
        $connection = $options['connection'] ?? config('database.default');
        $tables = $options['tables'] ?? null;
        
        $config = config("database.connections.{$connection}");
        $driver = $config['driver'];
        
        try {
            $results = match ($driver) {
                'mysql' => $this->optimizeMysql($connection, $tables),
                'pgsql' => $this->optimizePostgres($connection, $tables),
                'sqlite' => $this->optimizeSqlite($connection),
                default => throw new Exception("Unsupported database driver: {$driver}"),
            };
            
            return [
                'success' => true,
                'driver' => $driver,
                'results' => $results,
                'optimized_at' => now()->toDateTimeString(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Optimize MySQL tables.
     */
    protected function optimizeMysql(string $connection, ?array $tables): array
    {
        $tables = $tables ?? $this->getAllTables($connection);
        $results = [];
        
        foreach ($tables as $table) {
            try {
                DB::connection($connection)->statement("OPTIMIZE TABLE `{$table}`");
                $results[$table] = 'optimized';
            } catch (Exception $e) {
                $results[$table] = 'failed: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Optimize PostgreSQL tables.
     */
    protected function optimizePostgres(string $connection, ?array $tables): array
    {
        $tables = $tables ?? $this->getAllTables($connection);
        $results = [];
        
        foreach ($tables as $table) {
            try {
                DB::connection($connection)->statement("VACUUM ANALYZE {$table}");
                $results[$table] = 'optimized';
            } catch (Exception $e) {
                $results[$table] = 'failed: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Optimize SQLite database.
     */
    protected function optimizeSqlite(string $connection): array
    {
        try {
            DB::connection($connection)->statement('VACUUM');
            return ['database' => 'optimized'];
        } catch (Exception $e) {
            return ['database' => 'failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Analyze database performance.
     */
    public function analyze(string $connection = null): array
    {
        $connection = $connection ?? config('database.default');
        $driver = config("database.connections.{$connection}.driver");
        
        return [
            'connection' => $connection,
            'driver' => $driver,
            'tables' => $this->getTableStats($connection),
            'indexes' => $this->getIndexStats($connection),
            'size' => $this->getDatabaseSize($connection),
        ];
    }
    
    /**
     * Get table statistics.
     */
    protected function getTableStats(string $connection): array
    {
        $driver = config("database.connections.{$connection}.driver");
        
        return match ($driver) {
            'mysql' => $this->getMysqlTableStats($connection),
            'pgsql' => $this->getPostgresTableStats($connection),
            'sqlite' => $this->getSqliteTableStats($connection),
            default => [],
        };
    }
    
    /**
     * Get MySQL table statistics.
     */
    protected function getMysqlTableStats(string $connection): array
    {
        $database = config("database.connections.{$connection}.database");
        
        $stats = DB::connection($connection)
            ->select("
                SELECT 
                    TABLE_NAME as name,
                    TABLE_ROWS as rows,
                    DATA_LENGTH as data_size,
                    INDEX_LENGTH as index_size,
                    (DATA_LENGTH + INDEX_LENGTH) as total_size
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
            ", [$database]);
        
        return collect($stats)->map(function ($stat) {
            return [
                'name' => $stat->name,
                'rows' => (int) $stat->rows,
                'data_size' => $this->formatBytes($stat->data_size),
                'index_size' => $this->formatBytes($stat->index_size),
                'total_size' => $this->formatBytes($stat->total_size),
            ];
        })->toArray();
    }
    
    /**
     * Get PostgreSQL table statistics.
     */
    protected function getPostgresTableStats(string $connection): array
    {
        $stats = DB::connection($connection)
            ->select("
                SELECT 
                    schemaname || '.' || tablename as name,
                    n_live_tup as rows,
                    pg_total_relation_size(schemaname || '.' || tablename) as total_size
                FROM pg_stat_user_tables
                ORDER BY pg_total_relation_size(schemaname || '.' || tablename) DESC
            ");
        
        return collect($stats)->map(function ($stat) {
            return [
                'name' => $stat->name,
                'rows' => (int) $stat->rows,
                'total_size' => $this->formatBytes($stat->total_size),
            ];
        })->toArray();
    }
    
    /**
     * Get SQLite table statistics.
     */
    protected function getSqliteTableStats(string $connection): array
    {
        $tables = $this->getAllTables($connection);
        
        return collect($tables)->map(function ($table) use ($connection) {
            $count = DB::connection($connection)->table($table)->count();
            
            return [
                'name' => $table,
                'rows' => $count,
            ];
        })->toArray();
    }
    
    /**
     * Get index statistics.
     */
    protected function getIndexStats(string $connection): array
    {
        $driver = config("database.connections.{$connection}.driver");
        
        return match ($driver) {
            'mysql' => $this->getMysqlIndexStats($connection),
            'pgsql' => $this->getPostgresIndexStats($connection),
            default => [],
        };
    }
    
    /**
     * Get MySQL index statistics.
     */
    protected function getMysqlIndexStats(string $connection): array
    {
        $database = config("database.connections.{$connection}.database");
        
        $stats = DB::connection($connection)
            ->select("
                SELECT 
                    TABLE_NAME as table_name,
                    INDEX_NAME as index_name,
                    NON_UNIQUE as non_unique,
                    SEQ_IN_INDEX as sequence,
                    COLUMN_NAME as column_name
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ?
                ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX
            ", [$database]);
        
        return collect($stats)->groupBy('table_name')->map(function ($indexes, $table) {
            return [
                'table' => $table,
                'indexes' => $indexes->groupBy('index_name')->map(function ($columns, $index) {
                    return [
                        'name' => $index,
                        'unique' => $columns->first()->non_unique == 0,
                        'columns' => $columns->pluck('column_name')->toArray(),
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();
    }
    
    /**
     * Get PostgreSQL index statistics.
     */
    protected function getPostgresIndexStats(string $connection): array
    {
        $stats = DB::connection($connection)
            ->select("
                SELECT 
                    schemaname || '.' || tablename as table_name,
                    indexname as index_name,
                    indexdef as definition
                FROM pg_indexes
                WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
                ORDER BY tablename, indexname
            ");
        
        return collect($stats)->groupBy('table_name')->map(function ($indexes, $table) {
            return [
                'table' => $table,
                'indexes' => $indexes->map(function ($index) {
                    return [
                        'name' => $index->index_name,
                        'definition' => $index->definition,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();
    }
    
    /**
     * Get database size.
     */
    protected function getDatabaseSize(string $connection): array
    {
        $driver = config("database.connections.{$connection}.driver");
        
        return match ($driver) {
            'mysql' => $this->getMysqlDatabaseSize($connection),
            'pgsql' => $this->getPostgresDatabaseSize($connection),
            'sqlite' => $this->getSqliteDatabaseSize($connection),
            default => [],
        };
    }
    
    /**
     * Get MySQL database size.
     */
    protected function getMysqlDatabaseSize(string $connection): array
    {
        $database = config("database.connections.{$connection}.database");
        
        $size = DB::connection($connection)
            ->selectOne("
                SELECT 
                    SUM(DATA_LENGTH + INDEX_LENGTH) as size
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
            ", [$database]);
        
        return [
            'bytes' => (int) $size->size,
            'formatted' => $this->formatBytes($size->size),
        ];
    }
    
    /**
     * Get PostgreSQL database size.
     */
    protected function getPostgresDatabaseSize(string $connection): array
    {
        $database = config("database.connections.{$connection}.database");
        
        $size = DB::connection($connection)
            ->selectOne("SELECT pg_database_size(?) as size", [$database]);
        
        return [
            'bytes' => (int) $size->size,
            'formatted' => $this->formatBytes($size->size),
        ];
    }
    
    /**
     * Get SQLite database size.
     */
    protected function getSqliteDatabaseSize(string $connection): array
    {
        $database = config("database.connections.{$connection}.database");
        
        $size = file_exists($database) ? filesize($database) : 0;
        
        return [
            'bytes' => $size,
            'formatted' => $this->formatBytes($size),
        ];
    }
    
    /**
     * Get all tables.
     */
    protected function getAllTables(string $connection): array
    {
        $tables = Schema::connection($connection)->getTables();
        
        // Extract table names from the result
        return collect($tables)->map(function ($table) {
            // Handle different formats returned by different drivers
            if (is_array($table)) {
                return $table['name'] ?? $table['tablename'] ?? $table['table_name'] ?? null;
            }
            return $table->name ?? $table->tablename ?? $table->table_name ?? null;
        })->filter()->toArray();
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
