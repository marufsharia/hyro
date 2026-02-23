<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Admin Panel Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure admin panel behavior and appearance</p>
    </div>

    <form action="{{ route('hyro.admin.settings.update.admin') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <!-- Admin Prefix -->
        <div>
            <label for="admin_prefix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Admin Route Prefix
            </label>
            <input type="text" id="admin_prefix" name="admin_prefix" 
                value="{{ $settings['admin_prefix'] }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="admin/hyro">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">URL prefix for admin routes (e.g., admin/hyro)</p>
            <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">⚠️ Clear route cache after changing this setting</p>
        </div>

        <!-- Pagination Limit -->
        <div>
            <label for="pagination_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Pagination Limit
            </label>
            <input type="number" id="pagination_limit" name="pagination_limit" 
                value="{{ $settings['pagination_limit'] }}"
                min="5" max="100"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="15">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Number of items per page in admin listings</p>
        </div>

        <!-- Layout -->
        <div>
            <label for="layout" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Admin Layout
            </label>
            <input type="text" id="layout" name="layout" 
                value="{{ $settings['layout'] }}"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="hyro::admin.layouts.app">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Blade layout view for admin panel</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button type="submit" 
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>

            <form action="{{ route('hyro.admin.settings.cache.route') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Clear Route Cache
                </button>
            </form>
        </div>
    </form>
</div>
