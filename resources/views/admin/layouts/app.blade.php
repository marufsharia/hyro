<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Hyro Admin') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Styles -->
    @hyroCss
    @livewireStyles
    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Smooth transitions */
        * {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900" x-data="{ sidebarOpen: true, mobileMenuOpen: false }">
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Sidebar (Livewire Component) -->
        @livewire('hyro::admin.sidebar')

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-h-screen w-full lg:w-auto">
            <!-- Top Header (Livewire Component) -->
            @livewire('hyro::admin.header')

            <!-- Page Content -->
            <main class="flex-1 p-4 md:p-6 overflow-x-hidden">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                            </div>
                            <button @click="show = false" class="text-green-500 hover:text-green-700 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                            </div>
                            <button @click="show = false" class="text-red-500 hover:text-red-700 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
            <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-4 px-4 md:px-6 mt-auto">
                <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500 dark:text-gray-400 space-y-2 sm:space-y-0">
                    <p>&copy; {{ date('Y') }} Hyro. All rights reserved.</p>
                    <p>Version 1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    @hyroJs
    @livewireScripts
    @stack('scripts')
</body>
</html>
