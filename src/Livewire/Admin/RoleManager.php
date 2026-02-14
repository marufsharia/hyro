<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleManager extends Component
{
    use WithPagination;

    public $privileges = [];
    public $showModal = false;
    public $editMode = false;
    public $roleId;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $selectedPrivileges = [];
    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        $roleId = $this->roleId ?? 'NULL';
        
        return [
            'name' => 'required|string|max:255|unique:hyro_roles,name,' . $roleId,
            'slug' => 'required|string|max:255|unique:hyro_roles,slug,' . $roleId . '|alpha_dash',
            'description' => 'nullable|string|max:500',
            'selectedPrivileges' => 'array',
            'selectedPrivileges.*' => 'exists:hyro_privileges,id',
        ];
    }

    public function mount()
    {
        $this->privileges = Privilege::orderBy('name')->get()->toArray();
    }

    public function create()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $role = Role::with('privileges')->findOrFail($id);
        
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->slug = $role->slug;
        $this->description = $role->description ?? '';
        $this->selectedPrivileges = $role->privileges->pluck('id')->toArray();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                if ($this->editMode) {
                    $role = Role::findOrFail($this->roleId);
                    $role->update([
                        'name' => $this->name,
                        'slug' => $this->slug,
                        'description' => $this->description,
                    ]);
                    $role->privileges()->sync($this->selectedPrivileges);
                    $message = 'Role updated successfully.';
                } else {
                    $role = Role::create([
                        'name' => $this->name,
                        'slug' => $this->slug,
                        'description' => $this->description,
                    ]);
                    $role->privileges()->sync($this->selectedPrivileges);
                    $message = 'Role created successfully.';
                }

                session()->flash('success', $message);
            });

            $this->resetForm();
            $this->showModal = false;
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Role save error: ' . $e->getMessage());
            
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $role = Role::findOrFail($id);
            
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

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->selectedPrivileges = [];
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $roles = Role::withCount(['users', 'privileges'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('hyro::admin.roles.manager', [
            'roles' => $roles,
        ])->layout('hyro::admin.layouts.app');
    }
}
