<?php

namespace Marufsharia\Hyro\Http\Middleware;

use Illuminate\Http\Request;
use Marufsharia\Hyro\Exceptions\InvalidPrivilegeException;

class EnsureHasPrivilege extends HyroMiddleware
{
    /**
     * {@inheritdoc}
     */
    protected function checkAuthorization($user, array $requirements, Request $request): bool
    {
        $this->validateRequirements($requirements, 1, 1);
        $requiredPrivilege = $requirements[0];

        if (!method_exists($user, 'hasPrivilege')) {
            return false;
        }

        return $user->hasPrivilege($requiredPrivilege);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFailureReason($user, array $requirements): string
    {
        $requiredPrivilege = $requirements[0];
        $userPrivileges = method_exists($user, 'hyroPrivilegeSlugs')
            ? implode(', ', array_slice($user->hyroPrivilegeSlugs(), 0, 10)) // Show first 10
            : 'none';

        return "User does not have required privilege: {$requiredPrivilege}. User privileges: {$userPrivileges}" .
            (count($user->hyroPrivilegeSlugs() ?? []) > 10 ? '...' : '');
    }

    /**
     * {@inheritdoc}
     */
    protected function getMiddlewareName(): string
    {
        return 'hyro.privilege';
    }

    /**
     * Handle multiple privilege requirements.
     */
    public static function multiple(array $privileges): string
    {
        if (empty($privileges)) {
            throw new InvalidPrivilegeException('At least one privilege must be specified');
        }

        foreach ($privileges as $privilege) {
            if (!is_string($privilege) || empty($privilege)) {
                throw new InvalidPrivilegeException('All privileges must be non-empty strings');
            }

            // Validate privilege format (optional: add regex validation)
            if (!preg_match('/^[a-z][a-z0-9\.\*]*$/i', $privilege)) {
                throw new InvalidPrivilegeException(
                    "Invalid privilege format: {$privilege}. Use dot notation (e.g., 'users.create')"
                );
            }
        }

        return 'hyro.privilege:' . implode(',', $privileges);
    }
}
