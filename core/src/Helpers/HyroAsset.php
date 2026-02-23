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
        
        // Fallback to raw CSS
        if (File::exists(public_path('vendor/hyro/css/hyro-alert.css'))) {
            return '<link rel="stylesheet" href="' . asset('vendor/hyro/css/hyro-alert.css') . '">';
        }
        
        // Return empty string instead of null to avoid blade errors
        return '<!-- Hyro CSS not found. Run: php artisan hyro:publish-assets -->';
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
        
        // Fallback to raw JS
        if (File::exists(public_path('vendor/hyro/js/hyro-alert.js'))) {
            return '<script src="' . asset('vendor/hyro/js/hyro-alert.js') . '"></script>';
        }
        
        // Return empty string instead of null to avoid blade errors
        return '<!-- Hyro JS not found. Run: php artisan hyro:publish-assets -->';
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

