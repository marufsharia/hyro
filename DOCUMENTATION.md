# Hyro Documentation

Complete documentation for the Hyro enterprise authentication and authorization system.

## 📚 Table of Contents

### Getting Started
- [Installation Guide](#installation-guide)
- [Quick Start](#quick-start)
- [Configuration](#configuration)

### Core Concepts
- [Modular Architecture](#modular-architecture)
- [Authorization System](#authorization-system)
- [Roles & Privileges](#roles--privileges)

### Features
- [CRUD Generator](#crud-generator)
- [Plugin System](#plugin-system)
- [API Documentation](#api-documentation)
- [Profile Management](#profile-management)

### Advanced Topics
- [Custom Packages](#custom-packages)
- [Extending Hyro](#extending-hyro)
- [Security Best Practices](#security-best-practices)

---

## Installation Guide

### Requirements

- PHP 8.2 or higher
- Laravel 12.0 or higher
- Composer 2.0 or higher
- MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+

### Installation

```bash
composer require marufsharia/hyro
```

### Quick Setup

```bash
# Run migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin

# Publish assets (optional)
php artisan vendor:publish --tag=hyro-assets
```

---

## Quick Start

### 1. Add Trait to User Model

```php
use Marufsharia\Hyro\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
}
```

### 2. Check Permissions

```php
// Check if user has a role
if (auth()->user()->hasRole('admin')) {
    // Admin only code
}

// Check if user has a privilege
if (auth()->user()->hasPrivilege('users.create')) {
    // Create user
}
```

### 3. Protect Routes

```php
Route::middleware(['hyro.role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

Route::middleware(['hyro.privilege:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});
```

---

## Modular Architecture

Hyro v1.0.0+ features a modular architecture with 6 independent packages:

### Core Package (`marufsharia/hyro-core`)

The foundation of Hyro providing:
- Authorization engine with multi-resolution system
- Base models: Role, Privilege, User, AuditLog, UserSuspension
- Events, middleware, and contracts
- Base services and repositories

**Key Features:**
- Multi-resolution authorization (Token → Privilege → Wildcard → Role → Gate)
- Hierarchical RBAC
- Wildcard privilege patterns
- Temporal access control
- User suspension management

### Auth Package (`marufsharia/hyro-auth`)

Authentication and security features:
- Authentication controllers and middleware
- Two-factor authentication (2FA) with Google Authenticator
- Token synchronization service
- Emergency access commands

**Key Features:**
- Sanctum token authentication
- 2FA with QR code generation
- Recovery codes
- Token management

### API Package (`marufsharia/hyro-api`)

RESTful API with complete RBAC:
- API controllers for users, roles, privileges
- Request validation and resources
- API middleware and rate limiting

**Endpoints:**
- `/api/hyro/auth/*` - Authentication
- `/api/hyro/users/*` - User management
- `/api/hyro/roles/*` - Role management
- `/api/hyro/privileges/*` - Privilege management

### Admin Panel Package (`marufsharia/hyro-admin-panel`)

Beautiful admin interface:
- Dashboard with Livewire 3
- User, role, and privilege management
- Settings system with appearance customization
- Plugin manager UI
- Notification center
- Profile management

**Access:** `/admin/hyro`

### CRUD Package (`marufsharia/hyro-crud`)

Advanced CRUD generator:
- 10+ beautiful templates
- Auto-generate migrations, models, routes, and views
- Frontend templates (blog, ecommerce, portfolio, etc.)
- Smart route discovery and backup system

**Usage:**
```bash
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal" \
    --template=frontend.ecommerce \
    --migration
```

### Plugin Package (`marufsharia/hyro-plugin`)

Extensibility system:
- Plugin system with hot-loading
- Remote plugin installation (GitHub, GitLab, Packagist)
- Hook system for extensibility
- Plugin marketplace integration

**Commands:**
```bash
php artisan hyro:plugin:make MyPlugin
php artisan hyro:plugin:activate my-plugin
```

---

## Authorization System

### Multi-Resolution Authorization

Hyro uses a sophisticated multi-resolution authorization system:

1. **Token Level** - Check Sanctum token abilities
2. **Privilege Level** - Direct privilege assignment
3. **Wildcard Level** - Pattern matching (`users.*`)
4. **Role Level** - Role-based privileges
5. **Gate Level** - Laravel gates fallback

### Roles

Roles are collections of privileges that can be assigned to users.

```php
use Marufsharia\Hyro\Models\Role;

// Create a role
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Content editor role'
]);

// Assign to user
$user->assignRole($role);

// Check role
if ($user->hasRole('editor')) {
    // User is an editor
}
```

### Privileges

Privileges are specific permissions that can be granted to roles or users.

```php
use Marufsharia\Hyro\Models\Privilege;

// Create a privilege
$privilege = Privilege::create([
    'name' => 'Edit Posts',
    'slug' => 'posts.edit',
    'description' => 'Can edit blog posts'
]);

// Grant to role
$role->grantPrivilege($privilege);

// Check privilege
if ($user->hasPrivilege('posts.edit')) {
    // User can edit posts
}
```

### Wildcard Privileges

Use wildcards for flexible permission patterns:

```php
// Grant wildcard privilege
$privilege = Privilege::create([
    'slug' => 'posts.*',  // Matches posts.create, posts.edit, posts.delete
]);

$role->grantPrivilege($privilege);

// Now user can do anything with posts
$user->hasPrivilege('posts.create');  // true
$user->hasPrivilege('posts.edit');    // true
$user->hasPrivilege('posts.delete');  // true
```

### Blade Directives

```blade
@hasrole('admin')
    <a href="/admin">Admin Panel</a>
@endhasrole

@hasprivilege('posts.create')
    <button>Create Post</button>
@endhasprivilege

@hasanyrole(['admin', 'moderator'])
    <div>Admin or Moderator Content</div>
@endhasanyrole

@hasallprivileges(['posts.create', 'posts.edit'])
    <button>Create & Edit Posts</button>
@endhasallprivileges
```

---

## CRUD Generator

### Basic Usage

```bash
php artisan hyro:make-crud ModelName \
    --fields="field1:type,field2:type" \
    --migration
```

### Available Templates

#### Admin Templates
- `admin.template1` - Full-featured dashboard (default)
- `admin.template2` - Compact data entry

#### Frontend Templates
- `frontend.blog` - Blog/article layout
- `frontend.ecommerce` - E-commerce product grid
- `frontend.portfolio` - Portfolio/gallery masonry
- `frontend.magazine` - Magazine-style layout
- `frontend.landing` - Landing page cards
- `frontend.news` - News/media layout
- `frontend.gallery` - Photo gallery grid
- `frontend.directory` - Business directory list
- `frontend.dashboard` - Data dashboard table
- `frontend.minimal` - Minimal clean design

### Field Types

- `string` - Text input
- `text` - Textarea
- `email` - Email input
- `number` / `integer` - Number input
- `decimal` - Decimal number
- `boolean` / `checkbox` - Checkbox
- `date` - Date picker
- `datetime` - DateTime picker
- `image` - Image upload
- `file` - File upload
- `select` - Dropdown select

### Examples

#### E-commerce Product

```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string:required,description:text,price:decimal:required,stock:integer,image:image" \
    --searchable="name,description" \
    --export \
    --migration
```

#### Blog Article

```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.blog \
    --fields="title:string:required,content:text:required,author:string,featured_image:image" \
    --searchable="title,content,author" \
    --migration
```

---

## Plugin System

### Creating a Plugin

```bash
php artisan hyro:plugin:make MyPlugin
```

This creates a plugin structure:
```
hyro-plugins/MyPlugin/
├── src/
│   └── Plugin.php
├── resources/
│   └── views/
│       └── index.blade.php
├── routes/
│   └── web.php
├── database/
│   └── migrations/
├── composer.json
└── README.md
```

### Plugin Structure

```php
namespace HyroPlugins\MyPlugin;

use Marufsharia\Hyro\Support\Plugins\HyroPlugin;

class Plugin extends HyroPlugin
{
    public function boot(): void
    {
        // Register routes, views, etc.
    }

    public function register(): void
    {
        // Register services
    }
}
```

### Plugin Commands

```bash
# List plugins
php artisan hyro:plugin:list

# Activate plugin
php artisan hyro:plugin:activate my-plugin

# Deactivate plugin
php artisan hyro:plugin:deactivate my-plugin

# Install from remote
php artisan hyro:plugin:install-remote vendor/package
```

---

## API Documentation

### Authentication

```bash
# Login
POST /api/hyro/auth/login
{
    "email": "admin@example.com",
    "password": "password"
}

# Response
{
    "token": "1|abc123...",
    "user": { ... }
}
```

### Using the API

```bash
# Get users
curl -X GET http://your-app.com/api/hyro/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Create user
curl -X POST http://your-app.com/api/hyro/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'
```

### Available Endpoints

- `POST /api/hyro/auth/login` - Login
- `POST /api/hyro/auth/register` - Register
- `POST /api/hyro/auth/logout` - Logout
- `GET /api/hyro/users` - List users
- `POST /api/hyro/users` - Create user
- `GET /api/hyro/users/{id}` - Get user
- `PUT /api/hyro/users/{id}` - Update user
- `DELETE /api/hyro/users/{id}` - Delete user
- `GET /api/hyro/roles` - List roles
- `POST /api/hyro/roles` - Create role
- `GET /api/hyro/privileges` - List privileges

---

## Profile Management

### Features

- Profile information management
- Avatar management (upload/Gravatar/default)
- Password change with validation
- Two-factor authentication
- Account deletion with grace period

### Usage

```php
// Add trait to User model
use Marufsharia\Hyro\Traits\HasProfileManagement;

class User extends Authenticatable
{
    use HasProfileManagement;
}
```

### Routes

- `/profile` - Main profile page
- `/profile/settings` - Alternative route

### Activity Logging

```php
// Get user activity logs
$logs = auth()->user()->activityLogs()
    ->latest()
    ->paginate(20);

// Log custom activity
auth()->user()->logActivity('custom.action', [
    'key' => 'value'
]);
```

---

## Configuration

### Environment Variables

```env
# Core Features
HYRO_ENABLED=true
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true

# Admin Panel
HYRO_ADMIN_PREFIX=admin/hyro

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin
HYRO_PASSWORD_MIN_LENGTH=8

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit Logging
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365
```

### Publishing Configuration

```bash
# Publish config file
php artisan vendor:publish --tag=hyro-config

# Publish views
php artisan vendor:publish --tag=hyro-views

# Publish migrations
php artisan vendor:publish --tag=hyro-migrations
```

---

## Custom Packages

### Creating a Custom Package

Each Hyro package follows a standard structure:

```
my-package/
├── src/
│   ├── MyPackageServiceProvider.php
│   ├── Models/
│   ├── Controllers/
│   └── Services/
├── config/
│   └── my-package.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   └── web.php
└── composer.json
```

### Service Provider Example

```php
namespace Vendor\MyPackage;

use Illuminate\Support\ServiceProvider;

class MyPackageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'my-package');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/my-package.php', 'my-package'
        );
    }
}
```

---

## Extending Hyro

### Custom Middleware

```php
namespace App\Http\Middleware;

use Closure;

class CustomHyroMiddleware
{
    public function handle($request, Closure $next)
    {
        // Custom logic
        return $next($request);
    }
}
```

### Custom Events

```php
use Marufsharia\Hyro\Events\RoleAssigned;

Event::listen(RoleAssigned::class, function ($event) {
    // Handle role assignment
    Log::info('Role assigned', [
        'user' => $event->user->id,
        'role' => $event->role->name
    ]);
});
```

### Custom Commands

```php
namespace App\Console\Commands;

use Marufsharia\Hyro\Console\Commands\BaseCommand;

class MyCustomCommand extends BaseCommand
{
    protected $signature = 'hyro:my-command';
    protected $description = 'My custom Hyro command';

    public function handle()
    {
        $this->info('Running custom command...');
    }
}
```

---

## Security Best Practices

### 1. Enable Audit Logging

```env
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365
```

### 2. Use Strong Password Policy

```env
HYRO_PASSWORD_MIN_LENGTH=12
HYRO_MAX_LOGIN_ATTEMPTS=5
```

### 3. Enable Two-Factor Authentication

```php
// Enable 2FA for user
$user->enableTwoFactorAuth();

// Verify 2FA code
if ($user->verifyTwoFactorCode($code)) {
    // Code is valid
}
```

### 4. Protect Critical Roles

```env
HYRO_PROTECTED_ROLES=super-admin,admin
```

### 5. Use API Rate Limiting

```php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

### 6. Regular Security Audits

```bash
# Review audit logs
php artisan hyro:audit:review

# Check for suspicious activity
php artisan hyro:security:check
```

---

## CLI Commands Reference

### User Management
```bash
php artisan hyro:user:create              # Create new user
php artisan hyro:user:create --admin      # Create admin user
php artisan hyro:user:list                # List all users
php artisan hyro:user:suspend             # Suspend user
php artisan hyro:user:unsuspend           # Unsuspend user
```

### Role Management
```bash
php artisan hyro:role:create              # Create new role
php artisan hyro:role:list                # List all roles
php artisan hyro:role:assign              # Assign role to user
php artisan hyro:role:revoke              # Revoke role from user
```

### Plugin Management
```bash
php artisan hyro:plugin:list              # List installed plugins
php artisan hyro:plugin:make              # Create new plugin
php artisan hyro:plugin:activate          # Activate plugin
php artisan hyro:plugin:deactivate        # Deactivate plugin
```

---

## Troubleshooting

### Common Issues

#### 1. Permissions Not Working

Check if the trait is added to User model:
```php
use Marufsharia\Hyro\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
}
```

#### 2. Admin Panel Not Accessible

Ensure migrations are run:
```bash
php artisan migrate
```

Create an admin user:
```bash
php artisan hyro:user:create --admin
```

#### 3. CRUD Generator Not Working

Clear cache:
```bash
php artisan cache:clear
php artisan config:clear
```

#### 4. Plugin Not Loading

Check plugin is activated:
```bash
php artisan hyro:plugin:list
php artisan hyro:plugin:activate plugin-name
```

---

## Support

- **GitHub:** https://github.com/marufsharia/hyro
- **Issues:** https://github.com/marufsharia/hyro/issues
- **Email:** marufsharia@gmail.com

---

## License

Hyro is open-sourced software licensed under the [MIT license](LICENSE).
