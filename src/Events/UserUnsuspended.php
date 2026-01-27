<?php

namespace Marufsharia\Hyro\Events;

use Marufsharia\Hyro\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserUnsuspended
{
    use Dispatchable, SerializesModels;

    /**
     * The users instance.
     */
    public User $user;

    /**
     * The users who performed the unsuspension.
     */
    public ?User $unsuspender;

    /**
     * Additional metadata.
     */
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, ?User $unsuspender = null, array $metadata = [])
    {
        $this->user = $user;
        $this->unsuspender = $unsuspender;
        $this->metadata = array_merge([
            'unsuspended_at' => now(),
            'reason' => null,
            'via' => 'manual',
            'original_suspension_reason' => $user->suspension_reason ?? null,
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
