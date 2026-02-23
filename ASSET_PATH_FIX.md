# Asset Path Fix - Manifest Location Update

## Problem

CSS files were not loading correctly with the error:
```
<link rel="stylesheet" href="http://localhost:8000/vendor/hyro/assets/hyro-HPBdpIdl.css">
css not found
```

## Root Cause

The `HyroAsset` helper was looking for the manifest file at:
- `public/vendor/hyro/manifest.json`

But Vite v2.0 builds to:
- `public/vendor/hyro/.vite/manifest.json`

This caused the helper to not find the manifest and fall back to CDN assets.

## Solution

### 1. Updated HyroAsset Helper

Updated the `getManifestPath()` method to check multiple locations in order:

```php
protected static function getManifestPath(): ?string
{
    // Check published assets first (new v2.0 location)
    $publishedManifest = public_path('vendor/hyro/.vite/manifest.json');
    if (File::exists($publishedManifest)) {
        return $publishedManifest;
    }

    // Fallback to root manifest (for backward compatibility)
    $rootManifest = public_path('vendor/hyro/manifest.json');
    if (File::exists($rootManifest)) {
        return $rootManifest;
    }

    // Fallback to package assets (for development)
    $packageManifest = base_path('vendor/marufsharia/hyro/dist/.vite/manifest.json');
    if (File::exists($packageManifest)) {
        return $packageManifest;
    }

    return null;
}
```

### 2. Updated Login View

Modernized the login view to use the new `@hyroAssets` directive:

**Before:**
```blade
@hyroCss
@livewireStyles
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

**After:**
```blade
@hyroAssets
@livewireStyles
```

This eliminates:
- Duplicate Alpine.js loading (already included in @hyroAssets)
- Separate CSS and JS directives
- Manual CDN script tags

## Benefits

### 1. Correct Asset Loading
✅ CSS now loads from the correct path: `vendor/hyro/css/hyro-BSyM4jQE.css`
✅ JS loads from the correct path: `vendor/hyro/js/hyro-D-y3am57.js`
✅ Manifest is read from the correct location

### 2. Backward Compatibility
✅ Still checks old manifest location for backward compatibility
✅ Falls back to package assets for development
✅ Multiple fallback paths ensure reliability

### 3. Simplified Views
✅ Single `@hyroAssets` directive instead of multiple directives
✅ No duplicate Alpine.js loading
✅ Cleaner, more maintainable code

## Verification

### Test Asset Loading

```bash
php artisan tinker --execute="echo \Marufsharia\Hyro\Core\Helpers\HyroAsset::css();"
```

**Expected Output:**
```html
<link rel="stylesheet" href="http://localhost:8000/vendor/hyro/css/hyro-BSyM4jQE.css">
```

### Check Manifest Location

```bash
ls public/vendor/hyro/.vite/manifest.json
```

**Expected:** File exists

### Test Login Page

1. Visit: `http://localhost:8000/admin/login`
2. Open browser DevTools → Network tab
3. Verify CSS loads successfully (200 status)
4. Verify no 404 errors for assets

## Files Modified

### 1. core/src/Helpers/HyroAsset.php
- Updated `getManifestPath()` to check `.vite/manifest.json` first
- Added fallback to root manifest for backward compatibility
- Updated package manifest path to use `dist/.vite/manifest.json`

### 2. admin-panel/resources/views/admin/auth/login.blade.php
- Replaced `@hyroCss` with `@hyroAssets`
- Removed duplicate Alpine.js script tag
- Removed `@hyroJs` (included in `@hyroAssets`)
- Added `@livewireScripts` at the end of body

## Asset Path Resolution Order

The helper now checks paths in this order:

1. **Published Assets (v2.0)**: `public/vendor/hyro/.vite/manifest.json`
2. **Published Assets (v1.x)**: `public/vendor/hyro/manifest.json`
3. **Package Assets (Dev)**: `vendor/marufsharia/hyro/dist/.vite/manifest.json`
4. **CDN Fallback**: If none found, loads from CDN

## Manifest Structure

The manifest file structure:

```json
{
  "resources/css/hyro.css": {
    "file": "css/hyro-BSyM4jQE.css",
    "src": "resources/css/hyro.css",
    "isEntry": true,
    "name": "hyro"
  },
  "resources/js/hyro.js": {
    "file": "js/hyro-D-y3am57.js",
    "name": "hyro",
    "src": "resources/js/hyro.js",
    "isEntry": true
  }
}
```

## Testing Checklist

- [x] CSS loads correctly from manifest
- [x] JS loads correctly from manifest
- [x] Login page displays properly
- [x] No 404 errors in browser console
- [x] Alpine.js functionality works
- [x] Tailwind CSS styles applied
- [x] Dark mode toggle works
- [x] Form validation works
- [x] Backward compatibility maintained

## Troubleshooting

### Issue: CSS still not loading

**Solution 1**: Clear browser cache
```bash
# Hard refresh in browser
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

**Solution 2**: Republish assets
```bash
php artisan hyro:publish-assets --force
```

**Solution 3**: Check manifest exists
```bash
ls public/vendor/hyro/.vite/manifest.json
```

### Issue: Old CSS file referenced

**Solution**: Clear Laravel view cache
```bash
php artisan view:clear
php artisan cache:clear
```

### Issue: Assets not found in development

**Solution**: Ensure package assets are built
```bash
cd packages/marufsharia/hyro
npm run build
```

## Migration Notes

### For v1.x Users

If upgrading from v1.x:

1. **Republish assets**:
   ```bash
   php artisan hyro:publish-assets --force
   ```

2. **Update views** (optional but recommended):
   ```blade
   <!-- Old -->
   @hyroCss
   @hyroJs
   
   <!-- New -->
   @hyroAssets
   ```

3. **Clear caches**:
   ```bash
   php artisan view:clear
   php artisan cache:clear
   ```

### For New Installations

No action needed - assets will be published to the correct location automatically.

## Commit Details

- **Commit**: `c135dcf`
- **Message**: "fix: Update manifest path to use .vite subdirectory and modernize login view"
- **Files Changed**: 3
- **Status**: ✅ Pushed to GitHub

---

**Issue**: CSS not loading from correct path  
**Solution**: Updated manifest path resolution  
**Status**: ✅ Fixed and Pushed  
**Version**: v2.0.0+
