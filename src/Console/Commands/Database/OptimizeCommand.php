<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\DatabaseOptimizationService;

class OptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:db:optimize
                            {--connection= : Database connection to optimize}
                            {--tables=* : Specific tables to optimize}
                            {--analyze : Show database analysis}';

    /**
     * The console command description.
     */
    protected $description = 'Optimize database tables and analyze performance';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseOptimizationService $service): int
    {
        $connection = $this->option('connection') ?? config('database.default');
        
        // Show analysis if requested
        if ($this->option('analyze')) {
            return $this->showAnalysis($service, $connection);
        }
        
        $this->info('Optimizing database...');
        
        $tables = $this->option('tables');
        $tables = !empty($tables) ? $tables : null;
        
        $result = $service->optimize([
            'connection' => $connection,
            'tables' => $tables,
        ]);
        
        if ($result['success']) {
            $this->info('✓ Database optimized successfully!');
            $this->newLine();
            
            $this->table(
                ['Table', 'Status'],
                collect($result['results'])->map(function ($status, $table) {
                    return [$table, $status];
                })->toArray()
            );
            
            return self::SUCCESS;
        }
        
        $this->error('✗ Optimization failed: ' . $result['error']);
        return self::FAILURE;
    }
    
    /**
     * Show database analysis.
     */
    protected function showAnalysis(DatabaseOptimizationService $service, string $connection): int
    {
        $this->info('Analyzing database...');
        
        $analysis = $service->analyze($connection);
        
        // Database info
        $this->newLine();
        $this->info('Database Information:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Connection', $analysis['connection']],
                ['Driver', $analysis['driver']],
                ['Size', $analysis['size']['formatted']],
            ]
        );
        
        // Table statistics
        if (!empty($analysis['tables'])) {
            $this->newLine();
            $this->info('Table Statistics:');
            
            $headers = array_keys($analysis['tables'][0]);
            $rows = collect($analysis['tables'])->map(function ($table) {
                return array_values($table);
            })->toArray();
            
            $this->table($headers, $rows);
        }
        
        // Index statistics
        if (!empty($analysis['indexes'])) {
            $this->newLine();
            $this->info('Index Statistics:');
            
            foreach ($analysis['indexes'] as $tableIndex) {
                $this->line('Table: ' . $tableIndex['table']);
                
                foreach ($tableIndex['indexes'] as $index) {
                    if (isset($index['columns'])) {
                        $this->line('  - ' . $index['name'] . ' (' . implode(', ', $index['columns']) . ')' . ($index['unique'] ? ' [UNIQUE]' : ''));
                    } else {
                        $this->line('  - ' . $index['name']);
                    }
                }
                
                $this->newLine();
            }
        }
        
        return self::SUCCESS;
    }
}
