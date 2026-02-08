<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\RoleRevoked;

class RoleRevokedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The role revoked event.
     */
    public RoleRevoked $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(RoleRevoked $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = config('hyro.notifications.channels', ['database', 'mail']);
        
        if (method_exists($notifiable, 'getNotificationSettings')) {
            $settings = $notifiable->getNotificationSettings();
            $enabledChannels = [];
            
            if ($settings['email'] ?? true) {
                $enabledChannels[] = 'mail';
            }
            if ($settings['database'] ?? true) {
                $enabledChannels[] = 'database';
            }
            if (($settings['push'] ?? false) && in_array('broadcast', $channels)) {
                $enabledChannels[] = 'broadcast';
            }
            
            return array_intersect($channels, $enabledChannels);
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $roleName = $this->event->role->name;
        $revokerName = $this->event->revoker?->name ?? 'System Administrator';
        $reason = $this->event->metadata['reason'] ?? null;

        $message = (new MailMessage)
            ->subject('Role Revoked: ' . $roleName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your role **' . $roleName . '** has been revoked.')
            ->line('Revoked by: ' . $revokerName);

        if ($reason) {
            $message->line('Reason: ' . $reason);
        }

        $message->line('You no longer have access to the privileges associated with this role.')
            ->action('View Your Roles', url('/admin/profile/roles'))
            ->line('If you believe this is an error, please contact your administrator.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'role_revoked',
            'role_id' => $this->event->role->id,
            'role_name' => $this->event->role->name,
            'role_slug' => $this->event->role->slug,
            'revoker_id' => $this->event->revoker?->id,
            'revoker_name' => $this->event->revoker?->name,
            'reason' => $this->event->metadata['reason'] ?? null,
            'revoked_at' => $this->event->metadata['revoked_at'],
            'via' => $this->event->metadata['via'] ?? 'manual',
            'message' => "Your role '{$this->event->role->name}' has been revoked",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'title' => 'Role Revoked',
            'message' => "Your role '{$this->event->role->name}' has been revoked",
            'role' => $this->event->role->name,
            'icon' => 'shield-exclamation',
            'color' => 'warning',
            'action_url' => url('/admin/profile/roles'),
        ];
    }
}
