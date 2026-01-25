<?php

namespace Marufsharia\Hyro\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HyroSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Hyro default data...');

        // Seed roles if table is empty
        if (DB::table(config('hyro.database.tables.roles', 'hyro_roles'))->count() === 0) {
            $this->seedRoles();
        }

        // Seed privileges if table is empty
        if (DB::table(config('hyro.database.tables.privileges', 'hyro_privileges'))->count() === 0) {
            $this->seedPrivileges();
        }

        // Assign privileges to super-admin role
        $this->assignPrivilegesToSuperAdmin();

        $this->command->info('Hyro seeding completed!');
    }

    protected function seedRoles(): void
    {
        $roles = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-admin',
                'description' => 'Full system access with no restrictions',
                'is_protected' => true,
                'is_default' => false,
                'level' => 100,
                'metadata' => json_encode(['can_manage_roles' => true, 'can_manage_users' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'System administrator with management privileges',
                'is_protected' => true,
                'is_default' => false,
                'level' => 90,
                'metadata' => json_encode(['can_manage_users' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Content moderator with limited administrative access',
                'is_protected' => false,
                'is_default' => false,
                'level' => 50,
                'metadata' => json_encode(['can_moderate_content' => true]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular authenticated user',
                'is_protected' => false,
                'is_default' => true,
                'level' => 10,
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table(config('hyro.database.tables.roles', 'hyro_roles'))->insert($roles);
    }

    protected function seedPrivileges(): void
    {
        $privileges = [];
        $privilegeData = $this->getDefaultPrivileges();

        foreach ($privilegeData as $data) {
            $privileges[] = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'group' => $data['group'],
                'is_dangerous' => $data['dangerous'] ?? false,
                'metadata' => json_encode($data['metadata'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table(config('hyro.database.tables.privileges', 'hyro_privileges'))->insert($privileges);
    }

    protected function getDefaultPrivileges(): array
    {
        return [
            // User Management
            ['slug' => 'users.view', 'name' => 'View Users', 'description' => 'Can view user profiles', 'group' => 'users'],
            ['slug' => 'users.create', 'name' => 'Create Users', 'description' => 'Can create new users', 'group' => 'users'],
            ['slug' => 'users.edit', 'name' => 'Edit Users', 'description' => 'Can edit existing users', 'group' => 'users'],
            ['slug' => 'users.delete', 'name' => 'Delete Users', 'description' => 'Can delete users', 'group' => 'users', 'dangerous' => true],
            ['slug' => 'users.suspend', 'name' => 'Suspend Users', 'description' => 'Can suspend user accounts', 'group' => 'users'],
            ['slug' => 'users.impersonate', 'name' => 'Impersonate Users', 'description' => 'Can impersonate other users', 'group' => 'users', 'dangerous' => true],
            ['slug' => 'users.export', 'name' => 'Export Users', 'description' => 'Can export user data', 'group' => 'users'],

            // Role Management
            ['slug' => 'roles.view', 'name' => 'View Roles', 'description' => 'Can view roles', 'group' => 'roles'],
            ['slug' => 'roles.create', 'name' => 'Create Roles', 'description' => 'Can create new roles', 'group' => 'roles'],
            ['slug' => 'roles.edit', 'name' => 'Edit Roles', 'description' => 'Can edit existing roles', 'group' => 'roles'],
            ['slug' => 'roles.delete', 'name' => 'Delete Roles', 'description' => 'Can delete roles', 'group' => 'roles', 'dangerous' => true],
            ['slug' => 'roles.assign', 'name' => 'Assign Roles', 'description' => 'Can assign roles to users', 'group' => 'roles'],

            // Privilege Management
            ['slug' => 'privileges.view', 'name' => 'View Privileges', 'description' => 'Can view privileges', 'group' => 'privileges'],
            ['slug' => 'privileges.assign', 'name' => 'Assign Privileges', 'description' => 'Can assign privileges to roles', 'group' => 'privileges'],

            // System Management
            ['slug' => 'system.settings.view', 'name' => 'View Settings', 'description' => 'Can view system settings', 'group' => 'system'],
            ['slug' => 'system.settings.edit', 'name' => 'Edit Settings', 'description' => 'Can edit system settings', 'group' => 'system', 'dangerous' => true],
            ['slug' => 'system.backup', 'name' => 'Create Backups', 'description' => 'Can create system backups', 'group' => 'system', 'dangerous' => true],
            ['slug' => 'system.restore', 'name' => 'Restore Backups', 'description' => 'Can restore from backups', 'group' => 'system', 'dangerous' => true],
            ['slug' => 'system.maintenance', 'name' => 'Maintenance Mode', 'description' => 'Can toggle maintenance mode', 'group' => 'system', 'dangerous' => true],

            // Audit & Logs
            ['slug' => 'audit.logs.view', 'name' => 'View Audit Logs', 'description' => 'Can view audit logs', 'group' => 'audit'],
            ['slug' => 'audit.logs.clear', 'name' => 'Clear Audit Logs', 'description' => 'Can clear audit logs', 'group' => 'audit', 'dangerous' => true],
            ['slug' => 'audit.logs.export', 'name' => 'Export Audit Logs', 'description' => 'Can export audit logs', 'group' => 'audit'],

            // API Management
            ['slug' => 'api.tokens.create', 'name' => 'Create API Tokens', 'description' => 'Can create API tokens', 'group' => 'api'],
            ['slug' => 'api.tokens.revoke', 'name' => 'Revoke API Tokens', 'description' => 'Can revoke API tokens', 'group' => 'api'],
            ['slug' => 'api.tokens.view', 'name' => 'View API Tokens', 'description' => 'Can view API tokens', 'group' => 'api'],

            // Content Management
            ['slug' => 'content.view', 'name' => 'View Content', 'description' => 'Can view all content', 'group' => 'content'],
            ['slug' => 'content.create', 'name' => 'Create Content', 'description' => 'Can create content', 'group' => 'content'],
            ['slug' => 'content.edit', 'name' => 'Edit Content', 'description' => 'Can edit content', 'group' => 'content'],
            ['slug' => 'content.delete', 'name' => 'Delete Content', 'description' => 'Can delete content', 'group' => 'content'],
            ['slug' => 'content.publish', 'name' => 'Publish Content', 'description' => 'Can publish content', 'group' => 'content'],

            // Financial (if applicable)
            ['slug' => 'financial.view', 'name' => 'View Financial Data', 'description' => 'Can view financial data', 'group' => 'financial', 'dangerous' => true],
            ['slug' => 'financial.process', 'name' => 'Process Payments', 'description' => 'Can process payments', 'group' => 'financial', 'dangerous' => true],

            // Notifications
            ['slug' => 'notifications.send', 'name' => 'Send Notifications', 'description' => 'Can send system notifications', 'group' => 'notifications'],
            ['slug' => 'notifications.manage', 'name' => 'Manage Notifications', 'description' => 'Can manage notification templates', 'group' => 'notifications'],
        ];
    }

    protected function assignPrivilegesToSuperAdmin(): void
    {
        $superAdminRole = DB::table(config('hyro.database.tables.roles', 'hyro_roles'))
            ->where('slug', 'super-admin')
            ->first();

        if (!$superAdminRole) {
            return;
        }

        $privileges = DB::table(config('hyro.database.tables.privileges', 'hyro_privileges'))->get();

        $assignments = [];
        foreach ($privileges as $privilege) {
            $assignments[] = [
                'role_id' => $superAdminRole->id,
                'privilege_id' => $privilege->id,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table(config('hyro.database.tables.privilege_role', 'hyro_privilege_role'))->insert($assignments);
    }
}
