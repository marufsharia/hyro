<?php

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Illuminate\Console\Command;

/**
 * List all available plugins
 */
class PluginListCommand extends Command
{
    protected $signature = 'hyro:plugin:list {--enabled : Show only enabled plugins} {--disabled : Show only disabled plugins}';
    protected $description = 'List all available Hyro plugins';

    public function handle()
    {
        $pluginManager = app('hyro.plugins');
        $plugins = $pluginManager->getAllPlugins();

        if ($plugins->isEmpty()) {
            $this->info('📦 No plugins found.');
            $this->newLine();
            $this->comment('💡 Create a plugin with: php artisan hyro:plugin:make YourPluginName');
            return 0;
        }

        $tableData = [];

        foreach ($plugins as $id => $data) {
            $isLoaded = $pluginManager->isLoaded($id);
            $status = $isLoaded ? '<fg=green>✓ Loaded</>' : '<fg=red>✗ Not Loaded</>';

            // Filter based on options
            if ($this->option('enabled') && !$isLoaded) {
                continue;
            }
            if ($this->option('disabled') && $isLoaded) {
                continue;
            }

            $tableData[] = [
                $id,
                $data['meta']['name'],
                $data['meta']['version'],
                $data['meta']['author'],
                $status,
                !empty($data['meta']['dependencies']) ? implode(', ', $data['meta']['dependencies']) : '-',
            ];
        }

        if (empty($tableData)) {
            $this->warn('No plugins match the filters.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Version', 'Author', 'Status', 'Dependencies'],
            $tableData
        );

        $this->newLine();
        $this->info('📊 Total plugins: ' . count($tableData));

        return 0;
    }
}










