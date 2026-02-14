<?php

namespace Marufsharia\Hyro\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    public $stats = [];
    public $refreshing = false;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->refreshing = true;
        
        try {
            $this->stats = [
                'users' => User::count(),
                'roles' => Role::count(),
                'privileges' => Privilege::count(),
                'recent_users' => User::with('roles')
                    ->latest()
                    ->take(5)
                    ->get(),
            ];
        } catch (\Exception $e) {
            // Fallback if there's an error
            $this->stats = [
                'users' => 0,
                'roles' => 0,
                'privileges' => 0,
                'recent_users' => collect([]),
            ];
            
            Log::error('Dashboard stats error: ' . $e->getMessage());
        }
        
        $this->refreshing = false;
    }

    public function refresh()
    {
        $this->loadStats();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Dashboard refreshed successfully'
        ]);
    }

    public function render()
    {
        return view('hyro::admin.dashboard.dashboard')
            ->layout('hyro::admin.layouts.app');
    }
}
