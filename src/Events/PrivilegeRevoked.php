<?php

namespace Marufsharia\Hyro\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivilegeRevoked
{
    use Dispatchable, SerializesModels;

    /**
     * The role instance.
     */
    public Role $role;

    /**
     * The privilege instance.
     */
    public Privilege $privilege;

    /**
     * The user who performed the revocation.
     */
    public ?Authenticatable $revoker;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Role $role, Privilege $privilege, ?Authenticatable $revoker = null, array $metadata = [])
    {
        $this->role = $role;
        $this->privilege = $privilege;
        $this->revoker = $revoker;
        $this->metadata = array_merge([
            'revoked_at' => now(),
            'via' => 'manual',
            'scope' => $privilege->scope,
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
