<?php

namespace Marufsharia\Hyro\Core\Console\Commands\Role;

use Illuminate\Console\Command;
use Marufsharia\Hyro\Core\Models\Role;

class ListRolesCommand extends Command
{
    protected $signature = 'hyro:role:list';

    protected $description = 'List all roles';

    public function handle()
    {
        $roles = Role::withCount('users', 'privileges')->get();

        if ($roles->isEmpty()) {
            $this->info('No roles found.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Slug', 'Users', 'Privileges', 'Protected'],
            $roles->map(function ($role) {
                return [
                    $role->id,
                    $role->name,
                    $role->slug,
                    $role->users_count,
                    $role->privileges_count,
                    $role->is_protected ? 'Yes' : 'No',
                ];
            })
        );

        $this->newLine();
        $this->info('Total roles: ' . $roles->count());

        return 0;
    }
}
