<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Facades\Log;

class PrivilegeManager extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editMode = false;
    public $privilegeId;
    public $name = '';
    public $slug = '';
    public $category = '';
    public $description = '';
    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        $privilegeId = $this->privilegeId ?? 'NULL';
        
        return [
            'name' => 'required|string|max:255|unique:hyro_privileges,name,' . $privilegeId,
            'slug' => 'required|string|max:255|unique:hyro_privileges,slug,' . $privilegeId . '|alpha_dash',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $privilege = Privilege::findOrFail($id);
        
        $this->privilegeId = $privilege->id;
        $this->name = $privilege->name;
        $this->slug = $privilege->slug;
        $this->category = $privilege->category ?? '';
        $this->description = $privilege->description ?? '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $privilege = Privilege::findOrFail($this->privilegeId);
                $privilege->update([
                    'name' => $this->name,
                    'slug' => $this->slug,
                    'category' => $this->category,
                    'description' => $this->description,
                ]);
                $message = 'Privilege updated successfully.';
            } else {
                Privilege::create([
                    'name' => $this->name,
                    'slug' => $this->slug,
                    'category' => $this->category,
                    'description' => $this->description,
                ]);
                $message = 'Privilege created successfully.';
            }

            session()->flash('success', $message);

            $this->resetForm();
            $this->showModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Privilege save error: ' . $e->getMessage());
            
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $privilege = Privilege::findOrFail($id);
            
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

    public function resetForm()
    {
        $this->privilegeId = null;
        $this->name = '';
        $this->slug = '';
        $this->category = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $privileges = Privilege::withCount('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%')
                    ->orWhere('category', 'like', '%' . $this->search . '%');
            })
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return view('hyro::admin.privileges.manager', [
            'privileges' => $privileges,
        ])->layout('hyro::admin.layouts.app');
    }
}
