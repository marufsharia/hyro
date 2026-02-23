# Zero-Configuration Installation Guide

## Overview

As of v1.0.7, Hyro requires **ZERO manual configuration** for assets. Everything works automatically!

## Installation Steps

### Step 1: Install via Composer

```bash
composer require marufsharia/hyro
```

That's it! Assets are automatically published during installation.

### Step 2: Run Migrations

```bash
php artisan migrate
```

### Step 3: Create Admin User

```bash
php artisan hyro:user:create --admin
```

### Step 4: Start Using

```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000/hyro/admin/dashboard`

## What Happens Automatically?

### 1. Asset Publishing

When you run `composer require marufsharia/hyro`, the package automatically:

✅ Copies `manifest.json` to `public/vendor/hyro/`
✅ Copies compiled CSS/JS to `public/vendor/hyro/assets/`
✅ Copies fallback CSS/JS to `public/vendor/hyro/css/` and `public/vendor/hyro/js/`
✅ Creates necessary directories with proper permissions

### 2. Blade Directives Registration

The following directives are automatically available:

- `@hyroCss` - Loads versioned CSS file
- `@hyroJs` - Loads versioned JS file
- `@hyroAssets` - Loads both CSS and JS
- `@hyroImage('path/to/image.png')` - Loads package images

### 3. Service Provider Registration

Laravel automatically discovers and registers:

- `HyroServiceProvider` - Core functionality
- `AdminPanelServiceProvider` - Admin panel
- `CoreServiceProvider` - Authorization system
- `AuthServiceProvider` - Authentication
- `CrudServiceProvider` - CRUD operations
- `ApiServiceProvider` - API endpoints
- `PluginServiceProvider` - Plugin system

## Using Assets in Your Layouts

### In Package Views (Already Done)

The package views already use the directives:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>Hyro Admin</title>
    
    <!-- Automatically loads: /vendor/hyro/assets/hyro-[hash].css -->
    @hyroCss
    
    @livewireStyles
</head>
<body>
    {{ $slot }}
    
    <!-- Automatically loads: /vendor/hyro/assets/hyro-[hash].js -->
    @hyroJs
    
    @livewireScripts
</body>
</html>
```

### In Your Custom Views (Optional)

If you want to use Hyro styles in your own views:

```blade
<!DOCTYPE html>
<html>
<head>
    @hyroCss
</head>
<body>
    <div class="hyro-card">
        Your content here
    </div>
    
    @hyroJs
</body>
</html>
```

### Using Package Images

If the package includes images (like logos):

```blade
<img src="@hyroImage('logo.png')" alt="Logo">
```

This automatically resolves to: `/vendor/hyro/images/logo.png`

## How It Works

### The Magic Behind Auto-Publishing

**AdminPanelServiceProvider.php:**

```php
public function boot(): void
{
    // Auto-publish assets on package installation/update
    $this->autoPublishAssets();
    
    // ... rest of boot logic
}

private function autoPublishAssets(): void
{
    // Check if assets are already published
    if (File::exists(public_path('vendor/hyro/manifest.json'))) {
        return; // Already published, skip
    }

    // Only auto-publish during composer operations
    if ($this->app->runningInConsole()) {
        $this->publishAssetsAutomatically();
    }
}
```

### The Asset Resolution Flow

```
User visits page
    ↓
Blade renders @hyroCss
    ↓
HyroAsset::css() is called
    ↓
Reads public/vendor/hyro/manifest.json
    ↓
Finds: "resources/css/hyro.css" → "assets/hyro-HPBdpIdl.css"
    ↓
Generates: <link rel="stylesheet" href="/vendor/hyro/assets/hyro-HPBdpIdl.css">
    ↓
Browser loads versioned CSS file
```

## Manual Asset Publishing (Optional)

If you ever need to manually republish assets:

```bash
# Method 1: Custom command
php artisan hyro:publish-assets --force

# Method 2: Laravel publish
php artisan vendor:publish --tag=hyro-assets --force

# Method 3: Publish everything
php artisan vendor:publish --provider="Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider" --force
```

## Verifying Installation

### Check Assets Are Published

```bash
# Windows
dir public\vendor\hyro

# Linux/Mac
ls -la public/vendor/hyro
```

You should see:
```
manifest.json
assets/
  hyro-[hash].css
  hyro-[hash].js
css/
  hyro-alert.css
js/
  hyro-alert.js
images/
```

### Check Manifest Content

```bash
# Windows
type public\vendor\hyro\manifest.json

# Linux/Mac
cat public/vendor/hyro/manifest.json
```

Should show:
```json
{
  "resources/css/hyro.css": {
    "file": "assets/hyro-HPBdpIdl.css",
    "src": "resources/css/hyro.css",
    "isEntry": true
  },
  "resources/js/hyro.js": {
    "file": "assets/hyro-D-y3am57.js",
    "src": "resources/js/hyro.js",
    "isEntry": true
  }
}
```

### Test in Browser

1. Start server: `php artisan serve`
2. Visit: `http://127.0.0.1:8000/hyro/admin/dashboard`
3. View page source (Ctrl+U)
4. Look for:

```html
<link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/assets/hyro-HPBdpIdl.css">
<script type="module" src="http://127.0.0.1:8000/vendor/hyro/assets/hyro-D-y3am57.js"></script>
```

5. Check browser console (F12) - should have NO 404 errors

## Troubleshooting

### Assets Not Loading?

**Symptom**: Page has no styling, console shows 404 errors

**Solution**:
```bash
# Manually publish assets
php artisan hyro:publish-assets --force

# Clear caches
php artisan optimize:clear

# Restart server
php artisan serve
```

### Wrong CSS Loading?

**Symptom**: Seeing fallback CSS (hyro-alert.css) instead of versioned CSS

**Solution**:
```bash
# Check if manifest exists
dir public\vendor\hyro\manifest.json

# If missing, republish
php artisan hyro:publish-assets --force
```

### Permission Errors?

**Symptom**: "Permission denied" when publishing assets

**Solution**:
```bash
# Windows (run as Administrator)
icacls public\vendor /grant Users:F /t

# Linux/Mac
chmod -R 755 public/vendor
```

## Comparison: Before vs After

### Before v1.0.7 (Manual)

```bash
# Install
composer require marufsharia/hyro

# Publish config
php artisan vendor:publish --tag=hyro-config

# Publish assets
php artisan vendor:publish --tag=hyro-assets

# Publish views
php artisan vendor:publish --tag=hyro-views

# Run migrations
php artisan migrate

# Create user
php artisan hyro:user:create --admin
```

### After v1.0.7 (Automatic)

```bash
# Install
composer require marufsharia/hyro

# Run migrations
php artisan migrate

# Create user
php artisan hyro:user:create --admin
```

**3 steps eliminated!** ✅

## Benefits of Zero-Configuration

✅ **Faster setup** - Get started in seconds
✅ **No manual steps** - Assets publish automatically
✅ **No errors** - Can't forget to publish assets
✅ **Better UX** - Works out of the box
✅ **Laravel standard** - Follows package best practices

## Advanced: Customizing Assets

If you want to customize the CSS/JS:

### Step 1: Publish Views (Optional)

```bash
php artisan vendor:publish --tag=hyro-views
```

This copies views to `resources/views/vendor/hyro/`

### Step 2: Modify Layouts

Edit `resources/views/vendor/hyro/layouts/app.blade.php`:

```blade
<head>
    @hyroCss
    
    <!-- Your custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom-hyro.css') }}">
</head>
```

### Step 3: Add Custom Styles

Create `public/css/custom-hyro.css`:

```css
/* Override Hyro styles */
.hyro-card {
    background: linear-gradient(to right, #667eea, #764ba2);
}
```

## Summary

**Installation is now as simple as:**

1. `composer require marufsharia/hyro`
2. `php artisan migrate`
3. `php artisan hyro:user:create --admin`
4. Done! 🎉

No asset publishing, no configuration, no hassle. Just install and use!

## Need Help?

If assets still aren't loading after following this guide:

1. Check `storage/logs/laravel.log` for errors
2. Verify file permissions on `public/vendor/hyro/`
3. Try manual publishing: `php artisan hyro:publish-assets --force`
4. Clear all caches: `php artisan optimize:clear`
5. Restart your development server

## Version History

- **v1.0.7** - Zero-configuration installation with auto-publishing
- **v1.0.6** - Fixed asset publishing with Vite-built assets
- **v1.0.5** - Fixed Blade directives and HyroAsset helper
- **v1.0.4** - Fixed asset publishing paths
- **v1.0.3** - Fixed HasHyroFeatures trait namespace
- **v1.0.2** - Added CLI commands
- **v1.0.1** - Fixed migration trait namespace
- **v1.0.0** - Initial release
