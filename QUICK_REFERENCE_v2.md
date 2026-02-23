# Hyro v2.0 Quick Reference Guide

## Installation

```bash
# Install package
composer require marufsharia/hyro

# Run installation command
php artisan hyro:install

# Run migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin
```

## Blade Directives

### New (Recommended)
```blade
<!DOCTYPE html>
<html>
<head>
    @hyroAssets  {{-- Renders all registered assets --}}
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
```

### Legacy (Still Works)
```blade
<head>
    @hyroCss
    @hyroJs
    @livewireStyles
</head>
```

## Asset Registration

### Register Stylesheet
```php
use Marufsharia\Hyro\Core\Support\Assets\AssetManager;

AssetManager::registerStyle('my-style', asset('css/custom.css'));

// With attributes
AssetManager::registerStyle('my-style', asset('css/custom.css'), [
    'media' => 'print',
]);
```

### Register Script
```php
AssetManager::registerScript('my-script', asset('js/custom.js'));

// With attributes
AssetManager::registerScript('my-script', asset('js/custom.js'), [
    'defer' => true,
    'type' => 'module',
]);
```

### Register Inline CSS
```php
AssetManager::registerInlineStyle('custom-utilities', '
    .my-class { color: red; }
    .my-other-class { background: blue; }
');
```

### Register Inline JavaScript
```php
AssetManager::registerInlineScript('custom-init', '
    console.log("Initialized");
    window.myApp = {};
');
```

## Plugin Asset Registration

```php
namespace MyVendor\MyPlugin;

use Illuminate\Support\ServiceProvider;
use Marufsharia\Hyro\Core\Support\Assets\AssetManager;

class MyPluginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register plugin assets
        AssetManager::registerStyle(
            'my-plugin',
            asset('vendor/my-plugin/css/plugin.css')
        );
        
        AssetManager::registerScript(
            'my-plugin',
            asset('vendor/my-plugin/js/plugin.js'),
            ['defer' => true]
        );
    }
}
```

## Asset Helper

```php
use Marufsharia\Hyro\Core\Support\Assets\AssetHelper;

// Get versioned CSS URL
$cssUrl = AssetHelper::getCssUrl();

// Get versioned JS URL
$jsUrl = AssetHelper::getJsUrl();

// Check if built assets exist
if (AssetHelper::hasBuiltAssets()) {
    // Use built assets
} else {
    // Use CDN fallback
}

// Get manifest
$manifest = AssetHelper::getManifest();
```

## Commands

### Install Hyro
```bash
php artisan hyro:install
php artisan hyro:install --force          # Overwrite existing files
php artisan hyro:install --no-assets      # Skip asset publishing
php artisan hyro:install --no-migrations  # Skip migration publishing
php artisan hyro:install --no-seed        # Skip running HyroInstallSeeder
```

This command will:
1. Publish configuration files
2. Publish assets to public/vendor/hyro
3. Publish migrations
4. Add HasHyroFeatures trait to User model
5. Run migrations (with confirmation)
6. Seed default roles and privileges (with confirmation)

The seeder creates:
- **Roles**: Super Administrator, Administrator, Editor, User
- **Privileges**: Hyro access, role management, user management, content management

### Publish Assets
```bash
php artisan hyro:publish-assets
php artisan hyro:publish-assets --force   # Overwrite existing files
```

### User Management
```bash
php artisan hyro:user:create              # Create regular user
php artisan hyro:user:create --admin      # Create admin user
php artisan hyro:user:list                # List all users
```

### Role Management
```bash
php artisan hyro:role:create              # Create role
php artisan hyro:role:list                # List all roles
```

### Database Seeding
```bash
php artisan hyro:seed                     # Seed default roles and privileges
php artisan hyro:seed --force             # Force re-seed even if roles exist
```

## Building Assets (For Package Developers)

```bash
# Navigate to package directory
cd packages/marufsharia/hyro

# Install dependencies
npm install

# Build for production
npm run build

# Build for development (with watch)
npm run dev
```

## Directory Structure

```
packages/marufsharia/hyro/
├── resources/              # Source files
│   ├── css/
│   │   └── hyro.css       # Source CSS
│   └── js/
│       └── hyro.js        # Source JS
├── dist/                  # Built assets (committed)
│   ├── manifest.json      # Vite manifest
│   ├── css/
│   │   └── hyro-[hash].css
│   └── js/
│       └── hyro-[hash].js
├── core/
│   └── src/
│       └── Support/
│           └── Assets/
│               ├── AssetManager.php
│               └── AssetHelper.php
└── vite.config.js         # Vite configuration
```

## Published Assets

```
public/vendor/hyro/
├── manifest.json          # Vite manifest
├── css/
│   └── hyro-[hash].css   # Compiled CSS
└── js/
    └── hyro-[hash].js    # Compiled JS
```

## CDN Fallback

If built assets are not available, Hyro automatically loads:

- **Tailwind CSS**: `https://cdn.tailwindcss.com`
- **Alpine.js**: `https://cdn.jsdelivr.net/npm/alpinejs@3.x.x`
- **Alpine Plugins**: Collapse, Focus, Intersect

## Common Tasks

### Add Custom Styles to Admin Panel
```php
// In your service provider
use Marufsharia\Hyro\Core\Support\Assets\AssetManager;

public function boot()
{
    AssetManager::registerStyle('admin-custom', asset('css/admin-custom.css'));
}
```

### Add Custom JavaScript
```php
AssetManager::registerScript('admin-custom', asset('js/admin-custom.js'), [
    'defer' => true,
]);
```

### Override Core Styles
```php
// Register your styles after core styles
AssetManager::registerStyle('my-overrides', asset('css/overrides.css'));
```

### Remove Registered Asset
```php
AssetManager::removeStyle('unwanted-style');
AssetManager::removeScript('unwanted-script');
```

### Check if Asset is Registered
```php
if (AssetManager::hasStyle('my-style')) {
    // Style is registered
}

if (AssetManager::hasScript('my-script')) {
    // Script is registered
}
```

### Clear All Assets
```php
AssetManager::clear();
```

## Troubleshooting

### Assets not loading
```bash
# Republish assets
php artisan hyro:publish-assets --force

# Clear caches
php artisan view:clear
php artisan cache:clear

# Check if assets exist
ls -la public/vendor/hyro/
```

### Build errors
```bash
# Clear node_modules
rm -rf node_modules package-lock.json
npm install

# Clear dist
rm -rf dist/
npm run build
```

### CDN fallback not working
```bash
# Check if manifest exists
cat public/vendor/hyro/manifest.json

# If missing, republish
php artisan hyro:publish-assets --force
```

## Migration from v1.x

### Update Composer
```bash
composer update marufsharia/hyro
```

### Reinstall Assets
```bash
php artisan hyro:install --force
```

### Update Layouts
Replace:
```blade
@hyroCss
@hyroJs
```

With:
```blade
@hyroAssets
```

### Clear Caches
```bash
php artisan view:clear
php artisan cache:clear
```

## Best Practices

1. **Use @hyroAssets**: Single directive for all assets
2. **Register assets in service providers**: Don't hardcode in views
3. **Use unique asset names**: Prevent conflicts with other packages
4. **Test CDN fallback**: Ensure package works without published assets
5. **Commit dist/ directory**: Include built assets in repository
6. **Version your assets**: Use manifest for cache busting

## Links

- **Documentation**: [FILAMENT_STYLE_ARCHITECTURE.md](FILAMENT_STYLE_ARCHITECTURE.md)
- **Release Notes**: [v2.0.0_RELEASE_NOTES.md](v2.0.0_RELEASE_NOTES.md)
- **GitHub**: https://github.com/marufsharia/hyro
- **Packagist**: https://packagist.org/packages/marufsharia/hyro

---

**Version**: 2.0.0
**Last Updated**: February 23, 2026
