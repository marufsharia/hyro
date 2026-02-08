<?php

namespace Marufsharia\Hyro\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    /**
     * Display notification center.
     */
    public function index()
    {
        return view('hyro::notifications.index');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete notification.
     */
    public function destroy($notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted');
    }

    /**
     * Delete all read notifications.
     */
    public function destroyAll()
    {
        auth()->user()->readNotifications()->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All read notifications deleted');
    }

    /**
     * Show notification preferences.
     */
    public function preferences()
    {
        return view('hyro::notifications.preferences');
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validated = $request->validate([
            'email' => 'boolean',
            'database' => 'boolean',
            'push' => 'boolean',
            'sms' => 'boolean',
        ]);

        auth()->user()->updateNotificationSettings($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification preferences updated');
    }
}
