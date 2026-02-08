<?php

namespace Marufsharia\Hyro\Livewire;

use Livewire\Component;

class NotificationPreferences extends Component
{
    public $preferences = [];

    public function mount()
    {
        $this->preferences = auth()->user()->getNotificationSettings();
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences()
    {
        $this->validate([
            'preferences.email' => 'boolean',
            'preferences.database' => 'boolean',
            'preferences.push' => 'boolean',
            'preferences.sms' => 'boolean',
        ]);

        auth()->user()->updateNotificationSettings($this->preferences);

        session()->flash('success', 'Notification preferences updated successfully');
    }

    /**
     * Toggle a specific preference.
     */
    public function toggle($key)
    {
        $this->preferences[$key] = !($this->preferences[$key] ?? false);
        $this->updatePreferences();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('hyro::livewire.notification-preferences');
    }
}
