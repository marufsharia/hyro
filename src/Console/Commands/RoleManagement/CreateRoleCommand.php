<?php

namespace MarufSharia\Hyro\Console\Commands\RoleManagement;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateRoleCommand extends Command
{
    protected $signature = 'hyro:create-role
                            {name : Role name}
                            {--slug= : Role slug (auto-generated if not provided)}
                            {--description= : Role description}
                            {--protected : Make role protected (cannot be deleted)}
                            {--default : Make role default (assigned to new users)}
                            {--level= : Role level (higher = more privileges)}
                            {--privileges=* : Comma-separated privileges to assign}
                            {--no-interaction : Run non-interactively}';

    protected $description = 'Create a new Hyro role';

    public function handle(): int
    {
        $this->info('🎭 Creating new role...');

        $name = $this->argument('name');
        $slug = $this->option('slug') ?: Str::slug($name);
        $description = $this->option('description') ?: "{$name} role";
        $isProtected = $this->option('protected');
        $isDefault = $this->option('default');
        $level = (int) $this->option('level') ?: 50;
        $privileges = $this->option('privileges') ?: [];

        // Flatten privileges if provided as comma-separated string
        if (!empty($privileges) && is_string($privileges[0])) {
            $privileges = explode(',', $privileges[0]);
            $privileges = array_map('trim', $privileges);
        }

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'slug' => $slug,
            'level' => $level,
        ], [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:'.config('hyro.database.tables.roles', 'hyro_roles').',slug',
            'level' => 'integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        // Confirm creation
        if (!$this->option('no-interaction')) {
            $this->table(['Field', 'Value'], [
                ['Name', $name],
                ['Slug', $slug],
                ['Description', $description],
                ['Protected', $isProtected ? 'Yes' : 'No'],
                ['Default', $isDefault ? 'Yes' : 'No'],
                ['Level', $level],
                ['Privileges', empty($privileges) ? 'None' : implode(', ', $privileges)],
            ]);

            if (!$this->confirm('Create this role?')) {
                $this->info('Role creation cancelled.');
                return Command::SUCCESS;
            }
        }

        try {
            DB::beginTransaction();

            // Create role
            $roleId = DB::table(config('hyro.database.tables.roles', 'hyro_roles'))->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'is_protected' => $isProtected,
                'is_default' => $isDefault,
                'level' => $level,
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign privileges if provided
            if (!empty($privileges)) {
                $assignedPrivileges = [];
                $failedPrivileges = [];

                foreach ($privileges as $privilegeSlug) {
                    $privilege = DB::table(config('hyro.database.tables.privileges', 'hyro_privileges'))
                        ->where('slug', $privilegeSlug)
                        ->first();

                    if ($privilege) {
                        DB::table(config('hyro.database.tables.privilege_role', 'hyro_privilege_role'))->insert([
                            'role_id' => $roleId,
                            'privilege_id' => $privilege->id,
                            'granted_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $assignedPrivileges[] = $privilegeSlug;
                    } else {
                        $failedPrivileges[] = $privilegeSlug;
                    }
                }

                if (!empty($failedPrivileges)) {
                    $this->warn("⚠️  Some privileges not found: " . implode(', ', $failedPrivileges));
                }
            }

            DB::commit();

            $this->info("✅ Role created successfully! ID: {$roleId}");

            if (!empty($assignedPrivileges)) {
                $this->info("🔑 Assigned privileges: " . implode(', ', $assignedPrivileges));
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Failed to create role: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
