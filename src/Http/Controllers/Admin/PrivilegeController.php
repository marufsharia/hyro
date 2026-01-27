<?php

namespace Marufsharia\Hyro\Http\Controllers\Admin;

use Marufsharia\Hyro\Models\Privilege;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PrivilegeController extends Controller
{
    /**
     * Display a listing of privileges.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $privileges = Privilege::withCount('roles')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return view('hyro::admin.privileges.index', compact('privileges'));
    }

    /**
     * Show the form for creating a new privilege.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('hyro::admin.privileges.create');
    }

    /**
     * Store a newly created privilege.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hyro_privileges,name',
            'slug' => 'required|string|max:255|unique:hyro_privileges,slug|alpha_dash',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Privilege::create($validated);

        return redirect()->route('hyro.admin.privileges.index')
            ->with('success', 'Privilege created successfully.');
    }

    /**
     * Show the form for editing the specified privilege.
     *
     * @param  \Marufsharia\Hyro\Models\Privilege  $privilege
     * @return \Illuminate\View\View
     */
    public function edit(Privilege $privilege): View
    {
        return view('hyro::admin.privileges.edit', compact('privilege'));
    }

    /**
     * Update the specified privilege.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Marufsharia\Hyro\Models\Privilege  $privilege
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Privilege $privilege): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:hyro_privileges,name,' . $privilege->id,
            'slug' => 'required|string|max:255|unique:hyro_privileges,slug,' . $privilege->id . '|alpha_dash',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $privilege->update($validated);

        return redirect()->route('hyro.admin.privileges.index')
            ->with('success', 'Privilege updated successfully.');
    }

    /**
     * Remove the specified privilege.
     *
     * @param  \Marufsharia\Hyro\Models\Privilege  $privilege
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Privilege $privilege): RedirectResponse
    {
        // Don't allow deletion of core admin privileges
        $corePrivileges = ['access-hyro-admin', 'view-roles', 'create-roles', 'edit-roles', 'delete-roles'];

        if (in_array($privilege->slug, $corePrivileges)) {
            return back()->with('error', 'Cannot delete core admin privileges.');
        }

        $privilege->delete();

        return redirect()->route('hyro.admin.privileges.index')
            ->with('success', 'Privilege deleted successfully.');
    }
}
