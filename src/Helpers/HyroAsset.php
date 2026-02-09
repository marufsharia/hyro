<?php
namespace Marufsharia\Hyro\Helpers;

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
        $packageManifest = base_path('packages/marufsharia/hyro/public/build/manifest.json');
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
     * @return string|null
     */
    public static function css(): ?string
    {
        $css = self::asset('resources/css/hyro.css');
        return $css ? "<link rel=\"stylesheet\" href=\"{$css}\">" : null;
    }

    /**
     * Get JS script tag.
     *
     * @return string|null
     */
    public static function js(): ?string
    {
        $js = self::asset('resources/js/hyro.js');
        return $js ? "<script type=\"module\" src=\"{$js}\"></script>" : null;
    }

    /**
     * Get asset URL from manifest with smart loading.
     *
     * @param string $entry
     * @return string|null
     */
    public static function asset(string $entry): ?string
    {
        $manifest = static::manifest();

        if (!isset($manifest[$entry])) {
            // If not in manifest, try direct path
            $directPath = public_path('vendor/hyro/' . $entry);
            if (File::exists($directPath)) {
                return asset('vendor/hyro/' . $entry);
            }
            return null;
        }

        $baseUrl = self::getAssetBaseUrl();
        return $baseUrl . '/' . $manifest[$entry]['file'];
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

        return collect([
            $css,
            $js,
        ])->filter()->implode("\n");
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
     * @param string $imagePath
     * @return string
     */
    public static function image(string $imagePath): string
    {
        $publishedImage = public_path('vendor/hyro/images/' . $imagePath);
        
        if (File::exists($publishedImage)) {
            return asset('vendor/hyro/images/' . $imagePath);
        }

        // Fallback to package images
        return asset('vendor/hyro/images/' . $imagePath);
    }
}
