<nav class="bg-white shadow-sm border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo & Brand -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-900">{{ config('app.name', 'Laravel') }}</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors">
                    Home
                </a>
                <a href="{{ url('/about') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors">
                    About
                </a>
                <a href="{{ url('/contact') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors">
                    Contact
                </a>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50"
                             style="display: none;">
                            @if(Route::has('hyro.admin.dashboard'))
                            <a href="{{ route('hyro.admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Dashboard
                            </a>
                            @endif
                            @if(Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </a>
                            @endif
                            <div class="border-t border-gray-100"></div>
                            @if(Route::has('hyro.logout'))
                            <form method="POST" action="{{ route('hyro.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                            @else
                            <a href="{{ url('/admin/hyro/logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                Logout
                            </a>
                            @endif
                        </div>
                    </div>
                @else
                    @if(Route::has('hyro.login'))
                    <a href="{{ route('hyro.login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                        Login
                    </a>
                    @endif
                    @if(Route::has('hyro.register'))
                    <a href="{{ route('hyro.register') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Sign Up
                    </a>
                    @endif
                @endauth

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="md:hidden border-t border-gray-200"
         style="display: none;">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 font-medium">
                Home
            </a>
            <a href="{{ url('/about') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 font-medium">
                About
            </a>
            <a href="{{ url('/contact') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 font-medium">
                Contact
            </a>
        </div>
    </div>
</nav>
