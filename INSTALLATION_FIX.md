# Installation Error Fix

## Error Description

```
F:\Web\Laravel\2026\hyro-test\vendor/marufsharia/hyro does not exist and could not be created
```

This is a Windows filesystem permission issue that occurs when Composer tries to create the vendor directory.

## Solution 1: Clean Installation (Recommended)

Run these commands in your `hyro-test` directory:

```powershell
# Navigate to project
cd F:\Web\Laravel\2026\hyro-test

# Remove vendor directory if it exists
if (Test-Path vendor) { Remove-Item -Recurse -Force vendor }

# Remove composer.lock
if (Test-Path composer.lock) { Remove-Item composer.lock }

# Clear Composer cache
composer clear-cache

# Run as Administrator or fix permissions
# Right-click PowerShell -> Run as Administrator

# Install Hyro
composer require marufsharia/hyro
```

## Solution 2: Run PowerShell as Administrator

1. Close current PowerShell
2. Right-click PowerShell icon
3. Select "Run as Administrator"
4. Navigate to project: `cd F:\Web\Laravel\2026\hyro-test`
5. Run: `composer require marufsharia/hyro`

## Solution 3: Fix Directory Permissions

```powershell
# Give full control to current user
$path = "F:\Web\Laravel\2026\hyro-test"
$acl = Get-Acl $path
$rule = New-Object System.Security.AccessControl.FileSystemAccessRule(
    $env:USERNAME,
    "FullControl",
    "ContainerInherit,ObjectInherit",
    "None",
    "Allow"
)
$acl.SetAccessRule($rule)
Set-Acl $path $acl

# Now try installation
composer require marufsharia/hyro
```

## Solution 4: Fresh Laravel Project

If the above doesn't work, create a completely fresh project:

```powershell
# Navigate to parent directory
cd F:\Web\Laravel\2026

# Remove old test project
Remove-Item -Recurse -Force hyro-test

# Create fresh Laravel project
composer create-project laravel/laravel hyro-test

# Navigate to new project
cd hyro-test

# Install Hyro
composer require marufsharia/hyro
```

## Solution 5: Use Different Directory

Try installing in a directory without special characters or long paths:

```powershell
# Create in simpler path
cd C:\
mkdir test
cd test

# Create Laravel project
composer create-project laravel/laravel myapp
cd myapp

# Install Hyro
composer require marufsharia/hyro
```

## Verification After Installation

Once installed successfully, verify:

```powershell
# Check if package is installed
composer show marufsharia/hyro

# Check Hyro commands
php artisan list | findstr hyro

# Expected output:
# hyro:user:create
# hyro:role:create
# hyro:privilege:create
# hyro:make-crud
# ... (50+ commands)
```

## Alternative: Install from Local Path (For Testing)

If you want to test before Packagist submission:

Edit `composer.json` in your test project:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../HyroExample/packages/marufsharia/hyro",
            "options": {
                "symlink": false
            }
        }
    ],
    "require": {
        "marufsharia/hyro": "@dev"
    }
}
```

Then run:
```powershell
composer update marufsharia/hyro
```

## Common Issues

### Issue: "does not exist and could not be created"
**Cause:** Windows permissions or antivirus blocking
**Solution:** Run PowerShell as Administrator or disable antivirus temporarily

### Issue: "Failed to download from dist"
**Cause:** Packagist hasn't indexed yet or network issue
**Solution:** Wait a few minutes and try again, or clear cache

### Issue: "Package not found"
**Cause:** Packagist hasn't indexed the package yet
**Solution:** Wait 5-10 minutes after Packagist submission

## Check Packagist Status

Before installing, verify the package is available:

1. Visit: https://packagist.org/packages/marufsharia/hyro
2. Check if version v1.0.0 is listed
3. Check "Last updated" timestamp

If the package doesn't appear on Packagist yet, you need to:
1. Submit to Packagist first (https://packagist.org/packages/submit)
2. Wait for indexing (2-10 minutes)
3. Then try installation

## Success Indicators

Installation is successful when you see:

```
Package operations: 8 installs, 0 updates, 0 removals
- Installing marufsharia/hyro (v1.0.0): Extracting archive
Writing lock file
Installing dependencies from lock file
Generating optimized autoload files
```

And no errors appear.

## Need Help?

If you continue to have issues:
1. Check GitHub Issues: https://github.com/marufsharia/hyro/issues
2. Email: marufsharia@gmail.com
3. Provide error message and system info

---

**Note:** The error you're seeing is a Windows filesystem permission issue, not a problem with the Hyro package itself. The package is correctly published on GitHub.
