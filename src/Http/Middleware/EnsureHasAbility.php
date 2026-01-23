<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Illuminate\Http\Request;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;

class EnsureHasAbility extends HyroMiddleware
{
    /**
     * The authorization resolver.
     */
    private AuthorizationResolverContract $authorizationResolver;

    public function __construct(AuthorizationResolverContract $authorizationResolver)
    {
        $this->authorizationResolver = $authorizationResolver;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkAuthorization($user, array $requirements, Request $request): bool
    {
        $this->validateRequirements($requirements, 1, 1);
        $requiredAbility = $requirements[0];

        // Pass additional arguments from request if needed
        $arguments = $this->extractArguments($request, $requiredAbility);

        return $this->authorizationResolver->authorize($user, $requiredAbility, $arguments);
    }

    /**
     * Extract arguments for ability check from request.
     */
    private function extractArguments(Request $request, string $ability): array
    {
        // Extract model instances from route parameters based on ability name
        $arguments = [];

        // Example: For ability 'update' on resource 'users', look for user model in route
        if (preg_match('/^([a-z]+)\.(update|delete|view)$/', $ability, $matches)) {
            $resource = $matches[1];
            $model = $this->resolveModelFromRoute($request, $resource);

            if ($model) {
                $arguments[] = $model;
            }
        }

        return $arguments;
    }

    /**
     * Resolve model instance from route parameters.
     */
    private function resolveModelFromRoute(Request $request, string $resource): ?object
    {
        // Convert resource name to model class (e.g., 'users' -> 'User')
        $modelClass = 'App\\Models\\' . ucfirst(str_singular($resource));

        if (!class_exists($modelClass)) {
            return null;
        }

        // Look for route parameter (e.g., 'user', 'userId', 'id')
        $parameterNames = [
            $resource,
            str_singular($resource),
            str_singular($resource) . 'Id',
            'id'
        ];

        foreach ($parameterNames as $param) {
            $id = $request->route($param);
            if ($id) {
                return $modelClass::find($id);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureReason($user, array $requirements): string
    {
        $requiredAbility = $requirements[0];
        $userAbilities = $this->authorizationResolver->getAbilitiesForUser($user);
        $displayAbilities = implode(', ', array_slice($userAbilities, 0, 10));

        return "User does not have required ability: {$requiredAbility}. User abilities: {$displayAbilities}" .
            (count($userAbilities) > 10 ? '...' : '');
    }

    /**
     * {@inheritdoc}
     */
    protected function getMiddlewareName(): string
    {
        return 'hyro.ability';
    }
}
