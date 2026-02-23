<?php

namespace Marufsharia\Hyro\Core\Support\Assets;

class AssetManager
{
    /**
     * Registered stylesheets.
     *
     * @var array<string, string>
     */
    protected static array $styles = [];

    /**
     * Registered scripts.
     *
     * @var array<string, string>
     */
    protected static array $scripts = [];

    /**
     * Registered inline styles.
     *
     * @var array<string, string>
     */
    protected static array $inlineStyles = [];

    /**
     * Registered inline scripts.
     *
     * @var array<string, string>
     */
    protected static array $inlineScripts = [];

    /**
     * Register a stylesheet.
     *
     * @param string $name Unique identifier for the stylesheet
     * @param string $path URL or path to the stylesheet
     * @param array $attributes Additional HTML attributes
     * @return void
     */
    public static function registerStyle(string $name, string $path, array $attributes = []): void
    {
        static::$styles[$name] = [
            'path' => $path,
            'attributes' => $attributes,
        ];
    }

    /**
     * Register a script.
     *
     * @param string $name Unique identifier for the script
     * @param string $path URL or path to the script
     * @param array $attributes Additional HTML attributes
     * @return void
     */
    public static function registerScript(string $name, string $path, array $attributes = []): void
    {
        static::$scripts[$name] = [
            'path' => $path,
            'attributes' => $attributes,
        ];
    }

    /**
     * Register inline CSS.
     *
     * @param string $name Unique identifier
     * @param string $css CSS content
     * @return void
     */
    public static function registerInlineStyle(string $name, string $css): void
    {
        static::$inlineStyles[$name] = $css;
    }

    /**
     * Register inline JavaScript.
     *
     * @param string $name Unique identifier
     * @param string $js JavaScript content
     * @return void
     */
    public static function registerInlineScript(string $name, string $js): void
    {
        static::$inlineScripts[$name] = $js;
    }

    /**
     * Get all registered stylesheets.
     *
     * @return array<string, array>
     */
    public static function getStyles(): array
    {
        return static::$styles;
    }

    /**
     * Get all registered scripts.
     *
     * @return array<string, array>
     */
    public static function getScripts(): array
    {
        return static::$scripts;
    }

    /**
     * Get all inline styles.
     *
     * @return array<string, string>
     */
    public static function getInlineStyles(): array
    {
        return static::$inlineStyles;
    }

    /**
     * Get all inline scripts.
     *
     * @return array<string, string>
     */
    public static function getInlineScripts(): array
    {
        return static::$inlineScripts;
    }

    /**
     * Check if a style is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasStyle(string $name): bool
    {
        return isset(static::$styles[$name]);
    }

    /**
     * Check if a script is registered.
     *
     * @param string $name
     * @return bool
     */
    public static function hasScript(string $name): bool
    {
        return isset(static::$scripts[$name]);
    }

    /**
     * Remove a registered style.
     *
     * @param string $name
     * @return void
     */
    public static function removeStyle(string $name): void
    {
        unset(static::$styles[$name]);
    }

    /**
     * Remove a registered script.
     *
     * @param string $name
     * @return void
     */
    public static function removeScript(string $name): void
    {
        unset(static::$scripts[$name]);
    }

    /**
     * Clear all registered assets.
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$styles = [];
        static::$scripts = [];
        static::$inlineStyles = [];
        static::$inlineScripts = [];
    }

    /**
     * Render all stylesheets as HTML.
     *
     * @return string
     */
    public static function renderStyles(): string
    {
        $html = '';

        foreach (static::$styles as $name => $style) {
            $attributes = static::buildAttributes($style['attributes']);
            $html .= sprintf(
                '<link rel="stylesheet" href="%s"%s data-hyro-asset="%s">' . "\n",
                $style['path'],
                $attributes ? ' ' . $attributes : '',
                $name
            );
        }

        // Add inline styles
        if (!empty(static::$inlineStyles)) {
            $html .= '<style data-hyro-inline-styles>' . "\n";
            foreach (static::$inlineStyles as $name => $css) {
                $html .= "/* {$name} */\n{$css}\n";
            }
            $html .= '</style>' . "\n";
        }

        return $html;
    }

    /**
     * Render all scripts as HTML.
     *
     * @return string
     */
    public static function renderScripts(): string
    {
        $html = '';

        foreach (static::$scripts as $name => $script) {
            $attributes = static::buildAttributes($script['attributes']);
            $html .= sprintf(
                '<script src="%s"%s data-hyro-asset="%s"></script>' . "\n",
                $script['path'],
                $attributes ? ' ' . $attributes : '',
                $name
            );
        }

        // Add inline scripts
        if (!empty(static::$inlineScripts)) {
            $html .= '<script data-hyro-inline-scripts>' . "\n";
            foreach (static::$inlineScripts as $name => $js) {
                $html .= "/* {$name} */\n{$js}\n";
            }
            $html .= '</script>' . "\n";
        }

        return $html;
    }

    /**
     * Build HTML attributes string.
     *
     * @param array $attributes
     * @return string
     */
    protected static function buildAttributes(array $attributes): string
    {
        $html = [];

        foreach ($attributes as $key => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html[] = $key;
                }
            } else {
                $html[] = sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES));
            }
        }

        return implode(' ', $html);
    }
}
