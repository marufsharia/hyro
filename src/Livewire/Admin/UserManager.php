<?php

namespace HyroPlugins\PhoneBook\Livewire\Admin;

use HyroPlugins\PhoneBook\Livewire\BaseCrudComponent;
use Marufsharia\Hyro\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserManager extends BaseCrudComponent
{
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    // Role assignment
    public $selectedRoles = [];
    public $availableRoles = [];

    // Suspension
    public $showSuspendModal = false;
    public $suspension_reason;
    public $suspension_user_id;

    protected function getModel(): string
    {
        return config('hyro.database.models.users', \App\Models\User::class);
    }

    protected function getFields(): array
    {
        return [
            'name' => [
                'type' => 'text',
                'label' => 'Full Name',
                'rules' => 'required|string|max:255',
                'default' => '',
            ],
            'email' => [
                'type' => 'email',
                'label' => 'Email Address',
                'rules' => 'required|email|max:255|unique:users,email',
                'default' => '',
            ],
            'password' => [
                'type' => 'password',
                'label' => 'Password',
                'rules' => 'required|string|min:8|confirmed',
                'default' => '',
                'help' => 'Minimum 8 characters',
            ],
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email'];
    }

    protected function getTableColumns(): array
    {
        return ['name', 'email', 'roles', 'status', 'created_at'];
    }

    protected function withRelationships(): array
    {
        return ['roles', 'suspensions'];
    }

    public function mount()
    {
        parent::mount();
        $this->loadAvailableRoles();
    }

    protected function loadAvailableRoles()
    {
        $roleModel = config('hyro.database.models.role', Role::class);
        $this->availableRoles = $roleModel::orderBy('name')->get();
    }

    /**
     * Get validation rules
     */
    public function getRules(): array  // CHANGED: protected to public
    {
        $rules = parent::getRules();

        // Password is optional on edit
        if ($this->isEditing) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }

    protected function getFormData(): array
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        return $data;
    }

    protected function loadEditData($record)
    {
        $this->selectedRoles = $record->roles->pluck('id')->toArray();
    }

    protected function canUpdate($record): bool
    {
        return auth()->user()->hasPrivilege('users.update') || auth()->id() === $record->id;
    }

    protected function canDelete($record): bool
    {
        // Cannot delete yourself
        if (auth()->id() === $record->id) {
            return false;
        }

        return auth()->user()->hasPrivilege('users.delete');
    }

    protected function afterCreate($record)
    {
        // Sync roles
        $record->roles()->sync($this->selectedRoles);

        // Fire event
        event('hyro.user.created', [$record]);
    }

    protected function afterUpdate($record)
    {
        // Sync roles
        $record->roles()->sync($this->selectedRoles);

        // Fire event
        event('hyro.user.updated', [$record]);

        // Invalidate cache
        if (config('hyro.cache.enabled', true)) {
            \Cache::forget(config('hyro.cache.prefix', 'hyro:') . "user.{$record->id}.roles");
            \Cache::forget(config('hyro.cache.prefix', 'hyro:') . "user.{$record->id}.privileges");
        }
    }

    protected function resetAdditionalFields()
    {
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = [];
    }

    // Suspension Methods
    public function confirmSuspend($id)
    {
        $this->suspension_user_id = $id;
        $this->showSuspendModal = true;
    }

    public function suspend()
    {
        $this->validate([
            'suspension_reason' => 'required|string|max:500',
        ]);

        $model = $this->getModel();
        $user = $model::findOrFail($this->suspension_user_id);

        if (!auth()->user()->hasPrivilege('users.suspend')) {
            $this->alert('error', 'You do not have permission to suspend users.');
            return;
        }

        $user->suspend($this->suspension_reason);

        $this->alert('warning', 'User suspended successfully!');
        $this->dispatch('userSuspended', id: $this->suspension_user_id);

        $this->showSuspendModal = false;
        $this->suspension_user_id = null;
        $this->suspension_reason = '';
    }

    public function unsuspend($id)
    {
        $model = $this->getModel();
        $user = $model::findOrFail($id);

        if (!auth()->user()->hasPrivilege('users.suspend')) {
            $this->alert('error', 'You do not have permission to unsuspend users.');
            return;
        }

        $user->unsuspend();

        $this->alert('success', 'User unsuspended successfully!');
        $this->dispatch('userUnsuspended', id: $id);
    }

    public function closeSuspendModal()
    {
        $this->showSuspendModal = false;
        $this->suspension_user_id = null;
        $this->suspension_reason = '';
    }
}
