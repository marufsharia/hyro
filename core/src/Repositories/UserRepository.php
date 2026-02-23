<?php

namespace Marufsharia\Hyro\Core\Repositories;

use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return config('hyro.database.models.user', \App\Models\User::class);
    }

    /**
     * Apply filters to the query.
     */
    protected function applyFilters($query, array $filters)
    {
        parent::applyFilters($query, $filters);

        if (isset($filters['role'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('slug', $filters['role']);
            });
        }

        return $query;
    }

    /**
     * Apply search filter to query.
     */
    protected function applySearchFilter($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Prepare data before creating.
     */
    protected function prepareDataForCreate(array $data): array
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    /**
     * Prepare data before updating.
     */
    protected function prepareDataForUpdate(array $data): array
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()->syncWithoutDetaching([$roleId]);
    }

    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()->detach($roleId);
    }

    /**
     * Get user's roles
     */
    public function getRoles($userId)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return collect();
        }

        return $user->roles;
    }

    /**
     * Check if user has role
     */
    public function hasRole($userId, $roleSlug)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has privilege
     */
    public function hasPrivilege($userId, $privilegeSlug)
    {
        $user = $this->find($userId);
        
        if (!$user) {
            return false;
        }

        return $user->roles()
            ->whereHas('privileges', function($q) use ($privilegeSlug) {
                $q->where('slug', $privilegeSlug);
            })
            ->exists();
    }
}


