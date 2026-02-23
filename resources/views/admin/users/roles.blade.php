{{-- resources/views/admin/users/roles.blade.php --}}
@extends('hyro::admin.layouts.app')

@section('header', 'Manage User Roles')

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    User Roles: {{ $user->name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    Manage role assignments for {{ $user->email }}
                </p>
            </div>
            <a href="{{ route('hyro.admin.dashboard') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">User Information</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center">
                <img class="w-16 h-16 rounded-full mr-4"
                     src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff&size=128"
                     alt="{{ $user->name }}">
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                        </div>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                            Active
                        </span>
                    </div>
                    <div class="mt-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-medium">Member since:</span>
                            {{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-medium">Last updated:</span>
                            {{ $user->updated_at ? $user->updated_at->format('F d, Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Roles -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Current Roles</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Roles currently assigned to this user</p>
        </div>
        <div class="p-6">
            @if($user->roles->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($user->roles as $role)
                        <div class="inline-flex items-center px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800">
                            <span class="text-indigo-700 dark:text-indigo-300 font-medium">{{ $role->name }}</span>
                            <span class="ml-2 text-xs text-indigo-500 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/30 px-2 py-0.5 rounded">
                                {{ $role->privileges_count ?? $role->privileges->count() }} privileges
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.272 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No roles assigned to this user</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Assign roles using the form below</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Manage Roles Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manage Role Assignments</h3>
            <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Select or deselect roles for this user</p>
        </div>

        <form action="{{ route('hyro.admin.users.roles.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-green-800 dark:text-green-300">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-red-800 dark:text-red-300">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Roles Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    @foreach($roles as $role)
                        @php
                            $isAssigned = $user->roles->contains($role);
                            $isSuperAdmin = $role->slug === config('hyro.super_admin_role', 'super-admin');

                            // Check if this is the last super admin
                            $isLastSuperAdmin = false;
                            if ($isSuperAdmin && $isAssigned) {
                                try {
                                    $superAdminCount = \App\Models\User::whereHas('roles', function($query) use ($role) {
                                        $query->where('hyro_roles.id', $role->id);
                                    })->count();
                                    $isLastSuperAdmin = $superAdminCount <= 1;
                                } catch (\Exception $e) {
                                    // Handle error gracefully
                                    $isLastSuperAdmin = false;
                                }
                            }
                        @endphp

                        <div class="border rounded-lg p-4 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors {{ $isAssigned ? 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/10' : 'border-gray-200 dark:border-gray-800' }}">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input
                                        type="checkbox"
                                        name="roles[]"
                                        value="{{ $role->id }}"
                                        id="role_{{ $role->id }}"
                                        {{ $isAssigned ? 'checked' : '' }}
                                        {{ $isLastSuperAdmin ? 'disabled' : '' }}
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:border-gray-700 dark:bg-gray-800"
                                    >
                                </div>
                                <div class="ml-3 flex-1">
                                    <label for="role_{{ $role->id }}" class="font-medium text-gray-900 dark:text-white flex items-center justify-between">
                                        <span>{{ $role->name }}</span>
                                        @if($isSuperAdmin)
                                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">
                                                Super Admin
                                            </span>
                                        @endif
                                    </label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $role->description ?? 'No description' }}</p>

                                    <div class="mt-2">
                                        @if(isset($role->privileges) && $role->privileges->count() > 0)
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($role->privileges->take(3) as $privilege)
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                        {{ $privilege->name }}
                                                    </span>
                                                @endforeach
                                                @if($role->privileges->count() > 3)
                                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                        +{{ $role->privileges->count() - 3 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">No privileges assigned</span>
                                        @endif
                                    </div>

                                    @if($isLastSuperAdmin)
                                        <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 rounded text-xs text-yellow-700 dark:text-yellow-300">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.272 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                            Cannot remove the last super administrator
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium">{{ $user->roles->count() }}</span> of
                        <span class="font-medium">{{ $roles->count() }}</span> roles selected
                    </div>
                    <div class="flex gap-3">
                        <button type="reset" class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            Reset Changes
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Role Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Roles</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $roles->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">User Privileges</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            $totalPrivileges = 0;
                            foreach ($user->roles as $role) {
                                $totalPrivileges += isset($role->privileges) ? $role->privileges->count() : 0;
                            }
                        @endphp
                        {{ $totalPrivileges }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Super Admins</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        @php
                            $superAdminCount = 0;
                            try {
                                $superAdminRole = $roles->firstWhere('slug', config('hyro.super_admin_role', 'super-admin'));
                                if ($superAdminRole) {
                                    $superAdminCount = \App\Models\User::whereHas('roles', function($query) use ($superAdminRole) {
                                        $query->where('hyro_roles.id', $superAdminRole->id);
                                    })->count();
                                }
                            } catch (\Exception $e) {
                                // Handle error gracefully
                            }
                        @endphp
                        {{ $superAdminCount }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
