<?php

namespace Marufsharia\Hyro\Crud\Console\Commands\Crud;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Crud\Services\CrudRouteAutoDiscoverer;

/**
 * Discover and register CRUD routes automatically
 */
class DiscoverCrudRoutesCommand extends Command
{
    protected $signature = 'hyro:discover-routes
                            {--stats : Show detailed statistics}
                            {--dry-run : Show what would be generated without writing files}';

    protected $description = 'Automatically discover and register CRUD component routes';

    protected CrudRouteAutoDiscoverer $discoverer;

    public function __construct()
    {
        parent::__construct();
        $this->discoverer = new CrudRouteAutoDiscoverer();
    }

    public function handle(): int
    {
        $this->displayBanner();

        $this->info('🔍 Scanning for CRUD components...');
        $this->newLine();

        $result = $this->discoverer->discoverAndRegister();

        $this->displayResults($result);

        if ($this->option('stats')) {
            $this->displayStatistics();
        }

        if ($result['discovered'] === 0) {
            $this->warn('⚠️  No CRUD components discovered!');
            $this->newLine();
            $this->line('Make sure your components:');
            $this->line('  • Are in app/Livewire/Admin or app/Http/Livewire/Admin');
            $this->line('  • End with "Manager.php" (e.g., PostManager.php)');
            $this->line('  • Extend Marufsharia\Hyro\Livewire\BaseCrudComponent');
            return 1;
        }

        if (!empty($result['errors'])) {
            $this->error('❌ Errors occurred during discovery:');
            foreach ($result['errors'] as $error) {
                $this->line("   • {$error}");
            }
            return 1;
        }

        $this->newLine();
        $this->components->info('Route discovery completed successfully!');
        $this->newLine();

        $this->line('Next steps:');
        $this->line('  1. Run: php artisan route:cache');
        $this->line('  2. Visit: /admin/dashboard');
        $this->newLine();

        return 0;
    }

    protected function displayBanner(): void
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║          HYRO CRUD ROUTE AUTO-DISCOVERY v2.0                 ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    protected function displayResults(array $result): void
    {
        $this->components->task(
            "Discovered {$result['discovered']} CRUD components",
            fn() => true
        );

        $this->components->task(
            "Registered {$result['registered']} routes",
            fn() => true
        );

        if (!empty($result['warnings'])) {
            $this->newLine();
            $this->warn('⚠️  Warnings:');
            foreach ($result['warnings'] as $warning) {
                $this->line("   • {$warning}");
            }
        }
    }

    protected function displayStatistics(): void
    {
        $stats = $this->discoverer->getStatistics();

        $this->newLine();
        $this->line('📊 Detailed Statistics:');
        $this->newLine();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Components Discovered', $stats['total_discovered']],
                ['Errors', $stats['total_errors']],
                ['Warnings', $stats['total_warnings']],
            ]
        );

        if (!empty($stats['components'])) {
            $this->newLine();
            $this->line('📦 Discovered Components:');
            foreach ($stats['components'] as $component) {
                $this->line("   ✓ {$component}");
            }
        }
    }
}
