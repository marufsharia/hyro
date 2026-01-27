<?php

namespace Marufsharia\Hyro\Blade;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;

class HyroBladeHelper
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
     * Get the current authenticated users (safe for null).
     */
    public function getCurrentUser(): mixed
    {
        $user = Auth::user();

        // Return null users object with safe methods if no users
        if (!$user) {
            return new class {
                public function __call($method, $args)
                {
                    // Safe defaults for null users
                    if (str_starts_with($method, 'has')) {
                        return false;
                    }

                    if ($method === 'isSuspended') {
                        return false;
                    }

                    return null;
                }

                public function __get($property)
                {
                    return null;
                }
            };
        }

        return $user;
    }

    /**
     * Check if users has any of the given abilities.
     */
    public function canAny(array $abilities, $user = null): bool
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user) {
            return false;
        }

        try {
            return $this->authorizationResolver->hasAnyAbility($user, $abilities);
        } catch (\Exception $e) {
            // Fail closed
            if (Config::get('hyro.security.fail_closed', true)) {
                return false;
            }

            // Only throw in development
            if (app()->environment('local', 'testing')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Check if users has all of the given abilities.
     */
    public function canAll(array $abilities, $user = null): bool
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user) {
            return false;
        }

        try {
            return $this->authorizationResolver->hasAllAbilities($user, $abilities);
        } catch (\Exception $e) {
            // Fail closed
            if (Config::get('hyro.security.fail_closed', true)) {
                return false;
            }

            // Only throw in development
            if (app()->environment('local', 'testing')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Get users's roles as formatted string.
     */
    public function getUserRoles($user = null, string $glue = ', '): string
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hyroRoleSlugs')) {
            return '';
        }

        try {
            $roles = $user->hyroRoleSlugs();
            return implode($glue, $roles);
        } catch (\Exception $e) {
            // Return empty string on error
            return '';
        }
    }

    /**
     * Get users's privileges as formatted string.
     */
    public function getUserPrivileges($user = null, string $glue = ', '): string
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hyroPrivilegeSlugs')) {
            return '';
        }

        try {
            $privileges = $user->hyroPrivilegeSlugs();
            return implode($glue, $privileges);
        } catch (\Exception $e) {
            // Return empty string on error
            return '';
        }
    }

    /**
     * Check if users has role (safe method).
     */
    public function hasRole(string $role, $user = null): bool
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }

        try {
            return $user->hasRole($role);
        } catch (\Exception $e) {
            // Fail closed
            if (Config::get('hyro.security.fail_closed', true)) {
                return false;
            }

            return false;
        }
    }

    /**
     * Check if users has privilege (safe method).
     */
    public function hasPrivilege(string $privilege, $user = null): bool
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hasPrivilege')) {
            return false;
        }

        try {
            return $user->hasPrivilege($privilege);
        } catch (\Exception $e) {
            // Fail closed
            if (Config::get('hyro.security.fail_closed', true)) {
                return false;
            }

            return false;
        }
    }

    /**
     * Check if users is suspended (safe method).
     */
    public function isSuspended($user = null): bool
    {
        $user = $user ?: Auth::user();

        // Null users safe (non-authenticated users are not suspended)
        if (!$user || !method_exists($user, 'isSuspended')) {
            return false;
        }

        try {
            return $user->isSuspended();
        } catch (\Exception $e) {
            // Fail closed - assume not suspended
            return false;
        }
    }

    /**
     * Get users's role badges HTML.
     */
    public function roleBadges($user = null, array $options = []): string
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hyroRoleSlugs')) {
            return '';
        }

        try {
            $roles = $user->hyroRoleSlugs();
            $badges = [];

            $defaultClass = $options['class'] ?? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
            $colors = $options['colors'] ?? [
                'admin' => 'bg-red-100 text-red-800',
                'super-admin' => 'bg-purple-100 text-purple-800',
                'editor' => 'bg-blue-100 text-blue-800',
                'users' => 'bg-gray-100 text-gray-800',
            ];

            $defaultColor = $options['default_color'] ?? 'bg-gray-100 text-gray-800';

            foreach ($roles as $role) {
                $colorClass = $colors[$role] ?? $defaultColor;
                $badges[] = sprintf(
                    '<span class="%s %s">%s</span>',
                    $defaultClass,
                    $colorClass,
                    htmlspecialchars($role, ENT_QUOTES, 'UTF-8')
                );
            }

            return implode(' ', $badges);
        } catch (\Exception $e) {
            // Return empty string on error
            return '';
        }
    }

    /**
     * Get users's privilege chips HTML.
     */
    public function privilegeChips($user = null, array $options = []): string
    {
        $user = $user ?: Auth::user();

        // Null users safe
        if (!$user || !method_exists($user, 'hyroPrivilegeSlugs')) {
            return '';
        }

        try {
            $privileges = $user->hyroPrivilegeSlugs();
            $chips = [];

            $defaultClass = $options['class'] ?? 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium';
            $color = $options['color'] ?? 'bg-green-100 text-green-800';
            $limit = $options['limit'] ?? 5;
            $showMore = $options['show_more'] ?? true;

            $privileges = array_slice($privileges, 0, $limit);

            foreach ($privileges as $privilege) {
                $chips[] = sprintf(
                    '<span class="%s %s">%s</span>',
                    $defaultClass,
                    $color,
                    htmlspecialchars($privilege, ENT_QUOTES, 'UTF-8')
                );
            }

            $html = implode(' ', $chips);

            // Add "show more" if needed
            if ($showMore && count($user->hyroPrivilegeSlugs()) > $limit) {
                $remaining = count($user->hyroPrivilegeSlugs()) - $limit;
                $html .= sprintf(
                    ' <span class="text-xs text-gray-500">+%d more</span>',
                    $remaining
                );
            }

            return $html;
        } catch (\Exception $e) {
            // Return empty string on error
            return '';
        }
    }

    /**
     * Render a permission gate UI component.
     */
    public function gate(string $ability, $user = null, array $options = []): string
    {
        $user = $user ?: Auth::user();
        $hasPermission = false;

        // Check permission safely
        if ($user && method_exists($user, 'hasPrivilege')) {
            try {
                $hasPermission = $user->hasPrivilege($ability);
            } catch (\Exception $e) {
                // Fail closed
                $hasPermission = !Config::get('hyro.security.fail_closed', true);
            }
        }

        // Get UI options
        $authorizedContent = $options['authorized'] ?? '✅ Authorized';
        $unauthorizedContent = $options['unauthorized'] ?? '⛔ Unauthorized';
        $authorizedClass = $options['authorized_class'] ?? 'text-green-600';
        $unauthorizedClass = $options['unauthorized_class'] ?? 'text-red-600';

        if ($hasPermission) {
            return sprintf(
                '<span class="%s">%s</span>',
                $authorizedClass,
                $authorizedContent
            );
        }

        return sprintf(
            '<span class="%s">%s</span>',
            $unauthorizedClass,
            $unauthorizedContent
        );
    }

    /**
     * Get authorization status for display.
     */
    public function authorizationStatus(string $ability, $user = null): array
    {
        $user = $user ?: Auth::user();

        $status = [
            'authorized' => false,
            'user_id' => $user?->id,
            'ability' => $ability,
            'message' => '',
            'icon' => '⛔',
            'color' => 'text-red-600',
            'bg_color' => 'bg-red-50',
        ];

        if (!$user) {
            $status['message'] = 'No authenticated users';
            return $status;
        }

        if (!method_exists($user, 'hasPrivilege')) {
            $status['message'] = 'User does not have Hyro trait';
            return $status;
        }

        try {
            $authorized = $user->hasPrivilege($ability);

            if ($authorized) {
                $status['authorized'] = true;
                $status['message'] = 'Authorized';
                $status['icon'] = '✅';
                $status['color'] = 'text-green-600';
                $status['bg_color'] = 'bg-green-50';
            } else {
                $status['message'] = 'Unauthorized';
            }
        } catch (\Exception $e) {
            $status['message'] = 'Error checking authorization';
            if (Config::get('app.debug')) {
                $status['message'] .= ': ' . $e->getMessage();
            }
        }

        return $status;
    }
}
