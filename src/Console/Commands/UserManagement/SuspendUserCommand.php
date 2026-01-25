<?php

namespace Marufsharia\Hyro\Console\Commands\UserManagement;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Marufsharia\Hyro\Facades\Hyro;

class SuspendUserCommand extends Command
{
    protected $signature = 'hyro:suspend-user
                            {user : User ID, email, or username}
                            {--reason= : Reason for suspension}
                            {--duration= : Duration in days (0 for permanent)}
                            {--unsuspend : Unsuspend instead of suspend}
                            {--force : Force suspension without confirmation}
                            {--no-interaction : Run non-interactively}';

    protected $description = 'Suspend or unsuspend a Hyro user';

    public function handle(): int
    {
        $userIdentifier = $this->argument('user');
        $shouldUnsuspend = $this->option('unsuspend');

        if ($shouldUnsuspend) {
            return $this->handleUnsuspend($userIdentifier);
        }

        return $this->handleSuspend($userIdentifier);
    }

    protected function handleSuspend(string $userIdentifier): int
    {
        $this->info('🚫 Suspending user...');

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error("❌ User not found: {$userIdentifier}");
            return Command::FAILURE;
        }

        // Check if already suspended
        if ($user->suspended_at) {
            $this->error("❌ User is already suspended since {$user->suspended_at}");
            return Command::FAILURE;
        }

        // Get suspension details
        $reason = $this->option('reason') ?: $this->ask('Reason for suspension');
        $duration = (int) $this->option('duration') ?: $this->ask('Duration in days (0 for permanent)', 0);

        if (empty($reason)) {
            $this->error('❌ Suspension reason is required');
            return Command::FAILURE;
        }

        // Confirm suspension
        if (!$this->option('force') && !$this->option('no-interaction')) {
            $this->table(['Field', 'Value'], [
                ['User', "{$user->name} ({$user->email})"],
                ['Reason', $reason],
                ['Duration', $duration === 0 ? 'Permanent' : "{$duration} days"],
            ]);

            if (!$this->confirm('Are you sure you want to suspend this user?')) {
                $this->info('Suspension cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            // Suspend the user
            DB::transaction(function () use ($user, $reason, $duration) {
                // Update user table
                $user->suspended_at = now();
                $user->save();

                // Create suspension record
                DB::table(config('hyro.database.tables.user_suspensions', 'hyro_user_suspensions'))->insert([
                    'user_id' => $user->id,
                    'reason' => $reason,
                    'suspended_at' => now(),
                    'suspended_until' => $duration > 0 ? now()->addDays($duration) : null,
                    'auto_unsuspend' => $duration > 0,
                    'suspended_by' => auth()->check() ? auth()->id() : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Revoke all tokens
                if (class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                    $user->tokens()->delete();
                }
            });

            $this->info("✅ User suspended successfully!");
            $this->line("ID: {$user->id}");
            $this->line("Name: {$user->name}");
            $this->line("Email: {$user->email}");
            $this->line("Reason: {$reason}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to suspend user: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    protected function handleUnsuspend(string $userIdentifier): int
    {
        $this->info('✅ Unsuspending user...');

        // Find user
        $user = $this->findUser($userIdentifier);
        if (!$user) {
            $this->error("❌ User not found: {$userIdentifier}");
            return Command::FAILURE;
        }

        // Check if actually suspended
        if (!$user->suspended_at) {
            $this->error("❌ User is not suspended");
            return Command::FAILURE;
        }

        // Get unsuspend reason
        $reason = $this->option('reason') ?: $this->ask('Reason for unsuspension', 'Manual unsuspension');

        // Confirm unsuspension
        if (!$this->option('force') && !$this->option('no-interaction')) {
            $this->table(['Field', 'Value'], [
                ['User', "{$user->name} ({$user->email})"],
                ['Suspended Since', $user->suspended_at],
                ['Reason', $reason],
            ]);

            if (!$this->confirm('Are you sure you want to unsuspend this user?')) {
                $this->info('Unsuspension cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            DB::transaction(function () use ($user, $reason) {
                // Update user table
                $user->suspended_at = null;
                $user->save();

                // Update suspension record
                DB::table(config('hyro.database.tables.user_suspensions', 'hyro_user_suspensions'))
                    ->where('user_id', $user->id)
                    ->whereNull('unsuspended_at')
                    ->update([
                        'unsuspended_at' => now(),
                        'unsuspend_reason' => $reason,
                        'unsuspended_by' => auth()->check() ? auth()->id() : null,
                        'updated_at' => now(),
                    ]);
            });

            $this->info("✅ User unsuspended successfully!");
            $this->line("ID: {$user->id}");
            $this->line("Name: {$user->name}");
            $this->line("Email: {$user->email}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to unsuspend user: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    protected function findUser(string $identifier)
    {
        $userModel = config('hyro.models.user', App\Models\User::class);

        // Try by ID
        if (is_numeric($identifier)) {
            return $userModel::find($identifier);
        }

        // Try by email
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $userModel::where('email', $identifier)->first();
        }

        // Try by username (if username column exists)
        if (Schema::hasColumn((new $userModel)->getTable(), 'username')) {
            $user = $userModel::where('username', $identifier)->first();
            if ($user) return $user;
        }

        // Try by name
        return $userModel::where('name', 'LIKE', "%{$identifier}%")->first();
    }
}
