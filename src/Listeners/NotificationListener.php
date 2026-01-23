<?php

namespace Marufsharia\Hyro\Listeners;

use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Illuminate\Support\Facades\Notification;
use Marufsharia\Hyro\Notifications\RoleAssignedNotification;
use Marufsharia\Hyro\Notifications\RoleRevokedNotification;
use Marufsharia\Hyro\Notifications\UserSuspendedNotification;
use Marufsharia\Hyro\Notifications\UserUnsuspendedNotification;

class NotificationListener
{
    /**
     * Handle the RoleAssigned event.
     */
    public function handleRoleAssigned(RoleAssigned $event): void
    {
        if (config('hyro.notifications.role_assigned.enabled', true)) {
            $event->user->notify(new RoleAssignedNotification($event));
        }
    }

    /**
     * Handle the RoleRevoked event.
     */
    public function handleRoleRevoked(RoleRevoked $event): void
    {
        if (config('hyro.notifications.role_revoked.enabled', true)) {
            $event->user->notify(new RoleRevokedNotification($event));
        }
    }

    /**
     * Handle the UserSuspended event.
     */
    public function handleUserSuspended(UserSuspended $event): void
    {
        if (config('hyro.notifications.user_suspended.enabled', true)) {
            $event->user->notify(new UserSuspendedNotification($event));
        }

        // Notify admins about suspension
        if (config('hyro.notifications.admin_user_suspended.enabled', true)) {
            $this->notifyAdminsAboutSuspension($event);
        }
    }

    /**
     * Handle the UserUnsuspended event.
     */
    public function handleUserUnsuspended(UserUnsuspended $event): void
    {
        if (config('hyro.notifications.user_unsuspended.enabled', true)) {
            $event->user->notify(new UserUnsuspendedNotification($event));
        }
    }

    /**
     * Notify administrators about user suspension.
     */
    protected function notifyAdminsAboutSuspension(UserSuspended $event): void
    {
        // Get admin users (implementation depends on your admin role definition)
        // Example: $admins = User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->get();
        // Notification::send($admins, new AdminUserSuspendedNotification($event));
    }

    /**
     * Subscribe to events.
     */
    public function subscribe($events): array
    {
        return [
            RoleAssigned::class => 'handleRoleAssigned',
            RoleRevoked::class => 'handleRoleRevoked',
            UserSuspended::class => 'handleUserSuspended',
            UserUnsuspended::class => 'handleUserUnsuspended',
        ];
    }
}
