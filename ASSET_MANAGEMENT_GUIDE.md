# Complete Asset Management Guide for Laravel Packages

## Overview

This guide explains how Hyro handles CSS, JS, images, and other assets from development to production, and how they're used in Blade templates.

## Table of Contents

1. [Asset Structure](#asset-structure)
2. [Development Workflow](#development-workflow)
3. [Build Process](#build-process)
4. [Publishing Assets](#publishing-assets)
5. [Using Assets in Blade](#using-assets-in-blade)
6. [Best Practices](#best-practices)

---

## Asset Structure

### In the Package (packages/marufsharia/hyro/)

```
packages/marufsharia/hyro/
├── resources/                    # Source files (not published directly)
│   ├── css/
│   │   ├── hyro.css             # Main CSS (compiled by Vite)
│   │   └── hyro-alert.css       # Standalone CSS (published as-is)
│   ├── js/
│   │   ├── hyro.js              # Main JS (compiled by Vite)
│   │   └── hyro-alert.js        # Standalone JS (published as-is)
│   └── views/                   # Blade templates
│       └── layouts/
│           └── app.blade.php    # Uses @hyroCss and @hyroJs
│
├── public/                      # Built assets (published to user's app)
│   └── build/
│       ├── manifest.json        # Maps source → compiled files
│       └── assets/
│           ├── hyro-[hash].css  # Compiled & versioned CSS
│           └── hyro-[hash].js   # Compiled & versioned JS
│
├── admin-panel/
│   └── resources/
│       ├── css/                 # Admin-specific CSS
│       ├── js/                  # Admin-specific JS
│       └── views/               # Admin Blade templates
│
├── package.json                 # NPM dependencies
├── vite.config.js              # Vite build configuration
└── tailwind.config.js          # Tailwind CSS configuration
```

### In User's Application (after publishing)

```
your-app/
├── public/
│   └── vendor/
│       └── hyro/
│           ├── manifest.json         # Vite manifest
│           ├── assets/               # Compiled assets
│           │   ├── hyro-[hash].css
│           │   └── hyro-[hash].js
│           ├── css/                  # Raw CSS (fallback)
│           │   └── hyro-alert.css
│           ├── js/                   # Raw JS (fallback)
│           │   └── hyro-alert.js
│           └── images/               # Static images
│
└── resources/
    └── views/
        └── vendor/
            └── hyro/                 # Published views (optional)
```

---

## Development Workflow

### 1. Source Files

Create your CSS and JS in `resources/`:

**resources/css/hyro.css:**
```css
@import 'tailwindcss';

/* Your custom styles */
.hyro-card {
    @apply bg-white rounded-lg shadow-md p-6;
}

.hyro-button {
    @apply px-4 py-2 bg-indigo-600 text-white rounded-lg;
}
```

**resources/js/hyro.js:**
```javascript
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

// Register Alpine plugins
Alpine.plugin(collapse);
Alpine.plugin(focus);

// Start Alpine
window.Alpine = Alpine;
Alpine.start();

// Your custom JS
console.log('Hyro loaded!');
```

### 2. Vite Configuration

**vite.config.js:**
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/hyro.css',
                'resources/js/hyro.js'
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]'
            }
        }
    }
});
```

### 3. Package.json

**package.json:**
```json
{
  "name": "hyro",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  },
  "devDependencies": {
    "@alpinejs/collapse": "^3.15.5",
    "@alpinejs/focus": "^3.15.5",
    "@tailwindcss/vite": "^4.1.18",
    "alpinejs": "^3.15.5",
    "laravel-vite-plugin": "^2.0.0",
    "tailwindcss": "^4.1.18",
    "vite": "^7.0.7"
  }
}
```

---

## Build Process

### Development Build (with hot reload)

```bash
cd packages/marufsharia/hyro
npm install
npm run dev
```

This starts Vite dev server with:
- Hot Module Replacement (HMR)
- Instant updates on file changes
- Source maps for debugging

### Production Build

```bash
cd packages/marufsharia/hyro
npm run build
```

This creates:
1. **Minified CSS**: `public/build/assets/hyro-[hash].css`
2. **Minified JS**: `public/build/assets/hyro-[hash].js`
3. **Manifest**: `public/build/manifest.json`

**Example manifest.json:**
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

### Commit Built Assets

```bash
git add public/build/
git commit -m "Build assets for v1.0.6"
git push
```

**Important**: Always commit built assets so users don't need to run `npm build` after installing your package.

---

## Publishing Assets

### Method 1: Service Provider (Automatic)

**AdminPanelServiceProvider.php:**
```php
private function publishResources(): void
{
    // Publish built assets (manifest + compiled files)
    $this->publishes([
        __DIR__ . '/../../public/build' => public_path('vendor/hyro'),
    ], 'hyro-assets');

    // Publish raw assets as fallback
    $this->publishes([
        __DIR__ . '/../resources/css' => public_path('vendor/hyro/css'),
        __DIR__ . '/../resources/js' => public_path('vendor/hyro/js'),
    ], 'hyro-assets-raw');

    // Publish images
    $this->publishes([
        __DIR__ . '/../resources/images' => public_path('vendor/hyro/images'),
    ], 'hyro-images');

    // Publish views (optional - users can customize)
    $this->publishes([
        __DIR__ . '/../resources/views' => resource_path('views/vendor/hyro'),
    ], 'hyro-views');
}
```

### Method 2: Custom Artisan Command

**PublishAssetsCommand.php:**
```php
public function handle()
{
    $force = $this->option('force');

    // 1. Publish built assets (primary)
    $buildSource = __DIR__ . '/../../../public/build';
    $buildDest = public_path('vendor/hyro');
    
    if (File::exists($buildSource)) {
        File::copyDirectory($buildSource, $buildDest);
        $this->info('✓ Published built assets');
    }

    // 2. Publish raw assets (fallback)
    $this->publishDirectory(
        __DIR__ . '/../../../resources/css',
        public_path('vendor/hyro/css'),
        'css'
    );

    // 3. Publish images
    $this->publishDirectory(
        __DIR__ . '/../../../resources/images',
        public_path('vendor/hyro/images'),
        'images'
    );

    $this->info('✓ Assets published successfully!');
}
```

### User Commands

```bash
# Method 1: Custom command (recommended)
php artisan hyro:publish-assets --force

# Method 2: Standard Laravel publish
php artisan vendor:publish --tag=hyro-assets --force

# Method 3: Publish everything
php artisan vendor:publish --provider="Marufsharia\Hyro\AdminPanel\AdminPanelServiceProvider"
```

---

## Using Assets in Blade

### Approach 1: Blade Directives (Recommended)

**Create Helper Class:**

**core/src/Helpers/HyroAsset.php:**
```php
<?php
namespace Marufsharia\Hyro\Core\Helpers;

use Illuminate\Support\Facades\File;

class HyroAsset
{
    protected static function manifest(): array
    {
        $manifestPath = public_path('vendor/hyro/manifest.json');
        
        if (!File::exists($manifestPath)) {
            return [];
        }
        
        return json_decode(file_get_contents($manifestPath), true) ?? [];
    }

    public static function css(): string
    {
        $manifest = static::manifest();
        
        // Try to get from manifest
        if (isset($manifest['resources/css/hyro.css'])) {
            $file = $manifest['resources/css/hyro.css']['file'];
            $url = asset('vendor/hyro/' . $file);
            return "<link rel=\"stylesheet\" href=\"{$url}\">";
        }
        
        // Fallback to raw CSS
        if (File::exists(public_path('vendor/hyro/css/hyro-alert.css'))) {
            return '<link rel="stylesheet" href="' . asset('vendor/hyro/css/hyro-alert.css') . '">';
        }
        
        return '<!-- Hyro CSS not found. Run: php artisan hyro:publish-assets -->';
    }

    public static function js(): string
    {
        $manifest = static::manifest();
        
        // Try to get from manifest
        if (isset($manifest['resources/js/hyro.js'])) {
            $file = $manifest['resources/js/hyro.js']['file'];
            $url = asset('vendor/hyro/' . $file);
            return "<script type=\"module\" src=\"{$url}\"></script>";
        }
        
        // Fallback to raw JS
        if (File::exists(public_path('vendor/hyro/js/hyro-alert.js'))) {
            return '<script src="' . asset('vendor/hyro/js/hyro-alert.js') . '"></script>';
        }
        
        return '<!-- Hyro JS not found. Run: php artisan hyro:publish-assets -->';
    }

    public static function image(string $path): string
    {
        return asset('vendor/hyro/images/' . $path);
    }
}
```

**Register Blade Directives:**

**HyroServiceProvider.php:**
```php
private function registerBladeDirectives(): void
{
    \Blade::directive('hyroCss', function () {
        return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::css(); ?>";
    });

    \Blade::directive('hyroJs', function () {
        return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::js(); ?>";
    });

    \Blade::directive('hyroImage', function ($expression) {
        return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::image({$expression}); ?>";
    });
}
```

**Use in Blade Templates:**

**admin-panel/resources/views/layouts/app.blade.php:**
```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Hyro Admin' }}</title>

    <!-- Hyro CSS (automatically loads versioned file) -->
    @hyroCss
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- Additional Styles -->
    @stack('styles')
</head>
<body>
    <!-- Logo using image helper -->
    <img src="@hyroImage('logo.png')" alt="Logo">

    <!-- Content -->
    {{ $slot }}

    <!-- Hyro JS (automatically loads versioned file) -->
    @hyroJs
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>
```

### Approach 2: Direct Asset Helper

```blade
<!-- CSS -->
<link rel="stylesheet" href="{{ asset('vendor/hyro/assets/hyro-HPBdpIdl.css') }}">

<!-- JS -->
<script type="module" src="{{ asset('vendor/hyro/assets/hyro-D-y3am57.js') }}"></script>

<!-- Image -->
<img src="{{ asset('vendor/hyro/images/logo.png') }}" alt="Logo">
```

**Problem**: Hard-coded hash values break when you rebuild assets.

### Approach 3: Vite Helper (if using Vite in user's app)

```blade
@vite([
    'vendor/hyro/resources/css/hyro.css',
    'vendor/hyro/resources/js/hyro.js'
])
```

**Problem**: Requires user to configure Vite to include package assets.

---

## Best Practices

### 1. Always Commit Built Assets

```bash
# After making changes
npm run build
git add public/build/
git commit -m "Rebuild assets"
```

**Why**: Users shouldn't need Node.js to use your package.

### 2. Version Your Assets

Use Vite's hash-based naming:
- `hyro-[hash].css` instead of `hyro.css`
- Prevents browser caching issues
- Manifest maps source → versioned files

### 3. Provide Fallbacks

```php
// Try manifest first
if (isset($manifest['resources/css/hyro.css'])) {
    return $manifestAsset;
}

// Fallback to raw CSS
if (File::exists(public_path('vendor/hyro/css/hyro.css'))) {
    return $rawAsset;
}

// Show helpful error
return '<!-- Run: php artisan hyro:publish-assets -->';
```

### 4. Separate Concerns

```
resources/
├── css/
│   ├── hyro.css          # Main CSS (compiled by Vite)
│   └── hyro-alert.css    # Standalone (no compilation needed)
├── js/
│   ├── hyro.js           # Main JS (compiled by Vite)
│   └── hyro-alert.js     # Standalone (no compilation needed)
└── images/
    └── logo.png          # Static assets (copied as-is)
```

### 5. Use Blade Directives

**Benefits:**
- Clean syntax: `@hyroCss` vs `<link rel="stylesheet" href="...">`
- Automatic versioning
- Fallback handling
- Easy to update

### 6. Document Asset Publishing

In your README:

```markdown
## Installation

1. Install via Composer:
   ```bash
   composer require marufsharia/hyro
   ```

2. Publish assets:
   ```bash
   php artisan hyro:publish-assets
   ```

3. Use in your layouts:
   ```blade
   @hyroCss
   @hyroJs
   ```
```

### 7. Handle Images Properly

**For static images:**
```php
// Publish
$this->publishes([
    __DIR__ . '/../resources/images' => public_path('vendor/hyro/images'),
], 'hyro-images');

// Use
<img src="{{ asset('vendor/hyro/images/logo.png') }}">
```

**For dynamic images:**
```php
// Store in storage/app/public/hyro/
Storage::disk('public')->put('hyro/avatar.jpg', $file);

// Create symlink
php artisan storage:link

// Use
<img src="{{ asset('storage/hyro/avatar.jpg') }}">
```

### 8. Optimize for Production

**In vite.config.js:**
```javascript
export default defineConfig({
    build: {
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log
            }
        },
        rollupOptions: {
            output: {
                manualChunks: {
                    'alpine': ['alpinejs'],
                    'vendor': ['axios']
                }
            }
        }
    }
});
```

### 9. Test Asset Loading

Create a test route:

```php
Route::get('/test-assets', function () {
    return view('hyro::test-assets');
});
```

**test-assets.blade.php:**
```blade
<!DOCTYPE html>
<html>
<head>
    @hyroCss
</head>
<body>
    <h1>Asset Test</h1>
    <div class="hyro-card">
        If this is styled, CSS is working!
    </div>
    
    @hyroJs
    <script>
        console.log('If you see this, JS is working!');
    </script>
</body>
</html>
```

### 10. Handle CDN Assets

For external dependencies:

```blade
<!-- In your layout -->
<head>
    <!-- CDN CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tailwindcss/ui@latest/dist/tailwind-ui.min.css">
    
    <!-- Your CSS -->
    @hyroCss
</head>
<body>
    <!-- Your JS -->
    @hyroJs
    
    <!-- CDN JS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
```

---

## Complete Example

### Package Structure

```
packages/marufsharia/hyro/
├── resources/
│   ├── css/
│   │   └── hyro.css
│   ├── js/
│   │   └── hyro.js
│   ├── images/
│   │   └── logo.png
│   └── views/
│       └── layouts/
│           └── app.blade.php
├── public/
│   └── build/
│       ├── manifest.json
│       └── assets/
│           ├── hyro-[hash].css
│           └── hyro-[hash].js
├── core/
│   └── src/
│       └── Helpers/
│           └── HyroAsset.php
├── admin-panel/
│   └── src/
│       ├── AdminPanelServiceProvider.php
│       └── Console/
│           └── Commands/
│               └── PublishAssetsCommand.php
├── package.json
└── vite.config.js
```

### Build Command

```bash
npm run build
```

### Publish Command

```bash
php artisan hyro:publish-assets
```

### Use in Blade

```blade
@hyroCss
@hyroJs
<img src="@hyroImage('logo.png')">
```

### Result

```html
<link rel="stylesheet" href="http://yourapp.com/vendor/hyro/assets/hyro-HPBdpIdl.css">
<script type="module" src="http://yourapp.com/vendor/hyro/assets/hyro-D-y3am57.js"></script>
<img src="http://yourapp.com/vendor/hyro/images/logo.png">
```

---

## Troubleshooting

### Assets not loading?

1. Check if published: `dir public\vendor\hyro`
2. Check manifest: `type public\vendor\hyro\manifest.json`
3. Republish: `php artisan hyro:publish-assets --force`
4. Clear cache: `php artisan optimize:clear`

### Wrong CSS loading?

1. Check page source for actual URL
2. Verify file exists at that URL
3. Check browser console for 404 errors
4. Hard refresh: `Ctrl+Shift+R`

### Need to rebuild?

```bash
cd packages/marufsharia/hyro
npm run build
git add public/build/
git commit -m "Rebuild assets"
git push
git tag v1.0.7
git push --tags
```

---

## Summary

**Asset Flow:**
1. **Develop**: Edit `resources/css/hyro.css` and `resources/js/hyro.js`
2. **Build**: Run `npm run build` → Creates `public/build/assets/hyro-[hash].{css,js}`
3. **Commit**: Commit built files to Git
4. **Publish**: User runs `php artisan hyro:publish-assets`
5. **Use**: Blade directive `@hyroCss` reads manifest and outputs correct URL
6. **Load**: Browser loads versioned asset from `public/vendor/hyro/assets/`

This approach ensures:
- ✅ No build step required for users
- ✅ Automatic cache busting with versioned filenames
- ✅ Fallback to raw assets if manifest missing
- ✅ Clean Blade syntax
- ✅ Easy to update and maintain
