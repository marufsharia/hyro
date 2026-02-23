# Windows Installation Fix for Hyro

## The Problem

When running `composer require marufsharia/hyro` on Windows, you get:
```
F:\Web\Laravel\2026\hyro-test\vendor/marufsharia/hyro does not exist and could not be created
```

This is a **Windows long path limitation** issue, not a package problem.

## ✅ Solution 1: Enable Long Paths in Windows (Recommended)

### Step 1: Enable Long Paths via Registry

Run PowerShell as Administrator and execute:

```powershell
# Enable long paths in Windows
New-ItemProperty -Path "HKLM:\SYSTEM\CurrentControlSet\Control\FileSystem" `
-Name "LongPathsEnabled" -Value 1 -PropertyType DWORD -Force

# Enable long paths for Git
git config --system core.longpaths true
```

### Step 2: Restart PowerShell

Close and reopen PowerShell (as Administrator)

### Step 3: Install Hyro

```powershell
cd F:\Web\Laravel\2026\hyro-test
composer clear-cache
composer require marufsharia/hyro
```

---

## ✅ Solution 2: Use Shorter Path

Move your project to a shorter path:

```powershell
# Create project in shorter path
cd C:\
mkdir projects
cd projects

# Create Laravel project
composer create-project laravel/laravel myapp
cd myapp

# Install Hyro
composer require marufsharia/hyro
```

---

## ✅ Solution 3: Manual Installation Steps

If the above doesn't work, try this step-by-step approach:

```powershell
# Navigate to project
cd F:\Web\Laravel\2026\hyro-test

# Remove vendor and lock file
Remove-Item -Recurse -Force vendor -ErrorAction SilentlyContinue
Remove-Item composer.lock -ErrorAction SilentlyContinue

# Clear all Composer caches
composer clear-cache

# Update Composer to latest version
composer self-update

# Install with verbose output
composer require marufsharia/hyro -vvv
```

---

## ✅ Solution 4: Install Dependencies First

Sometimes installing dependencies separately helps:

```powershell
cd F:\Web\Laravel\2026\hyro-test

# Install dependencies one by one
composer require livewire/livewire
composer require laravel/sanctum
composer require jantinnerezo/livewire-alert
composer require wire-elements/modal
composer require pragmarx/google2fa

# Now install Hyro
composer require marufsharia/hyro
```

---

## ✅ Solution 5: Modify composer.json Directly

Edit your project's `composer.json`:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "marufsharia/hyro": "^1.0"
    }
}
```

Then run:
```powershell
composer update
```

---

## ✅ Solution 6: Use Composer with --prefer-source

```powershell
cd F:\Web\Laravel\2026\hyro-test
composer require marufsharia/hyro --prefer-source
```

---

## ✅ Solution 7: Check Antivirus/Windows Defender

Windows Defender or antivirus might be blocking file creation:

1. **Temporarily disable Windows Defender:**
   - Open Windows Security
   - Virus & threat protection
   - Manage settings
   - Turn off Real-time protection (temporarily)

2. **Try installation again:**
   ```powershell
   composer require marufsharia/hyro
   ```

3. **Re-enable Windows Defender**

---

## ✅ Solution 8: Run as Administrator

1. Right-click PowerShell
2. Select "Run as Administrator"
3. Navigate to project:
   ```powershell
   cd F:\Web\Laravel\2026\hyro-test
   ```
4. Install:
   ```powershell
   composer require marufsharia/hyro
   ```

---

## ✅ Solution 9: Fix Folder Permissions

```powershell
# Run as Administrator
$path = "F:\Web\Laravel\2026\hyro-test"

# Take ownership
takeown /F $path /R /D Y

# Grant full permissions
icacls $path /grant "${env:USERNAME}:(OI)(CI)F" /T

# Try installation
cd $path
composer require marufsharia/hyro
```

---

## ✅ Solution 10: Fresh Installation

Complete fresh start:

```powershell
# Remove old project
cd F:\Web\Laravel\2026
Remove-Item -Recurse -Force hyro-test

# Create fresh Laravel project
composer create-project laravel/laravel hyro-test

# Navigate to project
cd hyro-test

# Configure for SQLite (quick testing)
New-Item -ItemType File -Path "database\database.sqlite" -Force

# Update .env
(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' | Set-Content .env
(Get-Content .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite' | Set-Content .env

# Install Hyro
composer require marufsharia/hyro

# Publish and migrate
php artisan vendor:publish --tag=hyro-config
php artisan vendor:publish --tag=hyro-migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin
```

---

## Verification After Successful Installation

```powershell
# Check if package is installed
composer show marufsharia/hyro

# Expected output:
# name     : marufsharia/hyro
# descrip. : Hyro is a modular Laravel RBAC ecosystem...
# versions : * v1.0.0

# Check Hyro commands
php artisan list | findstr hyro

# Expected output:
# hyro:user:create
# hyro:role:create
# hyro:privilege:create
# hyro:make-crud
# ... (50+ commands)
```

---

## Quick Start After Installation

```powershell
# Publish configuration
php artisan vendor:publish --tag=hyro-config

# Publish migrations
php artisan vendor:publish --tag=hyro-migrations

# Run migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin

# Start server
php artisan serve

# Visit: http://localhost:8000/admin/hyro
```

---

## Still Having Issues?

### Check Package on Packagist

Visit: https://packagist.org/packages/marufsharia/hyro

Verify:
- ✅ Version v1.0.0 is listed
- ✅ "Last updated" shows recent date
- ✅ No warnings or errors

### Check Composer Version

```powershell
composer --version
# Should be 2.0 or higher
```

Update if needed:
```powershell
composer self-update
```

### Check PHP Version

```powershell
php -v
# Should be 8.2 or higher
```

### Get Detailed Error Information

```powershell
composer require marufsharia/hyro -vvv 2>&1 | Out-File error.log
notepad error.log
```

---

## Common Error Messages and Solutions

### "does not exist and could not be created"
**Solution:** Enable Windows long paths (Solution 1) or use shorter path (Solution 2)

### "Failed to download from dist"
**Solution:** Clear cache and try with `--prefer-source`

### "Your requirements could not be resolved"
**Solution:** Update Composer and clear cache

### "Access is denied"
**Solution:** Run PowerShell as Administrator

---

## Contact Support

If none of these solutions work:

1. **GitHub Issues:** https://github.com/marufsharia/hyro/issues
2. **Email:** marufsharia@gmail.com
3. **Include:**
   - Full error message
   - Windows version
   - PHP version
   - Composer version
   - Output of `composer require marufsharia/hyro -vvv`

---

## Success Indicators

Installation is successful when you see:

```
Package operations: 8 installs, 0 updates, 0 removals
  - Installing spatie/laravel-package-tools (1.93.0): Extracting archive
  - Installing livewire/livewire (v3.7.10): Extracting archive
  - Installing wire-elements/modal (2.0.14): Extracting archive
  - Installing paragonie/constant_time_encoding (v3.1.3): Extracting archive
  - Installing pragmarx/google2fa (v8.0.3): Extracting archive
  - Installing laravel/sanctum (v4.3.1): Extracting archive
  - Installing jantinnerezo/livewire-alert (v3.0.3): Extracting archive
  - Installing marufsharia/hyro (v1.0.0): Extracting archive
Writing lock file
Installing dependencies from lock file
Generating optimized autoload files
```

And you can run:
```powershell
php artisan list | findstr hyro
```

---

**The package is correctly published on Packagist. This is a Windows filesystem issue that can be resolved with the solutions above.** 

**Recommended:** Try Solution 1 (Enable Long Paths) first, as it fixes the root cause.
