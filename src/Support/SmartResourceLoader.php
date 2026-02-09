<?php

namespace Marufsharia\Hyro\Support;

use Illuminate\Support\Facades\File;

/**
 * Smart Resource Loader
 * 
 * Provides intelligent resource loading with automatic fallback support.
 * Allows users to customize package resources without modifying vendor files.
 * 
 * @package Marufsharia\Hyro\Support
 */
class SmartResourceLoader
{
    /**
     * Check if a resource exists in published location.
     *
     * @param string $publishedPath
     * @return bool
     */
    public static function isPublished(string $publishedPath): bool
    {
        return File::exists($publishedPath);
    }

    /**
     * Get the path to use for a resource (published or package).
     *
     * @param string $publishedPath
     * @param string $packagePath
     * @param bool $preferPublished
     * @return string|null
     */
    public static function getResourcePath(
        string $publishedPath,
        string $packagePath,
        bool $preferPublished = true
    ): ?string {
        if ($preferPublished && File::exists($publishedPath)) {
            return $publishedPath;
        }

        if (File::exists($packagePath)) {
            return $packagePath;
        }

        if (!$preferPublished && File::exists($publishedPath)) {
            return $publishedPath;
        }

        return null;
    }

    /**
     * Get both published and package paths if they exist.
     * Useful for loading resources from both locations.
     *
     * @param string $publishedPath
     * @param string $packagePath
     * @return array
     */
    public static function getBothPaths(string $publishedPath, string $packagePath): array
    {
        $paths = [];

        if (File::exists($publishedPath)) {
            $paths[] = $publishedPath;
        }

        if (File::exists($packagePath)) {
            $paths[] = $packagePath;
        }

        return $paths;
    }

    /**
     * Check if migrations are published.
     *
     * @return bool
     */
    public static function areMigrationsPublished(): bool
    {
        return File::exists(database_path('migrations/hyro'));
    }

    /**
     * Check if views are published.
     *
     * @return bool
     */
    public static function areViewsPublished(): bool
    {
        return File::exists(resource_path('views/vendor/hyro'));
    }

    /**
     * Check if routes are published.
     *
     * @return bool
     */
    public static function areRoutesPublished(): bool
    {
        return File::exists(base_path('routes/hyro'));
    }

    /**
     * Check if translations are published.
     *
     * @return bool
     */
    public static function areTranslationsPublished(): bool
    {
        return File::exists(resource_path('lang/vendor/hyro'));
    }

    /**
     * Check if events are published.
     *
     * @return bool
     */
    public static function areEventsPublished(): bool
    {
        return File::exists(app_path('Events/Hyro'));
    }

    /**
     * Check if providers are published.
     *
     * @return bool
     */
    public static function areProvidersPublished(): bool
    {
        return File::exists(app_path('Providers/Hyro'));
    }

    /**
     * Check if services are published.
     *
     * @return bool
     */
    public static function areServicesPublished(): bool
    {
        return File::exists(app_path('Services/Hyro'));
    }

    /**
     * Check if middleware are published.
     *
     * @return bool
     */
    public static function areMiddlewarePublished(): bool
    {
        return File::exists(app_path('Http/Middleware/Hyro'));
    }

    /**
     * Check if models are published.
     *
     * @return bool
     */
    public static function areModelsPublished(): bool
    {
        return File::exists(app_path('Models/Hyro'));
    }

    /**
     * Check if CRUD routes are registered.
     *
     * @return bool
     */
    public static function areCrudRoutesRegistered(): bool
    {
        return File::exists(base_path('routes/hyro/crud.php'));
    }

    /**
     * Get publication status for all resources.
     *
     * @return array
     */
    public static function getPublicationStatus(): array
    {
        return [
            'migrations' => self::areMigrationsPublished(),
            'views' => self::areViewsPublished(),
            'routes' => self::areRoutesPublished(),
            'crud_routes' => self::areCrudRoutesRegistered(),
            'translations' => self::areTranslationsPublished(),
            'events' => self::areEventsPublished(),
            'providers' => self::areProvidersPublished(),
            'services' => self::areServicesPublished(),
            'middleware' => self::areMiddlewarePublished(),
            'models' => self::areModelsPublished(),
            'assets' => \Marufsharia\Hyro\Helpers\HyroAsset::areAssetsPublished(),
        ];
    }

    /**
     * Get a summary of published resources.
     *
     * @return array
     */
    public static function getPublishedResourcesSummary(): array
    {
        $status = self::getPublicationStatus();
        $published = array_filter($status);
        
        return [
            'total' => count($status),
            'published' => count($published),
            'unpublished' => count($status) - count($published),
            'details' => $status,
        ];
    }

    /**
     * Check if a specific resource type is published.
     *
     * @param string $resourceType
     * @return bool
     */
    public static function isResourcePublished(string $resourceType): bool
    {
        $method = 'are' . ucfirst($resourceType) . 'Published';
        
        if (method_exists(self::class, $method)) {
            return self::$method();
        }

        return false;
    }

    /**
     * Get the loading strategy for a resource.
     *
     * @param string $resourceType
     * @return string (published|package|both|none)
     */
    public static function getLoadingStrategy(string $resourceType): string
    {
        $isPublished = self::isResourcePublished($resourceType);
        
        // Resources that support both locations
        $bothSupported = ['migrations', 'views', 'translations'];
        
        if (in_array($resourceType, $bothSupported)) {
            return $isPublished ? 'both' : 'package';
        }
        
        // Resources that use published or package
        return $isPublished ? 'published' : 'package';
    }
}
