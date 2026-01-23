<?php

namespace MarufSharia\Hyro;

use Illuminate\Support\Manager;
use MarufSharia\Hyro\Drivers\SanctumDriver;

class HyroManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return config('hyro.driver', 'sanctum');
    }

    public function createSanctumDriver(): SanctumDriver
    {
        return new SanctumDriver($this->app['config']->get('hyro'));
    }

    public function user()
    {
        return $this->driver()->user();
    }

    public function role()
    {
        return $this->driver()->role();
    }

    public function privilege()
    {
        return $this->driver()->privilege();
    }

    public function audit()
    {
        return $this->driver()->audit();
    }

    public function install(): bool
    {
        return $this->driver()->install();
    }

    public function uninstall(bool $force = false): bool
    {
        return $this->driver()->uninstall($force);
    }

    public function status(): array
    {
        return [
            'installed' => \Schema::hasTable(config('hyro.database.tables.roles', 'hyro_roles')),
            'features' => config('hyro.features'),
            'tables' => [
                'users' => config('hyro.database.tables.users'),
                'roles' => config('hyro.database.tables.roles'),
                'privileges' => config('hyro.database.tables.privileges'),
            ],
            'version' => '1.0.0-alpha',
            'driver' => $this->getDefaultDriver(),
        ];
    }
}
