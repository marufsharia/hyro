<?php

namespace Marufsharia\Hyro\Core\Console\Commands\Role;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Marufsharia\Hyro\Core\Models\Role;

class CreateRoleCommand extends Command
{
    protected $signature = 'hyro:role:create 
                            {--name= : Role name}
                            {--slug= : Role slug}
                            {--description= : Role description}';

    protected $description = 'Create a new role';

    public function handle()
    {
        $this->info('Creating new role...');
        $this->newLine();

        $name = $this->option('name') ?: $this->ask('Role name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $description = $this->option('description') ?: $this->ask('Description (optional)', '');

        try {
            $role = Role::create([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
            ]);

            $this->info('✓ Role created successfully!');
            $this->newLine();

            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $role->id],
                    ['Name', $role->name],
                    ['Slug', $role->slug],
                    ['Description', $role->description ?: 'N/A'],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create role: ' . $e->getMessage());
            return 1;
        }
    }
}
