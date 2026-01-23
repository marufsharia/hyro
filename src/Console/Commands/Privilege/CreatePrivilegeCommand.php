<?php

namespace Marufsharia\Hyro\Console\Commands\Privilege;

use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Console\Commands\BaseCommand;
use Marufsharia\Hyro\Console\Concerns\Confirmable;
use Marufsharia\Hyro\Console\Concerns\Validatable;
use Marufsharia\Hyro\Models\AuditLog;
use Marufsharia\Hyro\Models\Privilege;

class CreatePrivilegeCommand extends BaseCommand
{
    use Confirmable, Validatable;

    protected $signature = 'hyro:privilege:create
                            {slug : Privilege slug (dot notation, e.g., users.create)}
                            {--name= : Display name (defaults to slug)}
                            {--description= : Privilege description}
                            {--category= : Category for organization}
                            {--priority=50 : Priority for resolution order (1-100)}
                            {--protected : Mark as protected privilege}
                            {--dry-run : Preview changes}
                            {--force : Skip confirmation}';

    protected $description = 'Create a new privilege';

    protected function executeCommand(): void
    {
        $slug = $this->argument('slug');
        $name = $this->option('name') ?? $this->generateNameFromSlug($slug);
        $description = $this->option('description');
        $category = $this->option('category');
        $priority = (int) $this->option('priority');
        $protected = $this->option('protected');

        $this->validatePrivilegeIdentifier($slug);

        // Validate priority
        if ($priority < 1 || $priority > 100) {
            throw new \RuntimeException('Priority must be between 1 and 100');
        }

        // Check if privilege already exists
        $existingPrivilege = Privilege::where('slug', $slug)->first();
        if ($existingPrivilege) {
            throw new \RuntimeException("Privilege '{$slug}' already exists");
        }

        // Determine if it's a wildcard
        $isWildcard = str_contains($slug, '*');
        $wildcardPattern = $isWildcard ? $slug : null;

        // Show operation details
        $details = [
            ['Slug', $slug],
            ['Name', $name],
            ['Description', $description ?: 'N/A'],
            ['Category', $category ?: 'Uncategorized'],
            ['Priority', $priority],
            ['Wildcard', $isWildcard ? 'Yes' : 'No'],
            ['Protected', $protected ? 'Yes' : 'No'],
            ['Mode', $this->dryRun ? 'Dry Run' : 'Live'],
        ];

        if (!$this->confirmOperation('Create new privilege', $details)) {
            return;
        }

        // Create privilege
        $this->executeInTransaction(function () use ($slug, $name, $description, $category, $priority, $protected, $isWildcard, $wildcardPattern) {
            $privilegeData = [
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'category' => $category,
                'priority' => $priority,
                'is_protected' => $protected,
                'is_wildcard' => $isWildcard,
                'wildcard_pattern' => $wildcardPattern,
            ];

            if (!$this->dryRun) {
                $privilege = Privilege::create($privilegeData);
                $this->info("✅ Privilege '{$privilege->slug}' created successfully");

                if (Config::get('hyro.auditing.enabled', true)) {
                    AuditLog::log('privilege_created', $privilege, null, $privilegeData, [
                        'tags' => ['cli', 'privilege', 'create'],
                    ]);
                }
            } else {
                $this->info("🔍 [Dry Run] Would create privilege '{$slug}'");
            }
        });
    }

    private function generateNameFromSlug(string $slug): string
    {
        return str($slug)
            ->replace('.', ' ')
            ->replace('*', ' (any)')
            ->title()
            ->toString();
    }
}
