{{-- Plugin Details Modal --}}
@if($showDetailsModal && $selectedPlugin)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-2 sm:px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop - removed blur --}}
        <div class="fixed inset-0 transition-opacity bg-gray-900/75" 
             wire:click="$set('showDetailsModal', false)"></div>

        {{-- Modal - increased z-index, responsive width --}}
        <div class="inline-block w-full max-w-5xl my-4 sm:my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-xl sm:rounded-2xl relative z-10">
            
            {{-- Hero Header with Gradient - Responsive height --}}
            <div class="relative h-48 sm:h-64 md:h-72 bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 overflow-hidden">
                {{-- Background Pattern --}}
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>
                
                @if($selectedPlugin['icon'])
                    <img src="{{ $selectedPlugin['icon'] }}" alt="{{ $selectedPlugin['name'] }}" 
                         class="absolute inset-0 w-full h-full object-cover opacity-20">
                @endif
                
                {{-- Gradient Overlay --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                
                {{-- Content - Responsive padding --}}
                <div class="absolute inset-0 flex flex-col justify-between p-4 sm:p-6 md:p-8">
                    {{-- Close Button --}}
                    <div class="flex justify-end">
                        <button wire:click="$set('showDetailsModal', false)" 
                                class="p-2 bg-white/10 hover:bg-white/20 rounded-xl backdrop-blur-md transition-all duration-200 group">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white group-hover:rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- Plugin Info - Responsive layout --}}
                    <div>
                        <div class="flex flex-col sm:flex-row items-start space-y-3 sm:space-y-0 sm:space-x-4 md:space-x-6 mb-3 sm:mb-4">
                            {{-- Plugin Icon/Logo - Responsive size --}}
                            @if($selectedPlugin['icon'])
                            <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl p-2 sm:p-3 flex-shrink-0 border-2 border-white/20">
                                <img src="{{ $selectedPlugin['icon'] }}" alt="{{ $selectedPlugin['name'] }}" class="w-full h-full object-contain">
                            </div>
                            @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 bg-white/10 backdrop-blur-md rounded-xl sm:rounded-2xl flex items-center justify-center flex-shrink-0 border-2 border-white/20">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                </svg>
                            </div>
                            @endif
                            
                            <div class="flex-1 min-w-0">
                                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-1 sm:mb-2 drop-shadow-lg truncate">{{ $selectedPlugin['name'] }}</h2>
                                <p class="text-sm sm:text-base md:text-lg text-purple-100 mb-2 sm:mb-4 leading-relaxed line-clamp-2">{{ $selectedPlugin['short_description'] ?? $selectedPlugin['description'] }}</p>
                                
                                {{-- Meta Info Grid - Responsive --}}
                                <div class="grid grid-cols-2 gap-2 sm:gap-3 md:gap-4 text-xs sm:text-sm">
                                    <div class="flex items-center space-x-1 sm:space-x-2 text-purple-100 truncate">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span class="truncate">{{ $selectedPlugin['author'] }}</span>
                                        @if($selectedPlugin['author_website'] ?? false)
                                        <a href="{{ $selectedPlugin['author_website'] }}" target="_blank" class="hover:text-white transition-colors flex-shrink-0">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-1 sm:space-x-2 text-purple-100">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <span>v{{ $selectedPlugin['version'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 sm:space-x-2 text-purple-100 truncate">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="truncate">{{ $selectedPlugin['installed_at'] ? \Carbon\Carbon::parse($selectedPlugin['installed_at'])->format('M d, Y') : 'Not installed' }}</span>
                                    </div>
                                    <div class="flex items-center space-x-1 sm:space-x-2 text-purple-100">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>{{ $selectedPlugin['license'] ?? 'MIT' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Status Badges - Responsive --}}
                            <div class="flex flex-row sm:flex-col items-start sm:items-end space-x-2 sm:space-x-0 sm:space-y-2 mt-3 sm:mt-0">
                                @if($selectedPlugin['is_active'])
                                    <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-green-500/90 backdrop-blur-sm text-white text-xs sm:text-sm font-bold rounded-full shadow-lg flex items-center">
                                        <span class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></span>
                                        Active
                                    </span>
                                @elseif($selectedPlugin['is_installed'])
                                    <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-gray-500/90 backdrop-blur-sm text-white text-xs sm:text-sm font-bold rounded-full shadow-lg">Inactive</span>
                                @else
                                    <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-blue-500/90 backdrop-blur-sm text-white text-xs sm:text-sm font-bold rounded-full shadow-lg">Available</span>
                                @endif
                                <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-purple-500/90 backdrop-blur-sm text-white text-xs font-semibold rounded-full shadow-lg">
                                    {{ ucfirst($selectedPlugin['source']) }}
                                </span>
                                @if($selectedPlugin['download_count'] ?? 0 > 0)
                                <span class="px-3 py-1.5 sm:px-4 sm:py-2 bg-indigo-500/90 backdrop-blur-sm text-white text-xs font-semibold rounded-full shadow-lg flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    {{ number_format($selectedPlugin['download_count']) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content - Responsive padding --}}
            <div class="p-4 sm:p-6 md:p-8">
                {{-- Quick Actions Bar - Responsive --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 sm:mb-8 p-4 sm:p-5 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-600 space-y-4 sm:space-y-0">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-6 w-full sm:w-auto">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    @if($selectedPlugin['is_active'])
                                        Running
                                    @elseif($selectedPlugin['is_installed'])
                                        Installed
                                    @else
                                        Not Installed
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        @if($selectedPlugin['activated_at'])
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Activated</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($selectedPlugin['activated_at'])->diffForHumans() }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <button wire:click="healthCheck('{{ $selectedPlugin['id'] }}')" 
                            class="w-full sm:w-auto px-4 sm:px-5 py-2.5 bg-white dark:bg-gray-700 border-2 border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 text-sm font-semibold transition-all duration-200 flex items-center justify-center space-x-2 group">
                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Health Check</span>
                    </button>
                </div>

                {{-- Enhanced Tabs - Responsive with horizontal scroll --}}
                <div x-data="{ activeTab: 'overview' }" class="mb-6 sm:mb-8">
                    <div class="border-b-2 border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-1 sm:space-x-2 overflow-x-auto scrollbar-hide -mb-px">
                            <button @click="activeTab = 'overview'" 
                                    :class="activeTab === 'overview' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Overview</span>
                            </button>
                            
                            @if($selectedPlugin['settings_schema'] && count($selectedPlugin['settings_schema']) > 0)
                            <button @click="activeTab = 'settings'" 
                                    :class="activeTab === 'settings' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Settings</span>
                            </button>
                            @endif
                            
                            @if(count($selectedPlugin['required_plugins']) > 0 || count($selectedPlugin['optional_plugins']) > 0)
                            <button @click="activeTab = 'dependencies'" 
                                    :class="activeTab === 'dependencies' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Dependencies</span>
                            </button>
                            @endif
                            
                            @if(count($selectedPlugin['permissions']) > 0)
                            <button @click="activeTab = 'permissions'" 
                                    :class="activeTab === 'permissions' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Permissions</span>
                            </button>
                            @endif
                            
                            <button @click="activeTab = 'technical'" 
                                    :class="activeTab === 'technical' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                </svg>
                                <span>Technical</span>
                            </button>
                            
                            @if(count($selectedPlugin['versions']) > 0)
                            <button @click="activeTab = 'updates'" 
                                    :class="activeTab === 'updates' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Updates</span>
                                @if($selectedPlugin['has_update'])
                                <span class="px-1.5 sm:px-2 py-0.5 bg-red-500 text-white rounded-full text-xs font-bold">!</span>
                                @endif
                            </button>
                            @endif
                            
                            <button @click="activeTab = 'activity'" 
                                    :class="activeTab === 'activity' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Activity</span>
                            </button>
                            
                            <button @click="activeTab = 'health'" 
                                    :class="activeTab === 'health' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Health</span>
                            </button>
                            
                            @if($selectedPlugin['source'] === 'marketplace' || isset($selectedPlugin['marketplace_data']))
                            <button @click="activeTab = 'marketplace'" 
                                    :class="activeTab === 'marketplace' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Marketplace</span>
                            </button>
                            @endif
                            
                            @if($selectedPlugin['readme'])
                            <button @click="activeTab = 'readme'" 
                                    :class="activeTab === 'readme' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Documentation</span>
                            </button>
                            @endif
                            
                            @if($selectedPlugin['changelog'])
                            <button @click="activeTab = 'changelog'" 
                                    :class="activeTab === 'changelog' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Changelog</span>
                            </button>
                            @endif
                            
                            @if(count($selectedPlugin['screenshots']) > 0)
                            <button @click="activeTab = 'screenshots'" 
                                    :class="activeTab === 'screenshots' ? 'border-purple-600 text-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Screenshots</span>
                            </button>
                            @endif
                            
                            @if($selectedPlugin['is_installed'])
                            <button @click="activeTab = 'danger'" 
                                    :class="activeTab === 'danger' ? 'border-red-600 text-red-600 bg-red-50 dark:bg-red-900/20' : 'border-transparent text-gray-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20'"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 border-b-2 font-semibold text-xs sm:text-sm transition-all duration-200 rounded-t-lg flex items-center space-x-1.5 sm:space-x-2 whitespace-nowrap flex-shrink-0">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <span>Danger Zone</span>
                            </button>
                            @endif
                        </nav>
                    </div>

                    {{-- Tab Content --}}
                    <div class="mt-8">
                        {{-- Overview Tab --}}
                        <div x-show="activeTab === 'overview'" class="space-y-6"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            
                            {{-- Description Card --}}
                            <div class="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                <div class="flex items-start space-x-3 mb-3">
                                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Description</h3>
                                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">{{ $selectedPlugin['description'] }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Key Features --}}
                            @if(count($selectedPlugin['key_features']) > 0)
                            <div class="p-6 bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800/50 rounded-xl border border-blue-200 dark:border-blue-800">
                                <div class="flex items-center space-x-2 mb-4">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Key Features</h3>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($selectedPlugin['key_features'] as $feature)
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Use Cases --}}
                            @if(count($selectedPlugin['use_cases']) > 0)
                            <div class="p-6 bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800/50 rounded-xl border border-purple-200 dark:border-purple-800">
                                <div class="flex items-center space-x-2 mb-4">
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Use Cases</h3>
                                </div>
                                <ul class="space-y-2">
                                    @foreach($selectedPlugin['use_cases'] as $useCase)
                                    <li class="flex items-start space-x-2">
                                        <span class="text-purple-500 font-bold">•</span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $useCase }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Info Grid --}}
                            <div class="grid grid-cols-2 gap-4">
                                {{-- Compatibility --}}
                                <div class="p-6 bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800/50 rounded-xl border border-green-200 dark:border-green-800">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Compatibility</h3>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Hyro:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $selectedPlugin['hyro_version'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">PHP:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $selectedPlugin['php_version'] }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Laravel:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $selectedPlugin['laravel_version'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Performance Impact --}}
                                <div class="p-6 bg-gradient-to-br from-orange-50 to-white dark:from-orange-900/20 dark:to-gray-800/50 rounded-xl border border-orange-200 dark:border-orange-800">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Performance</h3>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $impact = $selectedPlugin['performance_impact'];
                                            $color = match($impact) {
                                                'low' => 'green',
                                                'medium' => 'yellow',
                                                'high' => 'red',
                                                default => 'gray'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 text-{{ $color }}-700 dark:text-{{ $color }}-400 rounded-full text-xs font-bold uppercase">
                                            {{ ucfirst($impact) }} Impact
                                        </span>
                                    </div>
                                </div>

                                {{-- Dependencies --}}
                                @if(count($selectedPlugin['dependencies']) > 0)
                                <div class="p-6 bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800/50 rounded-xl border border-blue-200 dark:border-blue-800">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Dependencies</h3>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedPlugin['dependencies'] as $dep)
                                        <span class="px-3 py-1.5 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-600">{{ $dep }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif

                                {{-- Installation Path --}}
                                @if($selectedPlugin['path'])
                                <div class="p-6 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center space-x-2 mb-4">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Installation Path</h3>
                                    </div>
                                    <code class="block p-3 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-mono border border-gray-200 dark:border-gray-600 break-all">{{ $selectedPlugin['path'] }}</code>
                                </div>
                                @endif
                            </div>

                            {{-- Security Notes --}}
                            @if($selectedPlugin['security_notes'])
                            <div class="p-6 bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-gray-800/50 rounded-xl border border-red-200 dark:border-red-800">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Security Notes</h3>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $selectedPlugin['security_notes'] }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Demo URL --}}
                            @if($selectedPlugin['demo_url'])
                            <div class="text-center">
                                <a href="{{ $selectedPlugin['demo_url'] }}" target="_blank" 
                                   class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 font-semibold shadow-lg shadow-blue-500/50 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>View Live Demo</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                            @endif
                        </div>

                        {{-- Settings Tab (Phase 2) --}}
                        @if($selectedPlugin['settings_schema'] && count($selectedPlugin['settings_schema']) > 0)
                        <div x-show="activeTab === 'settings'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Settings Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plugin Settings</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Configure plugin behavior and features</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Settings Form --}}
                                <div class="p-8 space-y-6" wire:ignore.self>
                                    @foreach($selectedPlugin['settings_schema'] as $setting)
                                    <div class="p-5 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                        <label class="block">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $setting['label'] }}</span>
                                                    @if(isset($setting['description']))
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $setting['description'] }}</p>
                                                    @endif
                                                </div>
                                                @if($setting['type'] === 'toggle')
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" 
                                                           wire:model="selectedPlugin.settings.{{ $setting['key'] }}" 
                                                           class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                                                </label>
                                                @endif
                                            </div>
                                            
                                            @if($setting['type'] === 'text')
                                            <input type="text" 
                                                   wire:model="selectedPlugin.settings.{{ $setting['key'] }}"
                                                   placeholder="{{ $setting['placeholder'] ?? '' }}"
                                                   class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white">
                                            @elseif($setting['type'] === 'password')
                                            <input type="password" 
                                                   wire:model="selectedPlugin.settings.{{ $setting['key'] }}"
                                                   placeholder="{{ $setting['placeholder'] ?? '••••••••' }}"
                                                   class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white font-mono">
                                            @elseif($setting['type'] === 'select')
                                            <select wire:model="selectedPlugin.settings.{{ $setting['key'] }}"
                                                    class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white">
                                                @foreach($setting['options'] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @elseif($setting['type'] === 'textarea')
                                            <textarea wire:model="selectedPlugin.settings.{{ $setting['key'] }}"
                                                      rows="4"
                                                      placeholder="{{ $setting['placeholder'] ?? '' }}"
                                                      class="w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 dark:text-white"></textarea>
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                    
                                    {{-- Action Buttons --}}
                                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button wire:click="resetPluginSettings('{{ $selectedPlugin['id'] }}')"
                                                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors">
                                            Reset to Defaults
                                        </button>
                                        <button wire:click="savePluginSettings('{{ $selectedPlugin['id'] }}', $wire.selectedPlugin.settings)"
                                                class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 font-medium shadow-lg shadow-purple-500/50 transition-all">
                                            Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Dependencies Tab (Phase 3) --}}
                        @if(count($selectedPlugin['required_plugins']) > 0 || count($selectedPlugin['optional_plugins']) > 0)
                        <div x-show="activeTab === 'dependencies'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Dependencies Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plugin Dependencies</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Required and optional plugins</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Dependencies Content --}}
                                <div class="p-8 space-y-6">
                                    {{-- Required Plugins --}}
                                    @if(count($selectedPlugin['required_plugins']) > 0)
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            Required Plugins
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($selectedPlugin['required_plugins'] as $plugin)
                                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                                <div class="flex items-center space-x-3">
                                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $plugin }}</span>
                                                </div>
                                                <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                                    Installed
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Optional Plugins --}}
                                    @if(count($selectedPlugin['optional_plugins']) > 0)
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Optional Plugins
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($selectedPlugin['optional_plugins'] as $plugin)
                                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                                <div class="flex items-center space-x-3">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $plugin }}</span>
                                                </div>
                                                <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 rounded-full text-xs font-bold">
                                                    Optional
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Conflicts --}}
                                    @if(count($selectedPlugin['conflicts_with']) > 0)
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Conflicts With
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($selectedPlugin['conflicts_with'] as $plugin)
                                            <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                                <div class="flex items-center space-x-3">
                                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ $plugin }}</span>
                                                </div>
                                                <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-bold">
                                                    Conflict
                                                </span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Permissions Tab (Phase 3) --}}
                        @if(count($selectedPlugin['permissions']) > 0)
                        <div x-show="activeTab === 'permissions'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Permissions Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plugin Permissions</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Manage access control and capabilities</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Permissions Content --}}
                                <div class="p-8">
                                    <div class="space-y-4">
                                        @foreach($selectedPlugin['permissions'] as $permission)
                                        <div class="p-5 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                        </svg>
                                                        <h4 class="font-bold text-gray-900 dark:text-white">{{ $permission->permission_name }}</h4>
                                                    </div>
                                                    @if($permission->description)
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $permission->description }}</p>
                                                    @endif
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer ml-4">
                                                    <input type="checkbox" 
                                                           checked="{{ $permission->is_active }}"
                                                           class="sr-only peer">
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 dark:peer-focus:ring-purple-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-purple-600"></div>
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Technical Info Tab (Phase 4) --}}
                        <div x-show="activeTab === 'technical'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Technical Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-700/50 dark:to-slate-900/50 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Technical Information</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Developer and system details</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Technical Content --}}
                                <div class="p-8 space-y-6">
                                    {{-- Plugin Path --}}
                                    @if($selectedPlugin['path'])
                                    <div class="p-5 bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800/50 rounded-xl border border-blue-200 dark:border-blue-800">
                                        <div class="flex items-center space-x-2 mb-3">
                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Plugin Directory</h4>
                                        </div>
                                        <code class="block p-3 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-mono border border-gray-200 dark:border-gray-700 break-all">{{ $selectedPlugin['path'] }}</code>
                                    </div>
                                    @endif

                                    {{-- Namespace --}}
                                    @if($selectedPlugin['namespace'])
                                    <div class="p-5 bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800/50 rounded-xl border border-purple-200 dark:border-purple-800">
                                        <div class="flex items-center space-x-2 mb-3">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                            </svg>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Namespace</h4>
                                        </div>
                                        <code class="block p-3 bg-white dark:bg-gray-900 text-purple-700 dark:text-purple-400 rounded-lg text-xs font-mono border border-gray-200 dark:border-gray-700">{{ $selectedPlugin['namespace'] }}</code>
                                    </div>
                                    @endif

                                    {{-- Service Providers --}}
                                    @if(count($selectedPlugin['service_providers']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800/50 rounded-xl border border-green-200 dark:border-green-800">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                                                </svg>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Service Providers</h4>
                                            </div>
                                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['service_providers']) }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($selectedPlugin['service_providers'] as $provider)
                                            <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <code class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $provider }}</code>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Routes --}}
                                    @if(count($selectedPlugin['routes']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-orange-50 to-white dark:from-orange-900/20 dark:to-gray-800/50 rounded-xl border border-orange-200 dark:border-orange-800">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Registered Routes</h4>
                                            </div>
                                            <span class="px-2 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['routes']) }}
                                            </span>
                                        </div>
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @foreach($selectedPlugin['routes'] as $route)
                                            <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span class="px-2 py-0.5 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded text-xs font-bold">
                                                        {{ $route['method'] }}
                                                    </span>
                                                    <code class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $route['uri'] }}</code>
                                                </div>
                                                @if($route['name'])
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Name: {{ $route['name'] }}</div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Migrations --}}
                                    @if(count($selectedPlugin['migrations']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/20 dark:to-gray-800/50 rounded-xl border border-indigo-200 dark:border-indigo-800">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                                </svg>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Database Migrations</h4>
                                            </div>
                                            <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['migrations']) }}
                                            </span>
                                        </div>
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @foreach($selectedPlugin['migrations'] as $migration)
                                            <div class="flex items-center space-x-2 p-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <code class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $migration['file'] }}</code>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Published Assets --}}
                                    @if(count($selectedPlugin['published_assets']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-pink-50 to-white dark:from-pink-900/20 dark:to-gray-800/50 rounded-xl border border-pink-200 dark:border-pink-800">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Published Assets</h4>
                                            </div>
                                            <span class="px-2 py-1 bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['published_assets']) }}
                                            </span>
                                        </div>
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @foreach($selectedPlugin['published_assets'] as $asset)
                                            <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <code class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ $asset['name'] }}</code>
                                                </div>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $asset['size'] }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Sidebar Entries --}}
                                    @if(count($selectedPlugin['sidebar_entries']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-cyan-50 to-white dark:from-cyan-900/20 dark:to-gray-800/50 rounded-xl border border-cyan-200 dark:border-cyan-800">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                                </svg>
                                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Sidebar Entries</h4>
                                            </div>
                                            <span class="px-2 py-1 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['sidebar_entries']) }}
                                            </span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($selectedPlugin['sidebar_entries'] as $entry)
                                            <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    @if($entry['icon'])
                                                    <span class="text-cyan-500">{!! $entry['icon'] !!}</span>
                                                    @endif
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $entry['label'] }}</span>
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Section: {{ $entry['section'] }}</div>
                                                @if($entry['route'])
                                                <code class="text-xs font-mono text-gray-600 dark:text-gray-400">{{ $entry['route'] }}</code>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- PSR-4 Autoload --}}
                                    @if(count($selectedPlugin['psr4_namespaces']) > 0)
                                    <div class="p-5 bg-gradient-to-br from-yellow-50 to-white dark:from-yellow-900/20 dark:to-gray-800/50 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                        <div class="flex items-center space-x-2 mb-3">
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">PSR-4 Autoload</h4>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($selectedPlugin['psr4_namespaces'] as $namespace => $path)
                                            <div class="p-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Namespace:</div>
                                                <code class="block text-xs font-mono text-purple-700 dark:text-purple-400 mb-2">{{ $namespace }}</code>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Path:</div>
                                                <code class="block text-xs font-mono text-gray-700 dark:text-gray-300">{{ $path }}</code>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Updates & Version History Tab (Phase 5) --}}
                        @if(count($selectedPlugin['versions']) > 0)
                        <div x-show="activeTab === 'updates'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Updates Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Updates & Version History</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Manage plugin versions and updates</p>
                                            </div>
                                        </div>
                                        
                                        {{-- Auto-Update Toggle --}}
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Auto-Update</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       wire:click="toggleAutoUpdate('{{ $selectedPlugin['id'] }}')"
                                                       {{ $selectedPlugin['auto_update_enabled'] ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Current Version Info --}}
                                <div class="p-6 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Current Version</div>
                                            <div class="text-3xl font-bold text-gray-900 dark:text-white">v{{ $selectedPlugin['current_version'] }}</div>
                                        </div>
                                        
                                        @if($selectedPlugin['has_update'])
                                        <div class="text-right">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Latest Version</div>
                                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">v{{ $selectedPlugin['latest_version'] }}</div>
                                            <button wire:click="updatePlugin('{{ $selectedPlugin['id'] }}', '{{ $selectedPlugin['latest_version'] }}')"
                                                    class="mt-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 text-sm font-semibold shadow-lg shadow-green-500/50 transition-all">
                                                Update Now
                                            </button>
                                        </div>
                                        @else
                                        <div class="flex items-center space-x-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="font-semibold">Up to date</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Version History --}}
                                <div class="p-8">
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Version History
                                    </h4>
                                    
                                    <div class="space-y-4 max-h-[500px] overflow-y-auto">
                                        @foreach($selectedPlugin['versions'] as $version)
                                        <div class="p-5 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border-2 {{ $version->version === $selectedPlugin['current_version'] ? 'border-purple-500 dark:border-purple-400' : 'border-gray-200 dark:border-gray-700' }}">
                                            {{-- Version Header --}}
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex items-center space-x-3">
                                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">v{{ $version->version }}</div>
                                                    
                                                    @if($version->version === $selectedPlugin['current_version'])
                                                    <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 rounded-full text-xs font-bold">
                                                        CURRENT
                                                    </span>
                                                    @endif
                                                    
                                                    @if($version->breaking_changes)
                                                    <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-bold flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                        </svg>
                                                        BREAKING
                                                    </span>
                                                    @endif
                                                    
                                                    @if($version->security_patch)
                                                    <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 rounded-full text-xs font-bold flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                        </svg>
                                                        SECURITY
                                                    </span>
                                                    @endif
                                                </div>
                                                
                                                <div class="text-right">
                                                    @if($version->release_date)
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ \Carbon\Carbon::parse($version->release_date)->format('M d, Y') }}
                                                    </div>
                                                    @endif
                                                    
                                                    @if($version->version !== $selectedPlugin['current_version'])
                                                    <button wire:click="rollbackPlugin('{{ $selectedPlugin['id'] }}', '{{ $version->version }}')"
                                                            class="mt-2 px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 text-xs font-medium transition-colors">
                                                        Rollback
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            {{-- Changelog --}}
                                            @if($version->changelog)
                                            <div class="mt-4 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="prose prose-sm dark:prose-invert max-w-none
                                                            prose-headings:text-gray-900 dark:prose-headings:text-white
                                                            prose-p:text-gray-700 dark:prose-p:text-gray-300
                                                            prose-ul:text-gray-700 dark:prose-ul:text-gray-300
                                                            prose-strong:text-gray-900 dark:prose-strong:text-white">
                                                    {!! \Illuminate\Support\Str::markdown($version->changelog) !!}
                                                </div>
                                            </div>
                                            @endif
                                            
                                            {{-- Download URL --}}
                                            @if($version->download_url)
                                            <div class="mt-3 flex items-center space-x-2 text-sm">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                <a href="{{ $version->download_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                    Download this version
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                {{-- Backup Settings --}}
                                <div class="px-8 pb-8">
                                    <div class="p-5 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                        <label class="flex items-start space-x-3 cursor-pointer">
                                            <input type="checkbox" 
                                                   wire:model="backupBeforeAction"
                                                   class="w-5 h-5 text-yellow-600 bg-white border-gray-300 rounded focus:ring-yellow-500 mt-0.5">
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-white">Create backup before updates</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Automatically backup plugin files before applying updates or rollbacks</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Activity Logs & Monitoring Tab (Phase 6) --}}
                        <div x-show="activeTab === 'activity'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Activity Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Activity Logs & Monitoring</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Track plugin usage and history</p>
                                            </div>
                                        </div>
                                        
                                        {{-- Export Button --}}
                                        <button wire:click="exportPluginLogs('{{ $selectedPlugin['id'] }}', 'csv')"
                                                class="px-4 py-2 bg-white dark:bg-gray-700 border-2 border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 text-sm font-semibold transition-all duration-200 flex items-center space-x-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>Export Logs</span>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Last Activity Info --}}
                                @if(isset($selectedPlugin['last_activated_by']) || isset($selectedPlugin['last_deactivated_by']))
                                <div class="p-6 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-2 gap-6">
                                        @if(isset($selectedPlugin['last_activated_by']))
                                        <div class="flex items-start space-x-3">
                                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Last Activated By</div>
                                                <div class="font-bold text-gray-900 dark:text-white">{{ $selectedPlugin['last_activated_by']['name'] ?? 'Unknown' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedPlugin['last_activated_by']['time'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if(isset($selectedPlugin['last_deactivated_by']))
                                        <div class="flex items-start space-x-3">
                                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Last Deactivated By</div>
                                                <div class="font-bold text-gray-900 dark:text-white">{{ $selectedPlugin['last_deactivated_by']['name'] ?? 'Unknown' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedPlugin['last_deactivated_by']['time'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Activity Content --}}
                                <div class="p-8 space-y-6">
                                    {{-- Usage Statistics --}}
                                    @if(isset($selectedPlugin['usage_stats']))
                                    <div class="p-6 bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800/50 rounded-xl border border-purple-200 dark:border-purple-800">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            Usage Statistics
                                        </h4>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $selectedPlugin['usage_stats']['total_activations'] ?? 0 }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Activations</div>
                                            </div>
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedPlugin['usage_stats']['config_changes'] ?? 0 }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Config Changes</div>
                                            </div>
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $selectedPlugin['usage_stats']['error_count'] ?? 0 }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Errors Logged</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Activity Logs --}}
                                    @if(isset($selectedPlugin['activity_logs']) && count($selectedPlugin['activity_logs']) > 0)
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Recent Activity
                                            <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                                {{ count($selectedPlugin['activity_logs']) }}
                                            </span>
                                        </h4>
                                        
                                        <div class="space-y-3 max-h-[600px] overflow-y-auto">
                                            @foreach($selectedPlugin['activity_logs'] as $log)
                                            <div class="p-5 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-700 transition-colors">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-start space-x-3 flex-1">
                                                        {{-- Action Icon --}}
                                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                                                    {{ $log->action === 'activate' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                                                    {{ $log->action === 'deactivate' ? 'bg-red-100 dark:bg-red-900/30' : '' }}
                                                                    {{ $log->action === 'install' ? 'bg-blue-100 dark:bg-blue-900/30' : '' }}
                                                                    {{ $log->action === 'uninstall' ? 'bg-orange-100 dark:bg-orange-900/30' : '' }}
                                                                    {{ $log->action === 'settings_updated' ? 'bg-purple-100 dark:bg-purple-900/30' : '' }}
                                                                    {{ !in_array($log->action, ['activate', 'deactivate', 'install', 'uninstall', 'settings_updated']) ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                                                            @if($log->action === 'activate')
                                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            @elseif($log->action === 'deactivate')
                                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            @elseif($log->action === 'install')
                                                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                            </svg>
                                                            @elseif($log->action === 'uninstall')
                                                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            @elseif($log->action === 'settings_updated')
                                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                            </svg>
                                                            @else
                                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            @endif
                                                        </div>
                                                        
                                                        {{-- Log Details --}}
                                                        <div class="flex-1">
                                                            <div class="flex items-center space-x-2 mb-1">
                                                                <span class="font-bold text-gray-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                                                <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded text-xs font-medium">
                                                                    {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                                                </span>
                                                            </div>
                                                            
                                                            @if($log->user_id)
                                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                                By: <span class="font-medium">{{ $log->user->name ?? 'User #' . $log->user_id }}</span>
                                                            </div>
                                                            @endif
                                                            
                                                            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                                @if($log->ip_address)
                                                                <span class="flex items-center">
                                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                                                    </svg>
                                                                    {{ $log->ip_address }}
                                                                </span>
                                                                @endif
                                                                <span>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i:s') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        
                                        {{-- Pagination Info --}}
                                        @if(isset($selectedPlugin['activity_pagination']))
                                        <div class="mt-4 flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                Showing {{ $selectedPlugin['activity_pagination']['from'] ?? 0 }} to {{ $selectedPlugin['activity_pagination']['to'] ?? 0 }} 
                                                of {{ $selectedPlugin['activity_pagination']['total'] ?? 0 }} entries
                                            </div>
                                            @if(($selectedPlugin['activity_pagination']['total'] ?? 0) > ($selectedPlugin['activity_pagination']['per_page'] ?? 20))
                                            <div class="flex items-center space-x-2">
                                                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    Previous
                                                </button>
                                                <button class="px-3 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    Next
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Activity Logs</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">This plugin has no recorded activity yet</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Health & Diagnostics Tab (Phase 7) --}}
                        <div x-show="activeTab === 'health'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Health Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Health & Diagnostics</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">System health checks and performance metrics</p>
                                            </div>
                                        </div>
                                        
                                        {{-- Run Diagnostics Button --}}
                                        <button wire:click="runPluginDiagnostics('{{ $selectedPlugin['id'] }}')"
                                                class="px-4 py-2 bg-white dark:bg-gray-700 border-2 border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-sm font-semibold transition-all duration-200 flex items-center space-x-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            <span>Run Diagnostics</span>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Overall Health Score --}}
                                @if(isset($selectedPlugin['health_score']))
                                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Overall Health Score</div>
                                            <div class="flex items-center space-x-3">
                                                <div class="text-5xl font-bold 
                                                            {{ $selectedPlugin['health_score'] >= 80 ? 'text-green-600 dark:text-green-400' : '' }}
                                                            {{ $selectedPlugin['health_score'] >= 50 && $selectedPlugin['health_score'] < 80 ? 'text-yellow-600 dark:text-yellow-400' : '' }}
                                                            {{ $selectedPlugin['health_score'] < 50 ? 'text-red-600 dark:text-red-400' : '' }}">
                                                    {{ $selectedPlugin['health_score'] }}
                                                </div>
                                                <div class="text-2xl text-gray-400">/100</div>
                                            </div>
                                        </div>
                                        
                                        {{-- Status Indicator --}}
                                        <div class="text-center">
                                            @if($selectedPlugin['health_score'] >= 80)
                                            <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-2">
                                                <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-green-600 dark:text-green-400">Excellent</span>
                                            @elseif($selectedPlugin['health_score'] >= 50)
                                            <div class="w-20 h-20 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center mb-2">
                                                <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-yellow-600 dark:text-yellow-400">Needs Attention</span>
                                            @else
                                            <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-2">
                                                <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-bold text-red-600 dark:text-red-400">Critical</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Health Checks Content --}}
                                <div class="p-8 space-y-6">
                                    {{-- System Checks --}}
                                    @if(isset($selectedPlugin['health_checks']))
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            System Checks
                                        </h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($selectedPlugin['health_checks'] as $check)
                                            <div class="p-5 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border-2 
                                                        {{ $check['status'] === 'pass' ? 'border-green-200 dark:border-green-800' : '' }}
                                                        {{ $check['status'] === 'warning' ? 'border-yellow-200 dark:border-yellow-800' : '' }}
                                                        {{ $check['status'] === 'fail' ? 'border-red-200 dark:border-red-800' : '' }}">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-start space-x-3 flex-1">
                                                        {{-- Status Icon --}}
                                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                                                                    {{ $check['status'] === 'pass' ? 'bg-green-100 dark:bg-green-900/30' : '' }}
                                                                    {{ $check['status'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/30' : '' }}
                                                                    {{ $check['status'] === 'fail' ? 'bg-red-100 dark:bg-red-900/30' : '' }}">
                                                            @if($check['status'] === 'pass')
                                                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            @elseif($check['status'] === 'warning')
                                                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                            </svg>
                                                            @else
                                                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            @endif
                                                        </div>
                                                        
                                                        {{-- Check Details --}}
                                                        <div class="flex-1">
                                                            <div class="font-bold text-gray-900 dark:text-white mb-1">{{ $check['name'] }}</div>
                                                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $check['message'] }}</div>
                                                            @if(isset($check['details']))
                                                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $check['details'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    {{-- Status Badge --}}
                                                    <span class="px-3 py-1 rounded-full text-xs font-bold
                                                                {{ $check['status'] === 'pass' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : '' }}
                                                                {{ $check['status'] === 'warning' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : '' }}
                                                                {{ $check['status'] === 'fail' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}">
                                                        {{ strtoupper($check['status']) }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Performance Metrics --}}
                                    @if(isset($selectedPlugin['performance_metrics']))
                                    <div class="p-6 bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800/50 rounded-xl border border-purple-200 dark:border-purple-800">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Performance Metrics
                                        </h4>
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $selectedPlugin['performance_metrics']['memory_usage'] ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Memory Usage</div>
                                            </div>
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $selectedPlugin['performance_metrics']['load_time'] ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Load Time</div>
                                            </div>
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 text-center">
                                                <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $selectedPlugin['performance_metrics']['cache_hit_rate'] ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Cache Hit Rate</div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Recommendations --}}
                                    @if(isset($selectedPlugin['health_recommendations']) && count($selectedPlugin['health_recommendations']) > 0)
                                    <div class="p-6 bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800/50 rounded-xl border border-blue-200 dark:border-blue-800">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                            Recommendations
                                        </h4>
                                        <ul class="space-y-3">
                                            @foreach($selectedPlugin['health_recommendations'] as $recommendation)
                                            <li class="flex items-start space-x-3 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $recommendation }}</span>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Marketplace Integration Tab (Phase 8) --}}
                        @if($selectedPlugin['source'] === 'marketplace' || isset($selectedPlugin['marketplace_data']))
                        <div x-show="activeTab === 'marketplace'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Marketplace Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Marketplace & Licensing</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Subscription, support, and purchase information</p>
                                            </div>
                                        </div>
                                        
                                        {{-- View in Marketplace Button --}}
                                        @if(isset($selectedPlugin['marketplace_data']['marketplace_url']))
                                        <a href="{{ $selectedPlugin['marketplace_data']['marketplace_url'] }}" target="_blank"
                                           class="px-4 py-2 bg-white dark:bg-gray-700 border-2 border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-sm font-semibold transition-all duration-200 flex items-center space-x-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            <span>View in Marketplace</span>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Pricing & Subscription Info --}}
                                @if(isset($selectedPlugin['marketplace_data']))
                                <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        {{-- Price --}}
                                        <div class="text-center">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Price</div>
                                            <div class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">
                                                @if(isset($selectedPlugin['marketplace_data']['price']))
                                                    {{ $selectedPlugin['marketplace_data']['price'] === 0 ? 'Free' : '$' . number_format($selectedPlugin['marketplace_data']['price'], 2) }}
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                            @if(isset($selectedPlugin['marketplace_data']['subscription_type']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ ucfirst($selectedPlugin['marketplace_data']['subscription_type']) }}
                                            </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Support Expiration --}}
                                        @if(isset($selectedPlugin['marketplace_data']['support_expires_at']))
                                        <div class="text-center">
                                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Support Expires</div>
                                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                                {{ \Carbon\Carbon::parse($selectedPlugin['marketplace_data']['support_expires_at'])->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs mt-1">
                                                @php
                                                    $daysLeft = \Carbon\Carbon::parse($selectedPlugin['marketplace_data']['support_expires_at'])->diffInDays(now());
                                                    $expired = \Carbon\Carbon::parse($selectedPlugin['marketplace_data']['support_expires_at'])->isPast();
                                                @endphp
                                                @if($expired)
                                                <span class="px-2 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-bold">
                                                    Expired
                                                </span>
                                                @elseif($daysLeft <= 30)
                                                <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-bold">
                                                    {{ $daysLeft }} days left
                                                </span>
                                                @else
                                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-xs font-bold">
                                                    Active
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                        
                                        {{-- Renewal Action --}}
                                        <div class="text-center flex flex-col justify-center">
                                            @if(isset($selectedPlugin['marketplace_data']['can_renew']) && $selectedPlugin['marketplace_data']['can_renew'])
                                            <button wire:click="renewPluginSubscription('{{ $selectedPlugin['id'] }}')"
                                                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 font-semibold shadow-lg shadow-indigo-500/50 transition-all">
                                                Renew Subscription
                                            </button>
                                            @else
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                No renewal needed
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Marketplace Content --}}
                                <div class="p-8 space-y-6">
                                    {{-- License Key Management --}}
                                    @if(isset($selectedPlugin['marketplace_data']['license_key']))
                                    <div class="p-6 bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800/50 rounded-xl border border-green-200 dark:border-green-800">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            License Key
                                        </h4>
                                        <div class="flex items-center space-x-3">
                                            <code class="flex-1 p-4 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-lg text-sm font-mono border border-gray-200 dark:border-gray-700">
                                                {{ $selectedPlugin['marketplace_data']['license_key'] }}
                                            </code>
                                            <button onclick="navigator.clipboard.writeText('{{ $selectedPlugin['marketplace_data']['license_key'] }}')"
                                                    class="px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                        @if(isset($selectedPlugin['marketplace_data']['license_status']))
                                        <div class="mt-3 flex items-center space-x-2">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                                        {{ $selectedPlugin['marketplace_data']['license_status'] === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : '' }}
                                                        {{ $selectedPlugin['marketplace_data']['license_status'] === 'expired' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : '' }}
                                                        {{ $selectedPlugin['marketplace_data']['license_status'] === 'suspended' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : '' }}">
                                                {{ strtoupper($selectedPlugin['marketplace_data']['license_status']) }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    
                                    {{-- Purchase History --}}
                                    @if(isset($selectedPlugin['marketplace_data']['purchase_history']) && count($selectedPlugin['marketplace_data']['purchase_history']) > 0)
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Purchase History
                                        </h4>
                                        <div class="space-y-3">
                                            @foreach($selectedPlugin['marketplace_data']['purchase_history'] as $purchase)
                                            <div class="p-5 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-white">{{ $purchase['type'] ?? 'Purchase' }}</div>
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $purchase['date'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                                            ${{ number_format($purchase['amount'] ?? 0, 2) }}
                                                        </div>
                                                        @if(isset($purchase['invoice_url']))
                                                        <a href="{{ $purchase['invoice_url'] }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                            View Invoice
                                                        </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Reviews & Ratings --}}
                                    @if(isset($selectedPlugin['marketplace_data']['reviews']) && count($selectedPlugin['marketplace_data']['reviews']) > 0)
                                    <div class="p-6 bg-gradient-to-br from-yellow-50 to-white dark:from-yellow-900/20 dark:to-gray-800/50 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            Reviews & Ratings
                                            <span class="ml-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-full text-xs font-bold">
                                                {{ $selectedPlugin['marketplace_data']['average_rating'] ?? 'N/A' }} ★
                                            </span>
                                        </h4>
                                        <div class="space-y-4 max-h-96 overflow-y-auto">
                                            @foreach($selectedPlugin['marketplace_data']['reviews'] as $review)
                                            <div class="p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="flex items-start justify-between mb-2">
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-white">{{ $review['author'] ?? 'Anonymous' }}</div>
                                                        <div class="flex items-center space-x-1 mt-1">
                                                            @for($i = 1; $i <= 5; $i++)
                                                            <svg class="w-4 h-4 {{ $i <= ($review['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                            </svg>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $review['date'] ?? 'N/A' }}</div>
                                                </div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $review['comment'] ?? '' }}</p>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    
                                    {{-- Quick Links --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        {{-- Documentation --}}
                                        @if(isset($selectedPlugin['marketplace_data']['documentation_url']))
                                        <a href="{{ $selectedPlugin['marketplace_data']['documentation_url'] }}" target="_blank"
                                           class="p-5 bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800/50 rounded-xl border-2 border-blue-200 dark:border-blue-800 hover:border-blue-400 dark:hover:border-blue-600 transition-colors group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-white">Documentation</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">View full docs</div>
                                                </div>
                                            </div>
                                        </a>
                                        @endif
                                        
                                        {{-- Support Ticket --}}
                                        @if(isset($selectedPlugin['marketplace_data']['support_url']))
                                        <a href="{{ $selectedPlugin['marketplace_data']['support_url'] }}" target="_blank"
                                           class="p-5 bg-gradient-to-br from-green-50 to-white dark:from-green-900/20 dark:to-gray-800/50 rounded-xl border-2 border-green-200 dark:border-green-800 hover:border-green-400 dark:hover:border-green-600 transition-colors group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-white">Get Support</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">Open ticket</div>
                                                </div>
                                            </div>
                                        </a>
                                        @endif
                                        
                                        {{-- FAQ --}}
                                        @if(isset($selectedPlugin['marketplace_data']['faq_url']))
                                        <a href="{{ $selectedPlugin['marketplace_data']['faq_url'] }}" target="_blank"
                                           class="p-5 bg-gradient-to-br from-purple-50 to-white dark:from-purple-900/20 dark:to-gray-800/50 rounded-xl border-2 border-purple-200 dark:border-purple-800 hover:border-purple-400 dark:hover:border-purple-600 transition-colors group">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-gray-900 dark:text-white">FAQ</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">Common questions</div>
                                                </div>
                                            </div>
                                        </a>
                                        @endif
                                    </div>
                                    
                                    {{-- Similar Plugins --}}
                                    @if(isset($selectedPlugin['marketplace_data']['similar_plugins']) && count($selectedPlugin['marketplace_data']['similar_plugins']) > 0)
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                            Similar Plugins
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($selectedPlugin['marketplace_data']['similar_plugins'] as $similar)
                                            <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors cursor-pointer">
                                                <div class="flex items-start space-x-3">
                                                    @if(isset($similar['icon']))
                                                    <img src="{{ $similar['icon'] }}" alt="{{ $similar['name'] }}" class="w-12 h-12 rounded-lg">
                                                    @else
                                                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                                                        </svg>
                                                    </div>
                                                    @endif
                                                    <div class="flex-1">
                                                        <div class="font-bold text-gray-900 dark:text-white">{{ $similar['name'] ?? 'Unknown' }}</div>
                                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $similar['description'] ?? '' }}</div>
                                                        @if(isset($similar['price']))
                                                        <div class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mt-2">
                                                            {{ $similar['price'] === 0 ? 'Free' : '$' . number_format($similar['price'], 2) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- README/Documentation Tab - Enhanced --}}
                        @if($selectedPlugin['readme'])
                        <div x-show="activeTab === 'readme'" 
                             x-data="{ 
                                 searchQuery: '', 
                                 showToc: true,
                                 showMarkdown: false,
                                 copyCode(button) {
                                     const code = button.parentElement.nextElementSibling.innerText;
                                     navigator.clipboard.writeText(code);
                                     button.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg><span>Copied!</span>';
                                     setTimeout(() => {
                                         button.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg><span>Copy</span>';
                                     }, 2000);
                                 }
                             }"
                             x-init="
                                 $nextTick(() => {
                                     const readmeContent = document.querySelector('.readme-content');
                                     if (!readmeContent) return;
                                     
                                     // Add copy buttons to code blocks
                                     readmeContent.querySelectorAll('pre').forEach(pre => {
                                         if (!pre.querySelector('.copy-code-btn')) {
                                             const wrapper = document.createElement('div');
                                             wrapper.className = 'relative group my-6';
                                             pre.parentNode.insertBefore(wrapper, pre);
                                             wrapper.appendChild(pre);
                                             const btn = document.createElement('button');
                                             btn.className = 'copy-code-btn absolute top-2 right-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center space-x-1.5 z-10';
                                             btn.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg><span>Copy</span>';
                                             btn.onclick = function() { copyCode(this); };
                                             wrapper.appendChild(btn);
                                         }
                                     });
                                     
                                     // Generate TOC
                                     const headings = readmeContent.querySelectorAll('h1, h2, h3');
                                     const tocList = document.getElementById('doc-toc-list');
                                     if (tocList && headings.length > 0) {
                                         tocList.innerHTML = '';
                                         headings.forEach((heading, index) => {
                                             const id = 'heading-' + index;
                                             heading.id = id;
                                             const level = parseInt(heading.tagName.substring(1));
                                             const li = document.createElement('li');
                                             const a = document.createElement('a');
                                             a.href = '#' + id;
                                             a.className = 'block py-1.5 px-3 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors ' + 
                                                          (level === 1 ? 'font-bold text-gray-900 dark:text-white' : 
                                                           level === 2 ? 'pl-6 text-gray-700 dark:text-gray-300' : 
                                                           'pl-9 text-gray-600 dark:text-gray-400');
                                             a.textContent = heading.textContent;
                                             a.onclick = (e) => {
                                                 e.preventDefault();
                                                 heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                                             };
                                             li.appendChild(a);
                                             tocList.appendChild(li);
                                         });
                                     }
                                 });
                             "
                             x-show="activeTab === 'readme'" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Documentation Header - Enhanced --}}
                                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-3 sm:space-y-0">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plugin Documentation</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Complete user manual and guide</p>
                                            </div>
                                        </div>
                                        
                                        {{-- Action Buttons --}}
                                        <div class="flex items-center space-x-2 w-full sm:w-auto">
                                            {{-- Search --}}
                                            <div class="relative flex-1 sm:flex-initial">
                                                <input type="text" 
                                                       x-model="searchQuery"
                                                       placeholder="Search docs..."
                                                       class="w-full sm:w-48 px-3 py-2 pl-9 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                                                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                            </div>
                                            
                                            {{-- Toggle TOC --}}
                                            <button @click="showToc = !showToc"
                                                    class="p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                                    title="Toggle Table of Contents">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                            </button>
                                            
                                            {{-- Toggle Markdown View --}}
                                            <button @click="showMarkdown = !showMarkdown"
                                                    :class="showMarkdown ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                                    class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                                    title="Toggle Markdown Source">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                </svg>
                                            </button>
                                            
                                            {{-- Copy All --}}
                                            <button onclick="navigator.clipboard.writeText(document.querySelector('.readme-content').innerText); this.innerHTML = '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg>'; setTimeout(() => this.innerHTML = '<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg>', 2000)"
                                                    class="p-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                                    title="Copy All Content">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Documentation Body with TOC --}}
                                <div class="flex flex-col lg:flex-row">
                                    {{-- Table of Contents Sidebar --}}
                                    <div x-show="showToc && !showMarkdown" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 -translate-x-4"
                                         x-transition:enter-end="opacity-100 translate-x-0"
                                         class=" sm:w-64 md:w-64 lg:w-64 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                                        <div class="p-4 sm:p-6 sticky top-0 max-h-[600px] overflow-y-auto">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                                Table of Contents
                                            </h4>
                                            <ul id="doc-toc-list" class="space-y-1">
                                                {{-- Generated dynamically via Alpine.js --}}
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    {{-- Markdown Source View --}}
                                    <div x-show="showMarkdown" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         class="flex-1 p-4 sm:p-6 md:p-8 max-h-[600px] overflow-y-auto">
                                        <div class="relative">
                                            <button onclick="navigator.clipboard.writeText(this.nextElementSibling.innerText); this.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'/></svg><span>Copied!</span>'; setTimeout(() => this.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg><span>Copy Markdown</span>', 2000)"
                                                    class="absolute top-2 right-2 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded-lg transition-colors flex items-center space-x-1.5 z-10">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <span>Copy Markdown</span>
                                            </button>
                                            <pre class="bg-gray-900 dark:bg-gray-950 text-gray-100 p-4 rounded-xl overflow-x-auto text-sm font-mono border border-gray-700"><code>{{ $selectedPlugin['readme'] }}</code></pre>
                                        </div>
                                    </div>
                                    
                                    {{-- Documentation Content with Enhanced Markdown Styling --}}
                                    <div x-show="!showMarkdown" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         class="flex-1 p-4 sm:p-6 md:p-8 max-h-[600px] overflow-y-auto readme-content">
                                        <div class="prose prose-sm sm:prose lg:prose-lg dark:prose-invert w-full
                                                    prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white prose-headings:scroll-mt-6
                                                    prose-h1:text-2xl sm:prose-h1:text-3xl prose-h1:mb-4 prose-h1:pb-3 prose-h1:border-b-2 prose-h1:border-blue-200 dark:prose-h1:border-blue-800
                                                    prose-h2:text-xl sm:prose-h2:text-2xl prose-h2:mt-8 prose-h2:mb-4 prose-h2:pb-2 prose-h2:border-b prose-h2:border-gray-200 dark:prose-h2:border-gray-700
                                                    prose-h3:text-lg sm:prose-h3:text-xl prose-h3:mt-6 prose-h3:mb-3
                                                    prose-h4:text-base sm:prose-h4:text-lg prose-h4:mt-4 prose-h4:mb-2
                                                    prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed prose-p:mb-4 prose-p:text-sm sm:prose-p:text-base
                                                    prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline prose-a:font-medium
                                                    prose-strong:text-gray-900 dark:prose-strong:text-white prose-strong:font-bold
                                                    prose-em:text-gray-700 dark:prose-em:text-gray-300 prose-em:italic
                                                    prose-code:text-purple-600 dark:prose-code:text-purple-400 prose-code:bg-purple-50 dark:prose-code:bg-purple-900/20 prose-code:px-2 prose-code:py-0.5 prose-code:rounded prose-code:text-sm prose-code:font-mono prose-code:before:content-none prose-code:after:content-none prose-code:border prose-code:border-purple-200 dark:prose-code:border-purple-800
                                                    prose-pre:bg-gray-900 dark:prose-pre:bg-gray-950 prose-pre:text-gray-100 prose-pre:p-4 prose-pre:rounded-xl prose-pre:overflow-x-auto prose-pre:shadow-lg prose-pre:border prose-pre:border-gray-700
                                                    prose-ul:list-disc prose-ul:pl-6 prose-ul:mb-4 prose-ul:space-y-2
                                                    prose-ol:list-decimal prose-ol:pl-6 prose-ol:mb-4 prose-ol:space-y-2
                                                    prose-li:text-gray-700 dark:prose-li:text-gray-300 prose-li:text-sm sm:prose-li:text-base
                                                    prose-blockquote:border-l-4 prose-blockquote:border-blue-500 prose-blockquote:pl-4 prose-blockquote:pr-4 prose-blockquote:italic prose-blockquote:text-gray-600 dark:prose-blockquote:text-gray-400 prose-blockquote:bg-blue-50 dark:prose-blockquote:bg-blue-900/20 prose-blockquote:py-3 prose-blockquote:rounded-r-lg prose-blockquote:my-4
                                                    prose-table:w-full prose-table:border-collapse prose-table:my-6 prose-table:text-sm
                                                    prose-thead:bg-gray-100 dark:prose-thead:bg-gray-700
                                                    prose-th:bg-gray-100 dark:prose-th:bg-gray-700 prose-th:p-3 prose-th:text-left prose-th:font-bold prose-th:border prose-th:border-gray-300 dark:prose-th:border-gray-600 prose-th:text-gray-900 dark:prose-th:text-white
                                                    prose-td:p-3 prose-td:border prose-td:border-gray-300 dark:prose-td:border-gray-600 prose-td:text-gray-700 dark:prose-td:text-gray-300
                                                    prose-tr:even:bg-gray-50 dark:prose-tr:even:bg-gray-800/50
                                                    prose-img:rounded-xl prose-img:shadow-xl prose-img:my-6 prose-img:border prose-img:border-gray-200 dark:prose-img:border-gray-700
                                                    prose-hr:border-gray-300 dark:prose-hr:border-gray-700 prose-hr:my-8
                                                    prose-kbd:bg-gray-100 dark:prose-kbd:bg-gray-800 prose-kbd:px-2 prose-kbd:py-1 prose-kbd:rounded prose-kbd:text-sm prose-kbd:font-mono prose-kbd:border prose-kbd:border-gray-300 dark:prose-kbd:border-gray-600">
                                            {!! \Illuminate\Support\Str::markdown($selectedPlugin['readme']) !!}
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Documentation Footer - Enhanced --}}
                                <div class="px-4 sm:px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-2 sm:space-y-0 text-sm">
                                        <span class="text-gray-600 dark:text-gray-400 flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="truncate">Documentation provided by plugin author</span>
                                        </span>
                                        <div class="flex items-center space-x-4">
                                            <span class="text-gray-500 dark:text-gray-400">
                                                Version: {{ $selectedPlugin['version'] }}
                                            </span>
                                            @if($selectedPlugin['documentation_url'] ?? false)
                                            <a href="{{ $selectedPlugin['documentation_url'] }}" target="_blank" 
                                               class="text-blue-600 dark:text-blue-400 hover:underline flex items-center">
                                                <span>Online Docs</span>
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Changelog Tab --}}
                        @if($selectedPlugin['changelog'])
                        <div x-show="activeTab === 'changelog'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Changelog Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Version History</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">Track changes and updates</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Changelog Content --}}
                                <div class="p-8 max-h-[600px] overflow-y-auto">
                                    <div class="prose prose-sm sm:prose lg:prose-lg dark:prose-invert max-w-none
                                                prose-headings:font-bold prose-headings:text-gray-900 dark:prose-headings:text-white
                                                prose-h1:text-2xl prose-h1:mb-4 prose-h1:text-green-600 dark:prose-h1:text-green-400
                                                prose-h2:text-xl prose-h2:mt-6 prose-h2:mb-3 prose-h2:text-green-600 dark:prose-h2:text-green-400
                                                prose-h3:text-lg prose-h3:mt-4 prose-h3:mb-2
                                                prose-p:text-gray-700 dark:prose-p:text-gray-300 prose-p:leading-relaxed
                                                prose-ul:list-disc prose-ul:pl-6 prose-ul:space-y-1
                                                prose-li:text-gray-700 dark:prose-li:text-gray-300
                                                prose-strong:text-gray-900 dark:prose-strong:text-white
                                                prose-code:text-purple-600 dark:prose-code:text-purple-400 prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm">
                                        {!! \Illuminate\Support\Str::markdown($selectedPlugin['changelog']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Screenshots Tab --}}
                        @if(count($selectedPlugin['screenshots']) > 0)
                        <div x-show="activeTab === 'screenshots'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                {{-- Screenshots Header --}}
                                <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Plugin Screenshots</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ count($selectedPlugin['screenshots']) }} image(s)</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Screenshots Grid --}}
                                <div class="p-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($selectedPlugin['screenshots'] as $index => $screenshot)
                                        <div class="group relative overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-300 cursor-pointer"
                                             onclick="window.open('{{ $screenshot }}', '_blank')">
                                            <img src="{{ $screenshot }}" 
                                                 alt="Screenshot {{ $index + 1 }}" 
                                                 class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105">
                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-all duration-300 flex items-center justify-center">
                                                <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                <p class="text-white text-sm font-medium">Screenshot {{ $index + 1 }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Danger Zone Tab (Phase 9) --}}
                        @if($selectedPlugin['is_installed'])
                        <div x-show="activeTab === 'danger'"
                             x-data="{ showDangerConfirm: false, confirmText: '', dangerAction: '' }"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100">
                            <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-red-300 dark:border-red-800 overflow-hidden">
                                {{-- Danger Zone Header --}}
                                <div class="px-4 sm:px-6 py-4 bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-900/30 border-b-2 border-red-300 dark:border-red-800">
                                    <div class="flex items-start space-x-3 sm:space-x-4">
                                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-red-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-lg sm:text-xl font-bold text-red-900 dark:text-red-300 mb-2">Danger Zone</h3>
                                            <p class="text-xs sm:text-sm text-red-800 dark:text-red-400">
                                                These actions are irreversible and may cause data loss. Proceed with extreme caution.
                                                A backup is strongly recommended before performing any dangerous operations.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Dangerous Actions Grid --}}
                                <div class="p-4 sm:p-6 md:p-8">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Force Uninstall --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Force Uninstall</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Completely remove plugin files and directories. Cannot be undone.
                                                    </p>
                                                    <button @click="showDangerConfirm = true; dangerAction = 'force_uninstall'"
                                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold transition-colors w-full">
                                                        Force Uninstall
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Reset Plugin Data --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Reset Plugin Data</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Clear all plugin data and restore to default state. Settings will be lost.
                                                    </p>
                                                    <button @click="showDangerConfirm = true; dangerAction = 'reset_data'"
                                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold transition-colors w-full">
                                                        Reset Data
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Delete Configuration --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Delete Configuration</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Remove all configuration files and settings. Plugin will need reconfiguration.
                                                    </p>
                                                    <button @click="showDangerConfirm = true; dangerAction = 'delete_config'"
                                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold transition-colors w-full">
                                                        Delete Config
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Remove Database Tables --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Remove Database Tables</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Drop all database tables created by this plugin. All data will be lost.
                                                    </p>
                                                    <button @click="showDangerConfirm = true; dangerAction = 'remove_tables'"
                                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold transition-colors w-full">
                                                        Remove Tables
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Clear Plugin Cache --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-orange-200 dark:border-orange-800 hover:border-orange-400 dark:hover:border-orange-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Clear Plugin Cache</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Remove all cached data for this plugin. Less dangerous but may affect performance.
                                                    </p>
                                                    <button wire:click="clearPluginCache('{{ $selectedPlugin['id'] }}')"
                                                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 text-sm font-semibold transition-colors w-full">
                                                        Clear Cache
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Disable Permanently --}}
                                        <div class="p-4 sm:p-5 bg-white dark:bg-gray-900 rounded-lg border-2 border-red-200 dark:border-red-800 hover:border-red-400 dark:hover:border-red-600 transition-colors">
                                            <div class="flex items-start space-x-3 mb-3">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1 text-sm sm:text-base">Disable Permanently</h4>
                                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                                        Prevent plugin from being activated again. Requires manual intervention to re-enable.
                                                    </p>
                                                    <button @click="showDangerConfirm = true; dangerAction = 'disable_permanently'"
                                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold transition-colors w-full">
                                                        Disable Forever
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Danger Confirmation Modal --}}
                            <div x-show="showDangerConfirm" 
                                 x-cloak
                                 class="fixed inset-0 z-[60] overflow-y-auto"
                                 style="display: none;">
                                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                    <div class="fixed inset-0 transition-opacity bg-gray-900/75" @click="showDangerConfirm = false"></div>

                                    <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl relative z-10">
                                        <div class="p-6">
                                            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                                                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>

                                            <h3 class="text-xl font-bold text-gray-900 dark:text-white text-center mb-2">Confirm Dangerous Action</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-6">
                                                This action cannot be undone. To confirm, please type the plugin name:
                                                <span class="font-bold text-red-600 dark:text-red-400">{{ $selectedPlugin['name'] }}</span>
                                            </p>

                                            <input type="text" 
                                                   x-model="confirmText"
                                                   placeholder="Type plugin name to confirm"
                                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:text-white mb-4">

                                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-6">
                                                <div class="flex items-start space-x-2">
                                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <div class="text-xs text-yellow-800 dark:text-yellow-300">
                                                        <strong>Warning:</strong> A backup will be created automatically, but recovery is not guaranteed. Make sure you have a recent backup of your entire system.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-end space-x-3">
                                                <button @click="showDangerConfirm = false; confirmText = ''" 
                                                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium">
                                                    Cancel
                                                </button>
                                                <button @click="if(confirmText === '{{ $selectedPlugin['name'] }}') { 
                                                            $wire.executeDangerousAction('{{ $selectedPlugin['id'] }}', dangerAction); 
                                                            showDangerConfirm = false; 
                                                            confirmText = ''; 
                                                        }"
                                                        :disabled="confirmText !== '{{ $selectedPlugin['name'] }}'"
                                                        :class="confirmText === '{{ $selectedPlugin['name'] }}' ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-400 cursor-not-allowed'"
                                                        class="px-6 py-2 text-white rounded-lg font-medium transition-colors disabled:opacity-50">
                                                    Confirm & Execute
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Danger Zone Section (Phase 9) - REMOVED, NOW IN TAB --}}

                {{-- Enhanced Action Buttons --}}
                <div class="flex items-center justify-between pt-8 border-t-2 border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        @if($selectedPlugin['is_installed'])
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Plugin is installed and ready
                            </span>
                        @else
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Install to start using this plugin
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        @if($selectedPlugin['is_installed'])
                            @if($selectedPlugin['is_active'])
                                <button wire:click="deactivate('{{ $selectedPlugin['id'] }}')" 
                                        wire:loading.attr="disabled"
                                        wire:target="deactivate('{{ $selectedPlugin['id'] }}')"
                                        class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 font-semibold transition-all duration-200 disabled:opacity-50 flex items-center space-x-2 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Deactivate</span>
                                </button>
                            @else
                                <button wire:click="activate('{{ $selectedPlugin['id'] }}')" 
                                        wire:loading.attr="disabled"
                                        wire:target="activate('{{ $selectedPlugin['id'] }}')"
                                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 font-semibold shadow-lg shadow-green-500/50 transition-all duration-200 disabled:opacity-50 flex items-center space-x-2 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Activate</span>
                                </button>
                            @endif
                            <button wire:click="uninstall('{{ $selectedPlugin['id'] }}', true)" 
                                    wire:loading.attr="disabled"
                                    wire:target="uninstall('{{ $selectedPlugin['id'] }}', true)"
                                    class="px-6 py-3 bg-red-600 text-white rounded-xl hover:bg-red-700 font-semibold shadow-lg shadow-red-500/50 transition-all duration-200 disabled:opacity-50 flex items-center space-x-2 group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Uninstall</span>
                            </button>
                        @else
                            <button wire:click="install('{{ $selectedPlugin['id'] }}')" 
                                    wire:loading.attr="disabled"
                                    wire:target="install('{{ $selectedPlugin['id'] }}')"
                                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 font-bold shadow-lg shadow-purple-500/50 transition-all duration-200 disabled:opacity-50 flex items-center space-x-2 group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Install Plugin</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif


{{-- Upload Plugin Modal --}}
@if($showUploadModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-show="true" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="$set('showUploadModal', false)"></div>

        <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Plugin</h2>
                    <button wire:click="$set('showUploadModal', false)" 
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    {{-- Upload Area --}}
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-purple-500 dark:hover:border-purple-400 transition-colors"
                         x-data="{ dragging: false }"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; $wire.uploadedFile = $event.dataTransfer.files[0]"
                         :class="{ 'border-purple-500 bg-purple-50 dark:bg-purple-900/20': dragging }">
                        
                        @if($uploadedFile)
                            <div class="flex items-center justify-center space-x-3">
                                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $uploadedFile->getClientOriginalName() }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($uploadedFile->getSize() / 1024, 2) }} KB</p>
                                </div>
                                <button wire:click="$set('uploadedFile', null)" 
                                        class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Drop your plugin ZIP file here</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">or click to browse</p>
                            <label class="inline-block px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 cursor-pointer transition-colors">
                                <input type="file" wire:model="uploadedFile" accept=".zip" class="hidden">
                                Choose File
                            </label>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">Maximum file size: 50MB</p>
                        @endif
                    </div>

                    {{-- Options --}}
                    <div class="space-y-3">
                        <label class="flex items-center space-x-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                            <input type="checkbox" wire:model="backupBeforeAction" 
                                   class="w-5 h-5 text-purple-600 bg-white border-gray-300 rounded focus:ring-purple-500">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Create backup before installation</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Recommended for safety</p>
                            </div>
                        </label>
                    </div>

                    {{-- Instructions --}}
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">Plugin Requirements:</h4>
                        <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1">
                            <li>• ZIP file must contain Plugin.php in root or src directory</li>
                            <li>• Plugin must extend HyroPlugin base class</li>
                            <li>• Valid namespace and class structure required</li>
                            <li>• Dependencies will be checked automatically</li>
                        </ul>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button wire:click="$set('showUploadModal', false)" 
                                class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium">
                            Cancel
                        </button>
                        <button wire:click="uploadPlugin" 
                                :disabled="!$wire.uploadedFile"
                                class="px-6 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 font-medium shadow-lg shadow-purple-500/50 disabled:opacity-50 disabled:cursor-not-allowed">
                            Upload & Install
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Confirmation Modal --}}
@if($showConfirmModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-show="true" x-cloak>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" wire:click="$set('showConfirmModal', false)"></div>

        <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-2xl rounded-2xl">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-gray-900 dark:text-white text-center mb-2">Confirm {{ ucfirst($confirmAction) }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 text-center mb-6">
                    Are you sure you want to {{ $confirmAction }} this plugin? 
                    @if($forceAction)
                        <span class="text-red-600 dark:text-red-400 font-semibold">This will permanently delete all plugin files.</span>
                    @else
                        This action can be reversed.
                    @endif
                </p>

                <div class="flex items-center justify-end space-x-3">
                    <button wire:click="$set('showConfirmModal', false)" 
                            class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium">
                        Cancel
                    </button>
                    <button wire:click="confirmUninstall" 
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        {{ $forceAction ? 'Force ' : '' }}{{ ucfirst($confirmAction) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
