<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable the REST API endpoints.
    | When disabled, all API routes will return 404.
    |
    */

    'api' => [
        'enabled' => env('HYRO_API_ENABLED', false),

        'middleware' => [
            'api',
            'auth:sanctum',
        ],

        'prefix' => env('HYRO_API_PREFIX', 'api/hyro'),

        'rate_limit' => [
            'enabled' => env('HYRO_API_RATE_LIMIT', true),
            'max_attempts' => env('HYRO_API_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('HYRO_API_DECAY_MINUTES', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CLI Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable the command-line interface.
    | When disabled, all Hyro commands will be unavailable.
    |
    */

    'cli' => [
        'enabled' => env('HYRO_CLI_ENABLED', true),

        'danger_confirmations' => env('HYRO_CLI_DANGER_CONFIRM', true),
    ],

    /*
|--------------------------------------------------------------------------
| Authentication Configuration
|--------------------------------------------------------------------------
|
| Configure the authentication settings for Hyro.
|
*/
    'auth' => [
        'enabled' => env('HYRO_AUTH_ENABLED', true),
        'routes' => [
            'login' => env('HYRO_AUTH_LOGIN_ROUTE', 'login'),
            'register' => env('HYRO_AUTH_REGISTER_ROUTE', 'register'),
            'logout' => env('HYRO_AUTH_LOGOUT_ROUTE', 'logout'),
            'password_request' => env('HYRO_AUTH_PASSWORD_REQUEST_ROUTE', 'password.request'),
            'password_email' => env('HYRO_AUTH_PASSWORD_EMAIL_ROUTE', 'password.email'),
            'password_reset' => env('HYRO_AUTH_PASSWORD_RESET_ROUTE', 'password.reset'),
            'password_update' => env('HYRO_AUTH_PASSWORD_UPDATE_ROUTE', 'password.update'),
        ],
        'redirects' => [
            'after_login' => env('HYRO_AUTH_REDIRECT_AFTER_LOGIN', '/dashboard'),
            'after_register' => env('HYRO_AUTH_REDIRECT_AFTER_REGISTER', '/dashboard'),
            'after_logout' => env('HYRO_AUTH_REDIRECT_AFTER_LOGOUT', '/'),
            'after_password_reset' => env('HYRO_AUTH_REDIRECT_AFTER_PASSWORD_RESET', '/dashboard'),
        ],
    ],


    /*
  |--------------------------------------------------------------------------
  | Route Configuration
  |--------------------------------------------------------------------------
  |
  | Configure the authentication routes used by Hyro.
  |
  */
    'routes' => [
        'login' => env('HYRO_LOGIN_ROUTE', 'login'),
        'logout' => env('HYRO_LOGOUT_ROUTE', 'logout'),
        'register' => env('HYRO_REGISTER_ROUTE', 'register'),
        'password_request' => env('HYRO_PASSWORD_REQUEST_ROUTE', 'password.request'),
        'password_email' => env('HYRO_PASSWORD_EMAIL_ROUTE', 'password.email'),
        'password_reset' => env('HYRO_PASSWORD_RESET_ROUTE', 'password.reset'),
        'password_update' => env('HYRO_PASSWORD_UPDATE_ROUTE', 'password.update'),
        'verification_notice' => env('HYRO_VERIFICATION_NOTICE_ROUTE', 'verification.notice'),
        'verification_verify' => env('HYRO_VERIFICATION_VERIFY_ROUTE', 'verification.verify'),
        'verification_resend' => env('HYRO_VERIFICATION_RESEND_ROUTE', 'verification.resend'),
    ],
    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable the web-based administration interface.
    | When disabled, all UI routes will return 404.
    |
    */

    'ui' => [
        'enabled' => env('HYRO_UI_ENABLED', true),

        'middleware' => [
            'web',

        ],

        'prefix' => env('HYRO_UI_PREFIX', 'admin/hyro'),

        'layout' => env('HYRO_UI_LAYOUT', 'layouts.app'),
    ],
    /*
       |--------------------------------------------------------------------------
       | Admin UI Configuration
       |--------------------------------------------------------------------------
       |
       | Configuration for the Hyro admin interface.
       |
       */

    'admin' => [
        /*
        |--------------------------------------------------------------------------
        | Admin Route Prefix
        |--------------------------------------------------------------------------
        |
        | The prefix for all admin routes. Change this if you want to customize
        | the admin panel URL.
        |
        */
        'route_prefix' => env('HYRO_ADMIN_PREFIX', 'admin/hyro'),

        /*
        |--------------------------------------------------------------------------
        | Admin Middleware
        |--------------------------------------------------------------------------
        |
        | Middleware applied to all admin routes. You can add additional
        | middleware here if needed.
        |
        */
        'middleware' => ['web', 'auth', 'hyro.privilege:access-hyro-admin'],

        /*
        |--------------------------------------------------------------------------
        | Items Per Page
        |--------------------------------------------------------------------------
        |
        | Number of items to show per page in admin lists.
        |
        */
        'items_per_page' => 20,

        /*
        |--------------------------------------------------------------------------
        | Enable User Role Management
        |--------------------------------------------------------------------------
        |
        | Whether to enable the user role management interface in the admin panel.
        |
        */
        'enable_user_management' => true,
    ],

    /*
  |--------------------------------------------------------------------------
  | Asset Configuration
  |--------------------------------------------------------------------------
  |
  | Configure the asset paths for Hyro.
  |
  */
    'assets' => [
        'css' => env('HYRO_CSS_PATH', '/vendor/hyro/css/hyro.css'),
        'js' => env('HYRO_JS_PATH', '/vendor/hyro/js/hyro.js'),
        'versioned' => env('HYRO_ASSETS_VERSIONED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Development and compilation settings.
    |
    */
    'development' => [
        'compile_assets' => env('HYRO_COMPILE_ASSETS', false),
        'asset_source_path' => env('HYRO_ASSET_SOURCE_PATH', resource_path('vendor/hyro')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database table names, models, and migration settings.
    | All table names are configurable for multi-tenant or legacy systems.
    |
    */

    'database' => [
        'connection' => env('HYRO_DB_CONNECTION', null),

        'migrations' => [
            'autoload' => env('HYRO_MIGRATIONS_AUTOLOAD', true),
            'publish' => env('HYRO_MIGRATIONS_PUBLISH', false),
        ],

        'tables' => [
            'roles' => env('HYRO_TABLE_ROLES', 'hyro_roles'),
            'privileges' => env('HYRO_TABLE_PRIVILEGES', 'hyro_privileges'),
            'role_user' => env('HYRO_TABLE_ROLE_USER', 'hyro_role_user'),
            'privilege_role' => env('HYRO_TABLE_PRIVILEGE_ROLE', 'hyro_privilege_role'),
            'user_suspensions' => env('HYRO_TABLE_USER_SUSPENSIONS', 'hyro_user_suspensions'),
            'audit_logs' => env('HYRO_TABLE_AUDIT_LOGS', 'hyro_audit_logs'),
        ],

        'models' => [
            'user' => env('HYRO_MODEL_USER', \App\Models\User::class),
            'role' => env('HYRO_MODEL_ROLE', \Marufsharia\Hyro\Models\Role::class),
            'privilege' => env('HYRO_MODEL_PRIVILEGE', \Marufsharia\Hyro\Models\Privilege::class),
            'user_suspension' => env('HYRO_MODEL_USER_SUSPENSION', \Marufsharia\Hyro\Models\UserSuspension::class),
            'audit_log' => env('HYRO_MODEL_AUDIT_LOG', \Marufsharia\Hyro\Models\AuditLog::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for performance optimization.
    | Disable caching for debugging or in development.
    |
    */

    'cache' => [
        'enabled' => env('HYRO_CACHE_ENABLED', true),

        'store' => env('HYRO_CACHE_STORE', null),

        'prefix' => env('HYRO_CACHE_PREFIX', 'hyro:'),

        'ttl' => [
            'user_roles' => env('HYRO_CACHE_USER_ROLES_TTL', 3600), // 1 hour
            'role_privileges' => env('HYRO_CACHE_ROLE_PRIVILEGES_TTL', 3600),
            'wildcard_resolution' => env('HYRO_CACHE_WILDCARD_TTL', 300), // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-critical settings. Change with extreme caution.
    | These settings enforce security boundaries and protection mechanisms.
    |
    */

    'security' => [
        'fail_closed' => env('HYRO_SECURITY_FAIL_CLOSED', true),

        'protected_roles' => [
            'super-admin',
            'administrator',
        ],

        'min_admins' => env('HYRO_MIN_ADMINS', 1),

        'suspension' => [
            'auto_revoke_tokens' => env('HYRO_SUSPENSION_REVOKE_TOKENS', true),
            'notify_user' => env('HYRO_SUSPENSION_NOTIFY_USER', true),
            'log_ip' => env('HYRO_SUSPENSION_LOG_IP', true),
        ],

        'password_policy' => [
            'enabled' => env('HYRO_PASSWORD_POLICY_ENABLED', false),
            'min_length' => env('HYRO_PASSWORD_MIN_LENGTH', 12),
            'requires_mixed_case' => env('HYRO_PASSWORD_MIXED_CASE', true),
            'requires_numbers' => env('HYRO_PASSWORD_NUMBERS', true),
            'requires_symbols' => env('HYRO_PASSWORD_SYMBOLS', true),
            'prevent_reuse' => env('HYRO_PASSWORD_PREVENT_REUSE', 5),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditing Configuration
    |--------------------------------------------------------------------------
    |
    | Configure audit logging for security and compliance.
    | Audit logs cannot be modified or deleted through the application.
    |
    */

    'auditing' => [
        'enabled' => env('HYRO_AUDITING_ENABLED', true),

        'events' => [
            'role_assigned',
            'role_revoked',
            'privilege_granted',
            'privilege_revoked',
            'user_suspended',
            'user_unsuspended',
            'token_revoked',
            'security_violation',
        ],

        'retention_days' => env('HYRO_AUDIT_RETENTION_DAYS', 365),

        'log_ip' => env('HYRO_AUDIT_LOG_IP', true),
        'log_user_agent' => env('HYRO_AUDIT_LOG_USER_AGENT', true),
        'log_request_data' => env('HYRO_AUDIT_LOG_REQUEST', false), // Be careful with this
    ],

    /*
    |--------------------------------------------------------------------------
    | Wildcard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure wildcard privilege behavior.
    | Wildcards allow matching multiple privileges with patterns.
    |
    */

    'wildcards' => [
        'enabled' => env('HYRO_WILDCARDS_ENABLED', true),

        'character' => env('HYRO_WILDCARD_CHAR', '*'),

        'patterns' => [
            'user.*' => 'All user operations',
            'admin.*' => 'All admin operations',
            '*.view' => 'View operations for any resource',
            '*.create' => 'Create operations for any resource',
            '*.update' => 'Update operations for any resource',
            '*.delete' => 'Delete operations for any resource',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UUID Configuration
    |--------------------------------------------------------------------------
    |
    | Configure UUID behavior for primary keys.
    | Enabling UUIDs enhances security but may impact performance.
    |
    */

    'uuid' => [
        'enabled' => env('HYRO_UUID_ENABLED', false),

        'version' => env('HYRO_UUID_VERSION', 4), // 4 or 7

        'column' => env('HYRO_UUID_COLUMN', 'uuid'),
    ],

    /*
        |--------------------------------------------------------------------------
        | Authorization Configuration
        |--------------------------------------------------------------------------
        |
        |
        |
        |
        */
    'authorization' => [
        'resolution_order' => env('HYRO_AUTH_RESOLUTION_ORDER', [
            'token_ability',
            'direct_privilege',
            'wildcard_privilege',
            'role_fallback',
            'laravel_gate',
        ]),

        'override_gate' => env('HYRO_OVERRIDE_GATE', false),

        'exclude_abilities' => env('HYRO_EXCLUDE_ABILITIES', [
            'viewNova',
            'viewHorizon',
            'viewTelescope',
        ]),

        'ability_role_map' => [
            // Example: 'edit_settings' => ['admin', 'editor'],
            // Example: 'view_reports' => ['viewer', 'analyst'],
        ],
    ],
    /*
       |--------------------------------------------------------------------------
       | Tokens Configuration
       |--------------------------------------------------------------------------
       |
       |
       |
       |
       */
    'tokens' => [
        'synchronization' => [
            'enabled' => env('HYRO_TOKEN_SYNC_ENABLED', true),
            'auto_revoke_on_suspension' => env('HYRO_TOKEN_REVOKE_ON_SUSPENSION', true),
            'auto_revoke_on_privilege_change' => env('HYRO_TOKEN_REVOKE_ON_PRIVILEGE_CHANGE', true),
            'auto_sync_on_events' => [
                'role_assigned' => true,
                'role_revoked' => true,
                'privilege_granted' => true,
                'privilege_revoked' => true,
                'user_suspended' => true,
                'user_unsuspended' => true,
            ],
            'delay_in_seconds' => env('HYRO_TOKEN_SYNC_DELAY', 0),
            'batch_size' => env('HYRO_TOKEN_SYNC_BATCH_SIZE', 100),
        ],
    ],

    /*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
    'notifications' => [
        'role_assigned' => [
            'enabled' => env('HYRO_NOTIFY_ROLE_ASSIGNED', true),
            'channels' => ['database', 'mail'],
        ],
        'role_revoked' => [
            'enabled' => env('HYRO_NOTIFY_ROLE_REVOKED', true),
            'channels' => ['database', 'mail'],
        ],
        'user_suspended' => [
            'enabled' => env('HYRO_NOTIFY_USER_SUSPENDED', true),
            'channels' => ['database', 'mail'],
        ],
        'user_unsuspended' => [
            'enabled' => env('HYRO_NOTIFY_USER_UNSUSPENDED', true),
            'channels' => ['database', 'mail'],
        ],
        'admin_user_suspended' => [
            'enabled' => env('HYRO_NOTIFY_ADMIN_USER_SUSPENDED', true),
            'channels' => ['database', 'mail'],
            'admin_roles' => ['super-admin', 'admin'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Broadcasting
    |--------------------------------------------------------------------------
    */
    'events' => [
        'broadcast' => [
            'enabled' => env('HYRO_EVENTS_BROADCAST', false),
            'driver' => env('HYRO_EVENTS_BROADCAST_DRIVER', 'pusher'),
            'channel_prefix' => env('HYRO_EVENTS_CHANNEL_PREFIX', 'hyro.'),
        ],
    ],
    /*
   |--------------------------------------------------------------------------
   | Middleware Configuration
   |--------------------------------------------------------------------------
   |
   */
    'middleware' => [
        'global' => [
            'audit' => env('HYRO_MIDDLEWARE_AUDIT_GLOBAL', false),
            'cors' => env('HYRO_MIDDLEWARE_CORS', true),
        ],

        'rate_limiting' => [
            'enabled' => env('HYRO_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('HYRO_RATE_LIMIT_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('HYRO_RATE_LIMIT_DECAY_MINUTES', 1),
            'by_ip' => env('HYRO_RATE_LIMIT_BY_IP', true),
            'by_user' => env('HYRO_RATE_LIMIT_BY_USER', false),
        ],

        'response' => [
            'json_pretty_print' => env('HYRO_JSON_PRETTY_PRINT', config('app.debug')),
            'include_request_id' => env('HYRO_INCLUDE_REQUEST_ID', true),
            'include_timestamp' => env('HYRO_INCLUDE_TIMESTAMP', true),
            'sanitize_errors' => env('HYRO_SANITIZE_ERRORS', !config('app.debug')),
        ],

        'auditing' => [
            'exclude_paths' => [
                'health',
                'status',
                'ping',
                'favicon.ico',
                '*.css',
                '*.js',
                '*.png',
                '*.jpg',
                '*.gif',
            ],

            'exclude_methods' => ['OPTIONS', 'HEAD'],

            'exclude_status_codes' => [404, 301, 302],

            'sensitive_fields' => [
                'password',
                'password_confirmation',
                'token',
                'api_key',
                'secret',
                'private_key',
                'credit_card',
                'ssn',
                'cvv',
            ],
        ],
    ],

];
