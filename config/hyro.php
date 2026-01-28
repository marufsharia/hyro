<?php

return [

    /*
    |--------------------------------------------------------------------------
    | General Toggles
    |--------------------------------------------------------------------------
    */
    'enabled' => env('HYRO_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api' => [
        'enabled' => env('HYRO_API_ENABLED', false),
        'prefix' => env('HYRO_API_PREFIX', 'api/hyro'),
        'middleware' => ['api', 'auth:sanctum'],
        'rate_limit' => [
            'enabled' => env('HYRO_API_RATE_LIMIT', true),
            'max_attempts' => env('HYRO_API_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('HYRO_API_DECAY_MINUTES', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'enabled' => env('HYRO_ADMIN_ENABLED', true),
        'redirects' => [
            'authenticated' => 'dashboard',
        ],
        'route' => [
            'prefix' => env('HYRO_ADMIN_PREFIX', 'admin/hyro'),
            'middleware' => ['web', 'hyro.auth'],
        ],
        'layout' => env('HYRO_ADMIN_LAYOUT', 'hyro::admin.layouts.app'),
        'pagination' => [
            'per_page' => 20,
        ],
        'features' => [
            'user_management' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'enabled' => env('HYRO_AUTH_ENABLED', true),
        'routes' => [
            'login' => 'login',
            'logout' => 'logout',
            'register' => 'register',
            'password' => [
                'request' => 'password.request',
                'email' => 'password.email',
                'reset' => 'password.reset',
                'update' => 'password.update',
            ],
            'verification' => [
                'notice' => 'verification.notice',
                'verify' => 'verification.verify',
                'resend' => 'verification.resend',
            ],
        ],
        'redirects' => [
            'login' => '/dashboard',
            'register' => '/dashboard',
            'logout' => '/',
            'password_reset' => '/dashboard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CLI
    |--------------------------------------------------------------------------
    */
    'cli' => [
        'enabled' => env('HYRO_CLI_ENABLED', true),
        'danger_confirmations' => env('HYRO_CLI_DANGER_CONFIRM', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire CRUD System
    |--------------------------------------------------------------------------
    */
    'livewire' => [
        'enabled' => env('HYRO_LIVEWIRE_ENABLED', true),
        'theme' => env('HYRO_LIVEWIRE_THEME', 'default'),
        'layout' => env('HYRO_LIVEWIRE_LAYOUT', 'hyro::admin.layouts.app'),
        'pagination' => [
            'per_page' => env('HYRO_LIVEWIRE_PER_PAGE', 15),
            'theme' => env('HYRO_LIVEWIRE_PAGINATION_THEME', 'tailwind'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin System
    |--------------------------------------------------------------------------
    */
    'plugins' => [
        'enabled' => env('HYRO_PLUGINS_ENABLED', true),
        'path' => env('HYRO_PLUGINS_PATH', base_path('hyro-plugins')),
        'autoload' => env('HYRO_PLUGINS_AUTOLOAD', true),
        'cache' => [
            'enabled' => env('HYRO_PLUGINS_CACHE_ENABLED', true),
            'ttl' => env('HYRO_PLUGINS_CACHE_TTL', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'css' => '/vendor/hyro/css/hyro.css',
        'js' => '/vendor/hyro/js/hyro.js',
        'versioned' => env('HYRO_ASSETS_VERSIONED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('HYRO_DB_CONNECTION'),
        'migrations' => [
            'autoload' => true,
            'publish' => false,
        ],
        'tables' => [
            'roles' => 'hyro_roles',
            'privileges' => 'hyro_privileges',
            'role_user' => 'hyro_role_user',
            'privilege_role' => 'hyro_privilege_role',
            'user_suspensions' => 'hyro_user_suspensions',
            'audit_logs' => 'hyro_audit_logs',
        ],
        'models' => [
            'users' => \App\Models\User::class,
            'role' => \Marufsharia\Hyro\Models\Role::class,
            'privilege' => \Marufsharia\Hyro\Models\Privilege::class,
            'user_suspension' => \Marufsharia\Hyro\Models\UserSuspension::class,
            'audit_log' => \Marufsharia\Hyro\Models\AuditLog::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization & Privileges
    |--------------------------------------------------------------------------
    */
    'authorization' => [
        'resolution_order' => [
            'token_ability',
            'direct_privilege',
            'wildcard_privilege',
            'role_fallback',
            'laravel_gate',
        ],
        'override_gate' => false,
        'wildcards' => [
            'enabled' => true,
            'character' => '*',
            'patterns' => [
                'users.*',
                'admin.*',
                '*.view',
                '*.create',
                '*.update',
                '*.delete',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tokens
    |--------------------------------------------------------------------------
    */
    'tokens' => [
        'sync' => [
            'enabled' => true,
            'revoke_on_suspension' => true,
            'revoke_on_privilege_change' => true,
            'batch_size' => 100,
            'delay' => 0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */
    'security' => [
        'fail_closed' => true,
        'protected_roles' => [
            'super-admin',
            'administrator',
        ],
        'password_policy' => [
            'enabled' => false,
            'min_length' => 12,
            'mixed_case' => true,
            'numbers' => true,
            'symbols' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditing
    |--------------------------------------------------------------------------
    */
    'auditing' => [
        'enabled' => true,
        'retention_days' => 365,
        'log' => [
            'ip' => true,
            'user_agent' => true,
            'request' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'prefix' => 'hyro:',
        'ttl' => [
            'roles' => 3600,
            'privileges' => 3600,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'enabled' => true,
        'channels' => ['database', 'mail'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Events
    |--------------------------------------------------------------------------
    */
    'events' => [
        'broadcast' => [
            'enabled' => false,
            'driver' => 'pusher',
            'channel_prefix' => 'hyro.',
        ],
    ],

];
