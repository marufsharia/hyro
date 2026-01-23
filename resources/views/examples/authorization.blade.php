
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hyro Blade Directives Example</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Hyro Stacks -->
@hyro_styles
        <style>
            .hyro-debug {
    border-left: 4px solid #4F46E5;
                background-color: #F5F3FF;
                padding: 1rem;
                margin: 1rem 0;
                border-radius: 0.375rem;
            }

            .hyro-debug h3 {
    color: #4F46E5;
    font-weight: 600;
                margin-bottom: 0.5rem;
            }
        </style>
@endhyro_styles

    @hyro_scripts
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Tooltip functionality for privilege chips
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-10 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-sm';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.left = e.pageX + 'px';
            tooltip.style.top = (e.pageY - 40) + 'px';
            tooltip.id = 'hyro-tooltip';
            document.body.appendChild(tooltip);
        });

        element.addEventListener('mouseleave', function() {
            const tooltip = document.getElementById('hyro-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
            });
</script>
@endhyro_scripts
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">Hyro Authorization Demo</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- User info with roles -->
                        <div class="text-sm text-gray-700">
@auth
    <div class="flex items-center space-x-2">
        <span>{{ auth()->user()->name }}</span>
        <div class="flex space-x-1">
            @hasrole('admin')
            <x-hyro-role name="admin" badge="true" color="red" size="xs" />
            @endhasrole
            @hasrole('editor')
            <x-hyro-role name="editor" badge="true" color="blue" size="xs" />
            @endhasrole
        </div>
    </div>
    @endauth
    </div>
    </div>
    </div>
    </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Demo Section: Basic Directives -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Authorization Directives</h2>

                <div class="space-y-4">
                    <!-- @hasrole directive -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Role-based Access</h3>
                        <div class="space-y-2">
                            @hasrole('admin')
                            <div class="bg-green-50 border border-green-200 rounded p-3">
                                <p class="text-green-800">✅ This content is visible to users with the 'admin' role.</p>
                            </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                    <p class="text-gray-600">This content requires the 'admin' role.</p>
                                </div>
                                @endhasrole

                                @hasanyrole(['admin', 'editor'])
                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                    <p class="text-blue-800">✅ This content is visible to users with either 'admin' or 'editor' role.</p>
                                </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                        <p class="text-gray-600">This content requires either 'admin' or 'editor' role.</p>
                                    </div>
                                    @endhasanyrole
                        </div>
                    </div>

                    <!-- @hasprivilege directive -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Privilege-based Access</h3>
                        <div class="space-y-2">
                            @hasprivilege('users.create')
                            <div class="bg-green-50 border border-green-200 rounded p-3">
                                <p class="text-green-800">✅ This content is visible to users with 'users.create' privilege.</p>
                                <button class="mt-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                    Create User
                                </button>
                            </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                    <p class="text-gray-600">This content requires 'users.create' privilege.</p>
                                    <button class="mt-2 px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed" disabled>
                                        Create User
                                    </button>
                                </div>
                                @endhasprivilege

                                @hasanyprivilege(['users.edit', 'users.delete'])
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                                    <p class="text-yellow-800">⚠️ This user has user modification privileges.</p>
                                </div>
                                @endhasanyprivilege
                        </div>
                    </div>

                    <!-- @hyrocan directive -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Ability-based Access</h3>
                        <div class="space-y-2">
                            @hyrocan('reports.view')
                            <div class="bg-purple-50 border border-purple-200 rounded p-3">
                                <p class="text-purple-800">📊 Reports Dashboard (visible with 'reports.view' ability)</p>
                                <div class="mt-2 grid grid-cols-2 gap-4">
                                    <div class="bg-white p-3 rounded border">
                                        <h4 class="font-medium">Sales Report</h4>
                                        <p class="text-sm text-gray-600">Q1 2024 Performance</p>
                                    </div>
                                    <div class="bg-white p-3 rounded border">
                                        <h4 class="font-medium">User Analytics</h4>
                                        <p class="text-sm text-gray-600">Active Users & Engagement</p>
                                    </div>
                                </div>
                            </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                    <p class="text-gray-600">📊 Reports Dashboard requires 'reports.view' ability.</p>
                                </div>
                                @endhyrocan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Section: Component Usage -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Component-based Authorization</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- hyro-protected component -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Protected Component</h3>
                        <p class="text-sm text-gray-600 mb-3">Using &lt;x-hyro-protected&gt; component</p>

                        <x-hyro-protected ability="settings.manage">
                            <div class="bg-indigo-50 border border-indigo-200 rounded p-3">
                                <h4 class="font-medium text-indigo-800">⚙️ System Settings</h4>
                                <p class="text-sm text-indigo-600">Only visible to users with 'settings.manage' privilege.</p>
                                <button class="mt-2 px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                    Configure
                                </button>
                            </div>
                        </x-hyro-protected>

                        <x-hyro-protected role="admin">
                            <div class="mt-3 bg-red-50 border border-red-200 rounded p-3">
                                <h4 class="font-medium text-red-800">🛡️ Admin Panel</h4>
                                <p class="text-sm text-red-600">Only visible to users with 'admin' role.</p>
                            </div>
                        </x-hyro-protected>
                    </div>

                    <!-- hyro-status component -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Status Indicators</h3>
                        <p class="text-sm text-gray-600 mb-3">Using &lt;x-hyro-status&gt; component</p>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span>Admin Access:</span>
                                <x-hyro-status role="admin" />
                            </div>

                            <div class="flex items-center justify-between">
                                <span>Create Users:</span>
                                <x-hyro-status privilege="users.create" />
                            </div>

                            <div class="flex items-center justify-between">
                                <span>Delete Users:</span>
                                <x-hyro-status privilege="users.delete" />
                            </div>

                            <div class="flex items-center justify-between">
                                <span>View Reports:</span>
                                <x-hyro-status ability="reports.view" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demo Section: Advanced Usage -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Advanced Directives & Helpers</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Inline Helpers -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Inline Helpers</h3>

                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">User's Roles:</p>
                                <p class="font-mono text-sm bg-gray-100 p-2 rounded">
                                    @hyro_roles
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">User's Privileges (first 5):</p>
                                <p class="font-mono text-sm bg-gray-100 p-2 rounded">
                                    @hyro_privileges
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Role Badges:</p>
                                <div class="flex space-x-2">
                                    {{ app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->roleBadges(null, [
                                        'class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    ]) }}
                                </div>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600">Privilege Chips:</p>
                                <div class="flex flex-wrap gap-2">
                                    {{ app(\Marufsharia\Hyro\Blade\HyroBladeHelper::class)->privilegeChips(null, [
                                        'limit' => 3,
                                        'show_more' => true,
                                    ]) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complex Conditions -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-700 mb-2">Complex Conditions</h3>

                        <div class="space-y-4">
                            <!-- Multiple conditions -->
                            @hyro(['and' => ['users.view', 'users.create']])
                            <div class="bg-green-50 border border-green-200 rounded p-3">
                                <p class="text-green-800">✅ User has BOTH 'users.view' AND 'users.create' privileges.</p>
                            </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                    <p class="text-gray-600">Requires both 'users.view' AND 'users.create' privileges.</p>
                                </div>
                                @endhyro

                                <!-- OR conditions -->
                                @hyro(['or' => ['reports.view', 'analytics.view']])
                                <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                    <p class="text-blue-800">✅ User has EITHER 'reports.view' OR 'analytics.view' privilege.</p>
                                </div>
                                @else
                                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                        <p class="text-gray-600">Requires either 'reports.view' OR 'analytics.view' privilege.</p>
                                    </div>
                                    @endhyro

                                    <!-- Nested conditions -->
                                    @php
                                        $complexCondition = [
                                            'or' => [
                                                'admin',
                                                ['and' => ['editor', 'content.publish']]
                                            ]
                                        ];
                                    @endphp

                                    @hyro($complexCondition)
                                    <div class="bg-purple-50 border border-purple-200 rounded p-3">
                                        <p class="text-purple-800">✅ User is either admin OR (editor with content.publish privilege).</p>
                                    </div>
                                    @else
                                        <div class="bg-gray-50 border border-gray-200 rounded p-3">
                                            <p class="text-gray-600">Requires admin role OR (editor role with content.publish privilege).</p>
                                        </div>
                                        @endhyro
                        </div>
                    </div>
                </div>
            </div>

            <!-- Debug Information (only in debug mode) -->
            @if(config('app.debug'))
                <div class="mt-6 hyro-debug">
                    <h3>🔍 Hyro Debug Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <strong>Current User:</strong>
                            <div class="mt-1">
                                @auth
                                    ID: {{ auth()->id() }}<br>
                                    Name: {{ auth()->user()->name }}<br>
                                    Email: {{ auth()->user()->email }}
                                @else
                                    Not authenticated
                                @endauth
                            </div>
                        </div>
                        <div>
                            <strong>User Roles:</strong>
                            <div class="mt-1">
                                @auth
                                    @if(method_exists(auth()->user(), 'hyroRoleSlugs'))
                                        {{ implode(', ', auth()->user()->hyroRoleSlugs()) }}
                                    @else
                                        User doesn't have Hyro trait
                                    @endif
                                @else
                                    N/A
                                @endauth
                            </div>
                        </div>
                        <div>
                            <strong>User Privileges:</strong>
                            <div class="mt-1">
                                @auth
                                    @if(method_exists(auth()->user(), 'hyroPrivilegeSlugs'))
                                        {{ implode(', ', auth()->user()->hyroPrivilegeSlugs()) }}
                                    @else
                                        User doesn't have Hyro trait
                                    @endif
                                @else
                                    N/A
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>
    </div>

    <!-- Include Hyro stacks -->
    @hyro_stacks
    </body>
    </html>
