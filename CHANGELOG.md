# Changelog

All notable changes to the Hyro package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-23

### 🎉 Major Release: Filament-Style Asset Architecture

This is a major release with breaking changes. See [v2.0.0_RELEASE_NOTES.md](v2.0.0_RELEASE_NOTES.md) for complete migration guide.

### Added
- **AssetManager**: Centralized asset registration system (`Marufsharia\Hyro\Core\Support\Assets\AssetManager`)
- **AssetHelper**: Helper class for manifest reading and asset URL resolution
- **@hyroAssets Blade Directive**: Single directive to render all registered assets
- **hyro:install Command**: One-command installation with all necessary setup (includes automatic seeding)
- **hyro:seed Command**: Dedicated command to seed default roles and privileges
- **Plugin Asset Support**: Plugins can now register their own assets programmatically
- **Blade Component**: `resources/views/components/assets.blade.php` for rendering assets
- **Comprehensive Documentation**: Added `FILAMENT_STYLE_ARCHITECTURE.md`
- **Auto-Seeding**: `hyro:install` now automatically runs `HyroInstallSeeder` to create default roles and privileges

### Changed
- **BREAKING**: Assets now build to `/dist` directory instead of `/public/build`
- **BREAKING**: Vite configuration updated to output to `dist/` with custom structure
- **BREAKING**: Asset publishing path changed from `public/build/` to `dist/`
- **BREAKING**: Removed raw CSS/JS fallback files, replaced with CDN fallbacks
- Updated `AdminPanelServiceProvider` to register assets using `AssetManager`
- Updated `HyroServiceProvider` to use new asset rendering system
- Updated `PublishAssetsCommand` to work with `/dist` directory
- Updated `.gitignore` to include `/dist` directory (pre-built assets committed)

### Improved
- Zero-configuration installation - works immediately after `composer require`
- No build step required for end users (pre-built assets included)
- Automatic asset publishing on package install/update
- CDN fallback ensures package works even without published assets
- Plugin-friendly architecture for easy asset registration
- Multi-panel support with centralized asset management
- Better cache busting with versioned filenames

### Deprecated
- `@hyroCss` directive (still works but use `@hyroAssets` instead)
- `@hyroJs` directive (still works but use `@hyroAssets` instead)

### Migration Guide
1. Update composer: `composer update marufsharia/hyro`
2. Reinstall assets: `php artisan hyro:install --force`
3. Update layouts: Replace `@hyroCss` and `@hyroJs` with `@hyroAssets`
4. Clear caches: `php artisan view:clear && php artisan cache:clear`

## [1.0.9] - 2026-02-23

### Fixed
- Fixed auto-publishing to work on `composer update`, not just initial install
- Assets now automatically republish when package is updated with newer assets
- Added timestamp comparison to detect when assets need updating

### Improved
- Smarter asset publishing that checks if package assets are newer than published assets
- Auto-publishing now works seamlessly on both install and update
- Better detection of when assets need to be republished

## [1.0.9] - 2026-02-23

### Added
- CDN fallback for CSS (Tailwind CSS) when built assets are not available
- CDN fallback for JS (Alpine.js + plugins) when built assets are not available
- Inline utility styles for Hyro-specific features (glass effects, scrollbars, etc.)

### Changed
- `HyroAsset::css()` now loads Tailwind CSS from CDN if manifest not found
- `HyroAsset::js()` now loads Alpine.js and plugins from CDN if manifest not found
- Package now works immediately after `composer require` without any build step

### Improved
- True zero-configuration experience - works out of the box
- No npm/build required for end users
- Automatic fallback to CDN ensures styling always works
- Better developer experience with instant functionality

## [1.0.8] - 2026-02-23

### Removed
- Removed duplicate CSS/JS files from `admin-panel/resources/css/` and `admin-panel/resources/js/`
- Removed duplicate views from root `resources/views/`
- Cleaned up package structure to eliminate confusion

### Changed
- Package now has clear separation: `resources/` for build source, `admin-panel/resources/views/` for admin views
- Reduced package size by ~50% by removing duplicates

### Improved
- Clearer package structure following Laravel conventions
- Easier to maintain with single source of truth for assets
- Better documentation of package architecture

## [1.0.7] - 2026-02-23

### Added
- Added `@hyroImage` Blade directive for loading package images
- Automatic asset publishing on package installation (no manual `php artisan hyro:publish-assets` required)
- Auto-publish checks if assets exist before copying to avoid unnecessary operations

### Changed
- Simplified HyroAsset helper class for better performance
- Assets now automatically published when package is installed via Composer
- Updated AdminPanelServiceProvider with `autoPublishAssets()` method

### Improved
- Zero-configuration asset management - works out of the box
- Better fallback path resolution in HyroAsset helper
- Cleaner implementation following Laravel package best practices

## [1.0.6] - 2026-02-23

### Fixed
- Fixed asset publishing to include Vite-built assets (manifest.json and compiled CSS/JS from public/build/)
- Updated AdminPanelServiceProvider to publish built assets instead of source files
- Updated PublishAssetsCommand to copy the entire build directory with manifest
- Assets now properly published with versioned filenames (e.g., hyro-D-y3am57.js)

### Changed
- Asset publishing now includes both built assets (primary) and raw assets (fallback)
- Added `hyro-assets-raw` tag for publishing uncompiled CSS/JS files

## [1.0.5] - 2026-02-23

### Fixed
- Fixed `@hyroCss`, `@hyroJs`, and `@hyroAssets` Blade directives not outputting CSS/JS tags
- Changed namespace from `Marufsharia\Hyro\Helpers\HyroAsset` to `Marufsharia\Hyro\Core\Helpers\HyroAsset` in HyroServiceProvider
- Fixed `addTraitToUserModel()` to use correct namespace `Marufsharia\Hyro\Core\Traits\HasHyroFeatures` instead of `Marufsharia\Hyro\Traits\HasHyroFeatures`
- Improved HyroAsset helper to return HTML comments instead of null when assets not found
- Added fallback logic to load hyro-alert.css and hyro-alert.js when manifest assets unavailable
- Fixed Blade directive string escaping to ensure proper PHP code generation
- Resolves issue where Hyro CSS was not loading in admin panel

## [1.0.4] - 2026-02-23

### Fixed
- Fixed asset publishing paths from `vendor/hyro-admin` to `vendor/hyro` to match view references
- Changed publish tags from `hyro-admin-assets` to `hyro-assets`

### Added
- `hyro:publish-assets` command for easy asset publishing

## [1.0.3] - 2026-02-23

### Fixed
- Fixed README.md to show correct namespace for `HasHyroFeatures` trait
- Changed from `Marufsharia\Hyro\Traits\HasHyroFeatures` to `Marufsharia\Hyro\Core\Traits\HasHyroFeatures`

## [1.0.2] - 2026-02-23

### Added
- `hyro:user:create` command with `--admin` flag for creating users
- `hyro:user:list` command for listing all users
- `hyro:role:create` command for creating roles
- `hyro:role:list` command for listing all roles

## [1.0.1] - 2026-02-23

### Fixed
- Fixed incorrect namespace for `HasUuidConfiguration` trait in migrations
- Changed from `Marufsharia\Hyro\Support\Traits\HasUuidConfiguration` to `Marufsharia\Hyro\Core\Support\Traits\HasUuidConfiguration`
- Resolves "Trait not found" error when running `php artisan migrate`

## [1.0.0] - 2026-02-22

### Added - Modular Architecture Release

#### Core Package (`marufsharia/hyro-core`)
- Multi-resolution authorization system (Token → Privilege → Wildcard → Role → Gate)
- Hierarchical role-based access control (RBAC)
- Wildcard privilege patterns (`users.*`, `posts.*.edit`)
- Temporal access control with role expiration
- User suspension management
- Enterprise audit logging with yearly table partitioning
- Base models: Role, Privilege, User, AuditLog, UserSuspension
- Events system for all authorization actions
- Middleware for role and privilege checking
- Comprehensive contracts and interfaces

#### Auth Package (`marufsharia/hyro-auth`)
- Authentication controllers and middleware
- Two-factor authentication (2FA) with Google Authenticator
- QR code generation for 2FA setup
- Recovery codes (8 codes per user)
- Token synchronization service
- Emergency access commands
- Sanctum token authentication
- Password reset functionality

#### API Package (`marufsharia/hyro-api`)
- RESTful API with complete RBAC
- Sanctum token authentication
- API controllers for users, roles, privileges, suspensions
- Request validation and resources
- API middleware and rate limiting
- Automatic token synchronization
- API documentation endpoint

#### Admin Panel Package (`marufsharia/hyro-admin-panel`)
- Beautiful admin UI with Livewire 3
- Dashboard with statistics and charts
- User management interface
- Role and privilege management
- Settings system with 6 categories:
  - General settings
  - Appearance & branding
  - RBAC configuration
  - CRUD generator settings
  - Plugin management
  - System settings
- Plugin manager UI with activation/deactivation
- Notification center with real-time updates
- Profile management system:
  - Profile information editing
  - Avatar management (upload/Gravatar/default)
  - Password change with validation
  - Two-factor authentication setup
  - Account deletion with 30-day grace period
- Sidebar registry system
- Dark mode support
- Responsive design

#### CRUD Package (`marufsharia/hyro-crud`)
- Advanced CRUD generator with 10+ templates
- Admin templates:
  - Template 1: Full-featured dashboard
  - Template 2: Compact data entry
- Frontend templates:
  - Blog layout
  - E-commerce product grid
  - Portfolio/gallery masonry
  - Magazine-style layout
  - Landing page cards
  - News/media layout
  - Photo gallery grid
  - Business directory list
  - Data dashboard table
  - Minimal clean design
- Auto-generate migrations, models, routes, and views
- Field types: string, text, email, number, decimal, boolean, date, datetime, image, file, select
- Search, pagination, and sorting
- Export functionality (CSV, Excel, PDF)
- Smart route discovery and backup system
- Production-ready code with zero manual fixes

#### Plugin Package (`marufsharia/hyro-plugin`)
- Plugin system with hot-loading
- Remote plugin installation (GitHub, GitLab, Packagist)
- Plugin marketplace integration
- Hook system for extensibility
- Plugin activation/deactivation
- Plugin settings management
- Plugin dependency management
- Plugin version tracking

### Features

#### Authorization
- Multi-resolution authorization system
- Wildcard privilege patterns
- Role hierarchy support
- Temporal access control
- User suspension with expiration
- Protected roles (cannot be deleted)
- Fail-closed security model

#### Audit Logging
- Comprehensive audit trail for all actions
- Yearly table partitioning for performance
- Sensitive data sanitization
- Batch tracking with UUID
- Tag-based filtering and search
- Configurable retention period

#### CLI Commands (50+)
- User management commands
- Role and privilege management
- Plugin management commands
- Database backup and restore
- Emergency access commands
- CRUD generator commands
- Route discovery commands

#### Blade Directives
- `@hasrole('role')` - Check if user has role
- `@hasprivilege('privilege')` - Check if user has privilege
- `@hasanyrole(['role1', 'role2'])` - Check if user has any role
- `@hasallprivileges(['priv1', 'priv2'])` - Check if user has all privileges

#### Middleware
- `hyro.role:role` - Require specific role
- `hyro.privilege:privilege` - Require specific privilege
- `hyro.role:role1,role2` - Require any of multiple roles
- API authentication middleware
- 2FA requirement middleware

### Changed
- Refactored from monolithic to modular architecture
- Separated concerns into 6 independent packages
- Improved namespace organization
- Enhanced service provider structure
- Optimized autoloading (6924 classes)

### Fixed
- Namespace conflicts across packages
- ModuleManager references updated to SidebarRegistry
- EditMode variable references in components
- Controller namespace issues in admin panel
- Route registration and discovery
- Asset loading and compilation

### Removed
- Monolithic package structure
- Duplicate code across modules
- Temporary development files
- Unnecessary documentation files
- Vendor folders from repository
- Update namespace scripts

### Security
- Fail-closed authorization by default
- Protected roles prevent deletion
- Comprehensive audit logging
- Sensitive data sanitization
- CSRF protection
- SQL injection prevention
- XSS prevention
- Rate limiting for API

### Performance
- Optimized database queries
- Caching support for permissions
- Lazy loading of resources
- Smart resource loading system
- Efficient route registration

### Documentation
- Complete README with modular architecture
- Comprehensive DOCUMENTATION.md
- API documentation
- CRUD generator guide
- Plugin development guide
- Security best practices
- Troubleshooting guide

---

## [Unreleased]

### Planned Features
- Advanced reporting system
- Multi-tenancy support
- Advanced notification channels
- Webhook system
- GraphQL API
- Real-time dashboard updates
- Advanced plugin marketplace
- Theme system
- Localization improvements

---

## Version History

### Version Numbering

Hyro follows [Semantic Versioning](https://semver.org/):
- **MAJOR** version for incompatible API changes
- **MINOR** version for new functionality in a backwards compatible manner
- **PATCH** version for backwards compatible bug fixes

### Upgrade Guide

#### From Pre-1.0 to 1.0.0

This is a major architectural change. Follow these steps:

1. **Backup your database**
   ```bash
   php artisan hyro:db:backup
   ```

2. **Update composer.json**
   ```json
   {
       "require": {
           "marufsharia/hyro": "^1.0"
       }
   }
   ```

3. **Update dependencies**
   ```bash
   composer update marufsharia/hyro
   ```

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Clear cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

6. **Update User model** (if not already done)
   ```php
   use Marufsharia\Hyro\Traits\HasHyroFeatures;
   
   class User extends Authenticatable
   {
       use HasHyroFeatures;
   }
   ```

7. **Test your application**
   - Verify authentication works
   - Check role and privilege assignments
   - Test CRUD operations
   - Verify API endpoints

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## Security Vulnerabilities

If you discover a security vulnerability within Hyro, please send an email to marufsharia@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The Hyro package is open-sourced software licensed under the [MIT license](LICENSE).
