<?php

namespace Marufsharia\Hyro\Livewire;

use Livewire\Component;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $recentNotifications = [];
    public $showDropdown = false;

    protected $listeners = [
        'notificationReceived' => 'refreshNotifications',
        'notification-read' => 'refreshNotifications',
        'all-notifications-read' => 'refreshNotifications',
    ];

    /**
     * Mount the component.
     */
    public function mount()
    {
        if (auth()->check()) {
            $this->refreshNotifications();
        }
    }

    /**
     * Refresh notifications.
     */
    public function refreshNotifications()
    {
        if (!auth()->check()) {
            $this->unreadCount = 0;
            $this->recentNotifications = collect([]);
            return;
        }

        $this->unreadCount = auth()->user()->unreadNotifications()->count();
        $this->recentNotifications = auth()->user()
            ->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Toggle dropdown.
     */
    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId)
    {
        if (!auth()->check()) {
            return;
        }

        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->refreshNotifications();
        }
    }

    /**
     * Mark all as read.
     */
    public function markAllAsRead()
    {
        if (!auth()->check()) {
            return;
        }

        auth()->user()->unreadNotifications->markAsRead();
        $this->refreshNotifications();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('hyro::livewire.notification-bell');
    }
}
