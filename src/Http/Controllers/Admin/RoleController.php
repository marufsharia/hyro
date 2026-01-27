<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin;

use Marufsharia\Hyro\Models\Role;
use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $roles = Role::withCount(['users', 'privileges'])
            ->orderBy('name')
            ->paginate(20);

        return view('hyro::admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $privileges = Privilege::orderBy('name')->get();

        return view('hyro::admin.roles.create', compact('privileges'));
    }

    /**
     * Store a newly created role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hyro_roles,name',
            'slug' => 'required|string|max:255|unique:hyro_roles,slug|alpha_dash',
            'description' => 'nullable|string|max:500',
            'privileges' => 'array',
            'privileges.*' => 'exists:hyro_privileges,id',
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);

            if (isset($validated['privileges'])) {
                $role->privileges()->sync($validated['privileges']);
            }
        });

        return redirect()->route('hyro.admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     *
     * @param  \Marufsharia\Hyro\Models\Role  $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role): View
    {
        $privileges = Privilege::orderBy('name')->get();
        $role->load('privileges');

        return view('hyro::admin.roles.edit', compact('role', 'privileges'));
    }

    /**
     * Update the specified role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Marufsharia\Hyro\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hyro_roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:hyro_roles,slug,' . $role->id . '|alpha_dash',
            'description' => 'nullable|string|max:500',
            'privileges' => 'array',
            'privileges.*' => 'exists:hyro_privileges,id',
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
            ]);

            $role->privileges()->sync($validated['privileges'] ?? []);
        });

        return redirect()->route('hyro.admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     *
     * @param  \Marufsharia\Hyro\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deletion of the last administrator
        $adminRole = Role::where('slug', config('hyro.super_admin_role', 'super-admin'))->first();

        if ($role->id === $adminRole->id && $adminRole->users()->count() > 0) {
            return back()->with('error', 'Cannot delete the super admin role while it has assigned users.');
        }

        $role->delete();

        return redirect()->route('hyro.admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Show form for editing role privileges.
     *
     * @param  \Marufsharia\Hyro\Models\Role  $role
     * @return \Illuminate\View\View
     */
    public function editPrivileges(Role $role): View
    {
        $privileges = Privilege::orderBy('category')->orderBy('name')->get();
        $role->load('privileges');

        return view('hyro::roles.privileges', compact('role', 'privileges'));
    }

    /**
     * Update role privileges.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Marufsharia\Hyro\Models\Role  $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrivileges(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'privileges' => 'array',
            'privileges.*' => 'exists:hyro_privileges,id',
        ]);

        $role->privileges()->sync($validated['privileges'] ?? []);

        return redirect()->route('hyro.admin.roles.index')
            ->with('success', 'Role privileges updated successfully.');
    }
}
