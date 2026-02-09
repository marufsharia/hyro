# Frontend Templates Guide

Quick reference for all 12 frontend templates available in Hyro CRUD generator.

## Template Overview

| # | Template | Color | Style | Best For |
|---|----------|-------|-------|----------|
| 1 | `frontend.template1` | Multi | Card Grid | General purpose |
| 2 | `frontend.template2` | Multi | List View | Simple listings |
| 3 | `frontend.blog` | Purple/Indigo | Featured + Grid | Blogs & Articles |
| 4 | `frontend.landing` | Blue/Cyan | Hero + Features | Marketing Pages |
| 5 | `frontend.news` | Red/Orange | Breaking + List | News & Media |
| 6 | `frontend.ecommerce` | Green/Emerald | Product Grid | Online Stores |
| 7 | `frontend.portfolio` | Pink/Rose | Masonry | Creative Work |
| 8 | `frontend.magazine` | Amber/Gold | Editorial | Publications |
| 9 | `frontend.gallery` | Violet/Fuchsia | Photo Grid | Image Collections |
| 10 | `frontend.directory` | Teal/Cyan | Detailed List | Business Listings |
| 11 | `frontend.dashboard` | Slate/Gray | Data Table | Data Management |
| 12 | `frontend.minimal` | Black/White | Typography | Minimalist Sites |

## Quick Start

### Basic Usage
```bash
php artisan hyro:make-crud {Name} \
    --frontend=true \
    --template=frontend.{template-name} \
    --fields="field1:type1,field2:type2"
```

### With Authentication
```bash
php artisan hyro:make-crud {Name} \
    --frontend=true \
    --auth=true \
    --template=frontend.{template-name} \
    --fields="field1:type1,field2:type2"
```

### Public Access (No Auth)
```bash
php artisan hyro:make-crud {Name} \
    --frontend=true \
    --auth=false \
    --template=frontend.{template-name} \
    --fields="field1:type1,field2:type2"
```

## Template Details

### 1. Blog Template
**Command**: `--template=frontend.blog`

**Perfect For**:
- Personal blogs
- Company blogs
- Content platforms
- Article publishing

**Key Features**:
- Featured article section
- Category filtering
- Reading time estimates
- Gradient hero header

**Recommended Fields**:
```bash
--fields="title:string,content:text,excerpt:text,image:image,category:string,published_at:datetime"
```

**Example**:
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --template=frontend.blog \
    --fields="title:string,content:text,image:image,category:string"
```

---

### 2. Landing Page Template
**Command**: `--template=frontend.landing`

**Perfect For**:
- Product features
- Service showcases
- Marketing pages
- Feature highlights

**Key Features**:
- Animated hero section
- Wave dividers
- Feature grid
- CTA sections

**Recommended Fields**:
```bash
--fields="title:string,description:text,icon:string,image:image,cta_text:string,cta_link:string"
```

**Example**:
```bash
php artisan hyro:make-crud Feature \
    --frontend=true \
    --template=frontend.landing \
    --fields="title:string,description:text,icon:string"
```

---

### 3. News Template
**Command**: `--template=frontend.news`

**Perfect For**:
- News websites
- Media outlets
- Press releases
- Breaking news

**Key Features**:
- Breaking news banner
- Lead story highlight
- Trending sidebar
- Timestamp display

**Recommended Fields**:
```bash
--fields="title:string,content:text,image:image,category:string,breaking:boolean,published_at:datetime"
```

**Example**:
```bash
php artisan hyro:make-crud News \
    --frontend=true \
    --template=frontend.news \
    --fields="title:string,content:text,image:image"
```

---

### 4. E-commerce Template
**Command**: `--template=frontend.ecommerce`

**Perfect For**:
- Online stores
- Product catalogs
- Marketplaces
- Shopping sites

**Key Features**:
- Product grid
- Quick view
- Star ratings
- Price display
- Add to cart

**Recommended Fields**:
```bash
--fields="name:string,description:text,price:decimal,image:image,stock:integer,sku:string,category:string"
```

**Example**:
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string,description:text,price:decimal,image:image"
```

---

### 5. Portfolio Template
**Command**: `--template=frontend.portfolio`

**Perfect For**:
- Creative portfolios
- Design showcases
- Photography
- Project galleries

**Key Features**:
- Masonry layout
- Hover effects
- Category tags
- Gradient overlays

**Recommended Fields**:
```bash
--fields="title:string,description:text,image:image,category:string,client:string,year:integer,url:string"
```

**Example**:
```bash
php artisan hyro:make-crud Project \
    --frontend=true \
    --template=frontend.portfolio \
    --fields="title:string,description:text,image:image,category:string"
```

---

### 6. Magazine Template
**Command**: `--template=frontend.magazine`

**Perfect For**:
- Editorial content
- Long-form articles
- Publications
- Magazine-style sites

**Key Features**:
- Editorial layout
- Serif typography
- Feature story
- Latest issues sidebar

**Recommended Fields**:
```bash
--fields="title:string,content:text,image:image,author:string,published_at:datetime,issue:string"
```

**Example**:
```bash
php artisan hyro:make-crud Story \
    --frontend=true \
    --template=frontend.magazine \
    --fields="title:string,content:text,author:string,image:image"
```

---

### 7. Gallery Template
**Command**: `--template=frontend.gallery`

**Perfect For**:
- Photo galleries
- Image collections
- Visual portfolios
- Photography sites

**Key Features**:
- Square grid
- Lightbox integration
- Like buttons
- Hover overlays

**Recommended Fields**:
```bash
--fields="title:string,description:text,image:image,photographer:string,taken_at:date,location:string"
```

**Example**:
```bash
php artisan hyro:make-crud Photo \
    --frontend=true \
    --template=frontend.gallery \
    --fields="title:string,description:text,image:image"
```

---

### 8. Directory Template
**Command**: `--template=frontend.directory`

**Perfect For**:
- Business directories
- Contact databases
- Listing sites
- Yellow pages

**Key Features**:
- Detailed listings
- Contact information
- Location display
- Website links

**Recommended Fields**:
```bash
--fields="name:string,description:text,image:image,location:string,phone:string,email:email,website:string,category:string"
```

**Example**:
```bash
php artisan hyro:make-crud Business \
    --frontend=true \
    --template=frontend.directory \
    --fields="name:string,description:text,location:string,phone:string,website:string"
```

---

### 9. Dashboard Template
**Command**: `--template=frontend.dashboard`

**Perfect For**:
- Data management
- Admin dashboards
- Analytics pages
- Record keeping

**Key Features**:
- Statistics cards
- Data table
- Inline actions
- Professional design

**Recommended Fields**:
```bash
--fields="name:string,description:text,status:string,value:decimal,date:date,category:string"
```

**Example**:
```bash
php artisan hyro:make-crud Record \
    --frontend=true \
    --template=frontend.dashboard \
    --fields="name:string,description:text,status:string"
```

---

### 10. Minimal Template
**Command**: `--template=frontend.minimal`

**Perfect For**:
- Minimalist sites
- Text-focused content
- Modern design
- Clean aesthetics

**Key Features**:
- Borderless design
- Grayscale images
- Typography focus
- Subtle interactions

**Recommended Fields**:
```bash
--fields="title:string,content:text,image:image,date:date,author:string"
```

**Example**:
```bash
php artisan hyro:make-crud Post \
    --frontend=true \
    --template=frontend.minimal \
    --fields="title:string,content:text,image:image"
```

## Choosing the Right Template

### By Use Case

**Content Publishing**: blog, magazine, news
**E-commerce**: ecommerce
**Visual Content**: gallery, portfolio
**Business**: directory, dashboard
**Marketing**: landing
**Minimalist**: minimal

### By Layout Preference

**Grid Layouts**: blog, landing, ecommerce, gallery, portfolio
**List Layouts**: news, directory, dashboard, minimal
**Special Layouts**: portfolio (masonry), magazine (editorial)

### By Color Preference

**Warm Colors**: news (red/orange), magazine (amber/gold)
**Cool Colors**: blog (purple/indigo), landing (blue/cyan), gallery (violet/fuchsia)
**Nature Colors**: ecommerce (green/emerald), directory (teal/cyan)
**Neutral Colors**: dashboard (slate/gray), minimal (black/white)
**Vibrant Colors**: portfolio (pink/rose)

## Customization

### Publishing Templates
```bash
php artisan vendor:publish --tag=hyro-stubs
```

Templates will be copied to:
```
resources/stubs/hyro/templates/frontend/{template-name}/
```

### Modifying Templates

1. Publish templates
2. Edit files in `resources/stubs/hyro/templates/`
3. Regenerate CRUD with modified templates

### Creating Custom Templates

1. Create directory: `resources/stubs/hyro/templates/frontend/custom/`
2. Add `component.stub` and `view.stub`
3. Use: `--template=frontend.custom`

## Tips & Best Practices

### 1. Choose Based on Content Type
- Images? → gallery, portfolio, ecommerce
- Text-heavy? → blog, magazine, minimal
- Data-driven? → dashboard, directory

### 2. Consider Your Audience
- General public? → landing, blog, news
- Customers? → ecommerce, directory
- Internal users? → dashboard

### 3. Match Your Brand
- Professional? → dashboard, directory, minimal
- Creative? → portfolio, gallery
- Modern? → landing, blog

### 4. Think About Features
- Need categories? → blog, ecommerce, portfolio
- Need contact info? → directory
- Need stats? → dashboard
- Need lightbox? → gallery

## Common Patterns

### Blog with Categories
```bash
php artisan hyro:make-crud Article \
    --frontend=true \
    --template=frontend.blog \
    --fields="title:string,content:text,image:image,category:string" \
    --searchable="title,content" \
    --filterable="category"
```

### E-commerce with Pricing
```bash
php artisan hyro:make-crud Product \
    --frontend=true \
    --template=frontend.ecommerce \
    --fields="name:string,description:text,price:decimal,image:image,stock:integer" \
    --searchable="name,description" \
    --sortable="name,price"
```

### Portfolio with Projects
```bash
php artisan hyro:make-crud Project \
    --frontend=true \
    --template=frontend.portfolio \
    --fields="title:string,description:text,image:image,category:string,url:string" \
    --searchable="title,description" \
    --filterable="category"
```

## Troubleshooting

### Template Not Found
- Check template name spelling
- Ensure template exists in `src/stubs/templates/frontend/`
- System falls back to default if not found

### Styling Issues
- Ensure Tailwind CSS is compiled
- Check dark mode configuration
- Verify Alpine.js is loaded

### Layout Problems
- Check responsive breakpoints
- Test on different screen sizes
- Verify grid/flex configurations

## Support

- Full documentation: `CRUD_TEMPLATE_SYSTEM.md`
- Template README: `src/stubs/templates/README.md`
- Quick start: `QUICK_START_CRUD_TEMPLATES.md`

---

**Total Templates**: 12 frontend templates
**All templates support**: Tailwind CSS 4, Alpine.js, Dark Mode, Responsive Design
