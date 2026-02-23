<?php

namespace Marufsharia\Hyro\Plugin\Console\Commands;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Plugin\Support\HookManager;

class ListHooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:hooks:list 
                            {--type=all : Type of hooks to list (all, actions, filters)}
                            {--plugin= : Filter by plugin ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered Hyro hooks';

    /**
     * Execute the console command.
     */
    public function handle(HookManager $hookManager): int
    {
        $type = $this->option('type');
        $pluginFilter = $this->option('plugin');

        $hooks = $hookManager->getHooks($type);

        if ($type === 'all') {
            $this->displayActions($hooks['actions'] ?? [], $pluginFilter);
            $this->displayFilters($hooks['filters'] ?? [], $pluginFilter);
        } elseif ($type === 'actions') {
            $this->displayActions($hooks, $pluginFilter);
        } elseif ($type === 'filters') {
            $this->displayFilters($hooks, $pluginFilter);
        }

        return self::SUCCESS;
    }

    /**
     * Display action hooks
     */
    protected function displayActions(array $actions, ?string $pluginFilter): void
    {
        if (empty($actions)) {
            $this->info('No action hooks registered.');
            return;
        }

        $this->info('Action Hooks:');
        $this->line('');

        $tableData = [];

        foreach ($actions as $name => $callbacks) {
            foreach ($callbacks as $callback) {
                if ($pluginFilter && $callback['plugin_id'] !== $pluginFilter) {
                    continue;
                }

                $tableData[] = [
                    $name,
                    $callback['plugin_id'],
                    $callback['priority'],
                    count($callbacks),
                ];
            }
        }

        if (empty($tableData)) {
            $this->warn("No action hooks found for plugin: {$pluginFilter}");
            return;
        }

        $this->table(
            ['Hook Name', 'Plugin', 'Priority', 'Total Listeners'],
            $tableData
        );

        $this->line('');
    }

    /**
     * Display filter hooks
     */
    protected function displayFilters(array $filters, ?string $pluginFilter): void
    {
        if (empty($filters)) {
            $this->info('No filter hooks registered.');
            return;
        }

        $this->info('Filter Hooks:');
        $this->line('');

        $tableData = [];

        foreach ($filters as $name => $callbacks) {
            foreach ($callbacks as $callback) {
                if ($pluginFilter && $callback['plugin_id'] !== $pluginFilter) {
                    continue;
                }

                $tableData[] = [
                    $name,
                    $callback['plugin_id'],
                    $callback['priority'],
                    count($callbacks),
                ];
            }
        }

        if (empty($tableData)) {
            $this->warn("No filter hooks found for plugin: {$pluginFilter}");
            return;
        }

        $this->table(
            ['Hook Name', 'Plugin', 'Priority', 'Total Listeners'],
            $tableData
        );

        $this->line('');
    }
}
