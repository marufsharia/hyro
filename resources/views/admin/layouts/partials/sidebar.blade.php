<div>
    <!-- Mobile Sidebar -->
    <aside 
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" 
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300" 
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
        class="fixed inset-y-0 ltr:left-0 rtl:right-0 z-50 w-64 bg-white dark:bg-gray-800 ltr:border-r rtl:border-l border-gray-200 dark:border-gray-700 flex flex-col transition-colors duration-300 lg:hidden"
        @click.away="mobileMenuOpen = false">
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <a href="{{ route('hyro.admin.dashboard') }}" class="flex items-center space-x-3">
            @php
                $logo = hyro_config('appearance.app_logo', '');
                $appName = hyro_config('appearance.app_name', 'Hyro');
            @endphp
            
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $appName }}" class="w-8 h-8 object-contain">
            @else
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            @endif
            
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                {{ $appName }}
            </span>
        </a>
        <!-- Mobile Close Button -->
        <button @click="mobileMenuOpen = false"
            class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('hyro.admin.dashboard') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.dashboard') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('dashboard', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>') !!}
            </svg>
            Dashboard
        </a>

        <!-- System Section -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</p>
        </div>
        
        <!-- Roles -->
        <a href="{{ route('hyro.admin.roles.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.roles.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('roles', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>') !!}
            </svg>
            Roles
        </a>

        <!-- Privileges -->
        <a href="{{ route('hyro.admin.privileges.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.privileges.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('privileges', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>') !!}
            </svg>
            Privileges
        </a>

{{--         <!-- Plugins -->
        <a href="{{ route('hyro.admin.plugins.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.plugins.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('plugins', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>') !!}
            </svg>
            Plugins
        </a> --}}

        <!-- Dynamic Sidebar Items from Plugins/Modules -->
        @foreach (Hyro::sidebar() as $key => $sectionOrItem)
            @if (isset($sectionOrItem['group']) && isset($sectionOrItem['items']))
                @if ($sectionOrItem['group'] === 'Modules')
                    <!-- Collapsible Plugins Section -->
                    <div class="pt-4" x-data="{ moduleOpen: localStorage.getItem('sidebar_module_open') !== 'false' }" x-init="$watch('moduleOpen', value => localStorage.setItem('sidebar_module_open', value))"
                        :key="section_{{ $key }}">
                        <button @click="moduleOpen = !moduleOpen"
                            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! hyro_menu_icon('modules_group', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>') !!}</svg>
                                <span>{{ $sectionOrItem['group'] }}</span>
                                <span
                                    class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-purple-600 rounded-full">
                                    {{ count($sectionOrItem['items']) }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': moduleOpen }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="moduleOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2 space-y-1">
                            @foreach ($sectionOrItem['items'] as $item)
                                @if (isset($item['route']) && Route::has($item['route']))
                                    {{-- Plugin item with direct URL --}}
                                    <a href="{{ $item['route'] }}"
                                        class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['route'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $item['icon'] ??
                                            ' <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                                                                </svg>' }}
                                        {{ $item['title'] ?? '*Untitled' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif($sectionOrItem['group'] === 'Plugins')
                    <!-- Collapsible Plugins Section -->
                    <div class="pt-4" x-data="{ pluginsOpen: localStorage.getItem('sidebar_plugins_open') !== 'false' }" x-init="$watch('pluginsOpen', value => localStorage.setItem('sidebar_plugins_open', value))">
                        <button @click="pluginsOpen = !pluginsOpen"
                            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! hyro_menu_icon('modules_group', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>') !!}</svg>
                                <span>{{ $sectionOrItem['group'] }}</span>
                                <span
                                    class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-purple-600 rounded-full">
                                    {{ count($sectionOrItem['items']) }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-180': pluginsOpen }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="pluginsOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2 space-y-1">
                            @foreach ($sectionOrItem['items'] as $item)
                                @if (isset($item['url']))
                                    {{-- Plugin item with direct URL --}}
                                    <a href="{{ $item['url'] }}"
                                        class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['url'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                        </svg>
                                        {{ $item['title'] ?? 'Untitled' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif($sectionOrItem['group'] === 'Settings')
                    {{-- Skip Settings section - it's hardcoded above --}}
                @else
                    <!-- Regular Section with items -->
                    <div class="pt-4 pb-2">
                        <p
                            class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            {{ $sectionOrItem['group'] }}</p>
                    </div>
                    @foreach ($sectionOrItem['items'] as $item)
                        @if (isset($item['url']))
                            {{-- Plugin item with direct URL --}}
                            <a href="{{ $item['url'] }}"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['url'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg>
                                {{ $item['title'] ?? 'Untitled' }}
                            </a>
                        @elseif(isset($item['route']) && Route::has($item['route']))
                            {{-- Module item with route --}}
                            <a href="{{ route($item['route']) }}"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs($item['route'] . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                {{ $item['title'] ?? 'Untitled' }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @elseif(isset($sectionOrItem['route']) && Route::has($sectionOrItem['route']))
                <!-- Single item -->
                <a href="{{ route($sectionOrItem['route']) }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs($sectionOrItem['route'] . '*') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    {{ $sectionOrItem['title'] ?? 'Untitled' }}
                </a>
            @endif
        @endforeach
    </nav>
    <!-- Settings Link -->
    <div class="px-4 pb-2">
        <a href="{{ route('hyro.admin.settings.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.settings.*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{!! hyro_menu_icon('settings', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>') !!}
            </svg>
            Settings
        </a>
    </div>

    <!-- User Profile (Fixed at Bottom) -->
    <div class="mt-auto border-t border-gray-200 dark:border-gray-700">
        <!-- User Profile -->
        @auth
            <div class="p-4">
                <div class="flex items-center space-x-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                    <img
                        src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover" wire:key="sidebar-avatar-{{ auth()->id() }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('hyro.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                            title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="p-4">
                <a href="{{ route('hyro.login') }}"
                    class="flex items-center justify-center space-x-2 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Login</span>
                </a>
            </div>
        @endauth
    </div>
</aside>

<!-- Desktop Sidebar -->
<aside 
    x-show="sidebarOpen"
    class="hidden lg:flex w-64 bg-white dark:bg-gray-800 ltr:border-r rtl:border-l border-gray-200 dark:border-gray-700 flex-col transition-colors duration-300 fixed inset-y-0 ltr:left-0 rtl:right-0 z-40">
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
        <a href="{{ route('hyro.admin.dashboard') }}" class="flex items-center space-x-3">
            @php
                $logo = hyro_config('appearance.app_logo', '');
                $appName = hyro_config('appearance.app_name', 'Hyro');
            @endphp
            
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $appName }}" class="w-8 h-8 object-contain">
            @else
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            @endif
            
            <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                {{ $appName }}
            </span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('hyro.admin.dashboard') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.dashboard') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('dashboard', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>') !!}
            </svg>
            Dashboard
        </a>

        <!-- System Section -->
        <div class="pt-4 pb-2">
            <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</p>
        </div>
        
        <!-- Roles -->
        <a href="{{ route('hyro.admin.roles.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.roles.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('roles', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>') !!}
            </svg>
            Roles
        </a>

        <!-- Privileges -->
        <a href="{{ route('hyro.admin.privileges.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.privileges.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('privileges', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>') !!}
            </svg>
            Privileges
        </a>

        <!-- Plugins -->
        <a href="{{ route('hyro.admin.plugins.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.plugins.*') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! hyro_menu_icon('plugins', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>') !!}
            </svg>
            Plugins
        </a>

        <!-- Dynamic Sidebar Items from Plugins/Modules -->
        @foreach (Hyro::sidebar() as $key => $sectionOrItem)
            @if (isset($sectionOrItem['group']) && isset($sectionOrItem['items']))
                @if ($sectionOrItem['group'] === 'Modules')
                    <!-- Collapsible Plugins Section -->
                    <div class="pt-4" x-data="{ moduleOpen: localStorage.getItem('sidebar_module_open') !== 'false' }" x-init="$watch('moduleOpen', value => localStorage.setItem('sidebar_module_open', value))"
                        :key="section_{{ $key }}">
                        <button @click="moduleOpen = !moduleOpen"
                            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! hyro_menu_icon('modules_group', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>') !!}</svg>
                                <span>{{ $sectionOrItem['group'] }}</span>
                                <span
                                    class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-purple-600 rounded-full">
                                    {{ count($sectionOrItem['items']) }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': moduleOpen }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="moduleOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2 space-y-1">
                            @foreach ($sectionOrItem['items'] as $item)
                                @if (isset($item['route']) && Route::has($item['route']))
                                    {{-- Plugin item with direct URL --}}
                                    <a href="{{ $item['route'] }}"
                                        class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['route'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        {{ $item['icon'] ??
                                            ' <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                                                                </svg>' }}
                                        {{ $item['title'] ?? '*Untitled' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif($sectionOrItem['group'] === 'Plugins')
                    <!-- Collapsible Plugins Section -->
                    <div class="pt-4" x-data="{ pluginsOpen: localStorage.getItem('sidebar_plugins_open') !== 'false' }" x-init="$watch('pluginsOpen', value => localStorage.setItem('sidebar_plugins_open', value))">
                        <button @click="pluginsOpen = !pluginsOpen"
                            class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! hyro_menu_icon('modules_group', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>') !!}</svg>
                                <span>{{ $sectionOrItem['group'] }}</span>
                                <span
                                    class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-purple-600 rounded-full">
                                    {{ count($sectionOrItem['items']) }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200"
                                :class="{ 'rotate-180': pluginsOpen }" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="pluginsOpen" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-2" class="mt-2 space-y-1">
                            @foreach ($sectionOrItem['items'] as $item)
                                @if (isset($item['url']))
                                    {{-- Plugin item with direct URL --}}
                                    <a href="{{ $item['url'] }}"
                                        class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['url'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                        </svg>
                                        {{ $item['title'] ?? 'Untitled' }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @elseif($sectionOrItem['group'] === 'Settings')
                    {{-- Skip Settings section - it's hardcoded above --}}
                @else
                    <!-- Regular Section with items -->
                    <div class="pt-4 pb-2">
                        <p
                            class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            {{ $sectionOrItem['group'] }}</p>
                    </div>
                    @foreach ($sectionOrItem['items'] as $item)
                        @if (isset($item['url']))
                            {{-- Plugin item with direct URL --}}
                            <a href="{{ $item['url'] }}"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->is(ltrim($item['url'], '/') . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                                </svg>
                                {{ $item['title'] ?? 'Untitled' }}
                            </a>
                        @elseif(isset($item['route']) && Route::has($item['route']))
                            {{-- Module item with route --}}
                            <a href="{{ route($item['route']) }}"
                                class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs($item['route'] . '*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                {{ $item['title'] ?? 'Untitled' }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @elseif(isset($sectionOrItem['route']) && Route::has($sectionOrItem['route']))
                <!-- Single item -->
                <a href="{{ route($sectionOrItem['route']) }}"
                    class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs($sectionOrItem['route'] . '*') ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg shadow-green-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                    {{ $sectionOrItem['title'] ?? 'Untitled' }}
                </a>
            @endif
        @endforeach
    </nav>
    <!-- Settings Link -->
    <div class="px-4 pb-2">
        <a href="{{ route('hyro.admin.settings.index') }}"
            class="flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all {{ request()->routeIs('hyro.admin.settings.*') ? 'bg-gradient-to-r from-purple-500 to-purple-600 text-white shadow-lg shadow-purple-500/50' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{!! hyro_menu_icon('settings', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>') !!}
            </svg>
            Settings
        </a>
    </div>

    <!-- User Profile (Fixed at Bottom) -->
    <div class="mt-auto border-t border-gray-200 dark:border-gray-700">
        <!-- User Profile -->
        @auth
            <div class="p-4">
                <div class="flex items-center space-x-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                    <img
                        src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover" wire:key="sidebar-avatar-{{ auth()->id() }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('hyro.logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors"
                            title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="p-4">
                <a href="{{ route('hyro.login') }}"
                    class="flex items-center justify-center space-x-2 p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Login</span>
                </a>
            </div>
        @endauth
    </div>
</aside>

</div>



