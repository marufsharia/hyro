<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\UserUnsuspended;

class UserUnsuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The user unsuspended event.
     */
    public UserUnsuspended $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(UserUnsuspended $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return config('hyro.notifications.channels', ['database', 'mail']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $unsuspenderName = $this->event->unsuspender?->name ?? 'System Administrator';
        $reason = $this->event->metadata['reason'] ?? 'Suspension period ended';

        return (new MailMessage)
            ->success()
            ->subject('Account Reactivated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Good news! Your account has been reactivated.')
            ->line('**Reactivated by:** ' . $unsuspenderName)
            ->line('**Reason:** ' . $reason)
            ->line('You can now:')
            ->line('• Log in to your account')
            ->line('• Access all your previous privileges')
            ->line('• Use the system normally')
            ->action('Log In Now', url('/login'))
            ->line('Welcome back! If you have any questions, please contact support.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'user_unsuspended',
            'unsuspender_id' => $this->event->unsuspender?->id,
            'unsuspender_name' => $this->event->unsuspender?->name,
            'reason' => $this->event->metadata['reason'] ?? null,
            'unsuspended_at' => now(),
            'via' => $this->event->metadata['via'] ?? 'manual',
            'message' => 'Your account has been reactivated',
            'severity' => 'info',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'title' => 'Account Reactivated',
            'message' => 'Your account has been reactivated',
            'icon' => 'check-circle',
            'color' => 'success',
            'action_url' => url('/login'),
        ];
    }
}
