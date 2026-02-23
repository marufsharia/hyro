<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

/**
 * Action Hook
 * 
 * Handles action hooks (fire-and-forget events with no return value).
 */
class ActionHook
{
    public function __construct(
        protected HookRegistry $registry,
        protected HookExecutor $executor,
        protected HookValidator $validator
    ) {}

    /**
     * Register an action hook
     * 
     * @param string $name Hook name (e.g., 'hyro.user.created')
     * @param callable $callback Callback function
     * @param int $priority Priority (lower = higher priority)
     * @param string $pluginId Plugin identifier
     * @throws \InvalidArgumentException If hook name is invalid
     */
    public function add(string $name, callable $callback, int $priority = 10, string $pluginId = 'core'): void
    {
        $this->validator->validate($name);
        $this->registry->registerAction($name, $callback, $priority, $pluginId);
    }

    /**
     * Execute an action hook
     * 
     * @param string $name Hook name
     * @param mixed ...$args Arguments to pass to callbacks
     * @return void
     */
    public function do(string $name, mixed ...$args): void
    {
        $callbacks = $this->registry->getActionCallbacks($name);

        if (empty($callbacks)) {
            return;
        }

        foreach ($callbacks as $hook) {
            $this->executor->executeAction(
                $name,
                $hook['callback'],
                $args,
                $hook['plugin_id']
            );
        }
    }

    /**
     * Remove an action hook
     * 
     * @param string $name Hook name
     * @param callable|null $callback Specific callback to remove, or null to remove all
     */
    public function remove(string $name, ?callable $callback = null): void
    {
        $this->registry->removeAction($name, $callback);
    }

    /**
     * Check if an action hook exists
     */
    public function has(string $name): bool
    {
        return $this->registry->hasAction($name);
    }

    /**
     * Get all registered action hooks
     */
    public function getAll(): array
    {
        return $this->registry->getAllActions();
    }

    /**
     * Get callbacks for a specific action hook
     */
    public function getCallbacks(string $name): array
    {
        return $this->registry->getActionCallbacks($name);
    }
}
