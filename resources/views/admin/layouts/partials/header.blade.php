<header class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm transition-colors duration-300">
    <div class="flex items-center justify-between h-16 px-4 md:px-6">
        <div class="flex items-center ltr:space-x-2 rtl:space-x-reverse md:ltr:space-x-4 md:rtl:space-x-reverse flex-1 min-w-0">
            <!-- Mobile menu button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Desktop sidebar toggle -->
            <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:block text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Search Bar -->
            <div class="relative flex-1 max-w-2xl" x-data="{ 
                showResults: @entangle('showResults').live,
                selectedIndex: @entangle('selectedIndex').live,
                handleKeydown(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        $wire.selectNext();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        $wire.selectPrevious();
                    } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
                        e.preventDefault();
                        const url = $wire.selectCurrent();
                        if (url) window.location.href = url;
                    }
                }
            }">
                <div class="relative">
                    <div class="absolute inset-y-0 ltr:left-0 rtl:right-0 flex items-center ltr:pl-3 rtl:pr-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search pages, features... (Ctrl+K)"
                        class="block w-full ltr:pl-10 rtl:pr-10 ltr:pr-10 rtl:pl-10 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:border-transparent transition-all duration-200"
                        @keydown.escape="showResults = false; $wire.clearSearch()"
                        @keydown="handleKeydown($event)"
                        @keydown.ctrl.k.prevent="$el.focus()"
                        @keydown.meta.k.prevent="$el.focus()"
                    >
                    @if($search)
                        <button 
                            wire:click="clearSearch"
                            type="button"
                            class="absolute inset-y-0 ltr:right-0 rtl:left-0 flex items-center ltr:pr-3 rtl:pl-3 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                    
                    <!-- Loading indicator -->
                    @if($isSearching)
                        <div class="absolute inset-y-0 ltr:right-10 rtl:left-10 flex items-center">
                            <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Search Results Dropdown -->
                @if($showResults && count($searchResults) > 0)
                <div 
                    class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[32rem] overflow-hidden z-50"
                    x-show="showResults"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.away="showResults = false"
                >
                    <!-- Results header -->
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ count($searchResults) }} {{ count($searchResults) === 1 ? 'Result' : 'Results' }}
                            </p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-xs">↑↓</kbd>
                                <span>Navigate</span>
                                <kbd class="px-2 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-xs">Enter</kbd>
                                <span>Select</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results list -->
                    <div class="overflow-y-auto max-h-96 py-2">
                        @foreach($searchResults as $index => $result)
                        <a 
                            href="{{ $result['url'] }}"
                            class="flex items-center px-4 py-3 transition-all duration-150 {{ $selectedIndex === $index ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50 border-l-4 border-transparent' }}"
                            @click="showResults = false"
                            wire:key="search-result-{{ $index }}"
                        >
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $selectedIndex === $index ? 'bg-gradient-to-br from-blue-500 to-blue-600' : 'bg-gradient-to-br from-gray-400 to-gray-500' }} flex items-center justify-center shadow-sm transition-all duration-150">
                                @if($result['icon'] === 'home')
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                @elseif($result['icon'] === 'puzzle')
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                </svg>
                                @elseif($result['icon'] === 'users')
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                @elseif($result['icon'] === 'shield')
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                @elseif($result['icon'] === 'user')
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                                @endif
                            </div>
                            <div class="ml-4 flex-1 min-w-0">
                                <p class="text-sm font-semibold {{ $selectedIndex === $index ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }} truncate">
                                    {{ $result['title'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                    {{ $result['description'] ?? $result['group'] }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2 flex-shrink-0 ml-3">
                                <span class="px-2 py-1 text-xs font-medium rounded {{ $selectedIndex === $index ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                    {{ $result['group'] }}
                                </span>
                                <svg class="w-4 h-4 {{ $selectedIndex === $index ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @elseif($showResults && count($searchResults) === 0 && strlen($search) > 0)
                <div 
                    class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50"
                    x-show="showResults"
                    x-transition
                    @click.away="showResults = false"
                >
                    <div class="p-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">No results found</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Try searching for "dashboard", "plugins", or "settings"</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center ltr:space-x-2 rtl:space-x-reverse md:ltr:space-x-4 md:rtl:space-x-reverse flex-shrink-0">
            <!-- Dark Mode Toggle (only show if theme mode is 'system') -->
            @if(hyro_config('appearance.theme_mode', 'system') === 'system')
            <button 
                @click="darkMode = !darkMode" 
                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                title="Toggle Dark Mode"
            >
                <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
            @endif

            <!-- Notifications Dropdown -->
            <div class="hidden sm:block" x-data="{ open: false }">
                <button 
                    @click="open = !open"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors relative"
                    x-ref="notificationButton"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1 ltr:right-1 rtl:left-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- Notifications Dropdown Menu -->
                <div 
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    x-anchor.bottom-end.offset.8="$refs.notificationButton"
                    class="fixed w-80 bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 z-50"
                    style="display: none;"
                >
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <a href="#" class="flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">New plugin available</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">ContactList plugin is ready to install</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">2 minutes ago</p>
                            </div>
                        </a>
                        <a href="#" class="flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">System updated</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">All plugins are up to date</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">1 hour ago</p>
                            </div>
                        </a>
                    </div>
                    <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                        <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- User Menu Dropdown -->
            @auth
            <div class="hidden sm:block" x-data="{ open: false }">
                <button 
                    @click="open = !open"
                    class="flex items-center ltr:space-x-2 rtl:space-x-reverse p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    x-ref="userButton"
                >
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover" wire:key="header-avatar-{{ auth()->id() }}">
                </button>

                <!-- User Dropdown Menu -->
                <div 
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    x-anchor.bottom-end.offset.8="$refs.userButton"
                    class="fixed w-64 bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 z-50"
                    style="display: none;"
                >
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ auth()->user()->email }}</p>
                    </div>
                    <div class="py-2">
                        <a href="{{ route('profile.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4 ltr:mr-3 rtl:ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            My Profile
                        </a>
                        <a href="{{ route('hyro.admin.settings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4 ltr:mr-3 rtl:ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Settings
                        </a>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 py-2">
                        <form method="POST" action="{{ route('hyro.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
</header>


<script>
    // Global keyboard shortcut for search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[placeholder*="Ctrl+K"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
</script>
