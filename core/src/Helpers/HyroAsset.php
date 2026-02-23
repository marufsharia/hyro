<?php
namespace Marufsharia\Hyro\Core\Helpers;

use Illuminate\Support\Facades\File;

class HyroAsset
{
    /**
     * Get the manifest file path with smart loading.
     * Checks published assets first, then falls back to package assets.
     *
     * @return string|null
     */
    protected static function getManifestPath(): ?string
    {
        // Check published assets first
        $publishedManifest = public_path('vendor/hyro/manifest.json');
        if (File::exists($publishedManifest)) {
            return $publishedManifest;
        }

        // Fallback to package assets (for development)
        $packageManifest = base_path('vendor/marufsharia/hyro/public/build/manifest.json');
        if (File::exists($packageManifest)) {
            return $packageManifest;
        }

        return null;
    }

    /**
     * Load and parse the manifest file.
     *
     * @return array
     */
    protected static function manifest(): array
    {
        $manifestPath = self::getManifestPath();

        if (!$manifestPath || !file_exists($manifestPath)) {
            return [];
        }

        return json_decode(file_get_contents($manifestPath), true) ?? [];
    }

    /**
     * Get the base URL for assets with smart loading.
     *
     * @return string
     */
    protected static function getAssetBaseUrl(): string
    {
        // Always use the public vendor path for assets
        // Assets should be published to public/vendor/hyro
        return asset('vendor/hyro');
    }

    /**
     * Get CSS link tag.
     *
     * @return string
     */
    public static function css(): string
    {
        $manifest = static::manifest();
        
        // Try to get from manifest
        if (isset($manifest['resources/css/hyro.css'])) {
            $file = $manifest['resources/css/hyro.css']['file'];
            $url = asset('vendor/hyro/' . $file);
            return "<link rel=\"stylesheet\" href=\"{$url}\">";
        }
        
        // Fallback: Load Tailwind CSS from CDN + Hyro alert styles
        $output = '';
        
        // Add Tailwind CSS v3 from CDN (compatible with the layouts)
        $output .= '<script src="https://cdn.tailwindcss.com"></script>' . "\n";
        
        // Add Hyro alert CSS if available
        if (File::exists(public_path('vendor/hyro/css/hyro-alert.css'))) {
            $output .= '<link rel="stylesheet" href="' . asset('vendor/hyro/css/hyro-alert.css') . '">';
        }
        
        // Add inline styles for Hyro-specific utilities
        $output .= '<style>
        [x-cloak] { display: none !important; }
        .text-balance { text-wrap: balance; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-dark { background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>';
        
        return $output;
    }

    /**
     * Get JS script tag.
     *
     * @return string
     */
    public static function js(): string
    {
        $manifest = static::manifest();
        
        // Try to get from manifest
        if (isset($manifest['resources/js/hyro.js'])) {
            $file = $manifest['resources/js/hyro.js']['file'];
            $url = asset('vendor/hyro/' . $file);
            return "<script type=\"module\" src=\"{$url}\"></script>";
        }
        
        // Fallback: Load Alpine.js and plugins from CDN + Hyro alert JS
        $output = '';
        
        // Add Alpine.js v3 and plugins from CDN
        $output .= '<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>' . "\n";
        $output .= '<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>' . "\n";
        $output .= '<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>' . "\n";
        $output .= '<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>' . "\n";
        
        // Add Hyro alert JS if available
        if (File::exists(public_path('vendor/hyro/js/hyro-alert.js'))) {
            $output .= '<script src="' . asset('vendor/hyro/js/hyro-alert.js') . '"></script>';
        }
        
        return $output;
    }

    /**
     * Get all asset tags (CSS and JS).
     *
     * @return string
     */
    public static function tags(): string
    {
        $css = self::css();
        $js = self::js();

        return $css . "\n" . $js;
    }

    /**
     * Check if assets are published.
     *
     * @return bool
     */
    public static function areAssetsPublished(): bool
    {
        return File::exists(public_path('vendor/hyro/manifest.json'));
    }

    /**
     * Get image URL with smart loading.
     *
     * @param string $path
     * @return string
     */
    public static function image(string $path): string
    {
        $publishedImage = public_path('vendor/hyro/images/' . $path);
        
        if (File::exists($publishedImage)) {
            return asset('vendor/hyro/images/' . $path);
        }

        // Fallback to package images
        return asset('vendor/hyro/images/' . $path);
    }
}

