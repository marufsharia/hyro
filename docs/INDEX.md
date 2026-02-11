# 📚 Hyro Documentation Index

Welcome to the Hyro documentation! This index will help you find the information you need.

---

## 🚀 Getting Started

### Essential Guides
1. **[Installation Guide](INSTALLATION.md)** - Complete installation instructions
2. **[Configuration Reference](CONFIGURATION.md)** - All configuration options
3. **[Usage Guide](USAGE.md)** - Usage examples and patterns
4. **[Quick Start: CRUD](QUICK_START_CRUD_TEMPLATES.md)** - Generate CRUD in minutes

---

## 📖 Core Features

### Authentication & Authorization
- **[Usage Guide](USAGE.md)** - Roles, privileges, and permissions
- **[API Documentation](API.md)** - REST API endpoints

### CRUD Generator
- **[CRUD Generator Guide](HyroCRUDGenerator.md)** - Complete CRUD generator documentation
- **[CRUD Template System](CRUD_TEMPLATE_SYSTEM.md)** - Template system overview
- **[Frontend Templates Guide](FRONTEND_TEMPLATES_GUIDE.md)** - 10 beautiful templates
- **[Quick Start: CRUD](QUICK_START_CRUD_TEMPLATES.md)** - Quick start guide

### Notifications
- **[Notification System](NOTIFICATIONS.md)** - Complete notification guide
- **[Quick Start: Notifications](QUICK_START_NOTIFICATIONS.md)** - Quick start guide

### Database Management
- **[Database Management](DATABASE_MANAGEMENT.md)** - Backup, restore, optimize

---

## 🔧 Advanced Topics

### System Architecture
- **[Smart Resource Loading](COMPLETE_SMART_LOADING_GUIDE.md)** - How Hyro loads resources
- **[Smart Route Loading](SMART_ROUTE_LOADING.md)** - Route loading system
- **[Smart Resource Loading](SMART_RESOURCE_LOADING.md)** - Asset loading system

### Development
- **[Route Backup Guide](ROUTE_BACKUP_GUIDE.md)** - Automatic route backups
- **[Contributing Guide](CONTRIBUTING.md)** - How to contribute
- **[Changelog](CHANGELOG.md)** - Version history

---

## 🚀 Deployment

- **[Deployment Guide](DEPLOYMENT.md)** - Production deployment checklist
- **[Configuration Reference](CONFIGURATION.md)** - Environment variables

---

## 📋 Quick Reference

### Installation
```bash
composer require marufsharia/hyro
php artisan vendor:publish --tag=hyro-config
php artisan migrate
php artisan hyro:user:create --admin
```

### Generate CRUD
```bash
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal" \
    --migration
```

### Check Permissions
```php
if (auth()->user()->hasRole('admin')) {
    // Admin code
}
```

---

## 🆘 Need Help?

1. Check the relevant guide above
2. Review the [README](../README.md)
3. Check [GitHub Issues](https://github.com/marufsharia/hyro/issues)
4. Contact: marufsharia@gmail.com

---

## 📝 Documentation Structure

```
docs/
├── INDEX.md (this file)
├── INSTALLATION.md
├── CONFIGURATION.md
├── USAGE.md
├── DEPLOYMENT.md
├── API.md
├── HyroCRUDGenerator.md
├── CRUD_TEMPLATE_SYSTEM.md
├── FRONTEND_TEMPLATES_GUIDE.md
├── QUICK_START_CRUD_TEMPLATES.md
├── NOTIFICATIONS.md
├── QUICK_START_NOTIFICATIONS.md
├── DATABASE_MANAGEMENT.md
├── ROUTE_BACKUP_GUIDE.md
├── COMPLETE_SMART_LOADING_GUIDE.md
├── SMART_ROUTE_LOADING.md
├── SMART_RESOURCE_LOADING.md
├── CONTRIBUTING.md
└── CHANGELOG.md
```

---

**Happy coding with Hyro! 🚀**
