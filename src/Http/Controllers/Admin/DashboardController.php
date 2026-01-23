<?php

namespace MarufSharia\Hyro\Http\Controllers\Admin;

use App\Models\User;
use MarufSharia\Hyro\Models\Role;
use MarufSharia\Hyro\Models\Privilege;
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
        $stats = [
            'users' => User::count(),
            'roles' => Role::count(),
            'privileges' => Privilege::count(),
            'recent_users' => User::with(['roles' => function ($query) {
                $query->latest()->limit(5);
            }])->latest()->take(5)->get(),
        ];

        return view('hyro::admin.dashboard', compact('stats'));
    }
}
