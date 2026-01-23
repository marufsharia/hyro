<?php

namespace Marufsharia\Hyro\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\PrivilegeGranted;
use Marufsharia\Hyro\Events\PrivilegeRevoked;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Marufsharia\Hyro\Listeners\TokenSynchronizationListener;
use Marufsharia\Hyro\Listeners\AuditLogListener;
use Marufsharia\Hyro\Listeners\NotificationListener;


class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        RoleAssigned::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
        RoleRevoked::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
        PrivilegeGranted::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
        PrivilegeRevoked::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
        UserSuspended::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
        UserUnsuspended::class => [
            TokenSynchronizationListener::class,
            AuditLogListener::class,
            NotificationListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     */
    protected $subscribe = [
        TokenSynchronizationListener::class,
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Conditionally register token synchronization listeners
        if (config('hyro.tokens.synchronization.enabled', true)) {
            $this->registerTokenSynchronization();
        }
    }

    /**
     * Register token synchronization event listeners.
     */
    protected function registerTokenSynchronization(): void
    {
        $this->app['events']->listen(
            RoleAssigned::class,
            [TokenSynchronizationListener::class, 'handleRoleAssigned']
        );

        $this->app['events']->listen(
            RoleRevoked::class,
            [TokenSynchronizationListener::class, 'handleRoleRevoked']
        );

        $this->app['events']->listen(
            PrivilegeGranted::class,
            [TokenSynchronizationListener::class, 'handlePrivilegeGranted']
        );

        $this->app['events']->listen(
            PrivilegeRevoked::class,
            [TokenSynchronizationListener::class, 'handlePrivilegeRevoked']
        );

        $this->app['events']->listen(
            UserSuspended::class,
            [TokenSynchronizationListener::class, 'handleUserSuspended']
        );

        $this->app['events']->listen(
            UserUnsuspended::class,
            [TokenSynchronizationListener::class, 'handleUserUnsuspended']
        );
    }
}
