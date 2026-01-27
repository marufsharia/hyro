<?php

namespace Marufsharia\Hyro\Console\Commands\Role;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class DeleteRoleCommand extends BaseCommand
{
    protected $signature = 'hyro:role:delete
                            {role : Role slug or ID}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--reassign-to= : Reassign users to another role before deletion}
                            {--keep-users : Keep users without reassigning (they will lose this role)}';

    protected $description = 'Delete a role';

    protected function executeCommand(): void
    {
        $role = $this->findRole($this->argument('role'));
        if (!$role) {
            $this->error("Role not found: " . $this->argument('role'));
            return;
        }

        // Check if it's a protected role
        if ($this->isProtectedRole($role)) {
            $this->error("Cannot delete protected role: {$role->name}");
            return;
        }

        $userCount = $role->users()->count();
        $privilegeCount = $role->privileges()->count();

        $this->table(
            ['Detail', 'Value'],
            [
                ['Role', $role->name],
                ['Slug', $role->slug],
                ['Description', $role->description ?? 'N/A'],
                ['Users with this role', $userCount],
                ['Assigned Privileges', $privilegeCount],
                ['Created', $role->created_at->format('Y-m-d H:i:s')],
                ['Reassign Users', $this->option('reassign-to') ? 'Yes' : 'No'],
                ['Keep Users', $this->option('keep-users') ? 'Yes' : 'No']
            ]
        );

        if ($userCount > 0 && !$this->option('reassign-to') && !$this->option('keep-users')) {
            $this->error("Role has {$userCount} users. Use --reassign-to or --keep-users flag.");
            return;
        }

        if (!$this->confirmDestructiveAction("PERMANENTLY delete role '{$role->name}'?")) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        // Extra confirmation for production
        if (app()->environment('production') && !$this->force) {
            if (!$this->confirm('This is production. Are you absolutely sure?')) {
                $this->infoMessage('Operation cancelled.');
                return;
            }
        }

        $this->executeInTransaction(function () use ($role) {
            // Handle users reassignment if requested
            if ($this->option('reassign-to')) {
                $this->reassignUsers($role);
            }

            // Delete the role
            $role->delete();

            if (!$this->dryRun) {
                $this->info("Role '{$role->name}' has been deleted");
            } else {
                $this->info("[DRY RUN] Would delete role '{$role->name}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }

    protected function isProtectedRole($role): bool
    {
        $protectedRoles = config('hyro.protected_roles', ['super-admin', 'admin', 'users']);

        return in_array($role->slug, $protectedRoles) ||
            in_array($role->name, $protectedRoles);
    }

    protected function reassignUsers($role): void
    {
        $targetRole = $this->findRole($this->option('reassign-to'));
        if (!$targetRole) {
            throw new \RuntimeException("Target role not found: " . $this->option('reassign-to'));
        }

        if ($targetRole->id === $role->id) {
            throw new \RuntimeException("Cannot reassign users to the same role being deleted");
        }

        $users = $role->users()->get();
        $count = 0;

        foreach ($users as $user) {
            // Remove old role and assign new one
            $user->removeRole($role);
            $user->assignRole($targetRole);
            $count++;

            if (!$this->dryRun) {
                $this->info("Reassigned users '{$user->email}' from '{$role->name}' to '{$targetRole->name}'");
            } else {
                $this->info("[DRY RUN] Would reassign users '{$user->email}'");
            }
        }

        $this->info("Reassigned {$count} users from '{$role->name}' to '{$targetRole->name}'");
    }
}
