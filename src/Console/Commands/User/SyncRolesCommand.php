<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class SyncRolesCommand extends BaseCommand
{
    protected $signature = 'hyro:user:sync-roles
                            {user : User email or ID}
                            {roles* : Role slugs or IDs to sync}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--detach : Remove existing roles not in the list}';

    protected $description = 'Sync user roles with provided list';

    protected function executeCommand(): void
    {
        $user = $this->findUser($this->argument('user'));
        if (!$user) {
            $this->error("User not found: " . $this->argument('user'));
            return;
        }

        $roles = [];
        $invalidRoles = [];

        foreach ($this->argument('roles') as $roleIdentifier) {
            $role = $this->findRole($roleIdentifier);
            if ($role) {
                $roles[] = $role;
            } else {
                $invalidRoles[] = $roleIdentifier;
            }
        }

        if (!empty($invalidRoles)) {
            $this->error("Invalid roles: " . implode(', ', $invalidRoles));
            return;
        }

        $currentRoles = $user->roles->pluck('name')->toArray();
        $newRoles = collect($roles)->pluck('name')->toArray();

        $added = array_diff($newRoles, $currentRoles);
        $removed = $this->option('detach') ? array_diff($currentRoles, $newRoles) : [];

        $this->table(
            ['Detail', 'Value'],
            [
                ['User', $user->email],
                ['Current Roles', implode(', ', $currentRoles) ?: 'None'],
                ['New Roles', implode(', ', $newRoles) ?: 'None'],
                ['Roles to Add', implode(', ', $added) ?: 'None'],
                ['Roles to Remove', implode(', ', $removed) ?: 'None'],
                ['Detach Mode', $this->option('detach') ? 'Yes' : 'No']
            ]
        );

        if (!$this->confirmDestructiveAction('Sync user roles?')) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($user, $roles) {
            $user->syncRoles($roles, $this->option('detach'));

            if (!$this->dryRun) {
                $this->info("Roles synced for user '{$user->email}'");
            } else {
                $this->info("[DRY RUN] Would sync roles for user '{$user->email}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }
}
