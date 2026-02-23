<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

use Illuminate\Support\Collection;

/**
 * Hook Registry
 * 
 * Stores and manages registered hooks with priority-based sorting.
 */
class HookRegistry
{
    /**
     * Registered action hooks
     */
    protected array $actions = [];

    /**
     * Registered filter hooks
     */
    protected array $filters = [];

    /**
     * Register an action hook
     */
    public function registerAction(string $name, callable $callback, int $priority, string $pluginId): void
    {
        if (!isset($this->actions[$name])) {
            $this->actions[$name] = [];
        }

        $this->actions[$name][] = [
            'callback' => $callback,
            'priority' => $priority,
            'plugin_id' => $pluginId,
        ];

        // Sort by priority (lower number = higher priority)
        usort($this->actions[$name], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Register a filter hook
     */
    public function registerFilter(string $name, callable $callback, int $priority, string $pluginId): void
    {
        if (!isset($this->filters[$name])) {
            $this->filters[$name] = [];
        }

        $this->filters[$name][] = [
            'callback' => $callback,
            'priority' => $priority,
            'plugin_id' => $pluginId,
        ];

        // Sort by priority (lower number = higher priority)
        usort($this->filters[$name], fn($a, $b) => $a['priority'] <=> $b['priority']);
    }

    /**
     * Get all callbacks for an action hook
     */
    public function getActionCallbacks(string $name): array
    {
        return $this->actions[$name] ?? [];
    }

    /**
     * Get all callbacks for a filter hook
     */
    public function getFilterCallbacks(string $name): array
    {
        return $this->filters[$name] ?? [];
    }

    /**
     * Check if an action hook exists
     */
    public function hasAction(string $name): bool
    {
        return isset($this->actions[$name]) && !empty($this->actions[$name]);
    }

    /**
     * Check if a filter hook exists
     */
    public function hasFilter(string $name): bool
    {
        return isset($this->filters[$name]) && !empty($this->filters[$name]);
    }

    /**
     * Remove an action hook
     */
    public function removeAction(string $name, ?callable $callback = null): void
    {
        if (!isset($this->actions[$name])) {
            return;
        }

        if ($callback === null) {
            unset($this->actions[$name]);
            return;
        }

        $this->actions[$name] = array_filter(
            $this->actions[$name],
            fn($hook) => $hook['callback'] !== $callback
        );

        if (empty($this->actions[$name])) {
            unset($this->actions[$name]);
        }
    }

    /**
     * Remove a filter hook
     */
    public function removeFilter(string $name, ?callable $callback = null): void
    {
        if (!isset($this->filters[$name])) {
            return;
        }

        if ($callback === null) {
            unset($this->filters[$name]);
            return;
        }

        $this->filters[$name] = array_filter(
            $this->filters[$name],
            fn($hook) => $hook['callback'] !== $callback
        );

        if (empty($this->filters[$name])) {
            unset($this->filters[$name]);
        }
    }

    /**
     * Get all registered actions
     */
    public function getAllActions(): array
    {
        return $this->actions;
    }

    /**
     * Get all registered filters
     */
    public function getAllFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get all hooks (actions and filters)
     */
    public function getAllHooks(): array
    {
        return [
            'actions' => $this->actions,
            'filters' => $this->filters,
        ];
    }

    /**
     * Clear all hooks
     */
    public function clear(): void
    {
        $this->actions = [];
        $this->filters = [];
    }

    /**
     * Get hook count
     */
    public function count(string $type = 'all'): int
    {
        return match ($type) {
            'actions' => count($this->actions),
            'filters' => count($this->filters),
            default => count($this->actions) + count($this->filters),
        };
    }
}
