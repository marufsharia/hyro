# Hyro Quick Start Guide

## ✅ Installation Complete!

Your Hyro package is now installed. Follow these steps to get started.

---

## Step 1: Update User Model

Edit `app/Models/User.php` and add the Hyro trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Marufsharia\Hyro\Core\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasHyroFeatures;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

**Important:** Use `Marufsharia\Hyro\Core\Traits\HasHyroFeatures` (not `Marufsharia\Hyro\Traits\HasHyroFeatures`)

---

## Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=hyro-config
```

This creates `config/hyro.php` where you can customize settings.

---

## Step 3: Run Migrations

```bash
php artisan migrate
```

This creates all necessary database tables:
- hyro_roles
- hyro_privileges
- hyro_role_user
- hyro_privilege_role
- hyro_user_suspensions
- hyro_audit_logs
- hyro_settings
- notifications

---

## Step 4: Create Admin User

```bash
php artisan hyro:user:create --admin
```

You'll be prompted for:
- **Name:** Your name
- **Email:** Your email
- **Password:** Your password (min 8 characters)

This creates a user with the `super-admin` role.

---

## Step 5: Configure Environment (Optional)

Add to your `.env` file:

```env
# Hyro Configuration
HYRO_ENABLED=true
HYRO_ADMIN_PREFIX=admin/hyro

# Features
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit Logging
HYRO_AUDIT_ENABLED=true
```

---

## Step 6: Start Development Server

```bash
php artisan serve
```

---

## Step 7: Access Admin Panel

Open your browser and visit:

```
http://localhost:8000/admin/hyro
```

Login with the admin credentials you created in Step 4.

---

## 🎉 You're Done!

You should now see the Hyro admin dashboard with:
- User Management
- Role Management
- Privilege Management
- Settings
- Plugin Manager
- Profile Management

---

## Available Commands

### User Management
```bash
php artisan hyro:user:create          # Create new user
php artisan hyro:user:create --admin  # Create admin user
php artisan hyro:user:list            # List all users
```

### Role Management
```bash
php artisan hyro:role:create          # Create new role
php artisan hyro:role:list            # List all roles
```

### CRUD Generator
```bash
php artisan hyro:make-crud ModelName --fields="name:string,email:email"
```

### Plugin Management
```bash
php artisan hyro:plugin:list          # List plugins
php artisan hyro:plugin:activate      # Activate plugin
php artisan hyro:plugin:deactivate    # Deactivate plugin
```

### Emergency Access
```bash
php artisan hyro:emergency:create-admin    # Create emergency admin
php artisan hyro:emergency:grant-access    # Grant emergency access
```

### See All Commands
```bash
php artisan list | findstr hyro
```

---

## Common Issues

### Issue: "Trait not found"

**Solution:** Make sure you're using the correct namespace in your User model:
```php
use Marufsharia\Hyro\Core\Traits\HasHyroFeatures;
```

### Issue: "Route not found"

**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan config:clear
```

### Issue: "Class not found"

**Solution:** Clear all caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
composer dump-autoload
```

---

## Next Steps

### 1. Create Roles and Privileges

```bash
# Create a role
php artisan hyro:role:create

# Or use the admin panel
# Visit: http://localhost:8000/admin/hyro/roles
```

### 2. Generate CRUD

```bash
# Generate a blog CRUD
php artisan hyro:make-crud Post \
    --fields="title:string,content:text,published:boolean" \
    --migration

# Generate e-commerce product CRUD
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string,price:decimal,image:image" \
    --migration
```

### 3. Explore the Admin Panel

- **Dashboard:** Overview and statistics
- **Users:** Manage users and assign roles
- **Roles:** Create and manage roles
- **Privileges:** Define granular permissions
- **Settings:** Customize appearance and behavior
- **Plugins:** Extend functionality
- **Profile:** Manage your account

---

## Documentation

- **Full Documentation:** See `DOCUMENTATION.md` in the package
- **CRUD Templates:** See `crud/stubs/templates/README.md`
- **API Documentation:** See API section in README.md
- **GitHub:** https://github.com/marufsharia/hyro
- **Issues:** https://github.com/marufsharia/hyro/issues

---

## Support

If you encounter any issues:

1. Check the documentation
2. Clear all caches
3. Check GitHub issues
4. Create a new issue with:
   - Error message
   - Steps to reproduce
   - Laravel version
   - PHP version

**Email:** marufsharia@gmail.com

---

## Summary Checklist

- [x] Package installed via Composer
- [ ] User model updated with HasHyroFeatures trait
- [ ] Migrations run successfully
- [ ] Admin user created
- [ ] Admin panel accessible
- [ ] All commands working

---

**Congratulations! Hyro is now ready to use!** 🎉
