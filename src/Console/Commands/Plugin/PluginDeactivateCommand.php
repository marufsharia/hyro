<?php
// ============================================
// Plugin Deactivate Command
// ============================================

namespace Marufsharia\Hyro\Console\Commands\Plugin;

use Illuminate\Console\Command;

class PluginDeactivateCommand extends Command
{
    protected $signature = 'hyro:plugin:deactivate {plugin : Plugin ID to deactivate}';
    protected $description = 'Deactivate a Hyro plugin';

    public function handle()
    {
        $pluginId = $this->argument('plugin');
        $pluginManager = app('hyro.plugins');

        if (!$pluginManager->isLoaded($pluginId)) {
            $this->error("❌ Plugin '{$pluginId}' is not loaded.");
            return 1;
        }

        try {
            $this->task('Deactivating plugin', function () use ($pluginManager, $pluginId) {
                $pluginManager->deactivate($pluginId);
            });

            $this->newLine();
            $this->info("✅ Plugin '{$pluginId}' deactivated successfully!");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to deactivate plugin: {$e->getMessage()}");
            return 1;
        }
    }
}
