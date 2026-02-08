# 🗄️ Hyro Database Management Tools

Complete database management system for backup, restore, optimization, and monitoring.

---

## 📋 Features

- ✅ **Database Backup** - Create compressed and encrypted backups
- ✅ **Database Restore** - Restore from backups with verification
- ✅ **Database Optimization** - Optimize tables and analyze performance
- ✅ **Backup Cleanup** - Automatic cleanup of old backups
- ✅ **Database Status** - Health monitoring and diagnostics
- ✅ **Multi-Driver Support** - MySQL, PostgreSQL, SQLite
- ✅ **Scheduled Backups** - Automatic backup scheduling
- ✅ **Encryption Support** - Secure backup encryption

---

## 🚀 Quick Start

### Create a Backup

```bash
php artisan hyro:db:backup
```

### Restore from Backup

```bash
# List available backups
php artisan hyro:db:restore --list

# Restore specific backup
php artisan hyro:db:restore backups/backup_mysql_2026-02-08_123456_abc123.sql.gz
```

### Optimize Database

```bash
php artisan hyro:db:optimize
```

### Check Database Status

```bash
php artisan hyro:db:status
```

---

## 📦 Configuration

### Environment Variables

```env
# Backup Configuration
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_ENCRYPT=false
HYRO_DB_BACKUP_KEY=your-encryption-key
HYRO_DB_BACKUP_RETENTION=30

# Automatic Backup Schedule
HYRO_DB_BACKUP_SCHEDULE=true
HYRO_DB_BACKUP_FREQUENCY=daily
HYRO_DB_BACKUP_TIME=02:00

# Optimization Configuration
HYRO_DB_OPTIMIZE_ENABLED=true
HYRO_DB_OPTIMIZE_SCHEDULE=true
HYRO_DB_OPTIMIZE_FREQUENCY=weekly

# Monitoring
HYRO_DB_MONITORING_ENABLED=true
HYRO_DB_SLOW_QUERY_THRESHOLD=1000
```

### Config File

```php
// config/hyro.php

'database' => [
    'backup' => [
        'enabled' => true,
        'disk' => 'local',
        'compress' => true,
        'encrypt' => false,
        'encryption_key' => env('HYRO_DB_BACKUP_KEY'),
        'retention_days' => 30,
        
        'schedule' => [
            'enabled' => false,
            'frequency' => 'daily', // daily, weekly, monthly
            'time' => '02:00',
        ],
    ],
    
    'optimization' => [
        'enabled' => true,
        'schedule' => [
            'enabled' => false,
            'frequency' => 'weekly',
        ],
    ],
    
    'monitoring' => [
        'enabled' => true,
        'slow_query_threshold' => 1000, // milliseconds
    ],
],
```

---

## 🔧 Commands

### Backup Command

Create a database backup with various options.

```bash
php artisan hyro:db:backup [options]
```

**Options:**
- `--connection=` - Database connection to backup (default: default connection)
- `--disk=` - Storage disk to use (default: local)
- `--compress` - Compress the backup (default: true)
- `--encrypt` - Encrypt the backup (default: false)
- `--no-compress` - Do not compress the backup
- `--no-encrypt` - Do not encrypt the backup

**Examples:**

```bash
# Basic backup
php artisan hyro:db:backup

# Backup specific connection
php artisan hyro:db:backup --connection=mysql

# Backup with encryption
php artisan hyro:db:backup --encrypt

# Backup to S3
php artisan hyro:db:backup --disk=s3

# Uncompressed backup
php artisan hyro:db:backup --no-compress
```

**Output:**

```
Creating database backup...
✓ Backup created successfully!

┌──────────┬────────────────────────────────────────────────┐
│ Property │ Value                                          │
├──────────┼────────────────────────────────────────────────┤
│ Path     │ backups/backup_mysql_2026-02-08_123456_abc.sql.gz │
│ Size     │ 2.45 MB                                        │
│ Connection│ mysql                                         │
│ Driver   │ mysql                                          │
│ Compressed│ Yes                                           │
│ Encrypted│ No                                             │
│ Created  │ 2026-02-08 12:34:56                           │
└──────────┴────────────────────────────────────────────────┘
```

---

### Restore Command

Restore database from a backup file.

```bash
php artisan hyro:db:restore [backup] [options]
```

**Arguments:**
- `backup` - Path to backup file (optional, will prompt if not provided)

**Options:**
- `--connection=` - Database connection to restore
- `--disk=` - Storage disk to use
- `--list` - List available backups
- `--force` - Force restore without confirmation

**Examples:**

```bash
# List available backups
php artisan hyro:db:restore --list

# Interactive restore (prompts for backup selection)
php artisan hyro:db:restore

# Restore specific backup
php artisan hyro:db:restore backups/backup_mysql_2026-02-08_123456.sql.gz

# Force restore without confirmation
php artisan hyro:db:restore backups/backup.sql.gz --force
```

**Output:**

```
┌───┬────────────────────────────────────────┬─────────┬──────────────┐
│ # │ Name                                   │ Size    │ Modified     │
├───┼────────────────────────────────────────┼─────────┼──────────────┤
│ 1 │ backup_mysql_2026-02-08_123456.sql.gz │ 2.45 MB │ 2 hours ago  │
│ 2 │ backup_mysql_2026-02-07_123456.sql.gz │ 2.40 MB │ 1 day ago    │
└───┴────────────────────────────────────────┴─────────┴──────────────┘

Enter backup number to restore (or 0 to cancel): 1

⚠ WARNING: This will replace your current database!
⚠ Make sure you have a recent backup before proceeding.

Are you sure you want to restore from this backup? (yes/no) [no]: yes

Restoring database...
✓ Database restored successfully!
```

---

### Optimize Command

Optimize database tables and analyze performance.

```bash
php artisan hyro:db:optimize [options]
```

**Options:**
- `--connection=` - Database connection to optimize
- `--tables=` - Specific tables to optimize (can be used multiple times)
- `--analyze` - Show database analysis instead of optimizing

**Examples:**

```bash
# Optimize all tables
php artisan hyro:db:optimize

# Optimize specific tables
php artisan hyro:db:optimize --tables=users --tables=posts

# Show database analysis
php artisan hyro:db:optimize --analyze
```

**Output (Optimize):**

```
Optimizing database...
✓ Database optimized successfully!

┌────────────────┬───────────┐
│ Table          │ Status    │
├────────────────┼───────────┤
│ users          │ optimized │
│ roles          │ optimized │
│ privileges     │ optimized │
│ audit_logs     │ optimized │
└────────────────┴───────────┘
```

**Output (Analyze):**

```
Analyzing database...

Database Information:
┌────────────┬────────┐
│ Property   │ Value  │
├────────────┼────────┤
│ Connection │ mysql  │
│ Driver     │ mysql  │
│ Size       │ 45.2 MB│
└────────────┴────────┘

Table Statistics:
┌────────────────┬────────┬───────────┬────────────┬────────────┐
│ name           │ rows   │ data_size │ index_size │ total_size │
├────────────────┼────────┼───────────┼────────────┼────────────┤
│ audit_logs     │ 125000 │ 25.5 MB   │ 8.2 MB     │ 33.7 MB    │
│ users          │ 5000   │ 2.1 MB    │ 0.8 MB     │ 2.9 MB     │
│ roles          │ 50     │ 16 KB     │ 8 KB       │ 24 KB      │
└────────────────┴────────┴───────────┴────────────┴────────────┘
```

---

### Cleanup Command

Clean up old database backups.

```bash
php artisan hyro:db:cleanup [options]
```

**Options:**
- `--days=` - Keep backups newer than this many days (default: 30)
- `--disk=` - Storage disk to use
- `--dry-run` - Show what would be deleted without deleting

**Examples:**

```bash
# Clean up backups older than 30 days
php artisan hyro:db:cleanup

# Clean up backups older than 7 days
php artisan hyro:db:cleanup --days=7

# Dry run (preview only)
php artisan hyro:db:cleanup --dry-run
```

**Output:**

```
Cleaning up backups older than 30 days...

┌────────────────────────────────────────┬─────────┬──────────────┐
│ Name                                   │ Size    │ Age          │
├────────────────────────────────────────┼─────────┼──────────────┤
│ backup_mysql_2026-01-01_123456.sql.gz │ 2.30 MB │ 38 days ago  │
│ backup_mysql_2025-12-25_123456.sql.gz │ 2.25 MB │ 45 days ago  │
└────────────────────────────────────────┴─────────┴──────────────┘

Delete these 2 backup(s)? (yes/no) [no]: yes

✓ Deleted 2 backup(s)
```

---

### Status Command

Show database status and health information.

```bash
php artisan hyro:db:status [options]
```

**Options:**
- `--connection=` - Database connection to check

**Examples:**

```bash
# Check default connection
php artisan hyro:db:status

# Check specific connection
php artisan hyro:db:status --connection=mysql
```

**Output:**

```
Database Status

┌────────────┬──────────────────┐
│ Property   │ Value            │
├────────────┼──────────────────┤
│ Connection │ mysql            │
│ Driver     │ mysql            │
│ Host       │ localhost        │
│ Database   │ hyro_db          │
│ Port       │ 3306             │
└────────────┴──────────────────┘

✓ Connection: OK
✓ Tables: 25
✓ Version: 8.0.32
✓ Migrations: Up to date
```

---

## 🔄 Scheduled Backups

### Setup Automatic Backups

1. **Enable in Configuration:**

```env
HYRO_DB_BACKUP_SCHEDULE=true
HYRO_DB_BACKUP_FREQUENCY=daily
HYRO_DB_BACKUP_TIME=02:00
```

2. **Add to Laravel Scheduler:**

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Daily backup at 2:00 AM
    $schedule->command('hyro:db:backup')
        ->daily()
        ->at('02:00');
    
    // Weekly optimization on Sunday at 3:00 AM
    $schedule->command('hyro:db:optimize')
        ->weekly()
        ->sundays()
        ->at('03:00');
    
    // Monthly cleanup on the 1st at 4:00 AM
    $schedule->command('hyro:db:cleanup')
        ->monthly()
        ->at('04:00');
}
```

3. **Setup Cron Job:**

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 💾 Storage Configuration

### Local Storage

```php
// config/filesystems.php

'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
    ],
],
```

### S3 Storage

```php
// config/filesystems.php

'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
    ],
],
```

```bash
# Backup to S3
php artisan hyro:db:backup --disk=s3
```

---

## 🔐 Backup Encryption

### Enable Encryption

```env
HYRO_DB_BACKUP_ENCRYPT=true
HYRO_DB_BACKUP_KEY=your-secure-encryption-key
```

### Create Encrypted Backup

```bash
php artisan hyro:db:backup --encrypt
```

**Note:** Encrypted backups are automatically decrypted during restore.

---

## 🎯 Best Practices

### 1. Regular Backups

- Schedule daily backups for production databases
- Keep at least 30 days of backups
- Store backups off-site (S3, etc.)

### 2. Test Restores

- Regularly test backup restoration
- Verify backup integrity
- Document restore procedures

### 3. Optimize Regularly

- Run optimization weekly
- Monitor table sizes
- Check index usage

### 4. Monitor Performance

- Track database size growth
- Monitor slow queries
- Review table statistics

### 5. Secure Backups

- Enable encryption for sensitive data
- Restrict backup access
- Use secure storage locations

---

## 🐛 Troubleshooting

### Backup Fails

**Problem:** mysqldump command not found

**Solution:**
```bash
# Install MySQL client
sudo apt-get install mysql-client

# Or specify full path in command
which mysqldump
```

### Restore Fails

**Problem:** Permission denied

**Solution:**
```bash
# Check file permissions
chmod 644 backup.sql

# Check database user permissions
GRANT ALL PRIVILEGES ON database.* TO 'user'@'localhost';
```

### Optimization Fails

**Problem:** Table is locked

**Solution:**
```bash
# Check for long-running queries
SHOW PROCESSLIST;

# Kill blocking queries
KILL <process_id>;
```

---

## 📊 Monitoring

### Database Size Monitoring

```bash
# Check database size
php artisan hyro:db:optimize --analyze

# Monitor growth over time
php artisan hyro:db:status
```

### Backup Monitoring

```bash
# List all backups
php artisan hyro:db:restore --list

# Check backup age
php artisan hyro:db:cleanup --dry-run
```

---

## 🎉 Summary

The Hyro database management system provides:

✅ Complete backup and restore functionality  
✅ Database optimization tools  
✅ Performance monitoring  
✅ Automatic scheduling  
✅ Multi-driver support  
✅ Encryption support  
✅ Easy-to-use CLI commands  
✅ Production-ready  

**Phase 11: Database Management Tools - 100% COMPLETE** ✅

---

**Need Help?** Check the main Hyro documentation or create an issue on GitHub.
