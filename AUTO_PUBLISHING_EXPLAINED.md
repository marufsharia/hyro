# Auto-Publishing Explained

## How Auto-Publishing Works in Hyro

### Overview

As of v1.0.9, Hyro automatically publishes assets during:
- ✅ Initial installation (`composer require marufsharia/hyro`)
- ✅ Package updates (`composer update marufsharia/hyro`)

No manual `php artisan hyro:publish-assets` required!

## The Logic

### When Assets Are Published

```php
private function shouldPublishAssets(): bool
{
    // 1. Check if manifest exists
    if (!exists('public/vendor/hyro/manifest.json')) {
        return true; // First install - publish!
    }

    // 2. Compare timestamps
    $publishedTime = filemtime('public/vendor/hyro/manifest.json');
    $packageTime = filemtime('vendor/.../public/build/manifest.json');

    // 3. If package is newer, republish
    return $packageTime > $publishedTime;
}
```

### Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│  composer require/update marufsharia/hyro                   │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Laravel discovers AdminPanelServiceProvider                │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│  boot() method calls autoPublishAssets()                    │
└─────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────┐
│  Check: Is this a console command?                          │
│  (composer runs in console)                                 │
└─────────────────────────────────────────────────────────────┘
         │
         ├─── No (web request) ──► Skip auto-publish
         │
         └─── Yes (console) ──► Continue
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  Check: Does manifest exist?                                │
│  public/vendor/hyro/manifest.json                           │
└─────────────────────────────────────────────────────────────┘
         │
         ├─── No ──► Publish assets (first install)
         │
         └─── Yes ──► Check timestamps
                  │
                  ▼
┌─────────────────────────────────────────────────────────────┐
│  Compare timestamps:                                        │
│  Package: vendor/.../public/build/manifest.json             │
│  Published: public/vendor/hyro/manifest.json                │
└─────────────────────────────────────────────────────────────┘
         │
         ├─── Package newer ──► Republish assets (update)
         │
         └─── Published newer/same ──► Skip (already up-to-date)
```

## Version History

### v1.0.9 (Current) - Smart Auto-Publishing

**Works on:**
- ✅ Initial install
- ✅ Package updates (if assets changed)

**Logic:**
```php
// Publishes if:
// 1. Manifest doesn't exist, OR
// 2. Package manifest is newer than published manifest
```

**Example:**
```bash
# First install
composer require marufsharia/hyro
# → Assets published automatically ✅

# Update package
composer update marufsharia/hyro
# → Assets republished if package has newer assets ✅
```

### v1.0.7-v1.0.8 - Basic Auto-Publishing

**Works on:**
- ✅ Initial install
- ❌ Package updates (skipped if manifest exists)

**Logic:**
```php
// Only published if manifest doesn't exist
if (!exists('manifest.json')) {
    publish();
}
```

**Problem:**
```bash
# First install
composer require marufsharia/hyro
# → Assets published ✅

# Update package
composer update marufsharia/hyro
# → Assets NOT republished ❌ (manifest exists, so skipped)
```

### v1.0.6 and Earlier - Manual Publishing

**Required manual step:**
```bash
composer require marufsharia/hyro
php artisan hyro:publish-assets  # ← Manual step required
```

## Implementation Details

### AdminPanelServiceProvider.php

```php
public function boot(): void
{
    // ... other boot logic

    // Auto-publish assets on package installation/update
    $this->autoPublishAssets();

    // ... rest of boot logic
}

private function autoPublishAssets(): void
{
    // Only during console commands (composer)
    if (!$this->app->runningInConsole()) {
        return;
    }

    // Check if assets need publishing
    if ($this->shouldPublishAssets()) {
        $this->publishAssetsAutomatically();
    }
}

private function shouldPublishAssets(): bool
{
    $manifestPath = public_path('vendor/hyro/manifest.json');
    
    // First install - no manifest exists
    if (!File::exists($manifestPath)) {
        return true;
    }

    // Check if package has newer assets
    $packageManifest = __DIR__ . '/../../public/build/manifest.json';
    if (!File::exists($packageManifest)) {
        return false;
    }

    // Compare modification times
    $publishedTime = filemtime($manifestPath);
    $packageTime = filemtime($packageManifest);

    // Republish if package is newer
    return $packageTime > $packageTime;
}

private function publishAssetsAutomatically(): void
{
    try {
        $buildSource = __DIR__ . '/../../public/build';
        $buildDest = public_path('vendor/hyro');

        if (File::exists($buildSource)) {
            // Create destination
            if (!File::exists($buildDest)) {
                File::makeDirectory($buildDest, 0755, true);
            }

            // Copy built assets
            File::copyDirectory($buildSource, $buildDest);

            // Copy raw assets as fallback
            // ... (css and js)
        }
    } catch (\Exception $e) {
        // Silently fail - can publish manually
    }
}
```

## Scenarios

### Scenario 1: Fresh Installation

```bash
composer require marufsharia/hyro
```

**What happens:**
1. Composer downloads package
2. Laravel discovers service provider
3. `boot()` runs
4. `autoPublishAssets()` checks: manifest exists? **No**
5. `shouldPublishAssets()` returns **true**
6. Assets published automatically ✅

**Result:**
```
public/vendor/hyro/
├── manifest.json          ✅ Published
├── assets/
│   ├── hyro-[hash].css   ✅ Published
│   └── hyro-[hash].js    ✅ Published
├── css/                   ✅ Published
└── js/                    ✅ Published
```

### Scenario 2: Update with New Assets

```bash
# Package developer rebuilds assets
cd packages/marufsharia/hyro
npm run build  # Creates new manifest with new timestamp
git commit && git push
git tag v1.0.9

# User updates
composer update marufsharia/hyro
```

**What happens:**
1. Composer downloads updated package
2. Laravel discovers service provider
3. `boot()` runs
4. `autoPublishAssets()` checks: manifest exists? **Yes**
5. `shouldPublishAssets()` compares timestamps:
   - Package manifest: `2026-02-23 15:30:00` (newer)
   - Published manifest: `2026-02-23 10:00:00` (older)
6. Returns **true** (package is newer)
7. Assets republished automatically ✅

**Result:**
```
public/vendor/hyro/
├── manifest.json          ✅ Updated
├── assets/
│   ├── hyro-[new-hash].css   ✅ New version
│   └── hyro-[new-hash].js    ✅ New version
```

### Scenario 3: Update without Asset Changes

```bash
# Package developer updates code but not assets
git commit && git push
git tag v1.0.9

# User updates
composer update marufsharia/hyro
```

**What happens:**
1. Composer downloads updated package
2. Laravel discovers service provider
3. `boot()` runs
4. `autoPublishAssets()` checks: manifest exists? **Yes**
5. `shouldPublishAssets()` compares timestamps:
   - Package manifest: `2026-02-23 10:00:00` (same)
   - Published manifest: `2026-02-23 10:00:00` (same)
6. Returns **false** (already up-to-date)
7. Skips publishing (no need) ✅

**Result:**
```
public/vendor/hyro/
├── manifest.json          ✅ Unchanged (already current)
├── assets/                ✅ Unchanged (already current)
```

## Manual Publishing

### When to Use

Auto-publishing should work in 99% of cases, but you can manually publish if:

- Assets got corrupted
- You deleted `public/vendor/hyro/`
- You want to force republish
- Auto-publishing failed for some reason

### Commands

```bash
# Method 1: Custom command (recommended)
php artisan hyro:publish-assets --force

# Method 2: Laravel publish
php artisan vendor:publish --tag=hyro-assets --force

# Method 3: Publish everything
php artisan vendor:publish --provider="Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider" --force
```

## Troubleshooting

### Assets Not Auto-Publishing on Update?

**Check 1: Is the package actually updated?**
```bash
composer show marufsharia/hyro
# Check version number
```

**Check 2: Did the package assets change?**
```bash
# Check package manifest timestamp
ls -la vendor/marufsharia/hyro/public/build/manifest.json

# Check published manifest timestamp
ls -la public/vendor/hyro/manifest.json

# Package should be newer for auto-publish to trigger
```

**Check 3: Are you running in console?**
```bash
# Auto-publish only works during composer commands
# Not during web requests
```

**Solution: Manual publish**
```bash
php artisan hyro:publish-assets --force
```

### Assets Publishing on Every Request?

This shouldn't happen because:
1. Auto-publish only runs in console (not web requests)
2. Timestamp check prevents unnecessary republishing

If it does happen, check:
```php
// In AdminPanelServiceProvider
if (!$this->app->runningInConsole()) {
    return; // Should skip on web requests
}
```

### Want to Disable Auto-Publishing?

Edit `AdminPanelServiceProvider.php`:

```php
private function autoPublishAssets(): void
{
    // Disable auto-publishing
    return;
    
    // ... rest of method
}
```

Then publish manually when needed:
```bash
php artisan hyro:publish-assets
```

## Benefits of Auto-Publishing

✅ **Zero configuration** - Works out of the box
✅ **Always up-to-date** - Assets update automatically
✅ **No manual steps** - Forget about `php artisan vendor:publish`
✅ **Smart detection** - Only republishes when needed
✅ **Fail-safe** - Can still publish manually if needed

## Summary

### v1.0.9 Auto-Publishing

**When it runs:**
- During `composer require marufsharia/hyro`
- During `composer update marufsharia/hyro`

**When it publishes:**
- If manifest doesn't exist (first install)
- If package manifest is newer (update with new assets)

**When it skips:**
- During web requests (only console)
- If published assets are already current

**Manual override:**
```bash
php artisan hyro:publish-assets --force
```

**Result:** Zero-configuration asset management that just works! 🎉
