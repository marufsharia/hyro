<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class UnsuspendCommand extends BaseCommand
{
    protected $signature = 'hyro:user:unsuspend
                            {user : User email or ID}
                            {--reason= : Reason for unsuspension}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Unsuspend a user account';

    protected function executeCommand(): void
    {
        $user = $this->findUser($this->argument('user'));
        if (!$user) {
            $this->error("User not found: " . $this->argument('user'));
            return;
        }

        if (!$user->is_suspended) {
            $this->warn("User '{$user->email}' is not suspended");
            return;
        }

        $reason = $this->option('reason') ?? 'Unsuspended via CLI';

        $this->table(
            ['Detail', 'Value'],
            [
                ['User', $user->email],
                ['Current Status', 'Suspended'],
                ['Suspended Since', $user->suspended_at?->format('Y-m-d H:i:s') ?? 'Unknown'],
                ['Suspension Reason', $user->suspension_reason ?? 'Not specified'],
                ['Unsuspension Reason', $reason],
                ['Operation', 'Unsuspend User']
            ]
        );

        if (!$this->confirmDestructiveAction("Unsuspend user '{$user->email}'?")) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($user, $reason) {
            $user->update([
                'is_suspended' => false,
                'suspension_reason' => null,
                'suspended_at' => null,
                'unsuspended_at' => now(),
                'unsuspension_reason' => $reason
            ]);

            if (!$this->dryRun) {
                $this->info("User '{$user->email}' has been unsuspended");
            } else {
                $this->info("[DRY RUN] Would unsuspend user '{$user->email}'");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }
}
