# 🛣️ Smart Route Loading Implementation

**Date:** February 9, 2026  
**Feature:** Smart Route Loading System  
**Status:** ✅ Completed

---

## 📋 Overview

Implemented a smart route loading system that allows Hyro routes to load from the package by default, but automatically switches to published routes when users customize them.

---

## 🎯 Problem Statement

Previously, Hyro routes were always loaded from the package, making it difficult for users to customize routes without modifying vendor files (which is a bad practice and gets overwritten on updates).

---

## ✨ Solution

Implemented a smart route loading mechanism in `HyroServiceProvider` that:

1. **Checks for published routes first** in `routes/hyro/` directory
2. **Falls back to package routes** if published routes don't exist
3. **Allows seamless customization** without modifying vendor files

---

## 🔧 Implementation Details

### Modified Files

#### 1. `HyroServiceProvider.php`

**Added `loadSmartRoutes()` method:**

```php
/**
 * Load routes from published location if exists, otherwise from package.
 *
 * @param string $routeFile
 * @return void
 */
private function loadSmartRoutes(string $routeFile): void
{
    $publishedRoute = base_path("routes/hyro/{$routeFile}");
    $packageRoute = __DIR__ . "/../routes/{$routeFile}";

    if (File::exists($publishedRoute)) {
        // Load from published routes (user has customized them)
        $this->loadRoutesFrom($publishedRoute);
    } elseif (File::exists($packageRoute)) {
        // Load from package routes (default)
        $this->loadRoutesFrom($packageRoute);
    }
}
```

**Updated `loadConditionalResources()` method:**

```php
private function loadConditionalResources(): void
{
    // Migrations
    if (config('hyro.database.migrations.autoload', true)) {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    // Routes
    if (config('hyro.api.enabled', false)) {
        $this->loadSmartRoutes('api.php');
    }

    if (config('hyro.admin.enabled', false)) {
        // Smart route loading: Load from published routes if they exist, otherwise from package
        $this->loadSmartRoutes('admin.php');
        $this->loadSmartRoutes('auth.php');
        $this->loadSmartRoutes('notifications.php');
        
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyro');
    }
}
```

**Added route publishing in `publishResources()` method:**

```php
// Routes (for customization)
$this->publishes([
    __DIR__ . '/../routes/admin.php' => base_path('routes/hyro/admin.php'),
    __DIR__ . '/../routes/auth.php' => base_path('routes/hyro/auth.php'),
    __DIR__ . '/../routes/notifications.php' => base_path('routes/hyro/notifications.php'),
    __DIR__ . '/../routes/api.php' => base_path('routes/hyro/api.php'),
], 'hyro-routes');
```

---

## 📚 Documentation Updates

### 1. Updated `INSTALLATION.md`

Added information about route publishing:

```markdown
### Step 2: Publish Configuration and Assets

# Or publish specific resources
php artisan vendor:publish --tag=hyro-routes  # Optional: Only if you want to customize routes

> **Note on Routes:** By default, Hyro loads routes from the package. You only need to publish routes if you want to customize them. Once published to `routes/hyro/`, those routes will take precedence over the package routes.
```

### 2. Updated `CONFIGURATION.md`

Added comprehensive "Route Customization" section covering:
- Smart route loading explanation
- How to publish routes
- Customization examples
- Route loading priority
- Best practices
- Reverting to package routes

---

## 🚀 Usage

### Default Behavior (No Customization)

Routes automatically load from the package:

```
vendor/marufsharia/hyro/routes/
├── admin.php
├── auth.php
├── notifications.php
└── api.php
```

### Publishing Routes for Customization

```bash
php artisan vendor:publish --tag=hyro-routes
```

This creates:

```
routes/hyro/
├── admin.php
├── auth.php
├── notifications.php
└── api.php
```

### Customizing Routes

Edit files in `routes/hyro/` directory. These will automatically take precedence over package routes.

### Reverting to Package Routes

Simply delete the published routes:

```bash
rm -rf routes/hyro/
```

---

## ✅ Benefits

1. **No Vendor Modifications:** Users never need to modify vendor files
2. **Update Safe:** Customizations survive package updates
3. **Flexible:** Users can customize only the routes they need
4. **Transparent:** Clear priority system (published > package)
5. **Reversible:** Easy to revert to package routes
6. **Backward Compatible:** Existing installations continue to work

---

## 🧪 Testing

### Test Scenarios

1. ✅ **Default Loading:** Routes load from package when not published
2. ✅ **Published Loading:** Routes load from `routes/hyro/` when published
3. ✅ **Partial Publishing:** Can publish only some routes (e.g., only admin.php)
4. ✅ **Fallback:** Falls back to package routes if published file is deleted
5. ✅ **No Errors:** No errors when routes directory doesn't exist

### Manual Testing

```bash
# Test 1: Default behavior
php artisan route:list | grep hyro

# Test 2: Publish routes
php artisan vendor:publish --tag=hyro-routes
php artisan route:list | grep hyro

# Test 3: Customize a route
# Edit routes/hyro/admin.php
php artisan route:list | grep hyro

# Test 4: Revert
rm -rf routes/hyro/
php artisan route:list | grep hyro
```

---

## 📝 Route Files

### Routes Included

1. **admin.php** - Admin panel routes
   - Dashboard
   - Role management
   - Privilege management
   - User-role management

2. **auth.php** - Authentication routes
   - Login
   - Register
   - Password reset
   - Logout

3. **notifications.php** - Notification routes
   - Notification center
   - Mark as read
   - Delete notifications
   - Preferences

4. **api.php** - API routes (if enabled)
   - RESTful API endpoints
   - Token authentication

---

## 🔄 Migration Path

### For Existing Users

No migration needed! The smart route loading is backward compatible:

- Existing installations continue to work without changes
- Routes load from package by default
- Users can opt-in to customization by publishing routes

### For New Users

- Install package normally
- Routes work out of the box
- Publish only if customization is needed

---

## 🎓 Best Practices

1. **Publish Selectively:** Only publish routes you need to customize
2. **Document Changes:** Add comments explaining customizations
3. **Version Control:** Commit published routes to version control
4. **Test Thoroughly:** Test route changes before deploying
5. **Keep Updated:** Check for route changes when upgrading Hyro
6. **Use Config:** Prefer config changes over route modifications when possible

---

## 🔮 Future Enhancements

Potential improvements for future versions:

1. **Route Merging:** Merge published routes with package routes instead of replacing
2. **Route Validation:** Validate published routes against package schema
3. **Route Diff:** Command to show differences between published and package routes
4. **Route Sync:** Command to sync published routes with package updates
5. **Route Templates:** Provide route templates for common customizations

---

## 📊 Impact

### Code Changes

- **Files Modified:** 1 (`HyroServiceProvider.php`)
- **Lines Added:** ~30
- **Lines Removed:** ~5
- **Net Change:** +25 lines

### Documentation Changes

- **Files Modified:** 2 (`INSTALLATION.md`, `CONFIGURATION.md`)
- **Files Created:** 1 (`SMART_ROUTE_LOADING.md`)
- **Documentation Added:** ~150 lines

---

## ✨ Conclusion

The smart route loading system provides a clean, maintainable way for users to customize Hyro routes without modifying vendor files. It's backward compatible, easy to use, and follows Laravel best practices.

**Status:** ✅ Ready for production

---

**Implementation Date:** February 9, 2026  
**Implemented By:** Kiro AI Assistant  
**Reviewed By:** Pending  
**Approved By:** Pending
