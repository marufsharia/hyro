<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\PrivilegeRevoked;

class PrivilegeRevokedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The privilege revoked event.
     */
    public PrivilegeRevoked $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(PrivilegeRevoked $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return config('hyro.notifications.channels', ['database']);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'privilege_revoked',
            'privilege_id' => $this->event->privilege->id,
            'privilege_name' => $this->event->privilege->name,
            'privilege_slug' => $this->event->privilege->slug,
            'role_id' => $this->event->role->id,
            'role_name' => $this->event->role->name,
            'revoker_id' => $this->event->revoker?->id,
            'revoker_name' => $this->event->revoker?->name,
            'revoked_at' => $this->event->metadata['revoked_at'],
            'message' => "Privilege '{$this->event->privilege->name}' revoked from your role '{$this->event->role->name}'",
        ];
    }
}
