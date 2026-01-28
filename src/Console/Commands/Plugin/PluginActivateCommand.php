<?php
// ============================================
// Plugin Activate Command
// ============================================

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Illuminate\Console\Command;

class PluginActivateCommand extends Command
{
    protected $signature = 'hyro:plugin:activate {plugin : Plugin ID to activate}';
    protected $description = 'Activate a Hyro plugin';

    public function handle()
    {
        $pluginId = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        if (!$pluginManager->getAllPlugins()->has($pluginId)) {
            $this->error("❌ Plugin '{$pluginId}' not found.");
            return 1;
        }

        try {
            $this->components->task('Activating plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->activate($pluginId);
            });

            $this->newLine();
            $this->info("✅ Plugin '{$pluginId}' activated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to activate plugin: {$e->getMessage()}");
            return 1;
        }
    }
}
