# Hyro Package Publishing Guide

This guide will walk you through publishing the Hyro package to GitHub and Packagist.

## Prerequisites

- Git installed and configured
- GitHub account with repository access
- Packagist account (https://packagist.org)
- Composer installed globally

## Step 1: Initialize Git Repository

```bash
cd packages/marufsharia/hyro

# Initialize Git repository
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial commit: Hyro v1.0.0 - Modular Laravel RBAC ecosystem"
```

## Step 2: Connect to GitHub

```bash
# Add remote repository
git remote add origin https://github.com/marufsharia/hyro.git

# Verify remote
git remote -v

# Push to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Create Version Tag

```bash
# Create annotated tag for v1.0.0
git tag -a v1.0.0 -m "Release v1.0.0: Initial stable release

Features:
- Modular architecture with 6 independent packages
- Advanced RBAC with wildcard privileges
- Beautiful admin panel with Livewire 3
- Powerful CRUD generator with 12+ templates
- RESTful API with Sanctum authentication
- Plugin system with hot-loading
- Two-factor authentication (2FA)
- Comprehensive audit logging
- User profile management
- 50+ CLI commands"

# Push tag to GitHub
git push origin v1.0.0

# Verify tag
git tag -l
```

## Step 4: Validate Composer Package

```bash
# Validate composer.json
composer validate --strict

# Test installation locally
composer require marufsharia/hyro:dev-main
```

## Step 5: Submit to Packagist

### Option A: Via Web Interface

1. Go to https://packagist.org/packages/submit
2. Enter repository URL: `https://github.com/marufsharia/hyro`
3. Click "Check"
4. Review package information
5. Click "Submit"

### Option B: Via API (if you have API token)

```bash
curl -X POST https://packagist.org/api/create-package \
  -H "Content-Type: application/json" \
  -d '{
    "repository": {
      "url": "https://github.com/marufsharia/hyro"
    }
  }' \
  -u "YOUR_USERNAME:YOUR_API_TOKEN"
```

## Step 6: Configure GitHub Webhook (Automatic)

Packagist will automatically configure a GitHub webhook for auto-updates when you submit the package.

To verify:
1. Go to your GitHub repository settings
2. Click "Webhooks"
3. You should see a webhook pointing to `https://packagist.org/api/github`

## Step 7: Verify Installation

After Packagist processes your package (usually takes a few minutes):

```bash
# Create a new Laravel project
composer create-project laravel/laravel test-hyro
cd test-hyro

# Install Hyro
composer require marufsharia/hyro

# Verify installation
php artisan list | grep hyro
```

## Step 8: Test Package Installation

```bash
# In a fresh Laravel project
composer require marufsharia/hyro

# Publish configuration
php artisan vendor:publish --tag=hyro-config

# Run migrations
php artisan migrate

# Create admin user
php artisan hyro:user:create --admin

# Start server
php artisan serve

# Visit http://localhost:8000/admin/hyro
```

## Troubleshooting

### Issue: "Package not found"

**Solution:** Wait a few minutes for Packagist to index your package. Check https://packagist.org/packages/marufsharia/hyro

### Issue: "Your requirements could not be resolved"

**Solution:** Ensure all dependencies are available on Packagist and version constraints are correct.

```bash
# Check dependency availability
composer show marufsharia/hyro --all
```

### Issue: "Minimum stability not met"

**Solution:** Ensure you've tagged a stable release (v1.0.0) and pushed it to GitHub.

```bash
# Verify tags
git tag -l

# If tag is missing, create it
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

## Updating the Package

### For Bug Fixes (Patch Release)

```bash
# Make your changes
git add .
git commit -m "Fix: Description of bug fix"

# Create patch tag
git tag -a v1.0.1 -m "Release v1.0.1: Bug fixes"
git push origin main
git push origin v1.0.1
```

### For New Features (Minor Release)

```bash
# Make your changes
git add .
git commit -m "Feature: Description of new feature"

# Create minor tag
git tag -a v1.1.0 -m "Release v1.1.0: New features"
git push origin main
git push origin v1.1.0
```

### For Breaking Changes (Major Release)

```bash
# Make your changes
git add .
git commit -m "Breaking: Description of breaking change"

# Create major tag
git tag -a v2.0.0 -m "Release v2.0.0: Breaking changes"
git push origin main
git push origin v2.0.0
```

## Packagist Auto-Update

Once the GitHub webhook is configured, Packagist will automatically update when you:
- Push commits to main branch
- Create new tags
- Create new releases

## Package Statistics

After publishing, you can track your package at:
- Packagist: https://packagist.org/packages/marufsharia/hyro
- GitHub: https://github.com/marufsharia/hyro

## Support

For issues or questions:
- GitHub Issues: https://github.com/marufsharia/hyro/issues
- Email: marufsharia@gmail.com

## Checklist

- [ ] Git repository initialized
- [ ] All files committed
- [ ] Remote repository added
- [ ] Code pushed to GitHub
- [ ] Version tag created (v1.0.0)
- [ ] Tag pushed to GitHub
- [ ] composer.json validated
- [ ] Package submitted to Packagist
- [ ] GitHub webhook configured
- [ ] Installation tested in fresh Laravel project
- [ ] Documentation reviewed
- [ ] README.md is complete
- [ ] LICENSE file exists
- [ ] CHANGELOG.md is updated

## Next Steps

1. Monitor Packagist for package approval
2. Test installation in a fresh Laravel project
3. Update documentation if needed
4. Announce release on social media
5. Create GitHub release notes
6. Update CHANGELOG.md for future releases

---

**Congratulations!** Your package is now published and ready for the world to use! 🎉
