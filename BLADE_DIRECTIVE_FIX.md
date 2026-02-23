# Blade Directive Fix - v1.0.5

## Issue Fixed
The `@hyroCss`, `@hyroJs`, and `@hyroAssets` Blade directives were not outputting any CSS/JS tags in the HTML.

## Root Cause
The directives in `HyroServiceProvider` were referencing the wrong namespace:
- **Wrong**: `\Marufsharia\Hyro\Helpers\HyroAsset`
- **Correct**: `\Marufsharia\Hyro\Core\Helpers\HyroAsset`

## What Was Fixed
Updated the Blade directive registrations in `src/HyroServiceProvider.php` to use the correct namespace path for the `HyroAsset` helper class.

## Testing the Fix

### 1. Update Your Project
```bash
composer update marufsharia/hyro
```

This will pull version `v1.0.5` which includes the fix.

### 2. Clear Caches
```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
```

### 3. Verify Assets Are Published
```bash
php artisan hyro:publish-assets
```

Or use the standard publish command:
```bash
php artisan vendor:publish --tag=hyro-assets
```

### 4. Check Your HTML Output
Visit your admin panel and view the page source. You should now see:

```html
<link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/assets/hyro-ayW2hTEZ.css">
```

And at the bottom:
```html
<script type="module" src="http://127.0.0.1:8000/vendor/hyro/assets/hyro-D-y3am57.js"></script>
```

## Expected Behavior

### Before Fix
```html
<!-- Nothing was output by @hyroCss -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">
```

### After Fix
```html
<link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/assets/hyro-ayW2hTEZ.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">
```

## How the HyroAsset Helper Works

The `HyroAsset` class uses a smart loading strategy:

1. **Checks for published assets** in `public/vendor/hyro/manifest.json`
2. **Reads the manifest** to get versioned asset filenames (e.g., `hyro-ayW2hTEZ.css`)
3. **Returns the full asset URL** using Laravel's `asset()` helper
4. **Falls back to direct paths** if manifest entry doesn't exist

## Manifest Structure

The `public/vendor/hyro/manifest.json` file maps source files to built assets:

```json
{
  "resources/css/hyro.css": {
    "file": "assets/hyro-ayW2hTEZ.css",
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

## Troubleshooting

### Still Not Seeing CSS?

1. **Check if assets are published:**
   ```bash
   ls public/vendor/hyro
   ```
   You should see: `manifest.json`, `assets/`, `css/`, `js/`, `images/`

2. **Check manifest exists:**
   ```bash
   cat public/vendor/hyro/manifest.json
   ```

3. **Re-publish assets:**
   ```bash
   php artisan vendor:publish --tag=hyro-assets --force
   ```

4. **Clear all caches:**
   ```bash
   php artisan optimize:clear
   ```

5. **Check browser console** for 404 errors on asset files

### Assets Published But Still Not Loading?

Make sure your layout file uses the directive correctly:
```blade
@hyroCss
@livewireStyles
```

Not:
```blade
{{ @hyroCss }}  <!-- Wrong! -->
```

## Version History

- **v1.0.5** - Fixed Blade directive namespace
- **v1.0.4** - Fixed asset publishing paths
- **v1.0.3** - Fixed HasHyroFeatures trait namespace in docs
- **v1.0.2** - Added CLI commands
- **v1.0.1** - Fixed migration trait namespace
- **v1.0.0** - Initial release
