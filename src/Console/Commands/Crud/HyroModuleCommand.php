<?php
namespace Marufsharia\Hyro\Console\Commands\Crud;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Services\ModuleManager;

class HyroModuleCommand extends Command
{
    protected $signature = "hyro:module
        {action : list|enable|disable|delete}
        {module? : Module key (posts)}
        {--force : Required for delete}";

    protected $description = "Manage Hyro modules";

    public function handle(ModuleManager $manager)
    {
        $action = $this->argument("action");
        $key    = $this->argument("module");

        if ($action === "list") {
            $this->table(
                ["Key", "Enabled", "Title"],
                collect($manager->all())->map(fn($m, $k) => [
                    $k,
                    $m['enabled'] ? "Yes" : "No",
                    $m['title']
                ])
            );
            return;
        }

        if (!$key) {
            $this->error("Module key required.");
            return;
        }

        if ($action === "disable") {
            $manager->disable($key);
            $this->info("Module [$key] disabled.");
        }

        if ($action === "enable") {
            $manager->enable($key);
            $this->info("Module [$key] enabled.");
        }

        if ($action === "delete") {

            if (!$this->option("force")) {
                $this->error("Delete is destructive. Use --force");
                return;
            }

            $manager->delete($key);

            $this->warn("Module [$key] deleted completely.");
        }
    }
}
