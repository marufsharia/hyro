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
     * Handle the RoleCreated event.
     */
    public function handleRoleCreated(RoleCreated $event): void
    {
        AuditLog::create([
            'action' => 'role_created',
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
                'creator_id' => $event->creator?->id,
                'creator_type' => $event->creator ? get_class($event->creator) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->creator?->id,
        ]);
    }


    /**
     * Handle the RoleUpdated event.
     */
    public function handleRoleUpdated(RoleUpdated $event): void
    {
        AuditLog::create([
            'action' => 'role_updated',
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
                'updater_id' => $event->updater?->id,
                'updater_type' => $event->updater ? get_class($event->updater) : null,
                'original' => $event->original,
                'changes' => $event->role->getChanges(),
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->updater?->id,
        ]);
    }

    /**
     * Handle the RoleDeleted event.
     */
    public function handleRoleDeleted(RoleDeleted $event): void
    {
        AuditLog::create([
            'action' => 'role_deleted',
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
                'deleter_id' => $event->deleter?->id,
                'deleter_type' => $event->deleter ? get_class($event->deleter) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
                'role_name' => $event->role->name,
                'role_slug' => $event->role->slug,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->deleter?->id,
        ]);
    }


    /**
     * Handle the RoleAssigned event.
     */
    public function handleRoleAssigned(RoleAssigned $event): void
    {
        AuditLog::create([
            'action' => 'role_assigned',
            'user_id' => $event->user->id,
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
                'assigner_id' => $event->assigner?->id,
                'assigner_type' => $event->assigner ? get_class($event->assigner) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->assigner?->id,
        ]);
    }

    /**
     * Handle the RoleRevoked event.
     */
    public function handleRoleRevoked(RoleRevoked $event): void
    {

        AuditLog::create([
            'action' => 'role_revoked',
            'user_id' => $event->user->id,
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
                'revoker_id' => $event->revoker?->id,
                'revoker_type' => $event->revoker ? get_class($event->revoker) : null,
                'via' => $event->metadata['via'] ?? 'manual',
                'reason' => $event->metadata['reason'] ?? null,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->revoker?->id,
        ]);
    }

    /**
     * Handle the PrivilegeGranted event.
     */
    public function handlePrivilegeGranted(PrivilegeGranted $event): void
    {
        AuditLog::create([
            'action' => 'privilege_granted',
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
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
            'performed_by' => Auth::id() ?? $event->granter?->id,
        ]);
    }

    /**
     * Handle the PrivilegeRevoked event.
     */
    public function handlePrivilegeRevoked(PrivilegeRevoked $event): void
    {
        AuditLog::create([
            'action' => 'privilege_revoked',
            'target_type' => get_class($event->role),
            'target_id' => $event->role->id,
            'details' => [
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
            'performed_by' => Auth::id() ?? $event->revoker?->id,
        ]);
    }

    /**
     * Handle the UserSuspended event.
     */
    public function handleUserSuspended(UserSuspended $event): void
    {
        AuditLog::create([
            'action' => 'user_suspended',
            'user_id' => $event->user->id,
            'details' => [
                'suspender_id' => $event->suspender?->id,
                'suspender_type' => $event->suspender ? get_class($event->suspender) : null,
                'duration_days' => $event->metadata['duration_days'],
                'reason' => $event->metadata['reason'],
                'via' => $event->metadata['via'] ?? 'manual',
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->suspender?->id,
        ]);
    }

    /**
     * Handle the UserUnsuspended event.
     */
    public function handleUserUnsuspended(UserUnsuspended $event): void
    {
        AuditLog::create([
            'action' => 'user_unsuspended',
            'user_id' => $event->user->id,
            'details' => [
                'unsuspender_id' => $event->unsuspender?->id,
                'unsuspender_type' => $event->unsuspender ? get_class($event->unsuspender) : null,
                'reason' => $event->metadata['reason'],
                'via' => $event->metadata['via'] ?? 'manual',
                'original_suspension_reason' => $event->metadata['original_suspension_reason'],
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => Auth::id() ?? $event->unsuspender?->id,
        ]);
    }

    /**
     * Subscribe to events.
     */
    public function subscribe($events): array
    {
        return [
            RoleAssigned::class => 'handleRoleAssigned',
            RoleRevoked::class => 'handleRoleRevoked',
            PrivilegeGranted::class => 'handlePrivilegeGranted',
            PrivilegeRevoked::class => 'handlePrivilegeRevoked',
            UserSuspended::class => 'handleUserSuspended',
            UserUnsuspended::class => 'handleUserUnsuspended',
        ];
    }
}
