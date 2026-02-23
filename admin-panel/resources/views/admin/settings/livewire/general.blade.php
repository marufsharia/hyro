<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">General Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure basic system settings</p>
    </div>

    <div class="p-6 space-y-6">
        <!-- Locale -->
        <div>
            <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Application Locale
            </label>
            <select wire:model="locale" id="locale" 
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="en">English</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
                <option value="de">German</option>
                <option value="ar">Arabic</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default language for the application</p>
        </div>

        <!-- Timezone -->
        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Timezone
            </label>
            <select wire:model="timezone" id="timezone" 
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="UTC">UTC</option>
                <option value="America/New_York">America/New York</option>
                <option value="America/Chicago">America/Chicago</option>
                <option value="America/Los_Angeles">America/Los Angeles</option>
                <option value="Europe/London">Europe/London</option>
                <option value="Europe/Paris">Europe/Paris</option>
                <option value="Asia/Dubai">Asia/Dubai</option>
                <option value="Asia/Tokyo">Asia/Tokyo</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default timezone for date/time display</p>
        </div>

        <!-- Maintenance Mode -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="maintenance_mode" class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Maintenance Mode</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Put the application in maintenance mode</p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="saveGeneral" 
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>
