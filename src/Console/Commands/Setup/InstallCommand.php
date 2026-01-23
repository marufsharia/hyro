<?php

namespace Marufsharia\Hyro\Console\Commands\Setup;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;

class InstallCommand extends BaseCommand
{
    use Confirmable;

    protected $signature = 'hyro:install
                            {--publish : Publish configuration and assets}
                            {--migrate : Run database migrations}
                            {--seed : Seed initial roles and privileges}
                            {--force : Skip confirmation}';

    protected $description = 'Install and setup Hyro package';

    protected function executeCommand(): void
    {
        $this->showWelcomeMessage();

        $steps = [];

        if ($this->option('publish')) {
            $steps[] = 'Publish configuration and assets';
        }

        if ($this->option('migrate')) {
            $steps[] = 'Run database migrations';
        }

        if ($this->option('seed')) {
            $steps[] = 'Seed initial data';
        }

        // If no options specified, run all steps
        if (empty($steps)) {
            $steps = [
                'Publish configuration and assets',
                'Run database migrations',
                'Seed initial data',
            ];
            $runAll = true;
        } else {
            $runAll = false;
        }

        $this->info('Installation Steps:');
        foreach ($steps as $i => $step) {
            $this->info("  " . ($i + 1) . ". {$step}");
        }

        if (!$this->confirmOperation('Install Hyro package', [
            ['Steps', count($steps)],
            ['Environment', app()->environment()],
            ['Database', Config::get('database.default')],
        ])) {
            return;
        }

        // Execute installation steps
        try {
            if ($runAll || $this->option('publish')) {
                $this->publishAssets();
            }
            // Publish assets
            if ($this->confirm('Publish compiled assets?', true)) {
                $this->call('vendor:publish', ['--tag' => 'hyro-assets']);
            }

            // Publish views
            if ($this->confirm('Publish views?', false)) {
                $this->call('vendor:publish', ['--tag' => 'hyro-views']);
            }

            if ($runAll || $this->option('migrate')) {
                $this->runMigrations();
            }

            if ($runAll || $this->option('seed')) {
                $this->seedInitialData();
            }

            $this->showSuccessMessage();
        } catch (\Exception $e) {
            $this->error("Installation failed: {$e->getMessage()}");
            throw $e;
        }
    }

    private function showWelcomeMessage(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                    Hyro Installation                     ║');
        $this->info('╠══════════════════════════════════════════════════════════╣');
        $this->info('║ Welcome to Hyro - Enterprise Authorization System        ║');
        $this->info('║ This installer will set up the package in your app.      ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function publishAssets(): void
    {
        $this->info('📦 Publishing assets...');

        $tags = ['hyro-config', 'hyro-migrations', 'hyro-views', 'hyro-translations'];

        foreach ($tags as $tag) {
            $this->info("  Publishing {$tag}...");
            Artisan::call('vendor:publish', [
                '--tag' => $tag,
                '--force' => true,
            ]);
        }

        $this->info('✅ Assets published successfully');
    }

    private function runMigrations(): void
    {
        $this->info('🗄️  Running migrations...');

        // Check if migrations table exists
        try {
            DB::table('migrations')->count();
        } catch (\Exception $e) {
            $this->warn('Migrations table not found. Creating it...');
            Artisan::call('migrate:install');
        }

        // Run migrations
        Artisan::call('migrate', [
            '--path' => 'database/migrations',
            '--force' => true,
        ]);

        $this->info('✅ Migrations completed successfully');
    }

    private function seedInitialData(): void
    {
        $this->info('🌱 Seeding initial data...');

        // Create default roles
        $roles = [
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

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            $this->info("  Created role: {$role->slug}");
        }

        // Create default privileges
        $privileges = [
            // User management
            ['slug' => 'users.view', 'category' => 'users'],
            ['slug' => 'users.create', 'category' => 'users'],
            ['slug' => 'users.update', 'category' => 'users'],
            ['slug' => 'users.delete', 'category' => 'users'],
            ['slug' => 'users.suspend', 'category' => 'users'],

            // Role management
            ['slug' => 'roles.view', 'category' => 'roles'],
            ['slug' => 'roles.create', 'category' => 'roles'],
            ['slug' => 'roles.update', 'category' => 'roles'],
            ['slug' => 'roles.delete', 'category' => 'roles'],
            ['slug' => 'roles.assign', 'category' => 'roles'],

            // Privilege management
            ['slug' => 'privileges.view', 'category' => 'privileges'],
            ['slug' => 'privileges.create', 'category' => 'privileges'],
            ['slug' => 'privileges.update', 'category' => 'privileges'],
            ['slug' => 'privileges.delete', 'category' => 'privileges'],
            ['slug' => 'privileges.grant', 'category' => 'privileges'],

            // System
            ['slug' => 'system.settings', 'category' => 'system'],
            ['slug' => 'system.maintenance', 'category' => 'system'],
            ['slug' => 'system.backup', 'category' => 'system'],

            // Wildcard privileges
            ['slug' => 'users.*', 'category' => 'wildcards', 'is_wildcard' => true],
            ['slug' => 'roles.*', 'category' => 'wildcards', 'is_wildcard' => true],
            ['slug' => 'privileges.*', 'category' => 'wildcards', 'is_wildcard' => true],
            ['slug' => 'system.*', 'category' => 'wildcards', 'is_wildcard' => true],
        ];

        foreach ($privileges as $privData) {
            $privilege = Privilege::firstOrCreate(
                ['slug' => $privData['slug']],
                array_merge([
                    'name' => str($privData['slug'])->replace('.', ' ')->title(),
                    'description' => null,
                    'priority' => 50,
                    'is_protected' => false,
                    'is_wildcard' => $privData['is_wildcard'] ?? false,
                    'wildcard_pattern' => $privData['is_wildcard'] ?? false ? $privData['slug'] : null,
                ], $privData)
            );
            $this->info("  Created privilege: {$privilege->slug}");
        }

        // Grant all privileges to super-admin role
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $allPrivileges = Privilege::all();

        foreach ($allPrivileges as $privilege) {
            $superAdmin->grantPrivilege($privilege->slug, 'Initial setup');
        }

        $this->info("✅ Granted {$allPrivileges->count()} privileges to super-admin role");

        $this->info('✅ Initial data seeded successfully');
    }

    private function showSuccessMessage(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                 Installation Complete!                   ║');
        $this->info('╠══════════════════════════════════════════════════════════╣');
        $this->info('║ Hyro has been successfully installed.                    ║');
        $this->info('║                                                          ║');
        $this->info('║ Next steps:                                              ║');
        $this->info('║ 1. Review config/hyro.php                                ║');
        $this->info('║ 2. Add HasHyroAccess trait to your User model           ║');
        $this->info('║ 3. Run: php artisan hyro:health-check                   ║');
        $this->info('║ 4. Check: php artisan hyro:user:list-roles              ║');
        $this->info('║                                                          ║');
        $this->info('║ Documentation: https://github.com/marufsharia/hyro       ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }
}
