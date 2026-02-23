<!DOCTYPE html>
@php
    $themeMode = hyro_config('appearance.theme_mode', 'system');
    $locale = hyro_config('locale', 'en');
    $isRtl = in_array($locale, ['ar', 'he', 'fa', 'ur']); // RTL languages
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" 
    x-data="{ 
        themeMode: '{{ $themeMode }}',
        darkMode: false,
        initTheme() {
            if (this.themeMode === 'dark') {
                this.darkMode = true;
            } else if (this.themeMode === 'light') {
                this.darkMode = false;
            } else {
                // System mode - check OS preference
                this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            
            // Watch for system theme changes if in system mode
            if (this.themeMode === 'system') {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    this.darkMode = e.matches;
                });
            }
        }
    }" 
    x-init="initTheme(); $watch('darkMode', val => { if (themeMode === 'system') localStorage.setItem('darkMode', val); })"
    :class="{ 'dark': darkMode }"  
    class="transition-colors duration-300">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ hyro_config('appearance.app_name', config('app.name', 'Hyro Admin')) }}</title>

    @php
        $favicon = hyro_config('appearance.app_favicon', '');
    @endphp
    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif

    <!-- Initialize dark mode before Alpine loads -->
    <script>
        // Set dark class immediately to prevent flash
        (function() {
            const themeMode = '{{ $themeMode }}';
            let shouldBeDark = false;
            
            if (themeMode === 'dark') {
                shouldBeDark = true;
            } else if (themeMode === 'light') {
                shouldBeDark = false;
            } else {
                // System mode
                shouldBeDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
            
            if (shouldBeDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @hyroCss
    @livewireStyles
    @stack('styles')

    <style>
        :root {
            --primary-color: {{ hyro_config('appearance.primary_color', '#3B82F6') }};
        }
        
        /* Apply primary color to various elements */
        .bg-blue-500, .bg-blue-600 {
            background-color: var(--primary-color) !important;
        }
        
        .text-blue-500, .text-blue-600, .text-blue-700 {
            color: var(--primary-color) !important;
        }
        
        .border-blue-500 {
            border-color: var(--primary-color) !important;
        }
        
        .ring-blue-500 {
            --tw-ring-color: var(--primary-color) !important;
        }
        
        .from-blue-500, .to-blue-600 {
            --tw-gradient-from: var(--primary-color) !important;
            --tw-gradient-to: var(--primary-color) !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 transition-colors duration-300" 
    x-data="{ 
        sidebarOpen: {{ hyro_config('appearance.sidebar_collapsed', false) ? 'false' : 'true' }}, 
        mobileMenuOpen: false 
    }">
    <!-- Mobile Menu Backdrop -->
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm z-40 lg:hidden" style="display: none;"></div>

    <div class="min-h-screen flex flex-col">
        <!-- Sidebar (Livewire Component) -->
        @livewire('hyro::admin.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300"
            x-bind:style="window.innerWidth >= 1024 && sidebarOpen ? '{{ $isRtl ? 'margin-right: 16rem' : 'margin-left: 16rem' }}' : ''"
            @resize.window="$el.style = window.innerWidth >= 1024 && sidebarOpen ? '{{ $isRtl ? 'margin-right: 16rem' : 'margin-left: 16rem' }}' : ''">
            <!-- Top Header (Livewire Component) -->
            @livewire('hyro::admin.header')

            <!-- Page Content -->
            <main class="flex-1 p-4 md:p-6 overflow-x-hidden transition-colors duration-300">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                    {{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="text-green-500 hover:text-green-700 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="text-red-500 hover:text-red-700 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
                {{ $slot ?? '' }}
            </main>

            <!-- Footer -->
            <footer
                class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4 px-4 md:px-6 mt-auto">
                <div
                    class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500 dark:text-gray-400 space-y-2 sm:space-y-0">
                    <p>&copy; {{ date('Y') }} {{ hyro_config('appearance.app_name', 'Hyro') }}. All rights reserved.</p>
                    <p>Version 1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    @hyroJs
    @livewireScripts
    
    <!-- Hyro Alert System -->
    <x-hyro::alert-scripts :livewire="true" />
    
    @stack('scripts')
</body>

</html>
