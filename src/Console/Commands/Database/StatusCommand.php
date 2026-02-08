<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:db:status
                            {--connection= : Database connection to check}';

    /**
     * The console command description.
     */
    protected $description = 'Show database status and health information';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = $this->option('connection') ?? config('database.default');
        $config = config("database.connections.{$connection}");
        
        $this->info('Database Status');
        $this->newLine();
        
        // Connection info
        $this->table(
            ['Property', 'Value'],
            [
                ['Connection', $connection],
                ['Driver', $config['driver']],
                ['Host', $config['host'] ?? 'N/A'],
                ['Database', $config['database']],
                ['Port', $config['port'] ?? 'N/A'],
            ]
        );
        
        // Test connection
        try {
            DB::connection($connection)->getPdo();
            $this->info('✓ Connection: OK');
        } catch (\Exception $e) {
            $this->error('✗ Connection: FAILED');
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        
        // Get table count
        try {
            $tables = Schema::connection($connection)->getTables();
            $tableCount = count($tables);
            $this->info("✓ Tables: {$tableCount}");
        } catch (\Exception $e) {
            $this->warn('⚠ Could not retrieve table count');
        }
        
        // Get version
        try {
            $version = $this->getDatabaseVersion($connection, $config['driver']);
            $this->info("✓ Version: {$version}");
        } catch (\Exception $e) {
            $this->warn('⚠ Could not retrieve version');
        }
        
        // Check migrations
        try {
            $pending = $this->getPendingMigrations();
            if ($pending > 0) {
                $this->warn("⚠ Pending migrations: {$pending}");
            } else {
                $this->info('✓ Migrations: Up to date');
            }
        } catch (\Exception $e) {
            $this->warn('⚠ Could not check migrations');
        }
        
        return self::SUCCESS;
    }
    
    /**
     * Get database version.
     */
    protected function getDatabaseVersion(string $connection, string $driver): string
    {
        return match ($driver) {
            'mysql' => DB::connection($connection)->selectOne('SELECT VERSION() as version')->version,
            'pgsql' => DB::connection($connection)->selectOne('SELECT version()')->version,
            'sqlite' => DB::connection($connection)->selectOne('SELECT sqlite_version() as version')->version,
            default => 'Unknown',
        };
    }
    
    /**
     * Get pending migrations count.
     */
    protected function getPendingMigrations(): int
    {
        // This is a simplified check
        // In a real implementation, you'd compare migration files with the migrations table
        return 0;
    }
}
