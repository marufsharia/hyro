<?php

namespace Marufsharia\Hyro\Console\Commands\Role;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;
use Marufsharia\Hyro\Models\Privilege;
use Marufsharia\Hyro\Models\Role;

class GrantPrivilegeCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:role:grant-privilege
                            {role : Role slug or ID}
                            {privilege : Privilege slug (supports wildcards)}
                            {--reason= : Reason for granting}
                            {--expires= : Expiration time}
                            {--conditions= : JSON conditions for conditional grant}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'Grant a privilege to a role';

    protected function executeCommand(): void
    {
        $roleIdentifier = $this->argument('role');
        $privilegeSlug = $this->argument('privilege');
        $reason = $this->option('reason');
        $expiresAt = $this->validateDuration($this->option('expires'));
        $conditions = $this->parseConditions($this->option('conditions'));

        $this->validateRoleIdentifier($roleIdentifier);
        $this->validatePrivilegeIdentifier($privilegeSlug);

        // Find role
        $role = $this->findRole($roleIdentifier);
        if (!$role) {
            throw new \RuntimeException("Role not found: {$roleIdentifier}");
        }

        // Check if privilege already granted
        $existingGrant = $role->privileges()
            ->where('slug', $privilegeSlug)
            ->where(function ($query) {
                $query->whereNull('privilege_role.expires_at')
                    ->orWhere('privilege_role.expires_at', '>', now());
            })
            ->first();

        if ($existingGrant) {
            $this->warn("Privilege '{$privilegeSlug}' already granted to role '{$role->slug}'");

            if (!$this->confirm('Update existing grant?', false)) {
                return;
            }
        }

        // Show operation details
        $details = [
            ['Role', $role->slug],
            ['Privilege', $privilegeSlug],
            ['Reason', $reason ?: 'N/A'],
            ['Expires', $expiresAt ? $expiresAt->format('Y-m-d H:i:s') : 'Never'],
            ['Conditions', $conditions ? json_encode($conditions) : 'None'],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ];

        if (!$this->confirmOperation('Grant privilege to role', $details)) {
            return;
        }

        // Grant privilege
        $this->executeInTransaction(function () use ($role, $privilegeSlug, $reason, $expiresAt, $conditions) {
            if (!$this->dryRun) {
                $role->grantPrivilege($privilegeSlug, $reason, $conditions, $expiresAt);
                $this->info("✅ Privilege '{$privilegeSlug}' granted to role '{$role->slug}'");

                // Invalidate caches
                app(\Marufsharia\Hyro\Contracts\CacheInvalidatorContract::class)
                    ->invalidateRoleCache($role->id);

                // Log audit
                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('privilege_granted', $role, null, [
                        'privilege' => $privilegeSlug,
                        'reason' => $reason,
                        'expires_at' => $expiresAt?->toISOString(),
                        'conditions' => $conditions,
                    ], [
                        'tags' => ['cli', 'role', 'privilege'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would grant privilege '{$privilegeSlug}' to role '{$role->slug}'");
            }
        });
    }

    private function parseConditions(?string $conditionsJson): ?array
    {
        if (empty($conditionsJson)) {
            return null;
        }

        $conditions = json_decode($conditionsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON format for conditions');
        }

        // Validate conditions structure
        if (!is_array($conditions)) {
            throw new \RuntimeException('Conditions must be a JSON object/array');
        }

        return $conditions;
    }
}
