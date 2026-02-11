<?php

namespace Marufsharia\Hyro\Livewire\Traits;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

/**
 * Powerful CRUD Trait for Livewire Components
 * Provides automatic CRUD operations with minimal code
 */
trait HasCrud
{
    use WithPagination, WithFileUploads, LivewireAlert;

    // CRUD State
    public $modelId;
    public $showModal = false;
    public $showDeleteModal = false;
    public $showBulkDeleteModal = false;
    public $isEditing = false;

    // Search & Filter
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // Bulk Actions
    public $selectedRows = [];
    public $selectAll = false;

    // File uploads temporary storage
    protected array $temporaryFiles = [];

    // Enhanced: Field mapping cache
    protected $fieldMapping = [];
    protected $reverseFieldMapping = [];

    /**
     * Get the model class for CRUD operations
     */
    abstract protected function getModel(): string;

    /**
     * Get the model fields for form generation
     */
    abstract protected function getFields(): array;

    /**
     * Get searchable fields
     */
    protected function getSearchableFields(): array
    {
        $searchable = [];
        foreach ($this->getFields() as $name => $field) {
            if (in_array($field['type'], ['text', 'email', 'textarea'])) {
                $searchable[] = $name;
            }
        }
        return $searchable ?: ['id'];
    }

    /**
     * Get table columns to display
     */
    protected function getTableColumns(): array
    {
        return array_keys($this->getFields());
    }

    /**
     * Boot the trait
     */
    public function bootHasCrud()
    {
        $this->initializeFieldMapping();
        $this->initializeFields();
    }

    /**
     * Mount component
     */
    public function mountHasCrud()
    {
        $this->initializeFieldMapping();
        $this->initializeFields();
        $this->perPage = config('hyro.livewire.pagination.per_page', 15);
    }

    /**
     * Initialize field mapping for dynamic field handling
     */
    protected function initializeFieldMapping(): void
    {
        foreach ($this->getFields() as $formField => $config) {
            $modelField = $config['model_field'] ?? $formField;
            $this->fieldMapping[$formField] = $modelField;
            $this->reverseFieldMapping[$modelField] = $formField;
        }
    }

    /**
     * Get the model field name for a form field
     */
    protected function getModelField(string $formField): string
    {
        return $this->fieldMapping[$formField] ?? $formField;
    }

    /**
     * Get the form field name for a model field
     */
    protected function getFormField(string $modelField): string
    {
        return $this->reverseFieldMapping[$modelField] ?? $modelField;
    }

    /**
     * Initialize form fields
     */
    protected function initializeFields(): void
    {
        foreach ($this->getFields() as $field => $config) {
            if (!property_exists($this, $field)) {
                $this->{$field} = $this->getDefaultValue($config);
            }
        }
    }

    /**
     * Get default value based on field type
     */
    protected function getDefaultValue(array $config)
    {
        $type = $config['type'] ?? 'text';
        $default = $config['default'] ?? null;

        if ($default !== null) {
            return $default;
        }

        return match($type) {
            'number', 'decimal' => 0,
            'checkbox' => false,
            'select' => null,
            'textarea' => '',
            default => '',
        };
    }

    /**
     * Render the component - Uses Hyro default layout like Filament
     */
    public function render()
    {
        return view($this->getViewName(), [
            'items' => $this->getItems(),
            'columns' => $this->getTableColumns(),
            'fields' => $this->getFields(),
            'resourceName' => $this->getResourceName(),
            'resourceNamePlural' => $this->getResourceNamePlural(),
        ])->layout($this->getLayout());
    }

    /**
     * Get the layout for the component
     * Priority: Component property > Config > Hyro default
     */
    protected function getLayout(): string
    {
        // 1. Check if component has custom layout property
        if (property_exists($this, 'layout') && !empty($this->layout)) {
            return $this->layout;
        }

        // 2. Check config for custom layout
        $configLayout = config('hyro.livewire.layout');
        if ($configLayout && view()->exists($configLayout)) {
            return $configLayout;
        }

        // 3. Use Hyro default layout (like Filament does)
        return 'hyro::admin.layouts.app';
    }

    /**
     * Get resource name
     */
    protected function getResourceName(): string
    {
        $className = class_basename($this);
        $name = str_ends_with($className, 'Manager') ? substr($className, 0, -7) : $className;
        return Str::title(Str::snake($name, ' '));
    }

    /**
     * Get resource name in kebab case
     */
    protected function getResourceNameKebab(): string
    {
        $className = class_basename($this);
        $name = str_ends_with($className, 'Manager') ? substr($className, 0, -7) : $className;
        return Str::kebab($name);
    }

    /**
     * Get resource name plural
     */
    protected function getResourceNamePlural(): string
    {
        return Str::plural($this->getResourceName());
    }

    /**
     * Get paginated items with search and sorting
     */
    protected function getItems()
    {
        $model = $this->getModel();
        $query = $model::query();

        // Apply search
        if ($this->search && !empty($this->getSearchableFields())) {
            $query->where(function ($q) {
                foreach ($this->getSearchableFields() as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        // Apply sorting
        if ($this->sortField && in_array($this->sortField, $this->getTableColumns())) {
            $query->orderBy($this->sortField, $this->sortDirection === 'asc' ? 'asc' : 'desc');
        }

        // Apply custom filters
        $this->applyFilters($query);

        // Apply relationships
        if (method_exists($this, 'withRelationships')) {
            $query->with($this->withRelationships());
        }

        return $query->paginate($this->perPage);
    }

    /**
     * Apply custom filters (override in component)
     */
    protected function applyFilters($query): void
    {
        // Override in child class
    }

    /**
     * Create new record
     */
    public function create()
    {
        $this->resetFields();
        $this->isEditing = false;
        $this->showModal = true;
        $this->dispatch('modal-opened', mode: 'create');
    }

    /**
     * Edit existing record
     */
    public function edit($id)
    {
        $model = $this->getModel();
        $record = $model::find($id);

        if (!$record) {
            $this->alert('error', 'Record not found.');
            return;
        }

        // Check view permission
        if (method_exists($this, 'canView') && !$this->canView($record)) {
            $this->alert('error', 'You do not have permission to view this record.');
            return;
        }

        $this->modelId = $id;
        $this->isEditing = true;

        // Enhanced: Populate form fields using field mapping
        foreach ($this->getFields() as $field => $config) {
            $modelField = $this->getModelField($field);
            
            if (isset($record->{$modelField})) {
                $this->{$field} = $record->{$modelField};
            } else {
                $this->{$field} = $config['default'] ?? $this->getDefaultValue($config);
            }
        }

        // Load additional data for edit
        if (method_exists($this, 'loadEditData')) {
            $this->loadEditData($record);
        }

        $this->showModal = true;
        $this->dispatch('modal-opened', mode: 'edit', id: $id);
    }

    /**
     * Save record (create or update) with enhanced error handling
     */
    public function save()
    {
        Log::info('Starting save process', [
            'isEditing' => $this->isEditing,
            'modelId' => $this->modelId,
            'model' => $this->getModel(),
        ]);

        try {
            // Validate input
            $this->validate($this->rules());

            $model = $this->getModel();
            $data = $this->getFormData();

            Log::info('Form data prepared:', $data);

            // Before save hook
            if (method_exists($this, 'beforeSave')) {
                $data = $this->beforeSave($data);
                Log::info('After beforeSave hook:', $data);
            }

            if ($this->isEditing) {
                $record = $model::find($this->modelId);

                if (!$record) {
                    $this->alert('error', 'Record not found.');
                    return;
                }

                // Check permissions
                if (method_exists($this, 'canUpdate') && !$this->canUpdate($record)) {
                    $this->alert('error', 'You do not have permission to update this record.');
                    return;
                }

                // Before update hook
                if (method_exists($this, 'beforeUpdate')) {
                    $data = $this->beforeUpdate($record, $data);
                }

                // Handle file cleanup for updates
                $this->handleFileUpdates($record, $data);

                $record->update($data);

                // After update hook
                if (method_exists($this, 'afterUpdate')) {
                    $this->afterUpdate($record);
                }

                Log::info('Record updated successfully:', [
                    'id' => $record->id,
                    'model' => get_class($record),
                ]);

                $this->alert('success', $this->getResourceName() . ' updated successfully!');
                $this->dispatch('record-updated', id: $record->id);
            } else {
                // Check permissions
                if (method_exists($this, 'canCreate') && !$this->canCreate()) {
                    $this->alert('error', 'You do not have permission to create records.');
                    return;
                }

                // Before create hook
                if (method_exists($this, 'beforeCreate')) {
                    $data = $this->beforeCreate($data);
                }

                // Create the record
                Log::info('Creating record with data:', $data);
                $record = $model::create($data);

                if (!$record) {
                    throw new \Exception('Failed to create record. Database returned null.');
                }

                Log::info('Record created successfully:', [
                    'id' => $record->id,
                    'model' => get_class($record),
                    'data' => $record->toArray(),
                ]);

                // After create hook
                if (method_exists($this, 'afterCreate')) {
                    $this->afterCreate($record);
                }

                $this->alert('success', $this->getResourceName() . ' created successfully!');
                $this->dispatch('record-created', id: $record->id);
            }

            $this->closeModal();
            $this->resetFields();

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed:', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Save Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? [],
                'model' => $model ?? null,
            ]);
            $this->alert('error', 'An error occurred while saving: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced form data preparation with field mapping
     */
    protected function getFormData(): array
    {
        $data = [];
        $fields = $this->getFields();

        foreach ($fields as $formField => $config) {
            $modelField = $this->getModelField($formField);
            $fieldType = $config['type'] ?? 'text';
            $value = $this->{$formField} ?? $config['default'] ?? null;

            // Handle file uploads
            if (in_array($fieldType, ['file', 'image']) && $value) {
                if (is_object($value) && method_exists($value, 'store')) {
                    $disk = $config['disk'] ?? 'public';
                    $path = $config['storage_path'] ?? $this->getResourceNameKebab();
                    
                    $data[$modelField] = $value->store($path, $disk);
                    
                    Log::info('File uploaded:', [
                        'field' => $modelField,
                        'path' => $data[$modelField],
                        'disk' => $disk,
                    ]);
                } else {
                    $data[$modelField] = $value;
                }
            } 
            // Handle JSON fields
            elseif ($fieldType === 'json' && is_array($value)) {
                $data[$modelField] = json_encode($value);
            }
            // Handle all other field types
            else {
                $data[$modelField] = $value;
                
                // Convert empty strings to null for nullable fields
                if ($value === '' && !str_contains($config['rules'] ?? '', 'required')) {
                    $data[$modelField] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Handle file updates - delete old files when replaced
     */
    protected function handleFileUpdates($record, array &$data): void
    {
        foreach ($this->getFields() as $field => $config) {
            if (($config['type'] === 'file' || $config['type'] === 'image') && isset($data[$field])) {
                // If new file uploaded and old exists, delete old
                if ($this->{$field} && $record->{$field} && $this->{$field} !== $record->{$field}) {
                    if (is_object($this->{$field}) && method_exists($this->{$field}, 'getRealPath')) {
                        // New file uploaded, delete old
                        Storage::disk($config['disk'] ?? 'public')->delete($record->{$field});
                    }
                }

                // If field is being cleared
                if ($data[$field] === null && $record->{$field}) {
                    Storage::disk($config['disk'] ?? 'public')->delete($record->{$field});
                }
            }
        }
    }

    /**
     * Confirm delete
     */
    public function confirmDelete($id)
    {
        $this->modelId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Delete record
     */
    public function delete()
    {
        $model = $this->getModel();

        // Try to find with or without trashed
        $record = method_exists($model, 'withTrashed')
            ? $model::withTrashed()->find($this->modelId)
            : $model::find($this->modelId);

        if (!$record) {
            $this->alert('error', 'Record not found.');
            $this->showDeleteModal = false;
            return;
        }

        // Check permissions
        if (method_exists($this, 'canDelete') && !$this->canDelete($record)) {
            $this->alert('error', 'You do not have permission to delete this record.');
            $this->showDeleteModal = false;
            return;
        }

        // Before delete hook
        if (method_exists($this, 'beforeDelete')) {
            $continue = $this->beforeDelete($record);
            if ($continue === false) {
                $this->showDeleteModal = false;
                return;
            }
        }

        try {
            // Handle file cleanup
            foreach ($this->getFields() as $field => $config) {
                if (($config['type'] === 'file' || $config['type'] === 'image') && $record->{$field}) {
                    Storage::disk($config['disk'] ?? 'public')->delete($record->{$field});
                }
            }

            if (method_exists($record, 'trashed') && $record->trashed()) {
                $record->forceDelete();
            } else {
                $record->delete();
            }

            // After delete hook
            if (method_exists($this, 'afterDelete')) {
                $this->afterDelete($this->modelId);
            }

            $this->alert('success', $this->getResourceName() . ' deleted successfully!');
            $this->dispatch('record-deleted', id: $this->modelId);

        } catch (\Exception $e) {
            Log::error('CRUD Delete Error: ' . $e->getMessage());
            $this->alert('error', 'An error occurred while deleting the record.');
        }

        $this->showDeleteModal = false;
        $this->modelId = null;
        $this->resetPage();
    }

    /**
     * Confirm bulk delete
     */
    public function confirmBulkDelete()
    {
        if (empty($this->selectedRows)) {
            $this->alert('warning', 'No records selected!');
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    /**
     * Bulk delete
     */
    public function bulkDelete()
    {
        if (empty($this->selectedRows)) {
            $this->alert('warning', 'No records selected!');
            return;
        }

        $model = $this->getModel();
        $records = $model::whereIn('id', $this->selectedRows)->get();

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($records as $record) {
            // Check permissions for each record
            if (method_exists($this, 'canDelete') && !$this->canDelete($record)) {
                $failedCount++;
                continue;
            }

            try {
                // Handle file cleanup
                foreach ($this->getFields() as $field => $config) {
                    if (($config['type'] === 'file' || $config['type'] === 'image') && $record->{$field}) {
                        Storage::disk($config['disk'] ?? 'public')->delete($record->{$field});
                    }
                }

                $record->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Bulk Delete Error for ID ' . $record->id . ': ' . $e->getMessage());
            }
        }

        if ($deletedCount > 0) {
            $this->alert('success', "{$deletedCount} records deleted successfully!");
        }

        if ($failedCount > 0) {
            $this->alert('warning', "{$failedCount} records could not be deleted due to permissions or errors.");
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        $this->showBulkDeleteModal = false;
        $this->dispatch('bulk-deleted', count: $deletedCount);
    }

    /**
     * Sort by field
     */
    public function sortBy($field)
    {
        if (!in_array($field, $this->getTableColumns())) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Toggle select all
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getItems()->pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    /**
     * Close modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showBulkDeleteModal = false;
        $this->resetFields();
        $this->resetValidation();
        $this->dispatch('modal-closed');
    }

    /**
     * Reset form fields
     */
    public function resetFields()
    {
        $this->modelId = null;
        $this->isEditing = false;

        foreach ($this->getFields() as $field => $config) {
            $this->{$field} = $this->getDefaultValue($config);
        }

        // Clean up temporary uploads
        foreach ($this->temporaryFiles as $file) {
            if (method_exists($file, 'delete')) {
                $file->delete();
            }
        }
        $this->temporaryFiles = [];

        // Reset additional fields
        if (method_exists($this, 'resetAdditionalFields')) {
            $this->resetAdditionalFields();
        }
    }

    /**
     * Get validation rules
     */
    protected function rules(): array
    {
        $rules = [];

        foreach ($this->getFields() as $field => $config) {
            if (isset($config['rules'])) {
                $fieldRules = $config['rules'];

                // Handle unique rule for updates
                if ($this->isEditing && is_string($fieldRules) && Str::contains($fieldRules, 'unique:')) {
                    $fieldRules = $this->handleUniqueRule($fieldRules, $field);
                }

                $rules[$field] = $fieldRules;
            }
        }

        // Merge with component-specific rules
        if (method_exists($this, 'additionalRules')) {
            $rules = array_merge($rules, $this->additionalRules());
        }

        return $rules;
    }

    /**
     * Handle unique validation rule for updates
     */
    protected function handleUniqueRule(string $rules, string $field): string
    {
        // Parse unique:table,column format and add ignore clause
        if (preg_match('/unique:([^,]+)(?:,([^|]+))?/', $rules, $matches)) {
            $table = $matches[1];
            $column = $matches[2] ?? $field;
            $ignoreId = $this->modelId ?? 'NULL';

            $replacement = "unique:{$table},{$column},{$ignoreId}";
            $rules = preg_replace('/unique:[^|]+/', $replacement, $rules);
        }
        return $rules;
    }

    /**
     * Get validation attributes
     */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->getFields() as $field => $config) {
            $attributes[$field] = $config['label'] ?? Str::title(str_replace('_', ' ', $field));
        }

        return $attributes;
    }

    /**
     * Get view name
     */
    protected function getViewName(): string
    {
        $componentName = class_basename($this);
        return 'livewire.admin.' . Str::kebab($componentName);
    }

    /**
     * Export to CSV
     */
    public function exportCsv()
    {
        $model = $this->getModel();
        $query = $model::query();

        // Apply current filters to export
        if ($this->search && !empty($this->getSearchableFields())) {
            $query->where(function ($q) {
                foreach ($this->getSearchableFields() as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        $items = $query->get();
        $columns = $this->getTableColumns();

        $filename = Str::slug($this->getResourceNamePlural()) . '-' . now()->format('Y-m-d-His') . '.csv';

        $handle = fopen('php://temp', 'r+');

        // Header
        $headers = array_map(fn($col) => Str::title(str_replace('_', ' ', $col)), $columns);
        fputcsv($handle, $headers);

        // Data
        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $column) {
                $value = $item->{$column};
                // Handle arrays/objects
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                $row[] = $value;
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Search updated
     */
    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    /**
     * Per page updated
     */
    public function updatedPerPage()
    {
        $this->resetPage();
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    /**
     * Reset page when filters change
     */
    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'filter')) {
            $this->resetPage();
        }
    }

    /**
     * Query string parameters for persistent state
     */
    protected function queryStringHasCrud(): array
    {
        return [
            'search' => ['except' => ''],
            'sortField' => ['except' => 'created_at'],
            'sortDirection' => ['except' => 'desc'],
            'perPage' => ['except' => 15],
        ];
    }

    /**
     * Enhanced: Debug method to check field mapping
     */
    public function debugFieldMapping()
    {
        $this->initializeFieldMapping();
        
        return [
            'fields' => $this->getFields(),
            'field_mapping' => $this->fieldMapping,
            'reverse_mapping' => $this->reverseFieldMapping,
            'form_data_sample' => $this->getFormData(),
            'model' => $this->getModel(),
            'fillable' => method_exists($this->getModel(), 'getFillable') ? 
                (new ($this->getModel()))->getFillable() : [],
        ];
    }

    /**
     * Enhanced: Dynamically get table columns with labels
     */
    protected function getTableColumnsWithLabels(): array
    {
        $columns = [];
        
        foreach ($this->getFields() as $field => $config) {
            $columns[$field] = [
                'label' => $config['label'] ?? Str::title(str_replace('_', ' ', $field)),
                'type' => $config['type'] ?? 'text',
                'sortable' => $config['sortable'] ?? true,
            ];
        }
        
        return $columns;
    }

    /**
     * Enhanced: Get field configuration for a specific field
     */
    protected function getFieldConfig(string $fieldName): ?array
    {
        $fields = $this->getFields();
        return $fields[$fieldName] ?? null;
    }

    /**
     * Enhanced: Validate if all required fields are filled
     */
    protected function validateRequiredFields(): bool
    {
        foreach ($this->getFields() as $field => $config) {
            if (str_contains($config['rules'] ?? '', 'required')) {
                if (empty($this->{$field})) {
                    $this->addError($field, 'This field is required.');
                    return false;
                }
            }
        }
        return true;
    }
}