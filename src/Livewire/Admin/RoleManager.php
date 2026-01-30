<?php

namespace MarufSharia\Hyro\Livewire\Admin;

use MarufSharia\Hyro\Livewire\BaseCrudComponent;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Str;

class RoleManager extends BaseCrudComponent
{
    public $name;
    public $slug;
    public $description;
    public $is_protected = false;

    // Privilege assignment
    public $selectedPrivileges = [];
    public $availablePrivileges = [];

    protected function getModel(): string
    {
        return config('hyro.database.models.role', Role::class);
    }

    protected function getFields(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'Role Name',
                'rules' => 'required|string|max:255',
                'default' => '',
                'help' => 'Display name of the role',
            ],
            'slug' => [
                'type' => 'text',
                'label' => 'Slug',
                'rules' => 'required|string|max:255|unique:' . config('hyro.database.tables.roles', 'hyro_roles') . ',slug',
                'default' => '',
                'help' => 'Unique identifier (auto-generated from name)',
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'rules' => 'nullable|string|max:500',
                'default' => '',
                'help' => 'Brief description of this role',
            ],
            'is_protected' => [
                'type' => 'checkbox',
                'label' => 'Protected Role',
                'rules' => 'boolean',
                'default' => false,
                'help' => 'Protected roles cannot be deleted',
            ],
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function getTableColumns(): array
    {
        return ['name', 'slug', 'description', 'users_count', 'privileges_count', 'created_at'];
    }

    protected function withRelationships(): array
    {
        return ['users', 'privileges'];
    }

    public function mount()
    {
        parent::mount();
        $this->loadAvailablePrivileges();
    }

    protected function loadAvailablePrivileges()
    {
        $privilegeModel = config('hyro.database.models.privilege', Privilege::class);
        $this->availablePrivileges = $privilegeModel::orderBy('name')->get();
    }

    public function updatedName($value)
    {
        if (!$this->isEditing && $value) {
            $this->slug = Str::slug($value);
        }
    }

    protected function loadEditData($record)
    {
        $this->selectedPrivileges = $record->privileges->pluck('id')->toArray();
    }

    protected function canUpdate($record): bool
    {
        if ($record->is_protected && !auth()->user()->hasPrivilege('roles.edit-protected')) {
            return false;
        }
        return auth()->user()->hasPrivilege('roles.update');
    }

    protected function canDelete($record): bool
    {
        // Cannot delete protected roles
        if ($record->is_protected) {
            return false;
        }

        // Cannot delete if it's the last admin role
        if (config('hyro.security.fail_closed', true)) {
            $protectedRoles = config('hyro.security.protected_roles', []);
            if (in_array($record->slug, $protectedRoles)) {
                $count = $this->getModel()::whereIn('slug', $protectedRoles)->count();
                if ($count === 1) {
                    return false;
                }
            }
        }

        return auth()->user()->hasPrivilege('roles.delete');
    }

    protected function beforeDelete($record): bool
    {
        // Check if role has users
        if ($record->users()->count() > 0) {
            $this->alert('error', 'Cannot delete role with assigned users. Reassign users first.');
            $this->showDeleteModal = false;
            return false;
        }

        return true;
    }

    protected function afterCreate($record)
    {
        // Sync privileges
        $record->privileges()->sync($this->selectedPrivileges);

        // Fire event
        event('hyro.role.created', [$record]);
    }

    protected function afterUpdate($record)
    {
        // Sync privileges
        $record->privileges()->sync($this->selectedPrivileges);

        // Fire event
        event('hyro.role.updated', [$record]);

        // Invalidate cache
        if (config('hyro.cache.enabled', true)) {
            \Cache::forget(config('hyro.cache.prefix', 'hyro:') . "role.{$record->id}");
        }
    }

    protected function resetAdditionalFields()
    {
        $this->selectedPrivileges = [];
    }

    /**
     * Apply custom filters to the query
     *
     * @param mixed $query
     * @return void
     */
    protected function applyFilters($query): void
    {
        // Add custom filters if needed
        // Example: if ($this->filterStatus) { $query->where('status', $this->filterStatus); }
    }

    public function getItemsProperty()
    {
        return $this->getItems();
    }
}
