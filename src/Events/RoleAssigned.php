<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\User;
use Marufsharia\Hyro\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * The user instance.
     */
    public User $user;

    /**
     * The role instance.
     */
    public Role $role;

    /**
     * The user who performed the assignment.
     */
    public ?User $assigner;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Role $role, ?User $assigner = null, array $metadata = [])
    {
        $this->user = $user;
        $this->role = $role;
        $this->assigner = $assigner;
        $this->metadata = array_merge([
            'assigned_at' => now(),
            'via' => 'manual',
            'reason' => null,
        ], $metadata);
    }

    /**
     * Get the event's broadcast channel name.
     */
    public function broadcastOn(): string
    {
        return 'user.' . $this->user->id;
    }
}
