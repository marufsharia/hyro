# HYRO CRUD Generator - Complete Stubs Package
## All Stub Files with File Upload Support

## 📦 Package Contents

All stub files are located in `/home/claude/stubs/`:

1. ✅ **component.stub** - Livewire component with file upload
2. ✅ **view.stub** - Tailwind CSS 4 + Alpine.js view
3. ✅ **migration.stub** - Database migration  
4. ✅ **model.stub** - Eloquent model with file helpers
5. ✅ **export.stub** - Export service (CSV, Excel, PDF)
6. ✅ **import.stub** - Import service with validation
7. ✅ **form-fields.stub** - Reusable form field components

## 🚀 Quick Start

### Installation

1. Extract all stub files to your Laravel project:
```bash
# Create stubs directory
mkdir -p resources/stubs/hyro/crud

# Copy all stub files
cp /home/claude/stubs/* resources/stubs/hyro/crud/
```

2. The CRUD generator will auto-detect and use these stubs

### Usage Example with File Upload

```bash
php artisan hyro:make-crud Post \
    --fields="title:string:required,slug:string:required|unique,body:text:required,excerpt:text:nullable,featured_image:image:nullable,attachment:file:nullable,status:select:required,published_at:datetime:nullable" \
    --searchable="title,body,excerpt" \
    --sortable="title,status,published_at,created_at" \
    --filterable="status" \
    --export \
    --import \
    --migration \
    --route
```

This automatically generates:
- ✅ Migration with file columns
- ✅ Model with file upload helpers
- ✅ Component with file upload handling
- ✅ Beautiful view with image preview
- ✅ Export/Import services

## 📸 File Upload Features

### Supported File Types

**Images** (`type: image`)
- Formats: JPG, PNG, GIF, WebP, SVG
- Auto preview in form
- Thumbnail in table
- Progress indicator
- Drag & drop support

**Files** (`type: file`)
- Any file type
- Size limits
- MIME type validation
- Download links
- File icons

### File Upload UI Features

- ✅ Drag & drop
- ✅ Click to upload
- ✅ Image preview (live)
- ✅ Progress bar
- ✅ Remove button
- ✅ File size validation
- ✅ File type validation
- ✅ Error messages
- ✅ Dark mode support

### Generated File Upload Code

**In Component:**
```php
public $featured_image;
public $attachment;

protected function getFields(): array
{
    return [
        'featured_image' => [
            'type' => 'file',
            'label' => 'Featured Image',
            'rules' => 'nullable|image|max:2048',
            'accept' => 'image/*',
            'storage_path' => 'posts/images',
            'disk' => 'public',
        ],
        'attachment' => [
            'type' => 'file',
            'label' => 'Attachment',
            'rules' => 'nullable|mimes:pdf,doc,docx|max:5120',
            'accept' => '.pdf,.doc,.docx',
            'storage_path' => 'posts/attachments',
            'disk' => 'public',
        ],
    ];
}

protected function getFormData(): array
{
    $data = parent::getFormData();
    
    // Handle file uploads
    if ($this->featured_image && !is_string($this->featured_image)) {
        $data['featured_image'] = $this->featured_image->store(
            'posts/images',
            'public'
        );
    }
    
    return $data;
}
```

**In Model:**
```php
protected $fillable = [
    'title', 'slug', 'body', 'excerpt',
    'featured_image', 'attachment',
    'status', 'published_at'
];

// Auto-generated helper methods
public function getFileUrl(string $field): ?string
{
    if (!$this->{$field}) {
        return null;
    }
    return Storage::disk('public')->url($this->{$field});
}

public function getImageThumb(string $field, int $width = 150): ?string
{
    // Returns thumbnail URL
}
```

**In View:**
```blade
{{-- Auto-generated file upload field with preview --}}
<div class="col-span-6">
    <label>Featured Image</label>
    <div x-data="{ uploading: false, progress: 0 }">
        {{-- Drag & drop area with preview --}}
        <div class="border-dashed border-2 rounded-xl">
            @if($featured_image)
                <img src="{{ $featured_image->temporaryUrl() }}" />
            @endif
            <input type="file" wire:model="featured_image" />
        </div>
        
        {{-- Progress bar --}}
        <div x-show="uploading">
            <div :style="`width: ${progress}%`"></div>
        </div>
    </div>
</div>
```

## 🎨 All Field Types Supported

### Text Fields
```bash
--fields="name:string:required"           # Text input
--fields="description:text:nullable"      # Textarea  
--fields="email:email:required|unique"    # Email input
--fields="url:url:nullable"               # URL input
--fields="password:password:required"     # Password input
```

### Numbers
```bash
--fields="price:decimal:required|min:0"   # Decimal (2 decimals)
--fields="quantity:integer:required"      # Integer
--fields="rating:float:nullable"          # Float
```

### Dates & Times
```bash
--fields="published_at:date:nullable"     # Date picker
--fields="scheduled_at:datetime:nullable" # DateTime picker
--fields="start_time:time:nullable"       # Time picker
```

### Files & Images
```bash
--fields="avatar:image:nullable"          # Image upload
--fields="document:file:nullable"         # File upload
--fields="gallery:images:nullable"        # Multiple images (future)
```

### Selections
```bash
--fields="status:select:required"         # Dropdown
--fields="category:radio:required"        # Radio buttons
--fields="tags:checkbox:nullable"         # Checkboxes
```

### Boolean
```bash
--fields="is_active:boolean:nullable"     # Toggle switch
--fields="is_featured:checkbox:nullable"  # Checkbox
```

### Relations
```bash
--fields="user_id:relation:required"      # belongsTo
--fields="categories:relations:nullable"  # belongsToMany (future)
```

## 📊 Export Examples

Generated export service:

```php
// Export to CSV
$csv = PostExportService::toCsv($query);

// Export to Excel
$excel = PostExportService::toExcel($query);

// Export to PDF
$pdf = PostExportService::toPdf($query);
```

## 📥 Import Examples

Generated import service:

```php
$result = PostImportService::fromFile($filePath);

if ($result['success']) {
    echo "Imported: {$result['summary']['success']} records";
    echo "Skipped: {$result['summary']['skipped']} records";
    echo "Errors: " . count($result['summary']['errors']);
}
```

## 🎯 Complete Real-World Example

### E-commerce Product with Images

```bash
php artisan hyro:make-crud Product \
    --fields="name:string:required|max:255,sku:string:required|unique,slug:string:required|unique,description:text:required,price:decimal:required|min:0,sale_price:decimal:nullable|min:0,stock:integer:required|min:0,featured_image:image:nullable,gallery_images:images:nullable,category_id:relation:required,status:select:required|in:active,inactive" \
    --searchable="name,sku,description" \
    --sortable="name,price,stock,created_at" \
    --filterable="status,category_id" \
    --relations="category" \
    --export \
    --import \
    --migration \
    --route
```

Generated files:
1. Migration with image columns
2. Model with file helpers
3. Component with image upload
4. View with image gallery
5. Export/Import services

## 📁 File Structure After Generation

```
app/
├── Livewire/Admin/
│   └── ProductManager.php          # Component with file upload
├── Models/
│   └── Product.php                 # Model with file helpers
└── Services/
    ├── Export/
    │   └── ProductExportService.php
    └── Import/
        └── ProductImportService.php

database/migrations/
└── 2024_01_29_create_products_table.php

resources/views/livewire/admin/
└── product-manager.blade.php        # View with file upload UI
```

## 🔧 Customization

### Custom File Processing

Add to your component:

```php
protected function beforeSave(array $data): array
{
    // Resize image before saving
    if ($this->featured_image && !is_string($this->featured_image)) {
        $image = Image::make($this->featured_image->getRealPath());
        $image->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        
        $filename = Str::slug($this->name) . '_' . time() . '.jpg';
        $path = 'products/' . $filename;
        
        Storage::disk('public')->put($path, (string) $image->encode('jpg', 90));
        $data['featured_image'] = $path;
    }
    
    return $data;
}

protected function afterDelete($recordId): void
{
    // Delete associated files
    if ($this->featured_image && is_string($this->featured_image)) {
        Storage::disk('public')->delete($this->featured_image);
    }
}
```

### Multiple File Upload (Gallery)

```php
public $gallery_images = [];

protected function afterCreate($record): void
{
    if ($this->gallery_images) {
        foreach ($this->gallery_images as $image) {
            $path = $image->store('products/gallery', 'public');
            $record->images()->create(['path' => $path]);
        }
    }
}
```

## 🎨 UI Features

### Form Features
- ✅ Beautiful Tailwind CSS 4 design
- ✅ Dark mode support
- ✅ Responsive layout
- ✅ Alpine.js interactions
- ✅ File upload with preview
- ✅ Progress indicators
- ✅ Drag & drop
- ✅ Validation errors
- ✅ Success notifications

### Table Features
- ✅ Responsive design
- ✅ Image thumbnails
- ✅ File download links
- ✅ Sorting
- ✅ Search
- ✅ Filtering
- ✅ Bulk actions
- ✅ Pagination
- ✅ Loading states

## 🔒 Security

All file uploads include:
- ✅ MIME type validation
- ✅ File size limits
- ✅ Secure file naming
- ✅ Protected storage paths
- ✅ XSS prevention
- ✅ CSRF protection

## 📦 Dependencies

Required packages:
```bash
composer require livewire/livewire
composer require maatwebsite/excel  # For export/import
composer require barryvdh/laravel-dompdf  # For PDF export
composer require intervention/image  # For image processing (optional)
```

## 🚀 Installation & Usage

1. **Extract stubs:**
```bash
mkdir -p resources/stubs/hyro/crud
cp -r /home/claude/stubs/* resources/stubs/hyro/crud/
```

2. **Generate CRUD:**
```bash
php artisan hyro:make-crud YourModel --fields="..." --export --import --migration
```

3. **Run migration:**
```bash
php artisan migrate
```

4. **Link storage (for file uploads):**
```bash
php artisan storage:link
```

5. **Access your CRUD:**
Navigate to `/admin/your-models`

## 📚 All Stubs Explained

### 1. component.stub
Livewire component template with:
- Full CRUD operations
- File upload handling
- Search & filter
- Export/Import methods
- Validation
- Permission checks

### 2. view.stub  
Blade view template with:
- Tailwind CSS 4 styling
- Alpine.js interactions
- File upload UI with preview
- Responsive table
- Modal forms
- Bulk actions
- Dark mode

### 3. migration.stub
Migration template with:
- All column types
- File columns
- Indexes
- Foreign keys
- Soft deletes
- Timestamps

### 4. model.stub
Model template with:
- Fillable fields
- Casts
- Relations
- File helper methods
- Soft deletes
- Audit trail (optional)

### 5. export.stub
Export service with:
- CSV export
- Excel export with styling
- PDF export
- Custom columns
- Filtered exports

### 6. import.stub
Import service with:
- CSV/Excel import
- Validation
- Error handling
- Batch processing
- Skip on error

### 7. form-fields.stub
Reusable form fields with:
- All input types
- File upload with preview
- Validation display
- Consistent styling
- Accessibility

## 🎉 Result

You get a **production-ready CRUD system** with:
- ✅ Zero configuration
- ✅ Beautiful UI
- ✅ File upload support
- ✅ Export/Import
- ✅ Search & filter
- ✅ Responsive design
- ✅ Dark mode
- ✅ Role-based access
- ✅ Audit trail
- ✅ Professional code quality

**Ready to use immediately!**

---

**Created by:** Hyro CRUD Generator v2.0  
**File Upload:** ✅ Fully Supported  
**UI Framework:** Tailwind CSS 4  
**JS Framework:** Alpine.js  
**Backend:** Livewire 3/4  

Need help? Check the examples in the documentation!
