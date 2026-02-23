<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

use InvalidArgumentException;

/**
 * Hook Validator
 * 
 * Validates hook names and enforces naming conventions.
 */
class HookValidator
{
    /**
     * Core namespace prefix
     */
    protected const CORE_PREFIX = 'hyro.';

    /**
     * Plugin namespace prefix
     */
    protected const PLUGIN_PREFIX = 'plugin.';

    /**
     * Validate hook name format
     * 
     * @throws InvalidArgumentException
     */
    public function validate(string $name): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Hook name cannot be empty');
        }

        if (!$this->isValidFormat($name)) {
            throw new InvalidArgumentException(
                "Invalid hook name format: '{$name}'. " .
                "Hook names must use dot notation (e.g., 'hyro.user.created' or 'plugin.myplugin.action')"
            );
        }

        if (!$this->hasValidNamespace($name)) {
            throw new InvalidArgumentException(
                "Invalid hook namespace: '{$name}'. " .
                "Hook names must start with 'hyro.' for core hooks or 'plugin.' for plugin hooks"
            );
        }
    }

    /**
     * Check if hook name has valid format (dot notation)
     */
    protected function isValidFormat(string $name): bool
    {
        // Must contain at least one dot
        if (!str_contains($name, '.')) {
            return false;
        }

        // Must not start or end with dot
        if (str_starts_with($name, '.') || str_ends_with($name, '.')) {
            return false;
        }

        // Must not have consecutive dots
        if (str_contains($name, '..')) {
            return false;
        }

        // Must only contain alphanumeric, dots, underscores, and hyphens
        if (!preg_match('/^[a-z0-9._-]+$/i', $name)) {
            return false;
        }

        return true;
    }

    /**
     * Check if hook name has valid namespace
     */
    protected function hasValidNamespace(string $name): bool
    {
        return str_starts_with($name, self::CORE_PREFIX) ||
               str_starts_with($name, self::PLUGIN_PREFIX);
    }

    /**
     * Check if hook is a core hook
     */
    public function isCoreHook(string $name): bool
    {
        return str_starts_with($name, self::CORE_PREFIX);
    }

    /**
     * Check if hook is a plugin hook
     */
    public function isPluginHook(string $name): bool
    {
        return str_starts_with($name, self::PLUGIN_PREFIX);
    }

    /**
     * Extract plugin ID from plugin hook name
     * 
     * @return string|null Plugin ID or null if not a plugin hook
     */
    public function extractPluginId(string $name): ?string
    {
        if (!$this->isPluginHook($name)) {
            return null;
        }

        $parts = explode('.', $name);
        
        // plugin.{plugin-id}.{action}
        return $parts[1] ?? null;
    }

    /**
     * Get recommended hook name format
     */
    public function getRecommendedFormat(): string
    {
        return "Core hooks: 'hyro.{category}.{action}' (e.g., 'hyro.user.created')\n" .
               "Plugin hooks: 'plugin.{plugin-id}.{action}' (e.g., 'plugin.analytics.page_viewed')";
    }
}
