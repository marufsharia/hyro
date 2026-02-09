# Hyro CRUD Template System

Complete guide to the Hyro CRUD template system for generating customizable CRUD interfaces.

## Table of Contents

1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [Available Templates](#available-templates)
4. [Command Options](#command-options)
5. [Template Types](#template-types)
6. [Route Management](#route-management)
7. [Customization](#customization)
8. [Examples](#examples)
9. [Best Practices](#best-practices)

## Overview

The Hyro CRUD Template System allows you to generate different UI styles for your CRUD interfaces. Choose from pre-built templates or create your own custom designs.

### Key Features

- **Multiple Templates**: Admin and frontend templates with different layouts
- **Smart Route Loading**: Automatic route registration with conflict detection
- **Authentication Control**: Optional authentication for frontend routes
- **Ownership Checks**: Built-in user ownership validation for frontend
- **Customizable**: Publish and modify templates to match your design

## Quick Start

### Generate Admin CRUD (Default)

```bash
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal,description:text" \
    --migration
```

### Generate Frontend CRUD

```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.template1 \
    --fields="name:string,price:decimal,image:image"
```

### Generate Public Frontend (No Auth)

```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.template2 \
    --fields="title:string,content:text"
```

## Available Templates

### Admin Templates

#### admin.template1 (Default)
**Full-Featured Admin Interface**

- Gradient backgrounds and modern design
- Comprehensive search and filters
- Bulk actions support
- Export/import functionality
- Advanced table with sorting
- Modal-based forms

**Best for**: Complete admin dashboards, complex data management

**Preview**:
```
┌─────────────────────────────────────────┐
│  Products                    [+ Create] │
├─────────────────────────────────────────┤
│  [Search...] [Filters] [10 per page]   │
├─────────────────────────────────────────┤
│  ☐  Name      Price    Actions          │
│  ☐  Product 1 $99.99   [Edit] [Delete] │
│  ☐  Product 2 $149.99  [Edit] [Delete] │
└─────────────────────────────────────────┘
```

#### admin.template2
**Compact Admin Interface**

- Minimal, space-efficient design
- Simple search bar
- Essential CRUD operations
- Compact table layout
- Quick actions

**Best for**: Simple admin panels, data entry, internal tools

**Preview**:
```
┌────────────────────────────────┐
│  Products        [+ Create]    │
│  [Search...] [10]              │
├────────────────────────────────┤
│  ☐ Name     Price    Actions   │
│  ☐ Item 1   $99.99   [E] [D]  │
└────────────────────────────────┘
```

### Frontend Templates

#### frontend.template1
**Card-Based Layout**

- Responsive grid of cards
- Image support
- User ownership checks
- Public browsing
- Modern card design

**Best for**: Product catalogs, portfolios, galleries, e-commerce

**Preview**:
```
┌──────────┐ ┌──────────┐ ┌──────────┐
│  Image   │ │  Image   │ │  Image   │
│  Title   │ │  Title   │ │  Title   │
│  Desc... │ │  Desc... │ │  Desc... │
│ [View]   │ │ [View]   │ │ [View]   │
└──────────┘ └──────────┘ └──────────┘
```

#### frontend.template2
**List-Based Layout**

- Clean vertical list
- Inline actions
- Minimal design
- Quick scanning
- Efficient space usage

**Best for**: Blogs, articles, news feeds, simple listings

**Preview**:
```
┌─────────────────────────────────────┐
│  Article Title                 [👁️📝🗑️] │
│  Brief description of article...   │
│  Jan 15, 2026 • by Author         │
├─────────────────────────────────────┤
│  Another Article              [👁️📝🗑️] │
│  Another description...            │
└─────────────────────────────────────┘
```

## Command Options

### Full Command Signature

```bash
php artisan hyro:make-crud {name}
    {--model=}              # Model class (default: App\Models\{Name})
    {--fields=}             # Field definitions (required)
    {--searchable=}         # Searchable fields
    {--sortable=}           # Sortable fields
    {--filterable=}         # Filterable fields
    {--relations=}          # Relationships
    {--soft-deletes}        # Enable soft deletes
    {--timestamps}          # Enable timestamps (default: true)
    {--export}              # Enable export
    {--import}              # Enable import
    {--audit}               # Enable audit logging
    {--privileges}          # Auto-create privileges
    {--migration}           # Generate migration
    {--route}               # Auto-register route (default: true)
    {--menu}                # Add to sidebar menu
    {--module}              # Register as module
    {--frontend=false}      # Generate frontend route
    {--auth=true}           # Require authentication
    {--template=admin.template1}  # Template to use
    {--force}               # Overwrite existing files
```

### Template Options

#### --frontend
Controls whether to generate admin or frontend route.

```bash
# Admin route (default)
--frontend=false

# Frontend route
--frontend=true
```

**Behavior**:
- `false`: Route under `admin/hyro` prefix with admin middleware
- `true`: Route at root level with optional auth

#### --auth
Controls authentication requirement.

```bash
# Require authentication (default)
--auth=true

# Public access
--auth=false
```

**Behavior**:
- `true`: Adds `auth` middleware
- `false`: No auth middleware (public access)

#### --template
Specifies which template to use.

```bash
# Default admin template
--template=admin.template1

# Compact admin template
--template=admin.template2

# Card-based frontend
--template=frontend.template1

# List-based frontend
--template=frontend.template2
```

**Format**: `TYPE.NAME`
- **TYPE**: `admin` or `frontend`
- **NAME**: `template1`, `template2`, or custom

## Template Types

### Admin Templates

**Purpose**: Backend management interfaces for administrators

**Features**:
- Full privilege checking
- Comprehensive CRUD operations
- Advanced filtering and search
- Bulk actions
- Export/import capabilities

**Route Configuration**:
- Prefix: `admin/hyro`
- Middleware: `['web', 'auth']`
- Name: `hyro.admin.{resource}`

**Permission Checks**:
```php
protected function canUpdate($record): bool
{
    return auth()->user()->hasPrivilege('product.update');
}
```

### Frontend Templates

**Purpose**: Public-facing or user-specific interfaces

**Features**:
- User ownership validation
- Public browsing support
- Simplified UI
- Mobile-responsive
- Optional authentication

**Route Configuration**:
- Prefix: None (root level)
- Middleware: `['web']` or `['web', 'auth']`
- Name: `frontend.{resource}`

**Permission Checks**:
```php
protected function canUpdate($record): bool
{
    // Users can only edit their own records
    return auth()->check() && auth()->id() === $record->user_id;
}
```

## Route Management

### Route File Location

All CRUD routes are registered in:
```
routes/hyro/crud.php
```

This file is auto-created on first CRUD generation.

### Admin Routes

```php
Route::prefix(config('hyro.admin.route.prefix', 'admin/hyro'))
    ->middleware(config('hyro.admin.route.middleware', ['web', 'auth']))
    ->name('hyro.admin.')
    ->group(function () {
        // products CRUD [Admin] // Permission: product
        Route::get('products', App\Livewire\Admin\ProductManager::class)
            ->name('products');
    });
```

**Access**: `https://yourapp.com/admin/hyro/products`

### Frontend Routes

```php
Route::middleware(['web'])
    ->name('frontend.')
    ->group(function () {
        // products CRUD [Frontend] // Permission: product
        Route::get('products', App\Livewire\Admin\ProductManager::class)
            ->name('products')->middleware(['auth']);
    });
```

**Access**: `https://yourapp.com/products`

### Route Conflict Detection

For frontend routes, the system automatically detects path conflicts:

```bash
# First CRUD
php artisan hyro:make-crud Product --frontend=true
# Route: /products

# Conflict detected, auto-renamed
php artisan hyro:make-crud Product --frontend=true
# Route: /products-1
```

## Customization

### Publishing Templates

Publish templates to customize them:

```bash
php artisan vendor:publish --tag=hyro-stubs
```

Templates will be copied to:
```
resources/stubs/hyro/templates/
├── admin/
│   ├── template1/
│   └── template2/
└── frontend/
    ├── template1/
    └── template2/
```

### Creating Custom Templates

1. **Create Template Directory**:
```bash
mkdir -p resources/stubs/hyro/templates/admin/template3
```

2. **Add Template Files**:
```
resources/stubs/hyro/templates/admin/template3/
├── component.stub
└── view.stub
```

3. **Use Custom Template**:
```bash
php artisan hyro:make-crud Product --template=admin.template3
```

### Template Priority

The system loads templates in this order:

1. **Published Template** (highest priority)
   ```
   resources/stubs/hyro/templates/{type}/{name}/{stub}.stub
   ```

2. **Package Template**
   ```
   packages/marufsharia/hyro/src/stubs/templates/{type}/{name}/{stub}.stub
   ```

3. **Default Stub** (fallback)
   ```
   packages/marufsharia/hyro/src/stubs/crud/{stub}.stub
   ```

## Examples

### Example 1: E-commerce Product Catalog

**Requirements**:
- Public browsing
- Card-based layout
- Image support
- User can manage their products

```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --auth=true \
    --template=frontend.template1 \
    --fields="name:string,description:text,price:decimal,image:image,user_id:integer" \
    --searchable="name,description" \
    --sortable="name,price,created_at" \
    --migration
```

**Result**:
- Route: `/products`
- Template: Card grid with images
- Auth: Required for create/edit/delete
- Public: Can browse without auth

### Example 2: Blog Articles

**Requirements**:
- Public reading
- List layout
- No authentication needed
- Simple interface

```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.template2 \
    --fields="title:string,content:text,author:string,published_at:datetime" \
    --searchable="title,content,author" \
    --sortable="title,published_at" \
    --migration
```

**Result**:
- Route: `/articles`
- Template: Clean list view
- Auth: None (public access)
- Layout: Vertical list

### Example 3: Admin Product Management

**Requirements**:
- Full admin features
- Export capability
- Bulk actions
- Advanced filters

```bash
php artisan hyro:make-crud Product \
    --template=admin.template1 \
    --fields="name:string,sku:string,price:decimal,stock:integer,category_id:integer" \
    --searchable="name,sku" \
    --sortable="name,price,stock,created_at" \
    --filterable="category_id" \
    --export \
    --privileges \
    --migration \
    --menu
```

**Result**:
- Route: `/admin/hyro/products`
- Template: Full-featured admin
- Features: Export, bulk actions, filters
- Menu: Added to sidebar

### Example 4: Simple Data Entry

**Requirements**:
- Minimal interface
- Quick data entry
- Admin only
- No extra features

```bash
php artisan hyro:make-crud Category \
    --template=admin.template2 \
    --fields="name:string,slug:string,description:text" \
    --searchable="name,slug" \
    --migration
```

**Result**:
- Route: `/admin/hyro/categories`
- Template: Compact admin
- Features: Basic CRUD only
- Layout: Space-efficient

## Best Practices

### 1. Choose the Right Template

**Admin Templates**:
- Use for backend management
- Always require authentication
- Include privilege checks
- Full feature set

**Frontend Templates**:
- Use for public-facing content
- Optional authentication
- User ownership checks
- Simplified UI

### 2. Authentication Strategy

**Public Content** (`--auth=false`):
```bash
# Blog, news, documentation
php artisan hyro:make-crud Article --frontend=true --auth=false
```

**User Content** (`--auth=true`):
```bash
# User profiles, dashboards, personal data
php artisan hyro:make-crud Profile --frontend=true --auth=true
```

**Admin Content** (always auth):
```bash
# Admin management
php artisan hyro:make-crud User --template=admin.template1
```

### 3. Field Selection

**Searchable Fields**:
- Text fields users will search
- Names, titles, descriptions
- SKUs, codes, identifiers

**Sortable Fields**:
- Numeric fields (price, quantity)
- Dates (created_at, updated_at)
- Status fields

**Filterable Fields**:
- Categories, tags
- Status enums
- Boolean flags

### 4. Template Customization

**When to Customize**:
- Brand-specific styling needed
- Unique layout requirements
- Special functionality

**How to Customize**:
1. Publish templates first
2. Modify published versions
3. Keep originals as backup
4. Test thoroughly

### 5. Route Organization

**Admin Routes**:
- Keep under `/admin/hyro` prefix
- Use consistent naming
- Group related resources

**Frontend Routes**:
- Use descriptive paths
- Avoid conflicts
- Consider SEO-friendly URLs

### 6. Ownership Checks

For frontend templates, ensure your model has:

```php
// Migration
$table->foreignId('user_id')->constrained()->onDelete('cascade');

// Model
protected $fillable = ['user_id', ...];

public function user()
{
    return $this->belongsTo(User::class);
}
```

### 7. Testing

After generation:

1. **Check Routes**:
```bash
php artisan route:list | grep products
```

2. **Test Permissions**:
- Try accessing as different users
- Verify ownership checks work
- Test auth requirements

3. **Verify UI**:
- Check responsive design
- Test all CRUD operations
- Validate form submissions

## Troubleshooting

### Template Not Found

**Error**: "Template admin.template3 not found, using default stub"

**Solution**:
1. Check template name format: `TYPE.NAME`
2. Verify files exist in correct location
3. System falls back to default automatically

### Route Already Exists

**Error**: "Route already exists or could not be registered"

**Solution**:
1. Check `routes/hyro/crud.php`
2. Remove duplicate route
3. Or use `--force` to overwrite

### Permission Denied

**Error**: User cannot access CRUD interface

**Solution**:
1. Check middleware configuration
2. Verify user has required privileges
3. For frontend: Check ownership logic

### Path Conflict

**Warning**: Frontend route path taken

**Solution**:
- System auto-renames: `products` → `products-1`
- Or specify custom path in route manager
- Check existing routes first

## Advanced Usage

### Custom Component Namespace

```bash
php artisan hyro:make-crud Product \
    --model="App\Models\Catalog\Product"
```

### Multiple Relations

```bash
php artisan hyro:make-crud Product \
    --relations="category,tags,reviews"
```

### Soft Deletes with Audit

```bash
php artisan hyro:make-crud Product \
    --soft-deletes \
    --audit \
    --timestamps
```

### Export with Import

```bash
php artisan hyro:make-crud Product \
    --export \
    --import
```

## Summary

The Hyro CRUD Template System provides:

✅ Multiple pre-built templates
✅ Admin and frontend variants
✅ Smart route management
✅ Authentication control
✅ Ownership validation
✅ Full customization support
✅ Automatic conflict detection
✅ Clean, maintainable code

Choose the right template for your use case and generate production-ready CRUD interfaces in seconds!
