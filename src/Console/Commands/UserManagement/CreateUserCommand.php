<?php

namespace Marufsharia\Hyro\Console\Commands\UserManagement;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class CreateUserCommand extends Command
{
    protected $signature = 'hyro:create-users
                            {--name= : User name}
                            {--email= : User email}
                            {--password= : User password}
                            {--admin : Make users an admin}';

    protected $description = 'Create a new Hyro users';

    public function handle(): int
    {
        $this->info('👤 Creating new Hyro users...');
        $this->line('');

        // Get password minimum length from config
        $minPasswordLength = config('hyro.auth.password_min_length', 8);

        // Collect users information with retry logic
        $name = $this->getValidName();
        $email = $this->getValidEmail();
        $password = $this->getValidPassword($minPasswordLength);
        $isAdmin = $this->option('admin');

        // Confirm creation
        $this->line('');
        $this->info('📝 User Details:');
        $this->table(['Field', 'Value'], [
            ['Name', $name],
            ['Email', $email],
            ['Password', '********'],
            ['Admin', $isAdmin ? 'Yes' : 'No'],
        ]);

        if (!$this->option('no-interaction') && !$this->confirm('Create this users?')) {
            $this->info('User creation cancelled.');
            return Command::SUCCESS;
        }

        // Create users
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $this->line('');
            $this->info('✅ User created successfully!');
            $this->line("📝 ID: {$user->id}");
            $this->line("👤 Name: {$user->name}");
            $this->line("📧 Email: {$user->email}");

            if ($isAdmin) {
                // In Phase 3, we'll assign admin role here
                $this->info('🔐 User marked as admin (role assignment will be implemented in Phase 3)');
            }

            $this->line('');
            $this->info('💡 Tip: Run `php artisan hyro:list-users` to see all users.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Failed to create users: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    protected function getValidName(): string
    {
        $name = $this->option('name');

        while (true) {
            if (!$name) {
                $name = $this->ask('What is the users\'s name?');
            }

            // Validate name
            if (empty(trim($name))) {
                $this->error('❌ Name cannot be empty.');
                $name = null;
                continue;
            }

            if (strlen(trim($name)) > 255) {
                $this->error('❌ Name must be less than 255 characters.');
                $name = null;
                continue;
            }

            // Name is valid
            return trim($name);
        }
    }

    protected function getValidEmail(): string
    {
        $email = $this->option('email');

        while (true) {
            if (!$email) {
                $email = $this->ask('What is the users\'s email?');
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('❌ Please enter a valid email address.');
                $email = null;
                continue;
            }

            // Check if email already exists
            if (User::where('email', $email)->exists()) {
                $this->error("❌ A users with email '{$email}' already exists.");
                $email = null;
                continue;
            }

            // Email is valid
            return $email;
        }
    }

    protected function getValidPassword(int $minLength): string
    {
        $password = $this->option('password');

        while (true) {
            if (!$password) {
                $password = $this->secret("Enter password (minimum {$minLength} characters):");
            }

            // Validate password
            if (strlen($password) < $minLength) {
                $this->error("❌ Password must be at least {$minLength} characters. You entered " . strlen($password) . " characters.");

                // If password was provided via option and fails, we can't retry in non-interactive mode
                if ($this->option('password') && $this->option('no-interaction')) {
                    $this->error('Cannot retry in non-interactive mode. Please use a longer password.');
                    throw new \RuntimeException('Password validation failed');
                }

                // Reset and retry
                $password = null;
                continue;
            }

            // Check password complexity if configured
            if (config('hyro.auth.password_requires_mixed_case', false)) {
                if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password)) {
                    $this->error('❌ Password must contain both uppercase and lowercase letters.');
                    $password = null;
                    continue;
                }
            }

            if (config('hyro.auth.password_requires_numbers', false)) {
                if (!preg_match('/[0-9]/', $password)) {
                    $this->error('❌ Password must contain at least one number.');
                    $password = null;
                    continue;
                }
            }

            if (config('hyro.auth.password_requires_symbols', false)) {
                if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
                    $this->error('❌ Password must contain at least one special character.');
                    $password = null;
                    continue;
                }
            }

            // Confirm password (except in non-interactive mode)
            if (!$this->option('no-interaction') && !$this->option('password')) {
                $confirm = $this->secret('Confirm password:');

                if ($password !== $confirm) {
                    $this->error('❌ Passwords do not match. Please try again.');
                    $password = null;
                    continue;
                }
            }

            // Password is valid
            return $password;
        }
    }
}
