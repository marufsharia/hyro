# Hyro Documentation Index

Welcome to the Hyro documentation! This index will help you find the information you need.

## 📖 Getting Started

- **[INSTALLATION.md](INSTALLATION.md)** - Step-by-step installation guide
- **[QUICK_START_NOTIFICATIONS.md](QUICK_START_NOTIFICATIONS.md)** - Quick start guide for notifications
- **[CONFIGURATION.md](CONFIGURATION.md)** - Complete configuration reference

## 🚀 Usage Guides

- **[USAGE.md](USAGE.md)** - Comprehensive usage examples and patterns
- **[HyroCRUDGenerator.md](HyroCRUDGenerator.md)** - CRUD generator documentation
- **[CRUD_TEMPLATE_SYSTEM.md](CRUD_TEMPLATE_SYSTEM.md)** - CRUD template system guide
- **[FRONTEND_TEMPLATES_GUIDE.md](FRONTEND_TEMPLATES_GUIDE.md)** - Frontend template guide
- **[ROUTE_BACKUP_GUIDE.md](ROUTE_BACKUP_GUIDE.md)** - Route backup and restore guide
- **[NOTIFICATIONS.md](NOTIFICATIONS.md)** - Notification system guide
- **[DATABASE_MANAGEMENT.md](DATABASE_MANAGEMENT.md)** - Database backup, restore, and optimization

## 🔧 Development

- **[CONTRIBUTING.md](CONTRIBUTING.md)** - How to contribute to Hyro
- **[API.md](API.md)** - REST API documentation
- **[Enhanced.md](Enhanced.md)** - Roadmap and planned enhancements
- **[SMART_ROUTE_LOADING.md](SMART_ROUTE_LOADING.md)** - Smart route loading system
- **[SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md)** - Smart loading for routes, views, and assets
- **[COMPLETE_SMART_LOADING_GUIDE.md](COMPLETE_SMART_LOADING_GUIDE.md)** - Complete guide for all resources

## 🚢 Deployment

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment guide

## 📝 Release Notes

- **[CHANGELOG.md](CHANGELOG.md)** - Version history and changes
- **[PHASE_8_COMPLETION_SUMMARY.md](PHASE_8_COMPLETION_SUMMARY.md)** - Phase 8: Notification System
- **[PHASE_11_COMPLETION_SUMMARY.md](PHASE_11_COMPLETION_SUMMARY.md)** - Phase 11: Database Management
- **[PHASE_15_COMPLETION_SUMMARY.md](PHASE_15_COMPLETION_SUMMARY.md)** - Phase 15: Documentation
- **[BUGFIX_SUMMARY_2026-02-08.md](BUGFIX_SUMMARY_2026-02-08.md)** - Bug fixes (Feb 8, 2026)
- **[ADMIN_REDESIGN_SUMMARY.md](ADMIN_REDESIGN_SUMMARY.md)** - Admin UI redesign details

## 🎯 Quick Links

### Installation
```bash
composer require marufsharia/hyro
php artisan vendor:publish --tag=hyro-config
php artisan migrate
php artisan db:seed --class=Marufsharia\\Hyro\\Database\\Seeders\\HyroSeeder
```

### Create Admin User
```bash
php artisan hyro:user:create --admin
```

### Common Commands
```bash
# User Management
php artisan hyro:user:list
php artisan hyro:user:create

# Role Management
php artisan hyro:role:list
php artisan hyro:role:create

# Privilege Management
php artisan hyro:privilege:list
php artisan hyro:privilege:create

# CRUD Route Backup
php artisan hyro:route-backup list
php artisan hyro:route-backup backup
php artisan hyro:route-backup restore
php artisan hyro:route-backup clean

# Database Management
php artisan hyro:db:backup
php artisan hyro:db:restore
php artisan hyro:db:optimize
```

## 📞 Support

- **GitHub Issues**: [https://github.com/marufsharia/hyro/issues](https://github.com/marufsharia/hyro/issues)
- **Email**: marufsharia@gmail.com
- **Documentation**: You're here! 📚

## 🔍 Search Tips

Use your browser's search function (Ctrl+F or Cmd+F) to find specific topics within each document.

### Common Topics

- **Authentication**: See [USAGE.md](USAGE.md#authorization)
- **Roles & Privileges**: See [USAGE.md](USAGE.md#roles-and-privileges)
- **Blade Directives**: See [USAGE.md](USAGE.md#blade-directives)
- **CLI Commands**: See [USAGE.md](USAGE.md#cli-commands)
- **Notifications**: See [NOTIFICATIONS.md](NOTIFICATIONS.md)
- **Database Backup**: See [DATABASE_MANAGEMENT.md](DATABASE_MANAGEMENT.md)
- **API Endpoints**: See [API.md](API.md)
- **Configuration Options**: See [CONFIGURATION.md](CONFIGURATION.md)
- **Resource Customization**: See [COMPLETE_SMART_LOADING_GUIDE.md](COMPLETE_SMART_LOADING_GUIDE.md)
- **Route Customization**: See [SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md#smart-route-loading)
- **View Customization**: See [SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md#smart-view-loading)
- **Asset Customization**: See [SMART_RESOURCE_LOADING.md](SMART_RESOURCE_LOADING.md#smart-asset-loading)
- **Deployment**: See [DEPLOYMENT.md](DEPLOYMENT.md)

---

**Version**: 1.0.0-beta.2  
**Last Updated**: February 9, 2026  
**Maintained by**: Maruf Sharia
