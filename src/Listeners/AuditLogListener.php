<?php

namespace Marufsharia\Hyro\Listeners;

use Marufsharia\Hyro\Events\RoleAssigned;
use Marufsharia\Hyro\Events\RoleCreated;
use Marufsharia\Hyro\Events\RoleDeleted;
use Marufsharia\Hyro\Events\RoleRevoked;
use Marufsharia\Hyro\Events\PrivilegeGranted;
use Marufsharia\Hyro\Events\PrivilegeRevoked;
use Marufsharia\Hyro\Events\RoleUpdated;
use Marufsharia\Hyro\Events\UserSuspended;
use Marufsharia\Hyro\Events\UserUnsuspended;
use Marufsharia\Hyro\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogListener
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
     * Handle the RoleCreated event.
     */
    public function handleRoleCreated(RoleCreated $event): void
    {
        AuditLog::create([
            'event' => 'role_created',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => Auth::id() ?? $event->creator?->id,
            'new_values' => [
                'creator_id' => $event->creator?->id,
                'creator_type' => $event->creator ? get_class($event->creator) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }


    /**
     * Handle the RoleUpdated event.
     */
    public function handleRoleUpdated(RoleUpdated $event): void
    {
        AuditLog::create([
            'event' => 'role_updated',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => Auth::id() ?? $event->updater?->id,
            'old_values' => $event->original,
            'new_values' => array_merge($event->role->getChanges(), [
                'updater_id' => $event->updater?->id,
                'updater_type' => $event->updater ? get_class($event->updater) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the RoleDeleted event.
     */
    public function handleRoleDeleted(RoleDeleted $event): void
    {
        AuditLog::create([
            'event' => 'role_deleted',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => Auth::id() ?? $event->deleter?->id,
            'old_values' => [
                'role_name' => $event->role->name,
                'role_slug' => $event->role->slug,
            ],
            'new_values' => [
                'deleter_id' => $event->deleter?->id,
                'deleter_type' => $event->deleter ? get_class($event->deleter) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }


    /**
     * Handle the RoleAssigned event.
     */
    public function handleRoleAssigned(RoleAssigned $event): void
    {
        AuditLog::create([
            'event' => 'role_assigned',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => $event->user->id,
            'new_values' => [
                'assigner_id' => $event->assigner?->id,
                'assigner_type' => $event->assigner ? get_class($event->assigner) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the RoleRevoked event.
     */
    public function handleRoleRevoked(RoleRevoked $event): void
    {

        AuditLog::create([
            'event' => 'role_revoked',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => $event->user->id,
            'new_values' => [
                'revoker_id' => $event->revoker?->id,
                'revoker_type' => $event->revoker ? get_class($event->revoker) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the PrivilegeGranted event.
     */
    public function handlePrivilegeGranted(PrivilegeGranted $event): void
    {
        AuditLog::create([
            'event' => 'privilege_granted',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => Auth::id() ?? $event->granter?->id,
            'new_values' => [
                'privilege_id' => $event->privilege->id,
                'privilege_name' => $event->privilege->name,
                'scope' => $event->privilege->scope,
                'granter_id' => $event->granter?->id,
                'granter_type' => $event->granter ? get_class($event->granter) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the PrivilegeRevoked event.
     */
    public function handlePrivilegeRevoked(PrivilegeRevoked $event): void
    {
        AuditLog::create([
            'event' => 'privilege_revoked',
            'auditable_type' => get_class($event->role),
            'auditable_id' => $event->role->id,
            'user_id' => Auth::id() ?? $event->revoker?->id,
            'new_values' => [
                'privilege_id' => $event->privilege->id,
                'privilege_name' => $event->privilege->name,
                'scope' => $event->privilege->scope,
                'revoker_id' => $event->revoker?->id,
                'revoker_type' => $event->revoker ? get_class($event->revoker) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the UserSuspended event.
     */
    public function handleUserSuspended(UserSuspended $event): void
    {
        AuditLog::create([
            'event' => 'user_suspended',
            'user_id' => $event->user->id,
            'new_values' => [
                'suspender_id' => $event->suspender?->id,
                'suspender_type' => $event->suspender ? get_class($event->suspender) : null,
                'duration_days' => $event->metadata['duration_days'],
                'reason' => $event->metadata['reason'],
                'via' => $event->metadata['via'] ?? 'manual',
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the UserUnsuspended event.
     */
    public function handleUserUnsuspended(UserUnsuspended $event): void
    {
        AuditLog::create([
            'event' => 'user_unsuspended',
            'user_id' => $event->user->id,
            'new_values' => [
                'unsuspender_id' => $event->unsuspender?->id,
                'unsuspender_type' => $event->unsuspender ? get_class($event->unsuspender) : null,
                'reason' => $event->metadata['reason'],
                'via' => $event->metadata['via'] ?? 'manual',
                'original_suspension_reason' => $event->metadata['original_suspension_reason'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
