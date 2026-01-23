<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;

class AssignRoleCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:user:assign-role
                            {user : User identifier (email, ID, or username)}
                            {role : Role slug or ID}
                            {--reason= : Reason for assignment}
                            {--expires= : Expiration time (e.g., "1 hour", "2 days", or minutes as number)}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'Assign a role to a user';

    protected function executeCommand(): void
    {
        $userIdentifier = $this->argument('user');
        $roleIdentifier = $this->argument('role');
        $reason = $this->option('reason');
        $expiresAt = $this->validateDuration($this->option('expires'));

        // Validate inputs
        $this->validateUserIdentifier($userIdentifier);
        $this->validateRoleIdentifier($roleIdentifier);

        // Find user and role
        $user = $this->findUser($userIdentifier);
        $role = $this->findRole($roleIdentifier);

        if (!$user) {
            throw new \RuntimeException("User not found: {$userIdentifier}");
        }

        if (!$role) {
            throw new \RuntimeException("Role not found: {$roleIdentifier}");
        }

        // Check if already assigned
        $alreadyAssigned = $user->roles()
            ->where('role_id', $role->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($alreadyAssigned) {
            $this->warn("User already has role '{$role->slug}'");

            if (!$this->confirm('Update existing assignment?', false)) {
                return;
            }
        }

        // Show operation details
        $details = [
            ['User', "{$user->email} (ID: {$user->id})"],
            ['Role', $role->slug],
            ['Reason', $reason ?? 'N/A'],
            ['Expires', $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'Never'],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ];

        if (!$this->confirmOperation('Assign role to user', $details)) {
            return;
        }

        // Execute assignment
        $this->executeInTransaction(function () use ($user, $role, $reason, $expiresAt) {
            $user->assignRole($role->slug, $reason, $expiresAt);

            if (!$this->dryRun) {
                $this->info("✅ Role '{$role->slug}' assigned to user '{$user->email}'");

                // Log audit
                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('role_assigned', $user, $role, [
                        'assigned_by' => 'cli',
                        'reason' => $reason,
                        'expires_at' => $expiresAt?->toISOString(),
                    ], [
                        'tags' => ['cli', 'user', 'role'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would assign role '{$role->slug}' to user '{$user->email}'");
            }
        });
    }
}
