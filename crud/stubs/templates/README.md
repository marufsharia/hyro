# Hyro CRUD Templates

This directory contains template variations for CRUD generation. Templates allow you to generate different UI styles for your CRUD interfaces.

## Directory Structure

```
templates/
├── admin/
│   ├── template1/          # Default admin template (full-featured)
│   └── template2/          # Compact admin template
└── frontend/
    ├── template1/          # Card-based layout (original)
    ├── template2/          # List-based layout (original)
    ├── blog/               # Blog/Article layout (Purple/Indigo)
    ├── landing/            # Landing page layout (Blue/Cyan)
    ├── news/               # News/Media layout (Red/Orange)
    ├── ecommerce/          # E-commerce product grid (Green/Emerald)
    ├── portfolio/          # Portfolio masonry layout (Pink/Rose)
    ├── magazine/           # Magazine editorial layout (Amber)
    ├── gallery/            # Photo gallery grid (Violet/Fuchsia)
    ├── directory/          # Business directory list (Teal/Cyan)
    ├── dashboard/          # Data dashboard table (Slate/Gray)
    └── minimal/            # Minimalist layout (Black/White)
```

## Available Templates

### Admin Templates

#### `admin.template1` (Default)
- **Style**: Full-featured admin interface
- **Layout**: Gradient backgrounds, large cards, comprehensive filters
- **Features**: Bulk actions, advanced search, export buttons
- **Best for**: Full admin dashboards with all features

#### `admin.template2` (Compact)
- **Style**: Minimal, space-efficient admin interface
- **Layout**: Compact table, simple filters
- **Features**: Essential CRUD operations only
- **Best for**: Simple admin panels, data entry forms

### Frontend Templates

#### `frontend.template1` (Card Grid - Original)
- **Style**: Modern card-based layout
- **Layout**: Responsive grid of cards with images
- **Features**: User ownership checks, public browsing
- **Best for**: Product catalogs, portfolios, galleries

#### `frontend.template2` (List View - Original)
- **Style**: Clean list-based layout
- **Layout**: Vertical list with inline actions
- **Features**: Minimal design, quick scanning
- **Best for**: Blogs, articles, simple listings

#### `frontend.blog` (Blog Layout)
- **Color**: Purple/Indigo gradient
- **Style**: Featured article + grid layout
- **Features**: Category filters, reading time, featured posts
- **Best for**: Blogs, articles, content publishing
- **Highlights**: Hero header, featured article section, category badges

#### `frontend.landing` (Landing Page)
- **Color**: Blue/Cyan gradient
- **Style**: Hero section + feature grid
- **Features**: Wave dividers, gradient cards, CTA sections
- **Best for**: Product features, service showcases, marketing pages
- **Highlights**: Animated hero, decorative elements, call-to-action

#### `frontend.news` (News/Media)
- **Color**: Red/Orange gradient
- **Style**: Breaking news banner + article grid
- **Features**: Breaking news ticker, trending sidebar, timestamps
- **Best for**: News sites, media outlets, press releases
- **Highlights**: Breaking news banner, lead story, trending section

#### `frontend.ecommerce` (E-commerce)
- **Color**: Green/Emerald
- **Style**: Product grid with hover effects
- **Features**: Quick actions, ratings, price display, cart icon
- **Best for**: Online stores, product catalogs, marketplaces
- **Highlights**: Product cards, hover overlays, shopping cart integration

#### `frontend.portfolio` (Portfolio)
- **Color**: Pink/Rose gradient
- **Style**: Masonry grid layout
- **Features**: Image-focused, category tags, hover effects
- **Best for**: Creative portfolios, design showcases, photography
- **Highlights**: Masonry layout, gradient overlays, project categories

#### `frontend.magazine` (Magazine)
- **Color**: Amber/Gold
- **Style**: Editorial magazine layout
- **Features**: Feature article, sidebar, serif fonts
- **Best for**: Editorial content, long-form articles, publications
- **Highlights**: Magazine-style header, feature story, latest issues sidebar

#### `frontend.gallery` (Photo Gallery)
- **Color**: Violet/Fuchsia gradient
- **Style**: Photo grid with lightbox
- **Features**: Aspect-ratio grid, like buttons, hover overlays
- **Best for**: Photo galleries, image collections, visual portfolios
- **Highlights**: Square grid, lightbox integration, like functionality

#### `frontend.directory` (Business Directory)
- **Color**: Teal/Cyan
- **Style**: Detailed listing cards
- **Features**: Contact info, location, website links, logos
- **Best for**: Business directories, listings, contact databases
- **Highlights**: Logo display, contact details, location info

#### `frontend.dashboard` (Data Dashboard)
- **Color**: Slate/Gray
- **Style**: Professional data table
- **Features**: Stats cards, sortable table, inline actions
- **Best for**: Data management, admin dashboards, analytics
- **Highlights**: Statistics cards, data table, professional design

#### `frontend.minimal` (Minimalist)
- **Color**: Black/White monochrome
- **Style**: Ultra-minimal, typography-focused
- **Features**: Clean lines, grayscale images, subtle interactions
- **Best for**: Minimalist sites, text-focused content, modern design
- **Highlights**: Borderless design, grayscale aesthetic, typography focus

## Usage Examples

### Blog Template
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --template=frontend.blog \
    --fields="title:string,content:text,image:image,category:string"
```

### Landing Page Template
```bash
php artisan hyro:make-crud Feature \
    --frontend=true \
    --template=frontend.landing \
    --fields="title:string,description:text,icon:string"
```

### News Template
```bash
php artisan hyro:make-crud News \
    --frontend=true \
    --template=frontend.news \
    --fields="title:string,content:text,image:image"
```

### E-commerce Template
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string,description:text,price:decimal,image:image"
```

### Portfolio Template
```bash
php artisan hyro:make-crud Project \
    --frontend=true \
    --template=frontend.portfolio \
    --fields="title:string,description:text,image:image,category:string"
```

### Magazine Template
```bash
php artisan hyro:make-crud Story \
    --frontend=true \
    --template=frontend.magazine \
    --fields="title:string,content:text,author:string,image:image"
```

### Gallery Template
```bash
php artisan hyro:make-crud Photo \
    --frontend=true \
    --template=frontend.gallery \
    --fields="title:string,description:text,image:image"
```

### Directory Template
```bash
php artisan hyro:make-crud Business \
    --frontend=true \
    --template=frontend.directory \
    --fields="name:string,description:text,location:string,phone:string,website:string,image:image"
```

### Dashboard Template
```bash
php artisan hyro:make-crud Record \
    --frontend=true \
    --template=frontend.dashboard \
    --fields="name:string,description:text,status:string"
```

### Minimal Template
```bash
php artisan hyro:make-crud Post \
    --frontend=true \
    --template=frontend.minimal \
    --fields="title:string,content:text,image:image"
```

## Template Characteristics

| Template | Color Scheme | Layout Type | Best Use Case |
|----------|-------------|-------------|---------------|
| blog | Purple/Indigo | Featured + Grid | Content publishing |
| landing | Blue/Cyan | Hero + Features | Marketing pages |
| news | Red/Orange | Breaking + List | News/Media sites |
| ecommerce | Green/Emerald | Product Grid | Online stores |
| portfolio | Pink/Rose | Masonry | Creative work |
| magazine | Amber/Gold | Editorial | Long-form content |
| gallery | Violet/Fuchsia | Photo Grid | Image collections |
| directory | Teal/Cyan | Detailed List | Business listings |
| dashboard | Slate/Gray | Data Table | Data management |
| minimal | Black/White | Typography | Minimalist sites |

## Customization

All templates support full customization through Tailwind CSS 4 and Alpine.js. Publish templates to customize:

```bash
php artisan vendor:publish --tag=hyro-stubs
```

Templates will be copied to:
```
resources/stubs/hyro/templates/
```

## Color Schemes

Each template uses a unique color palette:
- **Blog**: Purple (#9333EA) to Indigo (#4F46E5)
- **Landing**: Blue (#2563EB) to Cyan (#06B6D4)
- **News**: Red (#DC2626) to Orange (#EA580C)
- **E-commerce**: Green (#059669) to Emerald (#10B981)
- **Portfolio**: Pink (#EC4899) to Rose (#F43F5E)
- **Magazine**: Amber (#F59E0B) to Gold (#EAB308)
- **Gallery**: Violet (#7C3AED) to Fuchsia (#C026D3)
- **Directory**: Teal (#14B8A6) to Cyan (#06B6D4)
- **Dashboard**: Slate (#475569) to Gray (#6B7280)
- **Minimal**: Black (#000000) to White (#FFFFFF)

## Features by Template

### Interactive Features
- **blog**: Category filtering, featured posts
- **landing**: Animated hero, CTA sections
- **news**: Breaking news ticker, trending sidebar
- **ecommerce**: Quick view, add to cart, ratings
- **portfolio**: Masonry layout, project categories
- **magazine**: Editorial layout, latest issues
- **gallery**: Lightbox, like buttons
- **directory**: Contact info, location maps
- **dashboard**: Stats cards, data tables
- **minimal**: Grayscale effects, typography focus

## Support

For more information:
- Full documentation: `packages/marufsharia/hyro/docs/CRUD_TEMPLATE_SYSTEM.md`
- Quick start: `packages/marufsharia/hyro/docs/QUICK_START_CRUD_TEMPLATES.md`

---

**Total Templates**: 12 (2 admin + 10 frontend)
**All templates use Tailwind CSS 4 and Alpine.js**


## Usage

### Basic Usage

```bash
# Generate with default admin template
php artisan hyro:make-crud Product --fields="name:string,price:decimal"

# Generate with specific admin template
php artisan hyro:make-crud Product --template=admin.template2 --fields="name:string"

# Generate frontend CRUD
php artisan hyro:make-crud Product --frontend=true --template=frontend.template1 --fields="name:string"
```

### Command Options

- `--frontend=true|false` - Generate frontend route (default: false)
- `--auth=true|false` - Require authentication (default: true)
- `--template=TYPE.NAME` - Choose template (default: admin.template1)

### Template Format

Templates use the format: `TYPE.NAME`
- **TYPE**: `admin` or `frontend`
- **NAME**: `template1`, `template2`, etc.

## Examples

### Admin CRUD with Default Template
```bash
php artisan hyro:make-crud Post \
    --fields="title:string,content:text,published:boolean" \
    --template=admin.template1
```

### Admin CRUD with Compact Template
```bash
php artisan hyro:make-crud Category \
    --fields="name:string,slug:string" \
    --template=admin.template2
```

### Frontend CRUD with Card Layout
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.template1 \
    --fields="name:string,description:text,image:image,price:decimal"
```

### Frontend CRUD with List Layout (No Auth)
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --auth=false \
    --template=frontend.template2 \
    --fields="title:string,content:text,author:string"
```

## Customization

### Publishing Templates

You can publish templates to customize them:

```bash
php artisan vendor:publish --tag=hyro-stubs
```

This will copy templates to:
```
resources/stubs/hyro/templates/
```

### Creating Custom Templates

1. Create a new directory under `admin/` or `frontend/`:
   ```
   resources/stubs/hyro/templates/admin/template3/
   ```

2. Add `component.stub` and `view.stub` files

3. Use your custom template:
   ```bash
   php artisan hyro:make-crud Product --template=admin.template3
   ```

## Template Variables

All templates support these placeholder variables:

### Component Stub Variables
- `{{ namespace }}` - Component namespace
- `{{ componentName }}` - Component class name
- `{{ modelClass }}` - Model class name
- `{{ properties }}` - Component properties
- `{{ fields }}` - Field configuration
- `{{ searchableFields }}` - Searchable fields array
- `{{ tableColumns }}` - Table columns array
- `{{ permission }}` - Permission name
- `{{ relations }}` - Relationship methods
- `{{ filters }}` - Filter methods
- `{{ exportMethods }}` - Export methods
- `{{ importMethods }}` - Import methods
- `{{ customMethods }}` - Custom methods

### View Stub Variables
- `{{ title }}` - Page title
- `{{ description }}` - Page description
- `{{ resourceName }}` - Singular resource name
- `{{ resourceNamePlural }}` - Plural resource name
- `{{ tableHeaders }}` - Table header HTML
- `{{ tableColumns }}` - Table column HTML
- `{{ formFields }}` - Form field HTML
- `{{ filterFields }}` - Filter field HTML
- `{{ exportButton }}` - Export button HTML
- `{{ columnCount }}` - Total column count

## Route Behavior

### Admin Routes
- Registered under: `routes/hyro/crud.php`
- Prefix: `admin/hyro` (configurable)
- Middleware: `['web', 'auth']` (default)
- Name: `hyro.admin.{resource-name}`

### Frontend Routes
- Registered under: `routes/hyro/crud.php`
- Prefix: None (root level)
- Middleware: `['web']` or `['web', 'auth']` (based on --auth option)
- Name: `frontend.{resource-name}`
- Path conflict checking: Automatic

## Best Practices

1. **Choose the right template**:
   - Use admin templates for backend management
   - Use frontend templates for public-facing interfaces

2. **Authentication**:
   - Admin routes: Always require auth
   - Frontend routes: Use `--auth=false` for public content

3. **Ownership checks**:
   - Frontend templates include user ownership checks
   - Users can only edit/delete their own records

4. **Customization**:
   - Publish templates before customizing
   - Keep original templates as backup
   - Test custom templates thoroughly

## Troubleshooting

### Template Not Found
If you see "Template not found" warning:
1. Check template name format: `TYPE.NAME`
2. Verify template files exist
3. Falls back to default stub automatically

### Route Conflicts
For frontend routes with path conflicts:
- System automatically appends numbers: `products`, `products-1`, etc.
- Check `routes/hyro/crud.php` for registered routes

### Permission Issues
Frontend templates check ownership:
- Ensure your model has `user_id` column
- Or customize `canUpdate()` and `canDelete()` methods

## Support

For more information:
- Documentation: `packages/marufsharia/hyro/docs/`
- Examples: See generated CRUD files
- Issues: Check Laravel logs for errors
