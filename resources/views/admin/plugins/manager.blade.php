<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Plugin Manager</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage, install, and configure your plugins</p>
        </div>
        <div class="flex items-center space-x-3">
            <button wire:click="checkForUpdates" 
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates"
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg wire:loading.remove wire:target="checkForUpdates" class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <svg wire:loading wire:target="checkForUpdates" class="w-5 h-5 inline-block mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
            <button wire:click="$set('showUploadModal', true)" 
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all shadow-lg shadow-purple-500/50">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload Plugin
            </button>
        </div>
    </div>

    {{-- Stats Cards with Shimmer --}}
    <div class="grid grid-cols-3  sm:grid-cols-6 grid-cols-6 gap-4" wire:loading.class="pointer-events-none">
        @if(!$isLoaded)
            {{-- Shimmer placeholders --}}
            @for($i = 0; $i < 5; $i++)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-16 mb-2"></div>
                        <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded w-12"></div>
                    </div>
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
            </div>
            @endfor
        @else
            {{-- Actual stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Installed</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['installed'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Inactive</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['inactive'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Available</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['available'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    </div>


    {{-- Filters and Search --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            {{-- Search --}}
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Search plugins..."
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Filters --}}
            <div class="flex items-center space-x-3">
                <select wire:model.live="filter" 
                        class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white">
                    <option value="all">All Plugins</option>
                    <option value="installed">Installed</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="remote">Available</option>
                    <option value="updates">Updates Available</option>
                </select>

                <select wire:model.live="sortBy" 
                        class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white">
                    <option value="name">Sort by Name</option>
                    <option value="author">Sort by Author</option>
                    <option value="date">Sort by Date</option>
                    <option value="status">Sort by Status</option>
                </select>

                <button wire:click="$toggle('sortDirection')" 
                        class="p-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                    <svg class="w-5 h-5 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }} transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Bulk Actions --}}
        @if(count($selectedPlugins) > 0)
        <div class="mt-4 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-purple-900 dark:text-purple-300">
                    {{ count($selectedPlugins) }} plugin(s) selected
                </span>
                <div class="flex items-center space-x-3">
                    <select wire:model="bulkAction" 
                            class="px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-purple-300 dark:border-purple-600 rounded-lg">
                        <option value="">Select Action</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="uninstall">Uninstall</option>
                    </select>
                    <button wire:click="bulkExecute" 
                            class="px-4 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Apply
                    </button>
                    <button wire:click="$set('selectedPlugins', [])" 
                            class="px-4 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                        Clear
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>


    {{-- Plugin Grid with Shimmer --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @if(!$isLoaded)
            {{-- Shimmer placeholders for loading state --}}
            @for($i = 0; $i < 6; $i++)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-pulse">
                <div class="h-48 bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600"></div>
                <div class="p-6">
                    <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-full mb-2"></div>
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-5/6 mb-4"></div>
                    <div class="flex space-x-2">
                        <div class="flex-1 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    </div>
                </div>
            </div>
            @endfor
        @else
            @forelse($plugins as $plugin)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 group">
            {{-- Plugin Header --}}
            <div class="relative h-48 bg-gradient-to-br from-purple-500 to-purple-700 overflow-hidden">
                @if(isset($plugin['icon']) && $plugin['icon'])
                    <img src="{{ $plugin['icon'] }}" alt="{{ $plugin['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-20 h-20 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                    </div>
                @endif
                
                {{-- Status Badge --}}
                <div class="absolute top-4 right-4">
                    @if($plugin['is_active'])
                        <span class="px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-full shadow-lg">Active</span>
                    @elseif($plugin['is_installed'])
                        <span class="px-3 py-1 bg-gray-500 text-white text-xs font-semibold rounded-full shadow-lg">Inactive</span>
                    @else
                        <span class="px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full shadow-lg">Available</span>
                    @endif
                </div>

                {{-- Checkbox for bulk selection --}}
                @if($plugin['is_installed'])
                <div class="absolute top-4 left-4">
                    <input type="checkbox" 
                           wire:model.live="selectedPlugins" 
                           value="{{ $plugin['id'] }}"
                           class="w-5 h-5 text-purple-600 bg-white border-gray-300 rounded focus:ring-purple-500">
                </div>
                @endif
            </div>

            {{-- Plugin Content --}}
            <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                            {{ $plugin['name'] }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">by {{ $plugin['author'] }}</p>
                    </div>
                    <span class="text-xs font-mono text-gray-400 dark:text-gray-500">v{{ $plugin['version'] }}</span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 line-clamp-2">
                    {{ $plugin['description'] }}
                </p>

                {{-- Dependencies --}}
                @if(isset($plugin['dependencies']) && count($plugin['dependencies']) > 0)
                <div class="mb-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Dependencies:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($plugin['dependencies'] as $dep)
                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-xs text-gray-600 dark:text-gray-300 rounded">{{ $dep }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center space-x-2">
                    @if($plugin['is_installed'])
                        @if($plugin['is_active'])
                            <button wire:click="deactivate('{{ $plugin['id'] }}')" 
                                    wire:loading.attr="disabled"
                                    wire:target="deactivate('{{ $plugin['id'] }}')"
                                    class="flex-1 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-sm font-medium transition-colors disabled:opacity-50">
                                <span wire:loading.remove wire:target="deactivate('{{ $plugin['id'] }}')">Deactivate</span>
                                <span wire:loading wire:target="deactivate('{{ $plugin['id'] }}')">
                                    <svg class="w-4 h-4 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                            </button>
                        @else
                            <button wire:click="activate('{{ $plugin['id'] }}')" 
                                    wire:loading.attr="disabled"
                                    wire:target="activate('{{ $plugin['id'] }}')"
                                    class="flex-1 px-3 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 text-sm font-medium transition-all shadow-lg shadow-green-500/50 disabled:opacity-50">
                                <span wire:loading.remove wire:target="activate('{{ $plugin['id'] }}')">Activate</span>
                                <span wire:loading wire:target="activate('{{ $plugin['id'] }}')">
                                    <svg class="w-4 h-4 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                            </button>
                        @endif
                        
                        <button wire:click="showDetails('{{ $plugin['id'] }}')" 
                                class="px-3 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        
                        <button wire:click="uninstall('{{ $plugin['id'] }}')" 
                                wire:loading.attr="disabled"
                                wire:target="uninstall('{{ $plugin['id'] }}')"
                                class="px-3 py-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors disabled:opacity-50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @else
                        <button wire:click="install('{{ $plugin['id'] }}')" 
                                wire:loading.attr="disabled"
                                wire:target="install('{{ $plugin['id'] }}')"
                                class="flex-1 px-3 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 text-sm font-medium transition-all shadow-lg shadow-purple-500/50 disabled:opacity-50">
                            <span wire:loading.remove wire:target="install('{{ $plugin['id'] }}')">Install</span>
                            <span wire:loading wire:target="install('{{ $plugin['id'] }}')">
                                <svg class="w-4 h-4 inline-block animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </span>
                        </button>
                        
                        <button wire:click="showDetails('{{ $plugin['id'] }}')" 
                                class="px-3 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No plugins found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Try adjusting your search or filters</p>
                <button wire:click="$set('search', '')" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Clear Filters
                </button>
            </div>
        </div>
        @endforelse
        @endif
    </div>


    {{-- Include Modals --}}
    @include('hyro::admin.plugins.partials.modals')

    {{-- Subtle Loading Indicator (only for long operations) --}}
    <div wire:loading.delay.longer wire:target="checkForUpdates,activate,deactivate,install,uninstall,uploadPlugin,bulkExecute" class="fixed top-4 right-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center space-x-3 animate-fade-in">
            <svg class="animate-spin h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Processing...</span>
        </div>
    </div>
</div>
