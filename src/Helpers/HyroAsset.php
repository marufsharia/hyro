<?php
namespace Marufsharia\Hyro\Helpers;

class HyroAsset
{
    protected static function manifest(): array
    {
        $manifestPath = public_path('vendor/hyro/.vite/manifest.json');

        if (! file_exists($manifestPath)) {
            return [];
        }

        return json_decode(file_get_contents($manifestPath), true);
    }

    public static function asset(string $entry): ?string
    {
        $manifest = static::manifest();

        if (! isset($manifest[$entry])) {
            return null;
        }

        return asset('vendor/hyro/' . $manifest[$entry]['file']);
    }

    public static function tags(): string
    {
        $css = self::asset('resources/css/hyro.css');
        $js  = self::asset('resources/js/hyro.js');

        return collect([
            $css ? "<link rel=\"stylesheet\" href=\"{$css}\">" : null,
            $js  ? "<script type=\"module\" src=\"{$js}\"></script>" : null,
        ])->filter()->implode("\n");
    }
}
