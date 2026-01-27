<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class RemoveRoleCommand extends BaseCommand
{
    protected $signature = 'hyro:users:remove-role
                            {users : User email or ID}
                            {role : Role slug or ID}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Remove a role from a users';

    protected function executeCommand(): void
    {
        $user = $this->findUser($this->argument('users'));
        if (!$user) {
            $this->error("User not found: " . $this->argument('users'));
            return;
        }

        $role = $this->findRole($this->argument('role'));
        if (!$role) {
            $this->error("Role not found: " . $this->argument('role'));
            return;
        }

        if (!$user->hasRole($role->slug)) {
            $this->warn("User does not have role '{$role->name}'");
            return;
        }

        if (!$this->confirmDestructiveAction(
            "Remove role '{$role->name}' from users '{$user->email}'?"
        )) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($user, $role) {
            $user->removeRole($role);

            if (!$this->dryRun) {
                $this->info("Role '{$role->name}' removed from users '{$user->email}'");
            } else {
                $this->info("[DRY RUN] Would remove role '{$role->name}' from users '{$user->email}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }
}
