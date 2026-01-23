<?php

namespace Marufsharia\Hyro\Console\Commands\Emergency;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\User;

class UnlockdownCommand extends BaseCommand
{
    protected $signature = 'hyro:emergency:unlockdown
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--reason= : Reason for ending lockdown}
                            {--include-super-admins : Also reactivate super admins}
                            {--user= : Specific user to reactivate}';

    protected $description = 'End emergency lockdown and reactivate users';

    protected function executeCommand(): void
    {
        $this->alert('ENDING EMERGENCY LOCKDOWN');
        $this->info('========================================');
        $this->info('This will reactivate users disabled during lockdown');
        $this->info('========================================');

        $reason = $this->option('reason') ?? 'Lockdown ended via CLI';

        $query = User::where('is_active', false)
            ->whereNotNull('locked_at');

        if ($userId = $this->option('user')) {
            $user = $this->findUser($userId);
            if (!$user) {
                $this->error("User not found: {$userId}");
                return;
            }
            $query->where('id', $user->id);
            $this->infoMessage("Targeting specific user: {$user->email}");
        }

        if (!$this->option('include-super-admins')) {
            $query->where('is_super_admin', false);
            $this->infoMessage('Super admins will remain as-is');
        }

        $affectedUsers = $query->count();

        if ($affectedUsers === 0) {
            $this->warn('No users found to reactivate');
            return;
        }

        $this->table(
            ['Detail', 'Value'],
            [
                ['Operation', 'End Lockdown'],
                ['Reason', $reason],
                ['Users to Reactivate', $affectedUsers],
                ['Timestamp', now()->toDateTimeString()],
                ['Include Super Admins', $this->option('include-super-admins') ? 'Yes' : 'No'],
                ['Specific User', $this->option('user') ? 'Yes' : 'No']
            ]
        );

        if (!$this->confirmDestructiveAction('End lockdown and reactivate users?')) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        // Extra confirmation
        if (!$this->confirm('TYPE "END LOCKDOWN" to proceed:')) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $reactivatedCount = 0;

        $this->withItemsProgressBar($query->get(), function ($user) use (&$reactivatedCount, $reason) {
            $this->executeInTransaction(function () use ($user, $reason, &$reactivatedCount) {
                $user->update([
                    'is_active' => true,
                    'locked_reason' => null,
                    'locked_at' => null,
                    'unlocked_at' => now(),
                    'unlock_reason' => $reason
                ]);

                $reactivatedCount++;

                if (!$this->dryRun && $this->option('verbose')) {
                    $this->info("Reactivated user: {$user->email}");
                }
            });
        });

        if (!$this->dryRun) {
            $this->success("LOCKDOWN ENDED: {$reactivatedCount} users reactivated");
        } else {
            $this->info("[DRY RUN] Would reactivate {$affectedUsers} users");
        }

        $this->stats['processed'] = $affectedUsers;
        $this->stats['succeeded'] = $reactivatedCount;
    }
}
