# 🔔 Hyro Notification System

Complete notification system for Hyro with email, database, and real-time support.

---

## 📋 Features

- ✅ **Multi-Channel Notifications** - Email, Database, Push, SMS
- ✅ **Event-Driven** - Automatic notifications on role/privilege changes
- ✅ **User Preferences** - Customizable notification settings per user
- ✅ **Notification Center** - Beautiful UI for managing notifications
- ✅ **Notification Bell** - Real-time notification dropdown
- ✅ **Admin Alerts** - Notify administrators of important events
- ✅ **Queue Support** - Background processing for better performance
- ✅ **Email Templates** - Professional email designs
- ✅ **Customizable** - Easy to extend and customize

---

## 🚀 Quick Start

### 1. Run Migrations

The notifications table is created automatically by Laravel. If needed:

```bash
php artisan notifications:table
php artisan migrate
```

### 2. Configure Notifications

Update your `.env` file:

```env
# Enable notifications
HYRO_NOTIFICATIONS_ENABLED=true

# Notification channels
HYRO_NOTIFICATIONS_CHANNELS=database,mail

# Queue notifications (recommended)
HYRO_NOTIFICATIONS_QUEUE=true
HYRO_NOTIFICATIONS_QUEUE_CONNECTION=redis
HYRO_NOTIFICATIONS_QUEUE_NAME=notifications

# Event-specific settings
HYRO_NOTIFY_ROLE_ASSIGNED=true
HYRO_NOTIFY_ROLE_REVOKED=true
HYRO_NOTIFY_USER_SUSPENDED=true
HYRO_NOTIFY_USER_UNSUSPENDED=true
HYRO_NOTIFY_ADMIN_USER_SUSPENDED=true
```

### 3. Add Notification Bell to Layout

```blade
{{-- In your layout file (e.g., app.blade.php) --}}
<div class="flex items-center space-x-4">
    <livewire:hyro.notification-bell />
    
    {{-- Your other header items --}}
</div>
```

### 4. Add Notification Routes

Routes are automatically registered. Access:
- Notification Center: `/notifications`
- Preferences: `/notifications/preferences`

---

## 📧 Available Notifications

### 1. **UserSuspendedNotification**
Sent when a user account is suspended.

**Channels:** Database, Email  
**Triggered by:** `UserSuspended` event

**Email Preview:**
```
Subject: Account Suspended

Hello John Doe,

Your account has been suspended.

Suspended by: Admin User
Reason: Policy violation
Duration: 7 days

During this suspension period:
• You will not be able to log in
• All active sessions will be terminated
• Your API tokens have been revoked
```

### 2. **UserUnsuspendedNotification**
Sent when a user account is reactivated.

**Channels:** Database, Email  
**Triggered by:** `UserUnsuspended` event

### 3. **RoleAssignedNotification**
Sent when a role is assigned to a user.

**Channels:** Database, Email  
**Triggered by:** `RoleAssigned` event

### 4. **RoleRevokedNotification**
Sent when a role is revoked from a user.

**Channels:** Database, Email  
**Triggered by:** `RoleRevoked` event

### 5. **PrivilegeGrantedNotification**
Sent when a privilege is granted to a role.

**Channels:** Database (Email for important privileges)  
**Triggered by:** `PrivilegeGranted` event

### 6. **PrivilegeRevokedNotification**
Sent when a privilege is revoked from a role.

**Channels:** Database  
**Triggered by:** `PrivilegeRevoked` event

### 7. **AdminUserSuspendedNotification**
Sent to administrators when any user is suspended.

**Channels:** Database, Email  
**Triggered by:** `UserSuspended` event  
**Recipients:** Users with admin/super-admin roles

---

## 🎨 UI Components

### Notification Bell

Real-time notification dropdown in your header:

```blade
<livewire:hyro.notification-bell />
```

**Features:**
- Shows unread count badge
- Displays 5 most recent notifications
- Mark as read on click
- "Mark all as read" button
- Link to full notification center

### Notification Center

Full-page notification management:

```blade
<livewire:hyro.notification-center />
```

**Features:**
- Filter by all/unread/read
- Pagination
- Mark as read/unread
- Delete notifications
- Bulk actions

### Notification Preferences

User notification settings:

```blade
<livewire:hyro.notification-preferences />
```

**Features:**
- Toggle email notifications
- Toggle in-app notifications
- Toggle push notifications
- Toggle SMS notifications
- Auto-save on change

---

## 🔧 Configuration

### Global Settings

```php
// config/hyro.php

'notifications' => [
    'enabled' => true,
    'channels' => ['database', 'mail'],
    
    // Per-event configuration
    'role_assigned' => [
        'enabled' => true,
        'channels' => ['database', 'mail'],
    ],
    
    // Queue settings
    'queue' => [
        'enabled' => true,
        'connection' => 'redis',
        'queue' => 'notifications',
    ],
    
    // Real-time notifications
    'real_time' => [
        'enabled' => false,
        'driver' => 'pusher',
    ],
],
```

### User Preferences

Users can customize their notification preferences:

```php
// Get user preferences
$preferences = auth()->user()->getNotificationSettings();

// Update preferences
auth()->user()->updateNotificationSettings([
    'email' => true,
    'database' => true,
    'push' => false,
    'sms' => false,
]);
```

---

## 📝 Creating Custom Notifications

### 1. Create Notification Class

```bash
php artisan make:notification CustomNotification
```

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return config('hyro.notifications.channels', ['database', 'mail']);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Custom Notification')
            ->line('This is a custom notification')
            ->action('View Details', url('/'))
            ->line('Thank you!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'custom',
            'message' => 'This is a custom notification',
        ];
    }
}
```

### 2. Send Notification

```php
use App\Notifications\CustomNotification;

// Send to single user
$user->notify(new CustomNotification());

// Send to multiple users
Notification::send($users, new CustomNotification());
```

### 3. Listen to Events

```php
// In EventServiceProvider
protected $listen = [
    CustomEvent::class => [
        CustomNotificationListener::class,
    ],
];
```

---

## 🎯 Advanced Usage

### Conditional Notifications

```php
public function via($notifiable): array
{
    $channels = ['database'];
    
    // Only send email for important notifications
    if ($this->isImportant()) {
        $channels[] = 'mail';
    }
    
    // Check user preferences
    if (method_exists($notifiable, 'getNotificationSettings')) {
        $settings = $notifiable->getNotificationSettings();
        
        if (!($settings['email'] ?? true)) {
            $channels = array_diff($channels, ['mail']);
        }
    }
    
    return $channels;
}
```

### Custom Notification Channels

```php
// Create custom channel
php artisan make:notification-channel SmsChannel

// Use in notification
public function via($notifiable): array
{
    return ['database', 'mail', SmsChannel::class];
}

public function toSms($notifiable)
{
    return [
        'to' => $notifiable->phone,
        'message' => 'Your notification message',
    ];
}
```

### Notification Scheduling

```php
// Delay notification
$user->notify((new CustomNotification())->delay(now()->addMinutes(10)));

// Schedule for specific time
$user->notify((new CustomNotification())->delay(now()->addHours(2)));
```

### Notification Batching

```php
// Send to multiple users efficiently
Notification::send($users, new CustomNotification());

// Send via specific channel
Notification::route('mail', 'admin@example.com')
    ->notify(new CustomNotification());
```

---

## 🔍 Querying Notifications

### Get User Notifications

```php
// All notifications
$notifications = auth()->user()->notifications;

// Unread only
$unread = auth()->user()->unreadNotifications;

// Read only
$read = auth()->user()->readNotifications;

// Filter by type
$roleNotifications = auth()->user()
    ->notifications()
    ->where('type', 'role_assigned')
    ->get();

// Recent notifications
$recent = auth()->user()
    ->notifications()
    ->latest()
    ->limit(10)
    ->get();
```

### Mark as Read

```php
// Mark single notification
$notification->markAsRead();

// Mark all as read
auth()->user()->unreadNotifications->markAsRead();

// Mark specific notifications
auth()->user()
    ->unreadNotifications()
    ->where('type', 'role_assigned')
    ->update(['read_at' => now()]);
```

### Delete Notifications

```php
// Delete single notification
$notification->delete();

// Delete all read notifications
auth()->user()->readNotifications()->delete();

// Delete old notifications
auth()->user()
    ->notifications()
    ->where('created_at', '<', now()->subDays(30))
    ->delete();
```

---

## 🎨 Customizing Email Templates

### Override Default Templates

Create your own templates in `resources/views/vendor/hyro/notifications/email/`:

```blade
{{-- resources/views/vendor/hyro/notifications/email/user-suspended.blade.php --}}

@component('mail::message')
# Custom Suspension Email

Your custom content here...

@component('mail::button', ['url' => $url])
Custom Button
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Customize Mail Message

```php
public function toMail($notifiable): MailMessage
{
    return (new MailMessage)
        ->from('custom@example.com', 'Custom Name')
        ->subject('Custom Subject')
        ->greeting('Custom Greeting')
        ->line('Custom line')
        ->action('Custom Action', url('/'))
        ->line('Custom footer')
        ->markdown('custom.email.template');
}
```

---

## 🔔 Real-Time Notifications

### Setup Broadcasting

1. **Install Pusher or Laravel Echo Server:**

```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
```

2. **Configure Broadcasting:**

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=your-cluster

HYRO_NOTIFICATIONS_REALTIME=true
```

3. **Listen for Notifications:**

```javascript
// resources/js/app.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
});

// Listen for notifications
window.Echo.private(`App.Models.User.${userId}`)
    .notification((notification) => {
        console.log('New notification:', notification);
        // Update UI
        Livewire.emit('notificationReceived', notification);
    });
```

---

## 📊 Notification Analytics

### Track Notification Metrics

```php
// Get notification statistics
$stats = [
    'total' => auth()->user()->notifications()->count(),
    'unread' => auth()->user()->unreadNotifications()->count(),
    'read' => auth()->user()->readNotifications()->count(),
    'today' => auth()->user()
        ->notifications()
        ->whereDate('created_at', today())
        ->count(),
];

// Get notification types breakdown
$byType = auth()->user()
    ->notifications()
    ->select('type', DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
```

---

## 🧪 Testing

### Test Notification Sending

```php
use Illuminate\Support\Facades\Notification;

public function test_user_receives_suspension_notification()
{
    Notification::fake();
    
    $user = User::factory()->create();
    $user->suspend('Test reason');
    
    Notification::assertSentTo(
        $user,
        UserSuspendedNotification::class
    );
}
```

### Test Notification Content

```php
public function test_suspension_notification_content()
{
    $user = User::factory()->create();
    $notification = new UserSuspendedNotification($event);
    
    $mail = $notification->toMail($user);
    
    $this->assertEquals('Account Suspended', $mail->subject);
    $this->assertStringContainsString('suspended', $mail->render());
}
```

---

## 🐛 Troubleshooting

### Notifications Not Sending

1. **Check queue is running:**
```bash
php artisan queue:work
```

2. **Check notification is enabled:**
```php
config('hyro.notifications.enabled') // Should be true
```

3. **Check user preferences:**
```php
$user->getNotificationSettings() // Check enabled channels
```

### Email Not Sending

1. **Check mail configuration:**
```bash
php artisan config:cache
```

2. **Test mail:**
```bash
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('test@example.com'); });
```

### Database Notifications Not Showing

1. **Check notifications table exists:**
```bash
php artisan migrate:status
```

2. **Check Livewire is installed:**
```bash
composer show livewire/livewire
```

---

## 📚 API Reference

### NotificationController

```php
GET    /notifications              - List all notifications
POST   /notifications/{id}/read    - Mark as read
POST   /notifications/read-all     - Mark all as read
DELETE /notifications/{id}         - Delete notification
DELETE /notifications              - Delete all read
GET    /notifications/preferences  - Show preferences
POST   /notifications/preferences  - Update preferences
```

### Livewire Components

```php
<livewire:hyro.notification-bell />        // Notification dropdown
<livewire:hyro.notification-center />      // Full notification list
<livewire:hyro.notification-preferences /> // User preferences
```

---

## 🎉 Summary

The Hyro notification system provides:

✅ Complete notification infrastructure  
✅ Beautiful UI components  
✅ User preference management  
✅ Multi-channel support  
✅ Queue integration  
✅ Real-time capabilities  
✅ Easy customization  
✅ Production-ready  

**Phase 8: Notifications System - 100% COMPLETE** ✅

---

**Need Help?** Check the main Hyro documentation or create an issue on GitHub.
