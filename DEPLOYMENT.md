# 🚀 Hyro Deployment Guide

Production deployment guide for Hyro package.

---

## 📋 Pre-Deployment Checklist

### ✅ Code Preparation

- [ ] All tests passing
- [ ] Code reviewed and approved
- [ ] Dependencies updated
- [ ] Configuration validated
- [ ] Database migrations tested
- [ ] Backup procedures tested
- [ ] Documentation updated

### ✅ Environment Setup

- [ ] Production server configured
- [ ] Database server ready
- [ ] Redis server configured
- [ ] Queue workers configured
- [ ] Cron jobs set up
- [ ] SSL certificate installed
- [ ] Firewall rules configured

### ✅ Security Checklist

- [ ] Environment variables secured
- [ ] Debug mode disabled
- [ ] Error logging configured
- [ ] Rate limiting enabled
- [ ] CSRF protection enabled
- [ ] SQL injection prevention verified
- [ ] XSS protection enabled

---

## 🔧 Server Requirements

### Minimum Requirements

- **CPU:** 2 cores
- **RAM:** 4GB
- **Storage:** 20GB SSD
- **PHP:** 8.2+
- **MySQL:** 8.0+ or PostgreSQL 13+
- **Redis:** 6.0+
- **Web Server:** Nginx or Apache

### Recommended Requirements

- **CPU:** 4+ cores
- **RAM:** 8GB+
- **Storage:** 50GB+ SSD
- **Load Balancer:** For high traffic
- **CDN:** For static assets

---

## 📦 Deployment Steps

### Step 1: Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-redis \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server
```

### Step 2: Clone and Configure

```bash
# Clone repository
cd /var/www
git clone https://github.com/your-repo/your-app.git
cd your-app

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Copy environment file
cp .env.example .env
php artisan key:generate
```

### Step 3: Configure Environment

Edit `.env` file:

```env
APP_NAME="Your App"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Hyro Configuration
HYRO_ENABLED=true
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_CACHE_ENABLED=true
HYRO_AUDIT_ENABLED=true
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_DB_BACKUP_ENABLED=true

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin

# Backup
HYRO_DB_BACKUP_DISK=s3
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_ENCRYPT=true
HYRO_DB_BACKUP_RETENTION=90
```

### Step 4: Run Migrations

```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=Marufsharia\\Hyro\\Database\\Seeders\\HyroSeeder

# Create admin user
php artisan hyro:user:create --admin
```

### Step 5: Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### Step 6: Configure Web Server

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com;
    root /var/www/your-app/public;

    # SSL Configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Step 7: Set Up Queue Workers

Create supervisor configuration:

```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/your-app/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Step 8: Configure Cron Jobs

```bash
sudo crontab -e -u www-data
```

Add:

```cron
* * * * * cd /var/www/your-app && php artisan schedule:run >> /dev/null 2>&1
```

### Step 9: Set Up Monitoring

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs

# Set up log rotation
sudo nano /etc/logrotate.d/laravel
```

```
/var/www/your-app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

---

## 🔒 Security Hardening

### 1. Disable Debug Mode

```env
APP_DEBUG=false
```

### 2. Set Secure Permissions

```bash
# Set directory permissions
find /var/www/your-app -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/your-app -type f -exec chmod 644 {} \;

# Set storage permissions
chmod -R 775 /var/www/your-app/storage
chmod -R 775 /var/www/your-app/bootstrap/cache
```

### 3. Configure Firewall

```bash
# Allow SSH
sudo ufw allow 22/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
```

### 4. Enable Rate Limiting

```env
HYRO_API_RATE_LIMIT=true
HYRO_API_MAX_ATTEMPTS=60
```

### 5. Secure Database

```sql
-- Create dedicated database user
CREATE USER 'hyro_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON hyro_db.* TO 'hyro_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📊 Performance Optimization

### 1. Enable OPcache

```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. Configure Redis

```bash
sudo nano /etc/redis/redis.conf
```

```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
```

### 3. Enable HTTP/2

Already configured in Nginx example above.

### 4. Use CDN

Configure CDN for static assets:

```env
ASSET_URL=https://cdn.your-domain.com
```

---

## 💾 Backup Strategy

### Automated Backups

```bash
# Daily database backup
0 2 * * * cd /var/www/your-app && php artisan hyro:db:backup

# Weekly full backup
0 3 * * 0 cd /var/www/your-app && tar -czf /backups/full-$(date +\%Y\%m\%d).tar.gz .

# Monthly cleanup
0 4 1 * * cd /var/www/your-app && php artisan hyro:db:cleanup --days=90
```

### Backup to S3

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-backup-bucket

HYRO_DB_BACKUP_DISK=s3
```

---

## 🔄 Zero-Downtime Deployment

### Using Laravel Envoy

```php
@servers(['web' => 'user@your-server.com'])

@task('deploy', ['on' => 'web'])
    cd /var/www/your-app
    git pull origin main
    composer install --no-dev --optimize-autoloader
    npm install
    npm run build
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan queue:restart
    sudo supervisorctl restart laravel-worker:*
@endtask
```

Run deployment:

```bash
envoy run deploy
```

---

## 📈 Monitoring and Logging

### Application Monitoring

```bash
# Install Laravel Telescope (development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Error Tracking

Configure error tracking service (e.g., Sentry):

```env
SENTRY_LARAVEL_DSN=your-sentry-dsn
```

### Log Management

```bash
# View logs
tail -f storage/logs/laravel.log

# Search logs
grep "ERROR" storage/logs/laravel.log
```

---

## 🆘 Troubleshooting

### Issue: 500 Internal Server Error

```bash
# Check logs
tail -f storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Check permissions
ls -la storage/
ls -la bootstrap/cache/
```

### Issue: Queue Not Processing

```bash
# Check queue workers
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laravel-worker:*

# Check Redis
redis-cli ping
```

### Issue: Slow Performance

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan optimize
```

---

## 📚 Post-Deployment

### 1. Verify Deployment

```bash
# Check application status
php artisan hyro:health

# Check database
php artisan hyro:db:status

# Test API
curl https://your-domain.com/api/hyro/health
```

### 2. Monitor Performance

- Check response times
- Monitor error rates
- Review resource usage
- Check queue processing

### 3. Update Documentation

- Document deployment date
- Note any issues encountered
- Update runbook

---

**Deployment Complete!** 🎉

Your Hyro application is now live in production!
