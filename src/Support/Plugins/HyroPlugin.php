<?php

namespace Marufsharia\Hyro\Support\Plugins;

use Illuminate\Contracts\Foundation\Application;

/**
 * Base Plugin Class
 * All Hyro plugins must extend this class
 */
abstract class HyroPlugin
{
    protected Application $app;

    public function __construct(Application $app = null)
    {
        $this->app = $app ?? app();
    }

    /**
     * Plugin unique identifier (kebab-case)
     */
    abstract public function getId(): string;

    /**
     * Plugin display name
     */
    abstract public function getName(): string;

    /**
     * Plugin description
     */
    abstract public function getDescription(): string;

    /**
     * Plugin version (semver)
     */
    abstract public function getVersion(): string;

    /**
     * Plugin author
     */
    abstract public function getAuthor(): string;

    /**
     * Boot the plugin (called after all plugins are registered)
     */
    abstract public function boot(): void;

    /**
     * Register plugin services (called before boot)
     */
    abstract public function register(): void;

    /**
     * Get plugin dependencies
     *
     * @return array<string> Array of plugin IDs this plugin depends on
     */
    public function getDependencies(): array
    {
        return [];
    }

    /**
     * Get plugin configuration
     */
    public function getConfig(): array
    {
        return [];
    }

    /**
     * Check if plugin is enabled
     */
    public function isEnabled(): bool
    {
        return config("hyro.plugins.{$this->getId()}.enabled", true);
    }

    /**
     * Get plugin routes file path
     */
    public function routes(): ?string
    {
        return null;
    }

    /**
     * Get plugin migrations path
     */
    public function migrations(): ?string
    {
        return null;
    }

    /**
     * Get plugin views path
     */
    public function views(): ?string
    {
        return null;
    }

    /**
     * Get plugin assets path
     */
    public function assets(): ?string
    {
        return null;
    }

    /**
     * Get plugin translations path
     */
    public function lang(): ?string
    {
        return null;
    }

    /**
     * Plugin installation hook
     */
    public function install(): void
    {
        //
    }

    /**
     * Plugin uninstallation hook
     */
    public function uninstall(): void
    {
        //
    }

    /**
     * Plugin activation hook
     */
    public function activate(): void
    {
        //
    }

    /**
     * Plugin deactivation hook
     */
    public function deactivate(): void
    {
        //
    }

    /**
     * Plugin update hook
     */
    public function update(string $oldVersion, string $newVersion): void
    {
        //
    }

    /**
     * Get minimum Hyro version required
     */
    public function getMinimumHyroVersion(): string
    {
        return '1.0.0';
    }

    /**
     * Check if plugin is compatible with current Hyro version
     */
    public function isCompatible(): bool
    {
        $currentVersion = config('hyro.version', '1.0.0');
        return version_compare($currentVersion, $this->getMinimumHyroVersion(), '>=');
    }

    /**
     * Output info message (console only)
     */
    protected function info(string $message): void
    {
        if ($this->app->runningInConsole()) {
            echo "[{$this->getName()}] {$message}\n";
        }
    }

    /**
     * Output error message (console only)
     */
    protected function error(string $message): void
    {
        if ($this->app->runningInConsole()) {
            echo "[{$this->getName()}] ERROR: {$message}\n";
        }
    }

    /**
     * Output warning message (console only)
     */
    protected function warn(string $message): void
    {
        if ($this->app->runningInConsole()) {
            echo "[{$this->getName()}] WARNING: {$message}\n";
        }
    }
}
