<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire\Admin;

use Marufsharia\Hyro\Livewire\BaseCrudComponent;
use Marufsharia\Hyro\Core\Models\Role;
use Marufsharia\Hyro\Core\Models\Privilege;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleManager extends BaseCrudComponent
{
    public $privileges = [];
    public $selectedPrivileges = [];

    /**
     * Get the model class name.
     */
    protected function getModel(): string
    {
        return Role::class;
    }

    /**
     * Get the field definitions for the form.
     */
    protected function getFields(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'Name',
                'required' => true,
                'rules' => 'required|string|max:255|unique:hyro_roles,name,{id}',
            ],
            'slug' => [
                'type' => 'text',
                'label' => 'Slug',
                'required' => true,
                'rules' => 'required|string|max:255|unique:hyro_roles,slug,{id}|alpha_dash',
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'required' => false,
                'rules' => 'nullable|string|max:500',
            ],
            'selectedPrivileges' => [
                'type' => 'array',
                'label' => 'Privileges',
                'required' => false,
                'rules' => 'array',
                'model_field' => 'privileges',
            ],
        ];
    }

    /**
     * Get searchable fields for filtering.
     */
    protected function getSearchableFields(): array
    {
        return ['name', 'slug'];
    }

    /**
     * Mount the component.
     */
    public function mount()
    {
        $this->privileges = Privilege::orderBy('name')->get()->toArray();
    }

    /**
     * Override edit to load privileges relationship.
     */
    public function edit($id)
    {
        $role = Role::with('privileges')->findOrFail($id);
        $this->selectedPrivileges = $role->privileges->pluck('id')->toArray();
        
        parent::edit($id);
    }

    /**
     * Override save to sync privileges.
     */
    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = [
                    'name' => $this->name,
                    'slug' => $this->slug,
                    'description' => $this->description,
                ];

                if ($this->isEditing) {
                    $role = Role::findOrFail($this->modelId);
                    $role->update($data);
                    $message = 'Role updated successfully.';
                } else {
                    $role = Role::create($data);
                    $message = 'Role created successfully.';
                }

                // Sync privileges
                $role->privileges()->sync($this->selectedPrivileges);

                session()->flash('success', $message);
            });

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Role save error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Override delete to add super admin protection.
     */
    public function delete()
    {
        try {
            $role = Role::findOrFail($this->deleteId);
            
            // Prevent deletion of super admin role
            $adminRole = Role::where('slug', config('hyro.super_admin_role', 'super-admin'))->first();
            
            if ($adminRole && $role->id === $adminRole->id && $adminRole->users()->count() > 0) {
                session()->flash('error', 'Cannot delete the super admin role while it has assigned users.');
                return;
            }

            $role->delete();

            session()->flash('success', 'Role deleted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Role delete error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while deleting the role.');
        }
    }

    /**
     * Get the view name for rendering.
     */
    protected function getViewName(): string
    {
        return 'hyro::admin.roles.manager';
    }

    /**
     * Get the layout for the component.
     */
    protected function getLayout(): string
    {
        return 'hyro::admin.layouts.app';
    }
}
