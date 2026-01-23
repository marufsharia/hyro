<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Illuminate\Http\Request;
use Marufsharia\Hyro\Exceptions\InvalidRoleException;

class EnsureHasRole extends HyroMiddleware
{
    /**
     * {@inheritdoc}
     */
    protected function checkAuthorization($user, array $requirements, Request $request): bool
    {
        $this->validateRequirements($requirements, 1, 1);
        $requiredRole = $requirements[0];

        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole($requiredRole);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureReason($user, array $requirements): string
    {
        $requiredRole = $requirements[0];
        $userRoles = method_exists($user, 'hyroRoleSlugs')
            ? implode(', ', $user->hyroRoleSlugs())
            : 'none';

        return "User does not have required role: {$requiredRole}. User roles: {$userRoles}";
    }

    /**
     * {@inheritdoc}
     */
    protected function getMiddlewareName(): string
    {
        return 'hyro.role';
    }

    /**
     * Handle multiple role requirements.
     */
    public static function multiple(array $roles): string
    {
        if (empty($roles)) {
            throw new InvalidRoleException('At least one role must be specified');
        }

        foreach ($roles as $role) {
            if (!is_string($role) || empty($role)) {
                throw new InvalidRoleException('All roles must be non-empty strings');
            }
        }

        return 'hyro.role:' . implode(',', $roles);
    }
}
