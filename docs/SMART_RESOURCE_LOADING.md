# 🎨 Smart Resource Loading System

**Date:** February 9, 2026  
**Feature:** Smart Loading for Routes, Views, and Assets  
**Status:** ✅ Completed

---

## 📋 Overview

Hyro implements a comprehensive smart resource loading system that allows users to customize routes, views, and assets without modifying vendor files. The system automatically detects published resources and loads them with proper fallback to package resources.

---

## 🎯 Key Features

### 1. **Smart Route Loading**
- Routes load from package by default
- Published routes take precedence
- Automatic fallback to package routes

### 2. **Smart View Loading**
- Views load from package by default
- Published views override package views
- Partial customization supported (only publish views you need)
- Automatic fallback for missing views

### 3. **Smart Asset Loading**
- Assets load from published location
- Automatic manifest detection
- Image path resolution
- Development and production support

---

## 🚀 How It Works

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Smart Resource Loading                    │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │    Routes    │    │    Views     │    │    Assets    │  │
│  └──────────────┘    └──────────────┘    └──────────────┘  │
│         │                   │                    │           │
│         ▼                   ▼                    ▼           │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐  │
│  │  Published?  │    │  Published?  │    │  Published?  │  │
│  └──────────────┘    └──────────────┘    └──────────────┘  │
│    Yes │ No           Yes │ No           Yes │ No          │
│        │                  │                   │             │
│        ▼                  ▼                   ▼             │
│  ┌──────────┐      ┌──────────┐       ┌──────────┐        │
│  │Published │      │Published │       │Published │        │
│  │  Route   │      │   View   │       │  Asset   │        │
│  └──────────┘      └──────────┘       └──────────┘        │
│        │                  │                   │             │
│        │                  │                   │             │
│        ▼                  ▼                   ▼             │
│  ┌──────────┐      ┌──────────┐       ┌──────────┐        │
│  │ Package  │      │ Package  │       │ Package  │        │
│  │  Route   │      │   View   │       │  Asset   │        │
│  └──────────┘      └──────────┘       └──────────┘        │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### Loading Priority

1. **Routes:** `routes/hyro/*.php` → `vendor/marufsharia/hyro/routes/*.php`
2. **Views:** `resources/views/vendor/hyro/**` → `vendor/marufsharia/hyro/resources/views/**`
3. **Assets:** `public/vendor/hyro/**` → `vendor/marufsharia/hyro/public/build/**`

---

## 📦 Publishing Resources

### Publish All Resources

```bash
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"
```

### Publish Specific Resources

```bash
# Routes only
php artisan vendor:publish --tag=hyro-routes

# Views only
php artisan vendor:publish --tag=hyro-views

# Assets only
php artisan vendor:publish --tag=hyro-assets

# Config only
php artisan vendor:publish --tag=hyro-config
```

---

## 🛣️ Smart Route Loading

### Default Behavior

Routes automatically load from the package:

```
vendor/marufsharia/hyro/routes/
├── admin.php          # Admin panel routes
├── auth.php           # Authentication routes
├── notifications.php  # Notification routes
└── api.php           # API routes
```

### Publishing Routes

```bash
php artisan vendor:publish --tag=hyro-routes
```

Creates:

```
routes/hyro/
├── admin.php
├── auth.php
├── notifications.php
└── api.php
```

### Customizing Routes

Edit files in `routes/hyro/` directory:

```php
// routes/hyro/admin.php

Route::prefix(config('hyro.admin.route.prefix'))
    ->middleware(['web', 'auth', 'custom-middleware']) // Add custom middleware
    ->name('hyro.admin.')
    ->group(function () {
        // Add custom routes
        Route::get('/custom-dashboard', [CustomDashboardController::class, 'index'])
            ->name('custom.dashboard');
            
        // Override existing routes
        Route::get('/dashboard', [MyDashboardController::class, 'index'])
            ->name('dashboard');
    });
```

### Reverting Routes

```bash
rm -rf routes/hyro/
```

---

## 🎨 Smart View Loading

### Default Behavior

Views automatically load from the package:

```
vendor/marufsharia/hyro/resources/views/
├── admin/
│   ├── dashboard/
│   │   └── dashboard.blade.php
│   ├── layouts/
│   │   └── app.blade.php
│   ├── users/
│   ├── roles/
│   └── privileges/
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── passwords/
└── components/
```

### Publishing Views

```bash
php artisan vendor:publish --tag=hyro-views
```

Creates:

```
resources/views/vendor/hyro/
├── admin/
├── auth/
└── components/
```

### Customizing Views

#### Full Customization

Publish all views and modify as needed:

```bash
php artisan vendor:publish --tag=hyro-views
```

Edit any view:

```blade
{{-- resources/views/vendor/hyro/admin/dashboard/dashboard.blade.php --}}

@extends('hyro::admin.layouts.app')

@section('content')
    <div class="custom-dashboard">
        {{-- Your custom dashboard content --}}
    </div>
@endsection
```

#### Partial Customization

Only publish specific views you want to customize:

```bash
# Publish all views first
php artisan vendor:publish --tag=hyro-views

# Then delete views you don't need to customize
# Keep only the ones you want to modify
```

Example structure for partial customization:

```
resources/views/vendor/hyro/
├── admin/
│   ├── dashboard/
│   │   └── dashboard.blade.php  # Customized
│   └── layouts/
│       └── app.blade.php         # Customized
# Other views will load from package
```

### View Fallback

The smart view loading system supports fallback:

1. **First:** Check `resources/views/vendor/hyro/`
2. **Then:** Check `vendor/marufsharia/hyro/resources/views/`

This means you can customize only specific views, and the rest will load from the package.

### Using Views in Your Code

```blade
{{-- Use Hyro views with the hyro:: namespace --}}
@extends('hyro::admin.layouts.app')

{{-- Include Hyro components --}}
@include('hyro::components.alert')

{{-- Use Hyro components --}}
<x-hyro-card title="My Card">
    Content here
</x-hyro-card>
```

### Reverting Views

```bash
rm -rf resources/views/vendor/hyro/
```

---

## 🎭 Smart Asset Loading

### Default Behavior

Assets are compiled and should be published to work correctly:

```
vendor/marufsharia/hyro/public/build/
├── manifest.json
├── assets/
│   ├── hyro-[hash].css
│   └── hyro-[hash].js
└── images/
```

### Publishing Assets

```bash
php artisan vendor:publish --tag=hyro-assets
```

Creates:

```
public/vendor/hyro/
├── manifest.json
├── assets/
│   ├── hyro-[hash].css
│   └── hyro-[hash].js
└── images/
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
{{-- Get asset URL --}}
<link rel="stylesheet" href="{{ \Marufsharia\Hyro\Helpers\HyroAsset::css() }}">
<script src="{{ \Marufsharia\Hyro\Helpers\HyroAsset::js() }}"></script>

{{-- Get image URL --}}
<img src="{{ \Marufsharia\Hyro\Helpers\HyroAsset::image('logo.png') }}" alt="Logo">

{{-- Get custom asset --}}
<link rel="stylesheet" href="{{ \Marufsharia\Hyro\Helpers\HyroAsset::asset('custom.css') }}">
```

#### Direct Asset URLs

```blade
{{-- CSS --}}
<link rel="stylesheet" href="{{ asset('vendor/hyro/assets/hyro.css') }}">

{{-- JS --}}
<script src="{{ asset('vendor/hyro/assets/hyro.js') }}"></script>

{{-- Images --}}
<img src="{{ asset('vendor/hyro/images/logo.png') }}" alt="Logo">
```

### Customizing Assets

#### Option 1: Modify Published Assets

```bash
# Publish assets
php artisan vendor:publish --tag=hyro-assets

# Modify CSS
# Edit: public/vendor/hyro/assets/hyro.css

# Modify JS
# Edit: public/vendor/hyro/assets/hyro.js
```

#### Option 2: Override with Your Own Assets

```blade
{{-- In your layout, load your custom assets after Hyro assets --}}
@hyroAssets

{{-- Your custom overrides --}}
<link rel="stylesheet" href="{{ asset('css/hyro-overrides.css') }}">
<script src="{{ asset('js/hyro-overrides.js') }}"></script>
```

### Asset Compilation

If you're developing Hyro or need to recompile assets:

```bash
cd packages/marufsharia/hyro
npm install
npm run build

# Then publish the compiled assets
php artisan vendor:publish --tag=hyro-assets --force
```

### Checking Asset Status

```php
use Marufsharia\Hyro\Helpers\HyroAsset;

// Check if assets are published
if (HyroAsset::areAssetsPublished()) {
    echo "Assets are published";
} else {
    echo "Assets are loading from package";
}
```

---

## 🔧 Implementation Details

### HyroServiceProvider Methods

#### `loadSmartRoutes(string $routeFile): void`

Loads routes with smart detection:

```php
private function loadSmartRoutes(string $routeFile): void
{
    $publishedRoute = base_path("routes/hyro/{$routeFile}");
    $packageRoute = __DIR__ . "/../routes/{$routeFile}";

    if (File::exists($publishedRoute)) {
        $this->loadRoutesFrom($publishedRoute);
    } elseif (File::exists($packageRoute)) {
        $this->loadRoutesFrom($packageRoute);
    }
}
```

#### `loadSmartViews(): void`

Loads views with fallback support:

```php
private function loadSmartViews(): void
{
    $publishedViews = resource_path('views/vendor/hyro');
    $packageViews = __DIR__ . '/../resources/views';

    if (File::exists($publishedViews)) {
        // Load published views first (higher priority)
        $this->loadViewsFrom($publishedViews, 'hyro');
        
        // Also load package views as fallback
        if (File::exists($packageViews)) {
            $this->loadViewsFrom($packageViews, 'hyro');
        }
    } elseif (File::exists($packageViews)) {
        // Load only package views (default)
        $this->loadViewsFrom($packageViews, 'hyro');
    }
}
```

### HyroAsset Helper Methods

#### `asset(string $entry): ?string`

Gets asset URL with smart loading:

```php
public static function asset(string $entry): ?string
{
    $manifest = static::manifest();

    if (!isset($manifest[$entry])) {
        // Try direct path
        $directPath = public_path('vendor/hyro/' . $entry);
        if (File::exists($directPath)) {
            return asset('vendor/hyro/' . $entry);
        }
        return null;
    }

    return asset('vendor/hyro/' . $manifest[$entry]['file']);
}
```

#### `image(string $imagePath): string`

Gets image URL with smart loading:

```php
public static function image(string $imagePath): string
{
    $publishedImage = public_path('vendor/hyro/images/' . $imagePath);
    
    if (File::exists($publishedImage)) {
        return asset('vendor/hyro/images/' . $imagePath);
    }

    return asset('vendor/hyro/images/' . $imagePath);
}
```

---

## 📊 Resource Loading Matrix

| Resource Type | Published Location | Package Location | Priority |
|---------------|-------------------|------------------|----------|
| Routes | `routes/hyro/*.php` | `vendor/.../routes/*.php` | Published first |
| Views | `resources/views/vendor/hyro/**` | `vendor/.../resources/views/**` | Published first, package fallback |
| Assets | `public/vendor/hyro/**` | `vendor/.../public/build/**` | Published only |
| Config | `config/hyro.php` | `vendor/.../config/hyro.php` | Published overrides |

---

## 🎯 Best Practices

### 1. Selective Publishing

Only publish resources you need to customize:

```bash
# Don't publish everything if you only need to customize routes
php artisan vendor:publish --tag=hyro-routes

# Not: php artisan vendor:publish --provider="..."
```

### 2. Version Control

Add published resources to version control:

```gitignore
# .gitignore

# Keep published Hyro resources
!routes/hyro/
!resources/views/vendor/hyro/
!public/vendor/hyro/
```

### 3. Documentation

Document your customizations:

```php
// routes/hyro/admin.php

/**
 * CUSTOMIZATION: Added custom dashboard route
 * Date: 2026-02-09
 * Reason: Client requested custom analytics dashboard
 */
Route::get('/analytics', [AnalyticsController::class, 'index'])
    ->name('analytics');
```

### 4. Update Strategy

When updating Hyro:

```bash
# 1. Backup your customizations
cp -r routes/hyro routes/hyro.backup
cp -r resources/views/vendor/hyro resources/views/vendor/hyro.backup

# 2. Update package
composer update marufsharia/hyro

# 3. Check for changes in package
# Compare your customizations with new package files

# 4. Republish if needed
php artisan vendor:publish --tag=hyro-views --force

# 5. Reapply your customizations
```

### 5. Testing

Always test after customizing:

```bash
# Test routes
php artisan route:list --path=admin/hyro

# Test views
php artisan view:clear
# Visit admin panel in browser

# Test assets
# Check browser console for asset loading errors
```

---

## 🔍 Troubleshooting

### Routes Not Loading

**Problem:** Custom routes not working

**Solution:**
```bash
# Clear route cache
php artisan route:clear

# Check if routes exist
ls -la routes/hyro/

# Verify route file syntax
php artisan route:list
```

### Views Not Rendering

**Problem:** Custom views not showing

**Solution:**
```bash
# Clear view cache
php artisan view:clear

# Check if views exist
ls -la resources/views/vendor/hyro/

# Check view namespace
# Use: hyro::admin.dashboard.dashboard
# Not: admin.dashboard.dashboard
```

### Assets Not Loading

**Problem:** CSS/JS not loading

**Solution:**
```bash
# Publish assets
php artisan vendor:publish --tag=hyro-assets --force

# Check if assets exist
ls -la public/vendor/hyro/

# Check manifest
cat public/vendor/hyro/manifest.json

# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
```

### Mixed Content (Published + Package)

**Problem:** Some views from published, some from package

**Solution:**
This is expected behavior! The smart view loading supports partial customization. Only publish views you need to customize.

---

## 📈 Performance Considerations

### View Loading

- **Published views:** Loaded first (higher priority)
- **Package views:** Loaded as fallback (no performance impact)
- **Caching:** Laravel caches compiled views automatically

### Asset Loading

- **Manifest:** Cached after first read
- **Asset URLs:** Generated once per request
- **Browser caching:** Enabled via versioned filenames

### Route Loading

- **Route caching:** Use `php artisan route:cache` in production
- **Smart loading:** Minimal overhead (single file check)

---

## 🚀 Advanced Usage

### Custom View Namespaces

Register additional view namespaces:

```php
// In your AppServiceProvider

public function boot()
{
    // Add custom view namespace
    View::addNamespace('my-hyro', resource_path('views/my-hyro-customizations'));
}
```

### Dynamic Asset Loading

Load assets conditionally:

```blade
@if(\Marufsharia\Hyro\Helpers\HyroAsset::areAssetsPublished())
    {{-- Use published assets --}}
    @hyroAssets
@else
    {{-- Use CDN or alternative assets --}}
    <link rel="stylesheet" href="https://cdn.example.com/hyro.css">
@endif
```

### Custom Asset Paths

Override asset paths in config:

```php
// config/hyro.php

'assets' => [
    'url' => env('HYRO_ASSET_URL', '/vendor/hyro'),
    'path' => env('HYRO_ASSET_PATH', public_path('vendor/hyro')),
],
```

---

## 📚 Related Documentation

- [SMART_ROUTE_LOADING.md](SMART_ROUTE_LOADING.md) - Detailed route loading documentation
- [INSTALLATION.md](INSTALLATION.md) - Installation and setup guide
- [CONFIGURATION.md](CONFIGURATION.md) - Configuration options
- [USAGE.md](USAGE.md) - Usage examples and patterns

---

## ✅ Summary

The Smart Resource Loading system provides:

✅ **Flexibility** - Customize only what you need  
✅ **Safety** - Never modify vendor files  
✅ **Performance** - Efficient loading with caching  
✅ **Maintainability** - Easy updates and rollbacks  
✅ **Developer Experience** - Intuitive and well-documented  

---

**Last Updated:** February 9, 2026  
**Version:** 1.0.0  
**Status:** Production Ready
