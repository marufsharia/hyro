<?php

namespace Marufsharia\Hyro\Services;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;

class GateRegistrar
{
    /**
     * The authorization resolver.
     */
    private AuthorizationResolverContract $authorizationResolver;

    /**
     * Whether Hyro should override the Gate.
     */
    private bool $shouldOverrideGate;

    /**
     * Abilities that should be excluded from Hyro resolution.
     */
    private array $excludedAbilities;

    public function __construct(AuthorizationResolverContract $authorizationResolver)
    {
        $this->authorizationResolver = $authorizationResolver;
        $this->shouldOverrideGate = Config::get('hyro.authorization.override_gate', false);
        $this->excludedAbilities = Config::get('hyro.authorization.exclude_abilities', []);
    }

    /**
     * Register the Gate callback.
     */
    public function register(): void
    {
        if (!$this->shouldOverrideGate) {
            return;
        }

        app(Gate::class)->before(function ($user, $ability, $arguments = []) {
            return $this->checkAuthorization($user, $ability, $arguments);
        });
    }

    /**
     * Check authorization with Hyro.
     */
    private function checkAuthorization($user, string $ability, array $arguments): ?bool
    {
        // Check if user is authenticated
        if (!$user) {
            return false;
        }

        // Check if ability is excluded
        if ($this->isExcludedAbility($ability)) {
            return null; // Let Laravel handle it
        }

        // Check if user has the Hyro trait
        if (!method_exists($user, 'hasPrivilege')) {
            return null; // User doesn't use Hyro, let Laravel handle
        }

        try {
            return $this->authorizationResolver->authorize($user, $ability, $arguments);
        } catch (\Exception $e) {
            // If fail-closed is enabled, deny on error
            if (Config::get('hyro.security.fail_closed', true)) {
                return false;
            }

            // Re-throw the exception so Laravel can handle it
            throw $e;
        }
    }

    /**
     * Check if ability is excluded from Hyro resolution.
     */
    private function isExcludedAbility(string $ability): bool
    {
        // Exact match
        if (in_array($ability, $this->excludedAbilities)) {
            return true;
        }

        // Wildcard match
        foreach ($this->excludedAbilities as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = '/^' . str_replace(['*', '.'], ['.*', '\.'], $pattern) . '$/';
                if (preg_match($regex, $ability)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Define an ability for Hyro.
     */
    public function define(string $ability, callable|string $callback): void
    {
        app(Gate::class)->define($ability, $callback);
    }

    /**
     * Check if Gate has a specific ability defined.
     */
    public function has(string $ability): bool
    {
        return app(Gate::class)->has($ability);
    }

    /**
     * Get all defined abilities.
     */
    public function abilities(): array
    {
        return app(Gate::class)->abilities();
    }

    /**
     * Register a policy.
     */
    public function policy(string $class, string $policy): void
    {
        app(Gate::class)->policy($class, $policy);
    }
}
