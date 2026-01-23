# Hyro - Enterprise Auth System for Laravel

> **Namespace:** `MarufSharia\Hyro`
> **Author:** Maruf Sharia
> **Status:** Phase 1 Complete - Foundation Ready

## 🎯 Introduction

Hyro is an enterprise-grade Authentication, Authorization, Role & Privilege Management System for Laravel 12+. Built with security, extensibility, and developer experience in mind.

## ✨ Features (Planned)

| Phase | Status | Features |
|-------|--------|----------|
| 1 | ✅ Complete | Package Foundation, Service Provider, Configuration |
| 2 | 🔄 In Progress | Database Design & Migrations |
| 3 | ⏳ Pending | Core Models & Traits |
| 4 | ⏳ Pending | Authorization & Gate Integration |
| 5 | ⏳ Pending | Middleware System |
| 6 | ⏳ Pending | Artisan CLI (40+ commands) |
| 7 | ⏳ Pending | REST API |
| 8 | ⏳ Pending | Blade Directives |
| 9 | ⏳ Pending | Admin UI |
| 10 | ⏳ Pending | Security Hardening |
| 11 | ⏳ Pending | Documentation |

## 📦 Installation

```bash
composer require marufsharia/hyro
```
🚀 Quick Start
Install package:

```bash
php artisan hyro:install
Run migrations:
```
```bash
php artisan migrate
Create first admin:
```
```bash
php artisan hyro:create-user --admin
```
⚙️ Configuration
Enable/disable features via .env:

```env
# Enable/Disable Features
HYRO_API_ENABLED=true
HYRO_CLI_ENABLED=true
HYRO_UI_ENABLED=false
HYRO_BLADE_DIRECTIVES_ENABLED=true

# Table Names (optional customization)
HYRO_ROLES_TABLE=hyro_roles
HYRO_PRIVILEGES_TABLE=hyro_privileges

# Security
HYRO_PASSWORD_MIN_LENGTH=8
HYRO_MAX_LOGIN_ATTEMPTS=5
```
Or modify config/hyro.php directly after publishing:

```bash
php artisan vendor:publish --tag=hyro-config
🔧 Available Commands (Phase 1)
bash
php artisan hyro:install          # Install Hyro
php artisan hyro:create-user      # Create a new user
php artisan hyro:status   
```        # Check Hyro status (coming soon)
🧪 Testing Phase 1
Manual Test Script
Create a test route in your Laravel application:

```php
// routes/web.php
Route::get('/test-hyro', function() {
    // Test 1: Check if Hyro is loaded
    $status = \MarufSharia\Hyro\Facades\Hyro::status();
    
    // Test 2: Check configuration
    $config = config('hyro');
    
    return [
        'hyro_status' => $status,
        'config_loaded' => !empty($config),
        'features' => $config['features'] ?? [],
    ];
});
```
Or use Tinker:

bash
php artisan tinker
>>> \MarufSharia\Hyro\Facades\Hyro::status()
🏗️ Project Structure
text
marufsharia/hyro/
├── src/
│   ├── HyroServiceProvider.php
│   ├── HyroManager.php
│   ├── Contracts/
│   ├── Facades/
│   ├── Console/
│   └── Http/
├── config/hyro.php
├── database/migrations/
├── resources/views/
├── routes/
└── README.md
📚 Documentation
Complete documentation will be available in Phase 11. For now:

Service Provider: MarufSharia\Hyro\HyroServiceProvider

Facade: MarufSharia\Hyro\Facades\Hyro

Config: config('hyro')

🛡️ Security Notes
Feature Toggles: All features can be disabled via environment variables

Configurable Tables: Avoid table name collisions

Protected Roles: Super-admin and admin roles are protected by default

Audit Logging: Built-in audit trail (enable in config)

🤝 Contributing
This package is under active development. Phase 1 establishes the foundation. Next phases will add:

Database migrations

Core models with traits

Authorization system

Middleware

CLI commands

REST API

Admin UI

Security hardening

📄 License
MIT License. See LICENSE file.

👤 Author
Maruf Sharia

Email: marufsharia@gmail.com

GitHub: @marufsharia

