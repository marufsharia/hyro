<div align="center">

# 🛡️ Hyro

### Hyro is a modular Laravel RBAC ecosystem featuring advanced role-permission management, admin panel, CRUD generator, plugin system, and API layer — built for scalable SaaS and enterprise applications

[![Latest Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/marufsharia/hyro)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D12.0-FF2D20.svg)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Architecture](https://img.shields.io/badge/architecture-modular-brightgreen.svg)](https://github.com/marufsharia/hyro)

**Modular Architecture • Production-Ready • Zero Configuration • Beautiful UI**

[Features](#-features) • [Installation](#-installation) • [Quick Start](#-quick-start) • [Documentation](DOCUMENTATION.md)
</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Modular Architecture](#-modular-architecture)
- [Key Features](#-key-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [CRUD Generator](#-crud-generator)
- [Configuration](#-configuration)
- [Usage Examples](#-usage-examples)
- [CLI Commands](#-cli-commands)
- [API Documentation](#-api-documentation)
- [Security](#-security)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🎯 Overview

**Hyro** is a comprehensive, enterprise-grade Role-Based Access Control (RBAC) and authorization system for Laravel 12+. Built with a modular architecture and modern best practices, Hyro provides everything you need to manage users, roles, privileges, and permissions in your Laravel applications.

### Why Choose Hyro?

✅ **Modular Architecture** - 6 independent packages working seamlessly together  
✅ **Production-Ready** - Battle-tested code ready for enterprise deployment  
✅ **Zero Configuration** - Works out of the box with sensible defaults  
✅ **Beautiful Admin UI** - Modern, responsive interface built with Livewire 3 & Tailwind CSS  
✅ **Powerful CRUD Generator** - Generate complete CRUD interfaces in seconds with 12+ templates  
✅ **RESTful API** - Complete API layer with Sanctum authentication  
✅ **Plugin System** - Extensible architecture with hot-loadable plugins  
✅ **Comprehensive** - Auth, RBAC, audit logs, 2FA, notifications, and more  
✅ **Well-Documented** - Extensive documentation and real-world examples

---

## 🏗️ Modular Architecture

Hyro v1.0.0 features a **modular architecture** with 6 independent packages that work together seamlessly:

### 📦 Core Package (`marufsharia/hyro-core`)
**Foundation of the entire system**

- Multi-resolution authorization engine (Token → Privilege → Wildcard → Role → Gate)
- Core models: Role, Privilege, User, AuditLog, UserSuspension, HyroSetting
- Events system for all RBAC operations
- Middleware for route protection
- Contracts and interfaces
- Base services and repositories
- Helper functions and utilities

**Key Features:**
- Hierarchical RBAC with wildcard support (`users.*`, `posts.*.edit`)
- Temporal access control with role expiration
- User suspension management
- Comprehensive audit logging with yearly partitioning
- Cache invalidation system

### 🔐 Auth Package (`marufsharia/hyro-auth`)
**Authentication and security layer**

- Authentication controllers and middleware
- Two-Factor Authentication (2FA) with Google Authenticator
- Token synchronization service for Sanctum
- Emergency access commands
- Password reset and recovery
- Session management

**Key Features:**
- Google Authenticator 2FA with QR codes
- 8 recovery codes per user
- Token-based authentication
- Emergency admin access
- Lockdown mode for security incidents

### 🌐 API Package (`marufsharia/hyro-api`)
**RESTful API layer**

- Complete REST API with RBAC integration
- API controllers for users, roles, privileges, suspensions
- Request validation and resources
- API middleware and rate limiting
- Sanctum token authentication

**Endpoints:**
- `/api/hyro/auth/*` - Authentication
- `/api/hyro/users/*` - User management
- `/api/hyro/roles/*` - Role management
- `/api/hyro/privileges/*` - Privilege management
- `/api/hyro/suspensions/*` - User suspensions

### 🎨 Admin Panel Package (`marufsharia/hyro-admin-panel`)
**Beautiful admin interface**

- Modern admin UI built with Livewire 3
- Dashboard with statistics and charts
- User, role, and privilege management interfaces
- Settings system with appearance customization
- Plugin manager UI
- Notification center
- Profile management with avatar support
- Sidebar with dynamic menu system

**Key Features:**
- Responsive design with dark mode support
- Real-time updates with Livewire
- Beautiful alerts with Livewire Alert
- Modal dialogs with Wire Elements Modal
- Customizable branding and appearance

### ⚡ CRUD Package (`marufsharia/hyro-crud`)
**Advanced CRUD generator**

- Auto-generate complete CRUD interfaces
- 12+ beautiful templates (2 admin + 10 frontend)
- Auto-generate migrations, models, routes, and views
- Smart route discovery and backup system
- File upload support
- Search, filter, pagination, and sorting
- Export functionality (CSV, Excel, PDF)
- Privilege generation

**Templates:**
- **Admin:** template1 (full-featured), template2 (compact)
- **Frontend:** blog, ecommerce, portfolio, magazine, landing, news, gallery, directory, dashboard, minimal

### 🔌 Plugin Package (`marufsharia/hyro-plugin`)
**Extensibility system**

- Plugin system with hot-loading
- Remote plugin installation (GitHub, GitLab, Packagist)
- Hook system for extensibility
- Plugin marketplace integration
- Plugin activity logging
- Plugin settings management
- Plugin permissions and dependencies

**Key Features:**
- Install plugins from remote sources
- Activate/deactivate without code changes
- Hook into system events
- Plugin-specific settings
- Version management

---

## ✨ Key Features

### 🔐 Advanced Authorization

- **Multi-Resolution Authorization**: Token → Privilege → Wildcard → Role → Gate
- **Hierarchical RBAC**: Roles with inherited privileges
- **Wildcard Privileges**: `users.*` matches `users.create`, `users.edit`, etc.
- **Temporal Access**: Role expiration and time-based access
- **User Suspension**: Temporary or permanent account suspension
- **Protected Roles**: Prevent deletion of critical roles (super-admin, admin)

### 📊 Enterprise Audit Logging

- Comprehensive audit trail for all actions
- Yearly table partitioning for performance
- Sensitive data sanitization (passwords, tokens)
- Batch tracking with UUID
- Tag-based filtering and search
- User activity logs

### 🔔 Notification System

- Multi-channel notifications (Email, Database, Push, SMS)
- Beautiful notification center UI
- Real-time notification bell
- User preference management
- Queue support for performance
- 7 built-in notification types

### ⚡ CRUD Generator

- Generate complete CRUD in seconds
- 12+ beautiful templates
- Production-ready code with zero manual fixes
- Auto-generate migrations, models, routes, views
- File upload support
- Search, pagination, sorting
- Export to CSV/Excel/PDF
- Automatic privilege creation

### 👤 User Profile Management

- Complete profile information management
- Avatar management (upload/Gravatar/default)
- Password change with strong validation
- Two-factor authentication setup
- Account deletion with 30-day grace period
- User activity logging
- Timezone and locale settings

### 🎨 Beautiful Admin UI

- Modern Tailwind CSS interface
- Livewire 3.x components
- Alpine.js interactivity
- Fully responsive design
- Dark mode support
- Customizable branding
- Icon customization (115+ Heroicons)

### 💻 50+ CLI Commands

- User management commands
- Role and privilege management
- Plugin management
- Database backup and restore
- Emergency access commands
- CRUD generation commands

---

## 📋 Requirements

- **PHP:** 8.2 or higher
- **Laravel:** 12.0 or higher
- **Database:** MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+
- **Composer:** 2.0 or higher
- **Node.js:** 18+ (for asset compilation)
- **NPM/Yarn:** Latest version

### Required PHP Extensions
- OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo

---

## 🚀 Installation

### Step 1: Install via Composer

```bash
composer require marufsharia/hyro
```

### Step 2: Publish Configuration and Assets

```bash
# Publish configuration
php artisan vendor:publish --tag=hyro-config

# Publish migrations
php artisan vendor:publish --tag=hyro-migrations

# Publish assets (CSS, JS, images)
php artisan vendor:publish --tag=hyro-assets
```

### Step 3: Configure Environment

Add to your `.env` file:

```env
# Core Settings
HYRO_ENABLED=true
HYRO_ADMIN_PREFIX=admin/hyro

# Features
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_CLI_ENABLED=true

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin
HYRO_PASSWORD_MIN_LENGTH=8

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit Logging
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365
```

### Step 4: Run Migrations

```bash
php artisan migrate
```

### Step 5: Create Admin User

```bash
php artisan hyro:user:create --admin
```

Follow the prompts to create your first admin user.

### Step 6: Add Trait to User Model

Edit `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Marufsharia\Hyro\Core\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
    
    // ... rest of your User model
}
```

### Step 7: Compile Assets (Optional)

```bash
npm install
npm run build
```

**That's it!** Visit `/admin/hyro` to access the admin panel.

---

## 🎯 Quick Start

### Check Roles and Privileges

```php
// Check if user has a role
if (auth()->user()->hasRole('admin')) {
    // Admin only code
}

// Check if user has a privilege
if (auth()->user()->hasPrivilege('users.create')) {
    // Create user
}

// Check multiple roles (any)
if (auth()->user()->hasAnyRole(['admin', 'moderator'])) {
    // Admin or moderator code
}

// Check multiple privileges (all)
if (auth()->user()->hasAllPrivileges(['users.create', 'users.edit'])) {
    // User can create and edit
}
```

### Use Blade Directives

```blade
@hasrole('admin')
    <a href="/admin">Admin Panel</a>
@endhasrole

@hasprivilege('posts.create')
    <button>Create Post</button>
@endhasprivilege

@hasanyrole(['admin', 'moderator'])
    <div>Admin or Moderator Content</div>
@endhasanyrole

@hasallprivileges(['posts.create', 'posts.edit'])
    <button>Manage Posts</button>
@endhasallprivileges
```

### Protect Routes

```php
use Illuminate\Support\Facades\Route;

// Require specific role
Route::middleware(['hyro.role:admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

// Require specific privilege
Route::middleware(['hyro.privilege:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

// Require any of multiple roles
Route::middleware(['hyro.role:admin,moderator'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Require all privileges
Route::middleware(['hyro.privilege:posts.create,posts.edit'])->group(function () {
    Route::resource('posts', PostController::class);
});
```

---

## ⚡ CRUD Generator

Generate complete CRUD interfaces in seconds with beautiful templates!

### Basic Usage

```bash
# Generate admin CRUD
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal,stock:integer" \
    --migration

# Generate frontend CRUD with template
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string,description:text,image:image,price:decimal" \
    --migration
```

### Available Templates

#### Admin Templates
- `admin.template1` - Full-featured dashboard (default)
- `admin.template2` - Compact data entry

#### Frontend Templates
- `frontend.blog` - Blog/article layout
- `frontend.ecommerce` - E-commerce product grid
- `frontend.portfolio` - Portfolio/gallery masonry
- `frontend.magazine` - Magazine-style layout
- `frontend.landing` - Landing page cards
- `frontend.news` - News/media layout
- `frontend.gallery` - Photo gallery grid
- `frontend.directory` - Business directory list
- `frontend.dashboard` - Data dashboard table
- `frontend.minimal` - Minimal clean design

### Real-World Example: E-commerce Products

```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string:required,description:text,price:decimal:required,stock:integer,image:image,category:string" \
    --searchable="name,description,category" \
    --sortable="name,price,stock" \
    --export \
    --migration
```

### What Gets Generated

✅ Livewire component with full CRUD logic  
✅ Beautiful Blade view with chosen template  
✅ Database migration  
✅ Eloquent model (if doesn't exist)  
✅ Routes (admin or frontend)  
✅ Automatic route backup  
✅ Search, filter, and pagination  
✅ File upload support  
✅ Export functionality (optional)  
✅ Privilege creation (optional)

### Supported Field Types

- `string` - Text input
- `text` - Textarea
- `email` - Email input
- `number` / `integer` - Number input
- `decimal` - Decimal number
- `boolean` / `checkbox` - Checkbox
- `date` - Date picker
- `datetime` - DateTime picker
- `image` - Image upload
- `file` - File upload
- `select` - Dropdown select

---

## ⚙️ Configuration

### Environment Variables

```env
# Core Features
HYRO_ENABLED=true
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_CLI_ENABLED=true

# Admin Panel
HYRO_ADMIN_PREFIX=admin/hyro

# Security
HYRO_FAIL_CLOSED=true
HYRO_PROTECTED_ROLES=super-admin,admin
HYRO_PASSWORD_MIN_LENGTH=8
HYRO_MAX_LOGIN_ATTEMPTS=5

# Cache
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600

# Audit Logging
HYRO_AUDIT_ENABLED=true
HYRO_AUDIT_RETENTION_DAYS=365

# Database Backup
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_RETENTION=30
```

### Publish Configuration Files

```bash
# Publish main config
php artisan vendor:publish --tag=hyro-config

# Publish views (for customization)
php artisan vendor:publish --tag=hyro-views

# Publish translations
php artisan vendor:publish --tag=hyro-translations

# Publish CRUD templates
php artisan vendor:publish --tag=hyro-templates
```

---

## 💡 Usage Examples

### User Management

```php
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;

// Create a role
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Content editor role'
]);

// Create a privilege
$privilege = Privilege::create([
    'name' => 'Edit Posts',
    'slug' => 'posts.edit',
    'description' => 'Can edit blog posts'
]);

// Assign privilege to role
$role->grantPrivilege($privilege);

// Assign role to user
$user->assignRole($role);

// Check permissions
if ($user->hasRole('editor')) {
    // User is an editor
}

if ($user->hasPrivilege('posts.edit')) {
    // User can edit posts
}
```

### Wildcard Privileges

```php
// Grant wildcard privilege
$privilege = Privilege::create([
    'slug' => 'posts.*',  // Matches posts.create, posts.edit, posts.delete, etc.
]);

$role->grantPrivilege($privilege);

// Now user with this role can do anything with posts
$user->hasPrivilege('posts.create');  // true
$user->hasPrivilege('posts.edit');    // true
$user->hasPrivilege('posts.delete');  // true
```

### Suspend Users

```php
use Carbon\Carbon;

// Suspend user for 7 days
$user->suspend(
    reason: 'Violation of terms',
    until: Carbon::now()->addDays(7)
);

// Check if suspended
if ($user->isSuspended()) {
    // User is suspended
}

// Unsuspend user
$user->unsuspend();
```

### Audit Logging

```php
use Marufsharia\Hyro\Core\Facades\Hyro;

// Log an action
Hyro::audit()
    ->log('user.login')
    ->on($user)
    ->withProperties(['ip' => request()->ip()])
    ->save();

// Query audit logs
$logs = Hyro::audit()
    ->forUser($user)
    ->forAction('user.login')
    ->inDateRange($startDate, $endDate)
    ->get();
```

---

## 🔧 CLI Commands

### User Management
```bash
php artisan hyro:user:create              # Create new user
php artisan hyro:user:create --admin      # Create admin user
php artisan hyro:user:list                # List all users
php artisan hyro:user:suspend             # Suspend user
php artisan hyro:user:unsuspend           # Unsuspend user
```

### Role Management
```bash
php artisan hyro:role:create              # Create new role
php artisan hyro:role:list                # List all roles
php artisan hyro:role:assign              # Assign role to user
php artisan hyro:role:revoke              # Revoke role from user
```

### Privilege Management
```bash
php artisan hyro:privilege:create         # Create privilege
php artisan hyro:privilege:list           # List privileges
php artisan hyro:privilege:grant          # Grant privilege to role
php artisan hyro:privilege:revoke         # Revoke privilege from role
```

### CRUD Generator
```bash
php artisan hyro:make-crud                # Generate CRUD components
php artisan hyro:discover-routes          # Auto-discover routes
```

### Plugin Management
```bash
php artisan hyro:plugin:list              # List installed plugins
php artisan hyro:plugin:make              # Create new plugin
php artisan hyro:plugin:install           # Install plugin
php artisan hyro:plugin:activate          # Activate plugin
php artisan hyro:plugin:deactivate        # Deactivate plugin
```

### Emergency Access
```bash
php artisan hyro:emergency:create-admin   # Create emergency admin
php artisan hyro:emergency:grant-access   # Grant emergency access
```

---

## 🔌 API Documentation

### Authentication Endpoints

```bash
POST   /api/hyro/auth/login       # Login and get token
POST   /api/hyro/auth/register    # Register new user
POST   /api/hyro/auth/logout      # Logout and revoke token
POST   /api/hyro/auth/refresh     # Refresh token
GET    /api/hyro/auth/user        # Get authenticated user
```

### User Management Endpoints

```bash
GET    /api/hyro/users            # List all users
POST   /api/hyro/users            # Create new user
GET    /api/hyro/users/{id}       # Get user details
PUT    /api/hyro/users/{id}       # Update user
DELETE /api/hyro/users/{id}       # Delete user
```

### Example API Usage

```bash
# Login
curl -X POST http://your-app.com/api/hyro/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Response
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}

# Use token for authenticated requests
curl -X GET http://your-app.com/api/hyro/users \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Accept: application/json"
```

---

## 🛡️ Security

### Security Features

✅ **Fail-Closed Authorization** - Deny by default  
✅ **Protected Roles** - Prevent deletion of critical roles  
✅ **Comprehensive Audit Logging** - Complete audit trail  
✅ **Sensitive Data Sanitization** - Automatic password/token redaction  
✅ **Rate Limiting** - API rate limiting  
✅ **Token Management** - Sanctum integration with auto-sync  
✅ **Suspension System** - Temporal access control  
✅ **CSRF Protection** - Laravel CSRF protection  
✅ **SQL Injection Prevention** - Eloquent ORM  
✅ **XSS Prevention** - Blade templating

### Reporting Security Issues

Please report security vulnerabilities to: **marufsharia@gmail.com**

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

Hyro is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Maruf Sharia**
- Email: marufsharia@gmail.com
- GitHub: [@marufsharia](https://github.com/marufsharia)

---

## 🙏 Acknowledgments

- Laravel Framework
- Livewire
- Tailwind CSS
- Alpine.js
- All contributors and supporters

---

<div align="center">

**Made with ❤️ by Maruf Sharia**

[⬆ Back to Top](#️-hyro)

</div>
