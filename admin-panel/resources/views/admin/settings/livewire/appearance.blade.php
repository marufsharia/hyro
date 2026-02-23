<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Appearance & Branding</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customize the look and feel of your admin panel</p>
    </div>

    <div class="p-6 space-y-6">
        <!-- Application Name -->
        <div>
            <label for="app_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Application Name
            </label>
            <input type="text" wire:model="app_name" id="app_name"
                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Hyro Admin">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Displayed in browser title and header</p>
        </div>

        <!-- Logo & Favicon -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="app_logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Logo URL
                </label>
                <input type="text" wire:model="app_logo" id="app_logo"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="/images/logo.png">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Path to your logo image</p>
            </div>

            <div>
                <label for="app_favicon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Favicon URL
                </label>
                <input type="text" wire:model="app_favicon" id="app_favicon"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="/favicon.ico">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Path to your favicon</p>
            </div>
        </div>

        <!-- Primary Color -->
        <div>
            <label for="primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Primary Color
            </label>
            <div class="flex items-center space-x-3">
                <input type="color" wire:model="primary_color" id="primary_color"
                    class="h-10 w-20 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer">
                <input type="text" wire:model="primary_color"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="#3B82F6">
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Main accent color for buttons and links</p>
        </div>

        <!-- Theme Mode -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Theme Mode
            </label>
            <div class="grid grid-cols-3 gap-3">
                <button type="button" wire:click="updateThemeMode('light')"
                    class="flex flex-col items-center justify-center p-4 border-2 rounded-lg transition-all {{ $theme_mode === 'light' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400' }}">
                    <svg class="w-8 h-8 mb-2 {{ $theme_mode === 'light' ? 'text-blue-600' : 'text-gray-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span
                        class="text-sm font-medium {{ $theme_mode === 'light' ? 'text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">Light</span>
                </button>

                <button type="button" wire:click="updateThemeMode('dark')"
                    class="flex flex-col items-center justify-center p-4 border-2 rounded-lg transition-all {{ $theme_mode === 'dark' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400' }}">
                    <svg class="w-8 h-8 mb-2 {{ $theme_mode === 'dark' ? 'text-blue-600' : 'text-gray-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span
                        class="text-sm font-medium {{ $theme_mode === 'dark' ? 'text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">Dark</span>
                </button>

                <button type="button" wire:click="updateThemeMode('system')"
                    class="flex flex-col items-center justify-center p-4 border-2 rounded-lg transition-all {{ $theme_mode === 'system' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400' }}">
                    <svg class="w-8 h-8 mb-2 {{ $theme_mode === 'system' ? 'text-blue-600' : 'text-gray-400' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span
                        class="text-sm font-medium {{ $theme_mode === 'system' ? 'text-blue-700 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }}">System</span>
                </button>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Choose your preferred color scheme (changes apply instantly)</p>
        </div>

        <!-- Sidebar Collapsed by Default -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model="sidebar_collapsed" class="sr-only peer">
                <div
                    class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600">
                </div>
                <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-300">Collapse Sidebar by
                    Default</span>
            </label>
            <p class="mt-1 ml-14 text-xs text-gray-500 dark:text-gray-400">Start with a collapsed sidebar for more
                screen space</p>
        </div>

        <!-- RTL Support Info -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300">RTL Language Support</h4>
                    <p class="mt-1 text-xs text-blue-700 dark:text-blue-400">
                        Sidebar position automatically adjusts based on language direction.
                        Arabic, Hebrew, Farsi, and Urdu will display the sidebar on the right side.
                        Change the locale in General Settings to test RTL layout.
                    </p>
                </div>
            </div>
        </div>

<!-- Menu Icons Section (from Menu Icon Customizer Plugin) -->
@if (class_exists('\Codebusket\MenuIconCustomizer\Plugin') && app(\Marufsharia\Hyro\Support\Plugins\PluginManager::class)->isPluginActive('menu-icon-customizer'))
    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6" x-data="{ showCustomMenuSection: false }">
        
        <!-- Toggle Button -->
        <button @click="showCustomMenuSection = !showCustomMenuSection"
                class="w-full text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white flex items-center justify-between gap-2 group focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg px-2 py-2 transition-colors duration-200">
            
            <!-- Left side: Icon and Title -->
            <div class="flex items-center gap-3 pointer-cursor">
                <!-- Menu icon with background -->
                <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white pointer-cursor">Menu Icons</h3>
            </div>
            
            <!-- Right side: Expand/Collapse Indicator -->
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-full px-3 py-1.5 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors duration-200">
                <!-- Text indicator that changes based on state -->
                <span x-show="!showCustomMenuSection" 
                      x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-150"
                      x-transition:leave-start="opacity-100"
                      x-transition:leave-end="opacity-0"
                      x-cloak>
                    Expand
                </span>
                <span x-show="showCustomMenuSection" 
                      x-transition:enter="transition-opacity duration-150"
                      x-transition:enter-start="opacity-0"
                      x-transition:enter-end="opacity-100"
                      x-transition:leave="transition-opacity duration-150"
                      x-transition:leave-start="opacity-100"
                      x-transition:leave-end="opacity-0"
                      x-cloak>
                    Collapse
                </span>
                
                <!-- Animated arrow with rotation -->
                <svg class="w-4 h-4 transition-all duration-300 ease-in-out" 
                     :class="{ 'rotate-180': showCustomMenuSection }" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </button>
        
        <!-- Content Panel with Smooth Animation -->
        <div x-show="showCustomMenuSection" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 transform -translate-y-3 scale-95"
             class="mt-4 overflow-hidden"
             x-cloak>
            
            <!-- Content wrapper with subtle styling -->
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                @include('menu-icon-customizer::settings-section')
            </div>
            
            <!-- Optional: Footer note -->
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 px-1">
                Customize your menu icons appearance
            </p>
        </div>
        
    </div>

 
@endif
        <!-- Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="saveAppearance"
                class="inline-flex items-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // Listen for theme mode updates - simplified without event data
        Livewire.on('theme-mode-updated', () => {
            // Just reload the page - the new theme mode is already saved
            setTimeout(() => {
                window.location.reload();
            }, 100);
        });
        
        // Listen for page refresh event (from Save Changes button)
        Livewire.on('refresh-page', () => {
            // Delay reload to allow toast to show
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        });
    });
</script>
@endpush
