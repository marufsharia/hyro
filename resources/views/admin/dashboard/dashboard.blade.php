@extends('hyro::admin.layouts.app')

@section('header', 'Dashboard Overview')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
            Welcome back, {{ auth()->user()->name }}!
        </h2>
        <p class="text-gray-600 dark:text-gray-400 mt-1">
            Here's what's happening with your system today
        </p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['users'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-600 dark:text-green-400 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            {{ rand(4, 15) }}%
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Roles -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Roles</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['roles'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-600 dark:text-green-400 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            {{ rand(4, 15) }}%
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Privileges -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Privileges</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['privileges'] ?? 0 }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-600 dark:text-green-400 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            {{ rand(4, 15) }}%
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Sessions</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ rand(10, 50) }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-green-600 dark:text-green-400 text-sm font-medium flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            {{ rand(4, 15) }}%
                        </span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-2">vs last month</span>
                    </div>
                </div>
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Latest user registrations and role assignments</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('hyro.admin.users.roles.edit', auth()->user()) }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors text-sm font-medium">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        View All Users
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($stats['recent_users'] ?? [] as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img class="w-8 h-8 rounded-full mr-3"
                                     src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                     alt="{{ $user->name }}">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                    Active
                                </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400">
                                            {{ $role->name }}
                                        </span>
                                @empty
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic">No roles</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex gap-2">
                                <button class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            No recent users found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
            <div class="flex flex-col gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">New user registered</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">user@example.com • 2 minutes ago</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">Role assignment updated</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Admin role added • 5 minutes ago</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">Privilege created</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">New privilege: manage_users • 8 minutes ago</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-2 h-2 bg-orange-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white">System backup completed</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Database backup • 12 minutes ago</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Status</h3>
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-900 dark:text-white">Server Status</span>
                    </div>
                    <span class="text-sm text-green-600 dark:text-green-400 font-medium">Online</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-900 dark:text-white">Database</span>
                    </div>
                    <span class="text-sm text-green-600 dark:text-green-400 font-medium">Active</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm text-gray-900 dark:text-white">Cache</span>
                    </div>
                    <span class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Warning</span>
                </div>
                <div class="mt-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Server Load</span>
                        <span>65%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: 65%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
