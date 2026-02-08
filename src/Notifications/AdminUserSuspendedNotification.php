<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\UserSuspended;

class AdminUserSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The user suspended event.
     */
    public UserSuspended $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserSuspended $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $userName = $this->event->user->name;
        $userEmail = $this->event->user->email;
        $suspenderName = $this->event->suspender?->name ?? 'System';
        $reason = $this->event->metadata['reason'] ?? 'No reason provided';

        return (new MailMessage)
            ->subject('[Admin Alert] User Suspended: ' . $userName)
            ->greeting('Hello Administrator,')
            ->line('A user has been suspended in the system.')
            ->line('**User:** ' . $userName . ' (' . $userEmail . ')')
            ->line('**Suspended by:** ' . $suspenderName)
            ->line('**Reason:** ' . $reason)
            ->line('**Time:** ' . $this->event->metadata['suspended_at']->format('Y-m-d H:i:s'))
            ->action('View User Details', url('/admin/users/' . $this->event->user->id))
            ->line('This is an automated notification for administrative awareness.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'admin_user_suspended',
            'user_id' => $this->event->user->id,
            'user_name' => $this->event->user->name,
            'user_email' => $this->event->user->email,
            'suspender_id' => $this->event->suspender?->id,
            'suspender_name' => $this->event->suspender?->name,
            'reason' => $this->event->metadata['reason'] ?? null,
            'suspended_at' => $this->event->metadata['suspended_at'],
            'message' => "User {$this->event->user->name} has been suspended",
            'severity' => 'medium',
        ];
    }
}
