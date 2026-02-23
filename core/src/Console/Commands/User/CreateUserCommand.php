<?php

namespace Marufsharia\Hyro\Core\Console\Commands\User;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Marufsharia\Hyro\Core\Models\Role;

class CreateUserCommand extends Command
{
    protected $signature = 'hyro:user:create 
                            {--admin : Create user with super-admin role}
                            {--name= : User name}
                            {--email= : User email}
                            {--password= : User password}';

    protected $description = 'Create a new user';

    public function handle()
    {
        $this->info('Creating new user...');
        $this->newLine();

        // Get user model class
        $userModel = config('hyro.database.models.users', \App\Models\User::class);

        // Get user details
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->option('password') ?: $this->secret('Password');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  • ' . $error);
            }
            return 1;
        }

        // Create user
        try {
            $user = $userModel::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->info('✓ User created successfully!');
            $this->newLine();

            // Assign super-admin role if --admin flag is set
            if ($this->option('admin')) {
                $superAdminRole = Role::where('slug', 'super-admin')->first();
                
                if (!$superAdminRole) {
                    // Create super-admin role if it doesn't exist
                    $superAdminRole = Role::create([
                        'name' => 'Super Admin',
                        'slug' => 'super-admin',
                        'description' => 'Super administrator with full access',
                        'is_protected' => true,
                    ]);
                }

                $user->assignRole($superAdminRole);
                $this->info('✓ Super Admin role assigned');
                $this->newLine();
            }

            // Display user info
            $this->table(
                ['Field', 'Value'],
                [
                    ['User ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Roles', $user->roles->pluck('name')->join(', ') ?: 'None'],
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }
    }
}
