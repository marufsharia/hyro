# 🚀 Quick Start: Hyro Notifications

Get up and running with Hyro notifications in 5 minutes!

---

## ⚡ 1-Minute Setup

### Step 1: Add to Layout

```blade
{{-- resources/views/layouts/app.blade.php --}}
<header>
    <nav>
        {{-- Your navigation items --}}
        
        {{-- Add notification bell --}}
        <livewire:hyro.notification-bell />
    </nav>
</header>
```

### Step 2: Configure (Optional)

```env
# .env
HYRO_NOTIFICATIONS_ENABLED=true
HYRO_NOTIFICATIONS_QUEUE=true
```

### Step 3: Done! 🎉

Notifications will automatically be sent when:
- Roles are assigned/revoked
- Privileges are granted/revoked
- Users are suspended/unsuspended

---

## 📍 Quick Links

- **Notification Center:** `/notifications`
- **Preferences:** `/notifications/preferences`

---

## 🎯 Common Tasks

### Send a Notification

```php
use Marufsharia\Hyro\Notifications\RoleAssignedNotification;

$user->notify(new RoleAssignedNotification($event));
```

### Check Unread Count

```php
$count = auth()->user()->unreadNotifications()->count();
```

### Mark as Read

```php
$notification->markAsRead();
```

### Get User Preferences

```php
$prefs = auth()->user()->getNotificationSettings();
```

---

## 🎨 UI Components

### Notification Bell (Header)
```blade
<livewire:hyro.notification-bell />
```

### Notification Center (Full Page)
```blade
<livewire:hyro.notification-center />
```

### Preferences (Settings Page)
```blade
<livewire:hyro.notification-preferences />
```

---

## 🔧 Customization

### Disable Email for Specific Event

```php
// config/hyro.php
'notifications' => [
    'privilege_granted' => [
        'enabled' => true,
        'channels' => ['database'], // Remove 'mail'
    ],
],
```

### Custom Notification

```bash
php artisan make:notification MyNotification
```

```php
public function via($notifiable): array
{
    return ['database', 'mail'];
}

public function toArray($notifiable): array
{
    return [
        'type' => 'custom',
        'message' => 'Your custom message',
    ];
}
```

---

## 📚 Full Documentation

See `NOTIFICATIONS.md` for complete documentation.

---

**That's it!** You're ready to use Hyro notifications. 🎉
