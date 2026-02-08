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
     * Handle the event.
     */
    public function handle($event): void
    {
        $method = 'handle' . class_basename($event);
        
        if (method_exists($this, $method)) {
            $this->$method($event);
        }
    }

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
     * Notify administrators about users suspension.
     */
    protected function notifyAdminsAboutSuspension(UserSuspended $event): void
    {
        $userModel = config('hyro.models.users', \App\Models\User::class);
        
        // Get admin users
        $admins = $userModel::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['super-admin', 'admin', 'administrator']);
        })->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new AdminUserSuspendedNotification($event));
        }
    }
}
