# Changelog

All notable changes to the Hyro package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.5] - 2026-02-23

### Fixed
- Fixed `@hyroCss`, `@hyroJs`, and `@hyroAssets` Blade directives not outputting CSS/JS tags
- Changed namespace from `Marufsharia\Hyro\Helpers\HyroAsset` to `Marufsharia\Hyro\Core\Helpers\HyroAsset` in HyroServiceProvider
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
