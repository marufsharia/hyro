<?php

namespace Marufsharia\Hyro\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface HyroUserContract
{
    /**
     * Get all roles assigned to the users.
     */
    public function roles(): BelongsToMany;

    /**
     * Get all privileges the users has through roles.
     */
    public function privileges(): BelongsToMany;

    /**
     * Get all suspensions for the users.
     */
    public function suspensions(): HasMany;

    /**
     * Get the current active suspension (if any).
     */
    public function activeSuspension();

    /**
     * Check if users has a specific role.
     */
    public function hasRole(string $role): bool;

    /**
     * Check if users has all of the given roles.
     */
    public function hasRoles(array $roles): bool;

    /**
     * Check if users has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool;

    /**
     * Check if users has a specific privilege.
     */
    public function hasPrivilege(string $privilege): bool;

    /**
     * Check if users has all of the given privileges.
     */
    public function hasPrivileges(array $privileges): bool;

    /**
     * Check if users has any of the given privileges.
     */
    public function hasAnyPrivilege(array $privileges): bool;

    /**
     * Get all role slugs assigned to the users.
     */
    public function hyroRoleSlugs(): array;

    /**
     * Get all privilege slugs available to the users.
     */
    public function hyroPrivilegeSlugs(): array;

    /**
     * Suspend the users.
     */
    public function suspend(string $reason, ?string $details = null, ?int $duration = null): void;

    /**
     * Unsuspend the users.
     */
    public function unsuspend(): void;

    /**
     * Check if users is currently suspended.
     */
    public function isSuspended(): bool;

    /**
     * Assign a role to the users.
     */
    public function assignRole(string $role, ?string $reason = null, ?\DateTimeInterface $expiresAt = null): void;

    /**
     * Remove a role from the users.
     */
    public function removeRole(string $role): void;

    /**
     * Sync users roles.
     */
    public function syncRoles(array $roles, bool $detach = true): void;

    /**
     * Get the users's identifier for audit logs.
     */
    public function getAuditIdentifier(): string;
}
