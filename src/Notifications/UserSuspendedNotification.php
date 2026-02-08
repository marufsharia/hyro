<?php

namespace Marufsharia\Hyro\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marufsharia\Hyro\Events\UserSuspended;

class UserSuspendedNotification extends Notification implements ShouldQueue
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
        // Always send suspension notifications via all available channels
        return config('hyro.notifications.channels', ['database', 'mail']);
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $suspenderName = $this->event->suspender?->name ?? 'System Administrator';
        $reason = $this->event->metadata['reason'] ?? 'No reason provided';
        $durationDays = $this->event->metadata['duration_days'] ?? null;

        $message = (new MailMessage)
            ->error()
            ->subject('Account Suspended')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been suspended.')
            ->line('**Suspended by:** ' . $suspenderName)
            ->line('**Reason:** ' . $reason);

        if ($durationDays) {
            $message->line('**Duration:** ' . $durationDays . ' days')
                ->line('Your account will be automatically reactivated after this period.');
        } else {
            $message->line('**Duration:** Indefinite')
                ->line('Your account will remain suspended until manually reactivated by an administrator.');
        }

        $message->line('During this suspension period:')
            ->line('• You will not be able to log in to your account')
            ->line('• All your active sessions will be terminated')
            ->line('• Your API tokens have been revoked')
            ->line('If you believe this is an error or would like to appeal, please contact support.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'user_suspended',
            'suspender_id' => $this->event->suspender?->id,
            'suspender_name' => $this->event->suspender?->name,
            'reason' => $this->event->metadata['reason'] ?? null,
            'duration_days' => $this->event->metadata['duration_days'] ?? null,
            'suspended_at' => $this->event->metadata['suspended_at'],
            'via' => $this->event->metadata['via'] ?? 'manual',
            'message' => 'Your account has been suspended',
            'severity' => 'high',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): array
    {
        return [
            'title' => 'Account Suspended',
            'message' => 'Your account has been suspended',
            'reason' => $this->event->metadata['reason'] ?? 'No reason provided',
            'icon' => 'exclamation-triangle',
            'color' => 'danger',
            'severity' => 'high',
        ];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend($notifiable): bool
    {
        // Always send suspension notifications
        return true;
    }
}
