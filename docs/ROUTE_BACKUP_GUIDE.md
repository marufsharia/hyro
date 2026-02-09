# Route Backup Guide

Complete guide to managing CRUD route backups in Hyro.

## Overview

The Hyro CRUD generator automatically creates backups of your route files before making changes. This ensures you can always restore previous versions if needed.

## Automatic Backups

### When Backups Are Created

Backups are automatically created when:
- Running `php artisan hyro:make-crud` command
- Registering new CRUD routes
- Modifying the route file

### Backup Location

All backups are stored in:
```
storage/app/private/routes/
```

### Backup Filename Format

```
crud_routes_backup_YYYY-MM-DD_HHmmss.php
```

Example: `crud_routes_backup_2026-02-09_035717.php`

### Automatic Cleanup

The system automatically keeps only the **10 most recent backups** and deletes older ones to save disk space.

## Manual Backup Management

### List All Backups

View all available route backups:

```bash
php artisan hyro:route-backup list
```

**Output:**
```
Available Route Backups:

+---+------------------------------------------+---------+---------------------+
| # | Filename                                 | Size    | Date                |
+---+------------------------------------------+---------+---------------------+
| 1 | crud_routes_backup_2026-02-09_035717.php | 1.81 KB | 2026-02-09 03:57:17 |
| 2 | crud_routes_backup_2026-02-09_032145.php | 1.65 KB | 2026-02-09 03:21:45 |
+---+------------------------------------------+---------+---------------------+

Total backups: 2
Location: storage/app/private/routes
```

### Create Manual Backup

Create a backup manually:

```bash
php artisan hyro:route-backup backup
```

**Output:**
```
Creating route backup...
✓ Backup created successfully!
   Path: /path/to/storage/app/private/routes/crud_routes_backup_2026-02-09_040123.php
```

### Restore from Backup

#### Interactive Restore

Restore with interactive selection:

```bash
php artisan hyro:route-backup restore
```

The command will:
1. List all available backups
2. Ask you to select a backup number
3. Confirm before restoring
4. Restore the selected backup

#### Restore Specific File

Restore a specific backup by filename:

```bash
php artisan hyro:route-backup restore --file=crud_routes_backup_2026-02-09_035717.php
```

Or by full path:

```bash
php artisan hyro:route-backup restore --file=/full/path/to/backup.php
```

**Important:** After restoring, always run:
```bash
php artisan route:clear
```

### Clean Old Backups

Remove old backups, keeping only the most recent ones:

```bash
# Keep last 10 backups (default)
php artisan hyro:route-backup clean

# Keep last 5 backups
php artisan hyro:route-backup clean --keep=5

# Keep last 20 backups
php artisan hyro:route-backup clean --keep=20
```

**Output:**
```
This will delete 5 old backup(s), keeping the 10 most recent. Continue? (yes/no) [yes]:
> yes

Cleaning old backups...
✓ Deleted 5 old backup(s)
✓ Kept 10 most recent backup(s)
```

## Command Reference

### hyro:route-backup

**Syntax:**
```bash
php artisan hyro:route-backup {action} [options]
```

**Actions:**
- `list` - List all available backups
- `backup` - Create a new backup
- `restore` - Restore from a backup
- `clean` - Remove old backups

**Options:**
- `--file=` - Backup file to restore (for restore action)
- `--keep=` - Number of backups to keep (for clean action, default: 10)

## Use Cases

### 1. Before Major Changes

Create a manual backup before making significant route changes:

```bash
php artisan hyro:route-backup backup
# Make your changes
# If something goes wrong:
php artisan hyro:route-backup restore
```

### 2. Rollback After Error

If a CRUD generation causes issues:

```bash
# List backups
php artisan hyro:route-backup list

# Restore the previous version
php artisan hyro:route-backup restore --file=crud_routes_backup_2026-02-09_035717.php

# Clear route cache
php artisan route:clear
```

### 3. Disk Space Management

Clean up old backups to free disk space:

```bash
# Keep only last 5 backups
php artisan hyro:route-backup clean --keep=5
```

### 4. Audit Trail

Review backup history to see when routes were modified:

```bash
php artisan hyro:route-backup list
```

## Best Practices

### 1. Regular Cleanup

Schedule regular cleanup to prevent disk space issues:

```bash
# In your scheduler (app/Console/Kernel.php)
$schedule->command('hyro:route-backup clean --keep=10')->weekly();
```

### 2. Before Production Deployment

Always create a backup before deploying:

```bash
php artisan hyro:route-backup backup
```

### 3. Test Restores

Periodically test the restore process in development:

```bash
# Create backup
php artisan hyro:route-backup backup

# Make changes
php artisan hyro:make-crud Test --fields="name:string"

# Restore
php artisan hyro:route-backup restore

# Verify
php artisan route:list
```

### 4. Document Important Backups

Keep notes of important backup files:

```bash
# Create backup before major feature
php artisan hyro:route-backup backup
# Note: crud_routes_backup_2026-02-09_120000.php - Before v2.0 release
```

## Backup File Structure

Backup files are exact copies of your route file:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix(config('hyro.admin.route.prefix', 'admin/hyro'))
    ->middleware(config('hyro.admin.route.middleware', ['web', 'auth']))
    ->name('hyro.admin.')
    ->group(function () {
        // Your CRUD routes
    });
```

## Troubleshooting

### Backup Not Created

**Problem:** No backup created during CRUD generation

**Solution:**
1. Check directory permissions:
   ```bash
   chmod -R 775 storage/app/private
   ```

2. Manually create directory:
   ```bash
   mkdir -p storage/app/private/routes
   ```

### Cannot Restore Backup

**Problem:** Restore fails with "File not found"

**Solution:**
1. Verify backup exists:
   ```bash
   php artisan hyro:route-backup list
   ```

2. Use correct filename or path:
   ```bash
   php artisan hyro:route-backup restore --file=crud_routes_backup_2026-02-09_035717.php
   ```

### Routes Not Updated After Restore

**Problem:** Routes still show old version after restore

**Solution:**
```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Too Many Backups

**Problem:** Hundreds of backup files consuming disk space

**Solution:**
```bash
# Aggressive cleanup - keep only last 3
php artisan hyro:route-backup clean --keep=3
```

## Advanced Usage

### Programmatic Backup

Use the service directly in your code:

```php
use Marufsharia\Hyro\Services\SmartCrudRouteManager;

$routeManager = app(SmartCrudRouteManager::class);

// Create backup
$backupPath = $routeManager->backup();

// List backups
$backups = $routeManager->listBackups();

// Restore backup
$routeManager->restore($backupPath);

// Clean old backups
$deleted = $routeManager->cleanOldBackups(10);
```

### Custom Backup Location

Modify the backup location in `SmartCrudRouteManager`:

```php
// In your service provider
$this->app->extend(SmartCrudRouteManager::class, function ($service) {
    // Customize backup directory
    return $service;
});
```

## Security Considerations

### 1. Backup File Permissions

Ensure backup files are not publicly accessible:

```bash
# Correct permissions
chmod 640 storage/app/private/routes/*.php
```

### 2. Sensitive Information

Backup files may contain:
- Route definitions
- Middleware configurations
- Component class names

Keep them secure and don't commit to version control.

### 3. .gitignore

Add to `.gitignore`:

```gitignore
storage/app/private/routes/*.php
```

## Monitoring

### Check Backup Health

Create a health check command:

```bash
php artisan hyro:route-backup list
```

Monitor:
- Number of backups
- Disk space usage
- Last backup date

### Alerts

Set up alerts for:
- Failed backup creation
- Disk space warnings
- Missing backups

## Summary

The route backup system provides:

✅ **Automatic backups** on every CRUD generation
✅ **Manual backup** creation and restoration
✅ **Automatic cleanup** to manage disk space
✅ **Easy recovery** from mistakes
✅ **Audit trail** of route changes
✅ **Secure storage** in private directory

Always remember to clear route cache after restoring:
```bash
php artisan route:clear
```

---

**Location**: `storage/app/private/routes/`
**Default Retention**: 10 most recent backups
**Automatic**: Yes, on every CRUD generation
