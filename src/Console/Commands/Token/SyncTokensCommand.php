<?php

namespace Marufsharia\Hyro\Console\Commands\Token;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;

class SyncTokensCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:token:sync
                            {users? : User identifier (email, ID, or username) - sync all users if omitted}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}
                            {--verbose : Show detailed output}';

    protected $description = 'Sync token abilities with users privileges';

    protected function executeCommand(): void
    {
        $userIdentifier = $this->argument('users');
        $verbose = $this->option('verbose');

        $tokenService = app(\Marufsharia\Hyro\Services\TokenSynchronizationService::class);

        if ($userIdentifier) {
            $this->syncSingleUser($userIdentifier, $tokenService, $verbose);
        } else {
            $this->syncAllUsers($tokenService, $verbose);
        }
    }

    private function syncSingleUser(string $userIdentifier, $tokenService, bool $verbose): void
    {
        $this->validateUserIdentifier($userIdentifier);

        $user = $this->findUser($userIdentifier);
        if (!$user) {
            throw new \RuntimeException("User not found: {$userIdentifier}");
        }

        // Check if users uses Sanctum
        if (!in_array(\Laravel\Sanctum\HasApiTokens::class, class_uses_recursive($user))) {
            $this->warn("User '{$user->email}' does not use Sanctum tokens");
            return;
        }

        $status = $tokenService->getSyncStatus($user);

        if ($status['token_count'] === 0) {
            $this->warn("User '{$user->email}' has no tokens to sync");
            return;
        }

        $this->info("Syncing tokens for users: {$user->email}");
        $this->table(['Metric', 'Value'], [
            ['Total Tokens', $status['token_count']],
            ['Already Synced', $status['synced_tokens']],
            ['Needs Sync', $status['unsynced_tokens']],
        ]);

        if ($status['unsynced_tokens'] === 0) {
            $this->info('✅ All tokens are already in sync');
            return;
        }

        if (!$this->confirmOperation('Sync users tokens', [
            ['User', $user->email],
            ['Tokens to Sync', $status['unsynced_tokens']],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ])) {
            return;
        }

        $this->executeInTransaction(function () use ($user, $tokenService, $verbose) {
            if (!$this->dryRun) {
                $tokenService->checkAndSyncIfNeeded($user);

                $this->info("✅ Token sync completed for users '{$user->email}'");

                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('tokens_synced', $user, null, [
                        'synced_by' => 'cli',
                    ], [
                        'tags' => ['cli', 'token', 'sync'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would sync tokens for users '{$user->email}'");
            }
        });
    }

    private function syncAllUsers($tokenService, bool $verbose): void
    {
        $userModel = Config::get('hyro.models.users');
        $users = $userModel::has('tokens')->get();

        if ($users->isEmpty()) {
            $this->info('No users with tokens found');
            return;
        }

        $this->info("Found {$users->count()} users with tokens");

        $summary = [];
        foreach ($users as $user) {
            $status = $tokenService->getSyncStatus($user);
            $summary[] = [
                'users' => $user->email,
                'tokens' => $status['token_count'],
                'synced' => $status['synced_tokens'],
                'unsynced' => $status['unsynced_tokens'],
            ];
        }

        $this->table(['User', 'Total Tokens', 'Synced', 'Needs Sync'], $summary);

        $totalUnsynced = array_sum(array_column($summary, 'unsynced'));
        if ($totalUnsynced === 0) {
            $this->info('✅ All tokens are already in sync');
            return;
        }

        if (!$this->confirmDestructiveOperation('Sync ALL users tokens', [
            ['Users Affected', count($users)],
            ['Total Tokens', array_sum(array_column($summary, 'tokens'))],
            ['Tokens to Sync', $totalUnsynced],
            ['Impact', 'All unsynced tokens will be updated'],
        ])) {
            return;
        }

        $this->withProgressBar($users, function ($user) use ($tokenService, $verbose) {
            try {
                if (!$this->dryRun) {
                    $tokenService->checkAndSyncIfNeeded($user);

                    if ($verbose) {
                        $this->info("  Synced tokens for {$user->email}");
                    }
                } else {
                    if ($verbose) {
                        $this->info("  [Dry Run] Would sync tokens for {$user->email}");
                    }
                }
            } catch (\Exception $e) {
                $this->warn("  Failed to sync tokens for {$user->email}: {$e->getMessage()}");
            }
        });

        if (!$this->dryRun) {
            $this->info("✅ Token sync completed for all users");

            if (Config::get('hyro.auditing.enabled', true)) {
                AuditLog::log('all_tokens_synced', null, null, [
                    'user_count' => count($users),
                    'synced_by' => 'cli',
                ], [
                    'tags' => ['cli', 'token', 'bulk_sync'],
                ]);
            }
        } else {
            $this->info("🔍 [Dry Run] Would sync tokens for all users");
        }
    }
}
