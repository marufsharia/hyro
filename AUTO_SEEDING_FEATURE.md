# Auto-Seeding Feature in hyro:install

## Overview

The `hyro:install` command now automatically runs the `HyroInstallSeeder` to create default roles and privileges during installation. This provides a complete zero-configuration setup experience.

## What Gets Seeded

### Roles Created

| Role | Slug | Level | Description |
|------|------|-------|-------------|
| Super Administrator | `super-admin` | 100 | Full system access |
| Administrator | `admin` | 80 | Most privileges except dangerous operations |
| Editor | `editor` | 50 | Content management and viewing |
| User | `user` | 10 | Basic access (default role) |

### Privileges Created

#### Hyro Admin Panel
- `hyro.access` - Access Hyro Admin
- `hyro.sidebar.manage` - Manage Sidebar
- `hyro.cli.access` - Access CLI Tools

#### Role Management
- `roles.view` - View Roles
- `roles.create` - Create Roles
- `roles.edit` - Edit Roles
- `roles.delete` - Delete Roles

#### User Management
- `users.view` - View Users
- `users.create` - Create Users
- `users.edit` - Edit Users
- `users.delete` - Delete Users

#### Content Management
- `content.manage` - Manage Content

### Role-Privilege Assignments

#### Super Administrator
- **All privileges** - Complete system access

#### Administrator
- All privileges **except**:
  - `roles.delete`
  - `users.delete`

#### Editor
- `hyro.access`
- `content.manage`
- `users.view`

#### User
- `hyro.access` (minimal access)

## Installation Flow

### Default Behavior (With Seeding)

```bash
php artisan hyro:install
```

**Interactive prompts:**
1. "Do you want to run migrations now?" (default: yes)
2. "Do you want to seed default roles and privileges?" (default: yes)

**Output:**
```
Installing Hyro...

Publishing configuration...
✓ Configuration published

Publishing assets...
✓ Assets published to public/vendor/hyro

Publishing migrations...
✓ Migrations published

Adding HasHyroFeatures trait to User model...
✓ Trait added to User model

Running migrations...
✓ Migrations completed

Seeding default roles and privileges...
✓ Seeded successfully

Created roles:
  • Super Administrator (level 100)
  • Administrator (level 80)
  • Editor (level 50)
  • User (level 10)

Created privileges:
  • Hyro admin access
  • Role management
  • User management
  • Content management

✓ Hyro installed successfully!

Next steps:
  1. Create an admin user: php artisan hyro:user:create --admin
  2. Visit /admin to access the admin panel
```

### Skip Seeding

```bash
php artisan hyro:install --no-seed
```

This will skip the seeding step. You can run it manually later:

```bash
php artisan hyro:seed
```

Or using the full seeder class:

```bash
php artisan db:seed --class=Marufsharia\\Hyro\\Core\\Database\\Seeders\\HyroInstallSeeder
```

## Command Options

```bash
php artisan hyro:install [options]
```

### Options

| Option | Description |
|--------|-------------|
| `--force` | Overwrite existing files |
| `--no-assets` | Skip asset publishing |
| `--no-migrations` | Skip migration publishing |
| `--no-seed` | Skip running HyroInstallSeeder |

### Examples

```bash
# Full installation with all defaults
php artisan hyro:install

# Force overwrite existing files
php artisan hyro:install --force

# Install without seeding
php artisan hyro:install --no-seed

# Install without assets and seeding
php artisan hyro:install --no-assets --no-seed

# Minimal install (no assets, no migrations, no seeding)
php artisan hyro:install --no-assets --no-migrations --no-seed
```

## Benefits

### 1. Zero Configuration
- No manual seeding required
- Default roles and privileges ready immediately
- Complete setup in one command

### 2. Consistent Setup
- Same roles and privileges across all installations
- Predictable permission structure
- Standardized role levels

### 3. Time Saving
- No need to manually create roles
- No need to manually create privileges
- No need to manually assign permissions

### 4. Best Practices
- Pre-configured security levels
- Sensible default permissions
- Protected system roles

## Manual Seeding

If you skipped seeding during installation or want to re-seed:

### Using the Hyro Seed Command (Recommended)

```bash
# Run the seeder
php artisan hyro:seed

# Force re-seed even if roles exist
php artisan hyro:seed --force
```

### Using Laravel's Database Seeder

```bash
# Run the seeder directly
php artisan db:seed --class=Marufsharia\\Hyro\\Core\\Database\\Seeders\\HyroInstallSeeder

# Or create a database seeder that calls it
# database/seeders/DatabaseSeeder.php
public function run()
{
    $this->call(\Marufsharia\Hyro\Core\Database\Seeders\HyroInstallSeeder::class);
}

# Then run
php artisan db:seed
```

## Customization

### Modifying Default Roles

If you want different default roles, you can:

1. **Skip seeding during install**:
   ```bash
   php artisan hyro:install --no-seed
   ```

2. **Create your own seeder**:
   ```php
   // database/seeders/CustomHyroSeeder.php
   namespace Database\Seeders;
   
   use Illuminate\Database\Seeder;
   use Marufsharia\Hyro\Models\Role;
   use Marufsharia\Hyro\Models\Privilege;
   
   class CustomHyroSeeder extends Seeder
   {
       public function run()
       {
           // Create your custom roles and privileges
           Role::create([
               'slug' => 'custom-role',
               'name' => 'Custom Role',
               'level' => 60,
           ]);
       }
   }
   ```

3. **Run your custom seeder**:
   ```bash
   php artisan db:seed --class=CustomHyroSeeder
   ```

### Adding More Privileges

After installation, you can add more privileges:

```php
use Marufsharia\Hyro\Models\Privilege;

Privilege::create([
    'slug' => 'posts.publish',
    'name' => 'Publish Posts',
    'group' => 'posts',
    'description' => 'Ability to publish posts',
]);
```

## Error Handling

### Seeding Fails

If seeding fails during installation:

```
✗ Seeding failed: [error message]
⚠ You can run it manually later: php artisan hyro:seed
```

**Common causes:**
- Database connection issues
- Migrations not run
- Duplicate entries (if seeder already ran)
- Missing Role or Privilege models

**Solutions:**
1. Check database connection
2. Ensure migrations are run: `php artisan migrate`
3. Run seeder manually: `php artisan hyro:seed`
4. Check error details for specific issues

### Duplicate Entries

The seeder uses `firstOrCreate()`, so it's safe to run multiple times. It will:
- Create roles/privileges if they don't exist
- Skip if they already exist
- Update privilege assignments for roles

## Testing

### Test Installation

```bash
# Create fresh Laravel project
composer create-project laravel/laravel test-hyro
cd test-hyro

# Install Hyro
composer require marufsharia/hyro

# Run install (answer yes to all prompts)
php artisan hyro:install

# Verify roles created
php artisan tinker
>>> \Marufsharia\Hyro\Models\Role::all()->pluck('name', 'slug')
=> [
     "super-admin" => "Super Administrator",
     "admin" => "Administrator",
     "editor" => "Editor",
     "user" => "User",
   ]

# Verify privileges created
>>> \Marufsharia\Hyro\Models\Privilege::count()
=> 11

# Create admin user
php artisan hyro:user:create --admin

# Test admin panel
php artisan serve
# Visit: http://localhost:8000/admin
```

## Best Practices

1. **Use default seeding** for standard installations
2. **Skip seeding** only if you need custom roles/privileges
3. **Don't modify seeded roles** - they're protected by `is_protected` flag
4. **Add custom privileges** after installation
5. **Use role levels** for hierarchical access control
6. **Test permissions** after installation

## Comparison: Before vs After

### Before (v1.x)

```bash
composer require marufsharia/hyro
php artisan vendor:publish --tag=hyro-config
php artisan vendor:publish --tag=hyro-assets
php artisan vendor:publish --tag=hyro-migrations
php artisan migrate
php artisan db:seed --class=Marufsharia\\Hyro\\Core\\Database\\Seeders\\HyroInstallSeeder
php artisan hyro:user:create --admin
```

**6 commands required**

### After (v2.0)

```bash
composer require marufsharia/hyro
php artisan hyro:install
php artisan hyro:user:create --admin
```

**3 commands required** (and the install command is interactive)

## Conclusion

The auto-seeding feature in `hyro:install` provides:

✅ **Zero-configuration setup** - Everything ready in one command
✅ **Consistent installations** - Same roles/privileges everywhere
✅ **Time-saving** - No manual seeding required
✅ **Flexible** - Can skip with `--no-seed` flag
✅ **Safe** - Uses `firstOrCreate()` to prevent duplicates
✅ **Interactive** - Confirms before running migrations and seeding

This enhancement makes Hyro even easier to install and get started with, while maintaining flexibility for advanced users who need custom setups.

---

**Feature Added**: v2.0.0
**Seeder Location**: `core/database/seeders/HyroInstallSeeder.php`
**Command**: `php artisan hyro:install`
