# Before Releasing Hyro v2.0.0

## Critical Tasks Before Release

### 1. Build Assets (REQUIRED)

The most important step - build the assets and commit them:

```bash
cd packages/marufsharia/hyro

# Install dependencies
npm install

# Build for production
npm run build

# Verify build output
ls -la dist/
# Should see:
# - manifest.json
# - css/hyro-[hash].css
# - js/hyro-[hash].js
```

**Why this is critical:**
- The `/dist` directory contains pre-built assets
- End users don't need to build assets themselves
- Package works immediately after `composer require`
- CDN fallback only works if build fails

### 2. Commit Built Assets

```bash
# Add dist directory to git
git add dist/

# Verify files are staged
git status

# Should see:
# new file:   dist/manifest.json
# new file:   dist/css/hyro-[hash].css
# new file:   dist/js/hyro-[hash].js
```

**Important:** The `.gitignore` has been updated to NOT ignore `/dist/`

### 3. Test Installation

Create a fresh Laravel project and test:

```bash
# Create test project
composer create-project laravel/laravel hyro-test
cd hyro-test

# Add local package for testing
# Edit composer.json:
{
    "repositories": [
        {
            "type": "path",
            "url": "../packages/marufsharia/hyro"
        }
    ]
}

# Install package
composer require marufsharia/hyro

# Run install command
php artisan hyro:install

# Check assets published
ls -la public/vendor/hyro/

# Run migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin

# Start server and test
php artisan serve
# Visit: http://localhost:8000/admin
```

**Verify:**
- [ ] Assets published to `public/vendor/hyro/`
- [ ] Manifest file exists
- [ ] CSS loads correctly
- [ ] JavaScript works
- [ ] Admin panel displays properly
- [ ] No console errors

### 4. Test CDN Fallback

```bash
# Delete published assets
rm -rf public/vendor/hyro/

# Refresh admin panel
# Should load with CDN assets (Tailwind + Alpine.js)
```

**Verify:**
- [ ] Page loads without errors
- [ ] Styling looks correct
- [ ] JavaScript functionality works
- [ ] Console shows CDN URLs

### 5. Update README.md

Update the installation section in README.md:

```markdown
## 🚀 Installation

### Requirements
- PHP 8.2+
- Laravel 12+
- Composer

### Install via Composer

\`\`\`bash
composer require marufsharia/hyro
\`\`\`

### Run Installation Command

\`\`\`bash
php artisan hyro:install
\`\`\`

This command will:
- Publish configuration files
- Publish assets to public/vendor/hyro
- Publish migrations
- Add HasHyroFeatures trait to User model

### Run Migrations

\`\`\`bash
php artisan migrate
\`\`\`

### Create Admin User

\`\`\`bash
php artisan hyro:user:create --admin
\`\`\`

### Access Admin Panel

Visit: `http://your-app.test/admin`

## 📦 What's New in v2.0

### Filament-Style Asset Architecture

Hyro v2.0 introduces a complete overhaul of the asset management system:

- **Centralized Asset Management**: Register assets programmatically via `AssetManager`
- **Zero Configuration**: Works immediately after installation with CDN fallback
- **Plugin Support**: Plugins can easily register their own assets
- **One-Command Installation**: `php artisan hyro:install` does everything

### New Blade Directive

\`\`\`blade
<!DOCTYPE html>
<html>
<head>
    @hyroAssets  {{-- Single directive for all assets --}}
</head>
<body>
    {{ $slot }}
</body>
</html>
\`\`\`

### Migration from v1.x

See [v2.0.0_RELEASE_NOTES.md](v2.0.0_RELEASE_NOTES.md) for complete migration guide.
```

### 6. Commit All Changes

```bash
# Stage all changes
git add .

# Commit with detailed message
git commit -m "feat: Implement Filament-style asset architecture (v2.0.0)

BREAKING CHANGES:
- Assets now build to /dist directory instead of /public/build
- New @hyroAssets directive replaces @hyroCss and @hyroJs
- Asset publishing path changed from public/build to dist
- Removed raw CSS/JS fallback files, replaced with CDN fallbacks

Features:
- Added AssetManager for centralized asset registration
- Added AssetHelper for manifest reading and URL resolution
- Added hyro:install command for one-command installation
- Added plugin asset support via AssetManager
- Added CDN fallback system (Tailwind CSS + Alpine.js)
- Added Blade component for asset rendering
- Added comprehensive documentation

Documentation:
- Added FILAMENT_STYLE_ARCHITECTURE.md
- Added v2.0.0_RELEASE_NOTES.md
- Added v2.0.0_IMPLEMENTATION_SUMMARY.md
- Added QUICK_REFERENCE_v2.md
- Added v2.0.0_RELEASE_CHECKLIST.md
- Updated CHANGELOG.md

See v2.0.0_RELEASE_NOTES.md for complete details and migration guide."
```

### 7. Tag Release

```bash
# Create annotated tag
git tag -a v2.0.0 -m "Release v2.0.0: Filament-Style Asset Architecture

Major release introducing Filament-inspired asset management system.

Breaking Changes:
- Build directory changed to /dist
- New @hyroAssets directive
- Asset publishing path changed

New Features:
- AssetManager for centralized asset registration
- Plugin asset support
- Zero-configuration installation
- CDN fallback system
- hyro:install command

See v2.0.0_RELEASE_NOTES.md for complete details."

# Verify tag
git tag -l -n9 v2.0.0
```

### 8. Push to GitHub

```bash
# Push main branch
git push origin main

# Push tags
git push origin v2.0.0

# Or push everything
git push origin main --tags
```

### 9. Create GitHub Release

1. Go to: https://github.com/marufsharia/hyro/releases
2. Click "Draft a new release"
3. Choose tag: v2.0.0
4. Release title: "v2.0.0 - Filament-Style Asset Architecture"
5. Copy content from `v2.0.0_RELEASE_NOTES.md`
6. Check "Set as a pre-release" if you want to test first
7. Click "Publish release"

### 10. Verify Packagist

1. Go to: https://packagist.org/packages/marufsharia/hyro
2. Wait for webhook to trigger (usually instant)
3. Verify v2.0.0 appears in versions list
4. Click on v2.0.0 to verify details

## Quick Checklist

Before pushing to GitHub:

- [ ] Assets built (`npm run build`)
- [ ] `/dist` directory committed
- [ ] Tested fresh installation
- [ ] Tested CDN fallback
- [ ] README.md updated
- [ ] All changes committed
- [ ] Version tagged
- [ ] No syntax errors
- [ ] No debug code (dd, dump, var_dump)

After pushing to GitHub:

- [ ] GitHub release created
- [ ] Packagist updated
- [ ] Documentation accessible
- [ ] No critical issues reported

## Files to Review Before Release

### Core Implementation
- `core/src/Support/Assets/AssetManager.php`
- `core/src/Support/Assets/AssetHelper.php`
- `core/src/Console/Commands/InstallCommand.php`

### Service Providers
- `admin-panel/src/AdminPanelServiceProvider.php`
- `src/HyroServiceProvider.php`
- `core/src/CoreServiceProvider.php`

### Configuration
- `vite.config.js`
- `.gitignore`
- `package.json`

### Documentation
- `README.md`
- `CHANGELOG.md`
- `FILAMENT_STYLE_ARCHITECTURE.md`
- `v2.0.0_RELEASE_NOTES.md`
- `QUICK_REFERENCE_v2.md`

### Build Output (Must Exist)
- `dist/manifest.json`
- `dist/css/hyro-[hash].css`
- `dist/js/hyro-[hash].js`

## Common Issues

### Issue: dist/ directory is empty
**Solution:** Run `npm run build`

### Issue: Assets not loading after installation
**Solution:** Run `php artisan hyro:publish-assets --force`

### Issue: CDN fallback not working
**Solution:** Check browser console for errors, verify CDN URLs are accessible

### Issue: Vite build errors
**Solution:** 
```bash
rm -rf node_modules package-lock.json
npm install
npm run build
```

## Testing Commands

```bash
# Test build
npm run build

# Test installation
php artisan hyro:install

# Test asset publishing
php artisan hyro:publish-assets

# Test user creation
php artisan hyro:user:create --admin

# Clear caches
php artisan view:clear
php artisan cache:clear
```

## Final Verification

Before clicking "Publish Release" on GitHub:

1. **Build is complete**: `ls -la dist/` shows files
2. **Assets committed**: `git status` shows dist/ is tracked
3. **Installation works**: Fresh Laravel project installs successfully
4. **Admin panel loads**: No errors in browser console
5. **CDN fallback works**: Delete assets, page still loads
6. **Documentation complete**: All .md files are accurate
7. **No debug code**: No dd(), dump(), var_dump() in code
8. **Version tagged**: `git tag -l` shows v2.0.0

## Post-Release Monitoring

After release, monitor:

- GitHub Issues for installation problems
- Packagist download stats
- User feedback in discussions
- Error reports

Be ready to release v2.0.1 hotfix if critical issues found.

---

**Status**: 🟡 Ready for Final Review
**Next Step**: Build assets and commit
**Estimated Time**: 30 minutes
