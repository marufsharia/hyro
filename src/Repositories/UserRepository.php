<?php


namespace Marufsharia\Hyro\Repositories;

use Illuminate\Database\Eloquent\Model;
use Marufsharia\Hyro\Contracts\HyroUserContract;

class UserRepository
{
    protected HyroUserContract|string $userModel;

    public function __construct(array $config)
    {
        $this->userModel = $config['database']['models']['user'] ?? \App\Models\User::class;
    }

    /**
     * Get a user by ID
     */
    public function find(int $id): ?HyroUserContract
    {
        return $this->userModel::find($id);
    }

    /**
     * Get all users
     */
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userModel::all();
    }

    /**
     * Create a new user
     */
    public function create(array $data): HyroUserContract
    {
        return $this->userModel::create($data);
    }

    /**
     * Update a user
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->find($id);
        return $user ? $user->update($data) : false;
    }

    /**
     * Delete a user
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        return $user ? $user->delete() : false;
    }
}
