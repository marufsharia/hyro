<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\PrivilegeGranted;

class PrivilegeGrantedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The privilege granted event.
     */
    public PrivilegeGranted $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(PrivilegeGranted $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = config('hyro.notifications.channels', ['database']);
        
        // Only send email for important privileges
        if ($this->isImportantPrivilege()) {
            $channels[] = 'mail';
        }
        
        return array_unique($channels);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $privilegeName = $this->event->privilege->name;
        $roleName = $this->event->role->name;
        $granterName = $this->event->granter?->name ?? 'System Administrator';

        return (new MailMessage)
            ->subject('New Privilege Granted')
            ->greeting('Hello!')
            ->line('A new privilege has been granted to your role.')
            ->line('**Privilege:** ' . $privilegeName)
            ->line('**Role:** ' . $roleName)
            ->line('**Granted by:** ' . $granterName)
            ->line('This privilege allows you to: ' . ($this->event->privilege->description ?? 'Perform specific actions'))
            ->action('View Your Privileges', url('/admin/profile/privileges'))
            ->line('If you have any questions about this privilege, please contact your administrator.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'privilege_granted',
            'privilege_id' => $this->event->privilege->id,
            'privilege_name' => $this->event->privilege->name,
            'privilege_slug' => $this->event->privilege->slug,
            'role_id' => $this->event->role->id,
            'role_name' => $this->event->role->name,
            'granter_id' => $this->event->granter?->id,
            'granter_name' => $this->event->granter?->name,
            'granted_at' => $this->event->metadata['granted_at'],
            'message' => "New privilege '{$this->event->privilege->name}' granted to your role '{$this->event->role->name}'",
        ];
    }

    /**
     * Check if this is an important privilege that warrants email notification.
     */
    protected function isImportantPrivilege(): bool
    {
        $importantPatterns = [
            'admin.*',
            '*.delete',
            'users.suspend',
            'system.*',
        ];

        $slug = $this->event->privilege->slug;

        foreach ($importantPatterns as $pattern) {
            if (fnmatch($pattern, $slug)) {
                return true;
            }
        }

        return false;
    }
}
