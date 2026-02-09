<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Marufsharia\Hyro\Livewire\BaseCrudComponent;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Str;

class PrivilegeManager extends BaseCrudComponent
{
    public $name;
    public $slug;
    public $description;
    public $category;

    // Filter
    public $filterCategory = '';

    protected function getModel(): string
    {
        return config('hyro.database.models.privilege', Privilege::class);
    }

    protected function getFields(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'Privilege Name',
                'rules' => 'required|string|max:255',
                'default' => '',
                'help' => 'Display name of the privilege',
            ],
            'slug' => [
                'type' => 'text',
                'label' => 'Slug',
                'rules' => 'required|string|max:255|unique:' . config('hyro.database.tables.privileges', 'hyro_privileges') . ',slug',
                'default' => '',
                'help' => 'Use dot notation (e.g., posts.create, users.delete)',
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'rules' => 'nullable|string|max:500',
                'default' => '',
                'help' => 'Brief description of what this privilege allows',
            ],
            'category' => [
                'type' => 'text',
                'label' => 'Category',
                'rules' => 'nullable|string|max:255',
                'default' => '',
                'help' => 'Group privileges by category (e.g., Users, Posts, Settings)',
            ],
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'slug', 'description', 'category'];
    }

    protected function getTableColumns(): array
    {
        return ['name', 'slug', 'category', 'roles_count', 'created_at'];
    }

    protected function withRelationships(): array
    {
        return ['roles'];
    }

    public function updatedName($value)
    {
        if (!$this->isEditing && $value) {
            // Convert to dot notation for slug
            $this->slug = Str::slug($value, '.');
        }
    }

    public function updatedCategory($value)
    {
        if (!$this->isEditing && $value && $this->name) {
            // Auto-update slug with category
            $this->slug = Str::slug($value, '.') . '.' . Str::slug($this->name, '.');
        }
    }

    protected function canUpdate($record): bool
    {
        return auth()->user()->hasPrivilege('privileges.update');
    }

    protected function canDelete($record): bool
    {
        return auth()->user()->hasPrivilege('privileges.delete');
    }

    protected function beforeDelete($record): bool
    {
        // Check if privilege is assigned to any roles
        if ($record->roles()->count() > 0) {
            $this->alert('error', 'Cannot delete privilege assigned to roles. Remove from roles first.');
            $this->showDeleteModal = false;
            return false;
        }

        return true;
    }

    protected function afterCreate($record)
    {
        event('hyro.privilege.created', [$record]);
    }

    protected function afterUpdate($record)
    {
        event('hyro.privilege.updated', [$record]);

        // Invalidate cache for all roles that have this privilege
        if (config('hyro.cache.enabled', true)) {
            foreach ($record->roles as $role) {
                \Cache::forget(config('hyro.cache.prefix', 'hyro:') . "role.{$role->id}");
            }
        }
    }

    /**
     * Apply custom filters to the query
     *
     * @param mixed $query
     * @return void
     */
    protected function applyFilters($query): void
    {
        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }
    }

    public function getCategoriesProperty()
    {
        return $this->getModel()::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->orderBy('category')
            ->pluck('category');
    }

    public function filterByCategory($category)
    {
        $this->filterCategory = $category;
        $this->resetPage();
    }

    public function clearCategoryFilter()
    {
        $this->filterCategory = '';
        $this->resetPage();
    }

    // Bulk operations for privileges
    public function bulkAssignToRole($roleId)
    {
        if (empty($this->selectedRows)) {
            $this->alert('warning', 'No privileges selected!');
            return;
        }

        $roleModel = config('hyro.database.models.role');
        $role = $roleModel::findOrFail($roleId);

        $role->privileges()->syncWithoutDetaching($this->selectedRows);

        $this->alert('success', count($this->selectedRows) . ' privileges assigned to role!');
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    // Generate common privileges
    public function generateResourcePrivileges($resourceName)
    {
        $actions = ['view', 'create', 'update', 'delete'];
        $created = [];

        foreach ($actions as $action) {
            $slug = Str::slug($resourceName, '.') . '.' . $action;

            // Check if already exists
            if ($this->getModel()::where('slug', $slug)->exists()) {
                continue;
            }

            $privilege = $this->getModel()::create([
                'name' => ucfirst($action) . ' ' . ucfirst($resourceName),
                'slug' => $slug,
                'description' => "Allow {$action} operations on {$resourceName}",
                'category' => ucfirst($resourceName),
            ]);

            $created[] = $privilege->name;
        }

        if (count($created) > 0) {
            $this->alert('success', 'Created ' . count($created) . ' privileges for ' . $resourceName);
        } else {
            $this->alert('info', 'All privileges for ' . $resourceName . ' already exist');
        }
    }
}
