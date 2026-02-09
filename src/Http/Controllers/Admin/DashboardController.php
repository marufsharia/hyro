<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin;

use App\Models\User;
use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        try {
            $stats = [
                'users' => User::count(),
                'roles' => Role::count(),
                'privileges' => Privilege::count(),
                'recent_users' => User::with('roles')->latest()->take(5)->get(),
            ];
        } catch (\Exception $e) {
            // Fallback if there's an error
            $stats = [
                'users' => 0,
                'roles' => 0,
                'privileges' => 0,
                'recent_users' => collect([]),
            ];
            
            \Log::error('Dashboard stats error: ' . $e->getMessage());
        }

        return view('hyro::admin.dashboard.dashboard', compact('stats'));
    }
}
