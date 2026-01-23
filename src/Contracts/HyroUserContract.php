<?php

namespace Marufsharia\Hyro\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HyroUserContract
{
    /**
     * Get all roles assigned to the user.
     */
    public function roles(): BelongsToMany;

    /**
     * Get all privileges the user has through roles.
     */
    public function privileges(): BelongsToMany;

    /**
     * Get all suspensions for the user.
     */
    public function suspensions(): HasMany;

    /**
     * Get the current active suspension (if any).
     */
    public function activeSuspension();

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool;

    /**
     * Check if user has all of the given roles.
     */
    public function hasRoles(array $roles): bool;

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool;

    /**
     * Check if user has a specific privilege.
     */
    public function hasPrivilege(string $privilege): bool;

    /**
     * Check if user has all of the given privileges.
     */
    public function hasPrivileges(array $privileges): bool;

    /**
     * Check if user has any of the given privileges.
     */
    public function hasAnyPrivilege(array $privileges): bool;

    /**
     * Get all role slugs assigned to the user.
     */
    public function hyroRoleSlugs(): array;

    /**
     * Get all privilege slugs available to the user.
     */
    public function hyroPrivilegeSlugs(): array;

    /**
     * Suspend the user.
     */
    public function suspend(string $reason, ?string $details = null, ?int $duration = null): void;

    /**
     * Unsuspend the user.
     */
    public function unsuspend(): void;

    /**
     * Check if user is currently suspended.
     */
    public function isSuspended(): bool;

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $role, ?string $reason = null, ?\DateTimeInterface $expiresAt = null): void;

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $role): void;

    /**
     * Sync user roles.
     */
    public function syncRoles(array $roles, bool $detach = true): void;

    /**
     * Get the user's identifier for audit logs.
     */
    public function getAuditIdentifier(): string;
}
