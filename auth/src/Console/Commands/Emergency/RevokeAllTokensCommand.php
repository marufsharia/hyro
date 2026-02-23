<?php

namespace Marufsharia\Hyro\Auth\Console\Commands\Emergency;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Core\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Core\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Models\AuditLog;

class RevokeAllTokensCommand extends BaseCommand
{
    use Confirmable;

    protected $signature = 'hyro:emergency:revoke-all-tokens
                            {--except-admin : Keep admin users tokens}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'EMERGENCY: Revoke all users tokens (security breach response)';

    protected function executeCommand(): void
    {
        $exceptAdmin = $this->option('except-admin');

        // This is a critical emergency command - show warnings
        $this->showEmergencyWarning();

        if (!$this->confirmDestructiveOperation('REVOKE ALL USER TOKENS', [
            ['Operation', 'Irreversible token revocation'],
            ['Scope', $exceptAdmin ? 'All non-admin users' : 'ALL users including admins'],
            ['Impact', 'All active sessions will be terminated'],
            ['Recovery', 'Users must create new tokens'],
            ['Use Case', 'Security breach response'],
        ])) {
            $this->info('Command aborted');
            return;
        }

        // Double confirmation with typed phrase
        $confirmPhrase = $exceptAdmin ? 'REVOKE ALL NON-ADMIN TOKENS' : 'REVOKE ALL TOKENS';
        $typed = $this->ask("Type '{$confirmPhrase}' to proceed");

        if ($typed !== $confirmPhrase) {
            $this->error('Confirmation phrase mismatch. Aborting.');
            return;
        }

        $userModel = Config::get('hyro.models.users');
        $roleModel = Config::get('hyro.models.role');

        // Get users to revoke tokens from
        if ($exceptAdmin) {
            $adminRole = $roleModel::where('slug', 'admin')->first();

            if ($adminRole) {
                $users = $userModel::whereDoesntHave('roles', function ($query) use ($adminRole) {
                    $query->where('role_id', $adminRole->id);
                })->get();
            } else {
                $users = $userModel::all();
            }
        } else {
            $users = $userModel::all();
        }

        $this->info("Found {$users->count()} users to revoke tokens from");

        $this->withProgressBar($users, function ($user) {
            try {
                if (!$this->dryRun) {
                    $tokenCount = $user->tokens()->count();

                    if ($tokenCount > 0) {
                        $user->tokens()->delete();

                        if (Config::get('hyro.auditing.enabled', true)) {
                            AuditLog::log('tokens_revoked_emergency', $user, null, [
                                'token_count' => $tokenCount,
                                'revoked_by' => 'cli_emergency',
                            ], [
                                'tags' => ['emergency', 'token', 'revoke'],
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->warn("Failed to revoke tokens for {$user->email}: {$e->getMessage()}");
            }
        });

        if (!$this->dryRun) {
            $this->info('✅ All tokens revoked successfully');

            // Log emergency action
            if (Config::get('hyro.auditing.enabled', true)) {
                AuditLog::log('emergency_token_revocation', null, null, [
                    'user_count' => $users->count(),
                    'except_admin' => $exceptAdmin,
                    'executed_by' => 'cli',
                ], [
                    'tags' => ['emergency', 'security', 'token_revocation'],
                ]);
            }
        } else {
            $this->info('🔍 [Dry Run] Would revoke tokens for all users');
        }
    }

    private function showEmergencyWarning(): void
    {
        $this->newLine();
        $this->error('╔══════════════════════════════════════════════════════════╗');
        $this->error('║                     EMERGENCY COMMAND                    ║');
        $this->error('╠══════════════════════════════════════════════════════════╣');
        $this->error('║ This command will REVOKE ALL USER TOKENS.               ║');
        $this->error('║                                                          ║');
        $this->error('║ USE ONLY IN CASE OF:                                     ║');
        $this->error('║ • Security breach                                        ║');
        $this->error('║ • Suspected token compromise                             ║');
        $this->error('║ • Mass account takeover                                  ║');
        $this->error('║                                                          ║');
        $this->error('║ CONSEQUENCES:                                            ║');
        $this->error('║ • All active sessions terminated                         ║');
        $this->error('║ • Users must create new tokens                           ║');
        $this->error('║ • API clients will stop working                          ║');
        $this->error('║ • Mobile apps will be logged out                         ║');
        $this->error('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }
}
