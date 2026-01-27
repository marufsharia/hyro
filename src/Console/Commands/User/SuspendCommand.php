<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;

class SuspendCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:users:suspend
                            {users : User identifier (email, ID, or username)}
                            {reason : Reason for suspension}
                            {--duration= : Suspension duration (e.g., "1 hour", "2 days", or minutes as number)}
                            {--details= : Additional details}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'Suspend a users account';

    protected function executeCommand(): void
    {
        $userIdentifier = $this->argument('users');
        $reason = $this->argument('reason');
        $duration = $this->option('duration');
        $details = $this->option('details');

        $this->validateUserIdentifier($userIdentifier);

        $user = $this->findUser($userIdentifier);

        if (!$user) {
            throw new \RuntimeException("User not found: {$userIdentifier}");
        }

        // Check if already suspended
        if ($user->isSuspended()) {
            $this->warn("User '{$user->email}' is already suspended");

            if (!$this->confirm('Unsuspend instead?', false)) {
                return;
            }

             $this->unsuspendUser($user); //removed return
        }

        // Parse duration
        $durationSeconds = null;
        if ($duration) {
            $durationSeconds = $this->parseDurationToSeconds($duration);
        }

        // Show operation details
        $operationDetails = [
            ['User', "{$user->email} (ID: {$user->id})"],
            ['Reason', $reason],
            ['Duration', $duration ?: 'Indefinite'],
            ['Details', $details ?: 'N/A'],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ];

        if (!$this->confirmDestructiveOperation('Suspend users account', [
            ['User Access', 'Immediately blocked'],
            ['Active Sessions', 'All tokens will be revoked'],
            ['API Access', 'All API calls will be rejected'],
        ])) {
            return;
        }

        // Execute suspension
        $this->executeInTransaction(function () use ($user, $reason, $details, $durationSeconds) {
            if (!$this->dryRun) {
                $user->suspend($reason, $details, $durationSeconds);

                $durationText = $durationSeconds
                    ? " for {$this->formatSeconds($durationSeconds)}"
                    : ' indefinitely';

                $this->info("✅ User '{$user->email}' suspended{$durationText}");

                // Log audit
                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('user_suspended', $user, null, [
                        'reason' => $reason,
                        'details' => $details,
                        'duration_seconds' => $durationSeconds,
                        'suspended_by' => 'cli',
                    ], [
                        'tags' => ['cli', 'users', 'suspension'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would suspend users '{$user->email}'");
            }
        });
    }

    private function unsuspendUser($user): void
    {
        if (!$this->confirm("Unsuspend users '{$user->email}'?", false)) {
            return;
        }

        $this->executeInTransaction(function () use ($user) {
            if (!$this->dryRun) {
                $user->unsuspend();
                $this->info("✅ User '{$user->email}' unsuspended");

                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('user_unsuspended', $user, null, [
                        'unsuspended_by' => 'cli',
                    ], [
                        'tags' => ['cli', 'users', 'unsuspension'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would unsuspend users '{$user->email}'");
            }
        });
    }

    private function parseDurationToSeconds(string $duration): int
    {
        if (is_numeric($duration)) {
            return (int) $duration * 60; // Assume minutes
        }

        $modifiers = [
            'second' => 1,
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            'week' => 604800,
            'month' => 2592000,
            'year' => 31536000,
        ];

        foreach ($modifiers as $unit => $seconds) {
            if (preg_match('/^(\d+)\s*' . $unit . 's?$/i', $duration, $matches)) {
                return (int) $matches[1] * $seconds;
            }
        }

        throw new \RuntimeException("Invalid duration format: {$duration}");
    }

    private function formatSeconds(int $seconds): string
    {
        $units = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1,
        ];

        foreach ($units as $unit => $divisor) {
            if ($seconds >= $divisor) {
                $value = floor($seconds / $divisor);
                return $value . ' ' . $unit . ($value > 1 ? 's' : '');
            }
        }

        return $seconds . ' seconds';
    }
}
