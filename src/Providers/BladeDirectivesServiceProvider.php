<?php

namespace Marufsharia\Hyro\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Marufsharia\Hyro\Contracts\AuthorizationResolverContract;

class BladeDirectivesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // This service provider only registers Blade directives
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only register Blade directives if UI is enabled
        if (!Config::get('hyro.ui.enabled', false)) {
            return;
        }

        $this->registerConditionalDirectives();
        $this->registerInlineDirectives();
        $this->registerCustomComponents();
        $this->registerStackDirectives();
    }

    /**
     * Register conditional Blade directives (if statements).
     */
    private function registerConditionalDirectives(): void
    {
        // @hyrocan - Check if users has ability
        Blade::if('hyrocan', function ($ability, ...$arguments) {
            $user = Auth::user();

            // Null users safe - return false
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasPrivilege')) {
                return false;
            }

            try {
                // Use authorization resolver for consistency
                $resolver = app(AuthorizationResolverContract::class);
                return $resolver->authorize($user, $ability, $arguments, false);
            } catch (\Exception $e) {
                // Fail closed - return false on any error
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                // Only throw in development for debugging
                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasrole - Check if users has specific role
        Blade::if('hasrole', function ($role) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasRole')) {
                return false;
            }

            try {
                return $user->hasRole($role);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasanyrole - Check if users has any of the given roles
        Blade::if('hasanyrole', function ($roles) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasAnyRole')) {
                return false;
            }

            // Ensure roles is an array
            $rolesArray = is_array($roles) ? $roles : func_get_args();

            try {
                return $user->hasAnyRole($rolesArray);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasallroles - Check if users has all of the given roles
        Blade::if('hasallroles', function ($roles) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasRoles')) {
                return false;
            }

            // Ensure roles is an array
            $rolesArray = is_array($roles) ? $roles : func_get_args();

            try {
                return $user->hasRoles($rolesArray);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasprivilege - Check if users has specific privilege
        Blade::if('hasprivilege', function ($privilege) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasPrivilege')) {
                return false;
            }

            try {
                return $user->hasPrivilege($privilege);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasanyprivilege - Check if users has any of the given privileges
        Blade::if('hasanyprivilege', function ($privileges) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasAnyPrivilege')) {
                return false;
            }

            // Ensure privileges is an array
            $privilegesArray = is_array($privileges) ? $privileges : func_get_args();

            try {
                return $user->hasAnyPrivilege($privilegesArray);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hasallprivileges - Check if users has all of the given privileges
        Blade::if('hasallprivileges', function ($privileges) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasPrivileges')) {
                return false;
            }

            // Ensure privileges is an array
            $privilegesArray = is_array($privileges) ? $privileges : func_get_args();

            try {
                return $user->hasPrivileges($privilegesArray);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @hyro - Check multiple conditions (role AND/OR privilege)
        Blade::if('hyro', function ($conditions) {
            $user = Auth::user();

            // Null users safe
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'hasRole') || !method_exists($user, 'hasPrivilege')) {
                return false;
            }

            try {
                return $this->evaluateHyroConditions($user, $conditions);
            } catch (\Exception $e) {
                // Fail closed
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @suspended - Check if users is suspended
        Blade::if('suspended', function () {
            $user = Auth::user();

            // Null users safe (non-authenticated users are not suspended)
            if (!$user) {
                return false;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'isSuspended')) {
                return false;
            }

            try {
                return $user->isSuspended();
            } catch (\Exception $e) {
                // Fail closed - if we can't determine, assume not suspended
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });

        // @notsuspended - Check if users is NOT suspended
        Blade::if('notsuspended', function () {
            $user = Auth::user();

            // Null users safe (non-authenticated users are not suspended)
            if (!$user) {
                return true;
            }

            // Check if users has the Hyro trait
            if (!method_exists($user, 'isSuspended')) {
                return true;
            }

            try {
                return !$user->isSuspended();
            } catch (\Exception $e) {
                // Fail closed - if we can't determine, assume suspended (deny access)
                if (Config::get('hyro.security.fail_closed', true)) {
                    return false;
                }

                if (app()->environment('local', 'testing')) {
                    throw $e;
                }

                return false;
            }
        });
    }

    /**
     * Register inline Blade directives.
     */
    private function registerInlineDirectives(): void
    {
        // @hyro_user - Get current users with Hyro methods
        Blade::directive('hyro_user', function () {
            return '<?php echo app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->getCurrentUser(); ?>';
        });

        // @hyro_roles - Get users's roles
        Blade::directive('hyro_roles', function ($expression) {
            return '<?php echo app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->getUserRoles(' . $expression . '); ?>';
        });

        // @hyro_privileges - Get users's privileges
        Blade::directive('hyro_privileges', function ($expression) {
            return '<?php echo app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->getUserPrivileges(' . $expression . '); ?>';
        });

        // @hyro_can_any - Check multiple abilities inline
        Blade::directive('hyro_can_any', function ($expression) {
            return '<?php if (app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->canAny(' . $expression . ')): ?>';
        });

        Blade::directive('endhyro_can_any', function () {
            return '<?php endif; ?>';
        });

        // @hyro_can_all - Check all abilities inline
        Blade::directive('hyro_can_all', function ($expression) {
            return '<?php if (app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->canAll(' . $expression . ')): ?>';
        });

        Blade::directive('endhyro_can_all', function () {
            return '<?php endif; ?>';
        });
    }

    /**
     * Register custom Blade components.
     */
    private function registerCustomComponents(): void
    {
        // Register the hyro-protected component
        Blade::component('hyro::components.protected', 'hyro-protected');

        // Register the hyro-role component
        Blade::component('hyro::components.role', 'hyro-role');

        // Register the hyro-privilege component
        Blade::component('hyro::components.privilege', 'hyro-privilege');
    }

    /**
     * Register stack directives for CSS/JS.
     */
    private function registerStackDirectives(): void
    {
        // @hyro_styles - Push Hyro CSS to stack
        Blade::directive('hyro_styles', function () {
            return '<?php $__env->startPush(\'hyro_styles\'); ?>';
        });

        Blade::directive('endhyro_styles', function () {
            return '<?php $__env->stopPush(); ?>';
        });

        // @hyro_scripts - Push Hyro JS to stack
        Blade::directive('hyro_scripts', function () {
            return '<?php $__env->startPush(\'hyro_scripts\'); ?>';
        });

        Blade::directive('endhyro_scripts', function () {
            return '<?php $__env->stopPush(); ?>';
        });

        // @hyro_stacks - Include Hyro CSS/JS stacks
        Blade::directive('hyro_stacks', function () {
            return '<?php echo $__env->yieldPushContent(\'hyro_styles\'); echo $__env->yieldPushContent(\'hyro_scripts\'); ?>';
        });
    }

    /**
     * Evaluate complex Hyro conditions.
     */
    private function evaluateHyroConditions($user, $conditions): bool
    {
        // If conditions is a string, treat it as a single privilege check
        if (is_string($conditions)) {
            return $user->hasPrivilege($conditions);
        }

        // If conditions is an array, evaluate complex logic
        if (is_array($conditions)) {
            // Check for 'and' or 'or' operators
            if (isset($conditions['and'])) {
                return $this->evaluateAndConditions($user, $conditions['and']);
            }

            if (isset($conditions['or'])) {
                return $this->evaluateOrConditions($user, $conditions['or']);
            }

            // If no operator specified, treat as 'and'
            return $this->evaluateAndConditions($user, $conditions);
        }

        return false;
    }

    /**
     * Evaluate AND conditions.
     */
    private function evaluateAndConditions($user, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (is_string($condition)) {
                // Single privilege check
                if (!$user->hasPrivilege($condition)) {
                    return false;
                }
            } elseif (is_array($condition)) {
                // Nested condition
                if (!$this->evaluateHyroConditions($user, $condition)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Evaluate OR conditions.
     */
    private function evaluateOrConditions($user, array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (is_string($condition)) {
                // Single privilege check
                if ($user->hasPrivilege($condition)) {
                    return true;
                }
            } elseif (is_array($condition)) {
                // Nested condition
                if ($this->evaluateHyroConditions($user, $condition)) {
                    return true;
                }
            }
        }

        return false;
    }
}
