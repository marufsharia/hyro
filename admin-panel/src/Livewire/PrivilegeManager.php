<?php

namespace Marufsharia\Hyro\AdminPanel\Livewire;

use Marufsharia\Hyro\Livewire\BaseCrudComponent;
use Marufsharia\Hyro\Core\Models\Privilege;
use Illuminate\Support\Facades\Log;

class PrivilegeManager extends BaseCrudComponent
{
    /**
     * Get the model class name.
     */
    protected function getModel(): string
    {
        return Privilege::class;
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
                'rules' => 'required|string|max:255|unique:hyro_privileges,name,{id}',
            ],
            'slug' => [
                'type' => 'text',
                'label' => 'Slug',
                'required' => true,
                'rules' => 'required|string|max:255|unique:hyro_privileges,slug,{id}|alpha_dash',
            ],
            'category' => [
                'type' => 'text',
                'label' => 'Category',
                'required' => false,
                'rules' => 'nullable|string|max:255',
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'required' => false,
                'rules' => 'nullable|string|max:500',
            ],
        ];
    }

    /**
     * Get searchable fields for filtering.
     */
    protected function getSearchableFields(): array
    {
        return ['name', 'slug', 'category'];
    }

    /**
     * Override delete to add core privilege protection.
     */
    public function delete()
    {
        try {
            $privilege = Privilege::findOrFail($this->deleteId);
            
            // Don't allow deletion of core admin privileges
            $corePrivileges = ['access-hyro-admin', 'view-roles', 'create-roles', 'edit-roles', 'delete-roles'];
            
            if (in_array($privilege->slug, $corePrivileges)) {
                session()->flash('error', 'Cannot delete core admin privileges.');
                return;
            }

            $privilege->delete();

            session()->flash('success', 'Privilege deleted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Privilege delete error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while deleting the privilege.');
        }
    }

    /**
     * Get the view name for rendering.
     */
    protected function getViewName(): string
    {
        return 'hyro::admin.privileges.manager';
    }

    /**
     * Get the layout for the component.
     */
    protected function getLayout(): string
    {
        return 'hyro::admin.layouts.app';
    }
}
