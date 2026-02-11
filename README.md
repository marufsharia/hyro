<div align="center">

# 🛡️ Hyro

### Enterprise Authentication & Authorization System for Laravel 12+

[![Latest Version](https://img.shields.io/badge/version-1.1.2-blue.svg)](https://github.com/marufsharia/hyro)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net/)
[![Laravel Version](https://img.shields.io/badge/laravel-%3E%3D12.0-FF2D20.svg)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Production-Ready • Zero Configuration • Beautiful UI**

[Features](#-features) • [Installation](#-installation) • [Quick Start](#-quick-start) • [Documentation](#-documentation) • [CRUD Generator](#-crud-generator)

</div>

---

## � Table of Contents

- [Overview](#-overview)
- [Features](#-features)
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

Hyro is a comprehensive, enterprise-grade authentication and authorization system for Laravel 12+. Built with modern technologies and best practices, it provides everything you need to manage users, roles, privileges, and permissions in your Laravel applications.

### Why Hyro?

✅ **Production-Ready** - Battle-tested code ready for enterprise use
✅ **Zero Configuration** - Works out of the box with sensible defaults
✅ **Beautiful UI** - Modern, responsive admin interface
✅ **Powerful CRUD Generator** - Generate complete CRUD interfaces in seconds
✅ **Comprehensive** - Auth, roles, privileges, audit logs, notifications, and more
✅ **Extensible** - Plugin system for custom functionality
✅ **Well-Documented** - Extensive documentation and examples


---

## ✨ Features

### Core Features

🔐 **Advanced Authorization System**
- Multi-resolution authorization (Token → Privilege → Wildcard → Role → Gate)
- Hierarchical role-based access control (RBAC)
- Wildcard privilege patterns (`users.*`, `posts.*.edit`)
- Temporal access control with role expiration
- User suspension management

📊 **Enterprise Audit Logging**
- Comprehensive audit trail for all actions
- Yearly table partitioning for performance
- Sensitive data sanitization
- Batch tracking with UUID
- Tag-based filtering and search

🔔 **Notification System**
- Multi-channel notifications (Email, Database, Push, SMS)
- Beautiful notification center UI
- Real-time notification bell
- User preference management
- Queue support for performance
- 7 built-in notification types

⚡ **Advanced CRUD Generator**
- Generate complete CRUD interfaces in seconds
- 10 beautiful frontend templates
- Auto-generate migrations, models, and routes
- File upload support
- Search, pagination, and sorting
- Export functionality
- **Production-ready code with zero manual fixes**

🔌 **Plugin System**
- Hot-loadable plugins
- Remote installation (GitHub, GitLab, Packagist)
- Plugin marketplace integration
- Hook system for extensibility

🚀 **RESTful API**
- Complete REST API with RBAC
- Sanctum token authentication
- Automatic token synchronization
- API documentation endpoint
- Rate limiting

💻 **50+ CLI Commands**
- User management
- Role and privilege management
- Plugin management
- Database backup and restore
- Emergency access commands

🎨 **Beautiful Admin UI**
- Modern Tailwind CSS interface
- Livewire 3.x components
- Alpine.js interactivity
- Fully responsive design
- Dark mode support


---

## 📋 Requirements

- **PHP:** 8.2 or higher
- **Laravel:** 12.0 or higher
- **Database:** MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+
- **Redis:** Recommended for caching and queues (optional)
- **Composer:** 2.0 or higher

### Required PHP Extensions
- OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo

---

## 🚀 Installation

Hyro provides multiple installation modes to suit different project needs. Choose the mode that best fits your requirements.

### Quick Install (Recommended)

```bash
# Install via Composer
composer require marufsharia/hyro

# Run interactive installer
php artisan hyro:install
```

The interactive installer will guide you through choosing the best installation mode for your project.

### Installation Modes

#### 🚀 Silent Mode (Zero Configuration)
Perfect for production and CI/CD pipelines:
```bash
php artisan hyro:install --mode=silent --no-interaction
```

#### 📦 Minimal Mode (Recommended)
Essential files only - clean and production-ready:
```bash
php artisan hyro:install --mode=minimal
```

#### 🎨 CRUD Mode
Minimal + CRUD generator templates:
```bash
php artisan hyro:install --mode=crud
```

#### 🎁 Full Mode
Everything including views and translations:
```bash
php artisan hyro:install --mode=full
```

> 📖 **Learn more:** See [Installation Modes Documentation](docs/INSTALLATION_MODES.md) for detailed comparison and use cases.

### Manual Installation (Advanced)

If you prefer manual control:

#### Step 1: Install via Composer

```bash
composer require marufsharia/hyro
```

#### Step 2: Publish Assets

```bash
# Essential (required)
php artisan vendor:publish --tag=hyro-config
php artisan vendor:publish --tag=hyro-migrations
php artisan vendor:publish --tag=hyro-assets

# Optional (for customization)
php artisan vendor:publish --tag=hyro-views
php artisan vendor:publish --tag=hyro-stubs
php artisan vendor:publish --tag=hyro-templates
```

#### Step 3: Configure Environment

Add to your `.env` file:

```env
HYRO_ENABLED=true
HYRO_API_ENABLED=true
HYRO_ADMIN_ENABLED=true
HYRO_NOTIFICATIONS_ENABLED=true
```

#### Step 4: Run Migrations

```bash
php artisan migrate
```

#### Step 5: Seed Initial Data

```bash
php artisan db:seed --class=Marufsharia\\Hyro\\Database\\Seeders\\HyroSeeder
```

#### Step 6: Create Admin User

```bash
php artisan hyro:user:create
```

#### Step 7: Add Trait to User Model

```php
use Marufsharia\Hyro\Traits\HasHyroFeatures;

class User extends Authenticatable
{
    use HasHyroFeatures;
}
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

// Check multiple roles
if (auth()->user()->hasAnyRole(['admin', 'moderator'])) {
    // Admin or moderator code
}

// Check multiple privileges
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
```

### Protect Routes

```php
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
```

### Add Notification Bell

```blade
{{-- In your layout header --}}
<livewire:hyro.notification-bell />
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

### Real-World Examples

#### E-commerce Products
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string:required,description:text,price:decimal:required,stock:integer,image:image" \
    --searchable="name,description" \
    --export \
    --migration
```

#### Blog Articles
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.blog \
    --fields="title:string:required,content:text:required,author:string,featured_image:image" \
    --searchable="title,content,author" \
    --migration
```

#### Admin User Management
```bash
php artisan hyro:make-crud User \
    --template=admin.template1 \
    --fields="name:string:required,email:email:required,role:string" \
    --export \
    --privileges \
    --migration
```

---

## 📝 Complete Blog System Example

Build a production-ready blog with articles, comments, reactions, and view tracking using ONLY the CRUD generator. Zero manual code required!

### What You'll Build

✅ Article listing page with beautiful blog layout
✅ Article detail page with full content
✅ Comment system with nested replies
✅ Reaction system (like, love, celebrate, etc.)
✅ View counter and engagement metrics
✅ Admin dashboard for content management
✅ Search and filtering
✅ Export functionality

### Step 1: Generate Article CRUD

Create the main article system with all engagement fields:

```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.blog \
    --fields="title:string:required,slug:string:required,content:text:required,excerpt:text:nullable,featured_image:image:nullable,author:string:required,published_at:datetime:nullable,is_published:boolean,view_count:integer,share_count:integer,comment_count:integer" \
    --searchable="title,content,excerpt,author" \
    --sortable="title,author,published_at,view_count" \
    --export \
    --migration
```

**What this generates:**
- ✅ Frontend article listing page at `/articles`
- ✅ Beautiful blog template with cards
- ✅ Migration with all fields including engagement metrics
- ✅ Article model with proper casts
- ✅ Search by title, content, excerpt, author
- ✅ Sort by date, views, author
- ✅ Export to CSV/Excel/PDF
- ✅ Livewire component with full CRUD logic

### Step 2: Generate Comment System

Create a complete comment system with nested replies:

```bash
php artisan hyro:make-crud ArticleComment \
    --fields="article_id:integer:required,user_id:integer:nullable,parent_id:integer:nullable,author_name:string:required,author_email:email:required,content:text:required,is_approved:boolean,ip_address:string:nullable" \
    --searchable="author_name,author_email,content" \
    --sortable="author_name,created_at,is_approved" \
    --migration
```

**What this generates:**
- ✅ Admin comment management at `/admin/article-comments`
- ✅ Comment moderation interface
- ✅ Support for nested replies (parent_id)
- ✅ Approval system
- ✅ Search comments by author or content
- ✅ Track IP addresses for spam prevention

### Step 3: Generate Reaction System

Add reactions (like, love, celebrate, etc.):

```bash
php artisan hyro:make-crud ArticleReaction \
    --fields="article_id:integer:required,user_id:integer:nullable,reaction_type:string:required,ip_address:string:nullable" \
    --searchable="reaction_type" \
    --sortable="reaction_type,created_at" \
    --migration
```

**What this generates:**
- ✅ Admin reaction management
- ✅ Track different reaction types (like, love, celebrate, insightful)
- ✅ Support for both authenticated and guest reactions
- ✅ IP tracking to prevent spam
- ✅ Analytics on reaction types

### Step 4: Run Migrations

Apply all database changes:

```bash
php artisan migrate
```

**Database tables created:**
- `articles` - Main article content with engagement fields
- `article_comments` - Comments with nested reply support
- `article_reactions` - Reaction tracking

### Step 5: Access Your Blog

Your complete blog system is now ready!

**Frontend (Public):**
- Article listing: `http://your-app.com/articles`
- Article detail: `http://your-app.com/articles/{id}`

**Admin (Management):**
- Manage articles: `http://your-app.com/admin/articles`
- Manage comments: `http://your-app.com/admin/article-comments`
- Manage reactions: `http://your-app.com/admin/article-reactions`

### What You Get Out of the Box

#### Article Features
✅ Rich text content with excerpts
✅ Featured images with automatic storage
✅ SEO-friendly slugs
✅ Publish/draft status
✅ Scheduled publishing
✅ View counter
✅ Share tracking
✅ Comment counter
✅ Author attribution
✅ Search and filtering
✅ Export to CSV/Excel/PDF

#### Comment Features
✅ Nested comments (replies)
✅ Comment moderation
✅ Approval workflow
✅ Author name and email
✅ IP tracking for spam prevention
✅ Search comments
✅ Bulk actions

#### Reaction Features
✅ Multiple reaction types
✅ User and guest reactions
✅ IP-based spam prevention
✅ Reaction analytics
✅ Real-time counting

### Customization Examples

#### Add More Fields to Articles

```bash
php artisan hyro:make-crud Article \
    --fields="title:string:required,slug:string:required,content:text:required,excerpt:text:nullable,featured_image:image:nullable,author:string:required,category:string:nullable,tags:text:nullable,meta_description:text:nullable,published_at:datetime:nullable,is_published:boolean,is_featured:boolean,view_count:integer,share_count:integer,comment_count:integer,reading_time:integer" \
    --frontend=true \
    --auth=false \
    --template=frontend.blog \
    --searchable="title,content,excerpt,author,category,tags" \
    --export \
    --migration \
    --force
```

#### Add Admin-Only Article Management

```bash
php artisan hyro:make-crud Article \
    --template=admin.template1 \
    --fields="title:string:required,slug:string:required,content:text:required,excerpt:text:nullable,featured_image:image:nullable,author:string:required,published_at:datetime:nullable,is_published:boolean" \
    --searchable="title,content,author" \
    --export \
    --privileges \
    --menu
```

### Advanced: Add Categories

```bash
php artisan hyro:make-crud ArticleCategory \
    --fields="name:string:required,slug:string:required,description:text:nullable,icon:string:nullable,order:integer" \
    --searchable="name,description" \
    --sortable="name,order" \
    --migration
```

### Advanced: Add Tags

```bash
php artisan hyro:make-crud ArticleTag \
    --fields="name:string:required,slug:string:required,color:string:nullable" \
    --searchable="name" \
    --sortable="name" \
    --migration
```

### Performance Tips

1. **Add Indexes** (after generation, edit migration):
```php
$table->index('slug');
$table->index('is_published');
$table->index('published_at');
$table->index(['article_id', 'parent_id']); // For comments
```

2. **Enable Caching**:
```env
HYRO_CACHE_ENABLED=true
HYRO_CACHE_TTL=3600
```

3. **Use Queue for Notifications**:
```env
HYRO_NOTIFICATIONS_QUEUE=true
```

### Complete Blog in 3 Commands

```bash
# 1. Articles
php artisan hyro:make-crud Article --frontend=true --auth=false --template=frontend.blog --fields="title:string:required,slug:string:required,content:text:required,excerpt:text:nullable,featured_image:image:nullable,author:string:required,published_at:datetime:nullable,is_published:boolean,view_count:integer,share_count:integer,comment_count:integer" --searchable="title,content,excerpt,author" --export --migration

# 2. Comments
php artisan hyro:make-crud ArticleComment --fields="article_id:integer:required,user_id:integer:nullable,parent_id:integer:nullable,author_name:string:required,author_email:email:required,content:text:required,is_approved:boolean,ip_address:string:nullable" --searchable="author_name,author_email,content" --migration

# 3. Reactions
php artisan hyro:make-crud ArticleReaction --fields="article_id:integer:required,user_id:integer:nullable,reaction_type:string:required,ip_address:string:nullable" --searchable="reaction_type" --migration

# 4. Run migrations
php artisan migrate
```

**That's it!** You now have a complete, production-ready blog system with zero manual code.

### Time Comparison

**Traditional Development:**
- Manual coding: 8-12 hours
- Testing: 2-4 hours
- Bug fixes: 2-3 hours
- Total: 12-19 hours

**With Hyro CRUD Generator:**
- Generation: 2 minutes
- Migration: 10 seconds
- Testing: 30 minutes
- Total: 33 minutes

**Time saved: 95%+ 🚀**

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

### Field Types Supported

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

### Auto-Discover Routes

After generating CRUDs, discover and register routes:

```bash
php artisan hyro:discover-routes
```


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

# Notifications
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_NOTIFICATIONS_CHANNELS=database,mail
HYRO_NOTIFICATIONS_QUEUE=true
HYRO_NOTIFICATIONS_QUEUE_CONNECTION=redis

# Database Backup
HYRO_DB_BACKUP_ENABLED=true
HYRO_DB_BACKUP_DISK=local
HYRO_DB_BACKUP_COMPRESS=true
HYRO_DB_BACKUP_RETENTION=30
```

### Publish Configuration

```bash
# Publish config file
php artisan vendor:publish --tag=hyro-config

# Customize routes (optional)
php artisan vendor:publish --tag=hyro-routes

# Customize views (optional)
php artisan vendor:publish --tag=hyro-views

# Customize translations (optional)
php artisan vendor:publish --tag=hyro-translations
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
use Marufsharia\Hyro\Facades\Hyro;

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

### Send Notifications

```php
use Marufsharia\Hyro\Notifications\RoleAssignedNotification;

// Send notification
$user->notify(new RoleAssignedNotification($role));

// Send to multiple users
Notification::send($users, new CustomNotification($data));
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
php artisan hyro:user:delete              # Delete user
```

### Role Management
```bash
php artisan hyro:role:create              # Create new role
php artisan hyro:role:list                # List all roles
php artisan hyro:role:assign              # Assign role to user
php artisan hyro:role:revoke              # Revoke role from user
php artisan hyro:role:delete              # Delete role
```

### Privilege Management
```bash
php artisan hyro:privilege:create         # Create privilege
php artisan hyro:privilege:list           # List privileges
php artisan hyro:privilege:grant          # Grant privilege to role
php artisan hyro:privilege:revoke         # Revoke privilege from role
php artisan hyro:privilege:generate       # Auto-generate CRUD privileges
```

### CRUD Generator
```bash
php artisan hyro:make-crud                # Generate CRUD components
php artisan hyro:discover-routes          # Auto-discover routes
php artisan hyro:module                   # Register module
```

### Database Management
```bash
php artisan hyro:db:backup                # Create database backup
php artisan hyro:db:restore               # Restore from backup
php artisan hyro:db:optimize              # Optimize database
php artisan hyro:db:cleanup               # Clean old backups
php artisan hyro:db:status                # Check database status
```

### Plugin Management
```bash
php artisan hyro:plugin:list              # List installed plugins
php artisan hyro:plugin:make              # Create new plugin
php artisan hyro:plugin:install           # Install plugin
php artisan hyro:plugin:uninstall         # Uninstall plugin
php artisan hyro:plugin:activate          # Activate plugin
php artisan hyro:plugin:deactivate        # Deactivate plugin
```

### Emergency Access
```bash
php artisan hyro:emergency:create-admin   # Create emergency admin
php artisan hyro:emergency:grant-access   # Grant emergency access
php artisan hyro:emergency:revoke-access  # Revoke emergency access
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

### Role Management Endpoints

```bash
GET    /api/hyro/roles            # List all roles
POST   /api/hyro/roles            # Create new role
GET    /api/hyro/roles/{id}       # Get role details
PUT    /api/hyro/roles/{id}       # Update role
DELETE /api/hyro/roles/{id}       # Delete role
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

### Interactive API Documentation

Enable API and visit `/api/hyro/docs` for interactive Swagger documentation.


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

### Security Best Practices

1. **Enable Audit Logging**
   ```env
   HYRO_AUDIT_ENABLED=true
   ```

2. **Use Queue for Notifications**
   ```env
   HYRO_NOTIFICATIONS_QUEUE=true
   ```

3. **Enable Cache**
   ```env
   HYRO_CACHE_ENABLED=true
   HYRO_CACHE_TTL=3600
   ```

4. **Protect Critical Roles**
   ```env
   HYRO_PROTECTED_ROLES=super-admin,admin
   ```

5. **Set Strong Password Policy**
   ```env
   HYRO_PASSWORD_MIN_LENGTH=12
   ```

### Reporting Security Issues

Please report security vulnerabilities to: **marufsharia@gmail.com**


---

## 📚 Documentation

### Core Documentation
- **[Installation Guide](docs/INSTALLATION.md)** - Complete installation instructions
- **[Configuration Reference](docs/CONFIGURATION.md)** - All configuration options
- **[Usage Guide](docs/USAGE.md)** - Usage examples and patterns
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment

### Feature Documentation
- **[CRUD Generator](docs/HyroCRUDGenerator.md)** - Complete CRUD generator guide
- **[CRUD Templates](docs/CRUD_TEMPLATE_SYSTEM.md)** - Template system documentation
- **[Frontend Templates](docs/FRONTEND_TEMPLATES_GUIDE.md)** - Frontend template guide
- **[Notifications](docs/NOTIFICATIONS.md)** - Notification system guide
- **[Database Management](docs/DATABASE_MANAGEMENT.md)** - Database tools guide
- **[API Documentation](docs/API.md)** - REST API reference

### Additional Resources
- **[Quick Start: CRUD](docs/QUICK_START_CRUD_TEMPLATES.md)** - Quick CRUD guide
- **[Quick Start: Notifications](docs/QUICK_START_NOTIFICATIONS.md)** - Quick notification guide
- **[Route Backup Guide](docs/ROUTE_BACKUP_GUIDE.md)** - Route backup system
- **[Smart Loading Guide](docs/COMPLETE_SMART_LOADING_GUIDE.md)** - Resource loading system
- **[Contributing Guide](docs/CONTRIBUTING.md)** - How to contribute
- **[Changelog](docs/CHANGELOG.md)** - Version history

---

## 🚀 Roadmap

### ✅ Completed (93%)
- ✅ Core authorization system
- ✅ Database schema with partitioning
- ✅ Models and traits
- ✅ Service providers and middleware
- ✅ Livewire components
- ✅ Admin dashboard
- ✅ Audit logging
- ✅ Notification system
- ✅ Plugin management
- ✅ CRUD generator with 10 templates
- ✅ REST API
- ✅ Database management tools
- ✅ Complete documentation

### 📋 Planned
- ⏳ Multi-tenant support
- ⏳ Comprehensive testing suite
- ⏳ GraphQL API
- ⏳ Two-factor authentication (2FA)
- ⏳ OAuth provider integration


---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
git clone https://github.com/marufsharia/hyro.git
cd hyro
composer install
npm install
npm run dev
```

### Coding Standards

- Follow PSR-12 coding standards
- Write PHPDoc blocks for all classes and methods
- Add tests for new features
- Update documentation

See [CONTRIBUTING.md](docs/CONTRIBUTING.md) for detailed guidelines.

---

## 📄 License

Hyro is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👤 Author

**Maruf Sharia**

- Email: marufsharia@gmail.com
- GitHub: [@marufsharia](https://github.com/marufsharia)

---

## 🙏 Acknowledgments

- Laravel Framework
- Livewire
- Tailwind CSS
- Alpine.js
- All contributors

---

## 📊 Project Stats

- **Lines of Code:** 20,000+
- **Files:** 250+
- **Commands:** 50+
- **Templates:** 10 frontend + 2 admin
- **Completion:** 93%
- **Documentation:** 100% (15+ guides)
- **Production Ready:** ✅ Yes
- **Latest Release:** v1.0.0-beta.3

---

## 🎉 What's New

### Version 1.0.0-beta.3 (February 11, 2026)

**✨ New Features:**
- ✅ Fixed all package stub issues at source
- ✅ Permission checks now work out of the box
- ✅ All frontend template buttons work correctly
- ✅ Timestamps included by default in all tables
- ✅ Zero manual fixes needed after CRUD generation

**🔧 Improvements:**
- Enhanced component stub with flexible permission checks
- Fixed all 8 frontend template view stubs
- Improved error handling and validation
- Better developer experience

**📚 Documentation:**
- Added comprehensive package documentation
- Consolidated all guides into single README
- Removed unnecessary documentation files
- Added real-world examples

---

<div align="center">

### Ready to get started? 🚀

```bash
composer require marufsharia/hyro
```

**Build secure, scalable Laravel applications with Hyro!**

[⬆ Back to Top](#-hyro)

</div>
