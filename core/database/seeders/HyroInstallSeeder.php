<?php

namespace Marufsharia\Hyro\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;

class HyroInstallSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | 1. Create Roles
            |--------------------------------------------------------------------------
            */

            $roles = [
                'super-admin' => [
                    'name' => 'Super Administrator',
                    'level' => 100,
                ],
                'admin' => [
                    'name' => 'Administrator',
                    'level' => 80,
                ],
                'editor' => [
                    'name' => 'Editor',
                    'level' => 50,
                ],
                'user' => [
                    'name' => 'User',
                    'level' => 10,
                ],
            ];

            foreach ($roles as $slug => $data) {
                Role::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $data['name'],
                        'description' => $data['name'].' role',
                        'level' => $data['level'],
                        'is_protected' => true,
                        'is_default' => $slug === 'user',
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. Create Core Privileges
            |--------------------------------------------------------------------------
            */

            $privileges = [

                // Hyro Admin Panel
                ['slug' => 'hyro.access', 'name' => 'Access Hyro Admin', 'group' => 'hyro'],
                ['slug' => 'hyro.sidebar.manage', 'name' => 'Manage Sidebar', 'group' => 'hyro'],
                ['slug' => 'hyro.cli.access', 'name' => 'Access CLI Tools', 'group' => 'hyro'],

                // Roles
                ['slug' => 'roles.view', 'name' => 'View Roles', 'group' => 'roles'],
                ['slug' => 'roles.create', 'name' => 'Create Roles', 'group' => 'roles'],
                ['slug' => 'roles.edit', 'name' => 'Edit Roles', 'group' => 'roles'],
                ['slug' => 'roles.delete', 'name' => 'Delete Roles', 'group' => 'roles'],

                // Users
                ['slug' => 'users.view', 'name' => 'View Users', 'group' => 'users'],
                ['slug' => 'users.create', 'name' => 'Create Users', 'group' => 'users'],
                ['slug' => 'users.edit', 'name' => 'Edit Users', 'group' => 'users'],
                ['slug' => 'users.delete', 'name' => 'Delete Users', 'group' => 'users'],

                // Content
                ['slug' => 'content.manage', 'name' => 'Manage Content', 'group' => 'content'],
            ];

            foreach ($privileges as $privilege) {
                Privilege::firstOrCreate(
                    ['slug' => $privilege['slug']],
                    [
                        'name' => $privilege['name'],
                        'description' => $privilege['name'],
                        'group' => $privilege['group'],
                    ]
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Assign Privileges by Role
            |--------------------------------------------------------------------------
            */

            $allPrivileges = Privilege::pluck('id')->toArray();

            $superAdmin = Role::where('slug', 'super-admin')->first();
            $admin = Role::where('slug', 'admin')->first();
            $editor = Role::where('slug', 'editor')->first();
            $user = Role::where('slug', 'user')->first();

            // Super Admin → All
            $superAdmin?->privileges()->sync($allPrivileges);

            // Admin → Most except dangerous
            $adminPrivileges = Privilege::whereNotIn('slug', [
                'roles.delete',
                'users.delete',
            ])->pluck('id')->toArray();

            $admin?->privileges()->sync($adminPrivileges);

            // Editor → Content + View
            $editorPrivileges = Privilege::whereIn('slug', [
                'hyro.access',
                'content.manage',
                'users.view',
            ])->pluck('id')->toArray();

            $editor?->privileges()->sync($editorPrivileges);

            // User → Minimal
            $userPrivileges = Privilege::whereIn('slug', [
                'hyro.access',
            ])->pluck('id')->toArray();

            $user?->privileges()->sync($userPrivileges);

        });
    }
}