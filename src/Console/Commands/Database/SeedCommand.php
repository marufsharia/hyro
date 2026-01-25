<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Database\Seeders\HyroSeeder;

class SeedCommand extends Command
{
    protected $signature = 'hyro:db:seed
                            {--force : Force seeding without confirmation}
                            {--fresh : Run with fresh migrations first}';

    protected $description = 'Seed Hyro database with default data';

    public function handle(): int
    {
        $this->info('🌱 Seeding Hyro database...');

        if ($this->option('fresh')) {
            if (!$this->option('force') && !$this->confirm('This will refresh your database. Continue?', false)) {
                $this->error('Seeding cancelled.');
                return Command::FAILURE;
            }

            $this->call('migrate:fresh');
        }

        $this->call('db:seed', ['--class' => HyroSeeder::class]);

        $this->info('✅ Hyro database seeded successfully!');
        $this->line('');
        $this->info('Default data created:');
        $this->table(['Item', 'Count'], [
            ['Roles', DB::table(config('hyro.database.tables.roles', 'hyro_roles'))->count()],
            ['Privileges', DB::table(config('hyro.database.tables.privileges', 'hyro_privileges'))->count()],
        ]);

        return Command::SUCCESS;
    }
}
