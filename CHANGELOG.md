# Changelog

All notable changes to Hyro will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive documentation suite
- Installation guide
- Configuration guide
- Usage guide
- Deployment guide
- Contributing guidelines

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
