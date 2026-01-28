<?php

namespace Marufsharia\Hyro\Livewire\Traits;

use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Jantinnerezo\LivewireAlert\LivewireAlert;

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
    public $isEditing = false;

    // Search & Filter
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 15;

    // Bulk Actions
    public $selectedRows = [];
    public $selectAll = false;

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
        return ['name', 'title', 'email'];
    }

    /**
     * Get table columns to display
     */
    protected function getTableColumns(): array
    {
        return array_keys($this->getFields());
    }

    /**
     * Mount component
     */
    public function mount()
    {
        $this->initializeFields();
        $this->perPage = config('hyro.livewire.pagination.per_page', 15);
    }

    /**
     * Initialize form fields
     */
    protected function initializeFields()
    {
        foreach ($this->getFields() as $field => $config) {
            if (!property_exists($this, $field)) {
                $this->{$field} = $config['default'] ?? null;
            }
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view($this->getViewName(), [
            'items' => $this->getItems(),
            'columns' => $this->getTableColumns(),
            'fields' => $this->getFields(),
        ])->layout(config('hyro.livewire.layout', 'hyro::admin.layouts.app'));
    }

    /**
     * Get paginated items with search and sorting
     */
    protected function getItems()
    {
        $model = $this->getModel();
        $query = $model::query();

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                foreach ($this->getSearchableFields() as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

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
    protected function applyFilters($query)
    {
        //
    }

    /**
     * Create new record
     */
    public function create()
    {
        $this->resetFields();
        $this->isEditing = false;
        $this->showModal = true;
    }

    /**
     * Edit existing record
     */
    public function edit($id)
    {
        $model = $this->getModel();
        $record = $model::findOrFail($id);

        $this->modelId = $id;
        $this->isEditing = true;

        foreach ($this->getFields() as $field => $config) {
            if (isset($record->{$field})) {
                $this->{$field} = $record->{$field};
            }
        }

        // Load additional data for edit
        if (method_exists($this, 'loadEditData')) {
            $this->loadEditData($record);
        }

        $this->showModal = true;
    }

    /**
     * Save record (create or update)
     */
    public function save()
    {
        $this->validate($this->getRules());

        $model = $this->getModel();
        $data = $this->getFormData();

        // Before save hook
        if (method_exists($this, 'beforeSave')) {
            $data = $this->beforeSave($data);
        }

        if ($this->isEditing) {
            $record = $model::findOrFail($this->modelId);

            // Check permissions
            if (method_exists($this, 'canUpdate') && !$this->canUpdate($record)) {
                $this->alert('error', 'You do not have permission to update this record.');
                return;
            }

            $record->update($data);

            // After update hook
            if (method_exists($this, 'afterUpdate')) {
                $this->afterUpdate($record);
            }

            $this->alert('success', 'Record updated successfully!');
            $this->dispatch('recordUpdated', id: $record->id);
        } else {
            $record = $model::create($data);

            // After create hook
            if (method_exists($this, 'afterCreate')) {
                $this->afterCreate($record);
            }

            $this->alert('success', 'Record created successfully!');
            $this->dispatch('recordCreated', id: $record->id);
        }

        $this->closeModal();
        $this->resetFields();
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
        $record = $model::findOrFail($this->modelId);

        // Check permissions
        if (method_exists($this, 'canDelete') && !$this->canDelete($record)) {
            $this->alert('error', 'You do not have permission to delete this record.');
            return;
        }

        // Before delete hook
        if (method_exists($this, 'beforeDelete')) {
            $continue = $this->beforeDelete($record);
            if ($continue === false) {
                return;
            }
        }

        $record->delete();

        // After delete hook
        if (method_exists($this, 'afterDelete')) {
            $this->afterDelete($this->modelId);
        }

        $this->alert('success', 'Record deleted successfully!');
        $this->dispatch('recordDeleted', id: $this->modelId);

        $this->showDeleteModal = false;
        $this->modelId = null;
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

        // Check permissions for each record
        if (method_exists($this, 'canDelete')) {
            $records = $model::whereIn('id', $this->selectedRows)->get();
            foreach ($records as $record) {
                if (!$this->canDelete($record)) {
                    $this->alert('error', 'You do not have permission to delete some records.');
                    return;
                }
            }
        }

        $count = $model::whereIn('id', $this->selectedRows)->delete();

        $this->alert('success', "{$count} records deleted!");
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    /**
     * Sort by field
     */
    public function sortBy($field)
    {
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
        $this->resetFields();
        $this->resetValidation();
    }

    /**
     * Reset form fields
     */
    protected function resetFields()
    {
        $this->modelId = null;
        $this->isEditing = false;

        foreach ($this->getFields() as $field => $config) {
            $this->{$field} = $config['default'] ?? null;
        }

        // Reset additional fields
        if (method_exists($this, 'resetAdditionalFields')) {
            $this->resetAdditionalFields();
        }
    }

    /**
     * Get validation rules
     */
    protected function getRules(): array
    {
        $rules = [];

        foreach ($this->getFields() as $field => $config) {
            if (isset($config['rules'])) {
                $fieldRules = $config['rules'];

                // Handle unique rule for updates
                if ($this->isEditing && is_string($fieldRules) && Str::contains($fieldRules, 'unique:')) {
                    $table = $this->getTableName();
                    $fieldRules = preg_replace(
                        '/unique:([^,|]+)/',
                        "unique:$1,{$field},{$this->modelId}",
                        $fieldRules
                    );
                }

                $rules[$field] = $fieldRules;
            }
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
     * Get table name
     */
    protected function getTableName(): string
    {
        return (new ($this->getModel()))->getTable();
    }

    /**
     * Get form data
     */
    protected function getFormData(): array
    {
        $data = [];

        foreach ($this->getFields() as $field => $config) {
            if ($config['type'] === 'file' && $this->{$field}) {
                $data[$field] = $this->{$field}->store(
                    $config['storage_path'] ?? 'uploads',
                    $config['disk'] ?? 'public'
                );
            } else {
                $data[$field] = $this->{$field};
            }
        }

        return $data;
    }

    /**
     * Get view name
     */
    protected function getViewName(): string
    {
        $componentName = class_basename($this);
        return 'hyro::livewire.admin.' . Str::kebab($componentName);
    }

    /**
     * Export to CSV
     */
    public function exportCsv()
    {
        $model = $this->getModel();
        $items = $model::all();
        $columns = $this->getTableColumns();

        $filename = Str::slug(class_basename($model)) . '-' . now()->format('Y-m-d-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        // Header
        fputcsv($handle, $columns);

        // Data
        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $column) {
                $row[] = $item->{$column};
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
        ]);
    }

    /**
     * Search updated
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Per page updated
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Query string parameters
     */
    protected function queryString(): array
    {
        return [
            'search' => ['except' => ''],
            'sortField' => ['except' => 'created_at'],
            'sortDirection' => ['except' => 'desc'],
            'perPage' => ['except' => 15],
        ];
    }
}
