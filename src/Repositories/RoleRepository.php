<?php


namespace Marufsharia\Hyro\Repositories;

use Illuminate\Database\Eloquent\Model;

class RoleRepository
{
    protected string $roleModel;

    public function __construct(array $config)
    {
        $this->roleModel = $config['database']['models']['role'] ?? \Marufsharia\Hyro\Models\Role::class;
    }

    /**
     * Get a role by ID
     */
    public function find(int $id): ?Model
    {
        return $this->roleModel::find($id);
    }

    /**
     * Get all roles
     */
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->roleModel::all();
    }

    /**
     * Create a new role
     */
    public function create(array $data): Model
    {
        return $this->roleModel::create($data);
    }

    /**
     * Update a role
     */
    public function update(int $id, array $data): bool
    {
        $role = $this->find($id);
        return $role ? $role->update($data) : false;
    }

    /**
     * Delete a role
     */
    public function delete(int $id): bool
    {
        $role = $this->find($id);
        return $role ? $role->delete() : false;
    }
}
