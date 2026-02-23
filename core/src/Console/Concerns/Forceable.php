<?php

namespace Marufsharia\Hyro\Console\Concerns;

trait Forceable
{
    /**
     * Add the force option to command signature
     */
    protected function addForceOption(): void
    {
        $this->getDefinition()->addOption(
            new \Symfony\Component\Console\Input\InputOption(
                'force',
                'f',
                \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Force the operation to run without confirmation'
            )
        );
    }

    /**
     * Check if force option is set
     */
    protected function isForced(): bool
    {
        return $this->option('force') === true;
    }
}
