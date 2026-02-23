# Asset Flow Diagram

## Visual Overview of Asset Management

```
┌─────────────────────────────────────────────────────────────────────┐
│                    PACKAGE DEVELOPMENT                               │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│  Source Files    │
│                  │
│  resources/      │
│  ├── css/        │
│  │   └── hyro.css    ──────┐
│  ├── js/         │          │
│  │   └── hyro.js     ──────┤
│  └── images/     │          │
│      └── logo.png ──────┐   │
└──────────────────┘      │   │
                          │   │
                          │   ▼
                          │  ┌──────────────────┐
                          │  │  Vite Build      │
                          │  │  npm run build   │
                          │  └──────────────────┘
                          │   │
                          │   ▼
                          │  ┌──────────────────────────────┐
                          │  │  Built Assets                │
                          │  │                              │
                          │  │  public/build/               │
                          │  │  ├── manifest.json           │
                          │  │  └── assets/                 │
                          │  │      ├── hyro-[hash].css     │
                          │  │      └── hyro-[hash].js      │
                          │  └──────────────────────────────┘
                          │   │
                          │   ▼
                          │  ┌──────────────────┐
                          │  │  Git Commit      │
                          │  │  git add .       │
                          │  │  git commit      │
                          │  │  git push        │
                          │  └──────────────────┘
                          │   │
                          ▼   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    PACKAGE PUBLISHED                                 │
│                    (on Packagist)                                    │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    USER INSTALLATION                                 │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│  Composer        │
│  Install         │
│                  │
│  composer require│
│  marufsharia/hyro│
└──────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Package Downloaded          │
│                              │
│  vendor/marufsharia/hyro/    │
│  ├── public/build/           │
│  │   ├── manifest.json       │
│  │   └── assets/             │
│  ├── resources/              │
│  └── admin-panel/            │
└──────────────────────────────┘
         │
         ▼
┌──────────────────┐
│  Publish Assets  │
│                  │
│  php artisan     │
│  hyro:publish-   │
│  assets          │
└──────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│  Assets Copied to Public               │
│                                        │
│  public/vendor/hyro/                   │
│  ├── manifest.json          ◄──────────┼─── From: vendor/.../public/build/
│  ├── assets/                           │
│  │   ├── hyro-[hash].css              │
│  │   └── hyro-[hash].js               │
│  ├── css/                   ◄──────────┼─── From: vendor/.../resources/css/
│  │   └── hyro-alert.css               │
│  ├── js/                    ◄──────────┼─── From: vendor/.../resources/js/
│  │   └── hyro-alert.js                │
│  └── images/                ◄──────────┼─── From: vendor/.../resources/images/
│      └── logo.png                      │
└────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    BLADE TEMPLATE USAGE                              │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────┐
│  Blade Template              │
│                              │
│  <head>                      │
│    @hyroCss                  │
│  </head>                     │
│  <body>                      │
│    <img src="@hyroImage(...)"│
│    @hyroJs                   │
│  </body>                     │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Blade Directive Executed    │
│                              │
│  HyroAsset::css()            │
│  HyroAsset::js()             │
│  HyroAsset::image()          │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Read Manifest               │
│                              │
│  public/vendor/hyro/         │
│  manifest.json               │
│                              │
│  {                           │
│    "resources/css/hyro.css": │
│    {                         │
│      "file": "assets/        │
│        hyro-HPBdpIdl.css"    │
│    }                         │
│  }                           │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Generate HTML Tag           │
│                              │
│  <link rel="stylesheet"      │
│   href="/vendor/hyro/assets/ │
│   hyro-HPBdpIdl.css">        │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Rendered HTML               │
│                              │
│  <!DOCTYPE html>             │
│  <html>                      │
│  <head>                      │
│    <link rel="stylesheet"    │
│     href="http://app.com/    │
│     vendor/hyro/assets/      │
│     hyro-HPBdpIdl.css">      │
│  </head>                     │
│  <body>                      │
│    <img src="http://app.com/ │
│     vendor/hyro/images/      │
│     logo.png">               │
│    <script type="module"     │
│     src="http://app.com/     │
│     vendor/hyro/assets/      │
│     hyro-D-y3am57.js">       │
│    </script>                 │
│  </body>                     │
│  </html>                     │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Browser Loads Assets        │
│                              │
│  GET /vendor/hyro/assets/    │
│      hyro-HPBdpIdl.css       │
│  GET /vendor/hyro/assets/    │
│      hyro-D-y3am57.js        │
│  GET /vendor/hyro/images/    │
│      logo.png                │
└──────────────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Page Rendered with Styles   │
│  ✓ CSS Applied               │
│  ✓ JS Executed               │
│  ✓ Images Displayed          │
└──────────────────────────────┘
```

## Key Components

### 1. HyroAsset Helper

```php
class HyroAsset
{
    // Reads manifest.json
    protected static function manifest(): array
    
    // Returns <link> tag with versioned CSS
    public static function css(): string
    
    // Returns <script> tag with versioned JS
    public static function js(): string
    
    // Returns image URL
    public static function image(string $path): string
}
```

### 2. Blade Directives

```php
// In HyroServiceProvider
\Blade::directive('hyroCss', function () {
    return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::css(); ?>";
});

\Blade::directive('hyroJs', function () {
    return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::js(); ?>";
});

\Blade::directive('hyroImage', function ($expression) {
    return "<?php echo \\Marufsharia\\Hyro\\Core\\Helpers\\HyroAsset::image({$expression}); ?>";
});
```

### 3. Asset Publishing

```php
// In AdminPanelServiceProvider
$this->publishes([
    __DIR__ . '/../../public/build' => public_path('vendor/hyro'),
], 'hyro-assets');
```

## Fallback Strategy

```
┌─────────────────────────────────────────┐
│  Try to load from manifest              │
│  public/vendor/hyro/manifest.json       │
└─────────────────────────────────────────┘
         │
         ├─── Found? ───► Use versioned asset
         │                (assets/hyro-[hash].css)
         │
         └─── Not Found?
                │
                ▼
         ┌─────────────────────────────────┐
         │  Try raw CSS/JS                 │
         │  public/vendor/hyro/css/        │
         │  public/vendor/hyro/js/         │
         └─────────────────────────────────┘
                │
                ├─── Found? ───► Use raw asset
                │                (css/hyro-alert.css)
                │
                └─── Not Found?
                       │
                       ▼
                ┌─────────────────────────────┐
                │  Return HTML comment        │
                │  <!-- Run: php artisan      │
                │   hyro:publish-assets -->   │
                └─────────────────────────────┘
```

## Cache Busting Strategy

```
┌──────────────────────────────────────────────────────────────┐
│  Without Versioning (BAD)                                    │
├──────────────────────────────────────────────────────────────┤
│  hyro.css  ───► Browser caches ───► You update CSS          │
│                                  ───► Browser still uses old │
│                                       cached version ❌       │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│  With Versioning (GOOD)                                      │
├──────────────────────────────────────────────────────────────┤
│  hyro-ABC123.css ───► Browser caches                         │
│                                                               │
│  You update CSS ───► npm run build                           │
│                  ───► hyro-XYZ789.css (new hash)             │
│                  ───► Browser sees new filename              │
│                  ───► Downloads fresh version ✅              │
└──────────────────────────────────────────────────────────────┘
```

## Development vs Production

```
┌─────────────────────────────────────────────────────────────┐
│  DEVELOPMENT (Package Developer)                            │
├─────────────────────────────────────────────────────────────┤
│  1. Edit: resources/css/hyro.css                            │
│  2. Build: npm run build                                    │
│  3. Test: Check public/build/assets/                        │
│  4. Commit: git add public/build/ && git commit             │
│  5. Push: git push                                          │
│  6. Tag: git tag v1.0.7 && git push --tags                  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  PRODUCTION (Package User)                                  │
├─────────────────────────────────────────────────────────────┤
│  1. Install: composer require marufsharia/hyro              │
│  2. Publish: php artisan hyro:publish-assets                │
│  3. Use: @hyroCss in Blade templates                        │
│  4. Done! No npm/build required ✅                           │
└─────────────────────────────────────────────────────────────┘
```

## File Size Comparison

```
┌─────────────────────────────────────────────────────────────┐
│  Source Files (Development)                                 │
├─────────────────────────────────────────────────────────────┤
│  resources/css/hyro.css          50 KB (with comments)      │
│  resources/js/hyro.js            30 KB (with comments)      │
└─────────────────────────────────────────────────────────────┘
                    │
                    │ npm run build
                    ▼
┌─────────────────────────────────────────────────────────────┐
│  Built Files (Production)                                   │
├─────────────────────────────────────────────────────────────┤
│  assets/hyro-[hash].css          12 KB (minified)           │
│  assets/hyro-[hash].js            8 KB (minified)           │
│                                                              │
│  Savings: 60% smaller! ✅                                    │
└─────────────────────────────────────────────────────────────┘
```

## Summary

**The Flow:**
1. **Developer** writes CSS/JS → builds with Vite → commits built files
2. **User** installs package → publishes assets → uses Blade directives
3. **Helper** reads manifest → finds versioned file → generates HTML tag
4. **Browser** loads asset → caches with unique filename → no cache issues

**Benefits:**
- ✅ Users don't need Node.js/npm
- ✅ Automatic cache busting
- ✅ Minified assets for production
- ✅ Fallback to raw assets
- ✅ Clean Blade syntax
- ✅ Easy to maintain
