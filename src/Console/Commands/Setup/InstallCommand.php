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
use function Laravel\Prompts\warning;

class InstallCommand extends BaseCommand
{
    use Confirmable;

    protected $signature = 'hyro:install
                            {--mode= : Installation mode (minimal|crud|full|silent)}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Install and setup Hyro package with professional installation modes';

    protected function executeCommand(): void
    {
        // Determine installation mode
        $mode = $this->option('mode') ?? $this->selectInstallationMode();

        // Show welcome message
        $this->showWelcome($mode);

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
     * Select installation mode interactively
     */
    private function selectInstallationMode(): string
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            return 'minimal';
        }

        intro('🚀 Hyro Installation');

        note(
            "Choose your installation mode:\n\n" .
            "• Silent   - Zero configuration, minimal setup (production-ready)\n" .
            "• Minimal  - Essential files only (recommended for most projects)\n" .
            "• CRUD     - Minimal + CRUD generator templates and stubs\n" .
            "• Full     - Everything including views, translations, and examples"
        );

        return select(
            label: 'Select installation mode',
            options: [
                'minimal' => 'Minimal - Essential files only (Recommended)',
                'crud' => 'CRUD - Minimal + CRUD templates',
                'full' => 'Full - All publishable assets',
                'silent' => 'Silent - Zero interaction, auto-configure',
            ],
            default: 'minimal'
        );
    }

    /**
     * Show welcome message
     */
    private function showWelcome(string $mode): void
    {
        if ($this->option('no-interaction') || !$this->input->isInteractive()) {
            $this->info("Installing Hyro in {$mode} mode...");
            return;
        }

        $this->newLine();
        $this->components->twoColumnDetail(
            '<fg=cyan>Installation Mode</>',
            '<fg=green>' . strtoupper($mode) . '</>'
        );
        $this->components->twoColumnDetail(
            '<fg=cyan>Environment</>',
            '<fg=yellow>' . app()->environment() . '</>'
        );
        $this->components->twoColumnDetail(
            '<fg=cyan>Database</>',
            '<fg=yellow>' . Config::get('database.default') . '</>'
        );
        $this->newLine();
    }

    /**
     * Silent installation - Zero configuration, minimal setup
     */
    private function silentInstall(): void
    {
        spin(
            callback: function () {
                // Publish only essential config
                $this->publishConfig();
                
                // Publish migrations
                $this->publishMigrations();
                
                // Run migrations
                $this->runMigrations(silent: true);
                
                // Seed initial data
                $this->seedInitialData(silent: true);
                
                // Publish compiled assets
                $this->publishAssets();
            },
            message: 'Installing Hyro (silent mode)...'
        );

        info('Installation completed successfully!');
    }

    /**
     * Minimal installation - Essential files only
     */
    private function minimalInstall(): void
    {
        $steps = [
            'Publishing configuration' => fn() => $this->publishConfig(),
            'Publishing migrations' => fn() => $this->publishMigrations(),
            'Publishing compiled assets' => fn() => $this->publishAssets(),
            'Running migrations' => fn() => $this->runMigrations(),
            'Seeding initial data' => fn() => $this->seedInitialData(),
        ];

        $this->executeSteps($steps);
    }

    /**
     * CRUD installation - Minimal + CRUD templates
     */
    private function crudInstall(): void
    {
        $steps = [
            'Publishing configuration' => fn() => $this->publishConfig(),
            'Publishing migrations' => fn() => $this->publishMigrations(),
            'Publishing compiled assets' => fn() => $this->publishAssets(),
            'Publishing CRUD stubs' => fn() => $this->publishCrudStubs(),
            'Publishing CRUD templates' => fn() => $this->publishCrudTemplates(),
            'Running migrations' => fn() => $this->runMigrations(),
            'Seeding initial data' => fn() => $this->seedInitialData(),
        ];

        $this->executeSteps($steps);
    }

    /**
     * Full installation - All publishable assets
     */
    private function fullInstall(): void
    {
        $steps = [
            'Publishing configuration' => fn() => $this->publishConfig(),
            'Publishing migrations' => fn() => $this->publishMigrations(),
            'Publishing compiled assets' => fn() => $this->publishAssets(),
            'Publishing views' => fn() => $this->publishViews(),
            'Publishing translations' => fn() => $this->publishTranslations(),
            'Publishing CRUD stubs' => fn() => $this->publishCrudStubs(),
            'Publishing CRUD templates' => fn() => $this->publishCrudTemplates(),
            'Running migrations' => fn() => $this->runMigrations(),
            'Seeding initial data' => fn() => $this->seedInitialData(),
        ];

        $this->executeSteps($steps);
    }

    /**
     * Execute installation steps with progress
     */
    private function executeSteps(array $steps): void
    {
        foreach ($steps as $message => $callback) {
            spin(
                callback: $callback,
                message: $message . '...'
            );
        }
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

        outro('✓ Hyro installed successfully!');

        $this->newLine();
        
        note(
            "Installation Summary:\n\n" .
            "• Mode: " . strtoupper($mode) . "\n" .
            "• Configuration: config/hyro.php\n" .
            "• Migrations: database/migrations/\n" .
            "• Assets: public/vendor/hyro/\n" .
            ($mode === 'crud' || $mode === 'full' ? "• CRUD Stubs: resources/stubs/hyro/\n" : "") .
            ($mode === 'full' ? "• Views: resources/views/vendor/hyro/\n" : "")
        );

        $this->newLine();

        $this->components->info('Next Steps:');
        $this->components->task('Add HasHyroFeatures trait to your User model');
        $this->components->task('Review and customize config/hyro.php');
        $this->components->task('Create your first admin user: php artisan hyro:user:create');
        $this->components->task('Run health check: php artisan hyro:health-check');
        
        if ($mode === 'crud' || $mode === 'full') {
            $this->components->task('Generate CRUD: php artisan hyro:crud YourModel');
        }

        $this->newLine();
        
        $this->components->twoColumnDetail(
            '<fg=cyan>Documentation</>',
            '<fg=blue>https://github.com/marufsharia/hyro</>'
        );
        $this->components->twoColumnDetail(
            '<fg=cyan>Support</>',
            '<fg=blue>https://github.com/marufsharia/hyro/issues</>'
        );

        $this->newLine();
    }
}
