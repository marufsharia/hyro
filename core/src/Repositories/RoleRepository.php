<?php

namespace Marufsharia\Hyro\Core\Repositories;

class RoleRepository extends BaseRepository
{
    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return config('hyro.database.models.role', \Marufsharia\Hyro\Models\Role::class);
    }

    /**
     * Apply search filter to query.
     */
    protected function applySearchFilter($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Find role by slug
     */
    public function findBySlug($slug)
    {
        return $this->model::where('slug', $slug)->first();
    }

    /**
     * Assign privilege to role
     */
    public function assignPrivilege($roleId, $privilegeId)
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return false;
        }

        return $role->privileges()->syncWithoutDetaching([$privilegeId]);
    }

    /**
     * Remove privilege from role
     */
    public function removePrivilege($roleId, $privilegeId)
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return false;
        }

        return $role->privileges()->detach($privilegeId);
    }

    /**
     * Get role's privileges
     */
    public function getPrivileges($roleId)
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return collect();
        }

        return $role->privileges;
    }

    /**
     * Sync privileges for role
     */
    public function syncPrivileges($roleId, array $privilegeIds)
    {
        $role = $this->find($roleId);
        
        if (!$role) {
            return false;
        }

        return $role->privileges()->sync($privilegeIds);
    }
}


