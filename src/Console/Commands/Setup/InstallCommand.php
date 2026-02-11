<?php

namespace Marufsharia\Hyro\Console\Commands\Setup;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class InstallCommand extends BaseCommand
{
    use Confirmable;

    protected $signature = 'hyro:install
                            {--mode= : Installation mode (minimal|crud|full|silent)}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Install and setup Hyro package with professional installation modes';

    protected function executeCommand(): void
    {
  
        // Show beautiful welcome screen
        $this->showBeautifulWelcome();

        // Determine installation mode
        $mode = $this->option('mode') ?? $this->selectInstallationMode();

        // Show installation details
        $this->showInstallationDetails($mode);

        // Confirm installation
        if (!$this->confirmInstallation($mode)) {
            outro('Installation cancelled.');
            return;
        }

        // Execute installation based on mode
        match ($mode) {
            'silent' => $this->silentInstall(),
            'minimal' => $this->minimalInstall(),
            'crud' => $this->crudInstall(),
            'full' => $this->fullInstall(),
            default => $this->minimalInstall(),
        };

        // Show completion message
        $this->showCompletion($mode);
    }

    /**
     * Show beautiful welcome screen
     */
    private function showBeautifulWelcome(): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            return;
        }

        $this->newLine();
        
        // Beautiful ASCII art banner
        $banner = <<<'BANNER'
        ╔═══════════════════════════════════════════════════════════════╗
        ║                                                               ║
        ║   ██╗  ██╗██╗   ██╗██████╗  ██████╗                          ║
        ║   ██║  ██║╚██╗ ██╔╝██╔══██╗██╔═══██╗                         ║
        ║   ███████║ ╚████╔╝ ██████╔╝██║   ██║                         ║
        ║   ██╔══██║  ╚██╔╝  ██╔══██╗██║   ██║                         ║
        ║   ██║  ██║   ██║   ██║  ██║╚██████╔╝                         ║
        ║   ╚═╝  ╚═╝   ╚═╝   ╚═╝  ╚═╝ ╚═════╝                          ║
        ║                                                               ║
        ║        Enterprise Authorization System for Laravel           ║
        ║                                                               ║
        ╚═══════════════════════════════════════════════════════════════╝
        BANNER;

        foreach (explode("\n", $banner) as $line) {
            $this->line("<fg=cyan>{$line}</>");
        }

        $this->newLine();

        intro('🚀 Welcome to Hyro Installation');

        note(
            "Hyro is a comprehensive authorization system that provides:\n\n" .
            "  🔐 Advanced role-based access control (RBAC)\n" .
            "  ⚡ Powerful CRUD generator with 10+ templates\n" .
            "  🔔 Built-in notification system\n" .
            "  📊 Enterprise audit logging\n" .
            "  🔌 Extensible plugin system\n" .
            "  🚀 RESTful API with Sanctum\n" .
            "  🎨 Beautiful admin interface"
        );

        $this->newLine();
    }

    /**
     * Select installation mode interactively
     */
    private function selectInstallationMode(): string
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            return 'minimal';
        }

        // Show mode comparison table
        table(
            headers: ['Mode', 'Size', 'Files', 'Best For'],
            rows: [
                ['Silent', '~2MB', 'Config, Migrations, Assets', 'Production, CI/CD'],
                ['Minimal', '~2MB', 'Config, Migrations, Assets', 'Most Projects (Recommended)'],
                ['CRUD', '~5MB', 'Minimal + CRUD Stubs/Templates', 'Admin Panels, Dashboards'],
                ['Full', '~10MB', 'Everything (Views, Translations)', 'Development, Customization'],
            ]
        );

        $this->newLine();

        return select(
            label: 'Select installation mode',
            options: [
                'minimal' => '📦 Minimal - Essential files only (Recommended)',
                'crud' => '🎨 CRUD - Minimal + CRUD templates',
                'full' => '🎁 Full - All publishable assets',
                'silent' => '🚀 Silent - Zero interaction, auto-configure',
            ],
            default: 'minimal',
            hint: 'Use arrow keys to navigate, Enter to select'
        );
    }

    /**
     * Show installation details
     */
    private function showInstallationDetails(string $mode): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            return;
        }

        $this->newLine();

        $details = $this->getInstallationDetails($mode);

        note(
            "Installation Details:\n\n" .
            "  Mode: {$details['mode']}\n" .
            "  Size: {$details['size']}\n" .
            "  Files: {$details['files']}\n\n" .
            "What will be installed:\n" .
            implode("\n", array_map(fn($item) => "  {$item}", $details['includes']))
        );

        $this->newLine();
    }

    /**
     * Get installation details for a mode
     */
    private function getInstallationDetails(string $mode): array
    {
        return match ($mode) {
            'silent' => [
                'mode' => '🚀 Silent',
                'size' => '~2MB',
                'files' => 'Minimal',
                'includes' => [
                    '✓ Configuration file',
                    '✓ Database migrations',
                    '✓ Compiled CSS/JS assets',
                    '✓ Default roles & privileges',
                ],
            ],
            'minimal' => [
                'mode' => '📦 Minimal',
                'size' => '~2MB',
                'files' => 'Essential only',
                'includes' => [
                    '✓ Configuration file',
                    '✓ Database migrations',
                    '✓ Compiled CSS/JS assets',
                    '✓ Default roles & privileges',
                ],
            ],
            'crud' => [
                'mode' => '🎨 CRUD',
                'size' => '~5MB',
                'files' => 'Minimal + CRUD',
                'includes' => [
                    '✓ Configuration file',
                    '✓ Database migrations',
                    '✓ Compiled CSS/JS assets',
                    '✓ Default roles & privileges',
                    '✓ CRUD generator stubs',
                    '✓ 10+ frontend templates',
                ],
            ],
            'full' => [
                'mode' => '🎁 Full',
                'size' => '~10MB',
                'files' => 'Everything',
                'includes' => [
                    '✓ Configuration file',
                    '✓ Database migrations',
                    '✓ Compiled CSS/JS assets',
                    '✓ Default roles & privileges',
                    '✓ CRUD generator stubs',
                    '✓ 10+ frontend templates',
                    '✓ All Blade views',
                    '✓ Translation files',
                ],
            ],
        };
    }

    /**
     * Confirm installation
     */
    private function confirmInstallation(string $mode): bool
    {
        if ($this->option('force') || $this->option('no-interaction') || !$this->input->isInteractive()) {
            return true;
        }

        return confirm(
            label: 'Ready to install Hyro?',
            default: true,
            yes: 'Yes, install now',
            no: 'No, cancel',
            hint: 'This will publish files and run migrations'
        );
    }

    /**
     * Silent installation - Zero configuration, minimal setup
     */
    private function silentInstall(): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            $this->info('Installing Hyro in silent mode...');
        }

        spin(
            callback: function () {
                $this->publishConfig();
                $this->publishMigrations();
                $this->runMigrations(silent: true);
                $this->seedInitialData(silent: true);
                $this->publishAssets();
            },
            message: 'Installing Hyro (silent mode)...'
        );

        if ($this->input->isInteractive()) {
            info('Installation completed successfully!');
        }
    }

    /**
     * Minimal installation - Essential files only
     */
    private function minimalInstall(): void
    {
        $steps = [
            ['Publishing configuration', fn() => $this->publishConfig()],
            ['Publishing migrations', fn() => $this->publishMigrations()],
            ['Publishing compiled assets', fn() => $this->publishAssets()],
            ['Running migrations', fn() => $this->runMigrations()],
            ['Seeding initial data', fn() => $this->seedInitialData()],
        ];

        $this->executeStepsWithProgress($steps);
    }

    /**
     * CRUD installation - Minimal + CRUD templates
     */
    private function crudInstall(): void
    {
        $steps = [
            ['Publishing configuration', fn() => $this->publishConfig()],
            ['Publishing migrations', fn() => $this->publishMigrations()],
            ['Publishing compiled assets', fn() => $this->publishAssets()],
            ['Publishing CRUD stubs', fn() => $this->publishCrudStubs()],
            ['Publishing CRUD templates', fn() => $this->publishCrudTemplates()],
            ['Running migrations', fn() => $this->runMigrations()],
            ['Seeding initial data', fn() => $this->seedInitialData()],
        ];

        $this->executeStepsWithProgress($steps);
    }

    /**
     * Full installation - All publishable assets
     */
    private function fullInstall(): void
    {
        $steps = [
            ['Publishing configuration', fn() => $this->publishConfig()],
            ['Publishing migrations', fn() => $this->publishMigrations()],
            ['Publishing compiled assets', fn() => $this->publishAssets()],
            ['Publishing views', fn() => $this->publishViews()],
            ['Publishing translations', fn() => $this->publishTranslations()],
            ['Publishing CRUD stubs', fn() => $this->publishCrudStubs()],
            ['Publishing CRUD templates', fn() => $this->publishCrudTemplates()],
            ['Running migrations', fn() => $this->runMigrations()],
            ['Seeding initial data', fn() => $this->seedInitialData()],
        ];

        $this->executeStepsWithProgress($steps);
    }

    /**
     * Execute installation steps with progress indicators
     */
    private function executeStepsWithProgress(array $steps): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            foreach ($steps as [$message, $callback]) {
                $callback();
            }
            return;
        }

        $this->newLine();
        
        foreach ($steps as $index => [$message, $callback]) {
            $stepNumber = $index + 1;
            $totalSteps = count($steps);
            
            spin(
                callback: $callback,
                message: "[{$stepNumber}/{$totalSteps}] {$message}..."
            );
        }

        $this->newLine();
    }

    /**
     * Publish configuration file
     */
    private function publishConfig(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-config',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish migrations
     */
    private function publishMigrations(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-migrations',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish compiled assets (CSS/JS)
     */
    private function publishAssets(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-assets',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish views
     */
    private function publishViews(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-views',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish translations
     */
    private function publishTranslations(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-translations',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish CRUD stubs
     */
    private function publishCrudStubs(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-stubs',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Publish CRUD templates
     */
    private function publishCrudTemplates(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'hyro-templates',
            '--force' => $this->option('force'),
        ]);
    }

    /**
     * Run database migrations
     */
    private function runMigrations(bool $silent = false): void
    {
        // Check if migrations table exists
        try {
            DB::table('migrations')->count();
        } catch (\Exception $e) {
            if (!$silent) {
                warning('Migrations table not found. Creating it...');
            }
            Artisan::call('migrate:install');
        }

        // Run migrations
        Artisan::call('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
    }

    /**
     * Seed initial data (roles and privileges)
     */
    private function seedInitialData(bool $silent = false): void
    {
        // Create default roles
        $roles = $this->getDefaultRoles();
        
        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        // Create default privileges
        $privileges = $this->getDefaultPrivileges();
        
        foreach ($privileges as $privData) {
            Privilege::firstOrCreate(
                ['slug' => $privData['slug']],
                array_merge([
                    'name' => str($privData['slug'])->replace('.', ' ')->title(),
                    'description' => $privData['description'] ?? null,
                    'priority' => 50,
                    'is_protected' => $privData['is_protected'] ?? false,
                    'is_wildcard' => $privData['is_wildcard'] ?? false,
                    'wildcard_pattern' => ($privData['is_wildcard'] ?? false) ? $privData['slug'] : null,
                ], $privData)
            );
        }

        // Grant all privileges to super-admin role
        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $allPrivileges = Privilege::all();
            foreach ($allPrivileges as $privilege) {
                $superAdmin->grantPrivilege($privilege->slug, 'Initial setup');
            }
        }
    }

    /**
     * Get default roles
     */
    private function getDefaultRoles(): array
    {
        return [
            [
                'slug' => 'super-admin',
                'name' => 'Super Administrator',
                'description' => 'Full system access with all privileges',
                'is_protected' => true,
                'is_system' => true,
            ],
            [
                'slug' => 'admin',
                'name' => 'Administrator',
                'description' => 'System administrator with most privileges',
                'is_protected' => true,
                'is_system' => true,
            ],
            [
                'slug' => 'user',
                'name' => 'User',
                'description' => 'Regular user with basic privileges',
                'is_protected' => false,
                'is_system' => true,
            ],
        ];
    }

    /**
     * Get default privileges
     */
    private function getDefaultPrivileges(): array
    {
        return [
            // User management
            ['slug' => 'users.view', 'category' => 'users', 'description' => 'View users'],
            ['slug' => 'users.create', 'category' => 'users', 'description' => 'Create new users'],
            ['slug' => 'users.update', 'category' => 'users', 'description' => 'Update user information'],
            ['slug' => 'users.delete', 'category' => 'users', 'description' => 'Delete users'],
            ['slug' => 'users.suspend', 'category' => 'users', 'description' => 'Suspend/unsuspend users'],

            // Role management
            ['slug' => 'roles.view', 'category' => 'roles', 'description' => 'View roles'],
            ['slug' => 'roles.create', 'category' => 'roles', 'description' => 'Create new roles'],
            ['slug' => 'roles.update', 'category' => 'roles', 'description' => 'Update role information'],
            ['slug' => 'roles.delete', 'category' => 'roles', 'description' => 'Delete roles'],
            ['slug' => 'roles.assign', 'category' => 'roles', 'description' => 'Assign roles to users'],

            // Privilege management
            ['slug' => 'privileges.view', 'category' => 'privileges', 'description' => 'View privileges'],
            ['slug' => 'privileges.create', 'category' => 'privileges', 'description' => 'Create new privileges'],
            ['slug' => 'privileges.update', 'category' => 'privileges', 'description' => 'Update privilege information'],
            ['slug' => 'privileges.delete', 'category' => 'privileges', 'description' => 'Delete privileges'],
            ['slug' => 'privileges.grant', 'category' => 'privileges', 'description' => 'Grant privileges to roles'],

            // System
            ['slug' => 'system.settings', 'category' => 'system', 'description' => 'Manage system settings'],
            ['slug' => 'system.maintenance', 'category' => 'system', 'description' => 'Access maintenance mode'],
            ['slug' => 'system.backup', 'category' => 'system', 'description' => 'Manage system backups'],
            ['slug' => 'system.logs', 'category' => 'system', 'description' => 'View system logs'],

            // API
            ['slug' => 'api.access', 'category' => 'api', 'description' => 'Access API endpoints'],
            ['slug' => 'api.tokens', 'category' => 'api', 'description' => 'Manage API tokens'],

            // Wildcard privileges
            ['slug' => 'users.*', 'category' => 'wildcards', 'description' => 'All user privileges', 'is_wildcard' => true],
            ['slug' => 'roles.*', 'category' => 'wildcards', 'description' => 'All role privileges', 'is_wildcard' => true],
            ['slug' => 'privileges.*', 'category' => 'wildcards', 'description' => 'All privilege privileges', 'is_wildcard' => true],
            ['slug' => 'system.*', 'category' => 'wildcards', 'description' => 'All system privileges', 'is_wildcard' => true],
            ['slug' => 'api.*', 'category' => 'wildcards', 'description' => 'All API privileges', 'is_wildcard' => true],
            ['slug' => '*', 'category' => 'wildcards', 'description' => 'All privileges (super admin)', 'is_wildcard' => true, 'is_protected' => true],
        ];
    }

    /**
     * Show completion message
     */
    private function showCompletion(string $mode): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            $this->info('✓ Hyro installed successfully');
            return;
        }

        $this->newLine();

        outro('✨ Installation Complete!');

        $this->newLine();

        // Show installation summary
        $details = $this->getInstallationDetails($mode);
        
        note(
            "Installation Summary:\n\n" .
            "  Mode: {$details['mode']}\n" .
            "  Size: {$details['size']}\n" .
            "  Environment: " . app()->environment() . "\n" .
            "  Database: " . Config::get('database.default') . "\n\n" .
            "Files Published:\n" .
            "  • Configuration: config/hyro.php\n" .
            "  • Migrations: database/migrations/\n" .
            "  • Assets: public/vendor/hyro/\n" .
            ($mode === 'crud' || $mode === 'full' ? "  • CRUD Stubs: resources/stubs/hyro/\n" : "") .
            ($mode === 'full' ? "  • Views: resources/views/vendor/hyro/\n" : "")
        );

        $this->newLine();

        // Show next steps with beautiful formatting
        $this->line('<fg=cyan>╔═══════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║</> <fg=yellow;options=bold>Next Steps</>                                                    <fg=cyan>║</>');
        $this->line('<fg=cyan>╠═══════════════════════════════════════════════════════════════╣</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>1. Add trait to your User model:</>                          <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=gray>use Marufsharia\Hyro\Traits\HasHyroFeatures;</>           <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>2. Review configuration:</>                                  <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=green>php artisan config:clear</>                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=gray>nano config/hyro.php</>                                   <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>3. Create your first admin user:</>                          <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=green>php artisan hyro:user:create</>                           <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>4. Run health check:</>                                      <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=green>php artisan hyro:health-check</>                          <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        
        if ($mode === 'crud' || $mode === 'full') {
            $this->line('<fg=cyan>║</> <fg=white>5. Generate your first CRUD:</>                              <fg=cyan>║</>');
            $this->line('<fg=cyan>║</>    <fg=green>php artisan hyro:crud Product</>                          <fg=cyan>║</>');
            $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        }
        
        $this->line('<fg=cyan>╠═══════════════════════════════════════════════════════════════╣</>');
        $this->line('<fg=cyan>║</> <fg=yellow;options=bold>Resources</>                                                    <fg=cyan>║</>');
        $this->line('<fg=cyan>╠═══════════════════════════════════════════════════════════════╣</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>📚 Documentation:</>                                          <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=blue;options=underscore>https://github.com/marufsharia/hyro</>                  <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>💬 Support & Issues:</>                                      <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>    <fg=blue;options=underscore>https://github.com/marufsharia/hyro/issues</>           <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>║</> <fg=white>🌟 Star us on GitHub if you find Hyro useful!<//>            <fg=cyan>║</>');
        $this->line('<fg=cyan>║</>                                                               <fg=cyan>║</>');
        $this->line('<fg=cyan>╚═══════════════════════════════════════════════════════════════╝</>');

        $this->newLine();
        
        // Final message
        $this->line('  <fg=green;options=bold>🎉 Happy coding with Hyro!</>');
        $this->newLine();
    }
}
