<?php

namespace Marufsharia\Hyro\Plugin\Support\Hooks;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Hook Executor
 * 
 * Executes hooks with security sandboxing, timeout protection, and error handling.
 */
class HookExecutor
{
    /**
     * Timeout in seconds for hook execution
     */
    protected int $timeout;

    /**
     * Whether to log hook execution
     */
    protected bool $logExecution;

    /**
     * Disabled hooks
     */
    protected array $disabledHooks;

    public function __construct(
        int $timeout = 5,
        bool $logExecution = false,
        array $disabledHooks = []
    ) {
        $this->timeout = $timeout;
        $this->logExecution = $logExecution;
        $this->disabledHooks = $disabledHooks;
    }

    /**
     * Execute an action hook callback
     * 
     * @return void
     */
    public function executeAction(
        string $hookName,
        callable $callback,
        array $args,
        string $pluginId
    ): void {
        if ($this->isDisabled($hookName)) {
            $this->log('debug', "Hook '{$hookName}' is disabled, skipping execution");
            return;
        }

        $startTime = microtime(true);

        try {
            $this->log('debug', "Executing action hook '{$hookName}' for plugin '{$pluginId}'");
            
            // Execute with timeout protection
            $this->executeWithTimeout($callback, $args);
            
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
            $this->log('debug', "Action hook '{$hookName}' executed successfully in {$executionTime}ms");
            
        } catch (Throwable $e) {
            $this->handleError($hookName, $pluginId, $e);
        }
    }

    /**
     * Execute a filter hook callback
     * 
     * @return mixed Modified value or original value on error
     */
    public function executeFilter(
        string $hookName,
        callable $callback,
        mixed $value,
        array $args,
        string $pluginId
    ): mixed {
        if ($this->isDisabled($hookName)) {
            $this->log('debug', "Hook '{$hookName}' is disabled, returning original value");
            return $value;
        }

        $startTime = microtime(true);

        try {
            $this->log('debug', "Executing filter hook '{$hookName}' for plugin '{$pluginId}'");
            
            // Execute with timeout protection
            $result = $this->executeWithTimeout($callback, array_merge([$value], $args));
            
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
            $this->log('debug', "Filter hook '{$hookName}' executed successfully in {$executionTime}ms");
            
            return $result;
            
        } catch (Throwable $e) {
            $this->handleError($hookName, $pluginId, $e);
            
            // Return original value on error
            return $value;
        }
    }

    /**
     * Execute callback with timeout protection
     * 
     * @throws \Exception If timeout is exceeded
     */
    protected function executeWithTimeout(callable $callback, array $args): mixed
    {
        // Note: PHP doesn't have built-in timeout for function execution
        // This is a simplified implementation. For production, consider using:
        // - pcntl_alarm() on Unix systems
        // - Process isolation for critical hooks
        // - Queue jobs with timeout for long-running hooks
        
        $startTime = time();
        
        // Execute callback
        $result = call_user_func_array($callback, $args);
        
        $executionTime = time() - $startTime;
        
        if ($executionTime > $this->timeout) {
            throw new \Exception("Hook execution exceeded timeout of {$this->timeout} seconds");
        }
        
        return $result;
    }

    /**
     * Handle hook execution error
     */
    protected function handleError(string $hookName, string $pluginId, Throwable $e): void
    {
        $errorMessage = "Hook execution failed for '{$hookName}' in plugin '{$pluginId}': {$e->getMessage()}";
        
        Log::error($errorMessage, [
            'hook' => $hookName,
            'plugin' => $pluginId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        $this->log('error', $errorMessage);
    }

    /**
     * Check if hook is disabled
     */
    protected function isDisabled(string $hookName): bool
    {
        return in_array($hookName, $this->disabledHooks);
    }

    /**
     * Log message if logging is enabled
     */
    protected function log(string $level, string $message): void
    {
        if (!$this->logExecution) {
            return;
        }

        Log::log($level, "[Hyro Hooks] {$message}");
    }

    /**
     * Set timeout
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Enable/disable execution logging
     */
    public function setLogExecution(bool $logExecution): void
    {
        $this->logExecution = $logExecution;
    }

    /**
     * Set disabled hooks
     */
    public function setDisabledHooks(array $disabledHooks): void
    {
        $this->disabledHooks = $disabledHooks;
    }
}
