<?php

namespace Marufsharia\Hyro\Console\Commands\Role;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class RevokePrivilegeCommand extends BaseCommand
{
    protected $signature = 'hyro:role:revoke-privilege
                            {role : Role slug or ID}
                            {privilege : Privilege slug or ID}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--cascade : Also remove from child roles (if hierarchical)}';

    protected $description = 'Revoke a privilege from a role';

    protected function executeCommand(): void
    {
        $role = $this->findRole($this->argument('role'));
        if (!$role) {
            $this->error("Role not found: " . $this->argument('role'));
            return;
        }

        $privilege = $this->findPrivilege($this->argument('privilege'));
        if (!$privilege) {
            $this->error("Privilege not found: " . $this->argument('privilege'));
            return;
        }

        if (!$role->hasPrivilege($privilege->slug)) {
            $this->warn("Role '{$role->name}' does not have privilege '{$privilege->name}'");
            return;
        }

        $this->table(
            ['Detail', 'Value'],
            [
                ['Role', $role->name],
                ['Privilege', $privilege->name],
                ['Scope', $privilege->scope],
                ['Users Affected', $role->users()->count()],
                ['Cascade', $this->option('cascade') ? 'Yes' : 'No'],
                ['Operation', 'Revoke Privilege']
            ]
        );

        if (!$this->confirmDestructiveAction(
            "Revoke privilege '{$privilege->name}' from role '{$role->name}'?"
        )) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($role, $privilege) {
            $role->revokePrivilege($privilege);

            if ($this->option('cascade') && method_exists($role, 'childRoles')) {
                $this->revokeFromChildren($role, $privilege);
            }

            if (!$this->dryRun) {
                $this->info("Privilege '{$privilege->name}' revoked from role '{$role->name}'");
            } else {
                $this->info("[DRY RUN] Would revoke privilege '{$privilege->name}' from role '{$role->name}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }

    protected function revokeFromChildren($role, $privilege): void
    {
        $children = $role->childRoles()->get();

        foreach ($children as $child) {
            if ($child->hasPrivilege($privilege->slug)) {
                $child->revokePrivilege($privilege);
                $this->info("Also revoked from child role '{$child->name}'");
                $this->stats['processed']++;
                $this->stats['succeeded']++;
            }
        }
    }
}
