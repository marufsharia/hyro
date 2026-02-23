<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

/**
 * Hook Manager
 * 
 * Central manager for the Hyro hook system.
 * Provides a unified interface for action and filter hooks.
 */
class HookManager
{
    protected ActionHook $actions;
    protected FilterHook $filters;
    protected HookRegistry $registry;
    protected HookExecutor $executor;
    protected HookValidator $validator;

    public function __construct(
        ?HookRegistry $registry = null,
        ?HookExecutor $executor = null,
        ?HookValidator $validator = null
    ) {
        $this->registry = $registry ?? new HookRegistry();
        $this->validator = $validator ?? new HookValidator();
        
        // Get config values
        $timeout = config('hyro.hooks.timeout', 5);
        $logExecution = config('hyro.hooks.log_execution', false);
        $disabledHooks = config('hyro.hooks.disabled', []);
        
        $this->executor = $executor ?? new HookExecutor($timeout, $logExecution, $disabledHooks);
        
        $this->actions = new ActionHook($this->registry, $this->executor, $this->validator);
        $this->filters = new FilterHook($this->registry, $this->executor, $this->validator);
    }

    /**
     * Register an action hook
     * 
     * @param string $name Hook name (e.g., 'hyro.user.created')
     * @param callable $callback Callback function
     * @param int $priority Priority (lower = higher priority, default 10)
     * @param string $pluginId Plugin identifier (default 'core')
     */
    public function addAction(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->actions->add($name, $callback, $priority, $pluginId);
    }

    /**
     * Execute an action hook
     * 
     * @param string $name Hook name
     * @param mixed ...$args Arguments to pass to callbacks
     */
    public function doAction(string $name, mixed ...$args): void
    {
        $this->actions->do($name, ...$args);
    }

    /**
     * Register a filter hook
     * 
     * @param string $name Hook name (e.g., 'hyro.seo.title')
     * @param callable $callback Callback function (must return modified value)
     * @param int $priority Priority (lower = higher priority, default 10)
     * @param string $pluginId Plugin identifier (default 'core')
     */
    public function addFilter(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->filters->add($name, $callback, $priority, $pluginId);
    }

    /**
     * Apply filter hooks to a value
     * 
     * @param string $name Hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments to pass to callbacks
     * @return mixed Filtered value
     */
    public function applyFilters(string $name, mixed $value, mixed ...$args): mixed
    {
        return $this->filters->apply($name, $value, ...$args);
    }

    /**
     * Remove an action hook
     * 
     * @param string $name Hook name
     * @param callable|null $callback Specific callback to remove, or null to remove all
     */
    public function removeAction(string $name, ?callable $callback = null): void
    {
        $this->actions->remove($name, $callback);
    }

    /**
     * Remove a filter hook
     * 
     * @param string $name Hook name
     * @param callable|null $callback Specific callback to remove, or null to remove all
     */
    public function removeFilter(string $name, ?callable $callback = null): void
    {
        $this->filters->remove($name, $callback);
    }

    /**
     * Remove any hook (action or filter)
     * 
     * @param string $name Hook name
     * @param callable|null $callback Specific callback to remove, or null to remove all
     */
    public function removeHook(string $name, ?callable $callback = null): void
    {
        $this->removeAction($name, $callback);
        $this->removeFilter($name, $callback);
    }

    /**
     * Check if an action hook exists
     */
    public function hasAction(string $name): bool
    {
        return $this->actions->has($name);
    }

    /**
     * Check if a filter hook exists
     */
    public function hasFilter(string $name): bool
    {
        return $this->filters->has($name);
    }

    /**
     * Check if any hook (action or filter) exists
     */
    public function hasHook(string $name): bool
    {
        return $this->hasAction($name) || $this->hasFilter($name);
    }

    /**
     * Get all registered hooks
     * 
     * @param string $type Type of hooks to get ('all', 'actions', 'filters')
     * @return array
     */
    public function getHooks(string $type = 'all'): array
    {
        return match ($type) {
            'actions' => $this->actions->getAll(),
            'filters' => $this->filters->getAll(),
            default => [
                'actions' => $this->actions->getAll(),
                'filters' => $this->filters->getAll(),
            ],
        };
    }

    /**
     * Get callbacks for a specific hook
     * 
     * @param string $name Hook name
     * @param string $type Type of hook ('action' or 'filter')
     * @return array
     */
    public function getCallbacks(string $name, string $type = 'action'): array
    {
        return match ($type) {
            'filter' => $this->filters->getCallbacks($name),
            default => $this->actions->getCallbacks($name),
        };
    }

    /**
     * Clear all hooks
     */
    public function clear(): void
    {
        $this->registry->clear();
    }

    /**
     * Get hook count
     * 
     * @param string $type Type of hooks to count ('all', 'actions', 'filters')
     * @return int
     */
    public function count(string $type = 'all'): int
    {
        return $this->registry->count($type);
    }

    /**
     * Get the hook registry
     */
    public function getRegistry(): HookRegistry
    {
        return $this->registry;
    }

    /**
     * Get the hook executor
     */
    public function getExecutor(): HookExecutor
    {
        return $this->executor;
    }

    /**
     * Get the hook validator
     */
    public function getValidator(): HookValidator
    {
        return $this->validator;
    }

    /**
     * Get the action hook handler
     */
    public function actions(): ActionHook
    {
        return $this->actions;
    }

    /**
     * Get the filter hook handler
     */
    public function filters(): FilterHook
    {
        return $this->filters;
    }
}
