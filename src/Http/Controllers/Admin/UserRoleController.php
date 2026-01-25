<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin;

use App\Models\User;
use Marufsharia\Hyro\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class UserRoleController extends Controller
{
    /**
     * Show the form for editing user roles.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('hyro::users.roles', compact('user', 'roles'));
    }

    /**
     * Update user roles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:hyro_roles,id',
        ]);

        // Ensure at least one super admin remains
        $superAdminRole = Role::where('slug', config('hyro.super_admin_role', 'super-admin'))->first();
        $currentSuperAdmins = User::whereHas('roles', function ($query) use ($superAdminRole) {
            $query->where('hyro_roles.id', $superAdminRole->id);
        })->count();

        $userIsSuperAdmin = $user->roles()->where('hyro_roles.id', $superAdminRole->id)->exists();
        $willBeSuperAdmin = in_array($superAdminRole->id, $validated['roles'] ?? []);

        if ($userIsSuperAdmin && !$willBeSuperAdmin && $currentSuperAdmins <= 1) {
            return back()->with('error', 'Cannot remove the last super administrator.');
        }

        $user->roles()->sync($validated['roles'] ?? []);

        // Clear user role cache
        if (method_exists($user, 'clearHyroRoleCache')) {
            $user->clearHyroRoleCache();
        }

        return back()->with('success', 'User roles updated successfully.');
    }
}
