<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;
use Marufsharia\Hyro\Exceptions\AuthorizationException;
use Marufsharia\Hyro\Models\AuditLog;
use Marufsharia\Hyro\Models\Privilege;

class AuthorizationService implements AuthorizationResolverContract
{
    /**
     * Resolution order configuration.
     */
    private array $resolutionOrder;

    /**
     * Whether to fail closed (deny on error).
     */
    private bool $failClosed;

    /**
     * Cache for privilege wildcard expansions.
     */
    private array $wildcardCache = [];

    public function __construct()
    {
        $this->resolutionOrder = Config::get('hyro.authorization.resolution_order', [
            'token_ability',
            'direct_privilege',
            'wildcard_privilege',
            'role_fallback',
            'laravel_gate',
        ]);

        $this->failClosed = Config::get('hyro.security.fail_closed', true);
    }

    /**
     * {@inheritdoc}
     */
    public function authorize(
        Authenticatable $user,
        string $ability,
        array $arguments = [],
        bool $shouldLog = true
    ): bool {
        // Check if user is suspended
        if ($this->isUserSuspended($user)) {
            $this->logAuthorizationAttempt($user, $ability, false, 'user_suspended', $shouldLog);
            return false;
        }

        // Follow resolution order
        foreach ($this->resolutionOrder as $method) {
            try {
                $result = $this->{"resolveWith{$method}"}($user, $ability, $arguments);

                if ($result !== null) {
                    $this->logAuthorizationAttempt($user, $ability, $result, $method, $shouldLog);
                    return $result;
                }
            } catch (\Exception $e) {
                if ($this->failClosed) {
                    $this->logAuthorizationAttempt(
                        $user,
                        $ability,
                        false,
                        "{$method}_error",
                        $shouldLog,
                        $e->getMessage()
                    );
                    return false;
                }
                throw new AuthorizationException("Authorization failed at {$method}: " . $e->getMessage(), 0, $e);
            }
        }

        // If we reach here and fail-closed is enabled, deny access
        if ($this->failClosed) {
            $this->logAuthorizationAttempt($user, $ability, false, 'no_resolution', $shouldLog);
            return false;
        }

        // Fail-open: allow access if no resolution found
        $this->logAuthorizationAttempt($user, $ability, true, 'fail_open', $shouldLog);
        return true;
    }

    /**
     * Resolve using token ability.
     */
    private function resolveWithTokenAbility(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        // This is handled by Sanctum middleware - token abilities are checked before Gate
        // We return null to continue to next resolution method
        return null;
    }

    /**
     * Resolve using direct privilege.
     */
    private function resolveWithDirectPrivilege(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        if (!method_exists($user, 'hasPrivilege')) {
            return null;
        }

        // Check for exact privilege match
        if ($user->hasPrivilege($ability)) {
            return true;
        }

        return null;
    }

    /**
     * Resolve using wildcard privilege.
     */
    private function resolveWithWildcardPrivilege(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        if (!method_exists($user, 'hasPrivilege')) {
            return null;
        }

        // Get all user privileges
        $userPrivileges = $user->hyroPrivilegeSlugs();

        // Check against configured wildcard patterns
        $wildcardPatterns = Config::get('hyro.wildcards.patterns', []);

        foreach (array_keys($wildcardPatterns) as $pattern) {
            if ($this->matchesWildcard($pattern, $ability) && in_array($pattern, $userPrivileges)) {
                return true;
            }
        }

        // Check user's wildcard privileges
        foreach ($userPrivileges as $privilege) {
            if ($this->isWildcard($privilege) && $this->matchesWildcard($privilege, $ability)) {
                return true;
            }
        }

        return null;
    }

    /**
     * Resolve using role fallback.
     */
    private function resolveWithRoleFallback(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        if (!method_exists($user, 'hasRole')) {
            return null;
        }

        // Map abilities to roles (configurable mapping)
        $abilityRoleMap = Config::get('hyro.authorization.ability_role_map', []);

        if (isset($abilityRoleMap[$ability])) {
            $requiredRoles = (array) $abilityRoleMap[$ability];

            foreach ($requiredRoles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }

            return false; // Explicit mapping found but user doesn't have required role
        }

        return null;
    }

    /**
     * Resolve using Laravel Gate.
     */
    private function resolveWithLaravelGate(Authenticatable $user, string $ability, array $arguments): ?bool
    {
        // Defer to existing Gate policies
        try {
            $gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);

            // Check if there's a policy for this ability
            if ($gate->has($ability)) {
                return $gate->check($ability, $arguments);
            }
        } catch (\Exception $e) {
            if ($this->failClosed) {
                return false;
            }
            throw $e;
        }

        return null;
    }

    /**
     * Check if a user is suspended.
     */
    private function isUserSuspended(Authenticatable $user): bool
    {
        if (!method_exists($user, 'isSuspended')) {
            return false;
        }

        return $user->isSuspended();
    }

    /**
     * Check if a string contains wildcard character.
     */
    private function isWildcard(string $pattern): bool
    {
        return str_contains($pattern, '*');
    }

    /**
     * Check if a pattern matches an ability.
     */
    private function matchesWildcard(string $pattern, string $ability): bool
    {
        $cacheKey = 'wildcard_match:' . md5($pattern . ':' . $ability);

        return Cache::remember($cacheKey, 300, function () use ($pattern, $ability) {
            if (!$this->isWildcard($pattern)) {
                return false;
            }

            $regexPattern = '/^' . str_replace(
                    ['*', '.'],
                    ['.*', '\.'],
                    $pattern
                ) . '$/';

            return preg_match($regexPattern, $ability) === 1;
        });
    }

    /**
     * Log authorization attempt.
     */
    private function logAuthorizationAttempt(
        Authenticatable $user,
        string $ability,
        bool $allowed,
        string $resolutionMethod,
        bool $shouldLog,
        ?string $error = null
    ): void {
        if (!$shouldLog || !Config::get('hyro.auditing.enabled', true)) {
            return;
        }

        // Don't log if auditing is disabled for this ability
        $excludedAbilities = Config::get('hyro.auditing.exclude_abilities', []);
        if (in_array($ability, $excludedAbilities) || in_array('*', $excludedAbilities)) {
            return;
        }

        $event = $allowed ? 'authorization_granted' : 'authorization_denied';

        AuditLog::log($event, $user, null, [
            'ability' => $ability,
            'resolution_method' => $resolutionMethod,
            'allowed' => $allowed,
            'error' => $error,
        ], [
            'tags' => ['authorization', $resolutionMethod],
            'description' => $error ? "Authorization {$event} with error: {$error}" : null,
        ]);
    }

    /**
     * Get all abilities a user has.
     */
    public function getAbilitiesForUser(Authenticatable $user): array
    {
        if (!method_exists($user, 'hyroPrivilegeSlugs')) {
            return [];
        }

        $abilities = $user->hyroPrivilegeSlugs();

        // Expand wildcard privileges
        $expandedAbilities = [];
        foreach ($abilities as $ability) {
            if ($this->isWildcard($ability)) {
                $expanded = $this->expandWildcardAbility($ability);
                $expandedAbilities = array_merge($expandedAbilities, $expanded);
            } else {
                $expandedAbilities[] = $ability;
            }
        }

        return array_unique($expandedAbilities);
    }

    /**
     * Expand a wildcard ability to specific abilities.
     */
    private function expandWildcardAbility(string $wildcard): array
    {
        if (!isset($this->wildcardCache[$wildcard])) {
            $cacheKey = 'wildcard_expansion:' . md5($wildcard);
            $cacheTtl = Config::get('hyro.cache.ttl.wildcard_resolution', 300);

            $this->wildcardCache[$wildcard] = Cache::remember($cacheKey, $cacheTtl, function () use ($wildcard) {
                // Get all privileges from database
                return Privilege::where('slug', 'like', str_replace('*', '%', $wildcard))
                    ->orWhere('wildcard_pattern', 'like', str_replace('*', '%', $wildcard))
                    ->pluck('slug')
                    ->toArray();
            });
        }

        return $this->wildcardCache[$wildcard];
    }

    /**
     * Check if user has any of the given abilities.
     */
    public function hasAnyAbility(Authenticatable $user, array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->authorize($user, $ability, [], false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the given abilities.
     */
    public function hasAllAbilities(Authenticatable $user, array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if (!$this->authorize($user, $ability, [], false)) {
                return false;
            }
        }

        return true;
    }
}
