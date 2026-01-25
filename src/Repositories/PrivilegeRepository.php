<?php


namespace Marufsharia\Hyro\Repositories;

use Illuminate\Database\Eloquent\Model;

class PrivilegeRepository

{
    protected string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function all()
    {
        return $this->modelClass::all();
    }

    public function find($id)
    {
        return $this->modelClass::find($id);
    }

    public function userHas($userId, $privilegeName): bool
    {
        $user = $this->modelClass::where('user_id', $userId)
            ->where('name', $privilegeName)
            ->first();
        return $user !== null;
    }


    /**
     * Create a new privilege
     */
    public function create(array $data): Model
    {
        return $this->privilegeModel::create($data);
    }

    /**
     * Update a privilege
     */
    public function update(int $id, array $data): bool
    {
        $privilege = $this->find($id);
        return $privilege ? $privilege->update($data) : false;
    }

    /**
     * Delete a privilege
     */
    public function delete(int $id): bool
    {
        $privilege = $this->find($id);
        return $privilege ? $privilege->delete() : false;
    }
}
