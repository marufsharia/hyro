<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * The role instance.
     */
    public Role $role;

    /**
     * The users who updated the role.
     */
    public ?object $updater;

    /**
     * The original attributes before update.
     */
    public array $original;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Role $role, ?object $updater = null, array $original = [], array $metadata = [])
    {
        $this->role = $role;
        $this->updater = $updater;
        $this->original = $original;
        $this->metadata = array_merge([
            'updated_at' => now(),
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
