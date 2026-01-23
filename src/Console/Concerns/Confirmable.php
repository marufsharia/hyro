<?php

namespace Marufsharia\Hyro\Console\Concerns;

trait Confirmable
{
    /**
     * Confirm before proceeding with the operation.
     */
    protected function confirmOperation(string $operation, array $details = []): bool
    {
        $this->info("Operation: {$operation}");

        if (!empty($details)) {
            $this->table(['Detail', 'Value'], $details);
        }

        if ($this->option('force')) {
            $this->warn('⚠️  Force flag detected - skipping confirmation');
            return true;
        }

        if ($this->option('dry-run')) {
            $this->info('🔍 Dry run - no confirmation needed');
            return true;
        }

        return $this->confirm('Do you wish to continue?', false);
    }

    /**
     * Confirm destructive operation with impact analysis.
     */
    protected function confirmDestructiveOperation(string $operation, array $impact): bool
    {
        $this->error("⚠️  DESTRUCTIVE OPERATION: {$operation}");
        $this->error('This operation cannot be undone!');

        if (!empty($impact)) {
            $this->table(['Impact Area', 'Expected Effect'], $impact);
        }

        if ($this->option('force')) {
            $this->warn('Force flag detected - proceeding without confirmation');
            return true;
        }

        // Require double confirmation for destructive operations
        if (!$this->confirm('Are you ABSOLUTELY sure?', false)) {
            return false;
        }

        $confirmText = $this->ask('Type "CONFIRM" to proceed');
        return strtoupper($confirmText) === 'CONFIRM';
    }
}
