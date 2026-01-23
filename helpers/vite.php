<?php

namespace Marufsharia\Hyro\Helpers;

use Illuminate\Support\Facades\Vite as LaravelVite;

class Vite
{
    /**
     * Get the Vite tags for the Hyro assets.
     *
     * @param  bool  $withDevServer  Include the Vite dev server client
     * @return string
     */
    public static function tags($withDevServer = false)
    {
        $manifestPath = __DIR__ . '/../../public/build/manifest.json';

        // Check if running in development mode
        $isDevelopment = app()->environment('local') && file_exists($manifestPath);

        if ($isDevelopment && $withDevServer) {
            // In development, use Vite's dev server
            return LaravelVite::useHotFile(__DIR__ . '/../../public/hot')
                ->useBuildDirectory('build')
                ->withEntryPoints(['resources/css/hyro.css', 'resources/js/hyro.js']);
        }

        // In production, use compiled assets
        $cssPath = self::asset('assets/css/hyro.css');
        $jsPath = self::asset('assets/js/hyro.js');

        $tags = '';

        if ($cssPath) {
            $tags .= '<link rel="stylesheet" href="' . $cssPath . '">';
        }

        if ($jsPath) {
            $tags .= '<script type="module" src="' . $jsPath . '" defer></script>';
        }

        return $tags;
    }

    /**
     * Get the asset URL.
     *
     * @param  string  $path
     * @return string|null
     */
    public static function asset($path)
    {
        $manifestPath = __DIR__ . '/../../public/build/manifest.json';

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);

            if (isset($manifest[$path])) {
                return '/vendor/hyro/build/' . $manifest[$path]['file'];
            }
        }

        // Fallback to regular asset path
        return asset('vendor/hyro/' . $path);
    }

    /**
     * Check if Vite dev server is running.
     *
     * @return bool
     */
    public static function isDevServerRunning()
    {
        $hotFile = __DIR__ . '/../../public/hot';

        if (!file_exists($hotFile)) {
            return false;
        }

        $url = trim(file_get_contents($hotFile));

        // Test if the dev server is accessible
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}
