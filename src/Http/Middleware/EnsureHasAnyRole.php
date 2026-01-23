<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Illuminate\Http\Request;

class EnsureHasAnyRole extends HyroMiddleware
{
    /**
     * {@inheritdoc}
     */
    protected function checkAuthorization($user, array $requirements, Request $request): bool
    {
        $this->validateRequirements($requirements, 1);

        if (!method_exists($user, 'hasAnyRole')) {
            return false;
        }

        return $user->hasAnyRole($requirements);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureReason($user, array $requirements): string
    {
        $requiredRoles = implode(', ', $requirements);
        $userRoles = method_exists($user, 'hyroRoleSlugs')
            ? implode(', ', $user->hyroRoleSlugs())
            : 'none';

        return "User does not have any of the required roles: {$requiredRoles}. User roles: {$userRoles}";
    }

    /**
     * {@inheritdoc}
     */
    protected function getMiddlewareName(): string
    {
        return 'hyro.roles';
    }
}
