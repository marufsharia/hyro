# Auto-Publish Frontend Layouts

The CRUD generator now automatically detects and publishes frontend layouts when needed, ensuring a seamless development experience.

## How It Works

When generating a CRUD page with a frontend template, the generator:

1. **Detects Package Location** - Automatically finds the Hyro package whether installed via Composer (vendor/) or local development (packages/)
2. **Checks** if `frontend.blade.php` exists in `resources/views/layouts/`
3. **Publishes** the layout automatically if it's missing from the package location
4. **Publishes** frontend assets (CSS/JS) automatically
5. **Skips** publishing if layouts already exist (no overwrites)

## Package Path Detection

The auto-publish feature intelligently detects the package location:

1. **Local Development**: `packages/marufsharia/hyro/` (for package development)
2. **Composer Installation**: `vendor/marufsharia/hyro/` (for production use)
3. **Fallback**: Uses reflection to detect from command class location

This ensures the feature works in all scenarios!

## Automatic Detection

The generator automatically publishes layouts when:

- Using `--frontend` flag
- Using `--template=frontend.*` option
- Template type is set to `frontend`

## What Gets Published

### Layout Files

1. **Main Layout**
   - Source: `packages/marufsharia/hyro/resources/views/layouts/frontend.blade.php`
   - Destination: `resources/views/layouts/frontend.blade.php`

2. **Navigation Partial**
   - Source: `packages/marufsharia/hyro/resources/views/layouts/partials/frontend-nav.blade.php`
   - Destination: `resources/views/layouts/partials/frontend-nav.blade.php`

3. **Footer Partial**
   - Source: `packages/marufsharia/hyro/resources/views/layouts/partials/frontend-footer.blade.php`
   - Destination: `resources/views/layouts/partials/frontend-footer.blade.php`

### Assets

- **CSS**: `public/vendor/hyro/css/hyro.css`
- **JS**: `public/vendor/hyro/js/hyro.js`
- **Images**: `public/vendor/hyro/images/*`

## Usage Examples

### Example 1: Frontend CRUD Generation

```bash
php artisan hyro:make-crud Post \
    --fields="title:string,content:text,published_at:datetime" \
    --frontend \
    --template=frontend.blog
```

**Output:**
```
🚀 Generating files...

✓ Checking/publishing frontend layouts
   📦 Frontend layout not found, publishing...
   ✓ Published: frontend.blade.php, frontend-nav.blade.php, frontend-footer.blade.php
   
   📦 Publishing frontend assets...
   ✓ Assets published successfully

✓ Creating migration
✓ Checking/creating model
✓ Generating Livewire component
✓ Generating Blade view
✓ Registering CRUD route
✓ Running optimizations
```

### Example 2: Layout Already Exists

```bash
php artisan hyro:make-crud Article \
    --fields="title:string,body:text" \
    --frontend
```

**Output:**
```
🚀 Generating files...

✓ Checking/publishing frontend layouts
   ✓ Frontend layout already exists

✓ Creating migration
✓ Checking/creating model
✓ Generating Livewire component
✓ Generating Blade view
✓ Registering CRUD route
✓ Running optimizations
```

## Benefits

### 1. Zero Configuration
- No manual layout publishing needed
- Works out of the box
- Automatic asset management

### 2. Smart Detection
- Only publishes when needed
- Doesn't overwrite existing layouts
- Checks before every generation

### 3. Complete Setup
- Publishes all required files
- Includes navigation and footer
- Publishes CSS/JS assets

### 4. Developer Friendly
- Clear status messages
- Shows what was published
- Warns about failures

## Status Messages

### Success Messages

```
✓ Frontend layout already exists
```
Layout found, no action needed.

```
✓ Published: frontend.blade.php, frontend-nav.blade.php, frontend-footer.blade.php
```
Layouts successfully published.

```
✓ Assets published successfully
```
CSS/JS assets published.

### Warning Messages

```
⚠ Failed: frontend.blade.php (source not found)
```
Source file missing in package.

```
⚠ Failed: frontend.blade.php (copy failed)
```
Permission or disk issue.

```
⚠ Failed to publish assets: [error message]
```
Asset publishing failed.

## Manual Publishing

If you want to publish layouts manually:

```bash
# Publish views
php artisan vendor:publish --tag=hyro-views

# Publish assets
php artisan vendor:publish --tag=hyro-assets
```

## Customization

### After Auto-Publishing

Once layouts are published, you can customize them:

1. **Edit Layout**
   ```bash
   nano resources/views/layouts/frontend.blade.php
   ```

2. **Customize Navigation**
   ```bash
   nano resources/views/layouts/partials/frontend-nav.blade.php
   ```

3. **Modify Footer**
   ```bash
   nano resources/views/layouts/partials/frontend-footer.blade.php
   ```

### Prevent Auto-Publishing

If you don't want auto-publishing, create an empty layout file:

```bash
mkdir -p resources/views/layouts
touch resources/views/layouts/frontend.blade.php
```

The generator will detect it exists and skip publishing.

## Technical Details

### Detection Logic

```php
protected function ensureFrontendLayouts()
{
    $frontendLayoutPath = resource_path('views/layouts/frontend.blade.php');
    
    // Check if frontend layout exists
    if (File::exists($frontendLayoutPath)) {
        $this->line("   ✓ Frontend layout already exists");
        return true;
    }

    // Layout doesn't exist, publish it
    $this->line("   📦 Frontend layout not found, publishing...");
    return $this->publishFrontendLayouts();
}
```

### Publishing Process

1. Check if destination file exists
2. If exists, skip (add to "existing" list)
3. If not exists, check if source exists
4. If source exists, copy to destination
5. Create directories if needed
6. Track published files
7. Publish assets if any layouts were published

### Asset Publishing

```php
protected function publishFrontendAssets()
{
    Artisan::call('vendor:publish', [
        '--tag' => 'hyro-assets',
        '--force' => false,
    ]);
}
```

## Troubleshooting

### Layout Not Found

**Problem:** Source layout file not found

**Solution:**
```bash
# Re-install Hyro package
composer update marufsharia/hyro

# Or manually copy from package
cp packages/marufsharia/hyro/resources/views/layouts/frontend.blade.php \
   resources/views/layouts/frontend.blade.php
```

### Permission Denied

**Problem:** Cannot write to destination

**Solution:**
```bash
# Fix permissions
chmod -R 755 resources/views
chmod -R 755 public/vendor
```

### Assets Not Loading

**Problem:** CSS/JS not found

**Solution:**
```bash
# Re-publish assets
php artisan vendor:publish --tag=hyro-assets --force

# Clear cache
php artisan cache:clear
php artisan view:clear
```

## Best Practices

1. **Let It Auto-Publish**
   - Don't manually publish unless needed
   - Let the generator handle it

2. **Customize After Publishing**
   - Wait for auto-publish to complete
   - Then customize as needed

3. **Version Control**
   - Commit published layouts to git
   - Track customizations

4. **Keep Backups**
   - Before customizing, backup original
   - Easy to restore if needed

## Examples

### Blog CRUD with Auto-Publishing

```bash
php artisan hyro:make-crud Post \
    --fields="title:string,slug:string,content:text,excerpt:text,published_at:datetime" \
    --searchable="title,content" \
    --frontend \
    --template=frontend.blog \
    --migration
```

### E-commerce Product Page

```bash
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal,description:text,image:file" \
    --frontend \
    --template=frontend.ecommerce \
    --migration
```

### Portfolio Project Page

```bash
php artisan hyro:make-crud Project \
    --fields="title:string,description:text,image:file,url:string" \
    --frontend \
    --template=frontend.portfolio \
    --migration
```

## Summary

The auto-publish feature ensures:
- ✅ Layouts are always available
- ✅ Assets are properly published
- ✅ Zero manual configuration
- ✅ Smart detection and publishing
- ✅ Clear status messages
- ✅ No overwrites of existing files

---

**Made with ❤️ by the Hyro team**
