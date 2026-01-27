<?php

namespace Marufsharia\Hyro\Console\Commands\Token;

use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Models\User;

class RevokeTokensCommand extends BaseCommand
{
    protected $signature = 'hyro:token:revoke
                            {--users= : User email or ID (revoke all if not specified)}
                            {--token-id= : Specific token ID to revoke}
                            {--name= : Revoke tokens with specific name}
                            {--type= : Token type (access, refresh, etc.)}
                            {--older-than= : Revoke tokens older than X days}
                            {--dry-run : Preview changes without applying them}
                            {--force : Skip confirmation prompts}
                            {--reason= : Reason for revocation}';

    protected $description = 'Revoke access tokens';

    protected function executeCommand(): void
    {
        $reason = $this->option('reason') ?? 'Revoked via CLI';

        if ($this->option('token-id')) {
            $this->revokeSpecificToken();
            return;
        }

        $query = \DB::table('personal_access_tokens')->where('revoked', false);

        if ($userId = $this->option('users')) {
            $user = $this->findUser($userId);
            if (!$user) {
                $this->error("User not found: {$userId}");
                return;
            }
            $query->where('tokenable_id', $user->id)
                ->where('tokenable_type', get_class($user));
            $this->infoMessage("Targeting users: {$user->email}");
        }

        if ($name = $this->option('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($type = $this->option('type')) {
            $query->where('type', $type);
        }

        if ($olderThan = $this->option('older-than')) {
            $date = now()->subDays($olderThan);
            $query->where('created_at', '<', $date);
        }

        $tokenCount = $query->count();

        if ($tokenCount === 0) {
            $this->warn('No tokens found matching criteria');
            return;
        }

        $this->table(
            ['Detail', 'Value'],
            [
                ['Operation', 'Token Revocation'],
                ['Reason', $reason],
                ['Tokens to Revoke', $tokenCount],
                ['User Filter', $this->option('users') ?? 'All Users'],
                ['Name Filter', $this->option('name') ?? 'Any'],
                ['Type Filter', $this->option('type') ?? 'Any'],
                ['Older Than', $this->option('older-than') ? "{$this->option('older-than')} days" : 'Any']
            ]
        );

        if (!$this->confirmDestructiveAction("Revoke {$tokenCount} token(s)?")) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $revokedCount = 0;

        $query->chunk(100, function ($tokens) use (&$revokedCount, $reason) {
            foreach ($tokens as $token) {
                $this->executeInTransaction(function () use ($token, $reason, &$revokedCount) {
                    \DB::table('personal_access_tokens')
                        ->where('id', $token->id)
                        ->update([
                            'revoked' => true,
                            'revoked_at' => now(),
                            'revocation_reason' => $reason
                        ]);

                    $revokedCount++;

                    if (!$this->dryRun && $this->option('verbose')) {
                        $this->info("Revoked token ID: {$token->id} ({$token->name})");
                    }
                });
            }
        });

        if (!$this->dryRun) {
            $this->info("Revoked {$revokedCount} token(s)");
        } else {
            $this->info("[DRY RUN] Would revoke {$tokenCount} token(s)");
        }

        $this->stats['processed'] = $tokenCount;
        $this->stats['succeeded'] = $revokedCount;
    }

    protected function revokeSpecificToken(): void
    {
        $tokenId = $this->option('token-id');
        $token = \DB::table('personal_access_tokens')->find($tokenId);

        if (!$token) {
            $this->error("Token not found: {$tokenId}");
            return;
        }

        if ($token->revoked) {
            $this->warn("Token {$tokenId} is already revoked");
            return;
        }

        $this->table(
            ['Detail', 'Value'],
            [
                ['Token ID', $token->id],
                ['Name', $token->name],
                ['Type', $token->type],
                ['Created', $token->created_at],
                ['Last Used', $token->last_used_at ?? 'Never'],
                ['Abilities', implode(', ', json_decode($token->abilities, true))],
                ['Reason', $this->option('reason') ?? 'Manual revocation']
            ]
        );

        if (!$this->confirmDestructiveAction("Revoke this specific token?")) {
            $this->infoMessage('Operation cancelled.');
            return;
        }

        $this->executeInTransaction(function () use ($token) {
            \DB::table('personal_access_tokens')
                ->where('id', $token->id)
                ->update([
                    'revoked' => true,
                    'revoked_at' => now(),
                    'revocation_reason' => $this->option('reason') ?? 'Manual revocation via CLI'
                ]);

            if (!$this->dryRun) {
                $this->info("Token {$token->id} has been revoked");
            } else {
                $this->info("[DRY RUN] Would revoke token {$token->id}");
            }

            $this->stats['processed']++;
            $this->stats['succeeded']++;
        });
    }
}
