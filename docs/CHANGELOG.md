# Changelog

All notable changes to Hyro will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0-beta.3] - 2026-02-09

### Added
- Added 10 frontend CRUD templates (blog, landing, news, ecommerce, portfolio, magazine, gallery, directory, dashboard, minimal)
- Added CRUD template system with `--frontend` and `--template` options
- Added automatic route backup system before CRUD generation
- Added `hyro:route-backup` command with list, backup, restore, and clean actions
- Added frontend route support in SmartCrudRouteManager
- Added route conflict detection and resolution
- Added CRUD_TEMPLATE_SYSTEM.md documentation
- Added FRONTEND_TEMPLATES_GUIDE.md documentation
- Added ROUTE_BACKUP_GUIDE.md documentation
- Added template README with usage examples

### Changed
- Enhanced MakeCrudCommand to support frontend templates
- Updated SmartCrudRouteManager with backup functionality
- Improved route registration with automatic backups
- Updated documentation index with new guides
- Enhanced README with template and backup features

### Fixed
- Fixed namespace issues in BaseCrudComponent and HasCrud trait
- Fixed route references in admin dashboard and layout views
- Fixed incorrect route names (admin.* to hyro.admin.*)
- Added Route::has() checks to prevent undefined route errors

## [1.0.0-beta.2] - 2026-02-08

### Fixed
- Fixed type mismatch error in event system when using App\Models\User instead of Marufsharia\Hyro\Models\User
- Fixed RoleAssigned event to accept Authenticatable interface instead of concrete User model
- Fixed RoleRevoked event to accept Authenticatable interface
- Fixed PrivilegeGranted event to accept Authenticatable interface
- Fixed PrivilegeRevoked event to accept Authenticatable interface
- Fixed UserSuspended event to accept Authenticatable interface
- Fixed UserUnsuspended event to accept Authenticatable interface
- Fixed TokenSynchronizationListener subscribe method signature
- Fixed AuditLogListener to use correct field names (event, auditable_type, auditable_id)
- Fixed NotificationListener to properly handle events
- Fixed RoleAssignedNotification syntax error (removed blank line before <?php tag)
- Fixed EventServiceProvider to avoid duplicate listener registrations

### Added
- Added `hyro:user:create` command for easy user creation with interactive prompts
- Added support for --name, --email, --password, --admin, and --role options in user:create command
- Added handle() method to AuditLogListener for proper event handling
- Added handle() method to NotificationListener for proper event handling

### Changed
- Updated all event classes to use Illuminate\Contracts\Auth\Authenticatable interface
- Improved event listener registration to use subscriber pattern
- Enhanced compatibility with custom User models
- Better error handling in event listeners

## [1.0.0-beta] - 2026-02-08

### Added
- Complete notification system with 7 notification types
- Multi-channel notification support (Email, Database, Push, SMS)
- Beautiful notification center UI with Livewire
- Real-time notification bell component
- User notification preferences management
- Database backup and restore system
- Database optimization tools
- Database health monitoring
- Automatic backup cleanup with retention policies
- PHP-based backup fallback for Windows
- Compression and encryption support for backups
- 5 new database management CLI commands
- Comprehensive documentation for notifications
- Comprehensive documentation for database management

### Changed
- Updated README with complete feature list
- Enhanced configuration system
- Improved error handling across all services
- Better cache invalidation strategies
- Optimized database queries

### Fixed
- Array access error in DatabaseOptimizationService
- mysqldump not found issue on Windows
- Table name extraction from Schema::getTables()
- Windows-specific command formatting

## [0.9.0] - 2026-01-30

### Added
- REST API with RBAC protection
- Sanctum token authentication
- API documentation endpoint
- Rate limiting for API endpoints
- API versioning support
- 40+ CLI commands for management
- CRUD generator with auto-discovery
- Plugin management system
- Plugin marketplace integration
- Remote plugin installation

### Changed
- Improved authorization resolution system
- Enhanced audit logging performance
- Better wildcard privilege matching

### Fixed
- Cache invalidation issues
- Token synchronization bugs
- Role assignment edge cases

## [0.8.0] - 2026-01-15

### Added
- Audit logging system with yearly partitioning
- Sensitive data sanitization
- Batch tracking with UUID
- Tag-based filtering
- Automatic cleanup with retention policies
- AuditRequest middleware
- AuditLogListener for event-driven logging

### Changed
- Optimized audit log queries
- Improved partition management
- Enhanced logging performance

## [0.7.0] - 2026-01-01

### Added
- Admin dashboard with Tailwind CSS
- Livewire 3.x components
- User management UI
- Role management UI
- Privilege management UI
- Alpine.js interactivity
- Responsive design

### Changed
- Updated UI components
- Improved user experience
- Better mobile support

## [0.6.0] - 2025-12-15

### Added
- Livewire components for CRUD operations
- BaseCrudComponent abstract class
- HasCrud trait for reusable functionality
- Dynamic form rendering
- File upload support
- Search and pagination

### Changed
- Improved component architecture
- Better code reusability
- Enhanced form handling

## [0.5.0] - 2025-12-01

### Added
- Service providers and middleware
- BladeDirectivesServiceProvider with 15+ directives
- EventServiceProvider for event-listener mappings
- MiddlewareServiceProvider for middleware registration
- ApiServiceProvider for API-specific services
- Multiple middleware for authorization

### Changed
- Reorganized service provider structure
- Improved middleware performance
- Better event handling

## [0.4.0] - 2025-11-15

### Added
- Core models with comprehensive relationships
- User model with HasHyroAccess trait
- Role model with hierarchical support
- Privilege model with wildcard patterns
- AuditLog model with polymorphic relationships
- UserSuspension model for temporal control

### Changed
- Enhanced model relationships
- Improved query performance
- Better model documentation

## [0.3.0] - 2025-11-01

### Added
- Database schema with migrations
- Yearly partitioning for audit logs
- Composite indexes for performance
- Foreign key constraints
- JSON columns with GIN indexes (PostgreSQL)
- UUID support (configurable)

### Changed
- Optimized database structure
- Improved indexing strategy
- Better partition management

## [0.2.0] - 2025-10-15

### Added
- Configuration system
- Environment variable support
- Feature toggles
- Security configuration
- Cache configuration
- Auditing configuration

### Changed
- Enhanced configuration options
- Better default values
- Improved documentation

## [0.1.0] - 2025-10-01

### Added
- Initial release
- Basic package structure
- Service provider
- Configuration file
- README documentation

---

## Version History

- **1.0.0-beta** - Current version (87% complete)
- **0.9.0** - API and CLI commands
- **0.8.0** - Audit logging
- **0.7.0** - Admin UI
- **0.6.0** - Livewire components
- **0.5.0** - Service providers
- **0.4.0** - Core models
- **0.3.0** - Database schema
- **0.2.0** - Configuration
- **0.1.0** - Initial release

---

## Upgrade Guide

### Upgrading to 1.0.0-beta from 0.9.0

```bash
# Update package
composer update marufsharia/hyro

# Publish new assets
php artisan vendor:publish --provider="Marufsharia\Hyro\HyroServiceProvider" --force

# Run new migrations (if any)
php artisan migrate

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Breaking Changes

None in this release.

### Deprecations

None in this release.

---

## Support

For support, please:
- Check the [documentation](README.md)
- Search [existing issues](https://github.com/marufsharia/hyro/issues)
- Create a [new issue](https://github.com/marufsharia/hyro/issues/new)

---

## License

Hyro is open-sourced software licensed under the [MIT license](LICENSE).
