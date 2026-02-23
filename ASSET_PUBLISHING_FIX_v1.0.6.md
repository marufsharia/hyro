# Asset Publishing Fix - v1.0.6

## The Problem

The CSS and JS files were not loading because:

1. **Wrong files were being published**: The package was publishing source files from `resources/css` and `resources/js` instead of the Vite-compiled assets from `public/build/`
2. **Missing manifest.json**: The Vite manifest file wasn't being published, so the HyroAsset helper couldn't find the compiled assets
3. **No versioned assets**: Without the build files, the versioned filenames (like `hyro-D-y3am57.js`) weren't available

## The Solution

v1.0.6 now publishes the **built assets** from the package's `public/build/` directory, which includes:
- `manifest.json` - Maps source files to compiled assets
- `assets/` folder - Contains Vite-compiled CSS and JS with versioned filenames
- Raw CSS/JS as fallback

## How to Update and Test

### Step 1: Update the Package

```bash
cd F:\Web\Laravel\2026\hyro-test-app
composer update marufsharia/hyro
```

You should see:
```
- Upgrading marufsharia/hyro (v1.0.5 => v1.0.6)
```

### Step 2: Clear Old Assets

```bash
# Remove old published assets
rmdir /s /q public\vendor\hyro

# Clear all caches
php artisan optimize:clear
```

### Step 3: Publish Assets with New Command

```bash
php artisan hyro:publish-assets --force
```

Expected output:
```
Publishing Hyro assets...

Publishing built assets (Vite compiled)...
✓ Copied built directory recursively

Publishing raw assets (fallback)...
✓ Copied 1 css file(s)
✓ Copied 1 js file(s)

✓ Assets published successfully!

Assets published to:
  • public/vendor/hyro/manifest.json (Vite manifest)
  • public/vendor/hyro/assets/ (compiled CSS/JS)
  • public/vendor/hyro/css/ (raw CSS fallback)
  • public/vendor/hyro/js/ (raw JS fallback)
```

### Step 4: Verify Published Files

```bash
dir public\vendor\hyro
```

You should see:
```
Directory: public\vendor\hyro

Mode                 LastWriteTime         Length Name
----                 -------------         ------ ----
d-----                                            assets
d-----                                            css
d-----                                            images
d-----                                            js
-a----                                       xxx  manifest.json
```

Check the manifest:
```bash
type public\vendor\hyro\manifest.json
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

### Step 5: Test in Browser

```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000/hyro/admin/dashboard`

**View Page Source** (Ctrl+U) and look for:

```html
<head>
    <!-- This should now appear! -->
    <link rel="stylesheet" href="http://127.0.0.1:8000/vendor/hyro/assets/hyro-HPBdpIdl.css">
</head>

<!-- ... -->

<body>
    <!-- ... -->
    <script type="module" src="http://127.0.0.1:8000/vendor/hyro/assets/hyro-D-y3am57.js"></script>
</body>
```

### Step 6: Check Browser Console

Open DevTools (F12) → Console tab

**Success**: No 404 errors
**Failure**: 404 errors for CSS/JS files

## What Changed in v1.0.6

### 1. AdminPanelServiceProvider.php

**Before:**
```php
// Only published source files
$this->publishes([
    __DIR__ . '/../resources/css' => public_path('vendor/hyro/css'),
    __DIR__ . '/../resources/js' => public_path('vendor/hyro/js'),
], 'hyro-assets');
```

**After:**
```php
// Publish built assets (primary)
$this->publishes([
    __DIR__ . '/../../public/build' => public_path('vendor/hyro'),
], 'hyro-assets');

// Also publish raw CSS/JS for fallback
$this->publishes([
    __DIR__ . '/../resources/css' => public_path('vendor/hyro/css'),
    __DIR__ . '/../resources/js' => public_path('vendor/hyro/js'),
], 'hyro-assets-raw');
```

### 2. PublishAssetsCommand.php

Now copies the entire `public/build/` directory recursively, including:
- manifest.json
- assets/ folder with compiled files
- Plus raw CSS/JS as fallback

## Alternative Publishing Methods

### Method 1: Using Artisan (Recommended)
```bash
php artisan hyro:publish-assets --force
```

### Method 2: Using Vendor Publish
```bash
# Publish built assets
php artisan vendor:publish --tag=hyro-assets --force

# Optionally publish raw assets too
php artisan vendor:publish --tag=hyro-assets-raw --force
```

### Method 3: Publish All Hyro Resources
```bash
php artisan vendor:publish --provider="Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider" --force
```

## Understanding the Asset Flow

### In the Package (Development)

1. **Source files**: `resources/css/hyro.css`, `resources/js/hyro.js`
2. **Vite builds**: Compiles and versions files
3. **Output**: `public/build/assets/hyro-[hash].css`, `public/build/assets/hyro-[hash].js`
4. **Manifest**: `public/build/manifest.json` maps source → built files

### In Your Application (Production)

1. **Publish**: Copies `public/build/` → `public/vendor/hyro/`
2. **HyroAsset helper**: Reads `public/vendor/hyro/manifest.json`
3. **Blade directive**: `@hyroCss` outputs `<link>` tag with versioned filename
4. **Browser**: Loads `http://yourapp.com/vendor/hyro/assets/hyro-[hash].css`

## Troubleshooting

### Problem: "Built assets not found" warning

This means the package doesn't have pre-built assets. This shouldn't happen with the published package, but if it does:

**Solution:**
```bash
# In the package directory (if you're developing)
cd packages/marufsharia/hyro
npm install
npm run build

# Then republish
cd ../../..
php artisan hyro:publish-assets --force
```

### Problem: manifest.json not found

**Solution:**
```bash
# Check if it exists in the package
dir vendor\marufsharia\hyro\public\build\manifest.json

# If yes, republish
php artisan hyro:publish-assets --force

# If no, the package needs to be rebuilt
```

### Problem: CSS loads but no styling

**Possible causes:**
1. Browser cache - Hard refresh (Ctrl+Shift+R)
2. CSS file is empty - Check file size
3. Wrong CSS file loaded - Check network tab in DevTools

**Solution:**
```bash
# Check file size
dir public\vendor\hyro\assets\*.css

# Should be > 0 bytes
# If 0 bytes, republish with --force
php artisan hyro:publish-assets --force
```

### Problem: Still seeing fallback CSS (hyro-alert.css)

This means the manifest isn't working. Check:

```bash
# Verify manifest exists
type public\vendor\hyro\manifest.json

# Verify it has the correct structure
# Should have "resources/css/hyro.css" entry
```

## For Package Developers

If you're modifying the Hyro package and need to rebuild assets:

```bash
cd packages/marufsharia/hyro

# Install dependencies
npm install

# Build for production
npm run build

# Or build for development with watch
npm run dev
```

This will:
1. Compile `resources/css/hyro.css` → `public/build/assets/hyro-[hash].css`
2. Compile `resources/js/hyro.js` → `public/build/assets/hyro-[hash].js`
3. Generate `public/build/manifest.json`

Then commit and push the built files:
```bash
git add public/build/
git commit -m "Rebuild assets"
git push
```

## Success Checklist

✅ `composer show marufsharia/hyro` shows `v1.0.6`
✅ `public/vendor/hyro/manifest.json` exists
✅ `public/vendor/hyro/assets/` contains CSS and JS files
✅ Page source shows `<link>` tag with versioned CSS filename
✅ Page source shows `<script>` tag with versioned JS filename
✅ No 404 errors in browser console
✅ Admin panel has proper styling

## Version History

- **v1.0.6** - Fixed asset publishing to include Vite-built assets
- **v1.0.5** - Fixed Blade directives and HyroAsset helper
- **v1.0.4** - Fixed asset publishing paths
- **v1.0.3** - Fixed HasHyroFeatures trait namespace in docs
- **v1.0.2** - Added CLI commands
- **v1.0.1** - Fixed migration trait namespace
- **v1.0.0** - Initial release
