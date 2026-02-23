<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Plugin Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure plugin system behavior</p>
    </div>

    <form action="{{ route('hyro.admin.settings.update.plugins') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <!-- Auto Load -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="hidden" name="auto_load" value="0">
                <input type="checkbox" name="auto_load" value="1" 
                    {{ $settings['auto_load'] ? 'checked' : '' }}
                    class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Auto-Load Plugins</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Automatically load enabled plugins on application boot</p>
        </div>

        <!-- Allow Disable -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="hidden" name="allow_disable" value="0">
                <input type="checkbox" name="allow_disable" value="1" 
                    {{ $settings['allow_disable'] ? 'checked' : '' }}
                    class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Allow Plugin Disable</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Show disable button in plugin manager UI</p>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-medium mb-1">Plugin System Behavior</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Auto-Load: When enabled, active plugins load automatically on boot</li>
                        <li>Allow Disable: When disabled, users cannot disable plugins via UI (useful for critical plugins)</li>
                    </ul>
                </div>
            </div>
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
        </div>
    </form>
</div>
