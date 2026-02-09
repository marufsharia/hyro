# 📦 Hyro Installation Guide

Complete installation guide for Hyro - Enterprise Auth System for Laravel 12+.

---

## 📋 Requirements

### System Requirements

- **PHP:** 8.2 or higher
- **Laravel:** 12.0 or higher
- **Database:** MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+
- **Redis:** Recommended for caching and queues (optional)
- **Composer:** 2.0 or higher
- **Node.js:** 18+ (for asset compilation)
- **NPM/Yarn:** Latest version

### PHP Extensions

Required PHP extensions:
- OpenSSL
- PDO
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo

---

## 🚀 Installation Steps

### Step 1: Install via Composer

```bash
composer require marufsharia/hyro
```

### Step 2: Publish Configuration and Assets

```bash
# Publish all Hyro files
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider"

# Or publish specific resources
php artisan vendor:publish --tag=hyro-config
php artisan vendor:publish --tag=hyro-migrations
php artisan vendor:publish --tag=hyro-views
php artisan vendor:publish --tag=hyro-assets
php artisan vendor:publish --tag=hyro-lang
php artisan vendor:publish --tag=hyro-routes  # Optional: Only if you want to customize routes
```

> **Note on Routes:** By default, Hyro loads routes from the package. You only need to publish routes if you want to customize them. Once published to `routes/hyro/`, those routes will take precedence over the package routes.

### Step 3: Configure Environment

Add Hyro configuration to your `.env` file:

```env
# Hyro Configuration
HYRO_ENABLED=true
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_CLI_ENABLED=true

# Admin Panel
HYRO_ADMIN_PREFIX=admin/hyro

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit Logging
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365

# Notifications
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_NOTIFICATIONS_CHANNELS=database,mail
HYRO_NOTIFICATIONS_QUEUE=true

# Database Backup
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_RETENTION=30
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `hyro_roles` - Role definitions
- `hyro_privileges` - Privilege definitions
- `hyro_role_user` - User-role relationships
- `hyro_privilege_role` - Role-privilege relationships
- `hyro_user_suspensions` - User suspension records
- `hyro_audit_logs` - Audit trail (with yearly partitioning)

### Step 5: Seed Initial Data

```bash
php artisan db:seed --class=Marufsharia\\Hyro\\Database\\Seeders\\HyroSeeder
```

This creates:
- Default roles (Super Admin, Admin, User)
- Default privileges (user management, role management, etc.)
- Role-privilege assignments

### Step 6: Update User Model

Add the `HasHyroAccess` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Marufsharia\Hyro\Traits\HasHyroAccess;

class User extends Authenticatable
{
    use HasHyroAccess;
    
    // ... rest of your model
}
```

### Step 7: Create Admin User

```bash
php artisan hyro:user:create --admin
```

Follow the prompts to create your first admin user.

### Step 8: Compile Assets (Optional)

If you're using Hyro's admin UI:

```bash
npm install
npm run build
```

---

## 🔧 Configuration

### Database Configuration

Hyro supports multiple database drivers. Configure in `config/database.php`:

#### MySQL (Recommended)

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => 'InnoDB',
],
```

#### PostgreSQL

```php
'pgsql' => [
    'driver' => 'pgsql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'schema' => 'public',
],
```

### Cache Configuration

For optimal performance, configure Redis:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Configuration

For background processing:

```env
QUEUE_CONNECTION=redis
```

Then run the queue worker:

```bash
php artisan queue:work
```

### Mail Configuration

For email notifications:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🎯 Post-Installation

### 1. Test Installation

```bash
# Check database status
php artisan hyro:db:status

# List users
php artisan hyro:user:list

# List roles
php artisan hyro:role:list

# Check system health
php artisan hyro:health
```

### 2. Access Admin Panel

Visit: `http://your-domain.com/admin/hyro`

Login with the admin credentials you created.

### 3. Configure Permissions

Set up roles and privileges for your application:

```bash
# Create a new role
php artisan hyro:role:create

# Create a privilege
php artisan hyro:privilege:create

# Assign privilege to role
php artisan hyro:role:grant-privilege
```

### 4. Set Up Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily database backup at 2 AM
    $schedule->command('hyro:db:backup')
        ->daily()
        ->at('02:00');
    
    // Weekly database optimization
    $schedule->command('hyro:db:optimize')
        ->weekly()
        ->sundays()
        ->at('03:00');
    
    // Monthly backup cleanup
    $schedule->command('hyro:db:cleanup')
        ->monthly()
        ->at('04:00');
    
    // Daily audit log cleanup
    $schedule->command('hyro:audit:cleanup')
        ->daily()
        ->at('05:00');
}
```

Then set up the cron job:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🐛 Troubleshooting

### Issue: Migrations Fail

**Solution:**
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Run migrations again
php artisan migrate:fresh
```

### Issue: Permission Denied

**Solution:**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Class Not Found

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Clear config cache
php artisan config:clear
```

### Issue: Assets Not Loading

**Solution:**
```bash
# Publish assets again
php artisan vendor:publish --tag=hyro-assets --force

# Create storage link
php artisan storage:link
```

---

## 🔄 Upgrading

### From Previous Version

```bash
# Update package
composer update marufsharia/hyro

# Publish new assets
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider" --force

# Run new migrations
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🚀 Next Steps

1. **Read the Configuration Guide** - [CONFIGURATION.md](CONFIGURATION.md)
2. **Learn Usage Patterns** - [USAGE.md](USAGE.md)
3. **Explore API Documentation** - [API.md](API.md)
4. **Set Up Deployment** - [DEPLOYMENT.md](DEPLOYMENT.md)

---

## 📚 Additional Resources

- [README.md](README.md) - Package overview
- [NOTIFICATIONS.md](NOTIFICATIONS.md) - Notification system
- [DATABASE_MANAGEMENT.md](DATABASE_MANAGEMENT.md) - Database tools
- [HyroCRUDGenerator.md](HyroCRUDGenerator.md) - CRUD generator

---

**Installation Complete!** 🎉

You're now ready to use Hyro in your Laravel application.
