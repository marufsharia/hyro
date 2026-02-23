<?php

namespace Marufsharia\Hyro\Core\Console\Commands;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hyro:seed 
                            {--force : Force seeding even if roles already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default Hyro roles and privileges';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Seeding Hyro default roles and privileges...');
        $this->newLine();

        try {
            // Check if roles already exist
            $rolesExist = \Marufsharia\Hyro\Models\Role::whereIn('slug', [
                'super-admin', 'admin', 'editor', 'user'
            ])->exists();

            if ($rolesExist && !$this->option('force')) {
                if (!$this->confirm('Default roles already exist. Do you want to re-seed?', false)) {
                    $this->info('Seeding cancelled.');
                    return self::SUCCESS;
                }
            }

            // Load the seeder file directly
            $seederPath = __DIR__ . '/../../../database/seeders/HyroInstallSeeder.php';
            
            if (!file_exists($seederPath)) {
                throw new \Exception('Seeder file not found at: ' . $seederPath);
            }
            
            require_once $seederPath;

            // Run the seeder
            $seeder = new HyroInstallSeeder();
            $seeder->run();

            $this->newLine();
            $this->info('✓ Seeding completed successfully!');
            $this->newLine();

            // Display what was created
            $this->displayCreatedRoles();
            $this->displayCreatedPrivileges();

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('✗ Seeding failed: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    /**
     * Display created roles.
     */
    protected function displayCreatedRoles(): void
    {
        $this->line('Created/Updated Roles:');
        
        try {
            $roles = \Marufsharia\Hyro\Models\Role::whereIn('slug', [
                'super-admin', 'admin', 'editor', 'user'
            ])->get();

            foreach ($roles as $role) {
                $privilegeCount = $role->privileges()->count();
                $this->line(sprintf(
                    '  • %s (level %d) - %d privileges',
                    $role->name,
                    $role->level ?? 0,
                    $privilegeCount
                ));
            }
        } catch (\Exception $e) {
            $this->line('  • Roles created successfully');
        }

        $this->newLine();
    }

    /**
     * Display created privileges.
     */
    protected function displayCreatedPrivileges(): void
    {
        $this->line('Created/Updated Privileges:');
        
        $privilegeGroups = \Marufsharia\Hyro\Models\Privilege::all()
            ->groupBy('group');

        foreach ($privilegeGroups as $group => $privileges) {
            $this->line(sprintf('  • %s: %d privileges', ucfirst($group), $privileges->count()));
        }

        $this->newLine();
    }
}
