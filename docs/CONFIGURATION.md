# ⚙️ Hyro Configuration Guide

Complete configuration reference for Hyro package.

---

## 📋 Table of Contents

- [Environment Variables](#environment-variables)
- [Configuration File](#configuration-file)
- [Feature Toggles](#feature-toggles)
- [Security Settings](#security-settings)
- [Performance Tuning](#performance-tuning)
- [Advanced Configuration](#advanced-configuration)

---

## 🔧 Environment Variables

### Core Settings

```env
# Enable/Disable Hyro
HYRO_ENABLED=true

# API Configuration
HYRO_API_ENABLED=true
HYRO_API_PREFIX=api/hyro
HYRO_API_RATE_LIMIT=true
HYRO_API_MAX_ATTEMPTS=60
HYRO_API_DECAY_MINUTES=1

# Admin Panel
HYRO_ADMIN_ENABLED=true
HYRO_ADMIN_PREFIX=admin/hyro
HYRO_ADMIN_LAYOUT=hyro::admin.layouts.app

# CLI Commands
HYRO_CLI_ENABLED=true
HYRO_CLI_DANGER_CONFIRM=true

# Livewire
HYRO_LIVEWIRE_ENABLED=true
HYRO_LIVEWIRE_THEME=default
```

### Security Settings

```env
# Authorization
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin
HYRO_PASSWORD_MIN_LENGTH=8
HYRO_MAX_LOGIN_ATTEMPTS=5

# Token Management
HYRO_TOKEN_SYNC_ENABLED=true
HYRO_TOKEN_EXPIRATION=525600
```

### Cache Configuration

```env
# Cache Settings
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600
HYRO_CACHE_PREFIX=hyro_
HYRO_CACHE_DRIVER=redis
```

### Audit Logging

```env
# Audit Configuration
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365
HYRO_AUDIT_BATCH_TRACKING=true
HYRO_AUDIT_SANITIZE_SENSITIVE=true
```

### Notifications

```env
# Notification Settings
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_NOTIFICATIONS_CHANNELS=database,mail
HYRO_NOTIFICATIONS_QUEUE=true
HYRO_NOTIFICATIONS_QUEUE_CONNECTION=redis
HYRO_NOTIFICATIONS_QUEUE_NAME=notifications

# Per-Event Settings
HYRO_NOTIFY_ROLE_ASSIGNED=true
HYRO_NOTIFY_ROLE_REVOKED=true
HYRO_NOTIFY_USER_SUSPENDED=true
HYRO_NOTIFY_USER_UNSUSPENDED=true
HYRO_NOTIFY_ADMIN_USER_SUSPENDED=true

# Real-time Notifications
HYRO_NOTIFICATIONS_REALTIME=false
BROADCAST_DRIVER=pusher
```

### Database Management

```env
# Backup Configuration
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_ENCRYPT=false
HYRO_DB_BACKUP_KEY=your-encryption-key
HYRO_DB_BACKUP_RETENTION=30

# Backup Schedule
HYRO_DB_BACKUP_SCHEDULE=true
HYRO_DB_BACKUP_FREQUENCY=daily
HYRO_DB_BACKUP_TIME=02:00

# Optimization
HYRO_DB_OPTIMIZE_ENABLED=true
HYRO_DB_OPTIMIZE_SCHEDULE=true
HYRO_DB_OPTIMIZE_FREQUENCY=weekly

# Monitoring
HYRO_DB_MONITORING_ENABLED=true
HYRO_DB_SLOW_QUERY_THRESHOLD=1000
```

---

## 📄 Configuration File

The main configuration file is located at `config/hyro.php`.

### General Settings

```php
return [
    'enabled' => env('HYRO_ENABLED', true),
    
    // ... other settings
];
```

### API Configuration

```php
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
```

### Admin Panel Configuration

```php
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
],
```

### Security Configuration

```php
'security' => [
    'fail_closed' => env('HYRO_FAIL_CLOSED', true),
    'protected_roles' => explode(',', env('HYRO_PROTECTED_ROLES', 'super-admin,admin')),
    'password' => [
        'min_length' => env('HYRO_PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => false,
        'require_numbers' => false,
        'require_special' => false,
    ],
    'login' => [
        'max_attempts' => env('HYRO_MAX_LOGIN_ATTEMPTS', 5),
        'decay_minutes' => 1,
    ],
],
```

### Cache Configuration

```php
'cache' => [
    'enabled' => env('HYRO_CACHE_ENABLED', true),
    'ttl' => env('HYRO_CACHE_TTL', 3600),
    'prefix' => env('HYRO_CACHE_PREFIX', 'hyro_'),
    'driver' => env('HYRO_CACHE_DRIVER', 'redis'),
    'tags' => [
        'roles' => 'hyro_roles',
        'privileges' => 'hyro_privileges',
        'users' => 'hyro_users',
    ],
],
```

---

## 🎛️ Feature Toggles

Enable or disable specific features:

```php
'features' => [
    'api' => env('HYRO_API_ENABLED', false),
    'admin_ui' => env('HYRO_ADMIN_ENABLED', true),
    'cli' => env('HYRO_CLI_ENABLED', true),
    'livewire' => env('HYRO_LIVEWIRE_ENABLED', true),
    'audit_logging' => env('HYRO_AUDIT_ENABLED', true),
    'notifications' => env('HYRO_NOTIFICATIONS_ENABLED', true),
    'database_backup' => env('HYRO_DB_BACKUP_ENABLED', true),
    'plugins' => env('HYRO_PLUGINS_ENABLED', true),
],
```

---

## 🔐 Security Settings

### Fail-Closed Authorization

When enabled, denies access by default if authorization check fails:

```php
'security' => [
    'fail_closed' => true, // Deny by default
],
```

### Protected Roles

Prevent deletion of critical roles:

```php
'security' => [
    'protected_roles' => ['super-admin', 'admin'],
],
```

### Password Policy

```php
'security' => [
    'password' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_numbers' => true,
        'require_special' => true,
    ],
],
```

### Rate Limiting

```php
'api' => [
    'rate_limit' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],
],
```

---

## ⚡ Performance Tuning

### Cache Optimization

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'driver' => 'redis', // Use Redis for better performance
],
```

### Database Optimization

```php
'database' => [
    'optimization' => [
        'enabled' => true,
        'schedule' => [
            'enabled' => true,
            'frequency' => 'weekly',
        ],
    ],
],
```

### Queue Configuration

```php
'notifications' => [
    'queue' => [
        'enabled' => true,
        'connection' => 'redis',
        'queue' => 'notifications',
    ],
],
```

---

## 🔧 Advanced Configuration

### Wildcard Privileges

```php
'privileges' => [
    'wildcard' => [
        'enabled' => true,
        'separator' => '.',
        'patterns' => [
            'users.*' => 'All user operations',
            'posts.*.edit' => 'Edit any post',
        ],
    ],
],
```

### Audit Logging

```php
'audit' => [
    'enabled' => true,
    'retention_days' => 365,
    'batch_tracking' => true,
    'sanitize_sensitive' => true,
    'excluded_events' => [],
    'partitioning' => [
        'enabled' => true,
        'strategy' => 'yearly', // yearly, monthly, daily
    ],
],
```

### Plugin System

```php
'plugins' => [
    'enabled' => true,
    'path' => base_path('hyro-plugins'),
    'namespace' => 'HyroPlugins',
    'auto_discover' => true,
    'marketplace' => [
        'enabled' => true,
        'url' => 'https://marketplace.hyro.dev',
    ],
],
```

### CRUD Generator

```php
'crud' => [
    'enabled' => true,
    'namespace' => 'App\\Livewire\\Admin',
    'view_path' => 'resources/views/livewire/admin',
    'route_prefix' => 'admin',
    'auto_discover' => true,
],
```

---

## 📊 Monitoring Configuration

```php
'monitoring' => [
    'enabled' => true,
    'slow_query_threshold' => 1000, // milliseconds
    'log_queries' => env('APP_DEBUG', false),
    'health_check' => [
        'enabled' => true,
        'endpoint' => '/health',
    ],
],
```

---

## 🌐 Multi-Language Support

```php
'localization' => [
    'enabled' => true,
    'default' => 'en',
    'fallback' => 'en',
    'supported' => ['en', 'es', 'fr', 'de'],
],
```

---

## 🔄 Broadcasting Configuration

For real-time notifications:

```php
'notifications' => [
    'real_time' => [
        'enabled' => true,
        'driver' => 'pusher',
    ],
],
```

Configure broadcasting in `config/broadcasting.php`:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'encrypted' => true,
    ],
],
```

---

## 📝 Configuration Best Practices

### 1. Use Environment Variables

Always use environment variables for sensitive data:

```php
'database' => [
    'backup' => [
        'encryption_key' => env('HYRO_DB_BACKUP_KEY'),
    ],
],
```

### 2. Cache Configuration

In production, cache your configuration:

```bash
php artisan config:cache
```

### 3. Separate Environments

Use different `.env` files for different environments:
- `.env.local` - Local development
- `.env.staging` - Staging server
- `.env.production` - Production server

### 4. Version Control

Never commit `.env` files to version control. Use `.env.example` instead.

### 5. Regular Audits

Regularly review your configuration for security and performance.

---

## 🔍 Configuration Validation

Validate your configuration:

```bash
php artisan hyro:config:validate
```

---

## 📚 Related Documentation

- [INSTALLATION.md](INSTALLATION.md) - Installation guide
- [USAGE.md](USAGE.md) - Usage examples
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment guide
- [SECURITY.md](SECURITY.md) - Security best practices

---

**Configuration Complete!** ⚙️

Your Hyro package is now properly configured.
