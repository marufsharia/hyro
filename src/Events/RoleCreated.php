<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The role instance.
     */
    public Role $role;

    /**
     * The user who created the role.
     */
    public ?object $creator;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Role $role, ?object $creator = null, array $metadata = [])
    {
        $this->role = $role;
        $this->creator = $creator;
        $this->metadata = array_merge([
            'created_at' => now(),
            'via' => 'manual',
            'reason' => null,
        ], $metadata);
    }

    /**
     * Get the event's broadcast channel name.
     */
    public function broadcastOn(): string
    {
        return 'role.' . $this->role->id;
    }
}
