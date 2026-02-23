<?php

namespace Marufsharia\Hyro\Plugin\Console\Commands\Hooks;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Plugin\Support\Hooks\HookManager;

class ShowHookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:hooks:show {name : Hook name to show details for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show details for a specific Hyro hook';

    /**
     * Execute the console command.
     */
    public function handle(HookManager $hookManager): int
    {
        $name = $this->argument('name');

        $hasAction = $hookManager->hasAction($name);
        $hasFilter = $hookManager->hasFilter($name);

        if (!$hasAction && !$hasFilter) {
            $this->error("Hook '{$name}' not found.");
            return self::FAILURE;
        }

        $this->info("Hook: {$name}");
        $this->line('');

        if ($hasAction) {
            $this->displayActionDetails($name, $hookManager);
        }

        if ($hasFilter) {
            $this->displayFilterDetails($name, $hookManager);
        }

        return self::SUCCESS;
    }

    /**
     * Display action hook details
     */
    protected function displayActionDetails(string $name, HookManager $hookManager): void
    {
        $callbacks = $hookManager->getCallbacks($name, 'action');

        $this->info('Type: Action Hook (fire-and-forget)');
        $this->line("Listeners: " . count($callbacks));
        $this->line('');

        if (empty($callbacks)) {
            return;
        }

        $tableData = [];
        foreach ($callbacks as $index => $callback) {
            $tableData[] = [
                $index + 1,
                $callback['plugin_id'],
                $callback['priority'],
            ];
        }

        $this->table(
            ['#', 'Plugin', 'Priority'],
            $tableData
        );

        $this->line('');
    }

    /**
     * Display filter hook details
     */
    protected function displayFilterDetails(string $name, HookManager $hookManager): void
    {
        $callbacks = $hookManager->getCallbacks($name, 'filter');

        $this->info('Type: Filter Hook (data modification)');
        $this->line("Listeners: " . count($callbacks));
        $this->line('');

        if (empty($callbacks)) {
            return;
        }

        $tableData = [];
        foreach ($callbacks as $index => $callback) {
            $tableData[] = [
                $index + 1,
                $callback['plugin_id'],
                $callback['priority'],
            ];
        }

        $this->table(
            ['#', 'Plugin', 'Priority'],
            $tableData
        );

        $this->line('');
        $this->comment('Execution order: Priority 1 → Priority 10 → Priority 20 (lower = higher priority)');
    }
}
