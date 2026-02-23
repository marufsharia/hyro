# Composer Autoload Fix for Seeders

## Problem

The seeder class wasn't being autoloaded, causing this error:

```
Class "Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder" not found
```

## Root Cause

The `core/database/seeders/` directory wasn't included in Composer's PSR-4 autoload configuration.

## Solution

### 1. Updated composer.json Files

**Main Package** (`packages/marufsharia/hyro/composer.json`):
```json
{
    "autoload": {
        "psr-4": {
            "Marufsharia\\Hyro\\": "src/",
            "Marufsharia\\Hyro\\Core\\": "core/src/",
            "Marufsharia\\Hyro\\Core\\Database\\Seeders\\": "core/database/seeders/",
            "Marufsharia\\Hyro\\Auth\\": "auth/src/",
            "Marufsharia\\Hyro\\Api\\": "api/src/",
            "Marufsharia\\Hyro\\AdminPanel\\": "admin-panel/src/",
            "Marufsharia\\Hyro\\Crud\\": "crud/src/",
            "Marufsharia\\Hyro\\Plugin\\": "plugin/src/"
        }
    }
}
```

**Core Package** (`packages/marufsharia/hyro/core/composer.json`):
```json
{
    "autoload": {
        "psr-4": {
            "Marufsharia\\Hyro\\Core\\": "src/",
            "Marufsharia\\Hyro\\Core\\Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

### 2. Regenerate Autoload Files

After updating composer.json, you MUST regenerate the autoload files:

#### In the Package Directory

```bash
cd packages/marufsharia/hyro
composer dump-autoload
```

#### In the Application Directory (if testing locally)

```bash
cd /path/to/your/laravel/app
composer dump-autoload
```

## Verification

After running `composer dump-autoload`, verify the seeder can be loaded:

```bash
php artisan tinker
```

```php
>>> $seeder = new \Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder();
>>> get_class($seeder)
=> "Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder"
```

If this works, the autoload is fixed!

## Testing the Commands

### Test hyro:install

```bash
php artisan hyro:install
```

Should now work without the "Class not found" error.

### Test hyro:seed

```bash
php artisan hyro:seed
```

Should also work correctly.

## For Package Users

When users install the package via Composer, the autoload will work automatically:

```bash
composer require marufsharia/hyro
```

Composer automatically processes the `autoload` section and generates the correct autoload files.

## For Local Development

If you're developing the package locally using a path repository:

1. **Update composer.json in your test app**:
   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "../packages/marufsharia/hyro"
           }
       ]
   }
   ```

2. **Install/Update the package**:
   ```bash
   composer require marufsharia/hyro
   # or
   composer update marufsharia/hyro
   ```

3. **Dump autoload**:
   ```bash
   composer dump-autoload
   ```

## Why This Fix Works

### PSR-4 Autoloading

PSR-4 maps namespaces to directories:

```
Namespace: Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder
Directory: core/database/seeders/HyroInstallSeeder.php
```

By adding this to composer.json:
```json
"Marufsharia\\Hyro\\Core\\Database\\Seeders\\": "core/database/seeders/"
```

Composer knows where to find classes in the `Marufsharia\Hyro\Core\Database\Seeders` namespace.

### Autoload Generation

When you run `composer dump-autoload`, Composer:
1. Reads all `autoload` sections from all packages
2. Generates `vendor/composer/autoload_psr4.php`
3. Registers the namespace-to-directory mappings
4. PHP can now find and load the classes automatically

## Common Issues

### Issue 1: Still Getting "Class not found"

**Solution**: Run `composer dump-autoload` in both:
- Package directory: `cd packages/marufsharia/hyro && composer dump-autoload`
- Application directory: `cd /path/to/app && composer dump-autoload`

### Issue 2: Works in package but not in application

**Solution**: 
1. Remove the package: `composer remove marufsharia/hyro`
2. Clear composer cache: `composer clear-cache`
3. Reinstall: `composer require marufsharia/hyro`
4. Dump autoload: `composer dump-autoload`

### Issue 3: Local development path repository issues

**Solution**:
```bash
# In application directory
rm -rf vendor/marufsharia
composer dump-autoload
composer require marufsharia/hyro
```

## Best Practices

1. **Always include database/seeders in autoload** for packages
2. **Run composer dump-autoload** after changing composer.json
3. **Test locally** before pushing to repository
4. **Document autoload requirements** in package README

## Checklist Before Release

- [x] Added seeder namespace to main composer.json
- [x] Added seeder namespace to core/composer.json
- [ ] Run `composer dump-autoload` in package directory
- [ ] Test `php artisan hyro:install` in fresh Laravel app
- [ ] Test `php artisan hyro:seed` in fresh Laravel app
- [ ] Verify seeder can be instantiated in tinker
- [ ] Commit composer.json changes
- [ ] Tag and release

## Commands Summary

```bash
# In package directory
cd packages/marufsharia/hyro
composer dump-autoload

# Test in fresh Laravel app
cd /path/to/test/app
composer require marufsharia/hyro
php artisan hyro:install

# Verify seeder works
php artisan tinker
>>> new \Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder();
```

---

**Issue**: Seeder class not autoloaded
**Solution**: Added namespace to composer.json autoload
**Action Required**: Run `composer dump-autoload`
**Status**: ✅ Fixed (after running dump-autoload)
