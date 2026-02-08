
<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\RoleAssigned;

class RoleAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The role assigned event.
     */
    public RoleAssigned $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(RoleAssigned $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = config('hyro.notifications.channels', ['database', 'mail']);
        
        // Check user preferences if available
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
        $assignerName = $this->event->assigner?->name ?? 'System Administrator';
        $reason = $this->event->metadata['reason'] ?? null;

        $message = (new MailMessage)
            ->subject('New Role Assigned: ' . $roleName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned a new role: **' . $roleName . '**')
            ->line('Assigned by: ' . $assignerName);

        if ($reason) {
            $message->line('Reason: ' . $reason);
        }

        $message->line('This role grants you the following privileges:')
            ->line($this->formatPrivileges($this->event->role))
            ->action('View Your Roles', url('/admin/profile/roles'))
            ->line('If you have any questions, please contact your administrator.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'role_assigned',
            'role_id' => $this->event->role->id,
            'role_name' => $this->event->role->name,
            'role_slug' => $this->event->role->slug,
            'assigner_id' => $this->event->assigner?->id,
            'assigner_name' => $this->event->assigner?->name,
            'reason' => $this->event->metadata['reason'] ?? null,
            'assigned_at' => $this->event->metadata['assigned_at'],
            'via' => $this->event->metadata['via'] ?? 'manual',
            'message' => "You have been assigned the role: {$this->event->role->name}",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'title' => 'New Role Assigned',
            'message' => "You have been assigned the role: {$this->event->role->name}",
            'role' => $this->event->role->name,
            'icon' => 'shield-check',
            'color' => 'success',
            'action_url' => url('/admin/profile/roles'),
        ];
    }

    /**
     * Format privileges for display.
     */
    protected function formatPrivileges($role): string
    {
        $privileges = $role->privileges()->limit(10)->pluck('name')->toArray();
        
        if (empty($privileges)) {
            return 'No specific privileges assigned yet.';
        }

        $formatted = implode(', ', array_slice($privileges, 0, 5));
        
        if (count($privileges) > 5) {
            $formatted .= ' and ' . (count($privileges) - 5) . ' more...';
        }

        return $formatted;
    }
}
