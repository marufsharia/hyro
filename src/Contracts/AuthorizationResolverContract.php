<?php

namespace Marufsharia\Hyro\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthorizationResolverContract
{
    /**
     * Authorize a users for a specific ability.
     */
    public function authorize(
        Authenticatable $user,
        string $ability,
        array $arguments = [],
        bool $shouldLog = true
    ): bool;

    /**
     * Get all abilities a users has.
     */
    public function getAbilitiesForUser(Authenticatable $user): array;

    /**
     * Check if users has any of the given abilities.
     */
    public function hasAnyAbility(Authenticatable $user, array $abilities): bool;

    /**
     * Check if users has all of the given abilities.
     */
    public function hasAllAbilities(Authenticatable $user, array $abilities): bool;
}
