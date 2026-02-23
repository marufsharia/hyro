<?php

namespace Marufsharia\Hyro\Core\Console\Commands\User;

use Illuminate\Console\Command;

class ListUsersCommand extends Command
{
    protected $signature = 'hyro:user:list';

    protected $description = 'List all users';

    public function handle()
    {
        $userModel = config('hyro.database.models.users', \App\Models\User::class);
        
        $users = $userModel::with('roles')->get();

        if ($users->isEmpty()) {
            $this->info('No users found.');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Roles', 'Created'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->roles->pluck('name')->join(', ') ?: 'None',
                    $user->created_at->format('Y-m-d H:i'),
                ];
            })
        );

        $this->newLine();
        $this->info('Total users: ' . $users->count());

        return 0;
    }
}
