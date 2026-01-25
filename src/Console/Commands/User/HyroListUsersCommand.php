<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class HyroListUsersCommand extends Command
{
    protected $signature = 'hyro:list-users';
    protected $description = 'List all users with their roles and status';

    public function handle()
    {
        $this->info('Listing all users...');
        $this->newLine();

        // Check if hyro tables exist
        if (!Schema::hasTable('hyro_roles')) {
            $this->error('Hyro tables are not installed. Please run migrations first.');
            $this->info('Run: php artisan migrate');
            return 1;
        }

        // Get user model from config
        $userModel = Config::get('hyro.models.user', Config::get('auth.providers.users.model', 'App\Models\User'));
        $userTable = (new $userModel)->getTable();

        // Get all users
        $users = DB::table($userTable)->get();

        if ($users->isEmpty()) {
            $this->warn('No users found in the database.');
            return 0;
        }

        // Get roles for all users
        $userIds = $users->pluck('id');
        $userRoles = DB::table('hyro_role_user')
            ->join('hyro_roles', 'hyro_role_user.role_id', '=', 'hyro_roles.id')
            ->whereIn('hyro_role_user.user_id', $userIds)
            ->select('hyro_role_user.user_id', 'hyro_roles.name')
            ->get()
            ->groupBy('user_id');

        // Get suspensions for all users
        $userSuspensions = DB::table('hyro_user_suspensions')
            ->whereIn('user_id', $userIds)
            ->whereNull('unsuspended_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        $headers = ['ID', 'Name', 'Email', 'Roles', 'Suspended', 'Last Login', 'Created'];
        $rows = [];

        foreach ($users as $user) {
            // Get roles for this user
            $roles = $userRoles->get($user->id, collect())
                ->pluck('name')
                ->join(', ') ?: 'No roles';

            // Check if suspended
            $suspended = 'No';
            $suspensionInfo = '';

            // Check suspended_at column if it exists
            if (property_exists($user, 'suspended_at') && $user->suspended_at) {
                $suspended = 'Yes';
                $suspensionInfo = "Since: " . date('Y-m-d', strtotime($user->suspended_at));
            }

            // Check for active suspension record
            $activeSuspension = $userSuspensions->get($user->id, collect())->first();
            if ($activeSuspension) {
                $suspended = 'Yes';
                $suspensionInfo = "Since: " . date('Y-m-d', strtotime($activeSuspension->created_at));
                if ($activeSuspension->reason) {
                    $suspensionInfo .= " (" . substr($activeSuspension->reason, 0, 30) . "...)";
                }
            }

            $rows[] = [
                $user->id,
                $user->name ?? 'N/A',
                $user->email ?? 'N/A',
                $roles,
                $suspended . ($suspensionInfo ? "\n" . $suspensionInfo : ''),
                property_exists($user, 'last_login_at') && $user->last_login_at
                    ? date('Y-m-d H:i', strtotime($user->last_login_at))
                    : 'Never',
                date('Y-m-d', strtotime($user->created_at)),
            ];
        }

        $this->table($headers, $rows);

        // Show summary
        $this->newLine();
        $this->info('Summary:');
        $this->line("Total users: " . $users->count());

        // Count suspended users
        $suspendedCount = $users->filter(function($user) use ($userSuspensions) {
            $isSuspended = false;

            // Check suspended_at property
            if (property_exists($user, 'suspended_at') && $user->suspended_at) {
                $isSuspended = true;
            }

            // Check suspension records
            if ($userSuspensions->has($user->id)) {
                $isSuspended = true;
            }

            return $isSuspended;
        })->count();

        $this->line("Active users: " . ($users->count() - $suspendedCount));
        $this->line("Suspended users: " . $suspendedCount);

        return 0;
    }
}
