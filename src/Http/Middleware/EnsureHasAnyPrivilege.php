<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Illuminate\Http\Request;

class EnsureHasAnyPrivilege extends HyroMiddleware
{
    /**
     * {@inheritdoc}
     */
    protected function checkAuthorization($user, array $requirements, Request $request): bool
    {
        $this->validateRequirements($requirements, 1);

        if (!method_exists($user, 'hasAnyPrivilege')) {
            return false;
        }

        return $user->hasAnyPrivilege($requirements);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureReason($user, array $requirements): string
    {
        $requiredPrivileges = implode(', ', $requirements);
        $userPrivileges = method_exists($user, 'hyroPrivilegeSlugs')
            ? implode(', ', array_slice($user->hyroPrivilegeSlugs(), 0, 10))
            : 'none';

        return "User does not have any of the required privileges: {$requiredPrivileges}. User privileges: {$userPrivileges}" .
            (count($user->hyroPrivilegeSlugs() ?? []) > 10 ? '...' : '');
    }

    /**
     * {@inheritdoc}
     */
    protected function getMiddlewareName(): string
    {
        return 'hyro.privileges';
    }
}
