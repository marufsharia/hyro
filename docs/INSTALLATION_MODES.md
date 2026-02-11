# Hyro Installation Modes

Hyro provides multiple installation modes to suit different project needs, from minimal production-ready setups to full development environments with all features.

## Installation Modes

### 🚀 Silent Mode (Zero Configuration)

Perfect for production deployments and CI/CD pipelines. Installs with zero user interaction and minimal configuration.

```bash
php artisan hyro:install --mode=silent --no-interaction
```

**What it installs:**
- ✓ Essential configuration file
- ✓ Database migrations
- ✓ Compiled CSS/JS assets
- ✓ Default roles and privileges
- ✗ Views (uses package views)
- ✗ Translations (uses package translations)
- ✗ CRUD stubs/templates

**Use when:**
- Deploying to production
- Running in CI/CD pipelines
- You want zero configuration
- You don't need to customize views

---

### 📦 Minimal Mode (Recommended)

The recommended installation for most projects. Installs only essential files needed for the package to work.

```bash
php artisan hyro:install --mode=minimal
```

Or simply:
```bash
php artisan hyro:install
```

**What it installs:**
- ✓ Essential configuration file
- ✓ Database migrations
- ✓ Compiled CSS/JS assets
- ✓ Default roles and privileges
- ✗ Views (uses package views)
- ✗ Translations (uses package translations)
- ✗ CRUD stubs/templates

**Use when:**
- Starting a new project
- You don't need to customize views
- You want a clean, minimal setup
- You're using Hyro for authorization only

---

### 🎨 CRUD Mode

Minimal installation plus CRUD generator templates and stubs. Perfect for projects that will use the CRUD generator extensively.

```bash
php artisan hyro:install --mode=crud
```

**What it installs:**
- ✓ Essential configuration file
- ✓ Database migrations
- ✓ Compiled CSS/JS assets
- ✓ Default roles and privileges
- ✓ CRUD generator stubs
- ✓ CRUD frontend templates
- ✗ Views (uses package views)
- ✗ Translations (uses package translations)

**Use when:**
- You'll use the CRUD generator
- You want to customize CRUD templates
- You need custom frontend templates
- Building admin panels or dashboards

**After installation:**
```bash
# Generate CRUD for your models
php artisan hyro:crud Product
php artisan hyro:crud Order --template=ecommerce
```

---

### 🎁 Full Mode

Installs everything including views, translations, and all customizable files. Best for development and when you need full control.

```bash
php artisan hyro:install --mode=full
```

**What it installs:**
- ✓ Essential configuration file
- ✓ Database migrations
- ✓ Compiled CSS/JS assets
- ✓ Default roles and privileges
- ✓ CRUD generator stubs
- ✓ CRUD frontend templates
- ✓ All Blade views
- ✓ Translation files

**Use when:**
- You need to customize views
- You want to modify translations
- You need full control over everything
- Development/learning environment

---

## Interactive Installation

Run without any options for an interactive installation wizard:

```bash
php artisan hyro:install
```

The wizard will:
1. Show you all available modes
2. Let you choose your preferred mode
3. Display installation progress
4. Show next steps after completion

---

## Command Options

### `--mode`
Specify installation mode directly:
```bash
php artisan hyro:install --mode=minimal
php artisan hyro:install --mode=crud
php artisan hyro:install --mode=full
php artisan hyro:install --mode=silent
```

### `--no-interaction`
Run without any prompts (uses minimal mode by default):
```bash
php artisan hyro:install --no-interaction
```

### `--force`
Skip confirmation prompts and overwrite existing files:
```bash
php artisan hyro:install --mode=full --force
```

---

## Comparison Table

| Feature | Silent | Minimal | CRUD | Full |
|---------|--------|---------|------|------|
| Configuration | ✓ | ✓ | ✓ | ✓ |
| Migrations | ✓ | ✓ | ✓ | ✓ |
| Compiled Assets | ✓ | ✓ | ✓ | ✓ |
| Initial Data | ✓ | ✓ | ✓ | ✓ |
| CRUD Stubs | ✗ | ✗ | ✓ | ✓ |
| CRUD Templates | ✗ | ✗ | ✓ | ✓ |
| Views | ✗ | ✗ | ✗ | ✓ |
| Translations | ✗ | ✗ | ✗ | ✓ |
| User Interaction | ✗ | ✓ | ✓ | ✓ |
| Disk Space | ~2MB | ~2MB | ~5MB | ~10MB |

---

## After Installation

Regardless of the mode you choose, follow these steps:

### 1. Add Trait to User Model

```php
use Marufsharia\Hyro\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
    // ...
}
```

### 2. Review Configuration

```bash
# Edit config/hyro.php
nano config/hyro.php
```

### 3. Create Admin User

```bash
php artisan hyro:user:create
```

### 4. Run Health Check

```bash
php artisan hyro:health-check
```

### 5. (Optional) Generate CRUD

If you installed CRUD mode:
```bash
php artisan hyro:crud Product
```

---

## Upgrading Between Modes

You can upgrade from a minimal installation to full at any time:

```bash
# Upgrade from minimal to CRUD
php artisan hyro:install --mode=crud --force

# Upgrade from CRUD to full
php artisan hyro:install --mode=full --force
```

**Note:** Use `--force` to overwrite existing files.

---

## CI/CD Integration

### GitHub Actions

```yaml
- name: Install Hyro
  run: php artisan hyro:install --mode=silent --no-interaction
```

### GitLab CI

```yaml
install_hyro:
  script:
    - php artisan hyro:install --mode=silent --no-interaction
```

### Docker

```dockerfile
RUN php artisan hyro:install --mode=silent --no-interaction
```

---

## Troubleshooting

### "Command not found"

Make sure Hyro is installed via Composer:
```bash
composer require marufsharia/hyro
```

### "Permission denied"

Ensure proper permissions:
```bash
chmod -R 755 storage bootstrap/cache
```

### "Migration failed"

Check database connection:
```bash
php artisan migrate:status
```

### Re-install

To completely re-install:
```bash
# Remove published files
rm -rf config/hyro.php
rm -rf resources/views/vendor/hyro
rm -rf resources/stubs/hyro

# Re-install
php artisan hyro:install --mode=full --force
```

---

## Best Practices

1. **Production:** Use `silent` or `minimal` mode
2. **Development:** Use `crud` or `full` mode
3. **Version Control:** Don't commit published views unless customized
4. **Updates:** Re-run install with `--force` after package updates
5. **Customization:** Only publish what you need to customize

---

## Support

- **Documentation:** https://github.com/marufsharia/hyro
- **Issues:** https://github.com/marufsharia/hyro/issues
- **Discussions:** https://github.com/marufsharia/hyro/discussions
