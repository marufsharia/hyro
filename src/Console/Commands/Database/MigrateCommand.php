<?php

namespace Marufsharia\Hyro\Console\Commands\Database;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'hyro:migrate
                            {--fresh : Drop all tables and re-run migrations}
                            {--seed : Seed the database after migration}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Run Hyro database migrations';

    public function handle(): int
    {
        $this->info('🗄️  Running Hyro migrations...');

        $options = [
            '--path' => 'database/migrations',
            '--realpath' => true,
        ];

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        if ($this->option('fresh')) {
            $this->call('migrate:fresh', $options);
        } else {
            $this->call('migrate', $options);
        }

        if ($this->option('seed') || $this->confirm('Seed database with default data?', true)) {
            $this->call('hyro:db:seed', ['--force' => true]);
        }

        $this->info('✅ Hyro migrations completed successfully!');

        return Command::SUCCESS;
    }
}
