<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">RBAC Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure role-based access control</p>
    </div>

    <div class="p-6 space-y-6">
        <!-- RBAC Enabled -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="rbac_enabled" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable RBAC System</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Enable role-based access control and permission checks</p>
        </div>

        <!-- Default Role -->
        <div>
            <label for="default_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Default Registration Role
            </label>
            @if(count($availableRoles) > 0)
                <select wire:model="default_role" id="default_role" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Select a role --</option>
                    @foreach($availableRoles as $role)
                        <option value="{{ $role['slug'] }}">{{ $role['name'] }} ({{ $role['slug'] }})</option>
                    @endforeach
                </select>
            @else
                <input type="text" wire:model="default_role" id="default_role" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="user">
            @endif
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Role automatically assigned to new users during registration</p>
        </div>

        <!-- Super Admin Role -->
        <div>
            <label for="super_admin_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Super Admin Role
            </label>
            <input type="text" wire:model="super_admin_role" id="super_admin_role" 
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="super-admin">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Role with unrestricted access to all features</p>
        </div>

        <!-- Cache Permissions -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="cache_permissions" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Cache Permissions</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Cache permission checks for better performance</p>
        </div>

        <!-- Info Box -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-amber-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm text-amber-800 dark:text-amber-300">
                    <p class="font-medium mb-1">Important</p>
                    <p class="text-xs">Disabling RBAC will remove all permission checks. Only disable if you have alternative authorization in place.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="saveRbac" 
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>

            <button wire:click="clearAllCaches" 
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Clear Cache
            </button>
        </div>
    </div>
</div>
