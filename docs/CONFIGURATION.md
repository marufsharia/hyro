# ⚙️ Hyro Configuration Guide

Complete configuration reference for Hyro package.

---

## 📋 Table of Contents

- [Environment Variables](#environment-variables)
- [Configuration File](#configuration-file)
- [Feature Toggles](#feature-toggles)
- [Security Settings](#security-settings)
- [Performance Tuning](#performance-tuning)
- [Advanced Configuration](#advanced-configuration)

---

## 🔧 Environment Variables

### Core Settings

```env
# Enable/Disable Hyro
HYRO_ENABLED=true

# API Configuration
HYRO_API_ENABLED=true
HYRO_API_PREFIX=api/hyro
HYRO_API_RATE_LIMIT=true
HYRO_API_MAX_ATTEMPTS=60
HYRO_API_DECAY_MINUTES=1

# Admin Panel
HYRO_ADMIN_ENABLED=true
HYRO_ADMIN_PREFIX=admin/hyro
HYRO_ADMIN_LAYOUT=hyro::admin.layouts.app

# CLI Commands
HYRO_CLI_ENABLED=true
HYRO_CLI_DANGER_CONFIRM=true

# Livewire
HYRO_LIVEWIRE_ENABLED=true
HYRO_LIVEWIRE_THEME=default
```

### Security Settings

```env
# Authorization
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin
HYRO_PASSWORD_MIN_LENGTH=8
HYRO_MAX_LOGIN_ATTEMPTS=5

# Token Management
HYRO_TOKEN_SYNC_ENABLED=true
HYRO_TOKEN_EXPIRATION=525600
```

### Cache Configuration

```env
# Cache Settings
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600
HYRO_CACHE_PREFIX=hyro_
HYRO_CACHE_DRIVER=redis
```

### Audit Logging

```env
# Audit Configuration
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365
HYRO_AUDIT_BATCH_TRACKING=true
HYRO_AUDIT_SANITIZE_SENSITIVE=true
```

### Notifications

```env
# Notification Settings
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_NOTIFICATIONS_CHANNELS=database,mail
HYRO_NOTIFICATIONS_QUEUE=true
HYRO_NOTIFICATIONS_QUEUE_CONNECTION=redis
HYRO_NOTIFICATIONS_QUEUE_NAME=notifications

# Per-Event Settings
HYRO_NOTIFY_ROLE_ASSIGNED=true
HYRO_NOTIFY_ROLE_REVOKED=true
HYRO_NOTIFY_USER_SUSPENDED=true
HYRO_NOTIFY_USER_UNSUSPENDED=true
HYRO_NOTIFY_ADMIN_USER_SUSPENDED=true

# Real-time Notifications
HYRO_NOTIFICATIONS_REALTIME=false
BROADCAST_DRIVER=pusher
```

### Database Management

```env
# Backup Configuration
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_ENCRYPT=false
HYRO_DB_BACKUP_KEY=your-encryption-key
HYRO_DB_BACKUP_RETENTION=30

# Backup Schedule
HYRO_DB_BACKUP_SCHEDULE=true
HYRO_DB_BACKUP_FREQUENCY=daily
HYRO_DB_BACKUP_TIME=02:00

# Optimization
HYRO_DB_OPTIMIZE_ENABLED=true
HYRO_DB_OPTIMIZE_SCHEDULE=true
HYRO_DB_OPTIMIZE_FREQUENCY=weekly

# Monitoring
HYRO_DB_MONITORING_ENABLED=true
HYRO_DB_SLOW_QUERY_THRESHOLD=1000
```

---

## 📄 Configuration File

The main configuration file is located at `config/hyro.php`.

### General Settings

```php
return [
    'enabled' => env('HYRO_ENABLED', true),
    
    // ... other settings
];
```

### API Configuration

```php
'api' => [
    'enabled' => env('HYRO_API_ENABLED', false),
    'prefix' => env('HYRO_API_PREFIX', 'api/hyro'),
    'middleware' => ['api', 'auth:sanctum'],
    'rate_limit' => [
        'enabled' => env('HYRO_API_RATE_LIMIT', true),
        'max_attempts' => env('HYRO_API_MAX_ATTEMPTS', 60),
        'decay_minutes' => env('HYRO_API_DECAY_MINUTES', 1),
    ],
],
```

### Admin Panel Configuration

```php
'admin' => [
    'enabled' => env('HYRO_ADMIN_ENABLED', true),
    'redirects' => [
        'authenticated' => 'dashboard',
    ],
    'route' => [
        'prefix' => env('HYRO_ADMIN_PREFIX', 'admin/hyro'),
        'middleware' => ['web', 'hyro.auth'],
    ],
    'layout' => env('HYRO_ADMIN_LAYOUT', 'hyro::admin.layouts.app'),
    'pagination' => [
        'per_page' => 20,
    ],
],
```

### Security Configuration

```php
'security' => [
    'fail_closed' => env('HYRO_FAIL_CLOSED', true),
    'protected_roles' => explode(',', env('HYRO_PROTECTED_ROLES', 'super-admin,admin')),
    'password' => [
        'min_length' => env('HYRO_PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => false,
        'require_numbers' => false,
        'require_special' => false,
    ],
    'login' => [
        'max_attempts' => env('HYRO_MAX_LOGIN_ATTEMPTS', 5),
        'decay_minutes' => 1,
    ],
],
```

### Cache Configuration

```php
'cache' => [
    'enabled' => env('HYRO_CACHE_ENABLED', true),
    'ttl' => env('HYRO_CACHE_TTL', 3600),
    'prefix' => env('HYRO_CACHE_PREFIX', 'hyro_'),
    'driver' => env('HYRO_CACHE_DRIVER', 'redis'),
    'tags' => [
        'roles' => 'hyro_roles',
        'privileges' => 'hyro_privileges',
        'users' => 'hyro_users',
    ],
],
```

---

## 🎛️ Feature Toggles

Enable or disable specific features:

```php
'features' => [
    'api' => env('HYRO_API_ENABLED', false),
    'admin_ui' => env('HYRO_ADMIN_ENABLED', true),
    'cli' => env('HYRO_CLI_ENABLED', true),
    'livewire' => env('HYRO_LIVEWIRE_ENABLED', true),
    'audit_logging' => env('HYRO_AUDIT_ENABLED', true),
    'notifications' => env('HYRO_NOTIFICATIONS_ENABLED', true),
    'database_backup' => env('HYRO_DB_BACKUP_ENABLED', true),
    'plugins' => env('HYRO_PLUGINS_ENABLED', true),
],
```

---

## 🔐 Security Settings

### Fail-Closed Authorization

When enabled, denies access by default if authorization check fails:

```php
'security' => [
    'fail_closed' => true, // Deny by default
],
```

### Protected Roles

Prevent deletion of critical roles:

```php
'security' => [
    'protected_roles' => ['super-admin', 'admin'],
],
```

### Password Policy

```php
'security' => [
    'password' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_numbers' => true,
        'require_special' => true,
    ],
],
```

### Rate Limiting

```php
'api' => [
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],
],
```

---

## ⚡ Performance Tuning

### Cache Optimization

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'driver' => 'redis', // Use Redis for better performance
],
```

### Database Optimization

```php
'database' => [
    'optimization' => [
        'enabled' => true,
        'schedule' => [
            'enabled' => true,
            'frequency' => 'weekly',
        ],
    ],
],
```

### Queue Configuration

```php
'notifications' => [
    'queue' => [
        'enabled' => true,
        'connection' => 'redis',
        'queue' => 'notifications',
    ],
],
```

---

## 🔧 Advanced Configuration

### Wildcard Privileges

```php
'privileges' => [
    'wildcard' => [
        'enabled' => true,
        'separator' => '.',
        'patterns' => [
            'users.*' => 'All user operations',
            'posts.*.edit' => 'Edit any post',
        ],
    ],
],
```

### Audit Logging

```php
'audit' => [
    'enabled' => true,
    'retention_days' => 365,
    'batch_tracking' => true,
    'sanitize_sensitive' => true,
    'excluded_events' => [],
    'partitioning' => [
        'enabled' => true,
        'strategy' => 'yearly', // yearly, monthly, daily
    ],
],
```

### Plugin System

```php
'plugins' => [
    'enabled' => true,
    'path' => base_path('hyro-plugins'),
    'namespace' => 'HyroPlugins',
    'auto_discover' => true,
    'marketplace' => [
        'enabled' => true,
        'url' => 'https://marketplace.hyro.dev',
    ],
],
```

### CRUD Generator

```php
'crud' => [
    'enabled' => true,
    'namespace' => 'App\\Livewire\\Admin',
    'view_path' => 'resources/views/livewire/admin',
    'route_prefix' => 'admin',
    'auto_discover' => true,
],
```

---

## 📊 Monitoring Configuration

```php
'monitoring' => [
    'enabled' => true,
    'slow_query_threshold' => 1000, // milliseconds
    'log_queries' => env('APP_DEBUG', false),
    'health_check' => [
        'enabled' => true,
        'endpoint' => '/health',
    ],
],
```

---

## 🌐 Multi-Language Support

```php
'localization' => [
    'enabled' => true,
    'default' => 'en',
    'fallback' => 'en',
    'supported' => ['en', 'es', 'fr', 'de'],
],
```

---

## 🔄 Broadcasting Configuration

For real-time notifications:

```php
'notifications' => [
    'real_time' => [
        'enabled' => true,
        'driver' => 'pusher',
    ],
],
```

Configure broadcasting in `config/broadcasting.php`:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'encrypted' => true,
    ],
],
```

---

## 📝 Configuration Best Practices

### 1. Use Environment Variables

Always use environment variables for sensitive data:

```php
'database' => [
    'backup' => [
        'encryption_key' => env('HYRO_DB_BACKUP_KEY'),
    ],
],
```

### 2. Cache Configuration

In production, cache your configuration:

```bash
php artisan config:cache
```

### 3. Separate Environments

Use different `.env` files for different environments:
- `.env.local` - Local development
- `.env.staging` - Staging server
- `.env.production` - Production server

### 4. Version Control

Never commit `.env` files to version control. Use `.env.example` instead.

### 5. Regular Audits

Regularly review your configuration for security and performance.

---

## 🔍 Configuration Validation

Validate your configuration:

```bash
php artisan hyro:config:validate
```

---

## 📚 Related Documentation

- [INSTALLATION.md](INSTALLATION.md) - Installation guide
- [USAGE.md](USAGE.md) - Usage examples
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
- [SECURITY.md](SECURITY.md) - Security best practices

---

**Configuration Complete!** ⚙️

Your Hyro package is now properly configured.


---

## 🛣️ Route Customization

### Smart Route Loading

Hyro uses a smart route loading system that allows you to customize routes without modifying the package files.

**How it works:**

1. **Default Behavior:** Routes load from the package (`vendor/marufsharia/hyro/routes/`)
2. **Custom Routes:** If you publish routes to `routes/hyro/`, those take precedence

### Publishing Routes

To customize Hyro routes, publish them to your application:

```bash
php artisan vendor:publish --tag=hyro-routes
```

This will create the following files in your `routes/hyro/` directory:

- `admin.php` - Admin panel routes (dashboard, roles, privileges, users)
- `auth.php` - Authentication routes (login, register, password reset)
- `notifications.php` - Notification routes (notification center, preferences)
- `api.php` - API routes (if API is enabled)

### Customizing Routes

Once published, you can modify the routes in `routes/hyro/` directory:

**Example: Adding custom middleware to admin routes**

```php
// routes/hyro/admin.php

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware(['web', 'auth', 'custom-middleware']) // Add your middleware
    ->name('hyro.admin.')
    ->group(function () {
        // Your custom routes or modifications
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Add custom admin routes
        Route::get('/custom-page', [CustomController::class, 'index'])->name('custom');
    });
```

**Example: Customizing authentication routes**

```php
// routes/hyro/auth.php

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware('web')
    ->name('hyro.')
    ->group(function () {
        // Customize login route
        Route::get('/login', [CustomAuthController::class, 'showLoginForm'])
            ->name('login');
            
        // Add 2FA routes
        Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])
            ->name('2fa.verify');
    });
```

### Route Loading Priority

The route loading follows this priority:

1. **Published Routes** (`routes/hyro/*.php`) - Highest priority
2. **Package Routes** (`vendor/marufsharia/hyro/routes/*.php`) - Default fallback

### Reverting to Package Routes

To revert to package routes, simply delete the published route files:

```bash
rm -rf routes/hyro/
```

Hyro will automatically fall back to loading routes from the package.

### Best Practices

1. **Only publish when needed:** Don't publish routes unless you need to customize them
2. **Keep in sync:** When upgrading Hyro, check if package routes have changed
3. **Document changes:** Comment your customizations for future reference
4. **Test thoroughly:** Always test route changes in development before deploying

### Route Configuration

You can configure route prefixes and middleware in `config/hyro.php`:

```php
'admin' => [
    'route' => [
        'prefix' => env('HYRO_ADMIN_PREFIX', 'admin/hyro'),
        'middleware' => ['web', 'hyro.auth'],
    ],
],

'api' => [
    'prefix' => env('HYRO_API_PREFIX', 'api/hyro'),
    'middleware' => ['api', 'auth:sanctum'],
],
```

Or via environment variables:

```env
HYRO_ADMIN_PREFIX=admin/hyro
HYRO_API_PREFIX=api/hyro
```

---


---

## 🎨 View Customization

### Smart View Loading

Hyro uses a smart view loading system that allows you to customize views without modifying the package files.

**How it works:**

1. **Default Behavior:** Views load from the package (`vendor/marufsharia/hyro/resources/views/`)
2. **Custom Views:** If you publish views to `resources/views/vendor/hyro/`, those take precedence
3. **Fallback Support:** Unpublished views automatically load from the package

### Publishing Views

To customize Hyro views, publish them to your application:

```bash
php artisan vendor:publish --tag=hyro-views
```

This will create the following structure in your `resources/views/vendor/hyro/` directory:

- `admin/` - Admin panel views (dashboard, users, roles, privileges)
- `auth/` - Authentication views (login, register, password reset)
- `components/` - Reusable Blade components
- `notifications/` - Notification center views

### Customizing Views

#### Full Customization

Publish all views and modify as needed:

```bash
php artisan vendor:publish --tag=hyro-views
```

Then edit any view:

```blade
{{-- resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php --}}

@extends('hyro::admin.layouts.app')

@section('content')
    <div class="my-custom-dashboard">
        {{-- Your custom dashboard content --}}
        <h1>Welcome to My Custom Dashboard</h1>
    </div>
@endsection
```

#### Partial Customization

Only customize specific views:

1. Publish all views
2. Delete views you don't need to customize
3. Keep only the views you want to modify

Example:

```bash
# Publish all views
php artisan vendor:publish --tag=hyro-views

# Keep only dashboard and layout
# Delete other views you don't need to customize
```

The smart view loading will use your customized views and fall back to package views for the rest.

### View Namespaces

All Hyro views use the `hyro::` namespace:

```blade
{{-- Extend Hyro layouts --}}
@extends('hyro::admin.layouts.app')

{{-- Include Hyro views --}}
@include('hyro::components.alert')

{{-- Use Hyro components --}}
<x-hyro-card title="My Card">
    Content
</x-hyro-card>
```

### Common View Customizations

#### Customize Dashboard

```blade
{{-- resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php --}}

@extends('hyro::admin.layouts.app')

@section('content')
    {{-- Add your custom widgets --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Custom stat cards --}}
    </div>
@endsection
```

#### Customize Login Page

```blade
{{-- resources/views/vendor/hyro/auth/login.blade.php --}}

@extends('hyro::auth.layouts.guest')

@section('content')
    <div class="custom-login-form">
        {{-- Your custom login form --}}
    </div>
@endsection
```

#### Customize Layout

```blade
{{-- resources/views/vendor/hyro/admin/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Your custom head content --}}
    @hyroAssets
</head>
<body>
    {{-- Your custom layout --}}
    @yield('content')
</body>
</html>
```

### Reverting to Package Views

To revert to package views, simply delete the published views:

```bash
rm -rf resources/views/vendor/hyro/
```

### View Loading Priority

The view loading follows this priority:

1. **Published Views** (`resources/views/vendor/hyro/**`) - Highest priority
2. **Package Views** (`vendor/marufsharia/hyro/resources/views/**`) - Fallback

---

## 🎭 Asset Customization

### Smart Asset Loading

Hyro uses a smart asset loading system with manifest-based asset resolution.

**How it works:**

1. **Published Assets:** Load from `public/vendor/hyro/`
2. **Manifest:** Uses `manifest.json` for versioned asset URLs
3. **Cache Busting:** Automatic via versioned filenames

### Publishing Assets

Assets should be published for production use:

```bash
php artisan vendor:publish --tag=hyro-assets
```

This will create:

```
public/vendor/hyro/
├── manifest.json
├── assets/
│   ├── hyro-[hash].css
│   └── hyro-[hash].js
└── images/
    └── (image files)
```

### Using Assets in Views

#### Using Blade Directives

```blade
{{-- Load all Hyro assets (CSS + JS) --}}
@hyroAssets

{{-- Or load individually --}}
@hyroCss
@hyroJs
```

#### Using Helper Methods

```blade
{{-- In your layout head --}}
<head>
    {!! \Marufsharia\Hyro\Helpers\HyroAsset::css() !!}
    {!! \Marufsharia\Hyro\Helpers\HyroAsset::js() !!}
</head>

{{-- Get image URL --}}
<img src="{{ \Marufsharia\Hyro\Helpers\HyroAsset::image('logo.png') }}" alt="Logo">
```

### Customizing Assets

#### Option 1: Override Styles

Create your own stylesheet to override Hyro styles:

```blade
{{-- In your layout --}}
@hyroAssets

{{-- Your custom overrides --}}
<link rel="stylesheet" href="{{ asset('css/hyro-custom.css') }}">
```

```css
/* public/css/hyro-custom.css */

/* Override Hyro button colors */
.btn-primary {
    background-color: #your-color;
}

/* Override dashboard card styles */
.dashboard-card {
    border-radius: 12px;
}
```

#### Option 2: Modify Published Assets

```bash
# Publish assets
php artisan vendor:publish --tag=hyro-assets

# Modify the CSS file
# Edit: public/vendor/hyro/assets/hyro-[hash].css
```

> **Warning:** Modifying published assets directly will be overwritten when you republish. Use Option 1 for persistent customizations.

### Asset Configuration

Configure asset paths in `config/hyro.php`:

```php
'assets' => [
    'url' => env('HYRO_ASSET_URL', '/vendor/hyro'),
    'path' => env('HYRO_ASSET_PATH', public_path('vendor/hyro')),
],
```

Or via environment variables:

```env
HYRO_ASSET_URL=/vendor/hyro
HYRO_ASSET_PATH=/path/to/public/vendor/hyro
```

### Recompiling Assets

If you're developing Hyro or need to recompile assets:

```bash
cd packages/marufsharia/hyro
npm install
npm run build

# Publish the compiled assets
php artisan vendor:publish --tag=hyro-assets --force
```

### Asset Loading in Production

For production, ensure assets are published and optimized:

```bash
# Publish assets
php artisan vendor:publish --tag=hyro-assets

# Clear view cache
php artisan view:clear

# Optimize
php artisan optimize
```

---

## 📚 Smart Resource Loading Summary

| Resource | Default Location | Published Location | Priority |
|----------|-----------------|-------------------|----------|
| **Routes** | `vendor/.../routes/` | `routes/hyro/` | Published first |
| **Views** | `vendor/.../resources/views/` | `resources/views/vendor/hyro/` | Published first, package fallback |
| **Assets** | `vendor/.../public/build/` | `public/vendor/hyro/` | Published only |
| **Config** | `vendor/.../config/` | `config/hyro.php` | Published overrides |

### Best Practices

1. **Selective Publishing:** Only publish resources you need to customize
2. **Version Control:** Commit published resources to version control
3. **Documentation:** Document your customizations
4. **Testing:** Test after customizing
5. **Updates:** Check for changes when updating Hyro

### Complete Documentation

For comprehensive documentation on smart resource loading, see:
- [SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md) - Complete guide
- [SMART_ROUTE_LOADING.md](SMART_ROUTE_LOADING.md) - Route-specific details

---
