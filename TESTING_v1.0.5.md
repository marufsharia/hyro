# Testing Guide for v1.0.5 - Blade Directive Fix

## What Was Fixed

### Issue
The `@hyroCss` and `@hyroJs` Blade directives were not outputting any HTML tags, causing the admin panel to load without Hyro styles.

### Root Causes Fixed
1. **Wrong namespace**: Directives referenced `\Marufsharia\Hyro\Helpers\HyroAsset` instead of `\Marufsharia\Hyro\Core\Helpers\HyroAsset`
2. **Null returns**: Helper returned `null` when assets not found, causing empty output
3. **String escaping**: Blade directive strings needed proper escaping for backslashes

### Solutions Applied
1. Fixed namespace to `\Marufsharia\Hyro\Core\Helpers\HyroAsset`
2. Changed return types from `?string` to `string` with HTML comment fallbacks
3. Added fallback logic to load `hyro-alert.css` and `hyro-alert.js` when manifest assets unavailable
4. Improved string escaping in Blade directive registration

## Step-by-Step Testing

### Step 1: Update the Package

In your test application (not the package directory):

```bash
cd F:\Web\Laravel\2026\hyro-test-app
composer update marufsharia/hyro
```

Expected output:
```
Loading composer repositories with package information
Updating dependencies
Lock file operations: 0 installs, 1 update, 0 removals
  - Upgrading marufsharia/hyro (v1.0.4 => v1.0.5)
```

### Step 2: Clear All Caches

```bash
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan optimize:clear
```

### Step 3: Verify Assets Are Published

Check if assets exist:
```bash
dir public\vendor\hyro
```

You should see:
- `manifest.json`
- `assets\` folder with CSS and JS files
- `css\` folder with `hyro-alert.css`
- `js\` folder with `hyro-alert.js`

If not, publish them:
```bash
php artisan hyro:publish-assets
```

Or:
```bash
php artisan vendor:publish --tag=hyro-assets --force
```

### Step 4: Test the Admin Panel

Start your development server:
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000/hyro/admin/dashboard`

### Step 5: Inspect the HTML Source

Right-click on the page and select "View Page Source" or press `Ctrl+U`.

Look for the `<head>` section. You should now see:

#### Expected Output (Success):

```html
<head>
    <!-- ... other head tags ... -->
    
    <!-- This should now appear! -->
    <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/assets/hyro-ayW2hTEZ.css">
    
    <!-- Livewire styles -->
    <style>[wire\:loading], [wire\:loading\.delay], ...</style>
</head>
```

And before `</body>`:

```html
    <!-- This should now appear! -->
    <script type="module" src="http://127.0.0.1:8000/vendor/hyro/assets/hyro-D-y3am57.js"></script>
    
    <!-- Livewire scripts -->
    <script src="/livewire/livewire.js?id=..." ...></script>
</body>
```

#### Fallback Output (If manifest not working):

```html
<head>
    <!-- Fallback CSS -->
    <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/css/hyro-alert.css">
</head>

<!-- ... -->

    <!-- Fallback JS -->
    <script src="http://127.0.0.1:8000/vendor/hyro/js/hyro-alert.js"></script>
</body>
```

#### Error Output (If assets not published):

```html
<head>
    <!-- Hyro CSS not found. Run: php artisan hyro:publish-assets -->
</head>

<!-- ... -->

    <!-- Hyro JS not found. Run: php artisan hyro:publish-assets -->
</body>
```

### Step 6: Check Browser Console

Open Developer Tools (F12) and check the Console tab.

#### Success:
- No 404 errors for CSS/JS files
- No errors about missing stylesheets

#### Failure:
- 404 errors like: `GET http://127.0.0.1:8000/vendor/hyro/assets/hyro-ayW2hTEZ.css 404 (Not Found)`

If you see 404 errors, the files aren't published correctly. Run:
```bash
php artisan hyro:publish-assets
```

### Step 7: Verify Styling

The admin panel should now have proper styling:
- Sidebar with dark background
- Gradient header with Hyro logo
- Proper button colors and hover effects
- Card shadows and borders
- Smooth transitions

## Troubleshooting

### Problem: Still seeing "Hyro CSS not found" comment

**Solution:**
```bash
# Re-publish assets
php artisan vendor:publish --tag=hyro-assets --force

# Verify they exist
dir public\vendor\hyro\manifest.json
dir public\vendor\hyro\assets
```

### Problem: CSS loads but styles don't apply

**Possible causes:**
1. Browser cache - Hard refresh with `Ctrl+Shift+R`
2. Tailwind CDN conflict - Check if multiple Tailwind versions are loading
3. CSS specificity issues - Inspect elements in DevTools

**Solution:**
```bash
# Clear browser cache or use incognito mode
# Check for conflicting stylesheets in page source
```

### Problem: "Class HyroAsset not found" error

**This means the namespace fix didn't apply.**

**Solution:**
```bash
# Verify you have v1.0.5
composer show marufsharia/hyro

# Should show: versions : * v1.0.5

# If not, force update
composer update marufsharia/hyro --with-all-dependencies

# Clear caches
php artisan optimize:clear
```

### Problem: Blade directive not recognized

**Solution:**
```bash
# Clear compiled views
php artisan view:clear

# Restart development server
# Press Ctrl+C to stop
php artisan serve
```

## Manual Verification

If you want to test the helper directly, create a test route:

```php
// routes/web.php
Route::get('/test-hyro-assets', function () {
    $css = \Marufsharia\Hyro\Core\Helpers\HyroAsset::css();
    $js = \Marufsharia\Hyro\Core\Helpers\HyroAsset::js();
    
    return response()->json([
        'css' => $css,
        'js' => $js,
        'manifest_exists' => file_exists(public_path('vendor/hyro/manifest.json')),
        'manifest_path' => public_path('vendor/hyro/manifest.json'),
    ]);
});
```

Visit: `http://127.0.0.1:8000/test-hyro-assets`

Expected response:
```json
{
  "css": "<link rel=\"stylesheet\" href=\"http://127.0.0.1:8000/vendor/hyro/assets/hyro-ayW2hTEZ.css\">",
  "js": "<script type=\"module\" src=\"http://127.0.0.1:8000/vendor/hyro/assets/hyro-D-y3am57.js\"></script>",
  "manifest_exists": true,
  "manifest_path": "F:\\Web\\Laravel\\2026\\hyro-test-app\\public\\vendor\\hyro\\manifest.json"
}
```

## What Changed in the Code

### 1. HyroServiceProvider.php

**Before:**
```php
\Blade::directive('hyroCss', function () {
    return '<?php echo \Marufsharia\Hyro\Helpers\HyroAsset::css(); ?>';
});
```

**After:**
```php
\Blade::directive('hyroCss', function () {
    return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::css(); ?>";
});
```

Changes:
- Fixed namespace: `Helpers` → `Core\Helpers`
- Fixed escaping: Single quotes → Double quotes with escaped backslashes

### 2. HyroAsset.php

**Before:**
```php
public static function css(): ?string
{
    $css = self::asset('resources/css/hyro.css');
    return $css ? "<link rel=\"stylesheet\" href=\"{$css}\">" : null;
}
```

**After:**
```php
public static function css(): string
{
    $css = self::asset('resources/css/hyro.css');
    
    if ($css) {
        return "<link rel=\"stylesheet\" href=\"{$css}\">";
    }
    
    // Fallback: try to load hyro-alert.css directly
    $fallbackCss = public_path('vendor/hyro/css/hyro-alert.css');
    if (File::exists($fallbackCss)) {
        return '<link rel="stylesheet" href="' . asset('vendor/hyro/css/hyro-alert.css') . '">';
    }
    
    // Return empty string instead of null to avoid blade errors
    return '<!-- Hyro CSS not found. Run: php artisan hyro:publish-assets -->';
}
```

Changes:
- Return type: `?string` → `string`
- Added fallback to `hyro-alert.css`
- Returns HTML comment instead of `null` when assets missing

## Success Criteria

✅ `composer show marufsharia/hyro` shows version `v1.0.5`
✅ Page source contains `<link rel="stylesheet" href="...vendor/hyro/assets/hyro-...css">`
✅ Page source contains `<script type="module" src="...vendor/hyro/assets/hyro-...js">`
✅ No 404 errors in browser console
✅ Admin panel has proper styling (dark sidebar, gradient header, etc.)
✅ No PHP errors in `storage/logs/laravel.log`

## Next Steps After Success

Once the CSS/JS are loading correctly, you can:

1. Customize the admin panel styling
2. Add your own plugins
3. Configure roles and privileges
4. Set up API endpoints
5. Customize the dashboard

## Need Help?

If you're still experiencing issues after following this guide:

1. Check `storage/logs/laravel.log` for PHP errors
2. Check browser console for JavaScript errors
3. Verify file permissions on `public/vendor/hyro/` directory
4. Try deleting `public/vendor/hyro/` and re-publishing
5. Share the output of `composer show marufsharia/hyro` and page source

## Version History

- **v1.0.5** - Fixed Blade directives with improved fallback logic
- **v1.0.4** - Fixed asset publishing paths
- **v1.0.3** - Fixed HasHyroFeatures trait namespace in docs
- **v1.0.2** - Added CLI commands
- **v1.0.1** - Fixed migration trait namespace
- **v1.0.0** - Initial release
