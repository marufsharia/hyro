<?php

namespace Marufsharia\Hyro\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserSuspended
{
    use Dispatchable, SerializesModels;

    /**
     * The user instance.
     */
    public Authenticatable $user;

    /**
     * The user who performed the suspension.
     */
    public ?Authenticatable $suspender;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Authenticatable $user, ?Authenticatable $suspender = null, array $metadata = [])
    {
        $this->user = $user;
        $this->suspender = $suspender;
        $this->metadata = array_merge([
            'suspended_at' => now(),
            'duration_days' => null,
            'reason' => null,
            'via' => 'manual',
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
