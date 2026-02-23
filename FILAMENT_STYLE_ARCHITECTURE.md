# Filament-Style Asset Architecture

## Overview

Hyro v2.0.0 introduces a Filament-inspired asset management system that pro vides:

- **Centralized Asset Registration**: Register styles and scripts programmatically
- **Plugin Support**: Plugins can register their own assets
- **Zero Configuration**: Works out of the box with CDN fallbacks
- **Extensible**: Support for multi-panel and custom asset management

## Architecture Components

### 1. AssetManager (Core)

The `AssetManager` class provides a centralized registry for all assets:

```php
use Marufsharia\Hyro\Core\Support\Assets\AssetManager;

// Register a stylesheet
AssetManager::registerStyle('my-style', asset('css/custom.css'));

// Register a script
AssetManager::registerScript('my-script', asset('js/custom.js'), ['defer' => true]);

// Register inline CSS
AssetManager::registerInlineStyle('custom-utilities', '
    .my-class { color: red; }
');

// Register inline JavaScript
AssetManager::registerInlineScript('custom-init', '
    console.log("Initialized");
');
```

### 2. AssetHelper (Core)

The `AssetHelper` class manages manifest reading and asset URL resolution:

```php
use Marufsharia\Hyro\Core\Support\Assets\AssetHelper;

// Get versioned CSS URL
$cssUrl = AssetHelper::getCssUrl();

// Get versioned JS URL
$jsUrl = AssetHelper::getJsUrl();

// Check if built assets exist
if (AssetHelper::hasBuiltAssets()) {
    // Use built assets
}
```

### 3. Blade Component

The `@hyroAssets` directive renders all registered assets:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>My App</title>
    @hyroAssets
</head>
<body>
    <!-- Your content -->
</body>
</html>
```

This single directive replaces the need for multiple `@hyroCss` and `@hyroJs` directives.

## Build Process

### Directory Structure

```
packages/marufsharia/hyro/
├── resources/
│   ├── css/
│   │   └── hyro.css          # Source CSS
│   └── js/
│       └── hyro.js            # Source JS
├── dist/                      # Built assets (committed to repo)
│   ├── manifest.json          # Vite manifest
│   ├── css/
│   │   └── hyro-[hash].css   # Compiled CSS
│   └── js/
│       └── hyro-[hash].js    # Compiled JS
└── vite.config.js             # Vite configuration
```

### Building Assets

```bash
cd packages/marufsharia/hyro
npm install
npm run build
```

This builds assets to the `dist/` directory with:
- Versioned filenames (cache busting)
- Minified CSS and JS
- Source maps
- Manifest file for asset resolution

### Important: Commit Built Assets

Unlike typical Laravel applications, the `dist/` directory should be committed to the repository. This allows users to install the package without needing to build assets themselves.

```bash
git add dist/
git commit -m "Build assets for v2.0.0"
```

## Installation Flow

### For End Users

1. **Install via Composer**:
   ```bash
   composer require marufsharia/hyro
   ```

2. **Run Install Command**:
   ```bash
   php artisan hyro:install
   ```

   This command:
   - Publishes configuration
   - Publishes assets from `dist/` to `public/vendor/hyro/`
   - Publishes migrations
   - Adds `HasHyroFeatures` trait to User model

3. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

4. **Create Admin User**:
   ```bash
   php artisan hyro:user:create --admin
   ```

### No Build Step Required

Users do NOT need to:
- Install Node.js
- Run `npm install`
- Run `npm run build`

The package includes pre-built assets in the `dist/` directory.

## CDN Fallback

If built assets are not available, Hyro automatically falls back to CDN assets:

- **Tailwind CSS**: Loaded from `https://cdn.tailwindcss.com`
- **Alpine.js**: Loaded from `https://cdn.jsdelivr.net/npm/alpinejs@3.x.x`
- **Alpine Plugins**: Collapse, Focus, Intersect from CDN

This ensures the package works even if:
- Assets haven't been published
- Build process failed
- User deleted published assets

## Plugin Asset Registration

Plugins can register their own assets:

```php
namespace MyVendor\MyPlugin;

use Marufsharia\Hyro\Core\Support\Assets\AssetManager;
use Illuminate\Support\ServiceProvider;

class MyPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register plugin CSS
        AssetManager::registerStyle(
            'my-plugin',
            asset('vendor/my-plugin/css/plugin.css')
        );

        // Register plugin JS
        AssetManager::registerScript(
            'my-plugin',
            asset('vendor/my-plugin/js/plugin.js'),
            ['defer' => true]
        );
    }
}
```

## Multi-Panel Support

Each panel can register its own assets:

```php
// Admin Panel
AssetManager::registerStyle('admin-panel', asset('vendor/hyro/css/admin.css'));

// User Panel
AssetManager::registerStyle('user-panel', asset('vendor/hyro/css/user.css'));

// API Documentation Panel
AssetManager::registerStyle('api-docs', asset('vendor/hyro/css/api-docs.css'));
```

The `@hyroAssets` directive renders all registered assets, regardless of which panel registered them.

## Backward Compatibility

The legacy directives still work for backward compatibility:

```blade
@hyroCss  {{-- Legacy: Outputs CSS tags --}}
@hyroJs   {{-- Legacy: Outputs JS tags --}}
@hyroImage('logo.png')  {{-- Legacy: Outputs image URL --}}
```

However, the new `@hyroAssets` directive is recommended:

```blade
@hyroAssets  {{-- New: Outputs all registered assets --}}
```

## Commands

### hyro:install

Install Hyro with all necessary assets and configurations:

```bash
php artisan hyro:install
php artisan hyro:install --force          # Overwrite existing files
php artisan hyro:install --no-assets      # Skip asset publishing
php artisan hyro:install --no-migrations  # Skip migration publishing
```

### hyro:publish-assets

Manually publish assets:

```bash
php artisan hyro:publish-assets
php artisan hyro:publish-assets --force   # Overwrite existing files
```

## Comparison with Filament

| Feature | Filament | Hyro v2.0 |
|---------|----------|-----------|
| Asset Registration | ✅ Programmatic | ✅ Programmatic |
| Build Directory | `/dist` | ✅ `/dist` |
| Plugin Support | ✅ Yes | ✅ Yes |
| CDN Fallback | ❌ No | ✅ Yes |
| Multi-Panel | ✅ Yes | ✅ Yes |
| Zero Config | ✅ Yes | ✅ Yes |
| Pre-built Assets | ✅ Committed | ✅ Committed |

## Migration from v1.x

### Update Your Layouts

**Before (v1.x)**:
```blade
<head>
    @hyroCss
    @livewireStyles
</head>
<body>
    <!-- Content -->
    @hyroJs
    @livewireScripts
</body>
```

**After (v2.0)**:
```blade
<head>
    @hyroAssets
    @livewireStyles
</head>
<body>
    <!-- Content -->
    @livewireScripts
</body>
```

### Update Asset Publishing

**Before (v1.x)**:
```bash
php artisan vendor:publish --tag=hyro-assets
```

**After (v2.0)**:
```bash
php artisan hyro:install
# or
php artisan hyro:publish-assets
```

### Plugin Asset Registration

**Before (v1.x)**:
Plugins had to manually publish assets and include them in views.

**After (v2.0)**:
```php
use Marufsharia\Hyro\Core\Support\Assets\AssetManager;

AssetManager::registerStyle('my-plugin', asset('vendor/my-plugin/css/plugin.css'));
AssetManager::registerScript('my-plugin', asset('vendor/my-plugin/js/plugin.js'));
```

## Benefits

1. **Simplified Installation**: One command installs everything
2. **No Build Requirements**: Users don't need Node.js
3. **Automatic Updates**: Assets auto-publish on package update
4. **Plugin Friendly**: Plugins can easily add their own assets
5. **CDN Fallback**: Works even without published assets
6. **Cache Busting**: Versioned filenames prevent cache issues
7. **Extensible**: Easy to add custom assets programmatically

## Best Practices

1. **Always build before releasing**: Run `npm run build` before tagging a new version
2. **Commit dist directory**: Include built assets in the repository
3. **Use AssetManager**: Register assets programmatically, not hardcoded in views
4. **Test CDN fallback**: Ensure the package works without published assets
5. **Version your assets**: Use the manifest for cache busting
6. **Document plugin assets**: Tell plugin users how to register their assets

## Troubleshooting

### Assets not loading

1. Check if assets are published:
   ```bash
   ls -la public/vendor/hyro/
   ```

2. Republish assets:
   ```bash
   php artisan hyro:publish-assets --force
   ```

3. Check if CDN fallback is working:
   - View page source
   - Look for `cdn.tailwindcss.com` and `cdn.jsdelivr.net`

### Build errors

1. Clear node_modules and reinstall:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

2. Clear Vite cache:
   ```bash
   rm -rf dist/
   npm run build
   ```

### Plugin assets not loading

1. Ensure plugin is registered in service provider
2. Check asset paths are correct
3. Verify assets are published to `public/vendor/`

## Future Enhancements

- [ ] Asset versioning API
- [ ] Asset preloading hints
- [ ] Critical CSS extraction
- [ ] Asset bundling optimization
- [ ] Theme system integration
- [ ] Dark mode asset variants
