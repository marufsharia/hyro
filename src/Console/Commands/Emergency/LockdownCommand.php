<?php

namespace Marufsharia\Hyro\Console\Commands\Emergency;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Models\AuditLog;

class LockdownCommand extends BaseCommand
{
    use Confirmable;

    protected $signature = 'hyro:emergency:lockdown
                            {--reason= : Reason for lockdown}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'EMERGENCY: Lockdown the system (disable all non-admin access)';

    protected function executeCommand(): void
    {
        $reason = $this->option('reason') ?: 'Emergency lockdown initiated';

        $this->showLockdownWarning();

        if (!$this->confirmDestructiveOperation('SYSTEM LOCKDOWN', [
            ['Operation', 'Full system lockdown'],
            ['Scope', 'All non-admin users'],
            ['Actions', 'Suspend all non-admin users, revoke all tokens'],
            ['Admin Access', 'Unaffected (can reverse)'],
            ['Use Case', 'Critical security incident'],
        ])) {
            $this->info('Lockdown aborted');
            return;
        }

        // Final confirmation
        if (!$this->confirm('This will disrupt service for ALL non-admin users. Continue?', false)) {
            return;
        }

        $userModel = Config::get('hyro.models.user');
        $roleModel = Config::get('hyro.models.role');

        // Find admin role
        $adminRole = $roleModel::where('slug', 'admin')->first();

        if (!$adminRole) {
            $this->error('Admin role not found. Cannot proceed with lockdown.');
            return;
        }

        // Get non-admin users
        $nonAdminUsers = $userModel::whereDoesntHave('roles', function ($query) use ($adminRole) {
            $query->where('role_id', $adminRole->id);
        })->get();

        $this->info("Found {$nonAdminUsers->count()} non-admin users to lockdown");

        $actions = [
            'suspend_users' => 0,
            'revoke_tokens' => 0,
        ];

        $this->withProgressBar($nonAdminUsers, function ($user) use (&$actions, $reason) {
            try {
                if (!$this->dryRun) {
                    // Suspend user
                    $user->suspend($reason, 'Emergency system lockdown', null);
                    $actions['suspend_users']++;

                    // Revoke tokens
                    $tokenCount = $user->tokens()->count();
                    if ($tokenCount > 0) {
                        $user->tokens()->delete();
                        $actions['revoke_tokens'] += $tokenCount;
                    }

                    // Log individual user lockdown
                    if (Config::get('hyro.auditing.enabled', true)) {
                        AuditLog::log('user_locked_down', $user, null, [
                            'reason' => $reason,
                            'token_count' => $tokenCount,
                        ], [
                            'tags' => ['emergency', 'lockdown'],
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Failed to lockdown {$user->email}: {$e->getMessage()}");
            }
        });

        if (!$this->dryRun) {
            // Create lockdown record
            $lockdownData = [
                'reason' => $reason,
                'actions' => $actions,
                'initiated_at' => now(),
                'initiated_by' => 'cli',
            ];

            // Store lockdown state in cache or database
            cache()->put('hyro:emergency:lockdown', $lockdownData, now()->addDays(7));

            $this->info('✅ System lockdown completed');
            $this->table(['Action', 'Count'], [
                ['Users Suspended', $actions['suspend_users']],
                ['Tokens Revoked', $actions['revoke_tokens']],
            ]);

            // Log system-wide lockdown
            if (Config::get('hyro.auditing.enabled', true)) {
                AuditLog::log('system_lockdown', null, null, $lockdownData, [
                    'tags' => ['emergency', 'lockdown', 'system'],
                ]);
            }

            $this->showLockdownInstructions();
        } else {
            $this->info('🔍 [Dry Run] Would lockdown system');
        }
    }

    private function showLockdownWarning(): void
    {
        $this->newLine();
        $this->error('╔══════════════════════════════════════════════════════════╗');
        $this->error('║                     EMERGENCY LOCKDOWN                   ║');
        $this->error('╠══════════════════════════════════════════════════════════╣');
        $this->error('║ This command will LOCKDOWN THE ENTIRE SYSTEM.           ║');
        $this->error('║                                                          ║');
        $this->error('║ ACTIONS PERFORMED:                                       ║');
        $this->error('║ • Suspend ALL non-admin users                            ║');
        $this->error('║ • Revoke ALL tokens                                      ║');
        $this->error('║ • Block ALL API access for non-admins                    ║');
        $this->error('║                                                          ║');
        $this->error('║ ADMIN USERS ARE UNAFFECTED and can reverse this.         ║');
        $this->error('║                                                          ║');
        $this->error('║ USE ONLY IN CASE OF:                                     ║');
        $this->error('║ • Critical security breach                               ║');
        $this->error('║ • System compromise                                      ║');
        $this->error('║ • Active attack in progress                              ║');
        $this->error('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    private function showLockdownInstructions(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                    LOCKDOWN ACTIVE                       ║');
        $this->info('╠══════════════════════════════════════════════════════════╣');
        $this->info('║ To reverse lockdown, run:                                ║');
        $this->info('║ php artisan hyro:emergency:unlockdown                    ║');
        $this->info('║                                                          ║');
        $this->info('║ To check lockdown status:                                ║');
        $this->info('║ php artisan hyro:emergency:status                        ║');
        $this->info('║                                                          ║');
        $this->info('║ Communicate with affected users about the incident.      ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }
}
