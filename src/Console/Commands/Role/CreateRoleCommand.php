<?php

namespace Marufsharia\Hyro\Console\Commands\Role;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;
use Marufsharia\Hyro\Models\Role;

class CreateRoleCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:role:create
                            {slug : Role slug (unique identifier)}
                            {name : Role display name}
                            {--description= : Role description}
                            {--protected : Mark as protected role (cannot be deleted)}
                            {--system : Mark as system role}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'Create a new role';

    protected function executeCommand(): void
    {
        $slug = $this->argument('slug');
        $name = $this->argument('name');
        $description = $this->option('description');
        $protected = $this->option('protected');
        $system = $this->option('system');

        $this->validateRoleIdentifier($slug);

        // Check if role already exists
        $existingRole = Role::where('slug', $slug)->first();
        if ($existingRole) {
            throw new \RuntimeException("Role '{$slug}' already exists");
        }

        // Show operation details
        $details = [
            ['Slug', $slug],
            ['Name', $name],
            ['Description', $description ?: 'N/A'],
            ['Protected', $protected ? 'Yes' : 'No'],
            ['System', $system ? 'Yes' : 'No'],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ];

        if (!$this->confirmOperation('Create new role', $details)) {
            return;
        }

        // Create role
        $this->executeInTransaction(function () use ($slug, $name, $description, $protected, $system) {
            $roleData = [
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'is_protected' => $protected,
                'is_system' => $system,
            ];

            if (!$this->dryRun) {
                $role = Role::create($roleData);
                $this->info("✅ Role '{$role->slug}' created successfully");

                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('role_created', $role, null, $roleData, [
                        'tags' => ['cli', 'role', 'create'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would create role '{$slug}'");
            }
        });
    }
}
