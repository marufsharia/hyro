<?php

namespace Marufsharia\Hyro\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthorizationResolverContract
{
    /**
     * Authorize a user for a specific ability.
     */
    public function authorize(
        Authenticatable $user,
        string $ability,
        array $arguments = [],
        bool $shouldLog = true
    ): bool;

    /**
     * Get all abilities a user has.
     */
    public function getAbilitiesForUser(Authenticatable $user): array;

    /**
     * Check if user has any of the given abilities.
     */
    public function hasAnyAbility(Authenticatable $user, array $abilities): bool;

    /**
     * Check if user has all of the given abilities.
     */
    public function hasAllAbilities(Authenticatable $user, array $abilities): bool;
}
