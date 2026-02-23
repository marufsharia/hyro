<?php

namespace Marufsharia\Hyro\Core\Support\Assets;

use Illuminate\Support\Facades\File;

class AssetHelper
{
    /**
     * Get the manifest from the dist directory.
     *
     * @return array
     */
    public static function getManifest(): array
    {
        $manifestPath = public_path('vendor/hyro/manifest.json');

        if (!File::exists($manifestPath)) {
            return [];
        }

        try {
            $content = file_get_contents($manifestPath);
            return json_decode($content, true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the versioned asset URL from manifest.
     *
     * @param string $path Original asset path (e.g., 'resources/css/hyro.css')
     * @return string|null
     */
    public static function getVersionedAsset(string $path): ?string
    {
        $manifest = static::getManifest();

        if (isset($manifest[$path]['file'])) {
            return asset('vendor/hyro/' . $manifest[$path]['file']);
        }

        return null;
    }

    /**
     * Get CSS asset URL.
     *
     * @return string|null
     */
    public static function getCssUrl(): ?string
    {
        return static::getVersionedAsset('resources/css/hyro.css');
    }

    /**
     * Get JS asset URL.
     *
     * @return string|null
     */
    public static function getJsUrl(): ?string
    {
        return static::getVersionedAsset('resources/js/hyro.js');
    }

    /**
     * Check if built assets exist.
     *
     * @return bool
     */
    public static function hasBuiltAssets(): bool
    {
        return File::exists(public_path('vendor/hyro/manifest.json'));
    }

    /**
     * Get the path to the package dist directory.
     *
     * @return string
     */
    public static function getPackageDistPath(): string
    {
        return __DIR__ . '/../../../dist';
    }

    /**
     * Check if package has built assets.
     *
     * @return bool
     */
    public static function packageHasBuiltAssets(): bool
    {
        return File::exists(static::getPackageDistPath() . '/manifest.json');
    }
}
