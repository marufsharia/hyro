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
            AuditLogListener::class,
            NotificationListener::class,
        ],
        RoleRevoked::class => [
            AuditLogListener::class,
            NotificationListener::class,
        ],
        PrivilegeGranted::class => [
            AuditLogListener::class,
            NotificationListener::class,
        ],
        PrivilegeRevoked::class => [
            AuditLogListener::class,
            NotificationListener::class,
        ],
        UserSuspended::class => [
            AuditLogListener::class,
            NotificationListener::class,
        ],
        UserUnsuspended::class => [
            AuditLogListener::class,
            NotificationListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     */
    protected $subscribe = [
        // TokenSynchronizationListener is registered conditionally in boot()
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Conditionally register token synchronization listeners
        if (config('hyro.tokens.synchronization.enabled', true)) {
            $this->app['events']->subscribe(TokenSynchronizationListener::class);
        }
    }
}
