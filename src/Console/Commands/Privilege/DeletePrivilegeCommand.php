<?php

namespace Marufsharia\Hyro\Console\Commands\Privilege;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class DeletePrivilegeCommand extends BaseCommand
{
    protected $signature = 'hyro:privilege:delete
                            {privilege : Privilege slug or ID}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--cascade : Remove from all roles that have this privilege}';

    protected $description = 'Delete a privilege';

    protected function executeCommand(): void
    {
        $privilege = $this->findPrivilege($this->argument('privilege'));
        if (!$privilege) {
            $this->error("Privilege not found: " . $this->argument('privilege'));
            return;
        }

        // Check if it's a protected privilege
        if ($this->isProtectedPrivilege($privilege)) {
            $this->error("Cannot delete protected privilege: {$privilege->name}");
            return;
        }

        $roleCount = $privilege->roles()->count();

        $this->table(
            ['Detail', 'Value'],
            [
                ['Privilege', $privilege->name],
                ['Slug', $privilege->slug],
                ['Scope', $privilege->scope],
                ['Description', $privilege->description ?? 'N/A'],
                ['Roles using this', $roleCount],
                ['Cascade', $this->option('cascade') ? 'Yes' : 'No']
            ]
        );

        if (!$this->confirmDestructiveAction("Delete privilege '{$privilege->name}'?")) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($privilege) {
            if ($this->option('cascade')) {
                $privilege->roles()->detach();
            }

            $privilege->delete();

            if (!$this->dryRun) {
                $this->info("Privilege '{$privilege->name}' has been deleted");
            } else {
                $this->info("[DRY RUN] Would delete privilege '{$privilege->name}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }

    protected function isProtectedPrivilege($privilege): bool
    {
        $protectedPrivileges = config('hyro.protected_privileges', ['*', 'user:manage', 'role:view']);

        return in_array($privilege->slug, $protectedPrivileges);
    }
}
