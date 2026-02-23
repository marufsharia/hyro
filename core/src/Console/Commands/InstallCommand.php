<?php

namespace Marufsharia\Hyro\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:install 
                            {--force : Overwrite existing files}
                            {--no-assets : Skip asset publishing}
                            {--no-migrations : Skip migration publishing}
                            {--no-seed : Skip running HyroInstallSeeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Hyro package with all necessary assets and configurations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayHeader();
        
        $this->info('🚀 Starting Hyro Installation...');
        $this->newLine();

        $steps = [
            'config' => 'Publishing configuration...',
            'assets' => 'Publishing assets...',
            'migrations' => 'Publishing migrations...',
            'trait' => 'Adding HasHyroFeatures trait to User model...',
            'run_migrations' => 'Running migrations...',
            'seed' => 'Seeding default roles and privileges...',
            'views' => 'Publishing views...',
        ];

        $completed = [];

        try {
            // Step 1: Publish config
            $this->publishConfig();
            $completed[] = 'config';

            // Step 2: Publish assets
            if (!$this->option('no-assets')) {
                $this->publishAssets();
                $completed[] = 'assets';
            }

            // Step 3: Publish migrations
            if (!$this->option('no-migrations')) {
                $this->publishMigrations();
                $completed[] = 'migrations';
            }

            // Step 4: Add trait to User model
            $this->addTraitToUserModel();
            $completed[] = 'trait';

            // Step 5: Run migrations
            $this->runMigrations();
            $completed[] = 'run_migrations';

            // Step 6: Seed database
            if (!$this->option('no-seed')) {
                $this->seedDatabase();
                $completed[] = 'seed';
            }
            
            // Step 7: Publish views (optional)
            $this->publishViews();
            $completed[] = 'views';

            $this->displaySuccess();
            $this->displayNextSteps();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->displayError($e);
            return self::FAILURE;
        }
    }

    /**
     * Publish configuration file.
     */
    protected function publishConfig(): void
    {
        $this->displayProgress('config', 'Publishing configuration...');

        $params = [
            '--provider' => 'Marufsharia\Hyro\HyroServiceProvider',
            '--tag' => 'hyro-config',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publish assets.
     */
    protected function publishAssets(): void
    {
        $this->displayProgress('assets', 'Publishing assets...');

        $params = [
            '--provider' => 'Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider',
            '--tag' => 'hyro-assets',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publish migrations.
     */
    protected function publishMigrations(): void
    {
        $this->displayProgress('migrations', 'Publishing migrations...');

        $params = [
            '--provider' => 'Marufsharia\Hyro\Core\CoreServiceProvider',
            '--tag' => 'hyro-migrations',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Publish views (optional).
     */
    protected function publishViews(): void
    {
        if (!$this->confirm('Do you want to publish views for customization?', false)) {
            return;
        }

        $this->info('Publishing views...');

        $params = [
            '--provider' => 'Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider',
            '--tag' => 'hyro-views',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $this->line('  ✓ Views published to resources/views/vendor/hyro');
    }

    /**
     * Add HasHyroFeatures trait to User model.
     */
    protected function addTraitToUserModel(): void
    {
        $this->info('Adding HasHyroFeatures trait to User model...');

        $userModelPath = app_path('Models/User.php');

        if (!File::exists($userModelPath)) {
            $this->warn('  ⚠ User model not found at ' . $userModelPath);
            return;
        }

        $content = File::get($userModelPath);

        if (str_contains($content, 'HasHyroFeatures')) {
            $this->line('  ✓ Trait already added');
            return;
        }

        // Add use statement
        if (!str_contains($content, 'use Marufsharia\Hyro\Core\Traits\HasHyroFeatures;')) {
            $content = preg_replace(
                '/namespace App\\\\Models;/',
                "namespace App\\Models;\n\nuse Marufsharia\\Hyro\\Core\\Traits\\HasHyroFeatures;",
                $content
            );
        }

        // Add trait to class
        $content = preg_replace(
            '/class User extends Authenticatable\s*\{/',
            "class User extends Authenticatable\n{\n    use HasHyroFeatures;",
            $content
        );

        File::put($userModelPath, $content);

        $this->line('  ✓ Trait added to User model');
    }

    /**
     * Run migrations.
     */
    protected function runMigrations(): void
    {
        if (!$this->confirm('Do you want to run migrations now?', true)) {
            $this->warn('  ⚠ Skipped migrations. Run manually: php artisan migrate');
            return;
        }

        $this->info('Running migrations...');
        
        $this->call('migrate');
        
        $this->line('  ✓ Migrations completed');
    }

    /**
     * Seed database with default roles and privileges.
     */
    protected function seedDatabase(): void
    {
        if (!$this->confirm('Do you want to seed default roles and privileges?', true)) {
            $this->warn('  ⚠ Skipped seeding. Run manually: php artisan hyro:seed');
            return;
        }

        $this->info('Seeding default roles and privileges...');

        try {
            // Load the seeder file directly
            $seederPath = __DIR__ . '/../../../database/seeders/HyroInstallSeeder.php';
            
            if (!file_exists($seederPath)) {
                throw new \Exception('Seeder file not found at: ' . $seederPath);
            }
            
            require_once $seederPath;
            
            // Instantiate and run the seeder
            $seeder = new \Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder();
            $seeder->run();

            $this->line('  ✓ Seeded successfully');
            $this->newLine();
            $this->line('  Created roles:');
            $this->line('    • Super Administrator (level 100)');
            $this->line('    • Administrator (level 80)');
            $this->line('    • Editor (level 50)');
            $this->line('    • User (level 10)');
            $this->newLine();
            $this->line('  Created privileges:');
            $this->line('    • Hyro admin access');
            $this->line('    • Role management');
            $this->line('    • User management');
            $this->line('    • Content management');
        } catch (\Exception $e) {
            $this->error('  ✗ Seeding failed: ' . $e->getMessage());
            $this->warn('  You can run it manually later: php artisan hyro:seed');
        }
    }

    /**
     * Display next steps.
     */
    protected function displayNextSteps(): void
    {
        $this->newLine();
        $this->info('📋 Next Steps:');
        $this->newLine();
        
        $this->line('<fg=cyan>┌─────────────────────────────────────────────────────────────┐</>');
        $this->line('<fg=cyan>│</> <fg=white;options=bold>1. Create Admin User</>                                       <fg=cyan>│</>');
        $this->line('<fg=cyan>│</>   <fg=green>php artisan hyro:user:create --admin</>                          <fg=cyan>│</>');
        $this->line('<fg=cyan>│</>                                                             <fg=cyan>│</>');
        $this->line('<fg=cyan>│</> <fg=white;options=bold>2. Access Admin Panel</>                                       <fg=cyan>│</>');
        $this->line('<fg=cyan>│</>   <fg=green>Visit: http://localhost:8000/admin</>                           <fg=cyan>│</>');
        $this->line('<fg=cyan>│</>                                                             <fg=cyan>│</>');
        $this->line('<fg=cyan>│</> <fg=white;options=bold>3. Documentation</>                                            <fg=cyan>│</>');
        $this->line('<fg=cyan>│</>   <fg=blue>https://github.com/marufsharia/hyro</>                         <fg=cyan>│</>');
        $this->line('<fg=cyan>└─────────────────────────────────────────────────────────────┘</>');
        $this->newLine();
    }

    /**
     * Display installation header.
     */
    protected function displayHeader(): void
    {
    
        $this->newLine();
        $this->line('<fg=magenta;options=bold>╔══════════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=magenta;options=bold>║</> <fg=cyan;options=bold>  🛡️  H Y R O   I N S T A L L A T I O N   W I Z A R D    </> <fg=magenta;options=bold>║</>');
        $this->line('<fg=magenta;options=bold>║</> <fg=white>Enterprise-grade Authentication & Authorization System</>      <fg=magenta;options=bold>║</>');
        $this->line('<fg=magenta;options=bold>╚══════════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
    }

    /**
     * Display success message.
     */
    protected function displaySuccess(): void
    {
        $this->newLine();
        $this->line('<fg=green;options=bold>╔══════════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=green;options=bold>║</> <fg=white;options=bold>    ✅  I N S T A L L A T I O N   C O M P L E T E D    </> <fg=green;options=bold>║</>');
        $this->line('<fg=green;options=bold>║</> <fg=white>Hyro has been successfully installed and configured!</>        <fg=green;options=bold>║</>');
        $this->line('<fg=green;options=bold>╚══════════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
    }

    /**
     * Display error message.
     */
    protected function displayError(\Exception $e): void
    {
        $this->newLine();
        $this->line('<fg=red;options=bold>╔══════════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=red;options=bold>║</> <fg=white;options=bold>    ❌  I N S T A L L A T I O N   F A I L E D    </> <fg=red;options=bold>║</>');
        $this->line('<fg=red;options=bold>║</> <fg=white>An error occurred during installation</>                     <fg=red;options=bold>║</>');
        $this->line('<fg=red;options=bold>╚══════════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
        
        $this->error('Error: ' . $e->getMessage());
        $this->newLine();
        
        $this->warn('Troubleshooting:');
        $this->line('  1. Check database connection and permissions');
        $this->line('  2. Ensure all required PHP extensions are installed');
        $this->line('  3. Run individual commands manually:');
        $this->line('     • php artisan vendor:publish --tag=hyro-config');
        $this->line('     • php artisan vendor:publish --tag=hyro-assets');
        $this->line('     • php artisan migrate');
        $this->line('     • php artisan hyro:seed');
        $this->newLine();
    }

    /**
     * Display progress indicator.
     */
    protected function displayProgress(string $step, string $message, bool $success = true): void
    {
        $icon = $success ? '✅' : '❌';
        $color = $success ? 'green' : 'red';
        
        $this->line(sprintf(
            '<fg=%s>%s</> <fg=white>%s</> <fg=gray>%s</>',
            $color,
            $icon,
            $message,
            $this->getProgressBar($step)
        ));
    }

    /**
     * Get progress bar for current step.
     */
    protected function getProgressBar(string $currentStep): string
    {
        $steps = ['config', 'assets', 'migrations', 'trait', 'run_migrations', 'seed', 'views'];
        $currentIndex = array_search($currentStep, $steps);
        $totalSteps = count($steps);
        
        if ($currentIndex === false) {
            return '';
        }
        
        $progress = round(($currentIndex + 1) / $totalSteps * 100);
        $barLength = 20;
        $filled = round($progress / 100 * $barLength);
        $empty = $barLength - $filled;
        
        return sprintf(
            '[%s%s] %d%%',
            str_repeat('█', $filled),
            str_repeat('░', $empty),
            $progress
        );
    }
}
