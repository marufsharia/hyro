<?php

namespace Marufsharia\Hyro\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Marufsharia\Hyro\Models\Role;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hyro:user:create
                            {--name= : The name of the user}
                            {--email= : The email of the user}
                            {--password= : The password for the user}
                            {--admin : Create user as admin}
                            {--role= : Assign specific role to user}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating new user...');
        $this->newLine();

        // Get user details
        $name = $this->option('name') ?? $this->ask('Name');
        $email = $this->option('email') ?? $this->ask('Email');
        $password = $this->option('password') ?? $this->secret('Password');
        
        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        try {
            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->info('✓ User created successfully!');
            $this->newLine();

            // Assign role
            if ($this->option('admin')) {
                $role = Role::where('slug', 'admin')->first() 
                    ?? Role::where('slug', 'super-admin')->first();
                
                if ($role) {
                    $user->assignRole($role->slug);
                    $this->info('✓ Admin role assigned');
                } else {
                    $this->warn('⚠ Admin role not found. Please assign role manually.');
                }
            } elseif ($roleSlug = $this->option('role')) {
                $role = Role::where('slug', $roleSlug)->first();
                
                if ($role) {
                    $user->assignRole($role->slug);
                    $this->info("✓ Role '{$role->name}' assigned");
                } else {
                    $this->error("✗ Role '{$roleSlug}' not found");
                }
            } else {
                // Ask if user wants to assign a role
                if ($this->confirm('Would you like to assign a role?', true)) {
                    $roles = Role::all();
                    
                    if ($roles->isEmpty()) {
                        $this->warn('No roles available');
                    } else {
                        $this->table(
                            ['ID', 'Name', 'Slug'],
                            $roles->map(function ($role) {
                                return [$role->id, $role->name, $role->slug];
                            })->toArray()
                        );
                        
                        $roleSlug = $this->ask('Enter role slug');
                        $role = Role::where('slug', $roleSlug)->first();
                        
                        if ($role) {
                            $user->assignRole($role->slug);
                            $this->info("✓ Role '{$role->name}' assigned");
                        } else {
                            $this->error("✗ Role '{$roleSlug}' not found");
                        }
                    }
                }
            }

            // Display user info
            $this->newLine();
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Roles', $user->roles->pluck('name')->implode(', ') ?: 'None'],
                    ['Created', $user->created_at->format('Y-m-d H:i:s')],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Failed to create user: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
