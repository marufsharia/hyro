<?php

namespace MarufSharia\Hyro\Drivers;

class SanctumDriver
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function user()
    {
        // Will be implemented in Phase 3
        return null;
    }

    public function role()
    {
        // Will be implemented in Phase 3
        return null;
    }

    public function privilege()
    {
        // Will be implemented in Phase 3
        return null;
    }

    public function audit()
    {
        // Will be implemented in Phase 10
        return null;
    }

    public function install(): bool
    {
        // Will be implemented in Phase 6
        return true;
    }

    public function uninstall(bool $force = false): bool
    {
        // Will be implemented in Phase 6
        return true;
    }
}