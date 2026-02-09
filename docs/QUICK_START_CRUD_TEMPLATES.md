# Quick Start: CRUD Templates

Generate beautiful CRUD interfaces in seconds with Hyro's template system.

## Basic Usage

### Admin CRUD (Default)
```bash
php artisan hyro:make-crud Product \
    --fields="name:string,price:decimal,stock:integer" \
    --migration
```

**Result**: Full-featured admin interface at `/admin/hyro/products`

### Frontend CRUD
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.template1 \
    --fields="name:string,description:text,image:image,price:decimal"
```

**Result**: Card-based public interface at `/products`

## Available Templates

| Template | Type | Style | Best For |
|----------|------|-------|----------|
| `admin.template1` | Admin | Full-featured | Complete dashboards |
| `admin.template2` | Admin | Compact | Simple data entry |
| `frontend.template1` | Frontend | Card grid | Product catalogs |
| `frontend.template2` | Frontend | List view | Blogs, articles |

## Common Scenarios

### 1. E-commerce Products
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.template1 \
    --fields="name:string,description:text,price:decimal,image:image" \
    --searchable="name,description" \
    --migration
```

### 2. Blog Articles
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.template2 \
    --fields="title:string,content:text,author:string" \
    --migration
```

### 3. Admin Management
```bash
php artisan hyro:make-crud User \
    --template=admin.template1 \
    --fields="name:string,email:email,role:string" \
    --export \
    --privileges
```

### 4. Simple Categories
```bash
php artisan hyro:make-crud Category \
    --template=admin.template2 \
    --fields="name:string,slug:string"
```

## Options Reference

| Option | Default | Description |
|--------|---------|-------------|
| `--frontend` | `false` | Generate frontend route |
| `--auth` | `true` | Require authentication |
| `--template` | `admin.template1` | Template to use |
| `--migration` | - | Generate migration |
| `--export` | - | Enable export |
| `--privileges` | - | Create privileges |

## Template Comparison

### Admin Templates

**template1** (Default)
- ✅ Gradient design
- ✅ Advanced filters
- ✅ Bulk actions
- ✅ Export/import
- ❌ More space needed

**template2** (Compact)
- ✅ Space-efficient
- ✅ Quick data entry
- ✅ Simple interface
- ❌ Fewer features

### Frontend Templates

**template1** (Cards)
- ✅ Visual appeal
- ✅ Image support
- ✅ Responsive grid
- ❌ More vertical space

**template2** (List)
- ✅ Quick scanning
- ✅ Compact layout
- ✅ Clean design
- ❌ Less visual

## Next Steps

1. **Generate your first CRUD**:
   ```bash
   php artisan hyro:make-crud Test --fields="name:string" --migration
   ```

2. **Run migration**:
   ```bash
   php artisan migrate
   ```

3. **Access your CRUD**:
   - Admin: `http://yourapp.com/admin/hyro/tests`
   - Frontend: `http://yourapp.com/tests` (if `--frontend=true`)

4. **Customize templates** (optional):
   ```bash
   php artisan vendor:publish --tag=hyro-stubs
   ```

## Tips

💡 **Start with defaults**: Use `admin.template1` for admin, `frontend.template1` for public

💡 **Test templates**: Try different templates to see which fits your needs

💡 **Customize later**: Generate first, customize templates after

💡 **Check routes**: Run `php artisan route:list` to see registered routes

## Need Help?

- 📖 Full docs: `packages/marufsharia/hyro/docs/CRUD_TEMPLATE_SYSTEM.md`
- 📝 Template README: `packages/marufsharia/hyro/src/stubs/templates/README.md`
- 🔍 Examples: See generated files for reference

---

**Happy CRUD generating! 🚀**
