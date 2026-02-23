# Hyro Installation Test Guide

This guide provides step-by-step instructions to test the Hyro package installation.

## Test Environment Setup

### Prerequisites

```bash
# Verify requirements
php -v          # Should be 8.2 or higher
composer -V     # Should be 2.0 or higher
node -v         # Should be 18 or higher
npm -v          # Latest version
```

## Test 1: Fresh Laravel Installation

```bash
# Create new Laravel project
composer create-project laravel/laravel hyro-test
cd hyro-test

# Configure database (use SQLite for quick testing)
touch database/database.sqlite
```

Edit `.env`:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

## Test 2: Install Hyro Package

### From Packagist (Production)

```bash
composer require marufsharia/hyro
```

### From GitHub (Development)

```bash
composer require marufsharia/hyro:dev-main
```

### From Local Path (Testing)

Add to `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/hyro/package"
        }
    ],
    "require": {
        "marufsharia/hyro": "@dev"
    }
}
```

Then run:
```bash
composer update marufsharia/hyro
```

## Test 3: Publish Configuration

```bash
# Publish all Hyro assets
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"

# Or publish individually
php artisan vendor:publish --tag=hyro-config
php artisan vendor:publish --tag=hyro-migrations
php artisan vendor:publish --tag=hyro-assets
php artisan vendor:publish --tag=hyro-views
```

## Test 4: Configure Environment

Add to `.env`:
```env
# Core Settings
HYRO_ENABLED=true
HYRO_ADMIN_PREFIX=admin/hyro

# Features
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_CLI_ENABLED=true

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit
HYRO_AUDIT_ENABLED=true
```

## Test 5: Run Migrations

```bash
php artisan migrate
```

Expected output:
```
Migration table created successfully.
Migrating: 2024_01_01_000000_create_hyro_roles_table
Migrated:  2024_01_01_000000_create_hyro_roles_table
Migrating: 2024_01_01_000001_create_hyro_privileges_table
Migrated:  2024_01_01_000001_create_hyro_privileges_table
...
```

## Test 6: Add Trait to User Model

Edit `app/Models/User.php`:
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Marufsharia\Hyro\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
    
    // ... rest of your User model
}
```

## Test 7: Create Admin User

```bash
php artisan hyro:user:create --admin
```

Follow the prompts:
- Name: Test Admin
- Email: admin@test.com
- Password: password123

Expected output:
```
✓ User created successfully!
✓ Super Admin role assigned
✓ User ID: 1
```

## Test 8: Verify CLI Commands

```bash
# List all Hyro commands
php artisan list | grep hyro

# Test user commands
php artisan hyro:user:list

# Test role commands
php artisan hyro:role:list

# Test privilege commands
php artisan hyro:privilege:list
```

## Test 9: Start Development Server

```bash
php artisan serve
```

## Test 10: Access Admin Panel

1. Open browser: http://localhost:8000/admin/hyro
2. Login with admin credentials
3. Verify dashboard loads
4. Check sidebar menu items
5. Test navigation

## Test 11: Test CRUD Generator

```bash
# Generate a simple CRUD
php artisan hyro:make-crud TestModel \
    --fields="name:string,description:text" \
    --migration

# Verify files created
ls app/Livewire/Admin/
ls resources/views/livewire/admin/
ls database/migrations/
```

## Test 12: Test API Endpoints

```bash
# Get API token
curl -X POST http://localhost:8000/api/hyro/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password123"}'

# Use token to access protected endpoint
curl -X GET http://localhost:8000/api/hyro/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Test 13: Test Authorization

Create test route in `routes/web.php`:
```php
use Illuminate\Support\Facades\Route;

Route::middleware(['hyro.auth'])->group(function () {
    Route::get('/test-auth', function () {
        return 'Authenticated!';
    });
});

Route::middleware(['hyro.role:super-admin'])->group(function () {
    Route::get('/test-admin', function () {
        return 'Admin access!';
    });
});
```

Test in browser:
- http://localhost:8000/test-auth (should require login)
- http://localhost:8000/test-admin (should require admin role)

## Test 14: Test Blade Directives

Create test view `resources/views/test.blade.php`:
```blade
@extends('hyro::layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Hyro Test Page</h1>
    
    @hasrole('super-admin')
        <div class="bg-green-100 p-4 rounded mb-4">
            ✓ You are a Super Admin
        </div>
    @endhasrole
    
    @hasprivilege('users.create')
        <div class="bg-blue-100 p-4 rounded mb-4">
            ✓ You can create users
        </div>
    @endhasprivilege
    
    <div class="bg-gray-100 p-4 rounded">
        <p>User: {{ auth()->user()->name }}</p>
        <p>Email: {{ auth()->user()->email }}</p>
        <p>Roles: {{ auth()->user()->roles->pluck('name')->join(', ') }}</p>
    </div>
</div>
@endsection
```

Add route:
```php
Route::get('/test', function () {
    return view('test');
})->middleware('hyro.auth');
```

## Test 15: Test Plugin System

```bash
# Create test plugin
php artisan hyro:plugin:make TestPlugin

# List plugins
php artisan hyro:plugin:list

# Activate plugin
php artisan hyro:plugin:activate TestPlugin
```

## Verification Checklist

### Installation
- [ ] Package installed via Composer
- [ ] No dependency conflicts
- [ ] Configuration published
- [ ] Migrations published
- [ ] Assets published

### Database
- [ ] Migrations run successfully
- [ ] All tables created
- [ ] No migration errors
- [ ] Database seeded (if applicable)

### User Management
- [ ] Admin user created
- [ ] User can login
- [ ] Roles assigned correctly
- [ ] Privileges working

### Admin Panel
- [ ] Admin panel accessible
- [ ] Dashboard loads
- [ ] Sidebar navigation works
- [ ] All menu items accessible
- [ ] No JavaScript errors

### CRUD Generator
- [ ] CRUD command works
- [ ] Files generated correctly
- [ ] Routes registered
- [ ] Views render properly
- [ ] CRUD operations work

### API
- [ ] API endpoints accessible
- [ ] Authentication works
- [ ] Token generation works
- [ ] Protected routes secured
- [ ] API responses correct

### Authorization
- [ ] Role checks work
- [ ] Privilege checks work
- [ ] Middleware protection works
- [ ] Blade directives work
- [ ] Gates registered

### CLI Commands
- [ ] All commands listed
- [ ] User commands work
- [ ] Role commands work
- [ ] Privilege commands work
- [ ] Plugin commands work

### Assets
- [ ] CSS loads correctly
- [ ] JavaScript loads correctly
- [ ] Images display
- [ ] Icons render
- [ ] No 404 errors

### Performance
- [ ] Page load times acceptable
- [ ] No N+1 queries
- [ ] Cache working
- [ ] No memory issues

## Common Issues and Solutions

### Issue: "Class not found"

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
```

### Issue: "Migration already exists"

**Solution:**
```bash
# Remove duplicate migrations
rm database/migrations/*_create_hyro_*

# Re-publish
php artisan vendor:publish --tag=hyro-migrations --force
```

### Issue: "Assets not loading"

**Solution:**
```bash
# Re-publish assets
php artisan vendor:publish --tag=hyro-assets --force

# Clear cache
php artisan cache:clear
php artisan view:clear

# Rebuild assets
npm install
npm run build
```

### Issue: "Route not found"

**Solution:**
```bash
# Clear route cache
php artisan route:clear

# List routes
php artisan route:list | grep hyro
```

## Performance Testing

```bash
# Test with 100 users
php artisan tinker
>>> User::factory()->count(100)->create();

# Test with 1000 roles
>>> \Marufsharia\Hyro\Models\Role::factory()->count(1000)->create();

# Benchmark authorization
>>> $user = User::first();
>>> $start = microtime(true);
>>> $user->hasRole('admin');
>>> echo (microtime(true) - $start) * 1000 . ' ms';
```

## Load Testing

```bash
# Install Apache Bench
# Windows: Download from Apache website
# Linux: sudo apt-get install apache2-utils
# Mac: brew install ab

# Test admin panel
ab -n 1000 -c 10 http://localhost:8000/admin/hyro

# Test API endpoint
ab -n 1000 -c 10 -H "Authorization: Bearer TOKEN" http://localhost:8000/api/hyro/users
```

## Final Verification

```bash
# Run all tests
php artisan test

# Check for errors in logs
tail -f storage/logs/laravel.log

# Verify package version
composer show marufsharia/hyro
```

## Success Criteria

✅ All installation steps completed without errors
✅ Admin panel accessible and functional
✅ CRUD generator creates working components
✅ API endpoints respond correctly
✅ Authorization system works as expected
✅ No console errors in browser
✅ No errors in Laravel logs
✅ Performance is acceptable
✅ All tests pass

---

**If all tests pass, the installation is successful!** 🎉

For issues or questions:
- GitHub: https://github.com/marufsharia/hyro/issues
- Email: marufsharia@gmail.com
