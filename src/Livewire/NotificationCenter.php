<?php

namespace Marufsharia\Hyro\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read
    public $perPage = 10;

    protected $listeners = [
        'notificationReceived' => '$refresh',
    ];

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            $this->dispatch('notification-read', ['id' => $notificationId]);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->dispatch('all-notifications-read');
        session()->flash('success', 'All notifications marked as read');
    }

    /**
     * Delete notification.
     */
    public function deleteNotification($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->dispatch('notification-deleted', ['id' => $notificationId]);
            session()->flash('success', 'Notification deleted');
        }
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead()
    {
        auth()->user()->readNotifications()->delete();
        $this->dispatch('read-notifications-deleted');
        session()->flash('success', 'All read notifications deleted');
    }

    /**
     * Change filter.
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    /**
     * Get notifications based on filter.
     */
    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return $query->latest()->paginate($this->perPage);
    }

    /**
     * Get unread count.
     */
    public function getUnreadCountProperty()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('hyro::livewire.notification-center', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
