<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * The role instance.
     */
    public Role $role;

    /**
     * The users who deleted the role.
     */
    public ?object $deleter;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Role $role, ?object $deleter = null, array $metadata = [])
    {
        $this->role = $role;
        $this->deleter = $deleter;
        $this->metadata = array_merge([
            'deleted_at' => now(),
            'via' => 'manual',
            'reason' => null,
        ], $metadata);
    }

    /**
     * Get the event's broadcast channel name.
     */
    public function broadcastOn(): string
    {
        return 'role.deleted.' . $this->role->id;
    }
}
