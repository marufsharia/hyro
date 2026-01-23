<?php

namespace MarufSharia\Hyro\Database\Seeders;

use MarufSharia\Hyro\Models\Privilege;
use MarufSharia\Hyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HyroAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // Create core admin privileges
            $privileges = [
                [
                    'name' => 'Access Hyro Admin',
                    'slug' => 'access-hyro-admin',
                    'category' => 'Admin',
                    'description' => 'Allows access to the Hyro admin panel',
                ],
                [
                    'name' => 'View Roles',
                    'slug' => 'view-roles',
                    'category' => 'Roles',
                    'description' => 'Allows viewing roles',
                ],
                [
                    'name' => 'Create Roles',
                    'slug' => 'create-roles',
                    'category' => 'Roles',
                    'description' => 'Allows creating new roles',
                ],
                [
                    'name' => 'Edit Roles',
                    'slug' => 'edit-roles',
                    'category' => 'Roles',
                    'description' => 'Allows editing existing roles',
                ],
                [
                    'name' => 'Delete Roles',
                    'slug' => 'delete-roles',
                    'category' => 'Roles',
                    'description' => 'Allows deleting roles',
                ],
                [
                    'name' => 'View Privileges',
                    'slug' => 'view-privileges',
                    'category' => 'Privileges',
                    'description' => 'Allows viewing privileges',
                ],
                [
                    'name' => 'Create Privileges',
                    'slug' => 'create-privileges',
                    'category' => 'Privileges',
                    'description' => 'Allows creating new privileges',
                ],
                [
                    'name' => 'Edit Privileges',
                    'slug' => 'edit-privileges',
                    'category' => 'Privileges',
                    'description' => 'Allows editing existing privileges',
                ],
                [
                    'name' => 'Delete Privileges',
                    'slug' => 'delete-privileges',
                    'category' => 'Privileges',
                    'description' => 'Allows deleting privileges',
                ],
            ];

            foreach ($privileges as $privilegeData) {
                Privilege::firstOrCreate(
                    ['slug' => $privilegeData['slug']],
                    $privilegeData
                );
            }

            // Create super admin role
            $superAdminRole = Role::firstOrCreate(
                ['slug' => config('hyro.super_admin_role', 'super-admin')],
                [
                    'name' => 'Super Administrator',
                    'description' => 'Has full access to all system features',
                ]
            );

            // Assign all privileges to super admin
            $allPrivileges = Privilege::pluck('id')->toArray();
            $superAdminRole->privileges()->sync($allPrivileges);
        });
    }
}
