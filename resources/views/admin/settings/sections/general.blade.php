<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">General Settings</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure basic system settings</p>
    </div>

    <form action="{{ route('hyro.admin.settings.update.general') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <!-- Locale -->
        <div>
            <label for="locale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Application Locale
            </label>
            <select id="locale" name="locale" 
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="en" {{ $settings['locale'] === 'en' ? 'selected' : '' }}>English</option>
                <option value="es" {{ $settings['locale'] === 'es' ? 'selected' : '' }}>Spanish</option>
                <option value="fr" {{ $settings['locale'] === 'fr' ? 'selected' : '' }}>French</option>
                <option value="de" {{ $settings['locale'] === 'de' ? 'selected' : '' }}>German</option>
                <option value="ar" {{ $settings['locale'] === 'ar' ? 'selected' : '' }}>Arabic</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default language for the application</p>
        </div>

        <!-- Timezone -->
        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Timezone
            </label>
            <select id="timezone" name="timezone" 
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="UTC" {{ $settings['timezone'] === 'UTC' ? 'selected' : '' }}>UTC</option>
                <option value="America/New_York" {{ $settings['timezone'] === 'America/New_York' ? 'selected' : '' }}>America/New York</option>
                <option value="America/Chicago" {{ $settings['timezone'] === 'America/Chicago' ? 'selected' : '' }}>America/Chicago</option>
                <option value="America/Los_Angeles" {{ $settings['timezone'] === 'America/Los_Angeles' ? 'selected' : '' }}>America/Los Angeles</option>
                <option value="Europe/London" {{ $settings['timezone'] === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                <option value="Europe/Paris" {{ $settings['timezone'] === 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                <option value="Asia/Dubai" {{ $settings['timezone'] === 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                <option value="Asia/Tokyo" {{ $settings['timezone'] === 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default timezone for date/time display</p>
        </div>

        <!-- Maintenance Mode -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="hidden" name="maintenance_mode" value="0">
                <input type="checkbox" name="maintenance_mode" value="1" 
                    {{ $settings['maintenance_mode'] ? 'checked' : '' }}
                    class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Enable Maintenance Mode</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Put the application in maintenance mode</p>
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
