# 🎯 Complete Smart Resource Loading Guide

**Date:** February 9, 2026  
**Feature:** Comprehensive Smart Loading System  
**Status:** ✅ Production Ready

---

## 📋 Overview

Hyro implements a complete smart resource loading system that covers **all** package resources:

- ✅ Routes
- ✅ Views
- ✅ Assets
- ✅ Migrations
- ✅ Translations
- ✅ Events & Listeners
- ✅ Providers
- ✅ Services
- ✅ Middleware
- ✅ Models

This allows users to customize any aspect of Hyro without modifying vendor files, while maintaining automatic fallback support and update safety.

---

## 🎨 Resource Loading Strategies

### Strategy 1: Published First (Routes, Views, Translations)

**How it works:**
1. Check published location first
2. If found, load from published location
3. Also load from package as fallback (for missing files)

**Use case:** Partial customization where users only customize specific files.

**Resources using this strategy:**
- Routes
- Views  
- Translations

### Strategy 2: Both Locations (Migrations)

**How it works:**
1. Always load from package (core migrations)
2. Also load from published location (custom migrations)

**Use case:** Users can add custom migrations alongside package migrations.

**Resources using this strategy:**
- Migrations

### Strategy 3: Reference Only (Events, Providers, Services, Middleware, Models)

**How it works:**
1. Package provides the source code
2. Users can publish for reference or customization
3. Users manually register customized versions

**Use case:** Advanced customization where users need to modify core logic.

**Resources using this strategy:**
- Events & Listeners
- Providers
- Services
- Middleware
- Models

---

## 📦 Publishing Resources

### Publish All Resources

```bash
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"
```

### Publish Specific Resources

```bash
# Core Resources (Commonly Customized)
php artisan vendor:publish --tag=hyro-routes        # Routes
php artisan vendor:publish --tag=hyro-views         # Views
php artisan vendor:publish --tag=hyro-assets        # Assets (Required)
php artisan vendor:publish --tag=hyro-config        # Configuration

# Database Resources
php artisan vendor:publish --tag=hyro-migrations    # Migrations

# Localization
php artisan vendor:publish --tag=hyro-translations  # Language files

# Advanced Customization (For Reference)
php artisan vendor:publish --tag=hyro-events        # Events & Listeners
php artisan vendor:publish --tag=hyro-providers     # Service Providers
php artisan vendor:publish --tag=hyro-services      # Services
php artisan vendor:publish --tag=hyro-middleware    # Middleware
php artisan vendor:publish --tag=hyro-models        # Models

# Development
php artisan vendor:publish --tag=hyro-crud-stubs    # CRUD generator stubs
```

---

## 🛣️ Routes

### Default Location
```
vendor/marufsharia/hyro/routes/
├── admin.php
├── auth.php
├── notifications.php
└── api.php
```

### Published Location
```
routes/hyro/
├── admin.php
├── auth.php
├── notifications.php
└── api.php
```

### Loading Strategy
**Published First** - Published routes override package routes

### Usage

```bash
# Publish routes
php artisan vendor:publish --tag=hyro-routes

# Customize routes
# Edit: routes/hyro/admin.php

# Revert to package routes
rm -rf routes/hyro/
```

### Example Customization

```php
// routes/hyro/admin.php

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware(['web', 'auth', 'custom-middleware'])
    ->name('hyro.admin.')
    ->group(function () {
        // Add custom routes
        Route::get('/analytics', [AnalyticsController::class, 'index'])
            ->name('analytics');
    });
```

---

## 🎨 Views

### Default Location
```
vendor/marufsharia/hyro/resources/views/
├── admin/
├── auth/
├── components/
└── notifications/
```

### Published Location
```
resources/views/vendor/hyro/
├── admin/
├── auth/
├── components/
└── notifications/
```

### Loading Strategy
**Published First with Fallback** - Published views override, unpublished views load from package

### Usage

```bash
# Publish views
php artisan vendor:publish --tag=hyro-views

# Customize specific views
# Edit: resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php

# Revert to package views
rm -rf resources/views/vendor/hyro/
```

### Example Customization

```blade
{{-- resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php --}}

@extends('hyro::admin.layouts.app')

@section('content')
    <div class="custom-dashboard">
        <h1>{{ __('hyro::dashboard.welcome') }}</h1>
        {{-- Your custom dashboard content --}}
    </div>
@endsection
```

---

## 🎭 Assets

### Default Location
```
vendor/marufsharia/hyro/public/build/
├── manifest.json
├── assets/
└── images/
```

### Published Location
```
public/vendor/hyro/
├── manifest.json
├── assets/
└── images/
```

### Loading Strategy
**Published Only** - Assets must be published to work in production

### Usage

```bash
# Publish assets (Required)
php artisan vendor:publish --tag=hyro-assets

# Use in views
@hyroAssets

# Check status
\Marufsharia\Hyro\Helpers\HyroAsset::areAssetsPublished()
```

### Example Usage

```blade
{{-- Load all Hyro assets --}}
@hyroAssets

{{-- Or individually --}}
@hyroCss
@hyroJs

{{-- Images --}}
<img src="{{ \Marufsharia\Hyro\Helpers\HyroAsset::image('logo.png') }}" alt="Logo">
```

---

## 🗄️ Migrations

### Default Location
```
vendor/marufsharia/hyro/database/migrations/
├── 2024_01_01_000000_create_hyro_roles_table.php
├── 2024_01_01_000001_create_hyro_privileges_table.php
└── ...
```

### Published Location
```
database/migrations/hyro/
├── (package migrations copied here)
└── (your custom migrations)
```

### Loading Strategy
**Both Locations** - Package migrations always load, published migrations also load

### Usage

```bash
# Publish migrations (Optional - only if you want to modify them)
php artisan vendor:publish --tag=hyro-migrations

# Run migrations
php artisan migrate

# Create custom migration
php artisan make:migration add_custom_field_to_hyro_users --path=database/migrations/hyro
```

### Why Both Locations?

- **Package migrations:** Core Hyro tables (always loaded)
- **Published migrations:** Your custom modifications or additions

This allows you to:
1. Keep package migrations intact
2. Add your own custom migrations
3. Modify published migrations without affecting package

### Example Custom Migration

```php
// database/migrations/hyro/2026_02_09_000000_add_custom_field_to_hyro_users.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
```

---

## 🌍 Translations

### Default Location
```
vendor/marufsharia/hyro/resources/lang/
└── en/
    ├── messages.php
    ├── validation.php
    └── ...
```

### Published Location
```
resources/lang/vendor/hyro/
└── en/
    ├── messages.php
    ├── validation.php
    └── ...
```

### Loading Strategy
**Published First with Fallback** - Published translations override, missing keys load from package

### Usage

```bash
# Publish translations
php artisan vendor:publish --tag=hyro-translations

# Customize translations
# Edit: resources/lang/vendor/hyro/en/messages.php

# Add new language
# Create: resources/lang/vendor/hyro/es/messages.php
```

### Example Customization

```php
// resources/lang/vendor/hyro/en/messages.php

return [
    'welcome' => 'Welcome to My Custom Admin Panel',
    'dashboard' => 'Control Center', // Override package translation
    'custom_message' => 'This is my custom message',
];
```

### Usage in Views

```blade
{{-- Use Hyro translations --}}
{{ __('hyro::messages.welcome') }}

{{-- With parameters --}}
{{ __('hyro::messages.greeting', ['name' => $user->name]) }}
```

---

## 🎪 Events & Listeners

### Default Location
```
vendor/marufsharia/hyro/src/Events/
├── RoleAssigned.php
├── RoleRevoked.php
└── ...

vendor/marufsharia/hyro/src/Listeners/
├── AuditLogListener.php
├── NotificationListener.php
└── ...
```

### Published Location
```
app/Events/Hyro/
├── RoleAssigned.php
└── ...

app/Listeners/Hyro/
├── AuditLogListener.php
└── ...
```

### Loading Strategy
**Reference Only** - Publish for reference, manually register customizations

### Usage

```bash
# Publish events and listeners (For reference)
php artisan vendor:publish --tag=hyro-events

# Customize event
# Edit: app/Events/Hyro/RoleAssigned.php

# Register in EventServiceProvider
```

### Example Customization

```php
// app/Events/Hyro/CustomRoleAssigned.php

namespace App\Events\Hyro;

use Illuminate\Contracts\Auth\Authenticatable;
use Marufsharia\Hyro\Models\Role;

class CustomRoleAssigned
{
    public function __construct(
        public Authenticatable $user,
        public Role $role,
        public ?string $reason = null
    ) {}
}
```

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    \App\Events\Hyro\CustomRoleAssigned::class => [
        \App\Listeners\Hyro\CustomRoleAssignedListener::class,
    ],
];
```

---

## 🔌 Providers

### Default Location
```
vendor/marufsharia/hyro/src/Providers/
├── ApiServiceProvider.php
├── BladeDirectivesServiceProvider.php
├── EventServiceProvider.php
└── MiddlewareServiceProvider.php
```

### Published Location
```
app/Providers/Hyro/
├── ApiServiceProvider.php
└── ...
```

### Loading Strategy
**Reference Only** - Publish for reference, manually register customizations

### Usage

```bash
# Publish providers (For reference)
php artisan vendor:publish --tag=hyro-providers

# Customize provider
# Edit: app/Providers/Hyro/CustomHyroServiceProvider.php

# Register in config/app.php
```

### Example Customization

```php
// app/Providers/Hyro/CustomHyroServiceProvider.php

namespace App\Providers\Hyro;

use Illuminate\Support\ServiceProvider;

class CustomHyroServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register custom services
        $this->app->singleton('hyro.custom', function ($app) {
            return new \App\Services\Hyro\CustomService();
        });
    }

    public function boot()
    {
        // Boot custom logic
    }
}
```

```php
// config/app.php

'providers' => [
    // ...
    App\Providers\Hyro\CustomHyroServiceProvider::class,
],
```

---

## 🛠️ Services

### Default Location
```
vendor/marufsharia/hyro/src/Services/
├── AuthorizationService.php
├── CacheInvalidator.php
├── GateRegistrar.php
└── ...
```

### Published Location
```
app/Services/Hyro/
├── AuthorizationService.php
└── ...
```

### Loading Strategy
**Reference Only** - Publish for reference, manually bind customizations

### Usage

```bash
# Publish services (For reference)
php artisan vendor:publish --tag=hyro-services

# Customize service
# Edit: app/Services/Hyro/CustomAuthorizationService.php

# Bind in service provider
```

### Example Customization

```php
// app/Services/Hyro/CustomAuthorizationService.php

namespace App\Services\Hyro;

use Marufsharia\Hyro\Services\AuthorizationService;

class CustomAuthorizationService extends AuthorizationService
{
    public function authorize($user, $privilege): bool
    {
        // Add custom authorization logic
        if ($this->isCustomCondition($user)) {
            return true;
        }

        return parent::authorize($user, $privilege);
    }

    protected function isCustomCondition($user): bool
    {
        // Your custom logic
        return $user->hasCustomAccess();
    }
}
```

```php
// app/Providers/AppServiceProvider.php

public function register()
{
    $this->app->bind(
        \Marufsharia\Hyro\Contracts\AuthorizationResolverContract::class,
        \App\Services\Hyro\CustomAuthorizationService::class
    );
}
```

---

## 🚧 Middleware

### Default Location
```
vendor/marufsharia/hyro/src/Http/Middleware/
├── Authenticate.php
├── RedirectIfAuthenticated.php
└── ...
```

### Published Location
```
app/Http/Middleware/Hyro/
├── Authenticate.php
└── ...
```

### Loading Strategy
**Reference Only** - Publish for reference, manually register customizations

### Usage

```bash
# Publish middleware (For reference)
php artisan vendor:publish --tag=hyro-middleware

# Customize middleware
# Edit: app/Http/Middleware/Hyro/CustomAuthenticate.php

# Register in Kernel or routes
```

### Example Customization

```php
// app/Http/Middleware/Hyro/CustomAuthenticate.php

namespace App\Http\Middleware\Hyro;

use Closure;
use Illuminate\Http\Request;

class CustomAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // Add custom authentication logic
        if (!$this->isCustomAuthenticated($request)) {
            return redirect()->route('custom.login');
        }

        return $next($request);
    }

    protected function isCustomAuthenticated(Request $request): bool
    {
        // Your custom logic
        return $request->user() && $request->user()->hasCustomAuth();
    }
}
```

```php
// routes/hyro/admin.php

Route::middleware(['web', \App\Http\Middleware\Hyro\CustomAuthenticate::class])
    ->group(function () {
        // Your routes
    });
```

---

## 🗂️ Models

### Default Location
```
vendor/marufsharia/hyro/src/Models/
├── Role.php
├── Privilege.php
├── User.php
└── ...
```

### Published Location
```
app/Models/Hyro/
├── Role.php
└── ...
```

### Loading Strategy
**Reference Only** - Publish for reference, extend or replace models

### Usage

```bash
# Publish models (For reference)
php artisan vendor:publish --tag=hyro-models

# Extend model
# Create: app/Models/Hyro/CustomRole.php

# Configure in config/hyro.php
```

### Example Customization

```php
// app/Models/Hyro/CustomRole.php

namespace App\Models\Hyro;

use Marufsharia\Hyro\Models\Role as BaseRole;

class CustomRole extends BaseRole
{
    /**
     * Add custom attributes
     */
    protected $appends = ['custom_attribute'];

    /**
     * Add custom methods
     */
    public function getCustomAttributeAttribute()
    {
        return $this->name . ' - Custom';
    }

    /**
     * Add custom relationships
     */
    public function customRelation()
    {
        return $this->hasMany(CustomModel::class);
    }

    /**
     * Override methods
     */
    public function hasPrivilege($privilege): bool
    {
        // Add custom logic
        if ($this->isCustomRole()) {
            return true;
        }

        return parent::hasPrivilege($privilege);
    }

    protected function isCustomRole(): bool
    {
        return $this->slug === 'custom-role';
    }
}
```

```php
// config/hyro.php

'database' => [
    'models' => [
        'roles' => \App\Models\Hyro\CustomRole::class,
        // ...
    ],
],
```

---

## 🔍 Checking Publication Status

### Using SmartResourceLoader

```php
use Marufsharia\Hyro\Support\SmartResourceLoader;

// Check specific resource
if (SmartResourceLoader::areViewsPublished()) {
    echo "Views are published";
}

// Get all publication status
$status = SmartResourceLoader::getPublicationStatus();
/*
[
    'migrations' => true,
    'views' => true,
    'routes' => false,
    'translations' => false,
    'events' => false,
    'providers' => false,
    'services' => false,
    'middleware' => false,
    'models' => false,
    'assets' => true,
]
*/

// Get summary
$summary = SmartResourceLoader::getPublishedResourcesSummary();
/*
[
    'total' => 10,
    'published' => 3,
    'unpublished' => 7,
    'details' => [...]
]
*/

// Get loading strategy
$strategy = SmartResourceLoader::getLoadingStrategy('views');
// Returns: 'both', 'published', 'package', or 'none'
```

### Using Individual Helpers

```php
// Check assets
\Marufsharia\Hyro\Helpers\HyroAsset::areAssetsPublished();

// Check migrations
\Marufsharia\Hyro\Support\SmartResourceLoader::areMigrationsPublished();

// Check routes
\Marufsharia\Hyro\Support\SmartResourceLoader::areRoutesPublished();
```

---

## 📊 Resource Loading Matrix

| Resource | Default Location | Published Location | Loading Strategy | Customization Level |
|----------|-----------------|-------------------|------------------|---------------------|
| **Routes** | `vendor/.../routes/` | `routes/hyro/` | Published First | Easy |
| **Views** | `vendor/.../resources/views/` | `resources/views/vendor/hyro/` | Published First + Fallback | Easy |
| **Assets** | `vendor/.../public/build/` | `public/vendor/hyro/` | Published Only | Easy |
| **Migrations** | `vendor/.../database/migrations/` | `database/migrations/hyro/` | Both Locations | Medium |
| **Translations** | `vendor/.../resources/lang/` | `resources/lang/vendor/hyro/` | Published First + Fallback | Easy |
| **Events** | `vendor/.../src/Events/` | `app/Events/Hyro/` | Reference Only | Advanced |
| **Providers** | `vendor/.../src/Providers/` | `app/Providers/Hyro/` | Reference Only | Advanced |
| **Services** | `vendor/.../src/Services/` | `app/Services/Hyro/` | Reference Only | Advanced |
| **Middleware** | `vendor/.../src/Http/Middleware/` | `app/Http/Middleware/Hyro/` | Reference Only | Advanced |
| **Models** | `vendor/.../src/Models/` | `app/Models/Hyro/` | Reference Only | Advanced |

---

## 🎯 Best Practices

### 1. Selective Publishing

Only publish what you need:

```bash
# Good: Only publish what you'll customize
php artisan vendor:publish --tag=hyro-views
php artisan vendor:publish --tag=hyro-routes

# Avoid: Publishing everything unnecessarily
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"
```

### 2. Version Control

Add published resources to version control:

```gitignore
# .gitignore

# Keep published Hyro resources
!routes/hyro/
!resources/views/vendor/hyro/
!resources/lang/vendor/hyro/
!public/vendor/hyro/
!database/migrations/hyro/
```

### 3. Documentation

Document your customizations:

```php
/**
 * CUSTOMIZATION: Added custom dashboard widget
 * Date: 2026-02-09
 * Author: Your Name
 * Reason: Client requested real-time analytics
 * 
 * Changes:
 * - Added analytics widget to dashboard
 * - Modified dashboard layout
 * - Added custom CSS for widget styling
 */
```

### 4. Update Strategy

When updating Hyro:

```bash
# 1. Backup customizations
cp -r routes/hyro routes/hyro.backup
cp -r resources/views/vendor/hyro resources/views/vendor/hyro.backup

# 2. Update package
composer update marufsharia/hyro

# 3. Check for breaking changes
# Review CHANGELOG.md

# 4. Republish if needed
php artisan vendor:publish --tag=hyro-views --force

# 5. Reapply customizations
# Compare backup with new files and merge changes

# 6. Test thoroughly
php artisan test
```

### 5. Testing

Always test after customizing:

```bash
# Clear caches
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Run tests
php artisan test

# Manual testing
# - Test routes: php artisan route:list
# - Test views: Visit pages in browser
# - Test assets: Check browser console
```

---

## 🚀 Quick Start Guide

### For Basic Customization

```bash
# 1. Publish assets (Required)
php artisan vendor:publish --tag=hyro-assets

# 2. Publish views (Optional - for UI customization)
php artisan vendor:publish --tag=hyro-views

# 3. Customize views
# Edit: resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php

# 4. Clear caches
php artisan view:clear

# 5. Test
# Visit admin panel in browser
```

### For Advanced Customization

```bash
# 1. Publish all resources
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"

# 2. Customize as needed
# - Routes: routes/hyro/
# - Views: resources/views/vendor/hyro/
# - Migrations: database/migrations/hyro/
# - Translations: resources/lang/vendor/hyro/
# - Services: app/Services/Hyro/
# - Models: app/Models/Hyro/

# 3. Register customizations
# - Update config/hyro.php
# - Update config/app.php (for providers)
# - Update routes (for middleware)

# 4. Clear caches
php artisan optimize:clear

# 5. Test thoroughly
php artisan test
```

---

## 📚 Related Documentation

- [SMART_ROUTE_LOADING.md](SMART_ROUTE_LOADING.md) - Route loading details
- [SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md) - View and asset loading
- [INSTALLATION.md](INSTALLATION.md) - Installation guide
- [CONFIGURATION.md](CONFIGURATION.md) - Configuration options
- [USAGE.md](USAGE.md) - Usage examples

---

## ✅ Summary

Hyro's complete smart resource loading system provides:

✅ **Comprehensive Coverage** - All resources support smart loading  
✅ **Flexible Customization** - Customize only what you need  
✅ **Update Safety** - Customizations survive package updates  
✅ **Automatic Fallback** - Missing resources load from package  
✅ **Easy to Use** - Intuitive publishing and customization  
✅ **Well Documented** - Comprehensive guides and examples  
✅ **Production Ready** - Battle-tested and reliable  

---

**Last Updated:** February 9, 2026  
**Version:** 1.0.0  
**Status:** Production Ready ⭐⭐⭐⭐⭐
