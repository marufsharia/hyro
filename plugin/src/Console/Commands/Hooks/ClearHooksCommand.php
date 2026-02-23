<?php

namespace Marufsharia\Hyro\Plugin\Console\Commands\Hooks;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Plugin\Support\Hooks\HookManager;

class ClearHooksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:hooks:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all registered Hyro hooks';

    /**
     * Execute the console command.
     */
    public function handle(HookManager $hookManager): int
    {
        $actionCount = $hookManager->count('actions');
        $filterCount = $hookManager->count('filters');
        $totalCount = $actionCount + $filterCount;

        if ($totalCount === 0) {
            $this->info('No hooks to clear.');
            return self::SUCCESS;
        }

        $this->warn("This will clear {$totalCount} hooks ({$actionCount} actions, {$filterCount} filters).");

        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        $hookManager->clear();

        $this->info('All hooks cleared successfully.');

        return self::SUCCESS;
    }
}
