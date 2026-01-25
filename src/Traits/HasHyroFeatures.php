<?php


namespace Marufsharia\Hyro\Traits;

use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Facades\Config;

trait HasHyroFeatures
{
    /**
     * The user's roles.
     */
    public function roles()
    {
        $userModel = Config::get('hyro.models.user', \App\Models\User::class);
        $pivotTable = Config::get('hyro.database.tables.role_user', 'hyro_role_user');

        return $this->belongsToMany(Role::class, $pivotTable)
            ->withTimestamps()
            ->withPivot(['assigned_by', 'assigned_at', 'assignment_reason', 'expires_at'])
            ->where(function ($query) use ($pivotTable) {
                $query->whereNull("{$pivotTable}.expires_at")
                    ->orWhere("{$pivotTable}.expires_at", '>', now());
            });
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role): bool
    {
        if ($this->is_super_admin ?? false) {
            return true;
        }

        if (is_string($role)) {
            return $this->roles->contains('slug', $role);
        }

        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        return false;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role, $assignedBy = null)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_by' => $assignedBy?->id ?? auth()->id(),
                'assigned_at' => now(),
            ]);

            event(new \Marufsharia\Hyro\Events\RoleAssigned($this, $role, $assignedBy ?? auth()->user()));
        }

        return $this;
    }

    /**
     * Revoke a role from the user.
     */
    public function removeRole($role, $removedBy = null)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if ($this->hasRole($role)) {
            $this->roles()->detach($role->id);
            event(new \Marufsharia\Hyro\Events\RoleRevoked($this, $role, $removedBy ?? auth()->user()));
        }

        return $this;
    }

    /**
     * Get all privileges for the user.
     */
    public function getAllPrivileges()
    {
        $privilegeIds = $this->privileges()->pluck('id')->toArray();

        foreach ($this->roles as $role) {
            $privilegeIds = array_merge($privilegeIds, $role->privileges()->pluck('id')->toArray());
        }

        return Privilege::whereIn('id', array_unique($privilegeIds))->get();
    }

    /**
     * Check if user has a privilege.
     */
    public function hasPrivilege($privilegeSlug): bool
    {
        return $this->getAllPrivileges()->pluck('slug')->contains($privilegeSlug);
    }
}
