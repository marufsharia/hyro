# 📖 Hyro Usage Guide

Complete usage guide with examples for all Hyro features.

---

## 📋 Table of Contents

- [Basic Usage](#basic-usage)
- [Authorization](#authorization)
- [Roles and Privileges](#roles-and-privileges)
- [User Management](#user-management)
- [Blade Directives](#blade-directives)
- [API Usage](#api-usage)
- [CLI Commands](#cli-commands)
- [Advanced Features](#advanced-features)

---

## 🚀 Basic Usage

### Check User Permissions

```php
use Illuminate\Support\Facades\Auth;

// Check if user has a role
if (Auth::user()->hasRole('admin')) {
    // Admin only code
}

// Check if user has a privilege
if (Auth::user()->hasPrivilege('users.create')) {
    // Create user
}

// Check multiple roles (any)
if (Auth::user()->hasAnyRole(['admin', 'moderator'])) {
    // Admin or moderator code
}

// Check multiple roles (all)
if (Auth::user()->hasAllRoles(['admin', 'verified'])) {
    // Must have both roles
}

// Check multiple privileges
if (Auth::user()->hasAnyPrivilege(['posts.create', 'posts.edit'])) {
    // Can create or edit posts
}
```

### Assign Roles and Privileges

```php
$user = User::find(1);

// Assign a role
$user->assignRole('admin');

// Assign multiple roles
$user->assignRoles(['admin', 'moderator']);

// Remove a role
$user->removeRole('moderator');

// Sync roles (removes all others)
$user->syncRoles(['admin']);

// Check if user is suspended
if ($user->isSuspended()) {
    // Handle suspended user
}
```

---

## 🔐 Authorization

### In Controllers

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Check privilege
        if (!auth()->user()->hasPrivilege('users.view')) {
            abort(403, 'Unauthorized');
        }
        
        return view('users.index');
    }
    
    public function store(Request $request)
    {
        // Using Gate
        $this->authorize('create', User::class);
        
        // Create user
    }
}
```

### Using Middleware

```php
// In routes/web.php

use App\Http\Controllers\UserController;

// Protect single route
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('hyro.role:admin');

// Protect route group
Route::middleware(['hyro.role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
});

// Multiple roles (any)
Route::middleware(['hyro.any-role:admin,moderator'])->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
});

// Privilege-based protection
Route::middleware(['hyro.privilege:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});
```

### Using Gates

```php
// In AuthServiceProvider

use Illuminate\Support\Facades\Gate;

public function boot()
{
    Gate::define('edit-post', function ($user, $post) {
        return $user->hasPrivilege('posts.edit') || $user->id === $post->user_id;
    });
}

// In controller
if (Gate::allows('edit-post', $post)) {
    // Edit post
}

// Or using authorize
$this->authorize('edit-post', $post);
```

---

## 👥 Roles and Privileges

### Create Roles

```php
use Marufsharia\Hyro\Models\Role;

// Create a role
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Content editor role',
    'is_protected' => false,
]);

// Assign privileges to role
$role->grantPrivilege('posts.create');
$role->grantPrivilege('posts.edit');
$role->grantPrivilege('posts.delete');
```

### Create Privileges

```php
use Marufsharia\Hyro\Models\Privilege;

// Create a privilege
$privilege = Privilege::create([
    'name' => 'Create Posts',
    'slug' => 'posts.create',
    'description' => 'Can create new posts',
    'category' => 'posts',
]);

// Create wildcard privilege
$privilege = Privilege::create([
    'name' => 'All Post Operations',
    'slug' => 'posts.*',
    'description' => 'Can perform all post operations',
    'category' => 'posts',
    'is_wildcard' => true,
    'wildcard_pattern' => 'posts.*',
]);
```

### Wildcard Privileges

```php
// Grant wildcard privilege
$role->grantPrivilege('posts.*');

// Now user has all post privileges
$user->hasPrivilege('posts.create'); // true
$user->hasPrivilege('posts.edit');   // true
$user->hasPrivilege('posts.delete'); // true
$user->hasPrivilege('posts.publish'); // true
```

---

## 👤 User Management

### Create Users

```php
use App\Models\User;

$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
]);

// Assign role
$user->assignRole('editor');
```

### Suspend Users

```php
// Suspend user for 7 days
$user->suspend('Violation of terms', 7);

// Suspend indefinitely
$user->suspend('Serious violation');

// Unsuspend user
$user->unsuspend();

// Check suspension
if ($user->isSuspended()) {
    $suspension = $user->activeSuspension();
    echo "Suspended until: " . $suspension->suspended_until;
    echo "Reason: " . $suspension->reason;
}
```

### User Queries

```php
// Get users with specific role
$admins = User::role('admin')->get();

// Get users with any of these roles
$staff = User::anyRole(['admin', 'moderator'])->get();

// Get users with privilege
$editors = User::privilege('posts.edit')->get();

// Get suspended users
$suspended = User::suspended()->get();

// Get active users
$active = User::notSuspended()->get();
```

---

## 🎨 Blade Directives

### Role-Based Directives

```blade
{{-- Check single role --}}
@hasrole('admin')
    <a href="/admin">Admin Panel</a>
@endhasrole

{{-- Check any role --}}
@hasanyrole('admin|moderator')
    <a href="/moderation">Moderation</a>
@endhasanyrole

{{-- Check all roles --}}
@hasallroles('admin|verified')
    <a href="/premium">Premium Features</a>
@endhasallroles

{{-- Inverse check --}}
@unlessrole('admin')
    <p>You are not an admin</p>
@endunlessrole
```

### Privilege-Based Directives

```blade
{{-- Check single privilege --}}
@hasprivilege('posts.create')
    <button>Create Post</button>
@endhasprivilege

{{-- Check any privilege --}}
@hasanyprivilege('posts.create|posts.edit')
    <a href="/posts/manage">Manage Posts</a>
@endhasanyprivilege

{{-- Check all privileges --}}
@hasallprivileges('posts.create|posts.publish')
    <button>Create & Publish</button>
@endhasallprivileges
```

### Suspension Directives

```blade
@suspended
    <div class="alert alert-danger">
        Your account is suspended.
    </div>
@endsuspended

@notsuspended
    <div class="content">
        {{-- Regular content --}}
    </div>
@endnotsuspended
```

### User Information Directives

```blade
{{-- Display user info --}}
@hyro_user
    <p>Logged in as: {{ $user->name }}</p>
@endhydro_user

{{-- Display user roles --}}
@hyro_roles
    <p>Roles: {{ implode(', ', $roles) }}</p>
@endhydro_roles

{{-- Display user privileges --}}
@hyro_privileges
    <ul>
        @foreach($privileges as $privilege)
            <li>{{ $privilege }}</li>
        @endforeach
    </ul>
@endhydro_privileges
```

---

## 🌐 API Usage

### Authentication

```bash
# Login
curl -X POST http://your-domain.com/api/hyro/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Response
{
  "token": "1|abc123...",
  "user": {...}
}
```

### Using API Token

```bash
# Get users
curl -X GET http://your-domain.com/api/hyro/users \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

### User Management

```bash
# Create user
curl -X POST http://your-domain.com/api/hyro/users \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password"}'

# Update user
curl -X PUT http://your-domain.com/api/hyro/users/1 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe"}'

# Delete user
curl -X DELETE http://your-domain.com/api/hyro/users/1 \
  -H "Authorization: Bearer TOKEN"
```

### Role Management

```bash
# Assign role
curl -X POST http://your-domain.com/api/hyro/users/1/roles \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"role":"admin"}'

# Revoke role
curl -X DELETE http://your-domain.com/api/hyro/users/1/roles/admin \
  -H "Authorization: Bearer TOKEN"
```

---

## 💻 CLI Commands

### User Management

```bash
# Create user
php artisan hyro:user:create

# Create admin user
php artisan hyro:user:create --admin

# List users
php artisan hyro:user:list

# Suspend user
php artisan hyro:user:suspend

# Unsuspend user
php artisan hyro:user:unsuspend
```

### Role Management

```bash
# Create role
php artisan hyro:role:create

# List roles
php artisan hyro:role:list

# Assign role to user
php artisan hyro:role:assign

# Revoke role from user
php artisan hyro:role:revoke
```

### Privilege Management

```bash
# Create privilege
php artisan hyro:privilege:create

# List privileges
php artisan hyro:privilege:list

# Grant privilege to role
php artisan hyro:role:grant-privilege

# Revoke privilege from role
php artisan hyro:role:revoke-privilege
```

---

## 🔧 Advanced Features

### Scoped Privileges

```php
// Grant privilege for specific resource
$user->grantScopedPrivilege('posts.edit', 'post', 1);

// Check scoped privilege
if ($user->hasScopedPrivilege('posts.edit', 'post', $post->id)) {
    // Can edit this specific post
}
```

### Temporary Roles

```php
// Assign role with expiration
$user->assignRole('premium', now()->addDays(30));

// Check if role is expired
if ($user->hasRole('premium')) {
    // Still has premium access
}
```

### Audit Logging

```php
use Marufsharia\Hyro\Models\AuditLog;

// Query audit logs
$logs = AuditLog::where('user_id', $user->id)
    ->where('event', 'role_assigned')
    ->latest()
    ->get();

// Get user's activity
$activity = $user->auditLogs()
    ->whereBetween('created_at', [now()->subDays(7), now()])
    ->get();
```

### Notifications

```php
// Send custom notification
$user->notify(new CustomNotification($data));

// Get user notifications
$notifications = $user->notifications;

// Get unread notifications
$unread = $user->unreadNotifications;

// Mark as read
$user->unreadNotifications->markAsRead();
```

---

## 📚 Related Documentation

- [INSTALLATION.md](INSTALLATION.md) - Installation guide
- [CONFIGURATION.md](CONFIGURATION.md) - Configuration reference
- [API.md](API.md) - API documentation
- [BLADE_DIRECTIVES.md](BLADE_DIRECTIVES.md) - Blade directive reference

---

**Happy Coding!** 🚀
