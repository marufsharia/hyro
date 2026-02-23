<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

/**
 * Filter Hook
 * 
 * Handles filter hooks (data modification with return values).
 */
class FilterHook
{
    public function __construct(
        protected HookRegistry $registry,
        protected HookExecutor $executor,
        protected HookValidator $validator
    ) {}

    /**
     * Register a filter hook
     * 
     * @param string $name Hook name (e.g., 'hyro.seo.title')
     * @param callable $callback Callback function (must return modified value)
     * @param int $priority Priority (lower = higher priority)
     * @param string $pluginId Plugin identifier
     * @throws \InvalidArgumentException If hook name is invalid
     */
    public function add(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->validator->validate($name);
        $this->registry->registerFilter($name, $callback, $priority, $pluginId);
    }

    /**
     * Apply filter hooks to a value
     * 
     * @param string $name Hook name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments to pass to callbacks
     * @return mixed Filtered value
     */
    public function apply(string $name, mixed $value, mixed ...$args): mixed
    {
        $callbacks = $this->registry->getFilterCallbacks($name);

        if (empty($callbacks)) {
            return $value;
        }

        $filteredValue = $value;

        foreach ($callbacks as $hook) {
            $filteredValue = $this->executor->executeFilter(
                $name,
                $hook['callback'],
                $filteredValue,
                $args,
                $hook['plugin_id']
            );
        }

        return $filteredValue;
    }

    /**
     * Remove a filter hook
     * 
     * @param string $name Hook name
     * @param callable|null $callback Specific callback to remove, or null to remove all
     */
    public function remove(string $name, ?callable $callback = null): void
    {
        $this->registry->removeFilter($name, $callback);
    }

    /**
     * Check if a filter hook exists
     */
    public function has(string $name): bool
    {
        return $this->registry->hasFilter($name);
    }

    /**
     * Get all registered filter hooks
     */
    public function getAll(): array
    {
        return $this->registry->getAllFilters();
    }

    /**
     * Get callbacks for a specific filter hook
     */
    public function getCallbacks(string $name): array
    {
        return $this->registry->getFilterCallbacks($name);
    }
}
