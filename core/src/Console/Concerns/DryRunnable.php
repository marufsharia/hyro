<?php

namespace Marufsharia\Hyro\Console\Concerns;

trait DryRunnable
{
    /**
     * Add the dry-run option to command signature
     */
    protected function addDryRunOption(): void
    {
        $this->getDefinition()->addOption(
            new \Symfony\Component\Console\Input\InputOption(
                'dry-run',
                null,
                \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Simulate the operation without making changes'
            )
        );
    }

    /**
     * Execute callback only if not in dry-run mode
     */
    protected function executeUnlessDryRun(callable $callback, string $dryRunMessage = null)
    {
        if ($this->isDryRun()) {
            if ($dryRunMessage) {
                $this->infoMessage("[DRY RUN] {$dryRunMessage}");
            }
            return null;
        }

        return $callback();
    }

    /**
     * Check if dry-run option is set
     */
    protected function isDryRun(): bool
    {
        return $this->option('dry-run') === true;
    }
}
