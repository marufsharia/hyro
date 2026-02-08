<?php

namespace Marufsharia\Hyro\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Marufsharia\Hyro\Models\Role;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleRevoked
{
    use Dispatchable, SerializesModels;

    /**
     * The user instance.
     */
    public Authenticatable $user;

    /**
     * The role instance.
     */
    public Role $role;

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
    public function __construct(Authenticatable $user, Role $role, ?Authenticatable $revoker = null, array $metadata = [])
    {
        $this->user = $user;
        $this->role = $role;
        $this->revoker = $revoker;
        $this->metadata = array_merge([
            'revoked_at' => now(),
            'via' => 'manual',
            'reason' => null,
        ], $metadata);
    }

    /**
     * Get the event's broadcast channel name.
     */
    public function broadcastOn(): string
    {
        return 'users.' . $this->user->id;
    }
}
